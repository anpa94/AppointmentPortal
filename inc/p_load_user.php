<?php
include 'config.php';
include 'func.php';
require_once __DIR__ . '/App/Twig.php';

$twig = new Twig(__DIR__ . '/../templates');
$content = '';
if ($_POST['search'] == '*') {
    $content = '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Fehler!</strong> Ungültige Eingabe!</div>';
} elseif (strlen($_POST['search']) < 3) {
    $content = 'Ein automatische Suche erfolgt nach der Eingabe von mind. 3 Zeichen';
} else {
    $result = search_ldap('(&(objectCategory=person)(objectClass=user)(samAccountName=' . $_POST['search'] . '*))');
    unset($result['count']);
    $content .= '<table class="table table-hover"><thead><tr><th>Username</th><th>Name</th><th>Berechtigen</th></tr></thead><tbody>';
    foreach ($result as $user) {
        $content .= '<tr><td>'.$user['samaccountname'][0].'</td><td>'.$user['cn'][0].'</td><td><button class="btn btn-success adduser" id="'.$_POST['p'].'" user="'.$user['samaccountname'][0].'"><i class="fa fa-bullhorn" aria-hidden="true"></i> Rechte vergeben</td></tr>';
    }
    $content .= '</tbody></table>';
}

echo $twig->render('p_load_user.twig', ['content' => $content]);
