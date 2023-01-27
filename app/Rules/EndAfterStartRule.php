<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class EndAfterStartRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
            if (! $end->gt($start)) {
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
        return __('msg.end_time_should_be_after_start_time');
    }
}
