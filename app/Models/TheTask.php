<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TheTask extends Model
{
    protected $table = 'tasks';
    protected $fillable = [
        'schedule_id',
        'worker_id',
        'shop_id',
        'service_id',
        'note',
        'needs'
    ];

    protected $casts = [
        'due_date' => 'date'
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'shop_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

}
