<?php

namespace App\Actions;

use App\Repositories\EventRepository;
use Illuminate\Support\Str;

class GenerateUniqueEventSlug
{
    public function __construct(private EventRepository $events) {}

    public function handle(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'event';
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->events->slugExists($slug)) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
