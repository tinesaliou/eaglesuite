<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        $stmt = $conn->query("SELECT id, nom FROM unites ORDER BY nom");
        respond(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'add':
        $data = input_json();
        $stmt = $conn->prepare("INSERT INTO unites (nom) VALUES (?)");
        $ok = $stmt->execute([$data['nom'] ?? '']);
        respond(['success' => $ok]);
        break;

    default:
        respond(['success' => false, 'message' => 'Action inconnue']);
}
