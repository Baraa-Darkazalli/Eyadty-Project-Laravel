<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class ConflictTimesRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $times)
    {
        for ($i = 0; $i < count($times); $i++) {
            for ($j = $i + 1; $j < count($times); $j++) {
                $start1 = Carbon::parse($times[$i]['start'] ?? null);
                $start2 = Carbon::parse($times[$j]['start'] ?? null);
                $end1 = Carbon::parse($times[$i]['end'] ?? null);
                $end2 = Carbon::parse($times[$j]['end'] ?? null);
                if ($start2->between($start1, $end1,false)) {
                    return false;
                }
                if ($start1->between($start2, $end2, false)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('msg.there_is_conflict_in_start_and_end_times');
    }
}
