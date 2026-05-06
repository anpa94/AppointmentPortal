<?php
include "config.php";
include "func.php";

if ($_GET["mode"] == 'updateProject')
{
    if($row = $db_link->query('SELECT id, name, description, active, startdate, enddate, starttime, endtime FROM projects WHERE id = "'.$_GET["id"].'"')->fetch_object())
    {
       $query2 = $db_link->query("UPDATE `projects`
        SET
        `name`='" . utf8_encode($_GET["titel"]) . "',
        `description`='" . htmlentities(addslashes(trim($_GET["beschreibung"]))) . "',
        `startdate`='" . $_GET["startdate"] . "',
        `enddate`='" . $_GET["enddate"] . "',
        `starttime`='" . $_GET["starttime"] . "',
        `endtime`='" . $_GET["endtime"] . "'
        WHERE  `id`='" . $_GET["id"] . "'");
       $pid = $_GET["id"];
       
    }
    else
    {
        $query2 = $db_link->query("INSERT INTO `projects` (name, description, startdate, enddate, starttime, endtime) VALUES('" . utf8_encode($_GET["titel"]) . "',
        '" . htmlentities(addslashes(trim($_GET["beschreibung"]))) . "',
        '" . $_GET["startdate"] . "',
        '" . $_GET["enddate"] . "',
        '" . $_GET["starttime"] . "',
        '" . $_GET["endtime"] . "')");
        if (isset($db_link->insert_id))
        {
            $pid = $db_link->insert_id;
            $db_link->query("INSERT INTO `_authorisized_user_be` (project_id, username) VALUES('".$pid."', '" . $_GET["user"] . "')");
            $db_link->query("INSERT INTO `_weekdays` (project_id) VALUES('".$pid."')");
        }
    }
}
if ($_GET["mode"] == 'updateProjectMail')
{
	$pid = $_GET["id"];
    if($row = $db_link->query('SELECT sender, subject, body, ort FROM _emailinformation WHERE project_id = "'.$_GET["id"].'"')->fetch_object())
    {
       $query2 = $db_link->query("UPDATE `_emailinformation`
        SET
        `sender`='" . htmlentities(trim($_GET["sender"]))  . "',
        `subject`='" . htmlentities(addslashes(trim($_GET["subject"]))) . "',
        `body`='" . htmlentities(addslashes(trim($_GET["body"])))  . "',
        `ort`='" . htmlentities(addslashes(trim($_GET["ort"])))  . "'
        WHERE  `project_id`='" . $_GET["id"] . "'");
	   
	   echo "UPDATE `_emailinformation`
        SET
        `sender`='" . htmlentities(trim($_GET["sender"]))  . "',
        `subject`='" . htmlentities(addslashes(trim($_GET["subject"]))) . "',
        `body`='" . htmlentities(addslashes(trim($_GET["body"])))  . "',
        `ort`='" . htmlentities(addslashes(trim($_GET["ort"])))  . "'
        WHERE  `project_id`='" . $_GET["id"] . "'";
       $pid = $_GET["id"];
       
    }
    else
    {
        $query2 = $db_link->query("INSERT INTO `_emailinformation` (project_id, sender, subject, body, ort) VALUES('" . $_GET["id"] . "',
        '" . htmlentities(trim($_GET["sender"])) . "',
        '" . htmlentities(addslashes(trim($_GET["subject"]))) . "',
        '" . htmlentities(addslashes(trim($_GET["body"]))) . "',
        '" . htmlentities(addslashes(trim($_GET["ort"]))) . "')");
		
		echo "INSERT INTO `_emailinformation` (project_id, sender, subject, ort, body) VALUES('" . $_GET["id"] . "',
        '" . htmlentities(trim($_GET["sender"])) . "',
        '" . htmlentities(addslashes(trim($_GET["subject"]))) . "',
        '" . htmlentities(addslashes(trim($_GET["ort"]))) . "',
        '" . htmlentities(addslashes(trim($_GET["body"]))) . "')";
    }
}
if ($_GET["mode"] == 'adduser')
{
    $pid = $_GET["id"];
    if($row = $db_link->query('SELECT * FROM `_authorisized_user_be` WHERE project_id = "'.$_GET["id"].'" AND username ="'.$_GET["user"].'"')->fetch_object())
        echo'';
     else   
       $query2 = $db_link->query("INSERT INTO `_authorisized_user_be` (project_id, username) VALUES('".$_GET["id"]."', '" . $_GET["user"] . "')");
}

if ($_GET["mode"] == 'addinfo')
{
    $pid = $_GET["id"];
       $query2 = $db_link->query("INSERT INTO `_additionalinfos` (project_id, name, response, required) VALUES('".$_GET["id"]."', '" . utf8_encode($_GET["titel"]) . "','". $_GET["response"] . "','". $_GET["required"] . "')");
}
if ($_GET["mode"] == 'activate')
{
    $pid = $_GET["id"];
       $query2 = $db_link->query("UPDATE projects SET active = '".$_GET["wert"]."' WHERE id = '".$_GET["id"]."'");
}
if ($_GET["mode"] == 'addinfo_response')
{
    $pid = $_GET["id"];
       $query2 = $db_link->query("INSERT INTO `_additionalinfos_response` (add_id, name) VALUES('".$_GET["row_id"]."', '" . utf8_encode($_GET["titel"]) . "')");
}
if ($_GET["mode"] == 'addinfo_del')
{
    $pid = $_GET["id"];
       $query2 = $db_link->query("DELETE FROM  `_additionalinfos` WHERE id = '".$_GET["row_id"]."'");
}

if ($_GET["mode"] == 'addtimeslot')
{
    $pid = $_GET["id"];
    $duration = intval($_GET["time"]);
    $row = $db_link->query('SELECT starttime, endtime FROM projects WHERE id = "'.$pid.'"')->fetch_object();
	$starttime_temp = strtotime($row->starttime);
	$endtime_temp = strtotime('+'.$duration.' minutes', $starttime_temp);
    $query4 = $db_link->query("UPDATE `_timeslots` SET active = '0' WHERE project_id = '".$pid."'");
    $i = 1;
		while($endtime_temp <= strtotime($row->endtime))
		{
            $db_start = date("H:i:s",$starttime_temp);
			$db_end = date("H:i:s",$endtime_temp);
			$query2 = $db_link->query("INSERT INTO `_timeslots` (project_id, starttime, endtime) VALUES('".$pid."', '" . $db_start . "', '" . $db_end . "')");
			$starttime_temp = ($endtime_temp);
			$endtime_temp_1 = date("H:i:s",$endtime_temp);
			$endtime_temp = strtotime('+'.$duration.' minutes', $starttime_temp);
            $i++;
		}
}
if ($_GET["mode"] == 'single_maxbookchange')
{
	$pid = $_GET["id"];
	$query2 = $db_link->query("UPDATE `_timeslots` SET timeslotcount = '".$_GET['wert']."' WHERE project_id = '".$pid."' AND id='".$_GET['slotid']."'");
}
if ($_GET["mode"] == 'deletetimeslots')
{
	$pid = $_GET["id"];
	$query2 = $db_link->query("UPDATE `_timeslots` SET active = '0' WHERE project_id = '".$pid."'");
}


if ($_GET["mode"] == 'deleteuser')
{
    $pid = $_GET["id"];
    $query2 = $db_link->query("DELETE FROM `_authorisized_user_be` WHERE ID='".$_GET["row_id"]."'");
}

if ($_GET["mode"] == 'addtime_count')
{
    $pid = $_GET["id"];
    $query2 = $db_link->query("UPDATE `_timeslots` SET `timeslotcount`='" . $_GET["counter"] . "' WHERE project_id = '".$_GET["id"]."' AND active = '1'");
}
if ($_GET["mode"] == 'changeweekdates')
{
    $pid = $_GET["id"];
	$query = $db_link->query("SELECT ".$_GET['day']." AS day from `_weekdays` WHERE project_id = '".$pid."'")->fetch_object();
	$value = ($query->day == "1") ? '0' : '1';
    $query2 = $db_link->query("UPDATE `_weekdays` SET ".$_GET['day']."='" . $value . "' WHERE project_id = '".$pid."'");
}

if ($_GET["mode"] == 'blockdate')
{
    $pid = $_GET["id"];
    $query2 = $db_link->query("INSERT INTO `_blocked_dates` (project_id, date) VALUES ('" . $pid . "','".$_GET['day']."')");
}

if ($_GET["mode"] == 'unblockdate')
{
    $pid = $_GET["id"];
    $query2 = $db_link->query("DELETE FROM `_blocked_dates` WHERE project_id = '" . $pid . "' AND date = '".$_GET['day']."'");
}

if ($_GET["mode"] == 'reserve')
{
    $pid = $_GET["p"];
		$addi = str_replace('_undefined', '', $_GET['addi']);
		$addi = str_replace(':undefined', '', $addi);
		
	$query_max = $db_link->query("SELECT timeslotcount AS max_book from _timeslots WHERE project_id = '" . $pid . "' and id = '" .$_GET['slot']."'")->fetch_object();
	$query_akt = $db_link->query("SELECT count(id) AS akt_book FROM _booking WHERE project_id = '" . $pid . "' and timeslot_id = '" .$_GET['slot']."' AND date = '".$_GET['datum']."' AND status ='1'")->fetch_object();
	if ($query_max->max_book > $query_akt->akt_book)
	{
		$query2 = $db_link->query("INSERT INTO `_booking` (project_id, timeslot_id, date, user, booker, status, additional_infos) VALUES ('".$pid."', '".$_GET['slot']."', '".$_GET['datum']."', '".$_GET['user']."', '".$_GET['booker']."','1', '".$addi."')");
		sendmail($db_link->insert_id);
	}
	else
	{
		$pid = 0;
	}
	

}


if(isset($query2))
    echo $pid;
else
    echo '0';
?>

