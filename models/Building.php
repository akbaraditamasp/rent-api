<?php

namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Building extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name", "address", "facilities", "pic", "price"
    ];

    protected $casts = [
        "facilities" => "array",
        "price" => "int"
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function pics()
    {
        return $this->hasMany(Pic::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
