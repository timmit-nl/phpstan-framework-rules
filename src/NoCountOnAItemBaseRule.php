<?php

/**
 * PHPStan rule: use isLoaded($id) instead of count() or Functions::isPopulatedArray() on ::$array[$id]['base'].
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class NoCountOnAItemBaseRule implements Rule
{
    private const MESSAGE = 'Use $Entity->isLoaded($id) instead of count() or Functions::isPopulatedArray() on ...::$array[$id][\'base\']. Instantiate the aItem and pass the id.';

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
        if ($node instanceof FuncCall && $node->name instanceof Name && 'count' === $node->name->toLowerString()) {
            return $this->checkLoadedProbe($node->args[0]->value ?? null, 'timmit.noCountOnAItemBase');
        }

        if ($node instanceof StaticCall && $this->isFunctionsPopulatedArrayCall($node)) {
            return $this->checkLoadedProbe($node->args[0]->value ?? null, 'timmit.noPopulatedArrayOnAItemBase');
        }

        return [];
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    private function checkLoadedProbe(?Node $argument, string $identifier): array
    {
        if (null === $argument) {
            return [];
        }

        $target = $this->unwrapLoadedCheckCoalesce($argument);
        if (!$this->isAItemBaseAccess($target)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::MESSAGE)
                ->identifier($identifier)
                ->build(),
        ];
    }

    private function unwrapLoadedCheckCoalesce(Node $node): Node
    {
        if ($node instanceof Coalesce) {
            if ($node->right instanceof Array_ && [] === $node->right->items) {
                return $this->unwrapLoadedCheckCoalesce($node->left);
            }

            if ($node->right instanceof ConstFetch && 'null' === $node->right->name->toLowerString()) {
                return $this->unwrapLoadedCheckCoalesce($node->left);
            }
        }

        return $node;
    }

    private function isAItemBaseAccess(Node $node): bool
    {
        if (!$node instanceof Node\Expr\ArrayDimFetch) {
            return false;
        }

        if (!$node->dim instanceof String_ || 'base' !== $node->dim->value) {
            return false;
        }

        if (!$node->var instanceof Node\Expr\ArrayDimFetch) {
            return false;
        }

        if (!$node->var->var instanceof StaticPropertyFetch) {
            return false;
        }

        $staticProperty = $node->var->var;
        if (!$staticProperty->name instanceof Identifier || 'array' !== $staticProperty->name->toString()) {
            return false;
        }

        return true;
    }

    private function isFunctionsPopulatedArrayCall(StaticCall $call): bool
    {
        if (!$call->name instanceof Identifier || 'isPopulatedArray' !== $call->name->toString()) {
            return false;
        }

        if (!$call->class instanceof Name) {
            return false;
        }

        $className = $call->class->toString();

        return 'Functions' === $className || str_ends_with($className, '\\Functions');
    }
}
