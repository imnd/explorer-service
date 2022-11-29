<?php

namespace App\Services;

use App\Models\File,
    Illuminate\Database\Eloquent\ModelNotFoundException,
    Illuminate\Support\Facades\Storage,
    Illuminate\Support\Facades\DB,
    Illuminate\Support\Str,
    Exception,
    ZipArchive;

class FolderService extends FileService
{
    protected $type = File::TYPE_FOLDER;

    private function fillZip(File $file, ZipArchive $zip)
    {
        $children = $this
            ->fileHelper
            ->getAllChildren($file)
            ->where('type', '=', File::TYPE_S3);

        if ($children->count() == 0) {
            throw new Exception('no files in folder');
        }

        foreach ($children as $child) {
            $upperPath = $this->fileHelper->getFullPathSymbolic($file);
            $s3File = Storage::disk('s3')->get($child->external_id);
            $zip->addFromString(substr($this->fileHelper->getFullPathSymbolic($child), strlen($upperPath)), $s3File);
        }
        $zip->close();
        return $zip;
    }

    public function download(int $userId, string $uuid)
    {
        try {
            $file = File::where('uuid', $uuid)
                ->where('type', $this->type)
                ->firstOrFail();
            $zip = new ZipArchive();
            $fileName = $file->name . '.zip';
            $zip->open($fileName, ZipArchive::CREATE);
            $this->fillZip($file, $zip);
            return response()->download($fileName, $fileName, array('Content-Type: application/octet-stream', 'Content-Length: ' . filesize($fileName)))->deleteFileAfterSend(true);
        } catch (ModelNotFoundException $e) {
            return 'directory not found';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function delete(array $data)
    {
        try {
            DB::beginTransaction();
            $file = File::where('uuid', $data['uuid'])
                ->where('type', $this->type)
                ->firstOrFail();
            if (!$this->fileHelper->isEmpty($file)) {
                throw new Exception('folder in not empty');
            }
            $file->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function store(array $data)
    {
        try {
            if (isset($data['parent_uuid'])) {
                $parent = File::where('uuid', $data['parent_uuid'])
                    ->where('type', $this->type)
                    ->first();
            } else {
                $parent = File::where('user_id', $data['user_id'])
                    ->whereNull('parent_id')
                    ->first();
            }
            if (is_null($parent)) {
                $name = hash('md5', $data['user_id']);
                $parent = File::create([
                    'name' => $name,
                    'parent_id' => null,
                    'type' => 'folder',
                    'uuid' => Str::orderedUuid(),
                    'user_id' => $data['user_id']
                ]);
            }
            $name = $data['name'] ?? 'new_folder';
            if (!$this->fileHelper->isFolder($parent)) {
                throw new Exception('parent is not directory');
            }
            if ($this->fileHelper->hasDir($parent, $name)) {
                throw new Exception('directory already exist');
            }
            $file = new File([
                'type' => 'folder',
                'parent_id' => $parent->id,
                'name' => $name,
                'uuid' => Str::orderedUuid(),
                'user_id' => $data['user_id']
            ]);
            $file->save();
            return $file;
        } catch (ModelNotFoundException $e) {
            return 'directory not found';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
