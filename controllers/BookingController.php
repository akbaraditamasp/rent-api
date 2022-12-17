<?php

namespace Controller;

use Carbon\Carbon;
use EndyJasmi\Cuid;
use Model\Booking;
use Model\Building;
use Model\Customer;
use Respect\Validation\Validator as v;
use Siluet\App;
use Siluet\Auth;
use Siluet\CustomerAuth;
use Siluet\Eloquent;
use Siluet\Validation;
use Siluet\Xendit;

class BookingController
{
    public function add($id)
    {
        CustomerAuth::validate(false);
        $body = Validation::validate([
            "body" => CustomerAuth::$user ? [
                "date" => v::date("d-m-Y"),
                "detail" => v::arrayType(),
            ] : [
                "date" => v::date("d-m-Y"),
                "detail" => v::arrayType(),
                "user" => v::optional(v::arrayType()->keySet(
                    v::key("email", v::stringType()->notEmpty()->email()),
                    v::key("password", v::stringType()->notEmpty()),
                ))
            ]
        ]);
        App::controller(function () use ($id, $body) {
            /**
             * @var Building $building
             */
            $building = Building::findOrFail($id);
            $booking = $building->bookings()->whereDate("date", "=", Carbon::createFromFormat("d-m-Y", $body["date"])->toDateString())->first();

            if ($booking) {
                App::$response = App::$response->withStatus(400);
                return [
                    "error" => "Date has taken"
                ];
            }

            $booking = new Booking();
            $data = [];
            (Eloquent::getCapsule())->connection()->transaction(
                function () use ($booking, $building, $body, &$data) {
                    $user = CustomerAuth::$user;
                    if (!$user) {
                        $user = new Customer();
                        $user->email = $body["user"]["email"];
                        $user->password = password_hash($body["user"]["password"], PASSWORD_BCRYPT);
                        $user->save();

                        $token = CustomerAuth::makeToken($user);
                    }

                    $external = strtoupper(Cuid::cuid());

                    $params = [
                        'external_id' => $external,
                        'amount' => $building->price,
                        'description' => 'Sewa Gedung',
                        'invoice_duration' => 60 * 60 * 24,
                        'currency' => 'IDR',
                        'items' => [
                            [
                                'name' => 'Sewa Gedung',
                                'quantity' => 1,
                                'price' => $building->price,
                            ],
                        ],
                    ];

                    ["invoice_url" => $url] = (Xendit::get())::create($params);

                    $booking->date = Carbon::createFromFormat("d-m-Y", $body["date"])->toDateString();
                    $booking->price = $building->price;
                    $booking->is_paid = false;
                    $booking->inv = $external;
                    $booking->inv_link = $url;
                    $booking->detail = $body["detail"];
                    $booking->customer_id = $user->id;

                    $building->bookings()->save($booking);

                    $data = $booking->toArray();
                    if (isset($token)) {
                        $data["user"] = $user->toArray() + ["token" => $token];
                    }
                }
            );

            return $data;
        });
    }

    public function get($id)
    {
        CustomerAuth::validate(false);
        if (!CustomerAuth::$user) {
            Auth::validate();
        }
        App::controller(function () use ($id) {
            /**
             * @var Booking $booking
             */
            $booking;

            if (CustomerAuth::$user) {
                $booking = CustomerAuth::$user->bookings()->with("building")->findOrFail($id);
            } else {
                $booking = Booking::with("building")->findOrFail($id);
            }

            return $booking->toArray();
        });
    }

    public function index()
    {
        CustomerAuth::validate(false);

        if (!CustomerAuth::$user) {
            Auth::validate();
        }

        App::controller(function () {
            $limit = App::$request->getQueryParams()["limit"] ?? 5;
            $page = App::$request->getQueryParams()["page"] ?? 1;
            $offset = (((int) $page) - 1) * ((int) $limit);

            if (CustomerAuth::$user) {
                $bookings = CustomerAuth::$user->bookings()->with("building")->orderByDesc("created_at");
            } else {
                $bookings = Booking::with("building")->orderByDesc("created_at");
            }

            return [
                "pageTotal" => ceil($bookings->count() / ((int) $limit)),
                "rows" => $bookings->skip($offset)->take($limit)->get()
            ];
        });
    }

    public function check($id)
    {
        ["date" => $date] = Validation::validate([
            "query" => [
                "date" => v::date("d-m-Y")
            ]
        ]);
        App::controller(function () use ($id, $date) {
            $booking = Booking::where("building_id", $id)->whereDate("date", "=", Carbon::createFromFormat("d-m-Y", $date)->toDateString())->first();

            if ($booking) {
                App::$response = App::$response->withStatus(400);
                return [
                    "error" => "Date has taken"
                ];
            }

            return [];
        });
    }

    public function callback()
    {
        ["merchant_name" => $merchant, "id" => $id] = Validation::validate([
            "body" => [
                "merchant_name" => v::optional(v::stringType()->notEmpty()),
                "id" => v::optional(v::stringType()->notEmpty())
            ]
        ]);
        App::controller(function () use ($merchant, $id) {
            if ($merchant === "Xendit") {
                return ["success" => "Hello xendit!"];
            }

            $token = App::$request->getHeaderLine("x-callback-token") ?? "";

            if ($token !== $_ENV["X_CALLBACK_TOKEN"]) {
                App::$response = App::$response->withStatus(401);
                return ["error" => "Unauthorized"];
            }

            $getInvoice = (Xendit::get())::retrieve($id);

            $booking = Booking::where("inv", $getInvoice["external_id"])->firstOrFail();

            if ($getInvoice["status"] === "SETTLED" || $getInvoice["status"] === "PAID") {
                $booking->is_paid = true;
                $booking->save();
            } else if ($getInvoice["status"] === "EXPIRED") {
                $booking->delete();
            }

            return $booking->toArray();
        });
    }
}
