<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/func.php';
require_once __DIR__ . '/App/SiteLoader.php';

$site = isset($_GET['m']) ? $_GET['m'] : 'home';
$loader = new SiteLoader();
$loader->render($site);
