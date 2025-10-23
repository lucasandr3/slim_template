<?php

declare(strict_types=1);

namespace App\Http\Resources;

class ResourceCollection
{
    protected $resource;
    protected $resourceClass;
    protected $meta = [];

    public function __construct($resource, string $resourceClass)
    {
        $this->resource = $resource;
        $this->resourceClass = $resourceClass;
    }

    /**
     * Transforma a coleção em array
     */
    public function toArray(): array
    {
        $data = [];
        
        foreach ($this->resource as $item) {
            $data[] = (new $this->resourceClass($item))->toArray();
        }

        return [
            'data' => $data,
            'meta' => $this->meta
        ];
    }

    /**
     * Adiciona metadados
     */
    public function withMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Adiciona paginação
     */
    public function paginate(int $currentPage, int $perPage, int $total): self
    {
        $lastPage = (int) ceil($total / $perPage);
        
        $this->meta = array_merge($this->meta, [
            'pagination' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => ($currentPage - 1) * $perPage + 1,
                'to' => min($currentPage * $perPage, $total),
                'has_more_pages' => $currentPage < $lastPage
            ]
        ]);

        return $this;
    }

    /**
     * Adiciona links de paginação
     */
    public function withLinks(array $links): self
    {
        $this->meta = array_merge($this->meta, [
            'links' => $links
        ]);

        return $this;
    }

    /**
     * Cria uma coleção estática
     */
    public static function make($resource, string $resourceClass): self
    {
        return new static($resource, $resourceClass);
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
