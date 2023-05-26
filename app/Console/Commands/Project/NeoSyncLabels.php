<?php

namespace App\Console\Commands\Project;

use App\Models\Workspace;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Databags\Statement;


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
            //->withDriver("neo4j", "neo4j://localhost:7687?database=neo4j", Authenticate::basic("laravel", "zx87cv54"))
            ->withDriver("neo4j", "http://57.128.75.50:7474?database=revision-igor", Authenticate::basic("neo4j", "zx87cv54"))
            ->build();

        /** @var Workspace $workspace */
        $workspace = Workspace::find(1);

        $this->client->run('MATCH (l:Label) DETACH DELETE l');
        $this->client->run('MATCH (w:Workspace) DETACH DELETE w');

        $this->client->run('CREATE (:Workspace {id: $id, title: $title})', ['id' => $workspace->id, 'title' => $workspace->title]);

        $workspace->labels()
            ->where('type', 'address')
            ->where('blockchain', 'LTC')
            ->chunk(100, function (Collection $list) use ($workspace) {
                $this->saveLabels($workspace, $list);
            });
    }

    private function saveLabels(Workspace $workspace, Collection $list)
    {
        $parameters = [
            'labels' => []
        ];
        foreach ($list as $label) {
            /** @var $label Workspace\Label */
            $parameters['labels'][] = [
                'address' => $label->key,
                'label' => $label->label,
                'tag' => $label->tag,
                'blockchain' => $label->blockchain,
                'description' => $label->description,
                'workspace_id' => $label->workspace_id
            ];
        }

        $statement = "MATCH (w:Workspace {id: $workspace->id})\n";
        $statement .= "UNWIND \$labels AS label\n";
        $statement .= "CREATE (l:Label {label: label.label, type: 'address', key: label.address, tag: label.tag, description: label.description, blockchain: label.blockchain, workspace_id: w.id })\n";
        $statement .= "WITH l, label, w MERGE (l)-[:BELONGS_TO]->(w) WITH l, label, w  MATCH (a:Address {address: label.address}) MERGE (l)<-[:LABELED_BY]-(a)";

        $this->client->runStatement(new Statement($statement, $parameters));
    }
}
