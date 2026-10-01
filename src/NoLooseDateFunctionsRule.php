<?php

/**
 * PHPStan rule: avoid gmdate() and strtotime() for parsing loose date strings.
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class NoLooseDateFunctionsRule implements Rule
{
    /** @var array<string, string> */
    private const FORBIDDEN_FUNCTIONS = [
        'gmdate' => 'Use DateTime with an explicit timezone and format() instead of gmdate().',
        'strtotime' => 'Use DateTime::createFromFormat() or Functions::createDateTimeFromFormat() instead of strtotime().',
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
        if (!$node instanceof FuncCall) {
            return [];
        }

        if (!$node->name instanceof Name) {
            return [];
        }

        $functionName = $node->name->toLowerString();
        if (!isset(self::FORBIDDEN_FUNCTIONS[$functionName])) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::FORBIDDEN_FUNCTIONS[$functionName])
                ->identifier('timmit.noLooseDateFunction.' . $functionName)
                ->build(),
        ];
    }
}
