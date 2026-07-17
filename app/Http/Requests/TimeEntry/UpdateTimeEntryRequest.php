<?php

namespace App\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hours' => ['sometimes', 'numeric', 'min:0.01', 'max:24'],
            'comments' => ['nullable', 'string'],
            'spent_on' => ['sometimes', 'date'],
        ];
    }
}
