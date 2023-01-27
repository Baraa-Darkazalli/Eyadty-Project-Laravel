<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateName;
use App\Models\CertificateRating;
use App\Models\CertificateSource;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Employee;
use App\Models\Person;
use App\Traits\ApiResponderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\returnSelf;
use function PHPUnit\Framework\returnValue;

class CertificateController extends Controller
{
    use ApiResponderTrait;

    public function getCertificatesNamesMenu()//Admin
    {
        $certificates_names=CertificateName::select('id','name')->get();
        if(empty($certificates_names->count()))return $this->okResponse(null,'There are no certificates names');
        return $this->okResponse($certificates_names);
    }

    public function getCertificatesRatingsMenu()//Admin
    {
        $certificates_ratings=CertificateRating::select('id','name')->get();
        if(empty($certificates_ratings->count()))return $this->okResponse(null,'There are no certificates ratings');
        return $this->okResponse($certificates_ratings);
    }

    public function getCertificatesSourcesMenu(Request $request)//Admin
    {
        $country_id=$request->country_id??false;
        if(!$country_id)return $this->badRequestResponse();
        $certificates_ratings=CertificateSource::where('country_id',$country_id)->select('id','name')->get();
        if(empty($certificates_ratings->count()))return $this->okResponse(null,'There are no certificates sourses for this country');
        return $this->okResponse($certificates_ratings);
    }

    public function addCertificateName(Request $request)//role:Admin
    {
        $data = $request->only(
            'name'
        );
        $rules = [
            'name' => 'required|unique:certificate_names',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        } else {
            $certificate_name = new CertificateName();
            $certificate_name->name = $request->name;
            $certificate_name->save();

            return $this->createdResponse(__('msg.certificate_name_added_successfully'));
        }
    }

    public function addCertificateSource(Request $request)//role:Admin
    {
        $data = $request->only(
            'source_name',
            'country_id'
        );
        $rules = [
            'source_name' => 'required|unique:certificate_sources',
            'country_id' => 'required',
        ];
        $Country=Country::find($request->country_id)??false;
        if(!$Country)return $this->badRequestResponse(__('msg.this_is_failed_id'));
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        } else {
            $certificate_source = new CertificateSource();
            $certificate_source->source_name = $request->source_name;
            $certificate_source->country_id = $request->country_id;
            $certificate_source->save();

            return $this->createdResponse($certificate_source, __('msg.certificate_source_added_successfully'));
        }
    }
    public static function addCertificate($inputs)//role:Admin
    {
        $rules=[
            'employee_id'=>'required|exists:employees,id',
            'name_id'=>'required|exists:certificate_names,id',
            'source_id'=>'required|exists:certificate_sources,id',
            'rating_id'=>'required|exists:certificate_ratings,id',
        ];
        $validator=Validator::make($inputs,$rules);
        if($validator->fails())return false;

        $certificate = new Certificate();
        $certificate->certificate_date = $inputs['date']??null;
        $certificate->employee_id = $inputs['employee_id'];
        $certificate->certificate_name_id = $inputs['name_id'];
        $certificate->certificate_source_id = $inputs['source_id'];
        $certificate->certificate_rating_id = $inputs['rating_id'];
        $certificate->save();

        return true;
    }
    public function getCertificates(Request $request)//role:Admin|Patient|Doctor
    {
        //check if he want his working time
        if(auth()->user()->hasRole('Reception')||auth()->user()->hasRole('Doctor')){
            if(!isset($request->id))
                $employee=auth()->user()->person->employee??false;
            else{
                $person=Person::find($request->id);
                $employee=($person->employee)??(false);
            }
        }
        else{
            if(!isset($request->id))return $this->badRequestResponse();
            $person=Person::find($request->id);
            $employee=($person->employee)??(false);
        }

        //check if id exists
        if (! $employee) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }

        //check if no certificates
        if (empty($employee->certificates->count())) {
            return $this->okResponse(null, __('msg.there_are_no_certificates'));
        }

        //get all certificates
        foreach ($employee->certificates as $certificate) {
            $data[] = [
                'certificate_name' => $certificate->certificate_name->name,
                'certificate_source' => $certificate->certificate_source->source_name,
                'country' => $certificate->certificate_source->country->name,
                'certificate_date' => $certificate->certificate_date,
                'certificate_rating' => $certificate->certificate_ratings->name,
            ];
        }

        return $this->okResponse($data);
    }
}
