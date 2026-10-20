<?php

/**
 * Stand-in for a framework aItem that keeps loaded rows in a static array.
 *
 * The short name is aItem, $array is static and getSingle() exists, which is all the rule looks at.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Stub;

abstract class aItem
{
    /** @var array<string, mixed> */
    public static array $array = [];

    public function getSingle(string $id): void
    {
    }
}
