<?php
// config/auth.php
// Valeurs de config pour authentification
if (defined('AUTH_CONFIG_LOADED')) return;
define('AUTH_CONFIG_LOADED', true);

define('JWT_SECRET', 'change_this_secret_in_production'); // pour API JWT si besoin
define('DEFAULT_ROLE_ADMIN', 'admin');
