<?php

namespace App\Http\Resources;

use App\Models\File;
use App\Services\FileHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var FileHelper $fileHelper */
        $fileHelper = resolve(FileHelper::class);

        return [
            'uuid'          => $this->uuid,
            'type'          => $this->type,
            'name'          => $fileHelper->getFullName($this->resource),
            'last_modified' => $fileHelper->getLastModified($this->resource),
            'size'          => $fileHelper->getSize($this->resource),
            'external_id'   => $this->external_id,
            'extension'     => $this->extension,
            'parent_uuid'   => $this->when($this->parent, function(){
                return $this->parent->uuid;
            }),
            'folders_count' => $this->when(
                $this->type === File::TYPE_FOLDER,
                $this->children()->where('type', File::TYPE_FOLDER)->count()
            ),
            'files_count'   => $this->when(
                $this->type === File::TYPE_FOLDER,
                $this->children()->where('type', '<>', File::TYPE_FOLDER)->count()
            )
        ];
    }
}
