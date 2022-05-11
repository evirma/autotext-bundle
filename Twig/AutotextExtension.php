<?php

declare(strict_types=1);

namespace Evirma\Bundle\AutotextBundle\Twig;

use Evirma\Bundle\AutotextBundle\Autotext;
use JetBrains\PhpStorm\ArrayShape;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AutotextExtension extends AbstractExtension
{
    #[ArrayShape(['autotext' => "\Twig\TwigFilter"])]
    public function getFilters(): array
    {
        return [
            'autotext' => new TwigFilter('autotext', [$this, 'autotext'], array('is_safe' => array('html'))),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('autotext', [$this, 'autotext'], ['is_safe' => ['all']]),
        ];
    }

    public function autotext(string $text, string $id = null, array $vars = []): string
    {
        return Autotext::autotext($text, $id, $vars);
    }

    public function getTokenParsers(): array
    {
        return [new AutotextTokenParser()];
    }
}
