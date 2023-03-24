<?php

namespace App\Http\Requests\Workspace\Label;

use App\Models\Workspace\Label;
use Illuminate\Foundation\Http\FormRequest;

class ImportCSVRequest extends FormRequest
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
            'recreate_duplicates' => '', // TODO: Should be boolean
            'csv' => 'required|file'
        ];
    }
}
