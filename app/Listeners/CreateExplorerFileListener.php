<?php

namespace App\Listeners;

use App\Models\File,
    App\Services\FileHelper,
    Illuminate\Support\Str;

class CreateExplorerFileListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return mixed
     */
    public function handle($event)
    {
        /** @var FileHelper $fileHelper */
        $fileHelper = resolve(FileHelper::class);

        $file = File::where('external_id', $event->id)->first();
        if (!is_null($file)) {
            return false;
        }

        $parent = File::where('user_id', $event->user_id)
            ->whereType(File::TYPE_FOLDER)
            ->whereNull('parent_id')
            ->first();

        if (is_null($parent)) {
            $name = hash('md5', $event->user_id);
            $parent = File::create([
                'name' => $name,
                'parent_id' => null,
                'type' => File::TYPE_FOLDER,
                'uuid' => Str::orderedUuid(),
                'user_id' => $event->user_id
            ]);
        }

        $file = new File([
            'type' => File::TYPE_D24,
            'parent_id' => $parent->id,
            'name' => $event->name,
            'extension' => $event->extension,
            'external_id' => $event->id,
            'user_id' => $event->user_id,
            'uuid' => Str::orderedUuid(),
        ]);
        $file->name = $fileHelper->generateCopyName($parent, $file);
        $file->save();
    }
}
