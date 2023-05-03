<?php

namespace App\Http\Requests\Workspace\Board\Layout;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'board_id' => [
                'required',
                'integer'
            ],
            'title' => [
                'required',
                'min:3',
                'max:128'
            ],
            'layout' => [
                'required',
                'array'
            ]
        ];
    }
}
