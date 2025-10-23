<?php

declare(strict_types=1);

namespace App\Http\Requests;

class VerifyEmailRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'token' => 'required|min:64|max:64',
            'type' => 'max:50',
        ];
    }

    protected function fieldNames(): array
    {
        return [
            'email' => 'Email',
            'token' => 'Token',
            'type' => 'Tipo',
        ];
    }
}
