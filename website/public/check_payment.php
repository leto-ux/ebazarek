<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('IN_INDEX')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/helpers.php';
}


$offerId = filter_input(INPUT_GET, 'offerId', FILTER_VALIDATE_INT);

if (!$offerId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid offerId']);
    exit;
}

try {
    $stmt = DB::getInstance()->prepare('SELECT subwallet, price FROM Offers WHERE OfferID = ?');
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    exit;
}


if (!$offer) {
    echo json_encode(['status' => 'error', 'message' => 'Offer not found']);
    exit;
}

$address = trim($offer['subwallet']);
$requiredAmount = floatval($offer['price']);


$debug = [];
if (isset($_GET['debug'])) {
    $debug['request'] = ['offerId' => $offerId];
    $debug['database'] = ['address' => $address, 'requiredAmount' => $requiredAmount];
}


$cmd = '/var/www/website/bin/ltx --listtransactions';
exec($cmd, $output, $return_var);

if ($return_var !== 0) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to query blockchain transactions (ltx command failed).']);
    exit;
}

$json = implode("\n", $output);
$transactions = json_decode($json, true);

if (!is_array($transactions)) {
    $resp = ['status' => 'error', 'message' => 'Could not parse blockchain transaction data.'];
    if (isset($_GET['debug'])) {
        $debug['ltx_output'] = $json;
        $resp['debug'] = $debug;
    }
    echo json_encode($resp);
    exit;
}


$totalReceived = 0.0;
$addressTxs = [];
$txidToSave = null;

foreach ($transactions as $tx) {
    if (
        isset($tx['address'], $tx['amount']) &&
        $tx['address'] === $address &&
        floatval($tx['amount']) > 0
    ) {
        $totalReceived += floatval($tx['amount']);
        if (isset($_GET['debug'])) {
            $addressTxs[] = $tx;
        }
        if ($totalReceived >= $requiredAmount && $txidToSave === null) {
            $txidToSave = $tx['txid'];
        }
    }
}

if (isset($_GET['debug'])) {
    $debug['ltx_processing'] = [
        'found_transactions' => $addressTxs,
        'totalReceived' => $totalReceived
    ];
}


$response = [];
if ($totalReceived >= $requiredAmount) {
    $response['status'] = 'paid';
    if ($txidToSave !== null) {
        try {
            $updateStmt = DB::getInstance()->prepare('UPDATE Offers SET txid = ? WHERE OfferID = ?');
            $updateStmt->execute([$txidToSave, $offerId]);
        } catch (PDOException $e) {
            if (isset($_GET['debug'])) {
                $debug['txid_update_error'] = $e->getMessage();
            }
        }
    }
} else {
    $response['status'] = 'unpaid';
}

if (isset($_GET['debug'])) {
    $response['debug'] = $debug;
}

echo json_encode($response);
