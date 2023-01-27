<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CustomRule implements Rule
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
    public function passes($attribute, $value)
    {
        $success = true;
        foreach ($value as $item) {
            foreach ($item as $day) {
                if (! in_array($day, ['su', 'mo', 'tu', 'th', 'we', 'fr', 'sa'])) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('msg.error_in_days');
    }
}
