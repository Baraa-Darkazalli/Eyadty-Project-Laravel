<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DaysRule implements Rule
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
    public function passes($attribute, $day)
    {
            if (! in_array($day, ['sunday', 'monday', 'thursday', 'tuesday', 'wednesday', 'friday', 'saturday'])) {
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
        return __('msg.error_in_days_inputs');
    }
}
