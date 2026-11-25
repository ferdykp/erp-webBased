<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SecureUploadedFiles extends Command
{
    protected $signature = 'uploads:secure';

    protected $description = 'Move referenced sensitive uploads from public to private storage';

    public function handle(): int
    {
        $directories = ['products', 'product-tests', 'dosimeter_images'];
        $moved = 0;
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        foreach ($directories as $directory) {
            foreach ($public->allFiles($directory) as $path) {
                if (!Str::startsWith($path, $directory . '/') || Str::contains($path, ['../', '..\\'])) {
                    throw new RuntimeException('A public upload has an invalid path.');
                }

                if ($private->exists($path)) {
                    $public->delete($path);
                    continue;
                }

                $stream = $public->readStream($path);
                if (!is_resource($stream)) {
                    throw new RuntimeException('A public upload could not be read for migration.');
                }

                try {
                    $copied = $private->writeStream($path, $stream);
                } finally {
                    fclose($stream);
                }

                if (!$copied || !$private->exists($path)) {
                    throw new RuntimeException('A public upload could not be copied to private storage.');
                }

                if (!$public->delete($path) && $public->exists($path)) {
                    throw new RuntimeException('A public upload could not be removed after migration.');
                }

                $moved++;
            }
        }

        $this->info("Private upload migration complete. Moved: {$moved}.");

        return self::SUCCESS;
    }
}
