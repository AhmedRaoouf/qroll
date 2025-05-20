<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends BaseModel
{
    use SoftDeletes;


    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_sections', 'section_id', 'student_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'section_id');
    }
}
