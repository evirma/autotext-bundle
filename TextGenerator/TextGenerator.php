<?php

declare(strict_types=1);

namespace Evirma\Bundle\AutotextBundle\TextGenerator;

class TextGenerator
{
    protected static array $replaceList = [];

    public static function factory(string $template, array $options = []): Part|OrPart|XorPart
    {
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

    public static function getReplaceList(): array
    {
        return self::$replaceList;
    }

    public static function addReplaceList(array $array): void
    {
        foreach ($array as $k => $v) {
            self::addReplace($k, $v);
        }
    }

    public static function addReplace(string $name, string $value): void
    {
        self::$replaceList['%' . trim($name, '%') . '%'] = $value;
    }
}
