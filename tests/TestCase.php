<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase,
    Illuminate\Foundation\Testing\WithFaker,
    Illuminate\Foundation\Testing\RefreshDatabase,
    Illuminate\Http\UploadedFile,
    ReflectionClass;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, CreatesApplication, WithFaker;

    /**
     * @var string
     */
    protected $modelName = 'File';
    /**
     * Аутентифицированный пользователь
     * @var \App\User
     */
    protected $user;
    /**
     * HTTP метод
     * @var string
     */
    protected $method;
    /**
     * Какой контроллер тестируем
     * @var string
     */
    protected $controller;
    /**
     * Какой путь тестируем
     * @var string
     */
    protected $route;
    /**
     * Ожидаемый код HTTP статуса
     * @var integer
     */
    protected $expectedStatus;

    protected const METHODS_ACTIONS = [
        'POST' => 'store',
        'GET' => 'show',
        'PATCH' => 'update',
        'DELETE' => 'destroy',
    ];

    protected const METHODS_RESP_CODES = [
        'GET' => 200,
        'POST' => 201,
        'DELETE' => 202,
        'PATCH' => 204,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory('App\User')->create();
        $this->withoutMiddleware();
        $this->withoutExceptionHandling();
        if (is_null($this->controller)) {
            $reflect = new ReflectionClass($this);
            $controllerName = str_replace('Test', '', $reflect->getShortName());
            $controllerNameArr = preg_split('/(?=[A-Z])/', $controllerName);
            array_shift($controllerNameArr);
            $this->controller = strtolower(implode('-', $controllerNameArr));
        }
        if (!is_null($this->method)) {
            $this->expectedStatus = self::METHODS_RESP_CODES[$this->method];
        }
    }

    /**
     * @param $action string
     * @param $params array
     * @return TestCase
     */
    protected function setRoute($action = null, $params = null)
    {
        if (is_null($params)) {
            $params = [
                strtolower($this->controller) => 1
            ];
        }
        $this->route = route($this->getRoute($action), $params);
        return $this;
    }

    /**
     * @param $method string
     * @return TestCase
     */
    protected function setMethod($method)
    {
        $this->method = $method;
        $this->expectedStatus = self::METHODS_RESP_CODES[$this->method];
        return $this;
    }

    /**
     * @param $action string
     * @return string
     */
    protected function getRoute(string $action = null)
    {
        if (is_null($action)) {
            $action = self::METHODS_ACTIONS[$this->method];
        }
        return (empty($this->controller) ? '' : strtolower($this->controller) . '.') . $action;
    }

    /**
     * @return string
     */
    protected function getModelName()
    {
        return "App\\Models\\{$this->modelName}";
    }

    /**
     * @param $type string
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function createModel(string $type)
    {
        return factory($this->getModelName())
            ->create([
                'type' => $type,
                'user_id' => $this->user->id,
            ]);
    }

    protected function checkRequestStatus(array $params = [], $status = null)
    {
        if (is_null($status)) {
            $status = $this->expectedStatus;
        }
        $this
            ->actingAs($this->user, 'api')
            ->json($this->method, $this->route, $params)
            ->assertStatus($status);

        return $this;
    }

    /**
     * @param $params array
     */
    protected function checkRequestResult(array $params, $status = null)
    {
        $result = $this->getRequestResult($params, $status);
        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('error', $result);
        $this->checkFileResource($result['data']);

        return $this;
    }

    /**
     * @param $params array
     *
     * @return mixed
     */
    protected function getRequestResult(array $params = [], $status = null)
    {
        if (is_null($status)) {
            $status = $this->expectedStatus;
        }
        $response = $this
            ->actingAs($this->user, 'api')
            ->json($this->method, $this->route, $params);
       
        $response->assertStatus($status);

        return $response->decodeResponseJson();
    }

    /**
     * @param array $data
     */
    protected function checkFileResource(array $data)
    {
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('last_modified', $data);
        $this->assertArrayHasKey('size', $data);
        $this->assertArrayHasKey('external_id', $data);
        $this->assertArrayHasKey('extension', $data);

        return $this;
    }

    protected function getFileUploadParams($folder, $type)
    {
        return [
            'type' => $type,
            'name' => 'File name',
            'parent_uuid' => $folder->uuid,
            'file' => UploadedFile::fake()->image($this->faker->userName . '.jpg')
        ];
    }
}
