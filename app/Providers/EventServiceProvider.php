<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider,
    Illuminate\Auth\Events\Registered,
    Dogovor24\Queue\Events\Explorer\CreateExplorerFile,
    App\Events\FileRenamed,
    App\Events\FileCopied,
    Illuminate\Auth\Listeners\SendEmailVerificationNotification,
    App\Listeners\CreateExplorerFileListener,
    App\Listeners\RenameEntity,
    App\Listeners\CopyEntity
;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        CreateExplorerFile::class => [
            CreateExplorerFileListener::class
        ],
        FileRenamed::class => [
            RenameEntity::class
        ],
        FileCopied::class => [
            CopyEntity::class,
        ],
    ];

    protected $subscribe = [];
}
