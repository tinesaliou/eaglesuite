<?php
require_once __DIR__ . "/../../config/db.php";
session_start();

$pin = $_POST['pin'] ?? '';

$stmt = $conn->prepare("
    SELECT id, pos_pin, pos_pin_tentatives, pos_pin_blocage
    FROM utilisateurs WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['pos_pin']) {
    die("PIN non configuré");
}

if ($user['pos_pin_blocage'] && strtotime($user['pos_pin_blocage']) > time()) {
    die("Compte temporairement bloqué");
}

if (!password_verify($pin, $user['pos_pin'])) {

    $conn->prepare("
        UPDATE utilisateurs
        SET pos_pin_tentatives = pos_pin_tentatives + 1,
            pos_pin_blocage = IF(pos_pin_tentatives >= 2, DATE_ADD(NOW(), INTERVAL 5 MINUTE), NULL)
        WHERE id = ?
    ")->execute([$_SESSION['user_id']]);

    header("Location: index.php?page=pos-pin&err=1");
    exit;
}

// PIN OK
$conn->prepare("
    UPDATE utilisateurs
    SET pos_pin_tentatives = 0, pos_pin_blocage = NULL
    WHERE id = ?
")->execute([$_SESSION['user_id']]);

$_SESSION['pos_auth'] = true;
$_SESSION['pos_last_activity'] = time();

header("Location: index.php?page=pos");
exit;
