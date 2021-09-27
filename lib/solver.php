<?php

class Solver
{

    public $resolved = [];
    public $unresolved = [];


    function dep_resolve(Node $node)
    {
        // echo 'handling node ' . $node->name . '<br>';
        array_push($this->unresolved, $node);

        foreach ($node->edges as $edge) {
            // dump($edge->name);
            // dump(in_array($edge, $this->resolved));
            if (!in_array($edge, $this->resolved)) {
                if (in_array($edge, $this->unresolved)) {
                    echo 'Circular reference detected: ' . $node->name . ' -> ' . $edge->name;
                }
                $this->dep_resolve($edge, $this->resolved, $this->unresolved);
            }
        }
        if (!in_array($node, $this->resolved)) {
            array_push($this->resolved, $node);
        }

        $key = array_search($node, $this->unresolved);
        unset($this->unresolved[$key]);

        // dump($this->resolved);
    }
}
