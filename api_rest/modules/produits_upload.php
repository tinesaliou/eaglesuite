<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

$response = ['success' => false, 'message' => 'Erreur de téléversement'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['image']['name'])) {
        $targetDir = __DIR__ . '/../uploads/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $response = [
                'success' => true,
                'file' => $fileName,
                'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/eaglesuite/api_rest/' . $fileName
            ];
        } else {
            $response['message'] = "Échec du déplacement du fichier.";
        }
    } else {
        $response['message'] = "Aucune image reçue.";
    }
}

echo json_encode($response);
