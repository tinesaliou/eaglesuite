<?php
// rest_api/helpers.php
require_once __DIR__ . '/config/database.php';

function input_json() {
    $data = json_decode(file_get_contents("php://input"), true);
    return is_array($data) ? $data : [];
}

function respond($data, $code=200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}
