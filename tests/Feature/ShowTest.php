<?php

namespace Tests\Feature;

use
    Tests\TestCase,
    App\Models\File,
    Illuminate\Http\UploadedFile,
    Illuminate\Support\Facades\Storage,
    Illuminate\Support\Str,
    Symfony\Component\HttpFoundation\StreamedResponse
;

class ShowTest extends TestCase
{
    protected $controller = 'File';
    protected $method = 'GET';

    /** @test */
    public function guests_cannot_download_s3()
    {
        $this->withMiddleware();
        $this
            ->setMethod('GET')
            ->setRoute('show', Str::orderedUuid())
            ->json($this->method, $this->route, ['type' => File::TYPE_S3])
            ->assertStatus(401);
    }

    /**
     * Тестируем скачивание файла
     * @test
     * @return void
     */
    public function S3()
    {
        // создаем папку
        $folder = $this->createModel(File::TYPE_FOLDER);
        $type = File::TYPE_S3;
        // загружаем в нее файл
        $result = $this
            ->setMethod('POST')
            ->setRoute('store', [])
            ->actingAs($this->user, 'api')
            ->getRequestResult($this->getFileUploadParams($folder, $type));

        // проверяем на месте ли он
        $result = $this
            ->setMethod('GET')
            ->setRoute('show', $result['data']['uuid'])
            ->actingAs($this->user, 'api')
            ->json($this->method, $this->route, compact('type'))
        ;
        $this->assertTrue($result->baseResponse instanceof StreamedResponse);
    }

    /**
     * Тестируем скачивание файла D24
     * @test
     * @return void
     */
    /*public function D24()
    {
        $type = File::TYPE_D24;
        // проверяем на месте ли файл
        $this->setRoute('show', 1);
        $params = compact('type');
        $result = $this
            ->actingAs($this->user, 'api')
            ->json($this->method, $this->route, $params);
            
        print '<pre>'.print_r($result, true).'</pre>';die;
        
        $this
            ->assertIsArray($result)
            ->assertArrayNotHasKey('error', $result)
            ->checkFileResource($result['data'][0]);
    }*/
}
