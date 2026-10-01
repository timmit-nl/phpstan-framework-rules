<?php

/**
 * Fixture for strict DateTime review rules, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

use DateInterval;
use DateTime;
use Timmit\Phpstan\Rules\Tests\Stub\Functions;

/**
 * Exercises DateTime strict patterns for review rules.
 */
class DateTimeStrictFixture
{
    public function modifyUsage(DateTime $date): DateTime
    {
        return $date->modify('+1 month');
    }

    public function addUsage(DateTime $date): DateTime
    {
        return $date->add(new DateInterval('P1M'));
    }

    public function stringConstructor(): DateTime
    {
        return new DateTime('today');
    }

    public function emptyConstructor(): DateTime
    {
        return new DateTime();
    }

    public function parsedConstructor(string $value): ?DateTime
    {
        return DateTime::createFromFormat('Y-m-d|', $value) ?: null;
    }

    public function functionsHelper(string $value): DateTime
    {
        return Functions::createDateTimeFromFormat('Y-m-d|', $value);
    }

    public function dateFunction(): string
    {
        return date('Y-m-d');
    }

    public function strtotimeFunction(string $value): int
    {
        return strtotime($value);
    }

    public function formattedNow(): string
    {
        return (new DateTime())->format('Y-m-d');
    }

    public function unrelatedModify(\Exception $exception): \Exception
    {
        return $exception->modify('tail');
    }
}
