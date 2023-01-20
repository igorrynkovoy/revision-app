<?php

namespace App\Console\Commands\Project;

use App\Jobs\Fire;
use App\Models\Blockchain\Ethereum;
use App\Models\Graph\Ethereum\Address;
use App\Models\Graph\Ethereum\Transaction;
use App\Services\Ethereum\Services\Etherscan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laudis\Neo4j\Neo4j\Neo4jConnectionPool;
use Vinelab\NeoEloquent\Facade\Neo4jSchema;
use Vinelab\NeoEloquent\NeoEloquentServiceProvider;

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
    public function handle(Neo4jSchema $r)
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
