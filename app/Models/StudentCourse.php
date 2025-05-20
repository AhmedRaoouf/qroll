<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourse extends BaseModel
{
    use SoftDeletes;
    public $timestamps = false;
}
