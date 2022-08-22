<?php namespace Ascend\Core;

use LogicException;

class ModelChainAbstract
{
    protected string $table;

    public final function __construct() {
        if(!isset($this->table))
            throw new LogicException(get_class($this) . ' must have a $table');
    }
}