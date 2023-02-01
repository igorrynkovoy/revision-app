<?php

namespace App\Console\Commands\Project;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLabels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:import-labels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $handle = fopen(storage_path('Litecoin_wallets.csv'), "r");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            DB::table('project_address_labels')
                ->insertOrIgnore([
                    'blockchain' => 'LTC',
                    'address' => $data[0],
                    'label' => $data[2],
                    'tag' => $data[1],
                    'project_id' => 1
                ]);
        }
        fclose($handle);
    }
}
