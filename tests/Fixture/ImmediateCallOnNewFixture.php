<?php

/**
 * Fixture for NoImmediateCallOnNewRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

use DateTime;

/**
 * Exercises a direct call on new, and a plain instantiation that must stay allowed.
 */
class ImmediateCallOnNewFixture
{
    public string $label = 'fixture';

    public function chained(): string
    {
        return (new DateTime())->format('Y-m-d');
    }

    public function plain(): DateTime
    {
        $date = new DateTime();

        return $date;
    }

    public function property(): string
    {
        return (new self())->label;
    }
}
