<?php

namespace App\Domain\Traits;

trait HasAttributes
{
    private array $attributes = [];
    
    public function __get($name): mixed {
        return $this->attributes[$name] ?? null;
    }
    public function __set($name, $value): void {
        $this->attributes[$name] = $value;
    }
}