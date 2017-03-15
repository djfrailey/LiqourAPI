<?php

namespace David\Liqour;

interface ResourceInterface
{
    /**
     * Returns the URI of this resource.
     * @return string
     */
    public function getUri() : string;

    /**
     * Returns the numerical Id of this resource.
     * @return int
     */
    public function getId() : int;
}
