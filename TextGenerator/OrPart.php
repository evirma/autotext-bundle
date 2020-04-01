<?php

namespace Evirma\Bundle\AutotextBundle\TextGenerator;

use Evirma\Bundle\AutotextBundle\Permutation\Exception;
use Evirma\Bundle\AutotextBundle\Permutation\Permutation;

class OrPart extends XorPart
{
    /**
     * Word delimiter
     *
     * @var string
     */
    private $delimiter = ' ';

    /**
     * Последовательность, в которой будут следовать фразы шаблона при генерации
     * @var array
     */
    private $currentTemplateKeySequence;

    /**
     * Массив последовательностей слов, из которых будут формироваться фразы
     * @var array
     */
    private $sequenceArray = array();

    /**
     * @var Permutation
     */
    private $permutation;

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
        } catch (Exception $e) {
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
    public function getCount()
    {
        $repeats = $this->getReplacementCount();
        return $this->getTemplateCount() * $repeats;
    }

    /**
     * @return int
     */
    public function getTemplateCount()
    {
        return $this->factorial(count(reset($this->sequenceArray)));
    }

    /**
     * Смещает текущую последрвательность ключей массива шаблона на следующую
     *
     * @return array
     */
    public function next()
    {
        $key = implode('', $this->currentTemplateKeySequence);
        if (!isset($this->sequenceArray[$key]) || !($nextSequence = $this->sequenceArray[$key])) {
            $nextSequence              = $this->permutation->nextSequence($this->currentTemplateKeySequence);
            $this->sequenceArray[$key] = $nextSequence;
        }
        $this->currentTemplateKeySequence = $nextSequence;
        return $nextSequence;
    }


    public function getCurrentTemplate()
    {
        $templateKeySequence = $this->currentTemplateKeySequence;

        $templateArray = $this->template;
        for ($i = 0, $count = count($templateKeySequence); $i < $count; $i++) {
            $templateKey             = $templateKeySequence[$i];
            $templateKeySequence[$i] = $templateArray[$templateKey];
        }

        return implode($this->delimiter, $templateKeySequence);
    }

    /**
     * @param null $seed
     * @return string
     */
    public function getRandomTemplate($seed = null)
    {
        if ($seed) mt_srand(abs(crc32($seed.'_orPartRandom')));
        $templates = $this->template;

        $order = array_map(function () {return mt_rand();}, range(1, count($templates)));
        array_multisort($order, $templates);

        $result = [];
        $templateArray = $this->template;
        for ($i = 0, $count = count($this->currentTemplateKeySequence); $i < $count; $i++) {
            $templateKey             = $this->currentTemplateKeySequence[$i];
            $result[$i] = $templateArray[$templateKey];
        }

        return implode($this->delimiter, $result);
    }
}