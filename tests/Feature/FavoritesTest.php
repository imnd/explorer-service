<?php

namespace Tests\Feature;

use Tests\TestCase,
    App\Models\File;

class FavoritesTest extends TestCase
{
    protected $method = 'PATCH';

    /**
     * Тестируем добавление файла в избранное
     * @test
     * @return void
     */
    public function main()
    {
        $this->setRoute();
        foreach (File::TYPES as $type) {
            $file = $this->createModel($type);
            $this->checkRequestStatus([
                'type' => $type,
                'uuid' => $file->uuid,
            ]);
        }
    }
}
