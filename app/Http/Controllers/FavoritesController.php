<?php

namespace App\Http\Controllers;

use App\Contracts\FileContract,
    App\Http\Requests\FavoritesRequest,
    App\Http\Resources\FileResource,
    App\Models\File;

class FavoritesController extends Controller
{
    public function update(FavoritesRequest $request, FileContract $fileContract)
    {
        $result = $fileContract->addToFavorites(array_merge($request->all(), ['user_id' => $this->userId]));
        if ($result instanceof File) {
            return response()->json(['success' => 'success'], 204);
        }
        return response()->json(['error' => $result], 404);
    }
}
