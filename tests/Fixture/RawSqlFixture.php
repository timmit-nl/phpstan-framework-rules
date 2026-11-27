<?php

/**
 * Fixture for NoRawSqlRuleTest, analysed by PHPStan and never executed.
 *
 * SQL is reported wherever PHPStan scans; there is no path filter.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests\Fixture;

/**
 * Exercises raw SQL detection, including a keyword split across fragments.
 */
class RawSqlFixture
{
    public function plainSelect(): string
    {
        return 'SELECT * FROM users';
    }

    public function splitSelect(): string
    {
        return 'SELECT' . ' * FROM users';
    }

    public function interpolatedSelect(string $columns): string
    {
        return "SELECT{$columns} FROM users";
    }

    public function keywordOnly(): string
    {
        return 'SELECT';
    }

    public function notSql(): string
    {
        return 'selected option';
    }

    public function gluedKeyword(): string
    {
        return 'SELECT' . '*';
    }
}
