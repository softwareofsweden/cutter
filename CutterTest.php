<?php

include_once "Cutter.php";

/* Standard sized pieces of material */
$sizes = [3600, 4200, 4800];

/* Pieces of specified size and quantity */
$cutsAndQty = [
    2250 => 23,
    475 => 12,
    570 => 3,
    550 => 6,
];

$cutter = new Cutter($sizes, $cutsAndQty);
$result = $cutter->calculate();
$cutter->debugPrint($result);
