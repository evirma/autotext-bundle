<?php

namespace Evirma\Bundle\AutotextBundle\Twig;

use Evirma\Bundle\AutotextBundle\Autotext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AutotextExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            'autotext' => new TwigFilter('autotext', [$this, 'autotext'], array('is_safe' => array('html'))),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('autotext', [$this, 'autotext'], ['is_safe' => ['all']]),
        ];
    }

    /**
     * @param       $text
     * @param null  $id
     * @param array $vars
     * @return string
     */
    public function autotext($text, $id = null, $vars = [])
    {
        return Autotext::autotext($text, $id, $vars);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [new AutotextTokenParser()];
    }
}