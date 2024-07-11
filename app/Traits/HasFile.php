<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasFile
{
    /**
     * Update the file associated with the specified column.
     *
     * @param  UploadedFile  $file  The uploaded file.
     * @param  string  $column  The column name representing the file path.
     * @param  string  $path  The storage path for the new file.
     */
    public function updateFile(UploadedFile $file, string $column, string $path): void
    {
        tap($column, function ($previous) use ($file, $column, $path) {
            $this->forceFill([
                $column => $file->storePublicly(
                    $path, ['disk' => 'public']
                ),
            ])->save();

            // delete the previous file from storage
            if ($previous) {
                Storage::disk('public')->delete($previous);
            }
        });
    }

    /**
     * Delete the file and update the specified column in the database.
     *
     * @param  string  $path  The file path of the file to be deleted.
     * @param  string  $column  The database column to be updated with null.
     */
    public function deleteFile(string $path, string $column): void
    {
        Storage::disk('public')->delete($path);

        $this->forceFill([
            $column => null,
        ])->save();
    }
}
