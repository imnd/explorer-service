<?php

namespace App\Services;

use Exception,
    Illuminate\Database\Eloquent\ModelNotFoundException,
    Illuminate\Support\Facades\DB,
    Illuminate\Support\Str,
    App\Contracts\FileContract,
    App\Models\File
;

/**
 * Class FileService
 * @package App\Services
 */
abstract class FileService implements FileContract
{
    /**
     * Тип сервиса. Определяется в потомке.
     */
    protected $type;
    protected $file;
    protected $fileHelper;

    public function __construct(File $file = null)
    {
        $this->file = $file;
        $this->fileHelper = new FileHelper;
    }

    public function move(array $data)
    {
        if ($data['uuid'] === $data['destination_uuid']) {
            return 'destination uuid and file uuid are the same';
        }
        if (!$file = $this->_findFile($data['uuid'])) {
            return 'file model not found';
        }
        if (!$destination = _findFolder($data['destination_uuid'])) {
            return 'directory not found';
        }
        if ($this->fileHelper->hasFile($destination, $file->name)) {
            return 'directory contains file with same name';
        }
        try {
            $file->update(['parent_id' => $destination->id]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $file;
    }

    public function copy(array $data)
    {
        if ($data['uuid'] === $data['destination_uuid']) {
            return 'destination uuid and file uuid are the same';
        }
        if (!$file = $this->_findFile($data['uuid'])) {
            return 'file model not found';
        }
        if (!$destination = _findFolder($data['destination_uuid'])) {
            return 'directory not found';
        }
        if ($this->fileHelper->hasFile($destination, $file->name)) {
            return 'directory contains file with same name';
        }
        try {
            $fileCopy = $file->replicate();
            $fileCopy->name = $this->fileHelper->generateCopyName($destination, $fileCopy);
            $fileCopy->parent_id = $destination->id;
            $fileCopy->uuid = Str::orderedUuid();
            $fileCopy->external_id = hash('md5', $fileCopy->external_id . time());
            $fileCopy->save();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $fileCopy;
    }

    public function rename(array $data)
    {
        if (!$file = $this->_findFile($data['uuid'])) {
            return 'file model not found';
        }
        $newName = $data['newname'];
        if ($file->name === $newName) {
            return 'new name is the same';
        }
        if (
               $file->parent
            && $this->fileHelper->hasFile($file->parent, $newName)
        ) {
            return 'file with the same name already exist in this directory';
        }
        try {
            $file->update(['name' => $newName]);
            //$file->setRelations([]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $file;
    }

    public function addToFavorites(array $data)
    {
        if (!$file = $this->_findFile($data['uuid'])) {
            return 'file model not found';
        }
        try {
            $file->update(['favorite' => true]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $file;
    }

    /**
     * находим файл по $uuid
     */
    private function _findFile($uuid)
    {
        return File
            ::where('uuid', $uuid)
            ->where('type', $this->type)
            ->first();
    }

    /**
     * находим папку по $uuid
     */
    private function _findFolder($uuid)
    {
        return File
            ::where('uuid', $uuid)
            ->where('type', File::TYPE_FOLDER)
            ->first();
    }
}
