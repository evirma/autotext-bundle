<?php

declare(strict_types=1);

namespace Evirma\Bundle\AutotextBundle\TextGenerator;

class XorPart extends Part
{
    /**
     * Текущий ключ массива шаблонов
     */
    private int $currentTemplateKey = 0;

    public function __construct($template, array $options = array())
    {
        parent::__construct($template, $options);
        $this->setOptions($options);
        $template = $this->parseTemplate($template);

        $this->template         = explode('|', $template['template']);
        $this->replacementArray = $template['replacement_array'];
    }

    public function next(): void
    {
        $this->currentTemplateKey++;
        if (!isset($this->template[$this->currentTemplateKey])) {
            $this->currentTemplateKey = 0;
        }
    }

    public function getCurrentTemplate(): string
    {
        return $this->template[$this->currentTemplateKey];
    }

    /**
     * @noinspection DuplicatedCode
     */
    public function getRandomTemplate(string $seed = null): mixed
    {
        $templatesCount = count($this->template);
        $templateKey = 0;
        if ($templatesCount > 1) {
            if ($seed) mt_srand(abs(crc32($seed.'_XorPartRandom')));
            $templateKey = mt_rand(0, count($this->template) - 1);
        }
        return $this->template[$templateKey];
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return count($this->template) + $this->getReplacementCount() - 1;
    }
}
