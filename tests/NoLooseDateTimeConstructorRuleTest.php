<?php

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoLooseDateTimeConstructorRule;

/**
 * @extends RuleTestCase<NoLooseDateTimeConstructorRule>
 */
class NoLooseDateTimeConstructorRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoLooseDateTimeConstructorRule();
    }

    public function testFlagsStringConstructor(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/DateTimeStrictFixture.php']);

        $this->assertSame(
            [32 => ['timmit.noLooseDateTimeConstructor']],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
