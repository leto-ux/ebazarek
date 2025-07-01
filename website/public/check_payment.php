<?php
/**
 * API Endpoint to check the payment status of an offer.
 *
 * This script is called via AJAX from the 'buy' page. It returns a JSON response.
 *
 * GET Parameters:
 * - offerId (int): The ID of the offer to check.
 * - debug (optional): If present, the JSON response will include detailed debug info.
 */

// Set the content type to JSON for all responses from this file.
header('Content-Type: application/json');

// --- BOOTSTRAPPING & CONFIGURATION ---
// Adopting the structure from your other project files for consistency.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('IN_INDEX')) {
    // Assuming these files are in the parent directory relative to `/api`
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/helpers.php'; // Assuming DB::getInstance() is here
}


// --- 1. GET AND VALIDATE OFFER ID ---
// Using filter_input for better security, as seen in your example.
$offerId = filter_input(INPUT_GET, 'offerId', FILTER_VALIDATE_INT);

if (!$offerId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid offerId']);
    exit;
}


// --- 2. FETCH OFFER DETAILS FROM DATABASE ---
// Using the DB helper for consistency with your other scripts.
try {
    $stmt = DB::getInstance()->prepare('SELECT subwallet, price FROM Offers WHERE OfferID = ?');
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Optional: Log the detailed error to a file instead of showing the user.
    // error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    exit;
}


if (!$offer) {
    echo json_encode(['status' => 'error', 'message' => 'Offer not found']);
    exit;
}

// Extract details and ensure the address is clean.
$address = trim($offer['subwallet']);
$requiredAmount = floatval($offer['price']);


// --- DEBUGGING SETUP ---
// Initialize debug array if the 'debug' GET parameter is set.
$debug = [];
if (isset($_GET['debug'])) {
    $debug['request'] = ['offerId' => $offerId];
    $debug['database'] = ['address' => $address, 'requiredAmount' => $requiredAmount];
}


// --- 3. GET ALL TRANSACTIONS USING LTX EXECUTABLE ---
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
        $debug['ltx_output'] = $json; // Add raw output for debugging
        $resp['debug'] = $debug;
    }
    echo json_encode($resp);
    exit;
}


// --- 4. SUM ALL INCOMING AMOUNTS FOR THE SPECIFIC ADDRESS ---
$totalReceived = 0.0;
$addressTxs = []; // For debugging

foreach ($transactions as $tx) {
    // Check if the transaction belongs to the offer's address and is an incoming payment.
    if (
        isset($tx['address'], $tx['amount']) &&
        $tx['address'] === $address &&
        floatval($tx['amount']) > 0
    ) {
        $totalReceived += floatval($tx['amount']);
        if (isset($_GET['debug'])) {
            $addressTxs[] = $tx; // Log relevant transactions for debugging
        }
    }
}

if (isset($_GET['debug'])) {
    $debug['ltx_processing'] = [
        'found_transactions' => $addressTxs,
        'totalReceived' => $totalReceived
    ];
}


// --- 5. CHECK IF ENOUGH HAS BEEN RECEIVED AND RETURN STATUS ---
$response = [];
if ($totalReceived >= $requiredAmount) {
    $response['status'] = 'paid';
} else {
    $response['status'] = 'unpaid';
}

// Attach the debug information to the final response if enabled.
if (isset($_GET['debug'])) {
    $response['debug'] = $debug;
}

echo json_encode($response);
