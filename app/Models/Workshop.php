<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workshop extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'workshops';
    protected $guarded = ['id'];
    protected $casts = [
        'started_at' => 'date',
        'time_at' => 'datetime:H:i',
    ];

    public function setNameAttribute($value) {
        $this->attributes ['name'] = $value;
        $this->attributes ['slug'] = Str::slug($value, '-');
    }

    public function benefits() {
        return $this->hasMany(WorkshopBenefit::class);
    }

    public function participants() {
        return $this->hasMany(WorkshopParticipant::class);
    }

    public function category() {
        return $this->belongsTo(Category:: class, 'category_id', 'id');
    }

    public function instructor() {
        return $this->belongsTo(WorkshopInstructor:: class, 'workshop_instructor_id', 'id');
    }
}
