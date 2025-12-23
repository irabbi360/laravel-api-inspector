<?php

namespace Irabbi360\LaravelApiInspector\Writers;

use Illuminate\Support\Facades\Cache;

class CacheWriter
{
    protected string $cachePrefix = 'api-inspector:';

    protected int $ttl = 3600; // 1 hour

    /**
     * Save data to cache
     */
    public function save(string $key, array $data, ?int $ttl = null): void
    {
        Cache::put(
            $this->cachePrefix.$key,
            $data,
            $ttl ?? $this->ttl
        );
    }

    /**
     * Get data from cache
     */
    public function get(string $key): ?array
    {
        return Cache::get($this->cachePrefix.$key);
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        return Cache::has($this->cachePrefix.$key);
    }

    /**
     * Delete from cache
     */
    public function delete(string $key): void
    {
        Cache::forget($this->cachePrefix.$key);
    }

    /**
     * Clear all API Inspector cache
     */
    public function clearAll(): void
    {
        $keys = Cache::tags('api-inspector')->flush();
    }

    /**
     * Save routes to cache
     */
    public function saveRoutes(array $routes): void
    {
        $this->save('routes', $routes);
    }

    /**
     * Get routes from cache
     */
    public function getRoutes(): ?array
    {
        return $this->get('routes');
    }

    /**
     * Save documentation to cache
     */
    public function saveDocs(array $docs): void
    {
        $this->save('docs', $docs);
    }

    /**
     * Get documentation from cache
     */
    public function getDocs(): ?array
    {
        return $this->get('docs');
    }
}
