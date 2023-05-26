<?php

namespace App\Console\Commands;

use App\Models\Blockchain\Litecoin\Transaction;
use App\Models\Blockchain\Litecoin\TransactionOutput;
use Illuminate\Console\Command;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Databags\Statement;

class Neo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected Client $client;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->client = ClientBuilder::create()
            //->withDriver("neo4j", "neo4j://localhost:7687?database=neo4j", Authenticate::basic("laravel", "zx87cv54"))
            ->withDriver("neo4j", "http://57.128.75.50:7474?database=revision-igor", Authenticate::basic("neo4j", "zx87cv54"))
            ->build();

        /** @var Transaction $transaction */
        $transaction = Transaction::where('hash', '2f3a50b2bf0b5d6ae4cd619a0b3fef2d7cd38891300bfaeb4148a6e903f50732')->first();
        $transaction = Transaction::where('hash', '02145c7a72820cf858fefb30ecac743801a42b3503338870a3a71d1daca45732')->first();
        $this->saveTx($transaction);
    }

    private function saveTx(Transaction $transaction)
    {
        $this->info('Save TX: ' . $transaction->hash);

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

        $this->client->runStatement(Statement::create($statement, $parameters));

        $t = microtime(true);
        $this->saveInputs($transaction);
        dump(microtime(true) - $t);

        $t = microtime(true);
        $this->saveOutputs($transaction);
        dump(microtime(true) - $t);
    }

    private function saveInputs(Transaction $transaction)
    {
        $parameters = [
            'inputs' => []
        ];

        // $inputs = $transaction->inputs->unique('address');
        foreach ($transaction->inputs as $input) {
            /** @var $input TransactionOutput */
            $this->info('Save input ' . $input->input_index);
            $params = [
                'inputTxHash' => $input->input_transaction_hash,
                'inputIndex' => $input->input_index,
                'address' => $input->address,
                'inputValue' => $input->value,
                'previousTxHash' => $input->transaction_hash,
                'previousTxOutput' => $input->index
            ];
            $parameters['inputs'][] = $params;
        }

        $statement = "UNWIND \$inputs AS input\n";
        $statement .= "MERGE (tx:Transaction {hash: input.inputTxHash})\n";
        $statement .= "MERGE (a:Address {address: input.address})\n";
        $statement .= "MERGE (tx)<-[:AS_INPUT {index: input.inputIndex, tx_hash: input.inputTxHash, value: input.inputValue, previous_tx_hash: input.previousTxHash, previous_tx_output: input.previousTxOutput}]-(a)";

        $this->client->runStatement(new Statement($statement, $parameters));
    }

    private function saveOutputs(Transaction $transaction)
    {
        $parameters = [
            'outputs' => []
        ];

        foreach ($transaction->outputs as $output) {
            /** @var $output TransactionOutput */
            $this->info('Save output ' . $output->input_index);
            $params = [
                'txHash' => $output->transaction_hash,
                'outputIndex' => $output->index,
                'address' => $output->address,
                'value' => $output->value
            ];
            $parameters['outputs'][] = $params;
        }

        $statement = "UNWIND \$outputs AS output\n";
        $statement .= "MERGE (tx:Transaction {hash: output.txHash})\n";
        $statement .= "MERGE (a:Address {address: output.address})\n";
        $statement .= "MERGE (tx)-[:AS_OUTPUT {index: output.outputIndex, value: output.value}]->(a)";

        $this->client->runStatement(new Statement($statement, $parameters));
    }
}
