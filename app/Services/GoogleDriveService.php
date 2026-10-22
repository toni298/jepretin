<?php

namespace App\Services;

use App\Models\Photo;
use Google\Client;
use Google\Http\MediaFileUpload;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Storage;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

class GoogleDriveService
{
    private const CHUNK_SIZE = 5 * 1024 * 1024;

    private ?Client $client = null;

    private ?Drive $drive = null;

    public function uploadPhoto(Photo $photo, string $folderId): string
    {
        if (is_string($photo->gdrive_file_id) && $photo->gdrive_file_id !== '') {
            return $photo->gdrive_file_id;
        }

        $existingFileId = $this->findPhotoFileId((string) $photo->getKey(), $folderId);

        if ($existingFileId !== null) {
            return $existingFileId;
        }

        $disk = Storage::disk('s3');
        $stream = $disk->readStream($photo->storage_path);

        if (! is_resource($stream)) {
            throw new RuntimeException('Photo storage stream could not be opened.');
        }

        try {
            $contents = stream_get_contents($stream);
        } finally {
            fclose($stream);
        }

        if (! is_string($contents) || $contents === '') {
            throw new RuntimeException('Photo storage object is empty or could not be read.');
        }

        if (strlen($contents) > 10 * 1024 * 1024) {
            throw new RuntimeException('Photo exceeds the supported 10 MB upload limit.');
        }

        $mimeType = is_string($photo->mime_type) && $photo->mime_type !== ''
            ? $photo->mime_type
            : 'application/octet-stream';
        $file = new DriveFile;
        $file->setName($this->fileName($photo));
        $file->setParents([$folderId]);
        $file->setMimeType($mimeType);
        $file->setAppProperties([
            'jepretinPhotoId' => (string) $photo->getKey(),
        ]);

        $client = $this->client();
        $client->setDefer(true);

        try {
            /** @var RequestInterface $request */
            $request = $this->drive()->files->create($file, [
                'fields' => 'id',
                'uploadType' => 'resumable',
                'supportsAllDrives' => true,
            ]);
        } finally {
            $client->setDefer(false);
        }

        $upload = new MediaFileUpload(
            $client,
            $request,
            $mimeType,
            $contents,
            true,
            self::CHUNK_SIZE,
        );
        $upload->setFileSize(strlen($contents));

        $result = false;

        while ($result === false) {
            $result = $upload->nextChunk();
        }

        $fileId = is_object($result) && method_exists($result, 'getId')
            ? $result->getId()
            : null;

        if (! is_string($fileId) || $fileId === '') {
            throw new RuntimeException('Google Drive did not return a file ID.');
        }

        return $fileId;
    }

    private function client(): Client
    {
        if ($this->client instanceof Client) {
            return $this->client;
        }

        $client = new Client;
        $client->setApplicationName((string) config('app.name', 'Jepretin'));
        $client->setScopes([Drive::DRIVE]);

        $encodedCredentials = config('services.google_drive.service_account_json_base64');

        if (is_string($encodedCredentials) && $encodedCredentials !== '') {
            $json = base64_decode($encodedCredentials, true);

            if (! is_string($json)) {
                throw new RuntimeException('Google Drive service account configuration is invalid.');
            }

            $credentials = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($credentials)) {
                throw new RuntimeException('Google Drive service account configuration is invalid.');
            }

            $client->setAuthConfig($credentials);
            $this->client = $client;

            return $this->client;
        }

        $credentialsPath = config('services.google_drive.service_account_path');

        if (! is_string($credentialsPath) || ! is_file($credentialsPath)) {
            $misspelledPath = base_path('service-accoun.json');
            $credentialsPath = is_file($misspelledPath) ? $misspelledPath : null;
        }

        if (! is_string($credentialsPath) || ! is_file($credentialsPath)) {
            throw new RuntimeException('Google Drive service account credentials are not configured.');
        }

        $client->setAuthConfig($credentialsPath);
        $this->client = $client;

        return $this->client;
    }

    private function drive(): Drive
    {
        return $this->drive ??= new Drive($this->client());
    }

    private function findPhotoFileId(string $photoId, string $folderId): ?string
    {
        $files = $this->drive()->files->listFiles([
            'q' => sprintf(
                "'%s' in parents and appProperties has { key='jepretinPhotoId' and value='%s' } and trashed = false",
                $this->escapeQueryValue($folderId),
                $this->escapeQueryValue($photoId),
            ),
            'pageSize' => 1,
            'fields' => 'files(id)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ])->getFiles();

        $fileId = isset($files[0]) ? $files[0]->getId() : null;

        return is_string($fileId) && $fileId !== '' ? $fileId : null;
    }

    private function escapeQueryValue(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }

    private function fileName(Photo $photo): string
    {
        $originalName = is_string($photo->original_filename) ? $photo->original_filename : '';
        $fileName = basename(str_replace('\\', '/', $originalName));

        return $fileName !== '' ? mb_substr($fileName, 0, 180) : 'photo-'.$photo->getKey();
    }
}
