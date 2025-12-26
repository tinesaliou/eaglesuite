<?php
// /eaglesuite/admin/includes/breadcrumbs.php
function render_breadcrumbs($page) {
    $map = [
        'dashboard' => ['Dashboard', '/eaglesuite/admin/index.php?page=dashboard'],
        'clients' => ['Clients SaaS', '/eaglesuite/admin/index.php?page=clients'],
        'abonnements' => ['Abonnements', '/eaglesuite/admin/index.php?page=abonnements'],
        'facturation' => ['Facturation', '/eaglesuite/admin/index.php?page=facturation'],
        'renouvellement' => ['Renouvellements', '/eaglesuite/admin/index.php?page=renouvellement'],
    ];

    $crumbs = [];
    $crumbs[] = '<nav aria-label="breadcrumb" class="breadcrumb-wrap"><ol class="breadcrumb mb-0">';
    $crumbs[] = '<li class="breadcrumb-item"><a href="/eaglesuite/admin/index.php?page=dashboard">Accueil</a></li>';

    if (isset($map[$page])) {
        $crumbs[] = '<li class="breadcrumb-item active" aria-current="page">'.htmlspecialchars($map[$page][0]).'</li>';
    } else {
        // fallback: split possible subpage like client_edit
        if (strpos($page, 'client_') === 0) {
            $crumbs[] = '<li class="breadcrumb-item"><a href="/eaglesuite/admin/index.php?page=clients">Clients</a></li>';
            $crumbs[] = '<li class="breadcrumb-item active" aria-current="page">'.htmlspecialchars(ucfirst(str_replace('_',' ',$page))).'</li>';
        } else {
            $crumbs[] = '<li class="breadcrumb-item active" aria-current="page">'.htmlspecialchars(ucfirst($page)).'</li>';
        }
    }

    $crumbs[] = '</ol></nav>';
    return implode("\n", $crumbs);
}
