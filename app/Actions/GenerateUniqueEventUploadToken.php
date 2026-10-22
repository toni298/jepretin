<?php

namespace App\Actions;

use App\Repositories\EventRepository;
use Illuminate\Support\Str;

class GenerateUniqueEventUploadToken
{
    public function __construct(private EventRepository $events) {}

    public function handle(): string
    {
        do {
            $token = Str::random(20);
        } while ($this->events->uploadTokenExists($token));

        return $token;
    }
}
