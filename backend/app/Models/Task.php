<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Enums\TaskStatus;

class Task extends Model
{

    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = ['title', 'description', 'status', 'due_date', 'created_by'];

    //protected $appends = ['label', 'color', 'transitions'];

    protected $casts = [
        'status' => TaskStatus::class,
        'due_date' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'id');
    }

    public function getDueDateAttribute($value)
    {

        return Carbon::parse($value)->format('Y-m-d');
    }

    public function getTransitionsAttribute()
    {

        return $this->status->transitions();
    }

    public function getLabelAttribute()
    {

        return $this->status->label();
    }

    public function getColorAttribute()
    {

        return $this->status->color();
    }
}
