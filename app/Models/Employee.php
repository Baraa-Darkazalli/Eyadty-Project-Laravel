<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Employee extends Model
{
    use HasFactory;

    public static function createEmployee($inputs)
    {
        $validator = Validator::make($inputs, ['salary' => 'required']);
        $check_person_rules = \App\models\Person::checkRules($inputs);
        if ($validator->fails() || $check_person_rules['success'] == 0) {
            $errors = array_merge(json_decode($validator->errors(), true), json_decode($check_person_rules['errors'], true));

            return [
                'success' => 0,
                'errors' => $errors,
            ];
        } else {
            $person_id = \App\Models\Person::createPerson($inputs)['person_id'];

            $employee = new Employee();

            $employee->person_id = $person_id;
            $employee->salary = isset($inputs['salary']) ? $inputs['salary'] : null;
            $employee->previous_experience =
                isset($inputs['previous_experience']) ?
                    $inputs['previous_experience'] :
                    null;
            $employee->save();

            return [
                'success' => 1,
                'employee_id' => $employee->id,
            ];
        }
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id');
    }

    public function attendances()
    {
        return $this->belongsToMany(Attendance::class, 'attendance_employees', 'employee_id', 'attendance_id');
    }

    public function balance_payments()
    {
        return $this->hasMany(BalancePayment::class, 'employee_id', 'id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'employee_id', 'id');
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'employee_id', 'id');
    }

    public function working_hours()
    {
        return $this->belongsToMany(WorkingHour::class, 'employee_id', 'working_hour_id');
    }

    public function previous_works()
    {
        return $this->hasMany(PreviousWork::class, 'employee_id', 'id');
    }

    public function nurse()
    {
        return $this->hasOne(Nurse::class, 'employee_id', 'id');
    }

    public function malfunctions()
    {
        return $this->hasMany(Malfunction::class, 'employee_id', 'id');
    }

    public function reception()
    {
        return $this->hasOne(Reception::class, 'employee_id', 'id');
    }

    public function days()
    {
        return $this->belongsToMany(Day::class, 'working_hour_employees');
    }
}
