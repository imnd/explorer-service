<?php

namespace App\Listeners;

use Exception,
    GuzzleHttp\Exception\GuzzleException,
    Dogovor24\Authorization\Services\AuthRequestService,
    Illuminate\Contracts\Queue\ShouldQueue,
    Illuminate\Support\Facades\Log,
    Illuminate\Support\Facades\Auth;

class CopyEntity implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return mixed
     */
    public function handle($event)
    {
        $file = $event->data;
        try {
            $client = (new AuthRequestService(config('api.document_url')))->getHttpClient(false, true);
            $client->request(
                'POST',
                'entity',
                [
                    'json' => [
                        'type' => 'document',
                        'copy_id' => $file->external_id,
                        'user_id' => Auth::user()->id,
                    ]
                ]
            );
        } catch (GuzzleException | Exception $e) {
            Log::error('error in CopyEntityData::handle() ' . print_r($e, true));
        }
    }
}
