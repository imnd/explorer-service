<?php

namespace App\Http\Controllers;

use App\Contracts\FileContract,
    App\Http\Requests\MoveFileRequest,
    App\Http\Resources\FileResource,
    App\Models\File;

class MoveController extends Controller
{
    public function update(MoveFileRequest $request, FileContract $fileContract)
    {
        $result = $fileContract->move(array_merge($request->all(), ['user_id' => $this->userId]));
        if ($result instanceof File) {
            return response()->json(['success' => 'success'], 204);
        }
        return response()->json(['error' => $result], 404);
    }
}
