<?php

namespace App\Http\Requests\GameSessions;

use Illuminate\Foundation\Http\FormRequest;

class SubmitPlayerMoneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'total_money' => ['required', 'numeric'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'total_money.required' => 'Please enter a total amount.',
            'total_money.numeric' => 'Total amount must be a number.',
        ];
    }
}
