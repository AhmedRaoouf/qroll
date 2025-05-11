<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class StudentSection extends BaseModel
{
    use SoftDeletes;
    public $timestamps = false;
}
