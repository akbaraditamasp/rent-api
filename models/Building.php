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
        "name", "address", "facilities", "pic"
    ];

    
}
