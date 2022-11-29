<?php

namespace App\Services;

use
    Exception,
    App\Models\File,
    App\Events\FileRenamed,
    App\Events\FileCopied,
    Dogovor24\Authorization\Services\AuthRequestService,
    Illuminate\Database\Eloquent\ModelNotFoundException,
    Illuminate\Support\Facades\DB,
    Illuminate\Support\Str
;

class FileD24Service extends FileService
{
    protected $type = File::TYPE_D24;
    protected $urlDownload;

    public function __construct(File $file = null)
    {
        parent::__construct($file);

        $this->urlDownload = env('DOCUMENT_SERVICE_GENERATE_URL');
    }

    public function copy(array $data)
    {
        $file = parent::copy($data);
        if ($file instanceof File) {
            event(new FileCopied($file));
        }
        return $file;
    }

    public function rename(array $data)
    {
        $file = parent::rename($data);
        if ($file instanceof File) {
            event(new FileRenamed($file));
        }
        return $file;
    }

    public function store(array $data)
    {
        try {
            $name = $data['name'];
            DB::beginTransaction();
            $file = new File([
                'type' => $data['type'],
                'parent_id' => null,
                'name' => $name,
                'extension' => '',
                'external_id' => null,
                'user_id' => $data['user_id'],
                'uuid' => Str::orderedUuid(),
            ]);
            $file->save();
            DB::commit();
            return $file;
        } catch (Exception $e) {
            DB::rollback();
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

            $file->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function download(int $userId, string $uuid)
    {
        try {
            $file = File::where('uuid', $uuid)
                ->where('type', $this->type)
                ->where('user_id', $userId)
                ->firstOrFail();

        } catch (ModelNotFoundException $e) {
            return 'file not found';
        } catch (Exception $e) {
            return $e->getMessage();
        }
        $client = (new AuthRequestService(config('api.document_url'), [
            'timeout' => 60,
            'connect_timeout' => 30,
            'read_timeout' => 30,
        ]))->getHttpClient(false, true);
        $response = $client->request(
            'GET',
            "generate/{$file->external_id}"
        );
        return response()->streamDownload(function() use ($response) {
            echo $response->getBody()->getContents();
        }, $this->fileHelper->getFullName($file));
    }
}
