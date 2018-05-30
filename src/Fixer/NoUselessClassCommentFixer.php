<?php

declare(strict_types = 1);

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class NoUselessClassCommentFixer extends AbstractFixer
{
    public function getDefinition() : FixerDefinition
    {
        return new FixerDefinition(
            'There should be no comment like: "Class Foo\Bar".',
            [new CodeSample('<?php
/**
 * Class Foo\Bar
 * Class to do something
 */
')]
        );
    }

    public function isCandidate(Tokens $tokens) : bool
    {
        return $tokens->isAnyTokenKindsFound([T_COMMENT, T_DOC_COMMENT]);
    }

    public function fix(\SplFileInfo $file, Tokens $tokens) : void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind([T_COMMENT, T_DOC_COMMENT])) {
                continue;
            }

            if (\strpos($token->getContent(), 'Class ') === false) {
                continue;
            }

            $newContent = \preg_replace(
                '/(\*)?\h*Class\h+[A-Za-z0-1\\\\_]+\.?(\h*\R\h*|\h*$)/i',
                '',
                $token->getContent()
            );

            if ($newContent === $token->getContent()) {
                continue;
            }

            $tokens[$index] = new Token([$token->getId(), $newContent]);
        }
    }

    public function getPriority() : int
    {
        // must be run before NoEmptyPhpdocFixer, NoEmptyCommentFixer and PhpdocTrimFixer
        return 6;
    }
}