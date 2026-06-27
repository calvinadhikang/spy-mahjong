<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateXpRewardSettingsRequest extends FormRequest
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
        $xpRule = ['required', 'integer', 'min:-9999', 'max:9999'];

        return [
            'first_place_xp' => $xpRule,
            'second_place_xp' => $xpRule,
            'third_place_xp' => $xpRule,
            'fourth_place_xp' => $xpRule,
            'loss_xp' => $xpRule,
        ];
    }
}
