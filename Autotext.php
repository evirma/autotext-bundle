<?php

namespace Evirma\Bundle\AutotextBundle;

use Evirma\Bundle\AutotextBundle\TextGenerator\Part;
use Evirma\Bundle\AutotextBundle\TextGenerator\TextGenerator;

class Autotext
{
    /**
     * @param       $text
     * @param null  $seed
     * @param array $vars
     * @return string
     */
    public static function autotext($text, $seed = null, $vars = [])
    {
        $textGeneratorOptions = [Part::OPTION_GENERATE_RANDOM => $seed];
        $textGenerator = TextGenerator::factory($text, $textGeneratorOptions);
        $text = $seed ? $textGenerator->generateRandom($seed) : $textGenerator->generate();

        return self::replaceVars($text, $vars);
    }

    /**
     * @param $text
     * @param $vars
     * @return mixed|string
     */
    public static function replaceVars($text, $vars = null)
    {
        if (empty($text) || empty($vars) || (strpos($text, '%') === false)) {
            return trim($text);
        }

        $replaces = [];
        foreach ($vars as $k => &$v) {
            $replaces['%'.trim($k, ' %').'%'] = $v;
        }

        $text = strtr($text, $replaces);
        $text = preg_replace('#%\s*\w+\s*%#si', '', $text);

        return trim($text);
    }
}