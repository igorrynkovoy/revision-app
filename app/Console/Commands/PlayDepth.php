<?php

namespace App\Console\Commands;

use App\Models\Blockchain;
use App\Services\Sync\DepthSync;
use Illuminate\Console\Command;

class PlayDepth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play-depth {address} {depth?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(DepthSync\Service $service)
    {
        $address = $this->argument('address');
        $depth = $this->argument('depth');

        /** @var Blockchain\Litecoin\Address $address */
        $address = Blockchain\Litecoin\Address::firstOrCreate(['address' => $address]);

        if (is_null($depth)) {
            if (!$this->confirm('Create new depth sync for ' . $address->address . '?')) {
                return;
            }

            $depth = (int)$this->ask('Specify max depth.', 3);
            $limitAddress = (int)$this->ask('Specify address limit.', 10);
            $limitAddress = min($limitAddress, 20);
            $depthSync = $service->create($address, $depth, $limitAddress, 100);

            $this->info(sprintf(
                'Depth sync created for address %s with ID %s. Max depth: %s. Limit address: %s. Limit TXs: %s.',
                $address->address,
                $depthSync->id,
                $depthSync->max_depth,
                $depthSync->limit_addresses,
                $depthSync->limit_transactions,
            ));
        } else {
            if (!$this->confirm(sprintf('Address: %s. Depth: %s.', $address->address, (int)$depth))) {
                return;
            }
            $depthSync = Blockchain\DepthSync::where('blockchain', 'LTC')->where('address', $address->address)->whereNull('root_sync_id')->orderBy('processed')->first();
            $service->handleRootOnDepth($depthSync, (int)$depth);
        }

        return;


        $address = 'MBNqJixLVjJXi3NfU1chuRng5jTmUaTNVm';

        // Lvl 1
        $address = 'MLbcgKSE6rB3WwBzvbWnr365yrZxYLRJVH';
        $address = 'MU4wn25Yufux57VYNYJfEWvMLTQXig2sR4';
        $address = 'MSJwEh6RLfHRktK8g5vN4mDrz9u8UHqJQG';
        $address = 'LiLkLvkKG2RyTissGPPGdpqnPaW3fjHSj4';

        // Lvl 2
        $address = 'MWSu23jHcfTcDFv9tKbNc636dSk2F7ZDsq';
        $address = 'MHbUKEGWZLjznw7WCs3BLdEimnxKExoLRn';
        $address = 'MUFnxNfQVsaksemjiD83neNSjPX7kJGV5c';

        // Lvl 3
        $address = 'MGPQJ5Bj9WrMjoSodSPY8e7joqefuwY3hw';
        $address = 'MDJjQabQCbAraxFiRGJHEgQLdEAcKtfycK';

        /**
         * 1. Нужно написать Job для синхронизации адреса лайткоина и навести порядок, чтобы структура была одинаковая у эфира и лайта
         * 2. При создании DepthSync нужно запустить синхронизацию адреса
         * 3. Как то надо сделать, чтобы после успешной синхронизации вызывалась логика которая бы обновляла данные. Можно вести счетчик синков и когда синзронизированы все адреса, то выполняем переход к следующей глубине
         * Один из вариантов, у родительского DepthSync записывать общее количество адресов для синка. И с каждым синком проверять, не достигли ли максимального значения
         * Другой вариант, создать цепочку задач на синхронизацию адресов и по окончанию - вызов задачи перехода к новому уровню.
         * Но что тогда делать, если какая то из задача по синку зафейлится?
         */
    }


}
