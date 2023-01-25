<?php

namespace  App\Models;

class Image extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'link',
        'size',
    ];
}
