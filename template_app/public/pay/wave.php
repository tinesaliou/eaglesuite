<?php
header('Content-Type: application/json');

// Mode test Wave sandbox
echo json_encode([
    "checkout_url" => "https://checkout.sandbox.wave.com/pay?amount=5000&ref=test123"
]);
exit;
