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
}
