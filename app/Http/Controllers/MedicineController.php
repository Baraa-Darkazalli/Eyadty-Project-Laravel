<?php

namespace App\Http\Controllers;

use App\Models\MedicalName;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Traits\ApiResponderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicineController extends Controller
{
    use ApiResponderTrait;

    public function AddMedicineName(Request $request)//role:Doctor
    {
        $validator = Validator::make($request->only('name'), ['name' => 'required|unique:medical_names,name']);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }

        $medicine_name = new MedicalName();
        $medicine_name->name = $request->name;
        $medicine_name->save();

        return $this->okResponse(null, __('msg.medicine_name_created_succussfully'));
    }

    public function getAllNames()//role:Doctor
    {
        if (empty(MedicalName::count())) {
            return $this->okResponse(null, __('msg.there_ara_no_medicines_names'));
        }

        return $this->okResponse(MedicalName::all('id', 'name'));
    }

    public function DeleteMedicineName(Request $request)//role:Admin
    {
        $validator = Validator::make($request->only('id'), ['id' => 'required']);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }

        $medicine_name = (MedicalName::find($request->id)) ?? false;
        if (! $medicine_name) {
            return $this->badRequestResponse();
        }

        $medicine_name->delete();

        return $this->okResponse(null);
    }

    public static function AddMedicine($prescription_id, $medicines)
    {
        if (empty($medicines)) {
            return false;
        }

        $prescription = Prescription::find($prescription_id) ?? false;
        //check id
        if (! $prescription) {
            return false;
        }

        foreach ($medicines as $medicine) {
            if (! isset($medicine['medical_name_id'])) {
                return false;
            }
            if (! MedicalName::find($medicine['medical_name_id'])) {
                return false;
            }
            $med = new Medicine();
            $med->prescription_id = $prescription->id;
            $med->medical_name_id = $medicine['medical_name_id'];
            $med->num_of_pieces = ($medicine['num_of_cans']) ?? null;
            $med->num_of_doses = ($medicine['num_of_doses']) ?? null;
            $med->dose_description = ($medicine['dose_description']) ?? null;
            $med->save();
        }

        return true;
    }
}
