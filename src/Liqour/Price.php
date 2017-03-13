<?php

namespace David\Liqour;

use \DateTime;

class Price implements ResourceInterface
{
    private $id;
    private $amount;
    private $createdAt;
    private $modifiedAt;
    private $productUri;
    private $uri;

    public function __construct(
        int $id,
        float $amount,
        DateTime $createdAt,
        DateTime $modifiedAt,
        string $productUri,
        string $uri
    )
    {
        $this->id = $id;
        $this->amount = $amount;
        $this->createdAt = $createdAt;
        $this->modifiedAt = $modifiedAt;
        $this->productUri = $productUri;
        $this->uri = $uri;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getAmount() : float
    {
        return $this->amount;
    }

    public function getCreatedAt() : DateTime
    {
        return $this->createdAt;
    }

    public function getModifiedAt() : DateTime
    {
        return $this->modifiedAt;
    }

    public function getProductUri() : string
    {
        return $this->productUri;
    }

    public function getUri() : string
    {
        return $this->uri;
    }
}