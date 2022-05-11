<?php

declare(strict_types=1);

namespace Evirma\Bundle\AutotextBundle\TextGenerator;

use Evirma\Bundle\AutotextBundle\Permutation\Exception;
use Evirma\Bundle\AutotextBundle\Permutation\Permutation;

class OrPart extends XorPart
{
    /**
     * Word delimiter
     */
    private string $delimiter = ' ';

    /**
     * Последовательность, в которой будут следовать фразы шаблона при генерации
     */
    private array $currentTemplateKeySequence;

    /**
     * Массив последовательностей слов, из которых будут формироваться фразы
     */
    private array $sequenceArray = [];

    private Permutation $permutation;

    public function __construct($template, array $options = array())
    {
        $template        = preg_replace_callback('#^\+([^+]+)\+#', function ($match) use (&$delimiter) {
            $delimiter = $match[1];
            return '';
        }, $template);

        parent::__construct($template, $options);

        if (isset($delimiter)) {
            $this->delimiter = $delimiter;
        }

        $itemsCount        = count($this->template);

        try {
            $this->permutation = new Permutation($itemsCount);
        } catch (Exception) {
        }

        $firstSequence     = $this->permutation->current();
        $this->sequenceArray[0]           = $firstSequence;
        $this->currentTemplateKeySequence = $firstSequence;
    }

    /**
     * Returns count of variants
     *
     * @return int
     */
    public function getCount(): int
    {
        $repeats = $this->getReplacementCount();
        return $this->getTemplateCount() * $repeats;
    }

    public function getTemplateCount(): int
    {
        return $this->factorial(count(reset($this->sequenceArray)));
    }

    /**
     * Смещает текущую последрвательность ключей массива шаблона на следующую
     */
    public function next(): void
    {
        $key = implode('', $this->currentTemplateKeySequence);
        if (!isset($this->sequenceArray[$key]) || !($nextSequence = $this->sequenceArray[$key])) {
            $nextSequence              = $this->permutation->nextSequence($this->currentTemplateKeySequence);
            $this->sequenceArray[$key] = $nextSequence;
        }
        $this->currentTemplateKeySequence = $nextSequence;
    }


    public function getCurrentTemplate(): string
    {
        $templateKeySequence = $this->currentTemplateKeySequence;

        $templateArray = $this->template;
        for ($i = 0, $count = count($templateKeySequence); $i < $count; $i++) {
            $templateKey             = $templateKeySequence[$i];
            $templateKeySequence[$i] = $templateArray[$templateKey];
        }

        return implode($this->delimiter, $templateKeySequence);
    }

    public function getRandomTemplate($seed = null): string
    {
        if ($seed) mt_srand(abs(crc32($seed.'_orPartRandom')));
        $templates = $this->template;

        $order = array_map(function () {return mt_rand();}, range(1, count($templates)));
        array_multisort($order, $templates);

        $result = [];
        $templateArray = $templates;
        for ($i = 0, $count = count($this->currentTemplateKeySequence); $i < $count; $i++) {
            $templateKey             = $this->currentTemplateKeySequence[$i];
            $result[$i] = $templateArray[$templateKey];
        }

        return implode($this->delimiter, $result);
    }
}
