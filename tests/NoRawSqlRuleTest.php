<?php

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoRawSqlRule;

/**
 * @extends RuleTestCase<NoRawSqlRule>
 */
class NoRawSqlRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoRawSqlRule();
    }

    public function testFlagsSqlEvenWhenTheKeywordIsSplit(): void
    {
        $errors = $this->gatherAnalyserErrors([
            __DIR__ . '/Fixture/RawSqlFixture.php',
        ]);

        $this->assertSame(
            [
                20 => ['timmit.noRawSql'],
                25 => ['timmit.noRawSql'],
                30 => ['timmit.noRawSql'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
