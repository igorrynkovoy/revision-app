<?php

namespace App\Console\Commands\Project;

use App\Jobs\Fire;
use App\Models\Blockchain\Ethereum;
use App\Models\Graph\Ethereum\Address;
use App\Models\Graph\Ethereum\Transaction;
use App\Models\Project;
use App\Services\Ethereum\Services\Etherscan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Neo4j\Neo4jConnectionPool;
use Vinelab\NeoEloquent\Facade\Neo4jSchema;
use Vinelab\NeoEloquent\NeoEloquentServiceProvider;

class NeoSyncLabels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:neo-sync-labels';

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
    public function handle(Neo4jSchema $r)
    {
        $this->client = ClientBuilder::create()
            ->withDriver("neo4j", "neo4j://localhost?database=neo4j", Authenticate::basic("neo4j", "zx87cv54"))
            ->build();

        /** @var Project $project */
        $project = Project::find(1);


        $this->client->run('MATCH (l:AddressLabel) DETACH DELETE l');
        $this->client->run('MATCH (p:Project) DETACH DELETE p');

        $this->client->run('CREATE (:Project {id: $id, title: $title})', ['id' => $project->id, 'title' => $project->title]);
        $project->labels()->chunk(100, function(Collection $list) {
            $list->each(function(Project\AddressLabel $label) {
                $query = 'CREATE (al:AddressLabel {address: $address, label: $label, project_id: $project_id, description: $description, blockchain: $blockchain, tag: $tag})';
                $query .= 'WITH al MATCH (a:Address {address: $address}) MERGE (al)<-[:LABELED_BY]-(a)';
                $this->client->run($query, $label->getAttributes());
            });
        });
    }
}
