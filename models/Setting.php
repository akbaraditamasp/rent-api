<?php

namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Setting extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "key",
        "value"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id', "updated_at", "created_at",
    ];
}
