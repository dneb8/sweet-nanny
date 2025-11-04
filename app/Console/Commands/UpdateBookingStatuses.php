<?php

namespace App\Console\Commands;

use App\Services\BookingStatusService;
use Illuminate\Console\Command;

class UpdateBookingStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update booking statuses based on appointment times';

    /**
     * Execute the console command.
     */
    public function handle(BookingStatusService $service): int
    {
        $this->info('Updating booking statuses...');
        
        $updatedCount = $service->updateAllStatuses();
        
        $this->info("Updated {$updatedCount} booking(s).");
        
        return Command::SUCCESS;
    }
}
