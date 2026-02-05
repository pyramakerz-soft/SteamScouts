<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'lesson_school');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
    public function ebooks()
    {
        return $this->hasMany(Ebook::class);
    }
     public function getImageAttribute($val)
    {
        return ($val !== null) ? asset( $val) : "";
    }
         public function getFilePathAttribute($val)
    {
        return ($val !== null) ? asset('ebooks/'. $val) : "";
    }
        public function getTeacherFilePathAttribute($val)
    {
        return ($val !== null) ? asset('ebooks/' . $val) : "";
    }
}
