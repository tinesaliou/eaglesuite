<?php
function whatsapp_pay_link($waNumber, $invoiceId, $clientName, $amount) {
    $msg = "Paiement facture #{$invoiceId} - {$clientName}\nMontant: ".number_format($amount,0,',',' ')." F CFA\nRéf: {$invoiceId}";
    $msgEnc = rawurlencode($msg);
    return "https://wa.me/{$waNumber}?text={$msgEnc}";
}
