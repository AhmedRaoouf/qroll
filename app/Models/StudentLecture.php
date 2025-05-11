<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class StudentLecture extends BaseModel
{
    use SoftDeletes;
    public $timestamps = false;

}
