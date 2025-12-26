<?php
// rest_api/modules/auth.php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';

$action = $_GET['action'] ?? 'login';
$d = input_json();

switch($action) {

    // LOGIN
    case 'login':
        $email = $d['email'] ?? '';
        $password = $d['mot_de_passe'] ?? '';

        $stmt = $conn->prepare("SELECT id, nom, email, mot_de_passe, role_id, actif FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            if ($user['actif'] != 1) {
                respond(["success"=>false, "message"=>"Compte désactivé"]);
            } else {
                // renvoyer uniquement les infos utiles
                unset($user['mot_de_passe']);
                respond(["success"=>true, "utilisateur"=>$user]);
            }
        } else {
            respond(["success"=>false, "message"=>"Email ou mot de passe incorrect"]);
        }
        break;

    // REGISTER
    case 'register':
        $nom = $d['nom'] ?? '';
        $email = $d['email'] ?? '';
        $password = $d['mot_de_passe'] ?? '';

        if (!$email || !$password || !$nom) {
            respond(["success"=>false, "message"=>"Tous les champs sont requis"]);
        }

        // Vérifier si email existe déjà
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            respond(["success"=>false, "message"=>"Email déjà utilisé"]);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role_id, actif, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $ok = $stmt->execute([$nom, $email, $hash, 5, 1]);

        if ($ok) {
            $id = $conn->lastInsertId();
            $stmt2 = $conn->prepare("SELECT id, nom, email, role_id, actif FROM utilisateurs WHERE id = ?");
            $stmt2->execute([$id]);
            $user = $stmt2->fetch(PDO::FETCH_ASSOC);
            respond(["success"=>true, "utilisateur"=>$user]);
        } else {
            respond(["success"=>false, "message"=>"Impossible de créer le compte"]);
        }
        break;

    default:
        respond(["success"=>false,"message"=>"Action inconnue"],400);
}
