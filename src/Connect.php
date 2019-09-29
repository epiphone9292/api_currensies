<?php

abstract class Connect
{
    /** var PDO */
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
}