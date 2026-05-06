<?php
require_once 'config.php';

$site = isset($_GET['m']) ? $_GET['m'] : 'home';

if(json_decode(getConfig('authorizedGroups', 'frontend'))[0] == "ALL" || user_authorized(json_decode(getConfig('authorizedGroups', 'frontend'))))
{
    try
    {
        if(! include 'sites/'.$site . '.php')
             throw new Exception();
    }catch(Exception $e)
    {
        ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Fehler!</strong> Die angeforderte Seite gibt es nicht!
                <br>
                <strong>Error!</strong> The requested page doesn't exist!
            </div>
        <?php		
    }
}
else
{
    ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Fehler!</strong> Seite konnte nicht aufgerufen werden. Keine Berechtigung vorhanden!
                <br>
                <strong>Fehler!</strong> Page cannot be loaded. No access!
            </div>
        <?php
}