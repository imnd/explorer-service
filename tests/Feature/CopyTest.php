<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Queue,
    Tests\TestCase,
    App\Models\File;

class CopyTest extends TestCase
{
    protected $method = 'PATCH';

    /**
     * Тестируем копирование файла
     * @test
     * @return void
     */
    public function main()
    {
        Queue::fake();
        $this->setRoute();
        // создаем папку
        $folder = $this->createModel(File::TYPE_FOLDER);
        foreach (File::TYPES as $type) {
            $file = $this->createModel($type);
            $this->checkRequestResult([
                'type' => $type,
                'uuid' => $file->uuid,
                'destination_uuid' => $folder->uuid,
            ]);
        }
    }
}
