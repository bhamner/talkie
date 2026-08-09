<?php

namespace App\Http\Requests;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePhraseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'text' => ['required', 'string', 'max:255'],
            'menu_id' => [
                'nullable',
                'integer',
                Rule::exists('menus', 'id')->where('user_id', $userId),
            ],
        ];
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
