<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'title'
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
