<?php

namespace App\Http\Requests;

use App\Models\Menu;
use App\Models\Word;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
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
        $userId = $this->user()->id;

        return [
            'label' => ['required', 'string', 'max:100'],
            'speak_text' => ['nullable', 'string', 'max:255'],
            'menu_id' => [
                'nullable',
                'integer',
                Rule::exists('menus', 'id')->where('user_id', $userId),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $label = strtolower(trim((string) $this->input('label')));

            $exists = Word::query()
                ->forUser($this->user())
                ->whereRaw('LOWER(label) = ?', [$label])
                ->exists();

            if ($exists) {
                $validator->errors()->add('label', 'That word already exists somewhere on your board.');
            }
        });
    }

    public function menu(): ?Menu
    {
        $menuId = $this->validated('menu_id');

        if ($menuId === null) {
            return null;
        }

        return Menu::query()
            ->forUser($this->user())
            ->whereKey($menuId)
            ->firstOrFail();
    }
}
