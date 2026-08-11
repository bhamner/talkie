<?php

namespace App\Http\Requests;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('menus', 'id')->where('user_id', $userId),
            ],
        ];
    }

    public function parent(): ?Menu
    {
        $parentId = $this->validated('parent_id');

        if ($parentId === null) {
            return null;
        }

        return Menu::query()
            ->forUser($this->user())
            ->whereKey($parentId)
            ->firstOrFail();
    }
}
