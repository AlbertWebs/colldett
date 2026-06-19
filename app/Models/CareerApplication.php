<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CareerApplication extends Model
{
    public const STATUSES = ['new', 'reviewed', 'shortlisted', 'rejected'];

    protected $fillable = [
        'career_id',
        'name',
        'email',
        'phone',
        'cover_letter',
        'resume_path',
        'resume_original_name',
        'documents',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'documents' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (CareerApplication $application): void {
            $application->deleteStoredFiles();
        });
    }

    /**
     * @return array<int, array{path: string, original_name: string}>
     */
    public function documentEntries(): array
    {
        $documents = $this->documents;
        if (is_array($documents) && $documents !== []) {
            return array_values(array_filter(array_map(
                static fn ($doc) => is_array($doc) && ! empty($doc['path'])
                    ? [
                        'path' => (string) $doc['path'],
                        'original_name' => (string) ($doc['original_name'] ?? basename((string) $doc['path'])),
                    ]
                    : null,
                $documents
            )));
        }

        if ($this->resume_path) {
            return [[
                'path' => $this->resume_path,
                'original_name' => $this->resume_original_name ?: basename($this->resume_path),
            ]];
        }

        return [];
    }

    public function deleteStoredFiles(): void
    {
        $paths = [];
        foreach ($this->documentEntries() as $document) {
            $paths[] = $document['path'];
        }

        foreach (array_unique($paths) as $path) {
            if ($path !== '' && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }
    }

    /**
     * @return BelongsTo<Career, $this>
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }
}
