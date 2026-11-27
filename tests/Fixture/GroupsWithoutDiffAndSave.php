<?php

/**
 * Fixture for NoDirectSaveOnGroupsRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests\Fixture;

/**
 * A Groups class that has no diffAndSave() is not the framework group record.
 */
class Groups
{
    public function save(string $group_id): void
    {
    }
}

class GroupsWithoutDiffAndSave
{
    public function write(string $group_id): void
    {
        $groups = new Groups();
        $groups->save($group_id);
    }
}
