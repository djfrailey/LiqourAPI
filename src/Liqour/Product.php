<?php

namespace David\Liqour;

use David\Bag\Bag;
use \DateTime;

class Product implements ResourceInterface
{
    private $id;
    private $title;
    private $age;
    private $bottlesPerCase;
    private $description;
    private $onSale;
    private $proof;
    private $size;
    private $code;
    private $slug;
    private $createdAt;
    private $modifiedAt;
    private $uri;
    private $prices;
    private $api;

    public function __construct(
        int $id,
        string $title,
        float $age,
        int $bottlesPerCase,
        string $description,
        bool $onSale,
        float $proof,
        string $size,
        string $code,
        string $slug,
        DateTime $createdAt,
        DateTime $modifiedAt,
        string $uri,
        Api $api
    )
    {
        $this->id = $id;
        $this->title = $title;
        $this->age = $age;
        $this->bottlesPerCase = $bottlesPerCase;
        $this->description = $description;
        $this->onSale = $onSale;
        $this->proof = $proof;
        $this->size = $size;
        $this->code = $code;
        $this->slug = $slug;
        $this->createdAt = $createdAt;
        $this->modifiedAt = $modifiedAt;
        $this->uri = $uri;
        $this->api = $api;
        $this->prices = new Bag();
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getAge() : float
    {
        return $this->age;
    }

    public function getBottlesPerCase() : int
    {
        return $this->bottlesPerCase;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function isOnSale() : bool
    {
        return $this->onSale();
    }

    public function getProof() : float
    {
        return $this->proof;
    }

    public function getSize() : string
    {
        return $this->size;
    }

    public function getCode() : string
    {
        return $this->code;
    }

    public function getSlug() : string
    {
        return $this->slug;
    }

    public function getCreatedAt() : DateTime
    {
        return $this->createdAt;
    }

    public function getModifiedat() : DateTime
    {
        return $this->modifiedAt;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function getPrices() : Bag
    {
        return $this->api->getPrices(['product' => $this->id]);
    }
}
