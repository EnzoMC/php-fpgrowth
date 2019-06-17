<?php


namespace EnzoMC\PhpFPGrowth;

use stdClass;

class FPNode extends stdClass
{
    /**
     * Create the node.
     */
    function __construct($value, $count, $parent)
    {
        $this->value = $value;
        $this->count = $count;
        $this->parent = $parent;
        $this->link = null;
        $this->children = [];
    }

    /**
     * Check if node has a particular child node.
     */
    function has_child($value)
    {
        foreach ($this->children as $node) {
            if (($node->value == $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return a child node with a particular value.
     */
    function get_child($value)
    {
        foreach ($this->children as $node) {
            if (($node->value == $value)) {
                return $node;
            }
        }
        return;
    }

    /**
     * Add a node as a child node.
     */
    function add_child($value)
    {
        $child = new FPNode($value, 1, $this);
        $this->children[] = $child;
        return $child;
    }
}