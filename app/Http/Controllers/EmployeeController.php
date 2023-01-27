<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Day;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    use \App\Traits\ApiResponderTrait;

    public function createAttendance()
    {
        $attendance=new Attendance();
        $attendance->reception_id=auth()->user()->person->employee->reception->id;
        $attendance->save();
    }
    public function attendances($employee_id)
    {
        $employee_id=Person::find($employee_id)->employee->id??'false';

    }

    public static function assignWorkingTimes($working_times,$employee_id)
    {
        $rules=[
            '*.day' => [
                'required',
                new \App\Rules\DaysRule,

            ],
            '*.times' => [
                'required',
                new \App\Rules\RequierdTimeRule,
                new \App\Rules\EndAfterStartRule,
                new \App\Rules\HalfHourMultiplesRule,
                new \App\Rules\ConflictTimesRule,
            ],
        ];
        $validator=Validator::make($working_times,$rules);
        if($validator->fails())
            return [
                'success'=>0,
                'message'=>$validator->errors()->all()
            ];

        foreach($working_times as $working_time)
        {
            $day_id=Day::where('day',$working_time['day'])->first()->id;
            foreach($working_time['times'] as $time)
            {
                $time = \App\Models\WorkingHour::firstOrCreate(['start' => $time['start'], 'end' => $time['end']]);
                \App\Models\WorkingHourEmployees::firstOrCreate([
                    'employee_id' => $employee_id,
                    'day_id' => $day_id,
                    'working_hour_id' => $time->id,
                ]);
            }
        }
        return [
        'success'=>1,
        ];
    }

    public function getWorkTimes(Request $request)
    {
        //check if he want his working time
        if(auth()->user()->hasRole('Reception')||auth()->user()->hasRole('Doctor')){
            if(!isset($request->id))
                $employee_id=auth()->user()->person->employee->id;
        }
        $employee_id=Person::find($request->id)->employee->id??auth()->user()->person->employee->id;
        //check if employee id was input
        if (! isset($employee_id)) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }
        //get days,times from database
        $days = \App\Models\Day::all();
        $records = [];
        foreach ($days as $day) {
            $records[] = \Illuminate\Support\Facades\DB::table('working_hour_employees')
            ->join('working_hours', 'working_hours.id', 'working_hour_employees.working_hour_id')
            ->join('employees', 'employees.id', 'working_hour_employees.employee_id')
            ->join('days', 'days.id', 'working_hour_employees.day_id')
            ->where([['employees.id', '=', $employee_id], ['days.id', '=', $day->id]])
            ->select('days.day', 'working_hours.start', 'working_hours.end')
            ->get();
        }
        //remove empty arrays wich came from database
        $data = [];
        foreach ($records as $record) {
            $record = json_decode($record, true);
            if (! empty($record)) {
                $data[] = $record;
            }
        }
        //merge same days in one field and assign thier times to them
        $new_arr = [];
        foreach ($data as $items) {
            foreach ($items as $item) {
                $new_arr[$item['day']][] = ['start' => $item['start'], 'end' => $item['end']];
            }
        }
        if (empty($new_arr)) {
            return $this->badRequestResponse('the employee doesnt has any working time');
        } else {
            return $this->success($new_arr);
        }
    }

    public function getWorkingTimeInDay(Request $request)
    {
        $data = [];
        $date = $request->date;
        $employee_id = Person::find($request->employee_id)->employee->id ?? 'false';
        $data['employee_id'] = $employee_id;
        $data['date'] = $date;
        $today = Carbon::now()->format('Y-m-d');
        $rules =
        [
            'employee_id' => 'required|not_in:false',
            // 'date' => "required|date_format:Y-m-d|after_or_equal:{$today}",
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }
        $day = Carbon::parse($date)->format('l');
        $times = DB::table('working_hour_employees')
        ->join('working_hours', 'working_hours.id', 'working_hour_employees.working_hour_id')
        ->join('employees', 'employees.id', 'working_hour_employees.employee_id')
        ->join('days', 'days.id', 'working_hour_employees.day_id')
        ->where([['employees.id', '=', $employee_id], ['days.day', '=', $day]])
        ->select('working_hours.start', 'working_hours.end')
        ->get();
        if ($times->count() == 0) {
            return $this->noContent('the employee has not working times in this day');
        }

        return $this->success($times);
    }
}
