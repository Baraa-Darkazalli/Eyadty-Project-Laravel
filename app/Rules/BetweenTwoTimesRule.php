<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class BetweenTwoTimesRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $duration;

    protected $times;

    public function __construct($duration, $times)
    {
        $this->duration = $duration;
        $this->times = $times;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $value = Carbon::parse($value);
        foreach ($this->times as $time) {
            $start = Carbon::parse($time['start']);
            $end = Carbon::parse($time['end']);
            $end->subMinute($this->duration);
            if ($value->gte($start) && $value->lte($end)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __("msg.you_entered_invalid_time_it_is_outside_time_working");
    }
}
