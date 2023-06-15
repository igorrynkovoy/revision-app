<?php

namespace App\Services\GraphResponse;

class GraphNode
{
    private Graph $graph;
    private array $attributes;
    private string $id;
    private ?string $type = null;
    private array $edges = [];

    public function __construct(Graph $graph, string $id = null, array $attributes = [])
    {
        $this->id = $id ?? uuid_create();
        $this->graph = $graph;
        $this->attributes = $attributes;

        $this->graph->addNode($this);
    }

    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function getGraph()
    {
        return $this->graph;
    }

    public function addEdge(GraphEdge $edge)
    {
        $this->edges[$edge->getId()] = $edge;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'attributes' => empty($this->attributes) ? null : $this->attributes
        ];
    }

    public function __toArray()
    {
        return $this->toArray();
    }
}
