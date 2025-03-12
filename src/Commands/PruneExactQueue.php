<?php

namespace creativework\FilamentExact\Commands;

use Illuminate\Console\Command;

class PruneExactQueue extends Command
{
    public $signature = 'exact:prune';

    public $description = 'Prune old exact queue records';

    public function handle()
    {
        $class = config('filament-exact.model');
        $class = new $class;

        // Check if pruning is enabled
        if (! config('filament-exact.database.pruning.enabled', false)) {
            return;
        }

        $pruneAfter = config('filament-exact.database.pruning.after', 30);
        $class->where('created_at', '<=', now()->subDays($pruneAfter))->delete();

        $this->info('Old exact queue records pruned successfully');
    }
}
