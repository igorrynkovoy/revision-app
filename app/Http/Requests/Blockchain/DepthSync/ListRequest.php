<?php

namespace App\Http\Requests\Blockchain\DepthSync;

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
            'order' => 'string|in:asc,desc',
            'order_by' => 'string|in:id,address,child_addresses,current_depth,processed,processed_at',
            'address' => 'string|max:128',
            'direction' => 'string|in:both,sender,recipient'
        ];
    }
}
