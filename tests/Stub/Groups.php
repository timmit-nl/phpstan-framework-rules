<?php

/**
 * Stand-in for a framework Groups class that records a diff before save().
 *
 * The short name is Groups and diffAndSave() exists, which is all the rule looks at.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests\Stub;

class Groups
{
    /** @var array<string, mixed> */
    public static array $array = [];

    public function save(string $group_id): void
    {
    }

    /**
     * @return string[]
     */
    public function diffAndSave(string $group_id): array
    {
        $this->save($group_id);

        return [];
    }
}
