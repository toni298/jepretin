<?php

namespace App\Repositories;

use App\Models\Event;
use App\Models\Photo;

class PhotoRepository
{
    /**
     * @param  array{storage_path: string, original_filename: string, mime_type: string, file_size: int}  $data
     */
    public function createForEvent(Event $event, array $data): Photo
    {
        return $event->photos()->firstOrCreate(
            ['storage_path' => $data['storage_path']],
            [...$data, 'status' => 'pending_sync'],
        );
    }
}
