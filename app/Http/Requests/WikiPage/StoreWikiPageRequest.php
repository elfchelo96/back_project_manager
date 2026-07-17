<?php

namespace App\Http\Requests\WikiPage;

use Illuminate\Foundation\Http\FormRequest;

class StoreWikiPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('wiki.manage');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'content' => ['nullable', 'string'],
        ];
    }
}
