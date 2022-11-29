<?php

namespace App\Http\Controllers;

use App\Contracts\FileContract,
    App\Http\Requests\CopyFileRequest,
    App\Http\Resources\FileResource,
    App\Models\File;

class CopyController extends Controller
{
    public function update(CopyFileRequest $request, FileContract $fileContract)
    {
        $result = $fileContract->copy(array_merge($request->all(), [
            'user_id' => $this->userId,
        ]));
        if ($result instanceof File) {
            return new FileResource($result);
        }
        return response()->json(['error' => $result], 404);
    }
}
