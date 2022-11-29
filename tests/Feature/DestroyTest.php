<?php

namespace Tests\Feature;

use App\Models\File;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    protected $controller = 'File';

    /**
     * Тестируем удаление файла
     * @test
     * @return void
     */
    public function main()
    {
        foreach (File::TYPES as $type) {
            if ($type === File::TYPE_S3) {
                // создаем папку
                $folder = $this->createModel(File::TYPE_FOLDER);
                // загружаем в нее файл
                $result = $this
                    ->setMethod('POST')
                    ->setRoute('store', [])
                    ->actingAs($this->user, 'api')
                    ->getRequestResult($this->getFileUploadParams($folder, $type));

                $uuid = $result['data']['uuid'];
            } else {
                $uuid = $this->createModel($type)->uuid;
            }
            $this
                ->setMethod('DELETE')
                ->setRoute('destroy')
                ->actingAs($this->user, 'api')
                ->json($this->method, $this->route, compact('type', 'uuid'))
                ->assertStatus(200);
        }
    }
}
