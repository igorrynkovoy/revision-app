<?php

namespace App\Jobs\Tools\Addresses;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetDeepNeighbors implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $toolId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $toolId)
    {
        $this->toolId = $toolId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * Если дипсинк готов, то и результат выдать можно. Поэтому Job помечаем как готовый
         *Клиент теперь должен сделать запрос на данные по джобе. Джоба направит за результатами в нужный сервс.
         * Не плохо бы нарисовать
         */
    }
}
