<?php
require_once __DIR__ . "/../../config/db.php";

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM tva WHERE id=?");
        $stmt->execute([$id]);
    }
}
 header("Location: /eaglesuite/index.php?page=parametres&tab=tva");
exit;
