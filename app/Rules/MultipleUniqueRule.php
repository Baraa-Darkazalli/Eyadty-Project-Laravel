<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class MultipleUniqueRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $table;

    protected $message;

    public function __construct($table, $message)
    {
        $this->table = $table;
        $this->message = $message;
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
        if (DB::table($this->table)->where($value)->exists()) {
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
        return $this->message;
    }
}
