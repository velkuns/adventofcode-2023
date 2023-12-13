<?php

/*
 * Copyright (c) Deezer
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service;

/**
 * @template T
 */
class Cache
{
    /** @var T[] */
    private array $cache = [];

    /**
     * @param T $value
     */
    public function set(string|int $key, mixed $value): void
    {
        $this->cache[$key] = $value;
    }

    public function has(string|int $key): bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * @return T|null
     */
    public function get(string|int $key): mixed
    {
        return $this->cache[$key] ?? null;
    }
}
