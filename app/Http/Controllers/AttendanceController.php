<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponderTrait;
class AttendanceController extends Controller
{
    use ApiResponderTrait;
    public function createAttendance()
    {
        $attendance=new \App\Models\Attendance();
        $attendance->reception_id=auth()->user()->person->employee->reception->id;
        $attendance->save();
    }
    public function attendances($employee_id)
    {
        $employee_id=\App\Models\Person::find($employee_id)->employee->id??'false';
        if($employee_id=='false')
            return $this->badRequestResponse('invalid employee_id');
        $attendance=\App\Models\Attendance::whereDate('created_at',now())->first();
        $attendance->employees()->attach($employee_id,['absence_reasone'=>' ']);
        return $this->okResponse(null,'checked');
    }
}
