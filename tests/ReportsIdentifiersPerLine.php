<?php

/**
 * Shared assertion helper for PHPStan rule tests.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Analyser\Error;

/**
 * Groups analyser errors by line so a second error on the same line stays visible.
 */
trait ReportsIdentifiersPerLine
{
    /**
     * Groups error identifiers by the line they were reported on.
     *
     * A line can carry more than one identifier. Keeping every identifier
     * stops a later error from hiding an earlier one on that same line.
     *
     * @param Error[] $errors Analyser errors from one fixture file.
     *
     * @return array<int, list<string|null>> Identifiers keyed by source line, lines sorted ascending.
     */
    protected function reportedIdentifiersPerLine(array $errors): array
    {
        $reported = [];
        foreach ($errors as $error) {
            $line = (int) $error->getLine();
            if (!array_key_exists($line, $reported)) {
                $reported[$line] = [];
            }

            $reported[$line][] = $error->getIdentifier();
        }

        ksort($reported);

        return $reported;
    }
}
