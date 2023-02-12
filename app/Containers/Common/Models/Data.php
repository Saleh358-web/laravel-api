<?php

namespace App\Containers\Common\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use SoftDeletes;

    protected $table = 'data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'description',
        'type_id'
    ];

    public function type()
    {
        return $this->belongsTo(DataType::class, 'type_id');
    }
}
