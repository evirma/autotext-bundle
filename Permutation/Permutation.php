<?php /** @noinspection PhpUnused */

declare(strict_types=1);

namespace Evirma\Bundle\AutotextBundle\Permutation;

/**
 * Permutation algorithm on PHP
 *
 * @author     Eugene Myazin <eugene.myazin@gmail.com>
 * @since      27.08.14
 */
class Permutation
{
    private int $elements;
    private array $current;
    private array $first;
    private array $sequenceArray;

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

    function _permuteArray(array $items, array $perms = []): array
    {
        $result = array();

        for ($i = count($items) - 1; $i >= 0; --$i) {
            $newItems = $items;
            $newPerms = $perms;

            [$foo] = array_splice($newItems, $i, 1);
            array_unshift($newPerms, $foo);

            if (empty($newItems)) {
                $result[] = $newPerms;
            } else {
                $innerResult = $this->_permuteArray($newItems, $newPerms);
                foreach ($innerResult as $r) {
                    $result[] = $r;
                }
            }
        }

        return $result;
    }

    public function permuteArray(): array
    {
        return $this->_permuteArray($this->first);
    }

    /**
     * Get Next Sequence in order
     */
    public function nextSequence(array $currentSequence = []): array
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

    public function next(): array
    {
        $this->current = $this->nextSequence($this->current);
        return $this->current;
    }

    /**
     * Get by position
     */
    public function getByPos(int $position): array
    {
        return self::permutationByPos($this->current(), $position);
    }

    /**
     * Get by position
     */
    public static function permutationByPos(array $array, int $num): array
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
            $num = ($num - 1) % $factorial + 1;
        }

        return $res;
    }

    public function current(): array
    {
        return $this->current;
    }

    public function count(): int
    {
        return $this->elements;
    }

    /**
     * Factorial
     */
    private static function factorial(int $x): int
    {
        $result = ($x === 0) ? 1 : $x * self::factorial($x - 1);
        if ($result >= PHP_INT_MAX) {
            return PHP_INT_MAX;
        }
        return $result;
    }
}
