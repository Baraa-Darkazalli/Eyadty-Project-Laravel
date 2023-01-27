<?php

namespace App\Http\Controllers;

use App\Models\ExtraTreatments;
use App\Models\SessionCalculation;

class TreatmentsController extends Controller
{
    public static function AddExtraTreatment($session_calc_id, $extra_treatments)
    {
        if (empty($extra_treatments)) {
            return false;
        }

        $session_calc = SessionCalculation::find($session_calc_id) ?? false;
        //check id
        if (! $session_calc) {
            return false;
        }

        foreach ($extra_treatments as $extra_treatmente) {
            if (! isset($extra_treatmente['title'])) {
                return false;
            }
            if (! isset($extra_treatmente['treatment_price'])) {
                return false;
            }

            $extra = new ExtraTreatments();
            $extra->session_calculation_id = $session_calc_id;
            $extra->treatment_name = $extra_treatmente['title'];
            $extra->treatment_price = $extra_treatmente['treatment_price'];
            $extra->item_price = ($extra_treatmente['item_price']) ?? null;
            $extra->description = ($extra_treatmente['description']) ?? null;
            $extra->save();
        }

        return true;
    }
}
