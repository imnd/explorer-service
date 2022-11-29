<?php

namespace Tests\Feature;

use Tests\TestCase,
    App\Models\File;

class SearchTest extends TestCase
{
    protected $method = 'GET';
    protected $controller = 'search';

    /**
     * Тестируем поиск файла
     * @test
     * @return void
     */
    public function main()
    {
        $this->setRoute('index', []);
        foreach (File::TYPES as $type) {
            $file = $this->createModel($type);
            $params = compact('type');

            // тестируем нахождение файла
            $params['text'] = '*';
            $result = $this->getRequestResult($params);
            $this->assertIsArray($result);
            $this->assertArrayNotHasKey('error', $result);
            $this->assertArrayHasKey('meta', $result);
            $this->assertNotEquals($result['meta']['total'], 0);

            // тестируем ненахождение файла
            $params['text'] = md5(microtime());
            $result = $this->getRequestResult($params);
            $this->assertIsArray($result);
            $this->assertArrayHasKey('meta', $result);
            $this->assertEquals($result['meta']['total'], 0);
            
            $file->delete();
        }
    }
}
