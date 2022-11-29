<?php

namespace App\Listeners;

use Exception,
    GuzzleHttp\Exception\GuzzleException,
    Dogovor24\Authorization\Services\AuthRequestService,
    Illuminate\Contracts\Queue\ShouldQueue,
    Illuminate\Support\Facades\Log
;

class RenameEntity implements ShouldQueue
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
            $entityId = $file->external_id;
            $response = $client->request('GET', "entity/$entityId");
            $respData = json_decode($response->getBody()->getContents(), true)['data'];
            $payload = $respData['payload'];
            $payload['title'] = ['ru' => $file->name];
            $client->request(
                'PATCH',
                "entity/$entityId",
                ['json' =>
                    [
                        'type' => $respData['type'],
                        'payload' => $payload,
                    ]
                ]
            );
        } catch (GuzzleException | Exception $e) {
            Log::error('error in FileRenamedListener::handle() ' . print_r($e, true));
        }
    }
}
