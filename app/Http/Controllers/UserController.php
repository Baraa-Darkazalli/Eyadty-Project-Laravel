<?php

namespace App\Http\Controllers;

use App\Mail\ForgetPassword;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Person;
use App\Models\Phone;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponderTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use function PHPUnit\Framework\returnSelf;

class UserController extends Controller
{
    use ApiResponderTrait;

    public function forgetPassword(Request $request)
    {
        $email = $request->only('email');
        $validator = Validator::make($email, ['email' => 'required|email|exists:users,email']);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }
        $token = Str::random(8);
        DB::table('password_resets')->insert([
            'email' => $request->email, 'token' => $token, 'created_at' => Carbon::now(),
        ]);
        $details =
        [
            'title' => 'Reset Password Request',
            'body' => "hello dear i'm testing reset password Your code is:{$token}",
            'footer' => 'By Eyadti',
        ];
        Mail::to($request->email)->send(new ForgetPassword($details));

        return $this->okResponse(null,__('msg.reset_code_sented_to_your_mail_successfully'));
    }

    public function checkResetPasswordToken(Request $request)
    {
        $data=$request->only('email','token');
        $rules=[
            'email'=>'required',
            'token'=>'required'
        ];
        $valdiator=Validator::make($data,$rules);
        if($valdiator->fails())
        {
            return $valdiator->errors()->all();
        }
        $updatePassword = DB::table('password_resets')
        ->where(['email' => $request->email, 'token' => $request->token])
        ->first();
        if (! $updatePassword) {
            return $this->badRequestResponse('invalid code');
        }
        return $this->okResponse(null);
    }

    public function checkResetCode(Request $request)
    {
        $data = $request->only('email','token');
        $rules =
        [
            'email' => 'required|email|exists:users,email',
            'token'=>'required'
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }
        $updatePassword = DB::table('password_resets')
        ->where(['email' => $request->email, 'token' => $request->token])
        ->first();
        if (! $updatePassword) {
            return $this->badRequestResponse(__('msg.invalid_code'));
        }
        return $this->okResponse(null);
    }
    public function resetPassword(Request $request)
    {
        $data = $request->only('email', 'password', 'password_confirmation','token');
        $rules =
        [
            'email' => 'required|email|exists:users',
            'password' => 'required|confirmed',
            'token'=>'required'
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        User::where('email', $request->email)->update(['password' => bcrypt($request->password)]);
        DB::table('password_resets')->where(['email' => $request->email])->delete();

        return $this->okResponse(null,__('msg.password_reset_successfully'));
    }

    public function login(Request $request)
    {
        $login_data = $request->only('username', 'password');
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];
        $validator = Validator::make($login_data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        } else {
            if (filter_var($request->username, FILTER_VALIDATE_EMAIL))
            {
                unset($login_data['username']);
                $login_data['email']=$request->username;
            }
            if (! Auth::attempt($login_data)) {
                return $this->unauthorizedResponse(__('msg.invalid_data'));
            } else {
                $token = auth()->user()->createToken('authToken')->accessToken;
                $data = [];
                $data['accessToken'] = $token;
                $roles = auth()->user()->roles;
                $roles_names = [];
                foreach ($roles as $role) {
                    array_push($roles_names, $role['name']);
                }
                $data['roles'] = $roles_names;
                $data['name']=auth()->user()->person->name->first_name.' '.auth()->user()->person->name->last_name;
                $data['username']=auth()->user()->username;
                $data['lang']=auth()->user()->current_lang;
                \App\Models\Log::log('login', 'login opertation', auth()->user()->id);

                return $this->success($data, __('msg.logged_in_successfully'));
            }
        }
    }

    public function createPatientAccount(Request $request)//role: Admin|Reception
    {
        $data=$request->only(
            'patient_id',
            'email',
            'username',
            'password',
            'password_confirmation',
        );
        $rules=[
            'patient_id'=>'required|exists:people,id|unique:users,person_id',
            'email' => 'max:30|unique:users|email',
            'username' => 'required|max:15|unique:users',
            'password' => 'required|min:8|confirmed',
        ];
        $vaildator1 = Validator::make($data, $rules);
        if ($vaildator1->fails())return $this->badRequestResponse($vaildator1->errors()->all());

        $patient=Person::find($request->patient_id)->patient??false;
        if(!$patient)return $this->badRequestResponse(__('msg.this_is_failed_id'));

        $user = new User();
        $user->person_id = $request->patient_id;
        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->email = $request->email??null;
        $user->save();
        $user->attachRole('Patient');

        return $this->createdResponse(null,__('msg.account_created_successfully'));
    }
    public function changeLanguage(Request $request)//role:all
    {
        $user = auth()->user();

        if (! in_array($request->lang, ['en', 'ar'])) {
            return $this->badRequestResponse(__('msg.this_language_is_not_available'));
        } else {
            //default langauge (Eglish)
            app()->setLocale('en');
            $user->current_lang = app()->getLocale();
            $user->save();

            //set arabic langauge
            if (isset($request->lang) && $request->lang == 'ar') {
                app()->setLocale('ar');
                $user->current_lang = app()->getLocale();
                $user->save();
            }


            return $this->okResponse(null, __('msg.language_changed_successfully'));
        }
    }

    public function generalProfile(Request $request)//General info , role:all
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);

        if (empty($user->person->phones->count())) {
            $phone_numbers = null;
        }
        foreach ($user->person->phones as $phone) {
            $phone_numbers[]=[
                'phone_id'=>$phone->id,
                'phone_number'=> $phone->phone_number
            ];
        }
        $data = [
            'email' => $user->email,
            'gender' => $user->person->gender,
            'age' =>(Carbon::parse($user->person->birth_date)->diff(Carbon::now())->y)??null,
            'birth_date' => $user->person->birth_date,
            'first_name' => $user->person->name->first_name,
            'father_name' => $user->person->name->father_name,
            'last_name' => $user->person->name->last_name,
            'phones' => $phone_numbers,
        ];

        return $this->okResponse($data);
    }

    public function patientProfile()//role:Patient
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);

        $patient = ($user->person->patient) ?? (false);

        //check if id exists
        if (! $patient) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }

        //patient allergies
        if (empty($patient->allergies->count())) {
            $patient_allergies = null;
        } else {
            foreach ($patient->allergies as $allergy) {
                $patient_allergies[] = $allergy->name;
            }
        }

        //patient diseases
        if (empty($patient->diseases->count())) {
            $patient_diseases = null;
        } else {
            foreach ($patient->diseases as $disease) {
                $patient_diseases[] = $disease->name;
            }
        }
        $data = [
            'height' => $patient->height,
            'weight' => $patient->weight,
            'blood_group' => $patient->blood_types->name,
            'allergies' => $patient_allergies,
            'diseases' => $patient_diseases,

        ];

        return $this->okResponse($data);
    }

    public function editProfilePatient(Request $request)//role:Patient
    {
        $user = User::find(auth()->user()->id);
        $patient = ($user->person->patient) ?? (false);

        //check if id exists
        if (! $patient) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }

        $data = $request->only([
            'email',
            'phone_number',
        ]);
        $rules = [
            'email' => 'email|unique:users',
            'phone_number'=>'string|min:10'
        ];
        $vaildator = Validator::make($data, $rules);
        if ($vaildator->fails()) {
            return $vaildator->errors()->all();
        }

        $patient->person->user->email=$request->email??null;
        $no_phone=empty($patient->person->phones->count())?true:false;
        if($no_phone){
            $phone=new Phone();
            $phone->phone_number=$request->phone_number;
            $phone->person_id=$patient->person->id;
            $phone->save();
        }else{
            $patient->person->phones->first()->phone_number=($request->phone_number)??$patient->person->phones->first()->phone_number;
            $patient->person->phones->first()->save();
        }

        $patient->person->user->save();

        return $this->okResponse(null,__('msg.profile_edited_successfully'));
    }

    public function editProfileEmployee(Request $request)//role:Reception|Doctor
    {
        $user=User::find(auth()->user()->id);
        $employee=($user->person->employee)??(false);

        //check if id exists
        if(!$employee)
            return $this->notFoundResponse();

        $data=$request->only([
            'email',
            'phones'
        ]);
        $rules=[
            'email'=>'email',
            'phones.*'=>'string|min:10'
        ];
        $vaildator = Validator::make($data, $rules);
        if ($vaildator->fails())
            return $vaildator->errors()->all();

        foreach ($request->phones as $phone) {
            $phone_number=new Phone();
            $phone_number->phone_number=$phone;
            $phone_number->person_id=$employee->person->id;
            $phone_number->save();
        }
        $employee->person->user->email=$request->email??null;
        $employee->person->user->save();

        return $this->okResponse(null,__('msg.profile_edited_successfully'));
    }

    public function doctorProfile()//role:Doctor
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);

        $employee = ($user->person->employee) ?? (false);

        //check if id exists
        if (! $employee) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }

        $data=[
            'previous_experience'=>$employee->previous_experience,
            'session_duration'=>$employee->doctor->session_duration->session_duration,
            'salary_rate'=>$employee->doctor->salary_rate,
            'salary'=>$employee->salary,
            'clinic_name'=>$employee->doctor->clinic->clinic_name->name,
        ];

        return $this->okResponse($data);
    }

    public function receptionProfile()//role:Reception
    {
        $user_id=auth()->user()->id;
        $user=User::find($user_id);

        $employee=($user->person->employee)??(false);

        //check if id exists
        if(!$employee)
            return $this->badRequestResponse(__('msg.this_is_failed_id'));

        $data=[
            'previous_experience'=>$employee->previous_experience,
            'salary'=>$employee->salary,
        ];

        return $this->okResponse($data);
    }

    public function SendReport(Request $request)//role:Patient
    {
        $data = $request->only('content');
        $rules = [
            'content' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse(__('msg.the_content_field_is_required'));
        } else {
            //create report
            $report = new Report();
            $report->description = $request->content;
            $report->user_id = auth()->user()->id;
            $report->save();

            //send notification to admin
            $title = __('msg.patient').User::find(auth()->user()->id)->person->name->first_name.__('msg.sent_a_complaint');
            $content = $request->content;
            $admin_id=DB::table('role_user')->where('role_id',1)->first()->user_id;
            $data = [
                'receiver_id' => $admin_id,
                'title' => $title,
                'content' => $content,
            ];
            if (Notification::sendNotification($data)) {
                return $this->okResponse(null, __('msg.complaint_sent_successfully'));
            }
        }
    }

    public function logout()//role:all
    {
        $user = auth()->user()->token();
        $user->revoke();

        return $this->okResponse(null, __('msg.loged_out_successfully'));
    }
}
