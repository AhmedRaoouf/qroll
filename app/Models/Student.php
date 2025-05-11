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
}
