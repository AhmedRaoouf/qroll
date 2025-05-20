<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class SectionAbsence extends BaseModel
{
    use SoftDeletes;
    protected $table = 'section_absences';
    public $timestamps = false;

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
