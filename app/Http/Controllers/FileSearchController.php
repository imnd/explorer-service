<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileSearchRequest;
use App\Http\Resources\FileResource;
use App\Models\File;

class FileSearchController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(File::class);
    }

    public function index(FileSearchRequest $request)
    {
        $items = File::search($request->text);
        if ($request->get('type')) {
            $items->where('type', $request->get('type'));
        }
        return FileResource::collection($items->paginate());
    }
}
