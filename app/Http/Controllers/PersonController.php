<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function makePerson(Request $request)
    {
        $inputs = $request->all();

        $result = \App\Models\Employee::createEmployee($inputs);

        if ($result['success'] == 0) {
            return $result['errors'];
        } else {
            $person_id = \App\Models\Employee::find($result['employee_id']);

            return $person_id;
        }
    }
}
