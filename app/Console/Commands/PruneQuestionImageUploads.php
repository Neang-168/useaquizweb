<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PruneQuestionImageUploads extends Command
{
    protected $signature = 'question-images:prune-tmp';

    protected $description = 'Delete temporary question-image uploads older than 24 hours';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $cutoff = Carbon::now()->subDay();
        $deleted = 0;

        foreach ($disk->files('tmp') as $file) {
            if (Carbon::createFromTimestamp($disk->lastModified($file))->lt($cutoff)) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} expired temporary question image(s).");

        return self::SUCCESS;
    }
}
