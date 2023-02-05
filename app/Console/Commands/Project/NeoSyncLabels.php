<?php

namespace App\Console\Commands\Project;

use App\Models\Workspace;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;


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
    public function handle()
    {
        $this->client = ClientBuilder::create()
            ->withDriver("neo4j", "neo4j://localhost?database=neo4j", Authenticate::basic("neo4j", "zx87cv54"))
            ->build();

        /** @var Workspace $workspace */
        $workspace = Workspace::find(1);


        $this->client->run('MATCH (l:AddressLabel) DETACH DELETE l');
        $this->client->run('MATCH (p:Project) DETACH DELETE p');

        $this->client->run('CREATE (:Project {id: $id, title: $title})', ['id' => $workspace->id, 'title' => $workspace->title]);
        $workspace->labels()->chunk(100, function (Collection $list) {
            $list->each(function (Workspace\Label $label) {
                $query = 'CREATE (al:AddressLabel {address: $address, label: $label, project_id: $project_id, description: $description, blockchain: $blockchain, tag: $tag})';
                $query .= 'WITH al MATCH (a:Address {address: $address}) MERGE (al)<-[:LABELED_BY]-(a)';
                $this->client->run($query, $label->getAttributes());
            });
        });
    }
}
