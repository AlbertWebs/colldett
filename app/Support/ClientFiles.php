<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ClientFiles
{
    private const BASE = 'client-files';

    public static function directoryFor(int $clientId): string
    {
        return self::BASE.'/'.$clientId;
    }

    public static function ensureDirectory(int $clientId): void
    {
        $path = self::directoryFor($clientId);
        if (! Storage::disk('local')->exists($path)) {
            Storage::disk('local')->makeDirectory($path);
        }
    }

    /**
     * @return list<array{name: string, size: int, modified: int}>
     */
    public static function list(int $clientId): array
    {
        self::ensureDirectory($clientId);
        $path = self::directoryFor($clientId);
        $files = [];
        foreach (Storage::disk('local')->files($path) as $relative) {
            $name = basename($relative);
            if ($name === '' || str_starts_with($name, '.')) {
                continue;
            }
            $files[] = [
                'name' => $name,
                'size' => (int) Storage::disk('local')->size($relative),
                'modified' => (int) Storage::disk('local')->lastModified($relative),
            ];
        }

        usort($files, static fn (array $a, array $b): int => $b['modified'] <=> $a['modified']);

        return $files;
    }

    public static function storeUpload(int $clientId, UploadedFile $file): string
    {
        self::ensureDirectory($clientId);
        $original = $file->getClientOriginalName() ?: 'upload';
        $base = pathinfo($original, PATHINFO_FILENAME);
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $safeBase = Str::slug($base, '-');
        if ($safeBase === '') {
            $safeBase = 'file';
        }
        $filename = $safeBase.'.'.$ext;
        $path = self::directoryFor($clientId);
        $counter = 1;
        while (Storage::disk('local')->exists($path.'/'.$filename)) {
            $filename = $safeBase.'-'.$counter.'.'.$ext;
            $counter++;
        }
        $file->storeAs($path, $filename, 'local');

        return $filename;
    }

    public static function absolutePath(int $clientId, string $filename): ?string
    {
        $safe = self::safeFilename($filename);
        if ($safe === null) {
            return null;
        }
        $relative = self::directoryFor($clientId).'/'.$safe;
        if (! Storage::disk('local')->exists($relative)) {
            return null;
        }

        return Storage::disk('local')->path($relative);
    }

    public static function safeFilename(string $filename): ?string
    {
        $filename = basename(str_replace(['\\', '/'], '', $filename));
        if ($filename === '' || str_contains($filename, '..') || str_starts_with($filename, '.')) {
            return null;
        }

        return $filename;
    }

    public static function delete(int $clientId, string $filename): bool
    {
        $safe = self::safeFilename($filename);
        if ($safe === null) {
            return false;
        }
        $relative = self::directoryFor($clientId).'/'.$safe;
        if (! Storage::disk('local')->exists($relative)) {
            return false;
        }

        return Storage::disk('local')->delete($relative);
    }
}
