<?php
header("Content-Type: application/json");

$payload = [
    "item_name" => "Renouvellement abonnement",
    "item_price" => 1000,
    "command_id" => "CARD".time(),
    "success_url" => "https://votre-site.com/eaglesuite/admin/actions.php?action=webhook_payment",
    "ipn_url" => "https://votre-site.com/eaglesuite/admin/actions.php?action=webhook_payment"
];

$payload["signature"] = hash_hmac("sha256", implode("", $payload), "VOTRE_SECRET_KEY");

echo json_encode([
    "checkout_url" => "https://paytech.sn/pay?" . http_build_query($payload)
]);
