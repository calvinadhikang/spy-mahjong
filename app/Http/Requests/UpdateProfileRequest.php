<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge([
                'username' => strtolower($this->string('username')->value()),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($this->user()),
            ],
            'current_password' => ['required_with:password', 'string'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Please choose a username.',
            'username.regex' => 'Username may only contain lowercase letters, numbers, and underscores.',
            'username.unique' => 'That username is already taken.',
            'current_password.required_with' => 'Please enter your current password to set a new one.',
            'password.confirmed' => 'New passwords do not match.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->filled('password')) {
                return;
            }

            $user = $this->user();
            $identity = $user?->passwordIdentity();

            if (! $identity) {
                $validator->errors()->add('password', 'Password cannot be changed for this account.');

                return;
            }

            if (! Hash::check($this->string('current_password')->value(), $identity->secret)) {
                $validator->errors()->add('current_password', 'Current password is incorrect.');
            }
        });
    }
}
