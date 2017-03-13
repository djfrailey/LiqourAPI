<?php

namespace David\Liqour;

interface ResourceInterface
{
    public function getUri() : string;
    public function getId() : int;
}