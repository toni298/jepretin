<?php

namespace App\Services;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use UnexpectedValueException;
use Illuminate\Validation\ValidationException;

class SupabaseStorage
{
    /**
     * @return array{path: string, uploadUrl: string, headers: array<string, string>}
     */
    public function createCoverUpload(User $user, string $contentType): array
    {
        $this->ensureS3DiskIsConfigured();

        $extension = match ($contentType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new UnexpectedValueException('Format foto cover tidak didukung.'),
        };
        $path = "covers/{$user->getKey()}/".Str::uuid().".{$extension}";

        $signature = Storage::disk('s3')->temporaryUploadUrl(
            $path,
            now()->addMinutes(5),
            ['ContentType' => $contentType],
        );

        if (! isset($signature['url']) || ! is_string($signature['url']) || $signature['url'] === '') {
            throw new UnexpectedValueException('Disk S3 tidak mengembalikan URL upload yang valid.');
        }

        $headers = collect($signature['headers'] ?? [])
            ->filter(fn (mixed $value, string $name): bool => strtolower($name) === 'content-type' && is_string($value))
            ->all();

        return [
            'path' => $path,
            'uploadUrl' => $signature['url'],
            'headers' => $headers,
        ];
    }

    /**
     * @return array{path: string, uploadUrl: string, headers: array<string, string>}
     */
    public function createGuestPhotoUpload(Event $event, string $contentType): array
    {
        $this->ensureS3DiskIsConfigured();

        $extension = match ($contentType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new UnexpectedValueException('Format foto tidak didukung.'),
        };
        $path = "events/{$event->getKey()}/photos/".Str::uuid().".{$extension}";

        $signature = Storage::disk('s3')->temporaryUploadUrl(
            $path,
            now()->addMinutes(5),
            ['ContentType' => $contentType],
        );

        if (! isset($signature['url']) || ! is_string($signature['url']) || $signature['url'] === '') {
            throw new UnexpectedValueException('Disk S3 tidak mengembalikan URL upload yang valid.');
        }

        $headers = collect($signature['headers'] ?? [])
            ->filter(fn (mixed $value, string $name): bool => strtolower($name) === 'content-type' && is_string($value))
            ->all();

        return [
            'path' => $path,
            'uploadUrl' => $signature['url'],
            'headers' => $headers,
        ];
    }

    /**
     * @return array{size: int, mimeType: string}
     */
    public function verifyGuestPhoto(string $path, string $contentType): array
    {
        $disk = Storage::disk('s3');

        if (! $disk->exists($path)) {
            throw ValidationException::withMessages([
                'photo' => 'Foto belum berhasil diunggah. Silakan coba kembali.',
            ]);
        }

        $size = $disk->size($path);
        $mimeType = $disk->mimeType($path);

        if ($size < 1 || $size > 10 * 1024 * 1024 || $mimeType !== $contentType) {
            throw ValidationException::withMessages([
                'photo' => 'File foto tidak valid atau melebihi batas ukuran 10 MB.',
            ]);
        }

        return ['size' => $size, 'mimeType' => $mimeType];
    }

    public function publicUrl(string $path): string
    {
        $bucket = config('filesystems.disks.s3.bucket');
        $publicUrl = config('filesystems.disks.s3.public_url');

        if (! is_string($bucket) || $bucket === '') {
            throw new RuntimeException('AWS_BUCKET wajib diatur untuk membangun URL foto.');
        }

        if (is_string($publicUrl) && $publicUrl !== '') {
            return rtrim($publicUrl, '/').'/'.$this->encodePath($path);
        }

        $endpoint = config('filesystems.disks.s3.endpoint');
        $parts = is_string($endpoint) ? parse_url($endpoint) : false;
        $host = is_array($parts) ? ($parts['host'] ?? null) : null;

        if (! is_string($host) || ! str_ends_with($host, '.storage.supabase.co')) {
            throw new RuntimeException('Atur AWS_PUBLIC_URL sebagai base URL publik bucket Supabase.');
        }

        $publicHost = substr($host, 0, -strlen('.storage.supabase.co')).'.supabase.co';
        $scheme = is_array($parts) && ($parts['scheme'] ?? null) === 'http' ? 'http' : 'https';

        return "{$scheme}://{$publicHost}/storage/v1/object/public/"
            .rawurlencode($bucket).'/'.$this->encodePath($path);
    }

    private function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    private function ensureS3DiskIsConfigured(): void
    {
        foreach (['key', 'secret', 'region', 'bucket', 'endpoint'] as $setting) {
            $value = config("filesystems.disks.s3.{$setting}");

            if (! is_string($value) || $value === '') {
                throw new RuntimeException("Konfigurasi disk S3 belum lengkap: AWS_{$this->environmentName($setting)} wajib diatur.");
            }
        }
    }

    private function environmentName(string $setting): string
    {
        return match ($setting) {
            'key' => 'ACCESS_KEY_ID',
            'secret' => 'SECRET_ACCESS_KEY',
            'region' => 'DEFAULT_REGION',
            'bucket' => 'BUCKET',
            'endpoint' => 'ENDPOINT',
        };
    }
}
