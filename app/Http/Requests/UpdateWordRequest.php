<?php

namespace App\Http\Requests;

use App\Models\Word;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateWordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $word = $this->route('word');

        return $this->user() !== null
            && $word instanceof Word
            && $word->user_id === $this->user()->id;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('speak_text') === '') {
            $this->merge(['speak_text' => null]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100'],
            'speak_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $word = $this->route('word');
            $label = strtolower(trim((string) $this->input('label')));

            $exists = Word::query()
                ->forUser($this->user())
                ->whereRaw('LOWER(label) = ?', [$label])
                ->when($word instanceof Word, fn ($query) => $query->whereKeyNot($word->id))
                ->exists();

            if ($exists) {
                $validator->errors()->add('label', 'That word already exists somewhere on your board.');
            }
        });
    }
}
