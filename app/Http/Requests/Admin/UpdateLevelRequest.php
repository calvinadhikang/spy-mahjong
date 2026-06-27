<?php

namespace App\Http\Requests\Admin;

use App\Models\Level;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Level $level */
        $level = $this->route('level');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('levels', 'name')->ignore($level->id),
            ],
            'min_xp' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
                Rule::unique('levels', 'min_xp')->ignore($level->id),
            ],
        ];
    }
}
