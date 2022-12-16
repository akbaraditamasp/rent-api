<?php

namespace Siluet;

use Model\Customer;

class CustomerController
{
    public function index()
    {
        Auth::validate();
        App::controller(function () {
            $limit = App::$request->getQueryParams()["limit"] ?? 5;
            $page = App::$request->getQueryParams()["page"] ?? 1;
            $offset = (((int) $page) - 1) * ((int) $limit);

            $customer = Customer::orderByDesc("created_at");

            return [
                "pageTotal" => ceil($customer->count() / ((int) $limit)),
                "rows" => $customer->skip($offset)->take($limit)->get()
            ];
        });
    }
}
