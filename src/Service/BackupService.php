<?php
namespace Vedovelli\VedovelliPHPLib\Service;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Class BackupService
 *
 * This service handles database backup operations.
 */
class BackupService
{
    /**
     * @var Filesystem The disk instance for file operations.
     */
    private Filesystem $disk;

    /**
     * BackupService constructor.
     *
     * @param  string  $diskName  The name of the disk to use for backups (default: 's3').
     */
    public function __construct(string $diskName = 's3')
    {
        $this->disk = Storage::disk($diskName);
    }

    /**
     * Perform a database backup.
     *
     * @return bool True if the backup was successful, false otherwise.
     */
    public function sqliteBDBackup(): bool
    {
        $env = config('app.env');
        $timestamp = now()->format('Y-m-d_H:i:s');
        $sourcePath = database_path('database.sqlite');
        $destinationPath = "db-backups/$env/$timestamp-database.sqlite";

        if (! $this->isSourceFileValid($sourcePath)) {
            return false;
        }

        $localSize = \File::size($sourcePath);
        $lastBackup = $this->getLastBackup($env);

        if (! $lastBackup || $this->shouldBackup($lastBackup, $localSize)) {
            return $this->performBackup($sourcePath, $destinationPath);
        }

        return false;
    }

    /**
     * Check if the source file is valid and readable.
     *
     * @param  string  $sourcePath  The path to the source file.
     * @return bool True if the file exists and is readable, false otherwise.
     */
    private function isSourceFileValid(string $sourcePath): bool
    {
        return file_exists($sourcePath) && is_readable($sourcePath);
    }

    /**
     * Get the last backup file for the given environment.
     *
     * @param  string  $env  The environment name.
     * @return string|null The path to the last backup file, or null if no backups exist.
     */
    private function getLastBackup(string $env): ?string
    {
        return collect($this->disk->allFiles("db-backups/$env"))->last();
    }

    /**
     * Determine if a new backup should be performed.
     *
     * @param  string  $lastBackup  The path to the last backup file.
     * @param  int  $localSize  The size of the local database file.
     * @return bool True if a new backup should be performed, false otherwise.
     */
    private function shouldBackup(string $lastBackup, int $localSize): bool
    {
        $remoteSize = $this->disk->size($lastBackup);

        return $localSize !== $remoteSize;
    }

    /**
     * Perform the actual backup operation.
     *
     * @param  string  $sourcePath  The path to the source file.
     * @param  string  $destinationPath  The path where the backup should be stored.
     * @return bool True if the backup was successful, false otherwise.
     */
    private function performBackup(string $sourcePath, string $destinationPath): bool
    {
        $stream = fopen($sourcePath, 'r');

        return $this->disk->writeStream($destinationPath, $stream);
    }
}
