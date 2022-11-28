<?php

namespace Doctrine\Lexer\Benchmark;

use Doctrine\Common\Lexer\AbstractLexer;

class LexerBench
{
    private AbstractLexer $lexer;
    private $extracter;

    public function __construct()
    {
        $this->lexer = new class extends AbstractLexer
        {
            const T_UPPER =  1;
            const T_LOWER =  2;
            const T_NUMBER = 3;

            protected function getCatchablePatterns()
            {
                return array(
                    '[a-bA-Z0-9]',
                );
            }

            protected function getNonCatchablePatterns()
            {
                return array();
            }

            protected function getType(&$value)
            {
                if (is_numeric($value)) {
                    return self::T_NUMBER;
                }

                if (strtoupper($value) === $value) {
                    return self::T_UPPER;
                }

                if (strtolower($value) === $value) {
                    return self::T_LOWER;
                }
            }
        };

        $this->extracter = new class($this->lexer)
        {
            private $lexer;

            public function __construct($lexer)
            {
                $this->lexer = $lexer;
            }

            public function getUpperCaseCharacters($string)
            {
                $this->lexer->setInput($string);
                $this->lexer->moveNext();

                $upperCaseChars = array();
                while (true) {
                    if (!$this->lexer->lookahead) {
                        break;
                    }

                    $this->lexer->moveNext();

                    if ($this->lexer->token['type'] === 1) {
                        $upperCaseChars[] = $this->lexer->token->value;
                    }
                }

                return $upperCaseChars;
            }
        };
    }

    /**
     * @Revs(100000)
     * @RetryThreshold(10.0)
     */
    public function benchLexer(): void
    {
        $this->extracter->getUpperCaseCharacters('1aBcdEfgHiJ12');
    }
}
