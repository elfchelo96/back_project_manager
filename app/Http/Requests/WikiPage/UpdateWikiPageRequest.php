<?php

namespace App\Http\Requests\WikiPage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWikiPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('wikiPage'));
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:200'],
            'content' => ['nullable', 'string'],
        ];
    }
}
