<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Traits\ApiResponderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    use ApiResponderTrait;

    public function addCountry(Request $request)//role:Admin
    {
        $data = $request->only(
            'name'
        );
        $rules = [
            'name' => 'required|unique:countries',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        } else {
            $country = new Country();
            $country->name = $request->name;
            $country->save();

            return $this->createdResponse(__('msg.country_added_successfully'));
        }
    }
    public function getCountriesMenu()//Admin
    {
        $countries=Country::select('id','name')->get();
        if(empty($countries->count()))return $this->okResponse(null,'There are no certificates sourses for this country');
        return $this->okResponse($countries);
    }
}
