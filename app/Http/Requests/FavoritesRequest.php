<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;

class FavoritesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'required|in:' . File::getTypesList(),
            'uuid' => 'required|uuid|exists:files,uuid',
        ];
    }
}
