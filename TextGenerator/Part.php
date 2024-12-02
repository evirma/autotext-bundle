<?php

declare(strict_types=1);

namespace Evirma\Bundle\AutotextBundle\TextGenerator;

use JetBrains\PhpStorm\ArrayShape;

class Part
{
    const string OPTION_STRIP_WHITE_SPACE = 'strip_white_space';
    const string OPTION_FILTER_EMPTY_VALUES = 'filter_empty_values';
    const string OPTION_REMOVE_DUPLICATES = 'remove_duplicates';
    const string OPTION_GENERATE_RANDOM = 'generate_random';
    const string OPTION_GENERATE_HASH = 'generate_hash';

    /**
     * Шаблон для генерации
     * @see TextGenerator_Part::parseTemplate()
     */
    protected mixed $template = null;

    /**
     * Массив замен из управляющих конструкций (перестановок и переборов)
     * @var array<Part>
     */
    protected array $replacementArray = [];

    private array $options = [
        self::OPTION_STRIP_WHITE_SPACE => true,
        self::OPTION_FILTER_EMPTY_VALUES => true,
        self::OPTION_REMOVE_DUPLICATES => true,
        self::OPTION_GENERATE_HASH => null,
        self::OPTION_GENERATE_RANDOM => null
    ];

    /**
     * @param string $template - шаблон, по которому будет генерироваться текст
     * @param array $options
     */
    public function __construct(string $template, array $options = array())
    {
        $this->setOptions($options);
        $template = $this->parseTemplate($template);
        $this->template = $template['template'];
        $this->replacementArray = $template['replacement_array'];
    }

    /**
     * Парсит шаблон, заменяет все управляющие конструкции (переборы, перестановки и т.д) и получает массив типа:
     * array(
     *   'template' => 'Генератор может генерировать %%0%%',
     *   'replacement_array' => array(
     *       '%%0%%' => TextGenerator_OrPart
     *    )
     * )
     *
     * @param string $template - шаблон
     *
     * @return array
     */
    #[ArrayShape(['template' => "mixed", 'replacement_array' => "array"])]
    protected function parseTemplate(string $template): array
    {
        $replacementArray = array();

        $template = preg_replace_callback('#[\[{]((?:[^\[{\]}]+|(?R))*)[}\]]#', function ($match) use (&$replacementArray) {
            $key = '%0000' . count($replacementArray) . '%';
            $replacementArray[$key] = TextGenerator::factory((string)$match[0], $this->getOptions());
            return $key;
        }, $template);

        return array(
            'template' => $template,
            'replacement_array' => $replacementArray
        );
    }

    /**
     * Сгенерировать текст по текущему шаблону
     * @return string
     */
    public function generate(): string
    {
        $template = $this->getCurrentTemplate();
        $replacementArray = $this->getReplacementArray();

        $replacementArrayTmp = array();
        $searchArray = array();
        /**
         * @var mixed $key
         * @var array|Part[] $value
         */
        foreach ($replacementArray as $key => $value) {
            $searchArray[] = $key;
            $replacementArrayTmp[] = $value->generate();
        }
        $replacementArray = $replacementArrayTmp;

        $this->next();

        if ($searchArray) {
            return str_replace($searchArray, $replacementArray, $template);
        }
        return $template;
    }

    public function generateRandom(?string $seed = null): mixed
    {
        $template = $this->getRandomTemplate($seed);
        $replacementArray = $this->getReplacementArray();

        $replacementArrayTmp = array();
        $searchArray = array();
        /**
         * @var mixed $key
         * @var array|Part[] $value
         */
        foreach ($replacementArray as $key => $value) {
            $searchArray[] = $key;
            $replacementArrayTmp[] = $value->generateRandom($seed);
        }
        $replacementArray = $replacementArrayTmp;

        $this->next();

        if ($searchArray) {
            return str_replace($searchArray, $replacementArray, $template);
        }
        return $template;
    }

    public function getReplacementCount(): int
    {
        $repeats = 1;
        if (!empty($this->replacementArray)) {
            foreach ($this->replacementArray as $v) {
                $repeats *= $v->getCount();
            }
            return $repeats;
        } else {
            return 1;
        }
    }

    public function getCount(): int
    {
        $cnt = 1;
        if (is_array($this->template)) {
            $cnt = count($this->template);
        }

        return intval($cnt * $this->getReplacementCount());
    }

    protected function next(): void
    {
    }

    /**
     * Получить текущий шаблон, по которому будет сгенерен текст
     */
    protected function getCurrentTemplate(): string
    {
        if (is_null($this->template)) {
            return '';
        }

        if (is_string($this->template)) {
            return $this->template;
        }

        if (is_array($this->template)) {
            return implode(' ', $this->template);
        }

        return '';
    }

    public function getRandomTemplate(?string $seed = null): mixed
    {
        if (is_null($this->template)) {
            return '';
        }

        if (is_string($this->template)) {
            return $this->template;
        }

        $templatesCount = count($this->template);
        $templateKey = 0;
        if ($templatesCount > 1) {
            if ($seed) mt_srand(abs(crc32($seed . '_Part')));
            $templateKey = mt_rand(0, count($this->template) - 1);
        }
        return $this->template[$templateKey];
    }


    /**
     * Получить массив замен для шаблона
     * @return array<Part>
     */
    protected function getReplacementArray(): array
    {
        return $this->replacementArray;
    }

    /**
     * Set options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): Part
    {
        foreach ($options as $k => $v) {
            $this->setOption($k, $v);
        }
        return $this;
    }

    /**
     * Set option value
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setOption(string $name, mixed $value): Part
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Get option value be key
     *
     * @param string|null $key
     * @param mixed|null $default Default value if key don't exists
     *
     * @return array|null
     */
    public function getOption(?string $key = null, mixed $default = null): ?array
    {
        if (is_null($key)) {
            return $this->options;
        } elseif (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        return $default;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    protected function factorial(int $x): int
    {
        if ($x === 0) {
            return 1;
        } else {
            return intval($x * $this->factorial($x - 1));
        }
    }
}
