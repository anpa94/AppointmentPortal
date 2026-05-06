<?php

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

define('DEBUG', true); // Debug Modus
//Database
$db_host = "localhost";
$db_user = "appointmentportal";
$db_pw   = "appointmentportal";
$db_db   = "appointmentportal";

define('URLpath', 'https://ITbooking/'); // Pfad zum Portal


//Config END
////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Ab hier nichts ändern!


$db_link = new MySQLi($db_host, $db_user, $db_pw, $db_db);
if ($db_link->connect_errno)
{?>
      <div class="alert alert-danger alert-dismissible" role="alert">
            <strong>Fehler!</strong> Die Datenbankverbindung konnte nicht aufgebaut werden!
	  </div>
<?php
}

$query = $db_link->query("SELECT name, value FROM config WHERE category ='ldap'");
while($row = $query->fetch_object())
{
      if($row->name == 'user')
            $ldap_user = $row->value;
      if($row->name == 'pw')
            $ldap_pw = $row->value;
      if($row->name == 'dn')
            $ldap_dn = $row->value;
      if($row->name == 'host')
            $ldap_host = $row->value;
}

$user = $_SERVER['PHP_AUTH_USER'];
    if($pos = strpos($user, '\\'))
        $user = substr($user, $pos+1);


error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? 'On' : 'Off');




?>