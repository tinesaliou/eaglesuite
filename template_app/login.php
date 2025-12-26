<?php
//session_destroy();

session_start();

require_once __DIR__ . "/config/db.php"; // Connexion à la base

//  Si déjà connecté, redirige vers le tableau de bord
if (isset($_SESSION['user_id'])) {
    header("Location: index.php?page=dashboard");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            //  Authentification réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];

            // Charger les permissions de ce rôle
            $permStmt = $conn->prepare("
                SELECT p.code
                FROM permissions p
                INNER JOIN role_permissions rp ON rp.permission_id = p.id
                WHERE rp.role_id = ?
            ");
            $permStmt->execute([$user['role_id']]);
            $permissions = $permStmt->fetchAll(PDO::FETCH_COLUMN);

            $_SESSION['permissions'] = [];

            foreach ($permissions as $p) {
                $_SESSION['permissions'][$p] = true;
            }

            //var_dump($_SESSION);

            header("Location: index.php?page=dashboard");
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } else {
        $error = "Veuillez renseigner vos identifiants.";
    }
}
?>

<?php
// Place ici la logique PHP d'authentification (session_start, traitement POST...)
// Par exemple : session_start(); ... définir $error si nécessaire
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Connexion — EagleSuite ERP</title>


<link rel="stylesheet" href="/{{TENANT_DIR}}/public/vendor/fontawesome/css/all.min.css">
<link rel="stylesheet" href="/{{TENANT_DIR}}/public/vendor/bootstrap/css/bootstrap.min.css">

<style>
  :root{
    --bg-1: #0b1116;        /* very dark */
    --bg-2: #0f1720;        /* dark */
    --glass: rgba(255,255,255,0.04);
    --glass-2: rgba(255,255,255,0.06);
    --accent: #ff8c00;      /* primary orange (logo) */
    --accent-2: #ffb357;    /* lighter orange */
    --muted: #b8c4d9;
    --radius: 14px;
  }

  html,body{height:100%;background: radial-gradient(1200px 600px at 10% 20%, rgba(255,140,0,0.06), transparent 8%),
                           radial-gradient(900px 400px at 90% 80%, rgba(13,110,253,0.04), transparent 8%),
                           linear-gradient(180deg,var(--bg-1), var(--bg-2));
             margin:0;font-family:Inter, "Poppins", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
             -webkit-font-smoothing:antialiased;color:#fff;display:flex;align-items:center;justify-content:center;padding:24px;
  }

  /* Container */
  .auth-wrap{
    width:100%;
    max-width:1040px;
    display:grid;
    grid-template-columns: 480px 1fr;
    gap:28px;
    align-items:center;
  }

  /* Left card (login) */
  .card-auth{
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    border-radius: var(--radius);
    padding:28px;
    box-shadow: 0 12px 40px rgba(2,6,23,0.6);
    border:1px solid rgba(255,255,255,0.05);
    backdrop-filter: blur(8px) saturate(1.05);
  }

  .brand {
    display:flex;gap:14px;align-items:center;
  }
  .brand .logo {
    width:72px;height:72px;padding:8px;border-radius:12px;background:linear-gradient(135deg, rgba(255,140,0,0.12), rgba(255,140,0,0.04));
    box-shadow:0 6px 20px rgba(255,140,0,0.08), inset 0 1px 0 rgba(255,255,255,0.02);
    display:flex;align-items:center;justify-content:center;
    transition:transform .35s ease;
  }
  .brand .logo img{width:100%;height:100%;object-fit:contain;filter: drop-shadow(0 6px 14px rgba(255,140,0,0.14));}

  .brand h1{font-size:1.15rem;margin:0;color:var(--accent-2);letter-spacing:0.2px;}
  .brand p{margin:0;font-size:0.9rem;color:var(--muted);}

  .logo-pulse{
    position:relative;
  }
  .logo-pulse::after{
    content:"";
    position:absolute;inset:-18px;border-radius:14px;
    background: radial-gradient(circle at 30% 30%, rgba(255,140,0,0.12), transparent 18%),
                radial-gradient(circle at 70% 70%, rgba(255,140,0,0.08), transparent 12%);
    filter: blur(12px);opacity:0;transition:opacity .6s ease;
  }
  .brand:hover .logo-pulse::after{opacity:1;transform:scale(1.03);}

  h3.title{margin-top:18px;margin-bottom:8px;font-weight:600;color:#fff;}
  p.lead{margin:0;color:var(--muted);font-size:0.95rem;margin-bottom:18px;}

  /* form controls */
  .form-control{
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    border:1px solid rgba(255,255,255,0.06);
    color:#fff;padding:12px;border-radius:10px;
    box-shadow:none;
  }
  .form-control:focus{outline:none;box-shadow:0 6px 18px rgba(13,110,253,0.06);border-color:rgba(255,140,0,0.85);}

  .input-group-text{
    background:transparent;border:0;color:var(--muted);cursor:pointer;
  }

  .btn-accent{
    background: linear-gradient(90deg,var(--accent), var(--accent-2));
    border: none;color:#0b0b0b;font-weight:600;padding:12px;border-radius:10px;
    transition: transform .15s ease, box-shadow .15s ease;
    box-shadow: 0 8px 28px rgba(255,140,0,0.12);
  }
  .btn-accent:hover{transform:translateY(-2px);box-shadow:0 12px 34px rgba(255,140,0,0.16);}

  .text-muted-small{color:var(--muted);font-size:0.9rem;}

  .links-row{display:flex;justify-content:space-between;align-items:center;margin-top:10px;margin-bottom:6px;}
  .links-row a{color:var(--muted);text-decoration:none;font-size:0.9rem;}
  .links-row a:hover{color:var(--accent-2);text-decoration:underline;}

  .error-box{background:rgba(255,70,70,0.12);border:1px solid rgba(255,70,70,0.12);color:#ffb3b3;padding:10px;border-radius:8px;margin-bottom:12px;}

  /* right panel: visual */
  .visual{
    min-height:360px;border-radius:var(--radius);display:flex;align-items:center;justify-content:center;
    background:
      linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)),
      radial-gradient(600px 220px at 10% 20%, rgba(255,140,0,0.06), transparent 10%),
      radial-gradient(460px 180px at 90% 80%, rgba(13,110,253,0.04), transparent 10%);
    border:1px solid rgba(255,255,255,0.03);box-shadow: 0 18px 48px rgba(2,6,23,0.55);
    padding:28px;position:relative;overflow:hidden;
  }

  .visual .big-logo{
    width:260px;height:260px;border-radius:20px;
    display:flex;align-items:center;justify-content:center;
    background:linear-gradient(180deg, rgba(255,140,0,0.07), rgba(255,140,0,0.03));
    box-shadow: 0 20px 80px rgba(255,140,0,0.06), inset 0 2px 0 rgba(255,255,255,0.02);
    transition:transform .6s cubic-bezier(.2,.9,.3,1);
    border-radius:22px;
  }
  .visual .big-logo img{width:72%;height:72%;object-fit:contain;filter: drop-shadow(0 30px 60px rgba(255,140,0,0.12));}

  .visual .glow{
    position:absolute;inset:0;pointer-events:none;
    background: radial-gradient(circle at 40% 30%, rgba(255,140,0,0.16), transparent 6%),
                radial-gradient(circle at 70% 70%, rgba(255,140,0,0.08), transparent 10%);
    filter: blur(30px);mix-blend-mode:screen;opacity:0.9;
  }

  .visual:hover .big-logo{transform:translateY(-8px) scale(1.02);}

  /* Responsive */
  @media (max-width:1000px){
    .auth-wrap{grid-template-columns:1fr;gap:18px;padding:12px;}
    .visual{order:-1;height:220px;min-height:220px;border-radius:12px;padding:18px}
    .visual .big-logo{width:160px;height:160px}
  }
</style>
</head>
<body>

  <main class="auth-wrap" aria-labelledby="login-title">

    <!-- LOGIN CARD -->
    <section class="card-auth" aria-label="Formulaire de connexion">
      <div class="brand">
        <div class="logo logo-pulse" aria-hidden="true">
          <img src="/{{TENANT_DIR}}/public/icone/eaglesuite.png" alt="EagleSuite Logo">
        </div>
        <div>
          <h1 style="font-size:1rem;margin-bottom:6px;color:var(--accent);">EagleSuite</h1>
          <p class="text-muted-small">Système de gestion commerciale</p>
        </div>
      </div>

      <h3 id="login-title" class="title">Bienvenue - Connectez-vous</h3>
      <p class="lead">Accédez à votre espace. Entrez vos identifiants sécurisés.</p>

      <!-- Message d'erreur -->
      <?php if (!empty($error)): ?>
        <div class="error-box" role="alert">
          <i class="fa fa-exclamation-triangle me-2"></i>
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <label for="email" class="form-label">Adresse e-mail</label>
        <input id="email" name="email" type="email" autocomplete="username" class="form-control mb-3" placeholder="exemple@mail.com" required>

        <label for="password" class="form-label">Mot de passe</label>
        <div class="input-group mb-3">
          <input id="password" name="password" type="password" autocomplete="current-password" class="form-control" placeholder="••••••••" required aria-describedby="togglePassword">
          <span class="input-group-text" id="togglePassword" title="Afficher / masquer le mot de passe" role="button" tabindex="0" aria-label="Afficher le mot de passe">
            <i class="fa fa-eye" id="eyeIcon"></i>
          </span>
        </div>

        <div class="links-row">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
            <label class="form-check-label text-muted-small" for="remember">Se souvenir de moi</label>
          </div>
          <a href="#" class="text-muted-small">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="btn btn-accent w-100 mt-2" aria-live="polite">Se connecter</button>

        <div class="text-center mt-3 text-muted-small">
          <small>Connexion sécurisée • SSL activé</small>
        </div>
      </form>
    </section>

    <!-- VISUAL / HIGHLIGHT -->
    <aside class="visual" aria-hidden="true">
      <div class="glow" aria-hidden="true"></div>
      <div class="big-logo" role="img" aria-label="Logo EagleSuite">
        <img src="/{{TENANT_DIR}}/public/icone/eaglesuite_logo.png" alt="">
      </div>
    </aside>

  </main>

<script>
  // Toggle mot de passe
  (function(){
    const toggle = document.getElementById('togglePassword');
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');

    function togglePwd(){
      if (input.type === 'password'){ input.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
      else { input.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
    }

    toggle.addEventListener('click', togglePwd);
    toggle.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); togglePwd(); }
    });
  })();

  // small accessibility: focus first field
  window.addEventListener('DOMContentLoaded', () => {
    const mail = document.getElementById('email');
    if (mail) mail.focus({preventScroll:true});
  });
</script>

</body>
</html>
