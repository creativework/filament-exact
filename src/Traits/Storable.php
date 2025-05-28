<?php

namespace CreativeWork\FilamentExact\Traits;

use CreativeWork\FilamentExact\Exceptions\ApiException;
use CreativeWork\FilamentExact\Services\Connection;

trait Storable
{
    abstract public function exists(): bool;

    /**
     * @param  array<string, mixed>  $attributes
     */
    abstract protected function fill(array $attributes);

    abstract public function json(int $options = 0, bool $withDeferred = false): string;

    abstract public function connection(): Connection;

    abstract public function url(): string;

    /**
     * @return mixed
     */
    abstract public function primaryKeyContent();

    /**
     * @return $this
     *
     * @throws ApiException
     */
    public function save(): self
    {
        if ($this->exists()) {
            $this->fill($this->update());
        } else {
            $this->fill($this->insert());
        }

        return $this;
    }

    /**
     * @return array|mixed
     *
     * @throws ApiException
     */
    public function insert()
    {
        return $this->connection()->post($this->url(), $this->json(0, true));
    }

    /**
     * @return array|mixed
     *
     * @throws ApiException
     */
    public function update()
    {
        $primaryKey = $this->primaryKeyContent();

        return $this->connection()->put($this->url()."(guid'$primaryKey')", $this->json());
    }

    /**
     * @return array|mixed
     *
     * @throws ApiException
     */
    public function delete()
    {
        $primaryKey = $this->primaryKeyContent();

        return $this->connection()->delete($this->url()."(guid'$primaryKey')");
    }
}
