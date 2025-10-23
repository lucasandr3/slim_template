<?php

declare(strict_types=1);

namespace App\Http\Requests;

class ForgotPasswordRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }

    protected function fieldNames(): array
    {
        return [
            'email' => 'Email',
        ];
    }
}
