<?php

use App\Models\File;

class FilesSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function exec()
    {
        $root = factory(File::class)->create();

        $level1 = factory(File::class, 2)->create([
            'user_id' => $root->user_id,
            'parent_id' => $root->id,
            'type' => File::TYPE_FOLDER,
        ]);

        $level1->each(function ($file) {
            $dir = factory(File::class)->create([
                'user_id' => $file->user_id,
                'parent_id' => $file->id,
                'type' => File::TYPE_FOLDER,
            ]);
            factory(File::class)->create([
                'user_id' => $file->user_id,
                'parent_id' => $dir->id,
                'type' => File::TYPE_S3,
            ]);
        });
    }
}
