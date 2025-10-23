<?php

declare(strict_types=1);

namespace App\Http\Requests;

class ResetPasswordRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'token' => 'required|min:64|max:64',
            'password' => 'required|min:6|max:255',
            'password_confirmation' => 'required|same:password',
        ];
    }

    protected function fieldNames(): array
    {
        return [
            'token' => 'Token',
            'password' => 'Senha',
            'password_confirmation' => 'Confirmação de Senha',
        ];
    }
}
