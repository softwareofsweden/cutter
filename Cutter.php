<?php

class Cutter
{
    const KNIFE_WIDTH = 3;

    protected $_sizes;
    protected $_cutsAndQty;
    protected $_totalLength;

    public function __construct($sizes, $cutsAndQty)
    {
        $this->_sizes = $sizes;
        $this->_cutsAndQty = $cutsAndQty;
    }

    public function debugPrint($result)
    {
        foreach ($result['sizes_and_qty'] as $size => $qty) {
            echo $size . " x " . $qty . "\n";
        }
        foreach ($result['cutting'] as $piece) {
            echo $piece['size'] . " Cuts: " . join(',', $piece['cuts']) . " Waste: " . $piece['rest'] . "\n";
        }
        echo "Waste: " . $result['waste'] . " (" . round($result['waste'] / $result['total_length'] * 100) . "%)\n";
    }

    public function calculate($maxIterations = 1000)
    {
        // To avoid chaos...
        if ($maxIterations > 100000) {
            $maxIterations = 100000;
        }

        /*
           Make an array containing each specified size piece.
           Also calculate the total length of the specified size
           pieces. We will use this later to calculate waste.
        */
        $totalLength = 0;
        $cuts = [];
        foreach ($this->_cutsAndQty as $cut => $qty) {
            $totalLength += $cut * $qty;
            for ($i = 0; $i < $qty; $i++) {
                $cuts[] = $cut;
            }
        }

        // Initial setup
        $bestCuts = $cuts;
        $bestSizes = $this->_sizes;
        $minWaste = 1000000;

        $iteration = 1;

        while ($iteration++ < $maxIterations) {
            // Testing different combinations
            // by randomizing order of cuts
            shuffle($this->_sizes);
            shuffle($cuts);
            $result = $this->_calculate($this->_sizes, $cuts);
            if ($result['waste'] < $minWaste) {
                // Best so far... keep it
                $minWaste = $result['waste'];
                $bestCuts = $cuts;
                $bestSizes = $this->_sizes;
            }
        }

        // The final result
        $result = $this->_calculate($bestSizes, $bestCuts);
        $result['total_length'] = $totalLength;
        return $result;
    }

    protected function _calculate($sizes, $cuts)
    {
        $result = [];

        while (count($cuts) > 0) {
            $cut = array_pop($cuts);
            // Check pieces we already have
            $found = false;
            foreach ($result as &$piece) {
                if ($cut <= $piece['rest']) {
                    $found = true;
                    $piece['rest'] = $piece['rest'] - $cut - self::KNIFE_WIDTH;
                    $piece['cuts'][] = $cut;
                    break;
                }
            }
            // Get a new piece
            if (!$found) {
                foreach ($sizes as $size) {
                    if ($cut <= $size) {
                        $rest = $size - $cut;
                        $result[] = [
                            'size' => $size,
                            'rest' => $rest,
                            'cuts' => [$cut],
                        ];
                        break;
                    }
                }
            }
        }

        // Sort out how many standard sized pieces we need.
        // Also calculate waste and sort cuts.
        $sizes = [];
        $totalRest = 0;
        foreach ($result as &$piece) {
            rsort($piece['cuts']);
            $totalRest += $piece['rest'];
            if (array_key_exists($piece['size'], $sizes)) {
                $sizes[$piece['size']]++;
            } else {
                $sizes[$piece['size']] = 1;
            }
        }

        // Sort by size, then by number of cuts
        usort($result, function ($p1, $p2) {
            if ($p1['size'] === $p2['size']) {
                return count($p1['cuts']) - count($p2['cuts']);
            } else {
                return $p1['size'] - $p2['size'];
            }
        });

        return [
            'waste' => $totalRest,
            'sizes_and_qty' => $sizes,
            'cutting' => $result,
        ];
    }

}


