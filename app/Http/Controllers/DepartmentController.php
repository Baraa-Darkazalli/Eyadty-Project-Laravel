<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Traits\ApiResponderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    use ApiResponderTrait;

    public function add(Request $request)//role:Admin
    {
        $data = $request->only('name');
        $validator = Validator::make($data, ['name' => 'required|unique:clinic_departments']);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        } else {
            $department = new \App\Models\ClinicDepartment();
            $department->name = $request->name;
            $department->save();

            return $this->createdResponse($department, __('msg.department_added_successfully'));
        }
    }

    public function index()//role:Admin|Patient
    {
        //check if empty
        if (empty(Department::count())) {
            return $this->okResponse(null, __('msg.there_ara_no_departments'));
        }
        $departments = \App\Models\ClinicDepartment::select('id', 'name')->get();

        return $this->okResponse($departments);
    }

    public function search(Request $request)//role:Admin|Patient
    {
        $departments = Department::select('id', 'name')->where('name', 'LIKE', '%'.$request->input.'%')->get();
        if (count($departments) > 0) {
            return $this->okResponse($departments);
        } else {
            return $this->okResponse(null, __('msg.input_not_found'));
        }
    }
}
