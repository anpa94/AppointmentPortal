<?php

include("config.php");
include("func.php");
require_once "PHPExcel.php";
	$data = array("Buchungen", array("Datum", "Startzeit", "Endzeit", "Mitarbeiter", "Username", "Gebucht von", "Zusatzinfos", "Rufnummer"));
$row = $db_link->query('SELECT p.id, p.user, p.booker, p.date, p.additional_infos, t.starttime, t.endtime from _booking as p, _timeslots as t WHERE p.timeslot_id = t.id AND p.status = "1" and p.project_id = "'.$_GET['id'].'" AND p.date = "'.$_GET['date'].'" ORDER BY p.date ASC, t.starttime ASC ')->fetch_all(MYSQLI_ASSOC);
	if(isset($row['0']))
    {
        foreach($row as $valn)
            array_push($data, array($valn["date"], $valn["starttime"], $valn["endtime"], ldapFullname($valn['user']), $valn['user'],ldapFullname($valn['booker']),$valn['additional_infos'],ldapPhone($valn['user'])));
    }
export2excel("Gruppenmitglieder", FALSE, $data);
?>