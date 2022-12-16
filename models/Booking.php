<?php

namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Booking extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "date", "price", "is_paid", "inv", "inv_link", "detail", "building_id", "customer_id"
    ];

    protected $casts = [
        "date" => "date",
        "price" => "int",
        "detail" => "json"
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
