<?php

namespace App\Http\Controllers;

use App\Contracts\FileContract,
    App\Filters\Favorites,
    App\Filters\FiltersFileParentUuid,
    App\Filters\FiltersOnlyRoot,
    App\Http\Requests\DeleteFileRequest,
    App\Http\Requests\DownloadFileRequest,
    App\Http\Requests\IndexFileRequest,
    App\Http\Requests\RenameFileRequest,
    App\Http\Requests\StoreFileRequest,
    App\Http\Resources\FileResource,
    App\Models\File,
    Symfony\Component\HttpFoundation\StreamedResponse,
    Spatie\QueryBuilder\QueryBuilder,
    Spatie\QueryBuilder\Filter;

class FileController extends Controller
{
    public function index(IndexFileRequest $indexFileRequest)
    {
        $files = QueryBuilder::for(File::class)
            ->allowedFilters(
                Filter::exact('type'),
                Filter::exact('name'),
                Filter::exact('uuid'),
                Filter::custom('parent_uuid', FiltersFileParentUuid::class),
                Filter::custom('only_root', FiltersOnlyRoot::class),
                Filter::custom('favorites', Favorites::class)
            )
            ->whereUserId($this->userId)
            ->get();

        return FileResource::collection($files);
    }

    public function show(DownloadFileRequest $downloadFileRequest, FileContract $fileContract, $uuid)
    {
        $file = $fileContract->download($this->userId, $uuid);
        if ($file instanceof StreamedResponse) {
            return $file;
        }
        return response()->json(['error' => $file], 404);
    }

    public function store(StoreFileRequest $storeFileRequest, FileContract $fileContract)
    {
        $file = $fileContract->store(array_merge($storeFileRequest->all(), ['user_id' => $this->userId]));
        if ($file instanceof File) {
            return new FileResource($file);
        }
        return response()->json(['error' => $file], 404);
    }

    public function update(RenameFileRequest $renameFileRequest, FileContract $fileContract)
    {
        return response()->json(['error' => 'not implemented yet'], 404);
    }

    public function destroy(DeleteFileRequest $deleteFileRequest, FileContract $fileContract)
    {
        if ($fileContract->delete(array_merge($deleteFileRequest->all(), ['user_id' => $this->userId]))) {
            return response()->json(['success' => 'success'], 200);
        }
        return response()->json(['error' => 'invalid'], 404);
    }
}
