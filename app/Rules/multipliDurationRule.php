<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class multipliDurationRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $duration;

    public function __construct($duration)
    {
        $this->duration = $duration;
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
        $time = Carbon::parse($value);
        $duration = Carbon::parse($this->duration);
        if (! ($time->minute % $duration->minute == 0)) {
            return false;
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
        return "you must enter time multiple of {$this->duration} ";
    }
}
