<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function tasks()
    {
        return $this->hasMany(TheTask::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
