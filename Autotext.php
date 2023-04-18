<?php

declare(strict_types=1);

namespace Evirma\Bundle\AutotextBundle;

use Evirma\Bundle\AutotextBundle\TextGenerator\Part;
use Evirma\Bundle\AutotextBundle\TextGenerator\TextGenerator;

class Autotext
{
    public static function autotext(string $text, string|int|float $seed = null, array $vars = []): string
    {
        $textGeneratorOptions = [Part::OPTION_GENERATE_RANDOM => $seed];
        $textGenerator = TextGenerator::factory($text, $textGeneratorOptions);
        $text = $seed ? $textGenerator->generateRandom((string)$seed) : $textGenerator->generate();

        return self::replaceVars($text, $vars);
    }

    public static function replaceVars(string $text, array $vars = []): string
    {
        if (empty($text) || empty($vars) || (!str_contains($text, '%'))) {
            return trim($text);
        }

        $replaces = [];
        foreach ($vars as $k => $v) {
            $replaces['%'.trim($k, ' %').'%'] = $v;
        }

        $text = strtr($text, $replaces);

        /** @noinspection PhpRegExpRedundantModifierInspection */
        $text = preg_replace('#%\s*\w+\s*%#si', '', $text);

        return trim($text);
    }
}
