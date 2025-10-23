<?php

declare(strict_types=1);

namespace App\Http\Resources;

abstract class Resource
{
    protected $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Transforma o recurso em array
     */
    public function toArray(): array
    {
        return $this->transform($this->resource);
    }

    /**
     * Transforma o recurso (deve ser implementado pelas classes filhas)
     */
    abstract protected function transform($resource): array;

    /**
     * Retorna apenas campos específicos
     */
    public function only(array $fields): array
    {
        $data = $this->toArray();
        return array_intersect_key($data, array_flip($fields));
    }

    /**
     * Exclui campos específicos
     */
    public function except(array $fields): array
    {
        $data = $this->toArray();
        return array_diff_key($data, array_flip($fields));
    }

    /**
     * Adiciona campos extras
     */
    public function with(array $extra): array
    {
        return array_merge($this->toArray(), $extra);
    }

    /**
     * Cria um resource estático
     */
    public static function make($resource): self
    {
        return new static($resource);
    }

    /**
     * Converte para JSON
     */
    public function toJson(int $flags = 0): string
    {
        return json_encode($this->toArray(), $flags);
    }

    /**
     * Método mágico para conversão automática
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
