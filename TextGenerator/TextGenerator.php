<?php

declare(strict_types=1);

namespace Evirma\Bundle\AutotextBundle\TextGenerator;

class TextGenerator
{
    protected static array $replaceList = [];

    /**
     * Factory method
     *
     * @param       $template
     * @param array $options
     *
     * @return OrPart|Part|XorPart
     */
    public static function factory($template, array $options = array()): Part|OrPart|XorPart
    {
        $template = (string)$template;

        if ($replaceList = self::getReplaceList()) {
            $template = str_replace(array_keys($replaceList), array_values($replaceList), $template);
        }

        /** @noinspection RegExpRedundantEscape */
        if (preg_match_all('#[\[{]((?:[^\[{\]}]+|(?R))*)[\]}]#', $template) > 1) {
            return new Part($template, $options);
        }

        if (mb_strpos($template, '{', 0, 'UTF-8') === 0) {
            $template = mb_substr($template, 1, -1, 'UTF-8');
            return new XorPart($template, $options);
        }

        if (mb_strpos($template, '[', 0, 'UTF-8') === 0) {
            $template = mb_substr($template, 1, -1, 'UTF-8');
            return new OrPart($template, $options);
        }

        return new Part($template, $options);
    }

    /**
     * @return array
     */
    public static function getReplaceList(): array
    {
        return self::$replaceList;
    }

    public static function addReplaceList($array): void
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                self::addReplace($k, $v);
            }
        }
    }

    /**
     * Add replace
     *
     * @param $name
     * @param $value
     */
    public static function addReplace($name, $value): void
    {
        self::$replaceList['%' . trim($name, '%') . '%'] = (string)$value;
    }
}
