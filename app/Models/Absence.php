<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Absence extends BaseModel
{
    use SoftDeletes;
    protected $table = 'lecture_absences';
    public $timestamps = false;


    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }
}
