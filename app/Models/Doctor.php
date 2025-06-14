<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends BaseModel
{
    use SoftDeletes;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class); // أو belongsToMany حسب النظام
    }
}
