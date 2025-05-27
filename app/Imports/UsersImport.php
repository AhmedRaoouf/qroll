<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $user = User::create([
            'name'        => $row['name'],
            'email'       => $row['email'],
            'phone'       => $row['phone'] ?? null,
            'national_id' => $row['national_id'],
            'birth_date'  => $row['birth_date'] ?? null,
            'address'     => $row['address'] ?? null,
            'password'    => Hash::make('123456789'),
            'role_id'     => $this->getRoleId($row['type']), // دور بناءً على النوع
        ]);

        switch (strtolower($row['type'])) {
            case 'doctor':
                Doctor::create([
                    'user_id'   => $user->id,
                    'education' => $row['education'] ?? null,
                ]);
                break;
            case 'teacher':
                Teacher::create([
                    'user_id'   => $user->id,
                    'education' => $row['education'] ?? null,
                ]);
                break;
            case 'student':
                Student::create([
                    'user_id'     => $user->id,
                    'academic_id' => $row['academic_id'],
                ]);
                break;
        }

        return $user;
    }

    protected function getRoleId($type)
    {
        return \App\Models\Role::where('name', strtolower($type))->value('id');
    }
}
