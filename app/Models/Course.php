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
}
