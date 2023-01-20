<?php

namespace App\Console\Commands;

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

class NeoLTC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "neo:ltc";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command description";

    protected $etherScan;
    protected Client $client;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         * TODO
         * 1. Связать адреса друг с другом когда происходит передача между адресами
         * 2. Связывать между собой адреса, если они в одной транзакции в отправителях
         *
         * Transaction 0be78de53fbde6472ab530a87eb7291a7a1e3d4d79be1bb8fb35097ec9bb11ec
         * Save done. T1: 10.974581003189. T2: 10.9792740345.
         *
         * Transaction 0be78de53fbde6472ab530a87eb7291a7a1e3d4d79be1bb8fb35097ec9bb11ec
         * Save done. T1: 11.297525167465. T2: 11.320382118225.
         *
         */
        $this->client = ClientBuilder::create()
            ->withDriver("neo4j", "neo4j://localhost?database=neo4j", Authenticate::basic("neo4j", "zx87cv54"))
            ->build();

        $address = "ltc1qsek999jt4au7ewjlq88pc0vr4jkgs25t3ragn3";;
        /** @var DepthSync $depthSync */
        $depthSync = DepthSync::where('address', $address)
            ->where('current_depth', 0)
            ->first();

        if (!$depthSync) {
            $this->error('No depth sync');
            return;
        }

        $this->saveTx($depthSync->addressModel);

        $depthSync->children->each(function (DepthSync $sync) {
            if ($sync->child_addresses > $sync->limit_addresses) {
                return;
            }
            $this->saveTx($sync->addressModel);
        });
    }

    private function saveTx(Address $address)
    {
        $transactions = $address->transactions()->with(["outputs", "inputs"])->get();
        if ($address->blockchain_transactions > 100) {
            $this->warn('Skip address ' . $address->address);
            return;
        }
        $this->info('Save tx for ' . $address->address);

        foreach ($transactions as $transaction) {
            $t = microtime(true);
            $tsx = $this->client->beginTransaction();

            /** @var Transaction $transaction */
            $this->info('Transaction ' . $transaction->hash);

            $statement =
                'MERGE (tx:Transaction {hash: $tx.hash})
                        ON CREATE SET
                           tx.block_hash = $tx.blockHash,
                           tx.block_number = $tx.blockNumber,
                           tx.fee = $tx.fee,
                           tx.amount = $tx.amount,
                           tx.added_at = $tx.addedAt,
                           tx.total_inputs = $tx.totalInputs,
                           tx.total_outputs = $tx.totalOutputs';

            $parameters = [];
            $parameters['tx'] = [
                "hash" => $transaction->hash,
                "blockHash" => $transaction->block_hash,
                "blockNumber" => $transaction->block_number,
                "fee" => $transaction->fee,
                "amount" => $transaction->amount,
                "addedAt" => $transaction->added_at->toDateTimeString(),
                "totalInputs" => $transaction->total_inputs,
                "totalOutputs" => $transaction->total_outputs
            ];

            $tsx->runStatement(Statement::create($statement, $parameters));

            foreach ($transaction->outputs as $output) {
                /** @var TransactionOutput $output */
                $key = "out" . $output->index;
                $statements = [];
                $parameters = [];
                $statements[] = "MATCH (tx: Transaction {hash: \$$key.txHash})";
                $statements[] = "MERGE ($key:Output {index: \$$key.index, tx_hash: \$$key.txHash})
                            ON CREATE SET
                                $key.value = \$$key.value";
                $statements[] = "MERGE ($key)<-[:OUTPUT]-(tx)";
                $statements[] = "MERGE ({$key}Address:Address {address: \$$key.address})";
                $statements[] = "MERGE ($key)-[:BELONGS_TO]->({$key}Address)";
                $parameters[$key] = [
                    'index' => $output->index,
                    'txHash' => $output->transaction_hash,
                    'value' => $output->value,
                    'address' => $output->address
                ];

                $statement = implode("\n", $statements);
                $tsx->runStatement(Statement::create($statement, $parameters));
            }

            foreach ($transaction->inputs as $input) {
                /** @var TransactionOutput $input */
                $key = "in" . $input->input_index;
                $statements = [];
                $parameters = [];
                $statements[] = "MATCH (tx: Transaction {hash: \$$key.inputTxHash})";
                $statements[] = "MERGE ($key:Output {index: \$$key.index, tx_hash: \$$key.txHash})
                            ON CREATE SET
                                $key.value = \$$key.value,
                                $key.input_index = \$$key.inputIndex,
                                $key.input_tx_hash = \$$key.inputTxHash
                            ON MATCH SET
                                $key.input_index = \$$key.inputIndex,
                                $key.input_tx_hash = \$$key.inputTxHash";
                $statements[] = "MERGE ($key)-[:INPUT]->(tx)";
                $statements[] = "MERGE ({$key}Address:Address {address: \$$key.address})";
                $statements[] = "MERGE ($key)-[:BELONGS_TO]->({$key}Address)";
                $parameters[$key] = [
                    'index' => $input->index,
                    'txHash' => $input->transaction_hash,
                    'value' => $input->value,
                    'inputIndex' => $input->input_index,
                    'inputTxHash' => $input->input_transaction_hash,
                    'address' => $input->address
                ];

                $statement = implode("\n", $statements);
                $tsx->runStatement(Statement::create($statement, $parameters));
            }

            $t1 = microtime(true) - $t;
            $tsx->commit();
            $t2 = microtime(true) - $t;

            if (max($t1, $t2) > 1) {
                $this->warn(sprintf('Save done. T1: %s. T2: %s.', $t1, $t2));
            } else {
                $this->info(sprintf('Save done. T1: %s. T2: %s.', $t1, $t2));
            }
        }
    }
}
