<?php

namespace App\Console\Commands\Neo\Litecoin;

use App\Models\Blockchain\Litecoin\Address;
use App\Models\Blockchain\Litecoin\Transaction;
use App\Models\Blockchain\Litecoin\TransactionOutput;
use App\Models\Blockchain\DepthSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\Bolt\BoltUnmanagedTransaction;
use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Databags\Statement;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Formatter\SummarizedResultFormatter;

class Indexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "neo:ltc-indexes";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command description";

    protected Client $client;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->client = ClientBuilder::create()
            ->withDriver("neo4j", "neo4j://localhost?database=litecoin", Authenticate::basic("neo4j", "secret"))
            ->build();
        $this->client->runStatement(Statement::create(
            'CREATE CONSTRAINT FOR (a:Address) REQUIRE a.address IS UNIQUE'
        ));
        $this->client->runStatement(Statement::create(
            'CREATE CONSTRAINT FOR (tx:Transaction) REQUIRE tx.hash IS UNIQUE'
        ));
        $this->client->runStatement(Statement::create(
            'CREATE CONSTRAINT FOR (o:Output) REQUIRE (o.tx_hash,o.index) IS UNIQUE'
        ));
    }
}
