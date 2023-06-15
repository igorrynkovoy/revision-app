<?php

namespace App\Services\GraphResponse;

class GraphEdge
{
    private GraphNode $from;
    private GraphNode $to;
    private array $attributes;
    private ?string $type = null;
    private string $id;

    public function __construct(GraphNode $from, GraphNode $to, string $id = null, array $attributes = [])
    {
        $this->from = $from;
        $this->to = $to;
        $this->id = $id ?? uuid_create();
        $this->attributes = $attributes;

        $from->getGraph()->addEdge($this);
        $this->from->addEdge($this);
        $this->to->addEdge($this);
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

    public function getSource()
    {
        return $this->from;
    }

    public function getTarget()
    {
        return $this->to;
    }

    public function getNodes()
    {
        return [$this->from, $this->to];
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'source' => $this->from->getId(),
            'target' => $this->to->getId(),
            'type' => $this->type,
            'attributes' => empty($this->attributes) ? null : $this->attributes
        ];
    }

    public function __toArray()
    {
        return $this->toArray();
    }

}
