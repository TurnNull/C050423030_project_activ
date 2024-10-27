<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';
    protected $guarded = ['id'];

    public function setNameAttribute($value) {
        $this->attributes ['name'] = $value;
        $this->attributes ['slug'] = Str::slug($value, '-');
    }

    public function workshops() {
        return $this->hasMany(Workshop::class);
    }
}
