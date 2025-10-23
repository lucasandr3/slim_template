<?php

declare(strict_types=1);

namespace App\Http\Requests;

class RegisterRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|max:255',
        ];
    }

    protected function fieldNames(): array
    {
        return [
            'name' => 'Nome',
            'email' => 'Email',
            'password' => 'Senha',
        ];
    }
}
