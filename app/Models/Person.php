<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Person extends Model
{
    use HasFactory;

    public static function checkRules($inputs)
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'birth_date' => 'required',
        ];
        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return [
                'success' => 0,
                'errors' => $validator->errors(),
            ];
        } else {
            return [
                'success' => 1,
                'errors' => $validator->errors(),
            ];
        }
    }

    public static function createPerson($data)
    {
        // $rules=[
        //     'first_name'=>'required',
        //     'last_name'=>'required',
        //     'gender'=>'required',
        //     'birth_date'=>'required'
        // ];
        // $validator=Validator::make($data,$rules);
        // if($validator->fails())
        // {
        //     return [
        //         'success'=>0,
        //         'errors'=>$validator->errors()
        //     ];
        // }
        $check = Person::checkRules($data);
        if ($check['success'] == 0) {
            return [
                'success' => 0,
                'errors' => $check['errors'],
            ];
        } else {
            $name = new \App\Models\Name();
            $name->first_name = $data['first_name'];
            $name->last_name = $data['last_name'];
            $name->father_name =
                isset($data['father_name']) ?
                    $data['father_name'] :
                    null;
            $name->save();
            $person = new Person();
            $person->name_id = $name->id;
            $person->gender = $data['gender'];
            $person->birth_date = $data['birth_date'];
            $person->personal_number =
                isset($data['personal_number']) ?
                    $data['personal_number'] :
                    null;
            $person->save();

            return [
                'success' => 1,
                'person_id' => $person->id,
            ];
        }
    }

    public function phones()
    {
        return $this->hasMany(Phone::class, 'person_id', 'id');
    }

    public function name()
    {
        return $this->belongsTo(Name::class, 'name_id', 'id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'person_id', 'id');
    }

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'person_id', 'id');
    }
}
