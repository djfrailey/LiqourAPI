<?php

namespace Djfrailey\Liqour;

use Djfrailey\Bag\Bag;
use \DateTime;

/**
 * Represents a Product resource
 */
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

    public function __construct(
        int $id              = 0,
        string $title        = "",
        float $age           = 0,
        int $bottlesPerCase  = 0,
        string $description  = "",
        bool $onSale         = false,
        float $proof         = 0,
        string $size         = "",
        string $code         = "",
        string $slug         = "",
        DateTime $createdAt  = null,
        DateTime $modifiedAt = null,
        string $uri          = ""
    ) {
    
        $this->id             = $id;
        $this->title          = $title;
        $this->age            = $age;
        $this->bottlesPerCase = $bottlesPerCase;
        $this->description    = $description;
        $this->onSale         = $onSale;
        $this->proof          = $proof;
        $this->size           = $size;
        $this->code           = $code;
        $this->slug           = $slug;
        $this->createdAt      = $createdAt;
        $this->modifiedAt     = $modifiedAt;
        $this->uri            = $uri;
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

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    public function getUri() : string
    {
        return $this->uri;
    }
}
