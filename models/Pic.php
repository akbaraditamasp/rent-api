<?php

namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Pic extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "path",
        "building_id"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // protected $hidden = [
    //     'password',
    // ];

    protected $casts = [
        'user_id' => 'integer'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
