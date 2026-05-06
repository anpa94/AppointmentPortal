<?php

function user_authorized($groups) // Prüft ob der Benutzer in einer der angegebenen Gruppen ist. Rückgabe Boolean
{
    $authorized = TRUE;
    
    $user = $_SERVER['PHP_AUTH_USER'];
    if(substr($user, 0,4) == 'ece\\')
        $user = substr($_SERVER['PHP_AUTH_USER'],4);
    
    $filter = "(&(objectCategory=person)(objectClass=user)(samAccountName=" . $user . "))";
    $entries = search_ldap($filter);
    $userdn = $entries[0]['dn'];
    
    foreach($groups as $group)
    {
        $filter = "(&(objectCategory=group)(objectClass=group)(samAccountName=" . $group . "))";
        $entries = search_ldap($filter);
        
        if(isset($entries[0])) // Wenn Gruppe gefunden prüfe ob aktueller Benutzer enthalten ist
            foreach($entries[0]['member'] as $member) 
                if($member == $userdn)
                    $authorized = TRUE;
    }
    return $authorized;
}

function search_ldap($filter) //Führt den LDAP Query $filter aus und gibt das Ergebnis zurück
{
    $filter = str_replace('\\', '\\\\', $filter);
    global $ldap_user;
    global $ldap_pw;
    global $ldap_dn;
    global $ldap_host;

    $connect = ldap_connect($ldap_host) or exit("Keine Verbindung zu LDAP Server");

    ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

    $bind = ldap_bind($connect, $ldap_user, $ldap_pw) or exit("Verbindung kann nicht aufgebaut werden zu $ldap_host");

    $return = array();
    $pageSize = 1000; // Adjust the page size as needed
    $cookie = null;

    do {
        $read = ldap_search($connect, $ldap_dn, $filter, ["*"], 0, $pageSize, 0, LDAP_DEREF_NEVER, $cookie) or exit("Kann nicht suchen ");
        $entries = ldap_get_entries($connect, $read);

        if ($entries === false) {
            ldap_close($connect);
            exit("LDAP search failed.");
        }

        $return = array_merge($return, $entries);
        $cookie = isset($entries['pagedresult']) ? $entries['pagedresult'] : null;
    } while ($cookie !== null && $cookie != '');

    ldap_close($connect);

    return $return;
}


function getConfig($category, $name) //liest die Config aus der Datenbank aus
{
    global $db_link;
    if($row = $db_link->query('SELECT value FROM config WHERE category = "' . $category . '" AND name = "' . $name . '"')->fetch_object())
        return $row->value;
    else
        trigger_error("Dieser Konfigurationseintrag existiert nicht.", E_USER_ERROR);
}

function ldapFullname($user ='PHP_AUTH_USER') //Gibt denn vollständigen Namen des Users aus dem AD zurück
{
    if($user == 'PHP_AUTH_USER')
        $user = $_SERVER['PHP_AUTH_USER'];
    global $ldap_user;
    global $ldap_pw;
    global $ldap_dn;
    global $ldap_host;
    
    if($pos = strpos($user, '\\'))
        $user = substr($user, $pos+1);
    $filter = "(&(objectCategory=person)(objectClass=user)(samAccountName=" . $user . "))";
    $connect = ldap_connect( $ldap_host) or exit("Keine Verbindung zu LDAP Server");
        
    ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
    
    $bind = ldap_bind($connect, $ldap_user, $ldap_pw) or exit("Verbindung kann nicht aufgebaut werden zu $ldap_host");
    $read = ldap_search($connect, $ldap_dn, $filter) or exit("Kann nicht suchen ");
    $info = ldap_get_entries($connect, $read);
    ldap_close($connect);
    if (isset ($info[0]["displayname"][0]))    
		return $info[0]["displayname"][0];
	else
		return '';
    
}
function ldapPhone($user ='PHP_AUTH_USER') //Gibt denn vollständigen Namen des Users aus dem AD zurück
{
    if($user == 'PHP_AUTH_USER')
        $user = $_SERVER['PHP_AUTH_USER'];
    global $ldap_user;
    global $ldap_pw;
    global $ldap_dn;
    global $ldap_host;
    
    if($pos = strpos($user, '\\'))
        $user = substr($user, $pos+1);
    $filter = "(&(objectCategory=person)(objectClass=user)(samAccountName=" . $user . "))";
    $connect = ldap_connect( $ldap_host) or exit("Keine Verbindung zu LDAP Server");
        
    ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
    
    $bind = ldap_bind($connect, $ldap_user, $ldap_pw) or exit("Verbindung kann nicht aufgebaut werden zu $ldap_host");
    $read = ldap_search($connect, $ldap_dn, $filter) or exit("Kann nicht suchen ");
    $info = ldap_get_entries($connect, $read);
    ldap_close($connect);
    if (isset ($info[0]["telephonenumber"][0]))    
		return $info[0]["telephonenumber"][0];
	else
		return '';
    
}
function popo($text)
{
	echo' <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="tooltip" style="cursor:pointer;" title="'.trim($text).'"></i>';
}

function bookingCalendar($p, $datum)
{
	//echo'<pre>'.date("Y-m-d", $datum).'</pre>';
	global $user;
	global $db_link;
	global $language;
	$tage = $language['tage'];
	$tage_de = $language['tage_de'];
	//echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Buchungskalender</h4>';
	$p_startend = $db_link->query('SELECT startdate, enddate FROM projects WHERE id = "'.$p.'"')->fetch_object();
	$startdate = time() <= strtotime($p_startend->startdate) ? strtotime($p_startend->startdate) : time();
	$startmonday_temp = (date('w', $startdate)!=1) ? strtotime("last Monday", ($startdate)) : $startdate;
	$startmonday = ($datum !='0') ? $datum : $startmonday_temp ;
	$today = date('Y-m-d', time());
	//echo'<pre>'.date("Y-m-d", $startmonday).'</pre>';
	$nextweek = ($startmonday)+(3600 * 24 *7);
	//echo'<pre>'.date("Y-m-d", $nextweek).'</pre>';
	$weekbefore = $startmonday-(3600 * 24);
		//echo'<pre>'.date("Y-m-d", $weekbefore).'</pre>';
	//echo '<pre>'.date('Y-m-d', $startmonday).'</pre>';
		echo'<nav class="pull-left">
		<ul class="pagination">';
		if ($weekbefore > strtotime($p_startend->startdate))
		{
			echo '<li>
				<a class="weekchange"  datum="'.($startmonday-(3600 * 24 *7)).'" aria-label="Zurück"><span aria-hidden="true">&laquo;</span></a>
			</li>';
		}
		else
			echo '<li class="blocked"><a><i class="fa fa-ban" aria-hidden="true"></i></a></li>';
			echo'<li class="active">
				<a >KW '.(date('W', $startmonday)).'</a>
			</li>';
		if($nextweek < strtotime($p_startend->enddate))
		{
		    echo '<li>
				<a class="weekchange" datum="'.$nextweek.'" aria-label="Weiter"><span aria-hidden="true">&raquo;</span></a>
			</li>';
		}
		else
			echo '<li class="blocked"><a><i class="fa fa-ban" aria-hidden="true"></i></a></li>';
		echo '</ul>
	</nav>';
	echo '<p class="text-muted pull-right clearfix"><i class="fa fa-user-o" aria-hidden="true"></i> '.$language['freeslot'].' || <i class="fa fa-user" aria-hidden="true"></i> '.$language['reservedslot'].'</p>';
	echo '<table class="table table-sm cal"><thead><tr><th style="text-align:center; width:12.5% !important;">'.$language['dateblock'].'</th>';
	$i=0;
	$weekdates = array();
	while($i<7)
	{

		echo '<th style="text-align:center; width:12.5% !important;">'.date('d.m.y', $startmonday).'<br><small class="weekday">'.$tage[date("w",$startmonday)].'</small></th>';
		array_push($weekdates, $startmonday);
		$startmonday = $startmonday+(3600 * 24);
		$i++;
	}
	echo '</tr></thead><tbody>';
	$s = 0;
	$timeslots = $db_link->query('SELECT id, starttime, endtime, timeslotcount FROM _timeslots WHERE project_id = "'.$p.'" AND active = "1" ORDER BY ID ASC')->fetch_all(MYSQLI_ASSOC);
	$blocked_days = $db_link->query('SELECT mo as Montag, di as Dienstag, mi as Mittwoch, do as Donnerstag, fr as Freitag, sa as Samstag, so as Sonntag FROM _weekdays WHERE project_id = "'.$p.'"')->fetch_object();
	$blocked_dates = array();
	if($blocked_dates_row = $db_link->query('SELECT date FROM _blocked_dates WHERE project_id = "'.$p.'" ORDER BY date ASC')->fetch_all(MYSQLI_ASSOC))
	{
		foreach($blocked_dates_row as $row)
		array_push($blocked_dates, $row['date']);
	}
	else
		$blocked_dates = array();
	foreach($timeslots as $slot)
	{
		echo '<tr>';
		echo '<td style="text-align:center;"><i class="fa fa-hourglass-start" aria-hidden="true"></i> '.date("H:i", strtotime($slot["starttime"])).' Uhr<br><i class="fa fa-hourglass-end" aria-hidden="true"></i> '.date("H:i", strtotime($slot["endtime"]))." Uhr</td>";
		foreach ($weekdates as $val)
		{
			$tdclass="tdbookallowed";
			
			$datetemp = date('Y-m-d',$val);
			$wochentag_temp = $tage_de[date("w",strtotime($datetemp))];
			if($blocked_days->$wochentag_temp == '0')
				$tdclass="tdblocked";
			if(strtotime($datetemp) < strtotime($p_startend->startdate))
				$tdclass="tdblocked";
			if(strtotime($datetemp) > strtotime($p_startend->enddate))
				$tdclass="tdblocked";
			if(in_array($datetemp, $blocked_dates))
				$tdclass="tdblocked";
			if($datetemp < $today)
				$tdclass="tdblocked";
			$bookings = $db_link->query('SELECT count(id) as ANZ FROM _booking WHERE project_id = "'.$p.'" AND timeslot_id = "'.$slot['id'].'" AND date = "'.$datetemp.'" AND status = "1"')->fetch_object();
 

			if($bookings->ANZ >= $slot['timeslotcount'])
				$tdclass="tdblocked";
				$tdusercount = $bookings->ANZ;
			if($tdclass!="tdblocked")
				echo '<td class="tdcalendar '.$tdclass.'" tag="'.$datetemp.'" slot="'.$slot['id'].'"><i class="fa fa-plus-square-o" aria-hidden="true"></i> '.$language['booking'].'<br>';
			else
				echo '<td class="tdcalendar '.$tdclass.'"><i class="fa fa-ban" aria-hidden="true"></i> '.$language['nobooking'].'</br>';
			$u = 0;
			while($u < $tdusercount)
			{
				echo ' <i class="fa fa-user" aria-hidden="true"></i>';
				$u++;
			}	
			if($tdclass!="tdblocked")
			{
				$o = 0;
				while($o < ($slot['timeslotcount']-$tdusercount))
				{
					echo ' <i class="fa fa-user-o" aria-hidden="true"></i>';
					$o++;
				}
			}
			echo '</td>';	
		}
	}
		
		echo'</tbody></table>';
}

function sendmail($dbrow)
{
	require('../phpmailer/class.phpmailer.php');
	require('../phpmailer/class.smtp.php');
	echo $dbrow;
	global $db_link;
     $book = $db_link->query("SELECT p.user, p.booker, p.date, p.additional_infos, t.starttime, t.endtime, m.sender, m.subject, m.body, m.ort from _booking as p, _timeslots as t, _emailinformation as m WHERE p.timeslot_id = t.id AND p.project_id = m.project_id AND p.id =  '".$dbrow."'")->fetch_object();
	 
	$filter = "(&(objectCategory=person)(objectClass=user)(samAccountName=".$book->user."))";
$ldups = search_ldap($filter);
$rec = $ldups[0]['mail'][0]; 
// event params
$summary = html_entity_decode($book->body);
$venue = utf8_encode($book->ort);
$start = date('d.m.Y', strtotime($book->date));
$start_time = str_replace(':','',$book->starttime);
$end = date('Ymd', strtotime($book->date));
$end_time = str_replace(':','',$book->endtime);
$event_id = rand(500000, 600000);
$sequence = 0;
$status = 'TENTATIVE';
$description = html_entity_decode($book->body);

//PHPMailer
$mail = new PHPMailer();
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = 'ece8smtp';
$mail->Port = 25;
$mail->SMTPAuth = false;
$mail->IsHTML(true);
$mail->setFrom($book->sender);
//$mail->addReplyTo('your@kaserver.com', 'Water Melon');
$mail->addAddress($rec);


$recipient = $rec;
$mail->Subject = html_entity_decode($book->subject) ." am ".$start;
$mail->Body = $description;
$mail->send();
}

function sendmail_test($dbrow)
{
	require('/../phpmailer/class.phpmailer.php');
	require('/../phpmailer/class.smtp.php');
	global $db_link;
     $book = $db_link->query("SELECT p.user, p.booker, p.date, p.additional_infos, t.starttime, t.endtime, m.sender, m.subject, m.body from _booking as p, _timeslots as t, _emailinformation as m WHERE p.timeslot_id = t.id AND p.id = m.project_id AND p.id =  '".$dbrow."'")->fetch_object();
// event params
$summary = 'Summary of the event';
$venue = 'Simbawanga';
$start = date('Ymd', strtotime($book->date));
$start_time = str_replace(':','',$book->starttime);
$end = date('Ymd', strtotime($book->date));
$end_time = str_replace(':','',$book->endtime);
$event_id = rand(500000, 600000);
$sequence = 0;
$status = 'TENTATIVE';
$description = "blubberdiblubb". str_replace(':','',$book->endtime);;

//PHPMailer
$mail = new PHPMailer();
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = 'ece8smtp';
$mail->Port = 25;
$mail->SMTPAuth = false;
$mail->IsHTML(false);
$mail->setFrom('noreply@ece.com');
//$mail->addReplyTo('your@kaserver.com', 'Water Melon');
$mail->addAddress('alexander.scholz@ece.com');
$mail->ContentType = 'text/calendar';

$mail->Subject = "Eventtitel". $dbrow;
$mail->addCustomHeader('MIME-version',"1.0");
$mail->addCustomHeader('Content-type',"text/calendar; method=REQUEST; charset=UTF-8");
$mail->addCustomHeader('Content-Transfer-Encoding',"7bit");
$mail->addCustomHeader('X-Mailer',"Microsoft Office Outlook 12.0");
$mail->addCustomHeader("Content-class: urn:content-classes:calendarmessage");

$ical = "BEGIN:VCALENDAR\r\n";
$ical .= "VERSION:2.0\r\n";
$ical .= "PRODID:-//YourCassavaLtd//EateriesDept//EN\r\n";
$ical .= "METHOD:REQUEST\r\n";
$ical .= "BEGIN:VEVENT\r\n";
$ical .= "UID:".strtoupper(md5($event_id));
$ical .= "SEQUENCE:".$sequence."\r\n";
$ical .= "STATUS:".$status."\r\n";
$ical .= "DTSTAMPTZID=Europe/Berlin:".date('Ymd').'T'.date('His')."\r\n";
$ical .= "DTSTART:".$start."T".$start_time."\r\n";
$ical .= "DTEND:".$end."T".$end_time."\r\n";
$ical .= "LOCATION:".$venue."\r\n";
$ical .= "SUMMARY:".$summary."\r\n";
$ical .= "DESCRIPTION:".$description."\r\n";
$ical .= "BEGIN:VALARM\r\n";
$ical .= "TRIGGER:-PT15M\r\n";
$ical .= "ACTION:DISPLAY\r\n";
$ical .= "DESCRIPTION:Reminder\r\n";
$ical .= "END:VALARM\r\n";
$ical .= "END:VEVENT\r\n";
$ical .= "END:VCALENDAR\r\n";

$mail->Body = $ical;
$mail->send();
}
function export2excel($filename, $autofilter, ...$data)
{    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
	$objPHPExcel->removeSheetByIndex(0);
	$textFormat='@';//'General','0.00','@'
	$sheetindex = 0;
	foreach($data as $sheet)
	{
		$spaltenanzahl = count($sheet[1]);
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex($sheetindex);
		$objPHPExcel->getActiveSheet()->setTitle($sheet[0]);// Rename worksheet
		unset($sheet[0]);
		
        // Fill worksheet from values in array
        $objPHPExcel->getActiveSheet()->fromArray($sheet);
		
		$alphabet = 'A';
        for($i = 0; $i < $spaltenanzahl; $i++)
		{
			$objPHPExcel->getActiveSheet()->getStyle('A1:' . $alphabet . '1')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($alphabet)->setAutoSize(true);
			if($autofilter)
				$objPHPExcel->getActiveSheet()->setAutoFilter('A1:' . $alphabet . '1');
			$alphabet++;
		}
		$objPHPExcel->getActiveSheet()->getStyle('A1:' . $alphabet . '999')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
		$sheetindex++;
	}
	//Save Excel 2007 file
	$objPHPExcel->setActiveSheetIndex(0);
	$datum = date("Y-m-d_H_i");
	
	//Redirect output to a clients web browser (Excel2007)
	header('Content-Type: text/html; charset=UTF-8');
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="' . $filename . '_' . $datum . '.xlsx"');
	header('Cache-Control: max-age=0');
	//If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');
	
	//If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
}