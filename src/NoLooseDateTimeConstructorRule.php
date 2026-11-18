<?php

/**
 * PHPStan rule: no new DateTime('...') — use createFromFormat or Functions helpers.
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class NoLooseDateTimeConstructorRule implements Rule
{
    /** @var list<string> */
    private const DATE_TIME_CLASSES = [
        'DateTime',
        'DateTimeImmutable',
        '\\DateTime',
        '\\DateTimeImmutable',
    ];

    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @param Node $node
     * @param Scope $scope
     *
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof New_) {
            return [];
        }

        if (!$this->isDateTimeClass($node)) {
            return [];
        }

        if ([] === $node->args) {
            return [];
        }

        if ($this->isUnixTimestampConstructor($node)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Do not pass strings to the DateTime constructor; use DateTime::createFromFormat(), Functions::createDateTimeFromFormat(), or new DateTime() for the current moment.'
            )
                ->identifier('timmit.noLooseDateTimeConstructor')
                ->build(),
        ];
    }

    private function isDateTimeClass(New_ $node): bool
    {
        if (!$node->class instanceof Name) {
            return false;
        }

        return in_array($node->class->toString(), self::DATE_TIME_CLASSES, true);
    }

    private function isUnixTimestampConstructor(New_ $node): bool
    {
        $firstArg = $node->args[0]->value ?? null;
        if (!$firstArg instanceof String_) {
            return false;
        }

        return str_starts_with($firstArg->value, '@');
    }
}
