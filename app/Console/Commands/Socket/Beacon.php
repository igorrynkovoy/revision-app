<?php

namespace App\Console\Commands\Socket;

use Illuminate\Console\Command;

class Beacon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket:beacon';

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
        event(new \App\Events\Socket\Beacon());
    }
}
