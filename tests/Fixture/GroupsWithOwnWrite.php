<?php

/**
 * Fixture for NoDirectSaveOnGroupsRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests\Fixture;

use TiMMiT\PHPStan\Rules\Tests\Stub\Groups;

/**
 * A group writing itself, both from diffAndSave() and from other methods.
 */
class GroupsWithOwnWrite extends Groups
{
    /**
     * @param string $group_id
     *
     * @return string[]
     */
    public function diffAndSave(string $group_id): array
    {
        $this->save($group_id);

        return [];
    }

    public function correct(string $group_id): void
    {
        $this->save($group_id);
    }

    public function correctViaParent(string $group_id): void
    {
        parent::save($group_id);
    }
}
