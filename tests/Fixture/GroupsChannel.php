<?php

/**
 * Fixture for NoDirectSaveOnGroupsRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

use Timmit\Phpstan\Rules\Tests\Stub\Groups;

/**
 * A channel writing a group.
 */
class GroupsChannel
{
    public function writeDirectly(string $group_id): void
    {
        $Groups = new Groups();
        $Groups->save($group_id);
    }

    public function writeThroughDiffAndSave(string $group_id): void
    {
        $Groups = new Groups();
        $Groups->diffAndSave($group_id);
    }
}
