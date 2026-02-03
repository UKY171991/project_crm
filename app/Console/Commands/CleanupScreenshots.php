<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupScreenshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'screenshots:cleanup';
    protected $description = 'Delete screenshots older than 15 days';

    public function handle()
    {
        $date = \Carbon\Carbon::now()->subDays(15);
        $oldScreenshots = \App\Models\Screenshot::where('captured_at', '<', $date)->get();
        
        foreach ($oldScreenshots as $ss) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($ss->path);
            $ss->delete();
        }
        
        $this->info(count($oldScreenshots) . ' screenshots deleted.');
    }
}
