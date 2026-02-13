<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class MapHotelStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:map-hotel-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting hotel statuses...');
        Hotel::query()->update(['status' => 0]);

        $file = public_path('template_confirmation_HBDS.xlsx');

        if (!file_exists($file)) {
            $this->error('File not found!');
            return;
        }

        $this->info('Reading Excel file...');

        // Step 1: Read Excel into array
        $rows = Excel::toCollection(null, $file)->first();

        if (!$rows || $rows->count() <= 1) {
            $this->error('No data found in file.');
            return;
        }

        $rows->shift(); // Remove header row

        $hotelCodes = [];

        foreach ($rows as $row) {

            $hotelCode = trim($row[1] ?? '');
            $status = trim($row[2] ?? 0);

            if (!$hotelCode) {
                continue;
            }

            $hotelCodes[$hotelCode] = $status ?: 0;
        }

        if (empty($hotelCodes)) {
            $this->warn('No valid hotel codes found.');
            return;
        }

        $this->info('Fetching hotels from database...');

        // Step 2: Bulk hotels update in batches
        $codes = array_keys($hotelCodes);

        foreach (array_chunk($codes, 1000) as $key => $chunk) {
            $this->info('Updating records for chunk ' . ($key + 1));
            Hotel::whereIn('code', $chunk)
                ->update(['status' => 1]);
        }

        return Command::SUCCESS;
    }
}
