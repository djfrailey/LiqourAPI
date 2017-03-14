<?php

namespace David\Liqour;
use \DateTime;

/**
 * Represents a store resource.
 */
class Store implements ResourceInterface
{
    private $id;
    private $key;
    private $name;
    private $address;
    private $rawAddress;
    private $phoneNumber;
    private $county;
    private $rawHours;
    private $latitude;
    private $longitude;
    private $uri;

    public function __construct(
        int $id,
        int $key,
        string $name,
        string $address,
        string $rawAddress,
        string $phoneNumber,
        string $county,
        string $rawHours,
        float $latitude,
        float $longitude,
        string $uri
    )
    {
        $this->id = $id;
        $this->key = $key;
        $this->name = $name;
        $this->address = $address;
        $this->rawAddress = $rawAddress;
        $this->phoneNumber = $phoneNumber;
        $this->county = $county;
        $this->rawHours = $rawHours;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->uri = $uri;
    }

    public function getId() : int
    {
        return $this->id;
    }
    
    public function getKey() : int
    {
        return $this->key;
    }
    
    public function getName() : string
    {
        return $this->name;
    }
    
    public function getAddress() : string
    {
        return $this->address;
    }
    
    public function getRawAddress() : string
    {
        return $this->rawAddress;
    }
    
    public function getPhoneNumber() : string
    {
        return $this->phoneNumber;
    }
    
    public function getCounty() : string
    {
        return $this->county;
    }
    
    public function getRawHours() : string
    {
        return $this->rawHours;
    }
    
    public function getLatitude() : float
    {
        return $this->latitude;
    }
    
    public function getLongitude() : float
    {
        return $this->longitude;
    }
    
    public function getUri() : string
    {
        return $this->uri;
    }
}
