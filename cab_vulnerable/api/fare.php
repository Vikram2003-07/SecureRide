<?php
// ⚠️ VULNERABLE FARE API ENDPOINT
// This endpoint calculates fare and returns it as JSON.
// VULNERABILITY: The response can be intercepted and tampered using Burp Suite or browser DevTools.
// The booking form trusts whatever fare value is submitted — so modifying this response = free rides!

header('Content-Type: application/json');

$pickup  = isset($_GET['pickup'])  ? $_GET['pickup']  : '';
$dropoff = isset($_GET['dropoff']) ? $_GET['dropoff'] : '';

// ⚠️ VULNERABILITY: Simple client-side-style calculation — no real geocoding
// Attacker can intercept this response and change fare to 0.01
if ($pickup && $dropoff) {
    // Fake distance based on string difference (just for demo)
    $distance = abs(strlen($pickup) - strlen($dropoff)) + rand(5, 30);
    $fare = round(50 + $distance * 15, 2);
} else {
    $fare = 50.00;
    $distance = 0;
}

// ⚠️ VULNERABILITY: No authentication required to call this API
// ⚠️ VULNERABILITY: Response is not signed — can be tampered in transit
echo json_encode([
    'fare'     => $fare,
    'distance' => $distance,
    'currency' => 'INR',
    'note'     => 'WARNING: This API response can be tampered! Try intercepting with Burp Suite.'
]);
?>
