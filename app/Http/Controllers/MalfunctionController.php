<?php

namespace App\Http\Controllers;

use App\Models\Malfunction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponderTrait;
use App\Traits\HelperTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\returnSelf;

class MalfunctionController extends Controller
{
    use ApiResponderTrait,HelperTrait;

    public function sendMalfunction(Request $request)//Doctor|Reception
    {
        $data=$request->only('title','content');
        $rules=[
            'title'=>'required',
            'content'=>'required'
        ];
        $vaildator = Validator::make($data, $rules);
        if ($vaildator->fails())return $this->badRequestResponse($vaildator->errors()->all());

        //create malfunction
        $malfunction=new Malfunction();
        $malfunction->title=$request->title;
        $malfunction->content=$request->content;
        $malfunction->employee_id=User::find(auth()->user()->id)->person->employee->id;
        $malfunction->malfunction_statue_id=1;
        $malfunction->save();

        //send notification to admin
        $title = __('msg.employee').User::find(auth()->user()->id)->person->name->first_name.__('msg.sent_a_malfunction_complaint');
        $content = $request->title.' '.$request->content;
        $admin_id=DB::table('role_user')->where('role_id',1)->first()->user_id;
            $data = [
                'receiver_id' => $admin_id,
                'title' => $title,
                'content' => $content,
            ];
        if (Notification::sendNotification($data)) {
            return $this->okResponse(null, __('msg.malfunction_complaint_sent_successfully'));
        }
    }

    public function getMyMalfunctions($type=false)//role:Doctor|Reception
    {
        $malfunctions=User::find(auth()->user()->id)->person->employee->malfunctions??false;
        if(!$malfunctions||empty($malfunctions->count()))return $this->okResponse(null,__('msg.you_dont_have_any_malfunction_complaints'));

        foreach ($malfunctions as $malfunction) {
            $data[]=[
                'title'=>$malfunction->title,
                'content'=>$malfunction->content,
                'statues'=>$malfunction->malfunction_statue_id,
                'hours_ago' => $malfunction->created_at->diffInHours(Carbon::now()),
            ];
        }

        //check if route for web to make pagination result
        if ($type == 'Paginate') {
            $data = $this->paginate($data);
        }

        return $this->okResponse($data);
    }

    public function getLastMalfunction()//role:Doctor|Reception
    {
        $malfunction=Malfunction::where('employee_id',User::find(auth()->user()->id)->person->employee->id)->latest()->first()??false;
        if(!$malfunction||empty($malfunction->count()))return $this->okResponse(null,__('msg.you_dont_have_any_malfunction_complaints'));
        $data=[
            'title'=>$malfunction->title,
            'content'=>$malfunction->content,
            'statues'=>$malfunction->malfunction_statue_id,
            'hours_ago' => $malfunction->created_at->diffInHours(Carbon::now())
        ];
        return $this->okResponse($data);
    }

    public function searchMyMalfunctions(Request $request,$type=false)
    {
        $malfunctions=Malfunction::where([['employee_id',User::find(auth()->user()->id)->person->employee->id],['title','LIKE','%'.$request->input.'%']])->get();
        if(!$malfunctions||empty($malfunctions->count()))return $this->okResponse(null,__('msg.input_not_found'));

        foreach ($malfunctions as $malfunction) {
            $data[]=[
                'title'=>$malfunction->title,
                'content'=>$malfunction->content,
                'statues'=>$malfunction->malfunction_statue_id,
                'hours_ago' => $malfunction->created_at->diffInHours(Carbon::now()),
            ];
        }

        //check if route for web to make pagination result
        if ($type == 'Paginate') {
            $data = $this->paginate($data);
        }

        return $this->okResponse($data);
    }
}
