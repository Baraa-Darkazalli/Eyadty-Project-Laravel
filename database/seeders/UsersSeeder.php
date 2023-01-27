<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Employee;
use App\Models\Name;
use App\Models\Patient;
use App\Models\Person;
use App\Models\Reception;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createDoctor('omar123','123','omar@gmail.com');
        $this->createPatient('baraa123','123','baraa@gmail.com');
        $this->createReception('aboomar123','123','aboomarbaraa@gmail.com');
        $this->createAdmin('admin','admin','admin@gmail.com');
    }
    
    public function createAdmin($username, $password, $email)
    {
        $name=new Name();
        $name->first_name='Ahmad';
        $name->last_name='Al Bagdadi';
        $name->save();
        $person=new Person();
        $person->gender=1;
        $person->birth_date='2001-1-1';
        $person->name_id=$name->id;
        $person->save();
        $admin = new User();
        $admin->username = $username;
        $admin->email = $email;
        $admin->password = bcrypt($password);
        $admin->person_id=$person->id;
        $admin->save();
        $admin->attachRole('Admin');
    }

    public function createDoctor($username, $password, $email)
    {
        $name=Name::find(Doctor::first()->employee->person->name_id);
        $name->first_name='Omar';
        $name->last_name='Al Hakem';
        $name->save();
        $person=Person::find(Doctor::first()->employee->person_id);
        $person->gender=1;
        $person->birth_date='1999-1-1';
        $person->name_id=$name->id;
        $person->save();
        $employee=Employee::find(Doctor::first()->employee_id);
        $employee->salary=200000;
        $employee->previous_experience=3;
        $employee->person_id=$person->id;
        $employee->save();
        $doctor=Doctor::first();
        $doctor->salary_rate=0.5;
        $doctor->employee_id=$employee->id;
        $doctor->session_duration_id=1;
        $doctor->save();
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password = bcrypt($password);
        $user->person_id=$person->id;
        $user->save();
        $user->attachRole('Doctor');
    }

    public function createPatient($username, $password, $email)
    {
        $name=Name::find(Patient::first()->person->name_id);
        $name->first_name='Baraa';
        $name->last_name='Al Domani';
        $name->save();
        $person=Person::find(Patient::first()->person_id);
        $person->gender=1;
        $person->birth_date='2001-10-1';
        $person->name_id=$name->id;
        $person->save();
        $patient=Patient::first();
        $patient->weight=73;
        $patient->blood_type_id=1;
        $patient->person_id=$person->id;
        $patient->save();
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password = bcrypt($password);
        $user->person_id=$person->id;
        $user->save();
        $user->attachRole('Patient');
    }

    public function createReception($username, $password, $email)
    {
        $name=Name::find(Reception::first()->employee->person->name_id);
        $name->first_name='Baraa';
        $name->last_name='Darkazalli';
        $name->save();
        $person=Person::find(Reception::first()->employee->person_id);
        $person->gender=1;
        $person->birth_date='2001-10-13';
        $person->name_id=$name->id;
        $person->save();
        $employee=Employee::find(Reception::first()->employee_id);
        $employee->salary=100000;
        $employee->previous_experience=2;
        $employee->person_id=$person->id;
        $employee->save();
        $reception=Reception::first();
        $reception->employee_id=$employee->id;
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password = bcrypt($password);
        $user->person_id=$person->id;
        $user->save();
        $user->attachRole('Reception');
    }
}
