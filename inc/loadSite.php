<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/func.php';
require_once __DIR__ . '/App/Twig.php';
require_once __DIR__ . '/App/Template.php';
require_once __DIR__ . '/App/SiteLoader.php';

$loader = new SiteLoader();
echo $loader->render(isset($_GET['m']) ? $_GET['m'] : 'home');
