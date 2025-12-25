<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    protected $fillable = [
        'title',
        'description',
        'task_id',
        'worker_id',
        'due_date',
        'priority',
        'status',
        'assigned_by'
    ];

    protected $casts = [
        'due_date' => 'date'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
