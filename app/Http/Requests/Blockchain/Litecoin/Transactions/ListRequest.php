<?php

namespace App\Http\Requests\Blockchain\Litecoin\Transactions;

use App\Models\Workspace\Label;
use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
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
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'order' => 'string|in:asc,desc',
            'order_by' => 'string|in:block_number,hash',
            'block_number' => 'integer',
            'address' => 'string|max:64',
            'hash' => 'string|max:64'
        ];
    }
}
