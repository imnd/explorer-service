<?php

namespace App\Events;

use
    App\Models\File,
    Illuminate\Foundation\Events\Dispatchable,
    Illuminate\Broadcasting\InteractsWithSockets,
    Illuminate\Queue\SerializesModels;

class FileCopied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    public function __construct(File $file)
    {
        $this->data = $file;
    }
}
