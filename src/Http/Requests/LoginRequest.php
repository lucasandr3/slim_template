<?php

declare(strict_types=1);

namespace App\Http\Requests;

class LoginRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    protected function fieldNames(): array
    {
        return [
            'email' => 'Email',
            'password' => 'Senha',
        ];
    }
}
