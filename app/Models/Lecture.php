<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Lecture extends BaseModel
{
    use SoftDeletes;


    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_lectures', 'lecture_id', 'student_id');
    }
}
