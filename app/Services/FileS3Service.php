<?php

namespace App\Services;

use
    Exception,
    Illuminate\Database\Eloquent\ModelNotFoundException,
    Illuminate\Support\Facades\DB,
    Illuminate\Support\Facades\Storage,
    Illuminate\Support\Str,
    App\Models\File,
    App\Events\FileCopied;

class FileS3Service extends FileService
{
    protected $type = File::TYPE_S3;

    private const STORAGE = 's3';

    public function delete(array $data)
    {
        try {
            DB::beginTransaction();
            $file = File::where('uuid', $data['uuid'])
                ->where('type', $this->type)
                ->firstOrFail();

            if (!Storage::disk(self::STORAGE)->delete($file->external_id)) {
                throw new Exception('S3 delete returns false');
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
            $fullName = $data['file']->getClientOriginalName();
            $extension = $data['file']->getClientOriginalExtension();
            if (isset($data['parent_uuid'])) {
                $folder = File::where('uuid', $data['parent_uuid'])
                    ->firstOrFail();
            } else {
                $folder = File::where('user_id', $data['user_id'])
                    ->whereNull('parent_id')
                    ->firstOrFail();
            }
            if (!$this->fileHelper->isFolder($folder)) {
                throw new Exception('parent is not a directory');
            }
            $name = basename($fullName, ".$extension");
            if ($this->fileHelper->hasFile($folder, $name)) {
                throw new Exception('file already exist in this directory');
            }
            DB::beginTransaction();
            $externalId = $data['file']->hashName();
            $file = new File([
                'type' => $data['type'],
                'parent_id' => $folder->id,
                'name' => $name,
                'extension' => $extension,
                'external_id' => $externalId,
                'user_id' => $data['user_id'],
                'uuid' => Str::orderedUuid(),
            ]);
            $disk = Storage::disk(self::STORAGE);
            if (!$disk->put($externalId, file_get_contents($data['file']))) {
                throw new Exception('error loading to S3');
            }
            $file->payload = [
                'size' => Storage::disk(self::STORAGE)->size($externalId),
                'last_modified' => Storage::disk(self::STORAGE)->lastModified($externalId)
            ];
            $file->save();
            DB::commit();
            return $file;
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            return 'file not found';
        } catch (Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    public function download(int $userId, string $uuid)
    {
        try {
            $file = File::where('uuid', $uuid)->firstOrFail();
            $s3File = Storage::disk(self::STORAGE)->download($file->external_id);
            $s3File->headers->set('Content-Disposition', 'attachment; filename="' . $this->fileHelper->getFullName($file) . '"');
            return $s3File;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function copy(array $data)
    {
        $fileCopy = parent::copy($data);

        $fileSource = File::where('uuid', $data['uuid'])
            ->where('type', $this->type)
            ->firstOrFail();

        if (!Storage::disk(self::STORAGE)->copy($fileSource->external_id, $fileCopy->external_id)) {
            throw new Exception('error loading to S3');
        }
        if ($fileCopy instanceof File) {
            event(new FileCopied($fileCopy));
        }
        return $fileCopy;
    }
}
