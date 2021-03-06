<?php

declare(strict_types = 1);

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @deprecated use "implode_call" instead
 */
final class ImplodeCallFixer extends AbstractFixer implements DeprecatedFixerInterface
{
    /** @var FixerInterface */
    private $fixer;

    public function __construct()
    {
        $this->fixer = new \PhpCsFixer\Fixer\FunctionNotation\ImplodeCallFixer();
    }

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Function `implode` must be called with 2 arguments in the documented order.',
            [new CodeSample('<?php
implode($foo, "") . implode($bar);
')],
            null,
            'When the function `implode` is overridden.'
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $this->fixer->isCandidate($tokens);
    }

    public function isRisky(): bool
    {
        return $this->fixer->isRisky();
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->fixer->fix($file, $tokens);
    }

    public function getPriority(): int
    {
        return $this->fixer->getPriority();
    }

    /**
     * @return string[]
     */
    public function getSuccessorsNames(): array
    {
        return [$this->fixer->getName()];
    }
}
