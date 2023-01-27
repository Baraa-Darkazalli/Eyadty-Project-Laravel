<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class HalfHourMultiplesRule implements Rule
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
        foreach ($times as $time) {
            $start = Carbon::parse($time['start'] ?? null);
            $end = Carbon::parse($time['end'] ?? null);
            if (! ($start->minute == 0 || $start->minute == 30) || ! ($end->minute == 0 || $end->minute == 30)) {
                return false;
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
        return 'time should be multiple of half hour';
    }
}
