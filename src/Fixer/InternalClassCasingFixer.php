<?php

declare(strict_types = 1);

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class InternalClassCasingFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Class defined internally by an extension, or the core should be called using the correct casing.',
            [new CodeSample("<?php\n\$foo = new STDClass();\n")]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $namespaces = (new NamespacesAnalyzer())->getDeclarations($tokens);

        foreach ($namespaces as $namespace) {
            $this->fixCasing($tokens, $namespace->getScopeStartIndex(), $namespace->getScopeEndIndex(), $namespace->getFullName() === '');
        }
    }

    public function getPriority(): int
    {
        return 0;
    }

    private function fixCasing(Tokens $tokens, int $startIndex, int $endIndex, bool $isInGlobalNamespace): void
    {
        for ($index = $startIndex; $index < $endIndex; $index++) {
            if (!$tokens[$index]->isGivenKind(T_STRING)) {
                continue;
            }

            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            if ($tokens[$prevIndex]->isGivenKind(T_NS_SEPARATOR)) {
                $prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
                if ($tokens[$prevIndex]->isGivenKind(T_STRING)) {
                    continue;
                }
            } elseif (!$isInGlobalNamespace) {
                continue;
            }

            if ($tokens[$prevIndex]->isGivenKind([T_AS, T_CLASS, T_CONST, T_DOUBLE_COLON, T_FUNCTION, T_OBJECT_OPERATOR, CT::T_USE_TRAIT])) {
                continue;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($index);
            if ($tokens[$nextIndex]->isGivenKind(T_NS_SEPARATOR)) {
                continue;
            }

            if (!$tokens[$prevIndex]->isGivenKind([T_NEW]) && $tokens[$nextIndex]->equals('(')) {
                continue;
            }

            $correctCase = $this->getCorrectCase($tokens[$index]->getContent());

            if ($correctCase === $tokens[$index]->getContent()) {
                continue;
            }

            $tokens[$index] = new Token([T_STRING, $correctCase]);
        }
    }

    private function getCorrectCase(string $className): string
    {
        static $classes;

        if ($classes === null) {
            $classes = [];
            foreach (\get_declared_classes() as $class) {
                if ((new \ReflectionClass($class))->isInternal()) {
                    $classes[\strtolower($class)] = $class;
                }
            }
        }

        $lowercaseClassName = \strtolower($className);

        if (!isset($classes[$lowercaseClassName])) {
            return $className;
        }

        return $classes[$lowercaseClassName];
    }
}
