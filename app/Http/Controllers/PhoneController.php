<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Phone;
use Illuminate\Http\Request;
use App\Traits\ApiResponderTrait;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\returnSelf;

class PhoneController extends Controller
{
    use ApiResponderTrait;
    public static function addPhone($input,$person_id)
    {
        $rule=[
            'phone'=>'min:10'
        ];
        $vaildator = Validator::make([$input], $rule);
        $rule1=[
            'person_id'=>'required|min:10'
        ];
        $vaildator1 = Validator::make([$person_id], $rule1);
        if ($vaildator->fails())
            return [
                'success' => 0,
                'message' => $vaildator->errors()->all(),
            ];

        $phone=new Phone();
        $phone->phone_number=$input['phone'];
        $phone->person_id=$person_id;
        $phone->save();
        return [
            'success' => 1,
            'message' => 'ok',
        ];
    }
    public function deletePhone(Request $request)//Role:Doctor|Reception|Admin
    {
        $data=$request->only('phone_id');
        $rule=[
            'phone_id'=>'required'
        ];
        $vaildator = Validator::make($data, $rule);
        if ($vaildator->fails())
            return $vaildator->errors()->all();

        $phone=Phone::find($request->phone_id)??false;

        if(!$phone)return $this->badRequestResponse(__('msg.this_is_failed_id'));

        $phone->delete();

        return $this->okResponse(null,__('msg.phone_deleted_successfully'));
    }
}
