<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\File;

class MoveTest extends TestCase
{
    protected $method = 'PATCH';

    /**
     * Тестируем перемещение файла
     * @test
     * @return void
     */
    public function main()
    {
        $this->setRoute();
        foreach (File::TYPES as $type) {
            $folder = $this->createModel(File::TYPE_FOLDER);
            $file = $this->createModel($type);
            $this->checkRequestStatus([
                'type' => $type,
                'uuid' => $file->uuid,
                'destination_uuid' => $folder->uuid,
            ]);
            $folder->delete();
        }
    }
}
