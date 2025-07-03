<?php
header('Content-Type: application/json');

// 1. Get offer ID from request
$offerId = $_GET['offerId'] ?? null;
if (!$offerId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing offerId']);
    exit;
}

require_once __DIR__ . '/../config.php'; // adjust path as needed
$db = new PDO('sqlite:/var/www/website/instance/bazabazarka.sqlite');
$stmt = $db->prepare('SELECT subwallet, price FROM offers WHERE id = ?');
$stmt->execute([$offerId]);
$offer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$offer) {
    echo json_encode(['status' => 'error', 'message' => 'Offer not found']);
    exit;
}

$address = trim($offer['subwallet']);
$requiredAmount = floatval($offer['price']);

// Debug info array
$debug = [];
if (isset($_GET['debug'])) {
    $debug['address'] = $address;
    $debug['requiredAmount'] = $requiredAmount;
}

// 3. Get all transactions using ltx
$cmd = '/var/www/website/bin/ltx --listtransactions';
exec($cmd, $output, $return_var);

if ($return_var !== 0) {
    echo json_encode(['status' => 'error', 'message' => 'ltx command failed']);
    exit;
}

$json = implode("\n", $output);
$data = json_decode($json, true);

if (!is_array($data)) {
    $resp = ['status' => 'error', 'message' => 'Invalid ltx output'];
    if (isset($_GET['debug'])) $resp['debug'] = $debug;
    echo json_encode($resp);
    exit;
}

// 4. Sum all positive amounts for the address
$totalReceived = 0.0;
$addressTxs = [];
foreach ($data as $tx) {
    if (
        isset($tx['address'], $tx['amount']) &&
        $tx['address'] === $address
    ) {
        $addressTxs[] = $tx;
        if ($tx['amount'] > 0) {
            $totalReceived += floatval($tx['amount']);
        }
    }
}
if (isset($_GET['debug'])) {
    $debug['addressTxs'] = $addressTxs;
    $debug['totalReceived'] = $totalReceived;
}

// 5. Check if enough has been received
if ($totalReceived >= $requiredAmount) {
    $resp = ['status' => 'paid'];
    if (isset($_GET['debug'])) $resp['debug'] = $debug;
    echo json_encode($resp);
} else {
    $resp = ['status' => 'unpaid'];
    if (isset($_GET['debug'])) $resp['debug'] = $debug;
    echo json_encode($resp);
}
