<?php

function sendWhatsApp($phone, $message) {

    if (!$phone || !$message) return false;

    // Nettoyage numéro
    $phone = preg_replace('/\D+/', '', $phone); // enlève espaces + caractères
    if (strlen($phone) < 9) return false;

    // Ajouter indicatif si absent
    if (strlen($phone) === 9) {
        $phone = "221" . $phone; 
    }

    $token   = "TON_TOKEN_WHATSAPP_API";     // <-- changer
    $phoneId = "TON_PHONE_ID_WHATSAPP";      // <-- changer

    $url = "https://graph.facebook.com/v18.0/$phoneId/messages";

    $payload = [
        "messaging_product" => "whatsapp",
        "to"   => $phone,
        "type" => "text",
        "text" => ["body" => $message]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $res = curl_exec($ch);
    $err = curl_error($ch);

    curl_close($ch);

    // log
    file_put_contents(__DIR__ . "/whatsapp_log.txt",
        "[".date('Y-m-d H:i:s')."] SEND to $phone => ".($err ?: $res)."\n",
        FILE_APPEND
    );

    return $err ? false : true;
}
