<?php

/**
 * Fixture for NoGetBaseFieldOfJugglingRuleTest (group_id), analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

/**
 * Exercises getBaseFieldOf(..., 'group_id') vs getGroupIdOf().
 */
class GetBaseFieldOfGroupIdFixture
{
    public function badBare(string $administratie_relatie_id): ?string
    {
        return $this->getBaseFieldOf($administratie_relatie_id, 'group_id');
    }

    public function badWithOptionalArgs(string $administratie_relatie_id): ?string
    {
        return $this->getBaseFieldOf($administratie_relatie_id, 'group_id', false, false);
    }

    public function goodDedicatedAccessor(string $administratie_relatie_id): ?string
    {
        return $this->getGroupIdOf($administratie_relatie_id);
    }

    public function goodOtherField(string $administratie_relatie_id): ?string
    {
        return $this->getBaseFieldOf($administratie_relatie_id, 'naam');
    }

    public function badNamedField(string $administratie_relatie_id): ?string
    {
        return $this->getBaseFieldOf(field: 'group_id', id: $administratie_relatie_id);
    }

    public function unrelatedRecord(\Exception $record, string $id): ?string
    {
        return $record->getBaseFieldOf($id, 'group_id');
    }

    private function getBaseFieldOf(
        string $id,
        string $field,
        bool $force = false,
        bool $check_on_group = true,
        bool $writeNode = false
    ): ?string {
        return null;
    }

    private function getGroupIdOf(
        string $id,
        bool $force = false,
        bool $check_on_group = true,
        bool $writeNode = false
    ): ?string {
        return null;
    }
}
