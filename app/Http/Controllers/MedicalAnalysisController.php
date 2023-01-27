<?php

namespace App\Http\Controllers;

use App\Models\MedicalAnalysis;
use App\Models\MedicalAnalysisName;
use App\Models\Prescription;
use App\Traits\ApiResponderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicalAnalysisController extends Controller
{
    use ApiResponderTrait;

    public function AddAnalysisName(Request $request)//role:Doctor
    {
        $validator = Validator::make($request->only('name'), ['name' => 'required|unique:medical_analysis_names,name']);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }

        $analysis_name = new MedicalAnalysisName();
        $analysis_name->name = $request->name;
        $analysis_name->save();

        return $this->okResponse(null, __('msg.analysis_name_created_succussfully'));
    }

    public function getAllNames()//role:Doctor
    {
        if (empty(MedicalAnalysisName::count())) {
            return $this->okResponse(null, __('msg.there_ara_no_medical_analysis_names'));
        }

        return $this->okResponse(MedicalAnalysisName::all('id', 'name'));
    }

    public function DeleteAnalysisName(Request $request)//role:Admin
    {
        $validator = Validator::make($request->only('id'), ['id' => 'required']);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }

        $analysis_name = (MedicalAnalysisName::find($request->id)) ?? false;
        if (! $analysis_name) {
            return $this->badRequestResponse();
        }

        $analysis_name->delete();

        return $this->okResponse(null);
    }

    public static function AddMedicalAnalysis($prescription_id, $medical_analysis)
    {
        if (empty($medical_analysis)) {
            return false;
        }

        $prescription = Prescription::find($prescription_id) ?? false;
        //check id
        if (! $prescription) {
            return false;
        }

        foreach ($medical_analysis as $analysis) {
            if (! isset($analysis['medical_analysis_name_id'])) {
                return false;
            }
            if (! MedicalAnalysisName::find($analysis['medical_analysis_name_id'])) {
                return false;
            }
            $med = new MedicalAnalysis();
            $med->prescription_id = $prescription_id;
            $med->medical_analysis_name_id = $analysis['medical_analysis_name_id'];
            $med->description = ($analysis['description']) ?? null;
            $med->save();
        }

        return true;
    }
}
