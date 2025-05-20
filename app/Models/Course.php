<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends BaseModel
{
    use SoftDeletes;


    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_courses', 'course_id', 'student_id');
    }
}
