<?php

/**
 * PHPUnit tests for the PHPStan rule that keeps group writes on the diffAndSave() route.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoDirectSaveOnGroupsRule;

/**
 * @extends RuleTestCase<NoDirectSaveOnGroupsRule>
 */
class NoDirectSaveOnGroupsRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoDirectSaveOnGroupsRule();
    }

    public function testAChannelHasToUseDiffAndSave(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/GroupsChannel.php']);

        /* Line 21 calls save(), line 27 calls diffAndSave(). */
        $this->assertSame([21 => ['timmit.groupsDirectSave']], $this->reportedIdentifiersPerLine($errors));
    }

    public function testOnlyDiffAndSaveMayWriteTheRecordItself(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/GroupsWithOwnWrite.php']);

        /* Line 25 is diffAndSave() writing itself; line 32 $this->save(); line 37 parent::save(). */
        $this->assertSame(
            [
                32 => ['timmit.groupsDirectSave'],
                37 => ['timmit.groupsDirectSave'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }

    public function testAGroupsClassWithoutDiffAndSaveIsLeftAlone(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/GroupsWithoutDiffAndSave.php']);

        $this->assertSame([], $this->reportedIdentifiersPerLine($errors));
    }
}
