<?php

namespace Evirma\Bundle\AutotextBundle\Permutation;

/**
 * Permutation algorithm on PHP
 *
 * @author     Eugene Myazin <eugene.myazin@gmail.com>
 * @since      27.08.14
 */
class Permutation
{
    /**
     * @var integer
     */
    private $elements;

    /**
     * @var array
     */
    private $current;

    /**
     * @var array
     */
    private $first;

    /**
     * @var array
     */
    private $sequenceArray;

    /**
     * Permutation constructor.
     *
     * @param $elements
     * @throws Exception
     */
    public function __construct($elements)
    {
        if (!(int)$elements) {
            throw new Exception('Count of elements must be more than zero');
        }

        if ((int)$elements > 312) {
            throw new Exception('Too many elements');
        }

        $this->elements = (int)$elements;
        $this->first = $this->current = range(0, $elements - 1);
        $this->sequenceArray[0] = $this->first;
    }

    /**
     * @param       $items
     * @param array $perms
     * @return array
     */
    function _permuteArray($items, $perms = array())
    {
        $result = array();

        for ($i = count($items) - 1; $i >= 0; --$i) {
            $newitems = $items;
            $newperms = $perms;

            list($foo) = array_splice($newitems, $i, 1);
            array_unshift($newperms, $foo);

            if (empty($newitems)) {
                $result[] = $newperms;
            } else {
                $innerResult = $this->_permuteArray($newitems, $newperms);
                foreach ($innerResult as &$r) {
                    $result[] = $r;
                }
            }
        }

        return $result;
    }

    /**
     *
     * @return array
     */
    public function permuteArray()
    {
        return $this->_permuteArray($this->first);
    }

    /**
     * Get Next Sequence in order
     *
     * @param array $currentSequence
     *
     * @return array
     */
    public function nextSequence($currentSequence = null)
    {
        $sequenceLength = count($currentSequence);

        //Ищем максимальный k-индекс, для которого a[k] < a[k - 1]
        $k = null;
        for ($i = 0; $i < $sequenceLength; $i++) {
            if (isset($currentSequence[$i + 1]) && $currentSequence[$i] < $currentSequence[$i + 1]) {
                $k = $i;
            }
        }
        //Если k невозможно определить, то это конец последовательности, начинаем сначала
        if (is_null($k)) {
            //На колу мочало, начинай с начала!
            return reset($this->sequenceArray);
        }
        //Ищем максимальный l-индекс, для которого a[k] < a[l]
        $l = null;
        for ($i = 0; $i < $sequenceLength; $i++) {
            if ($currentSequence[$k] < $currentSequence[$i]) {
                $l = $i;
            }
        }
        //Если k невозможно определить (что весьма странно, k определили же), то начинаем сначала
        if (is_null($l)) {
            //На колу мочало, начинай с начала!
            return reset($this->sequenceArray);
        }
        $nextSequence = $currentSequence;
        //Меняем местами a[k] и a[l]
        $nextSequence[$k] = $currentSequence[$l];
        $nextSequence[$l] = $currentSequence[$k];

        $k2 = $k + 1;
        //Разворачиваем массив начиная с k2 = k + 1
        if ($k2 < ($sequenceLength - 1)) {
            for ($i = 0, $count = floor(($sequenceLength - $k2) / 2); $i < $count; $i++) {
                $key1 = $k2 + $i;
                $key2 = $sequenceLength - 1 - $i;
                $val1 = $nextSequence[$key1];
                $nextSequence[$key1] = $nextSequence[$key2];
                $nextSequence[$key2] = $val1;
            }
        }

        return $nextSequence;
    }

    /**
     * @return array
     */
    public function next()
    {
        $this->current = $this->nextSequence($this->current);
        return $this->current;
    }

    /**
     * Get by position
     *
     * @param $position
     *
     * @return array
     */
    public function getByPos($position)
    {
        return self::permutationByPos($this->current(), (int)$position);
    }

    /**
     * Get by position
     *
     * @param $array
     * @param $num
     *
     * @return array
     */
    public static function permutationByPos($array, $num)
    {
        $num = abs($num)+1;
        $n = count($array);
        $used = array_fill(0, $n, false);
        $res = [];

        $factorial = self::factorial($n);

        if ($num > $factorial) {
            $num = $num % $factorial;
            if ($num == 0) {
                $num = $factorial - abs($num);
            }
        }

        for ($i = 1; $i <= $n; $i++) {
            $factorial = self::factorial($n - $i);
            $blockNum = intval(($num - 1) / $factorial + 1);

            $pos = 0;
            for ($j=1; $j<count($used); $j++) {
                if (!$used[$j]) $pos++;
                if ($blockNum == $pos) break;
            }

            $res[$i-1]=$j-1;
            $used[$j] = true;
            $num = intval(($num - 1) % $factorial) + 1;
        }

        return $res;
    }

    /**
     * @return array
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->elements;
    }

    /**
     * Factorial
     *
     * @param $x
     *
     * @return int
     */
    private static function factorial($x)
    {
        $result = ($x === 0) ? 1 : $x * self::factorial($x - 1);
        if ($result >= PHP_INT_MAX) {
            return PHP_INT_MAX;
        }
        return $result;
    }
}