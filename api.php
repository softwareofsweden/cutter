<?php
/**
 * GET ?q={"sizes":[...],"cutsAndQty":{"":,...}}
 */

include_once "Cutter.php";

$q = isset($_GET['q']) ? $_GET['q'] : '';
if ($q == '') {
    // Provide sample
    $sizes = [3600, 4200, 4800];
    $cutsAndQty = [
        2250 => 23,
        475 => 12,
        570 => 3,
        550 => 6,
    ];
    $json = json_encode(['sizes' => $sizes, 'cutsAndQty' => $cutsAndQty]);
    echo 'Try this<br>';
    echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['DOCUMENT_URI'] . '?q=' . $json;
    return;
}

header('Content-Type: application/json');

// Redirect all errors
set_error_handler(function() {
    echo json_encode(['error' => true]);
    die();
}, E_ALL);

// Calculate and return result
$params = json_decode($q, true);
$cutter = new Cutter($params['sizes'], $params['cutsAndQty']);
$result = $cutter->calculate(100);
echo json_encode($result);

restore_error_handler();

