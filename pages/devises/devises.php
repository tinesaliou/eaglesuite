<?php
require_once __DIR__ . "/../../config/db.php";
$title = "Gestion des devises";

// Ajouter une devise
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ajouter"])) {
    $stmt = $conn->prepare("INSERT INTO devises (code, nom, symbole, taux_par_defaut, actif) VALUES (?,?,?,?,1)");
    $stmt->execute([$_POST["code"], $_POST["nom"], $_POST["symbole"], $_POST["taux_par_defaut"]]);
}

// Supprimer
if (isset($_GET["supprimer"])) {
    $stmt = $conn->prepare("DELETE FROM devises WHERE id=?");
    $stmt->execute([$_GET["supprimer"]]);
}

// Liste
$devises = $conn->query("SELECT * FROM devises ORDER BY code")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
  <h2>Gestion des devises</h2>

  <form method="POST" class="row g-3 mb-4">
    <div class="col-md-2"><input type="text" name="code" class="form-control" placeholder="Code (ex: USD)" required></div>
    <div class="col-md-3"><input type="text" name="nom" class="form-control" placeholder="Nom complet" required></div>
    <div class="col-md-2"><input type="text" name="symbole" class="form-control" placeholder="Symbole" required></div>
    <div class="col-md-2"><input type="number" step="0.0001" name="taux_par_defaut" class="form-control" placeholder="Taux" required></div>
    <div class="col-md-2"><button type="submit" name="ajouter" class="btn btn-success">Ajouter</button></div>
  </form>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr><th>Code</th><th>Nom</th><th>Symbole</th><th>Taux (vers CFA)</th><th>Actif</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php foreach($devises as $d): ?>
      <tr>
        <td><?= htmlspecialchars($d["code"], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($d["nom"], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($d["symbole"], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($d["taux_par_defaut"], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= $d["actif"] ? "✅" : "❌" ?></td>
        <td>
          <a href="?supprimer=<?= $d["id"] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">Supprimer</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
