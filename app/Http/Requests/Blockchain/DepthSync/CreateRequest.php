<?php

namespace App\Http\Requests\Blockchain\DepthSync;

use App\Models\Blockchain\DepthSync;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'address' => 'required',
            'blockchain' => 'required|in:LTC',
            'max_depth' => [
                'required',
                'integer',
                'min:1',
                'max:16'
            ],
            'direction' => [
                'required',
                Rule::in(DepthSync::getDirectionsList())
            ],
            'limit_addresses' => 'required|integer|min:1|max:50',
            'limit_transactions' => 'required|integer|min:1|max:128',
        ];
    }
}
