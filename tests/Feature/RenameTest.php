<?php

namespace Tests\Feature;

use Tests\TestCase,
    App\Models\File,
    Illuminate\Support\Facades\Event;

class RenameTest extends TestCase
{
    protected $method = 'PATCH';

    /**
     * Тестируем переименование файла
     * @test
     * @return void
     */
    public function main()
    {
        Event::fake();
        $this->setRoute();
        foreach (File::TYPES as $type) {
            $file = $this->createModel($type);
            $this->checkRequestStatus([
                'type' => $type,
                'uuid' => $file->uuid,
                'newname' => $file->name . md5(microtime()),
            ]);
        }
    }
}
