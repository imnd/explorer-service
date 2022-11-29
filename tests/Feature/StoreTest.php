<?php

namespace Tests\Feature;

use
    Tests\TestCase,
    App\Models\File,
    Illuminate\Support\Facades\Storage,
    Illuminate\Http\UploadedFile;

class StoreTest extends TestCase
{
    protected $controller = 'File';
    protected $method = 'POST';

    /**
     * Тестируем загрузку файла
     * @test
     * @return void
     */
    public function S3()
    {
        $type = File::TYPE_S3;
        // создаем папку
        $folder = $this->createModel(File::TYPE_FOLDER);
        // загружаем в нее файл
        $this->setRoute(null, []);
        $this->checkRequestResult($this->getFileUploadParams($folder, $type));
    }
}
