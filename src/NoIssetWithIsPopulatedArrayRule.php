<?php

/**
 * PHPStan rule: do not combine !isset($x) with !Functions::isPopulatedArray($x).
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeDumper;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class NoIssetWithIsPopulatedArrayRule implements Rule
{
    private const MESSAGE = 'Use !Functions::isPopulatedArray($expr ?? null) instead of !isset($expr) || !Functions::isPopulatedArray($expr).';

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
        if (!$node instanceof BooleanOr) {
            return [];
        }

        if ($this->isIssetWithIsPopulatedArrayPair($node->left, $node->right)
            || $this->isIssetWithIsPopulatedArrayPair($node->right, $node->left)
        ) {
            return [
                RuleErrorBuilder::message(self::MESSAGE)
                    ->identifier('timmit.noIssetWithIsPopulatedArray')
                    ->build(),
            ];
        }

        return [];
    }

    private function isIssetWithIsPopulatedArrayPair(Node $left, Node $right): bool
    {
        $issetExpression = $this->getNotIssetExpression($left);
        $populatedExpression = $this->getNotIsPopulatedArrayExpression($right);

        if (null === $issetExpression || null === $populatedExpression) {
            return false;
        }

        return $this->nodesMatch($issetExpression, $populatedExpression);
    }

    private function getNotIssetExpression(Node $node): ?Node
    {
        if (!$node instanceof BooleanNot || !$node->expr instanceof Isset_) {
            return null;
        }

        if (1 !== count($node->expr->vars)) {
            return null;
        }

        return $node->expr->vars[0];
    }

    private function getNotIsPopulatedArrayExpression(Node $node): ?Node
    {
        if (!$node instanceof BooleanNot || !$node->expr instanceof StaticCall) {
            return null;
        }

        $call = $node->expr;
        if (!$this->isFunctionsIsPopulatedArrayCall($call)) {
            return null;
        }

        return $call->args[0]->value ?? null;
    }

    private function isFunctionsIsPopulatedArrayCall(StaticCall $call): bool
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

    private function nodesMatch(Node $left, Node $right): bool
    {
        $dumper = new NodeDumper([
            'dumpComments' => false,
            'dumpPositions' => false,
            'dumpOtherAttributes' => false,
        ]);

        return $dumper->dump($left) === $dumper->dump($right);
    }
}
