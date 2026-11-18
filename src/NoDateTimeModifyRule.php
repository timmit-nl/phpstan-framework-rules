<?php

/**
 * PHPStan rule: do not use DateTime::modify(); use add/sub or Functions::modifyDateTimeFromInterval().
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use DateTimeInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<Node>
 */
class NoDateTimeModifyRule implements Rule
{
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
        if (!$node instanceof MethodCall) {
            return [];
        }

        if (!$node->name instanceof Identifier || 'modify' !== $node->name->toString()) {
            return [];
        }

        /* Report modify() only when the receiver is certainly a DateTimeInterface. */
        $receiverType = $scope->getType($node->var);
        if (!(new ObjectType(DateTimeInterface::class))->isSuperTypeOf($receiverType)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Do not use DateTime::modify(); use add()/sub() with DateInterval or Functions::modifyDateTimeFromInterval() instead.'
            )
                ->identifier('timmit.noDateTimeModify')
                ->build(),
        ];
    }
}
