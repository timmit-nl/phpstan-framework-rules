<?php

/**
 * PHPStan rule: pass an aItem's id, never the aItem instance.
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Reports parameters and properties typed as an aItem.
 *
 * aItem keeps loaded rows in a static $array, so a fresh instance created where the row is
 * needed sees the same data. Handing the instance around only couples the caller to it.
 *
 * A class counts when its own short name is aItem, or an ancestor's is, and that class has a
 * static $array and getSingle(). Gull\Framework\aItem and TiMMiT\Mosquito\aItem match the same
 * way. Facades and API clients do not extend aItem and are left alone.
 *
 * @implements Rule<Node>
 */
class NoAItemInSignatureRule implements Rule
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
        $errors = [];
        if ($node instanceof FunctionLike) {
            foreach ($node->getParams() as $param) {
                $aItemName = $this->aItemNameOf($param->type, $scope);
                if (null !== $aItemName) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf('Do not type-hint aItem %s in a signature. Pass the id and instantiate it where the row is needed.', $aItemName)
                    )
                        ->identifier('timmit.noAItemInSignature')
                        ->line($param->getStartLine())
                        ->build();
                }
            }
        }

        if ($node instanceof Property) {
            $aItemName = $this->aItemNameOf($node->type, $scope);
            if (null !== $aItemName) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf('Do not keep aItem %s in a property. Keep the id and instantiate it where the row is needed.', $aItemName)
                )
                    ->identifier('timmit.noAItemProperty')
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * Name of the first aItem class in a declared type, or null when the type holds none.
     *
     * @param Node\Identifier|Node\Name|Node\ComplexType|null $typeNode
     */
    private function aItemNameOf(?Node $typeNode, Scope $scope): ?string
    {
        $aItemName = null;
        foreach ($this->namesIn($typeNode) as $name) {
            $type = $scope->getFunctionType($name, false, false);
            foreach ($type->getObjectClassReflections() as $classReflection) {
                if (null === $aItemName && $this->isAItem($classReflection)) {
                    $aItemName = $classReflection->getName();
                }
            }
        }

        return $aItemName;
    }

    /**
     * Class names in a declared type, with nullable, union and intersection types taken apart.
     *
     * @return list<Node\Name>
     */
    private function namesIn(?Node $typeNode): array
    {
        $names = [];
        if ($typeNode instanceof Node\Name) {
            $names[] = $typeNode;
        }
        if ($typeNode instanceof Node\NullableType) {
            $names = $this->namesIn($typeNode->type);
        }
        if ($typeNode instanceof Node\UnionType || $typeNode instanceof Node\IntersectionType) {
            foreach ($typeNode->types as $memberType) {
                $names = array_merge($names, $this->namesIn($memberType));
            }
        }

        return $names;
    }

    /**
     * True when this class or an ancestor is named aItem and holds rows in a static $array.
     */
    private function isAItem(ClassReflection $classReflection): bool
    {
        $current = $classReflection;
        while ($current instanceof ClassReflection) {
            if (
                'aItem' === $this->shortNameOf($current)
                && $current->hasStaticProperty('array')
                && $current->hasMethod('getSingle')
            ) {
                return true;
            }

            $current = $current->getParentClass();
        }

        return false;
    }

    private function shortNameOf(ClassReflection $classReflection): string
    {
        $name = $classReflection->getName();
        $separator = strrpos($name, '\\');
        if (false === $separator) {
            return $name;
        }

        return substr($name, $separator + 1);
    }
}
