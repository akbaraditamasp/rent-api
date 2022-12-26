<?php

namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class User extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "username",
        "password"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_owner' => 'bool',
    ];

    public function buildings()
    {
        return $this->hasMany(Building::class, "user_id");
    }
}
