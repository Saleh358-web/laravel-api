<?php

namespace App\Containers\Common\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DataType extends Model
{
    use SoftDeletes;

    protected $table = 'data_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

    public function data()
    {
        return $this->hasMany(Data::class, 'type_id');
    }
}
