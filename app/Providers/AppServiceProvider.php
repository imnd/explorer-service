<?php

namespace App\Providers;

use App\Contracts\FileContract;
use App\Models\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(FileContract::class, function ($app, $params) {
            $file = $params[0] ?? null;
            if (!is_null($file) && $file instanceof File) {
                $type = $file->type;
            } else {
                $type = request('type');
            }
            $fileService = config('explorer.file_services_namespace') . studly_case($type) . 'Service';
            if (class_exists($fileService)) {
                return new $fileService($file);
            }
            abort(404);
        });
    }
}
