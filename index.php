<?php
require_once 'inc/config.php';
require_once 'inc/App/Twig.php';
require_once 'inc/App/Template.php';
ob_start();
require 'inc/loadSite.php';
$content = ob_get_clean();

$template = new Template();
$template->renderLayout($content);
