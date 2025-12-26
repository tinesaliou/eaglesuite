<?php
require_once "../../config/db.php";

$id = $_POST['id'];
$conn->prepare("DELETE FROM roles WHERE id=?")->execute([$id]);

header("Location: /{{TENANT_DIR}}/index.php?page=roles");
exit;
