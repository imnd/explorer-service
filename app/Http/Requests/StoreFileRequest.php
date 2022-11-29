<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'required|in:' . File::getTypesList(),
            'parent_uuid' => 'required|uuid|exists:files,uuid',
            'file' => 'required|file',
            'name' => 'required|string',
        ];
    }
}
