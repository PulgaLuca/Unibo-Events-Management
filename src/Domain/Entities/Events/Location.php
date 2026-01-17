<?php

declare(strict_types=1);

namespace App\Domain\Entities\Events;

class Location
{
    private string $id;
    private string $country;
    private string $city;
    private ?string $description;

    public function __construct(
        string $id,
        string $country,
        string $city,
        ?string $description = null
    ) {
        $this->id = $id;
        $this->country = $country;
        $this->city = $city;
        $this->description = $description;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
