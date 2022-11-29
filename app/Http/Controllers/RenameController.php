<?php

namespace App\Http\Controllers;

use App\Contracts\FileContract,
    App\Http\Requests\RenameFileRequest,
    App\Http\Resources\FileResource,
    App\Models\File;

class RenameController extends Controller
{
    public function update(RenameFileRequest $request, FileContract $fileContract)
    {
        $result = $fileContract->rename(array_merge($request->all(), ['user_id' => $this->userId]));
        if ($result instanceof File) {
            return response()->json(['success' => 'success'], 204);
        }
        return response()->json(['error' => $result], 404);
    }
}
