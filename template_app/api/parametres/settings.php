<?php
require_once __DIR__ . "/../../config/db.php";

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$table  = $_POST['table']  ?? $_GET['table']  ?? '';

function jsonResponse($status, $data = []) {
    echo json_encode(["status" => $status, "data" => $data]);
    exit;
}

try {
    switch ($action) {
        case "list":
            $stmt = $conn->query("SELECT * FROM {$table} ORDER BY id DESC");
            jsonResponse("success", $stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case "create":
            $fields = $_POST;
            unset($fields['action'], $fields['table']);
            $columns = implode(",", array_keys($fields));
            $placeholders = ":" . implode(",:", array_keys($fields));
            $stmt = $conn->prepare("INSERT INTO {$table} ($columns) VALUES ($placeholders)");
            $stmt->execute($fields);
            jsonResponse("success", ["id" => $conn->lastInsertId()]);
            break;

        case "update":
            $id = $_POST['id'] ?? 0;
            unset($_POST['action'], $_POST['table'], $_POST['id']);
            $fields = $_POST;
            $set = implode(",", array_map(fn($k) => "$k=:$k", array_keys($fields)));
            $fields['id'] = $id;
            $stmt = $conn->prepare("UPDATE {$table} SET $set WHERE id=:id");
            $stmt->execute($fields);
            jsonResponse("success");
            break;

        case "delete":
            $id = $_POST['id'] ?? 0;
            $stmt = $conn->prepare("DELETE FROM {$table} WHERE id=?");
            $stmt->execute([$id]);
            jsonResponse("success");
            break;

        default:
            jsonResponse("error", "Action non reconnue");
    }
} catch (Exception $e) {
    jsonResponse("error", $e->getMessage());
}
