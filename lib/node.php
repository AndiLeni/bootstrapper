<?php

class Node
{

    public $name;
    public $edges = [];

    public function __construct(String $name)
    {
        $this->name = $name;
    }

    public function addEdge(Node $node)
    {
        array_push($this->edges, $node);
    }
}
