<?php

/**
 * Fixture for NoClosureRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

/**
 * Exercises classical closures and first-class callables.
 */
class ClosureFixture
{
    public function bad(): void
    {
        $this->run(function (): void {
        });
    }

    public function good(): void
    {
        $this->run($this->handle(...));
    }

    public function handle(): void
    {
    }

    private function run(callable $callback): void
    {
        $callback();
    }
}
