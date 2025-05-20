<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends BaseModel
{
    use SoftDeletes;


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'student_courses', 'student_id', 'course_id');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'student_sections', 'student_id', 'section_id');
    }

    public function lectures()
    {
        return $this->belongsToMany(Lecture::class, 'student_lectures', 'student_id', 'lecture_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'student_id');
    }

    public function section_absences()
    {
        return $this->hasMany(SectionAbsence::class, 'student_id');
    }
}
