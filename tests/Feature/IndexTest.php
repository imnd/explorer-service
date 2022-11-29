<?php

namespace Tests\Feature;

use Tests\TestCase,
    App\Models\File;

class IndexTest extends TestCase
{
    protected $controller = 'File';
    protected $method = 'GET';

    /**
     * Тестируем добавление файла в избранное
     * @test
     * @return void
     */
    public function main()
    {
        $this->setRoute('index');
        $fileNum = 10;
        foreach (File::TYPES as $type) {
            for ($i = 0; $i < $fileNum; $i++) {
                $this->createModel($type);
            }
            $result = $this->getRequestResult([], 200);
            $this->assertIsArray($result);
            $this->assertArrayNotHasKey('error', $result);

            for ($i = 0; $i < $fileNum; $i++) {
                $this->checkFileResource($result['data'][$i]);
            }
        }
    }
}
