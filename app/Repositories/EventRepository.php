<?php

namespace App\Repositories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;

class EventRepository
{
    private const PAGE_SIZE = 10;

    /**
     * @param  array{search?: string|null, status?: string|null, cursor?: string|null}  $filters
     * @return CursorPaginator<int, Event>
     */
    public function paginateForUser(User $user, array $filters): CursorPaginator
    {
        $search = trim($filters['search'] ?? '');

        $paginator = $user->events()
            ->withCount([
                'photos',
                'photos as synced_photos_count' => fn (Builder $query) => $query->where('status', 'synced'),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $pattern = "%{$search}%";

                    $query->whereLike('title', $pattern)
                        ->orWhereLike('slug', $pattern)
                        ->orWhereLike('bride_name', $pattern)
                        ->orWhereLike('groom_name', $pattern);
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(
                self::PAGE_SIZE,
                ['*'],
                'cursor',
                Cursor::fromEncoded($filters['cursor'] ?? null),
            );

        return $paginator->appends(array_filter([
            'search' => $filters['search'] ?? null,
            'status' => $filters['status'] ?? null,
        ], fn (?string $value): bool => $value !== null && $value !== ''));
    }

    /**
     * @return Collection<int, Event>
     */
    public function dashboardEventsFor(User $user): Collection
    {
        return $user->events()
            ->withCount([
                'photos',
                'photos as synced_photos_count' => fn (Builder $query) => $query->where('status', 'synced'),
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id', 'title', 'slug', 'bride_name', 'groom_name', 'status']);
    }

    /**
     * @return array{total: int, active: int}
     */
    public function summaryFor(User $user): array
    {
        $events = $user->events();

        return [
            'total' => (clone $events)->count(),
            'active' => (clone $events)->where('status', 'active')->count(),
        ];
    }

    public function findOwnedOrFail(User $user, string $id): Event
    {
        return $user->events()->whereKey($id)->firstOrFail();
    }

    public function findBySlugOrFail(string $slug): Event
    {
        return Event::query()->where('slug', $slug)->firstOrFail();
    }

    public function findByUploadTokenOrFail(string $token): Event
    {
        return Event::query()->where('upload_token', $token)->firstOrFail();
    }

    public function createFor(User $user, array $data): Event
    {
        return $user->events()->create($data);
    }

    public function update(Event $event, array $data): void
    {
        $event->update($data);
    }

    public function archive(Event $event): void
    {
        $event->update(['status' => 'archived']);
    }

    public function loadPhotoCounts(Event $event): Event
    {
        return $event->loadCount([
            'photos',
            'photos as synced_photos_count' => fn (Builder $query) => $query->where('status', 'synced'),
        ]);
    }

    public function slugExists(string $slug): bool
    {
        return Event::query()->where('slug', $slug)->exists();
    }

    public function uploadTokenExists(string $token): bool
    {
        return Event::query()->where('upload_token', $token)->exists();
    }
}
