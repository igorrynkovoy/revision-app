<?php

namespace App\Services\GraphResponse;

class Graph
{
    private array $nodes = [];
    private array $edges = [];

    public function createNode(string $id = null, array $attributes = [])
    {
        return new GraphNode($this, $id, $attributes);
    }

    public function createEdge($source, $target, string $id = null, array $attributes = [])
    {
        return new GraphEdge($source, $target, null, $attributes);
    }

    public function addNode(GraphNode $graphNode)
    {
        $this->nodes[$graphNode->getId()] = $graphNode;
    }

    public function addEdge(GraphEdge $graphEdge)
    {
        $this->edges[$graphEdge->getId()] = $graphEdge;
    }

    public function getNode(string $id): ?GraphNode
    {
        return $this->nodes[$id] ?? null;
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function toArray(): array
    {
        $result = [
            'nodes' => [],
            'edges' => []
        ];

        foreach ($this->nodes as $nodeId => $node) {
            $result['nodes'][$nodeId] = $node->toArray();
        }

        foreach ($this->edges as $edgeId => $edge) {
            $result['edges'][$edgeId] = $edge->toArray();
        }

        return $result;
    }
}
