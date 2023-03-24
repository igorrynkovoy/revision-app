<?php

namespace App\Console\Commands;

use App\Models\Blockchain\Litecoin\Address;
use App\Services\Litecoin\Syncers\ByAddress\AddressSyncer;
use Illuminate\Console\Command;

class PlayDebug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play-debug';

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
        /** @var Address $address */
        $address = Address::whereAddress('LKQY2fbwYvabCGwRa1L8DnRasgyz4gmjRU')->first();
        /**
         * Остановился на том, что при синке этого адреса падает ошибка
         * После этого надо убедиться что для дипсинка 318 на уровне 1 засинканы все адреса и посмотреть как в синке последнего адреса создается джоба ProcessDepthSync для уровня 1
         */
        $syncer = new AddressSyncer($address);
        $syncer->syncInformation();
        $syncer->sync();
        dd($address->address);
    }

}
