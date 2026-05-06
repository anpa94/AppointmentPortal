<?php
require_once '../../inc/config.php';
require_once '../../inc/func.php';

if((json_decode(getConfig('authorizedGroups', 'frontend'))[0] == "ALL" || user_authorized(json_decode(getConfig('authorizedGroups', 'frontend')))) && isset($_POST['mode']))
	{
	$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$lang = substr($lang, 0, 2)=='de' ? "de" : "en";
	require_once '../../language/'.$lang.'.php';
	echo'<input id="savereservation" type="hidden" class="form-control" disabled value="'.$language['savereservation'].'">';
	$p = isset($_POST['p']) ? $_POST['p'] : 'X';
	$datum = isset($_POST['d']) ? $_POST['d'] : '0';
	echo'<input id="d" type="hidden" class="form-control" disabled value="'.$datum.'">';
	if($_POST['mode'] == "loadProject" || $_POST['mode'] == "loadProjectBackend" || $_POST['mode'] == "selectProject")
		$_POST['mode']($db_link, $p, $datum);
	else
		echo '<div class="alert alert-danger"><strong>Fehler!</strong> Diese Seite existiert nicht.</div>';
	}
else
	echo '<div class="alert alert-danger"><strong>Fehler!</strong> Du hast keine Berechtigungen für dieses Projekts.</div>';

function loadProject($db_link, $project, $datum)
{
	global $language;

	if($data_head = $db_link->query('SELECT id, name, startdate, enddate, description from projects where active = "1" and id = "'.$project.'"')->fetch_object())
	{
		global $user;
		$tage = $language['tage'];
		$tage_de = $language['tage_de'];
		$mode = __FUNCTION__;
		//sendmail();
		echo'<input id="id" type="hidden" class="form-control" disabled value="'.$data_head->id.'">';
		echo'<input id="user" type="hidden" class="form-control" disabled value="'.$user.'">';
		echo'<input id="mode" type="hidden" class="form-control" disabled value="'.$mode.'">';
		echo'<input id="dud" type="hidden" class="form-control" disabled value="'.$datum.'">';
		echo'<div id="message" class="pull-right"></div>';
		echo '<h3>'.$data_head->name.'</h3>';
		echo'<ul class="nav nav-tabs">
			<li class="active"><a href="#head" data-toggle="tab"><i class="fa fa-newspaper-o" aria-hidden="true"></i> ' .$language['projektinformationen'] .'</a></li>
			<li><a href="#book" data-toggle="tab"><i class="fa fa-book" aria-hidden="true"></i> '.$language['bookingcalendar'].'</a></li>';
		echo '</ul>';
		echo'<div class="tab-content clearfix">
			<div class="tab-pane active" id="head">
				<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> '.$language['description'].'</h4>
				'.nl2br($data_head->description).'
				<div class="divider"></div>
				<div class="col-md-6">
					<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> '.$language['firstbookingday'].'</h4>
					<div class = "alert alert-info"><i class="fa fa-calendar" aria-hidden="true"></i> '.$tage[date("w", strtotime($data_head->startdate))].', <strong>'.date("d.m.Y", strtotime($data_head->startdate)).'</strong></div>
				</div>
				<div class="col-md-6">
					<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> '.$language['lastbookingday'].'</h4>
					<div class = "alert alert-info"><i class="fa fa-calendar" aria-hidden="true"></i> '.$tage[date("w", strtotime($data_head->enddate))].', <strong>'.date("d.m.Y", strtotime($data_head->enddate)).'</strong></div>
				</div>';
				echo '<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> '.$language['mybookings'].'</h4>';
				$row = $db_link->query('SELECT p.user, p.booker, p.date, p.additional_infos, t.starttime, t.endtime from _booking as p, _timeslots as t WHERE p.timeslot_id = t.id AND p.status = "1" and p.project_id = "'.$project.'" AND (p.user = "'. $user .'" OR p.booker = "'. $user .'") ORDER BY p.date ASC, t.starttime ASC ')->fetch_all(MYSQLI_ASSOC);

				if(isset($row['0']))
				{

					echo '<table id="bookinglist" class="table table-sm table-striped"><thead><tr><th>Datum</th><th>Startzeit</th><th>Endzeit</th><th>Mitarbeiter</th><th>Gebucht von</th><th>Zusatzinfos</th></tr></thead><tbody>';
					foreach ($row as $valn)
					{
							echo '<tr><td>'.$valn["date"].'</td><td>'.$valn["starttime"].'</td><td>'.$valn["endtime"].'</td><td>'.ldapFullname($valn['user']).'</td><td>'.ldapFullname($valn['booker']).'</td><td>'.str_replace('____', '; ',$valn['additional_infos']).'</td></tr>';
				
					}
					echo '</tbody></table>';
				}
				else
					echo $language["nobookings"];

			echo'</div>';
			echo'<div class="tab-pane" id="book">';
				echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> '.$language['bookingcalendar'].'</h4>';
				bookingCalendar($project, $datum);
			echo '</div>';
		echo '</div>';	
	}
	else
		echo '<div class="alert alert-danger"><strong>Fehler!</strong> Dieses Projekt existiert nicht oder wurde noch nicht freigeschaltet.</div>';

};

function loadProjectBackend($db_link, $project, $datum)
{
	global $language;
	global $user;
	$mode = __FUNCTION__;
	$today = date("Y-m-d", time());
	$lastday = new dateTime($today);
	$lastday ->add(new DateInterval('P2Y'));
	
	echo'<input id="mode" type="hidden" class="form-control" disabled value="'.$mode.'">';
	echo'<input id="dud" type="hidden" class="form-control" disabled value="'.$datum.'">';
	if($row = $db_link->query('SELECT id FROM _authorisized_user_be WHERE project_id = "'.$project.'" AND username = "'.$user.'"')->fetch_object() OR (user_authorized(json_decode(getConfig('authorizedGroups', 'backend')))==TRUE AND $project =="X"))
	{
		$data_temp = (object) array('id' => 'X', 'name' => '', 'active' => '0', 'description' => '', 'startdate' => $today, 'enddate' => $lastday ->format("Y-m-d"), 'starttime' => '10:00:00', 'endtime' => '18:00:00');
		$row1 = $db_link->query('SELECT id, name, description, active, startdate, enddate, starttime, endtime FROM projects WHERE id = "'.$project.'"')->fetch_object();
		$data_head = isset($row1) ? $row1 : $data_temp;

		$data_mail_temp = (object) array('sender' => '', 'subject' => '', 'body' => '', 'ort' => '');
		$row_mail = $db_link->query('SELECT sender, subject, body, ort FROM _emailinformation WHERE project_id = "'.$project.'"')->fetch_object();
		$data_mail = isset($row_mail) ? $row_mail : $data_mail_temp;
		
		echo'
		<div id="message" class="pull-right col-md-8"></div>';
		if ($project == 'X')
		{
			$timeslots_count = '';
			$timeslotstobook = 0;
			echo'<h4><i class="fa fa-cog" aria-hidden="true"></i> Projekt anlegen</h4>';
		}
		else
			echo'<h4><i class="fa fa-cog" aria-hidden="true"></i> Projektadministration'; 
			if ($data_head->active == 0)
				echo ' <button type="button" wert = "1" id="activate" class="btn btn-danger btn-sm "><i class="fa fa-floppy-o" aria-hidden="true"></i> Projekt aktivieren</button>';
	if 		($data_head->active == 1)
				echo ' <button type="button" wert = "0" id="activate" class="btn btn-success btn-sm "><i class="fa fa-floppy-o" aria-hidden="true"></i> Projekt deaktivieren</button>';
			echo'</h4><h3>'.$data_head->name.'</h3></h4>
		<ul class="nav nav-tabs">
			<li class="active"><a href="#head" data-toggle="tab"><i class="fa fa-magic" aria-hidden="true"></i> Kopfdaten</a></li>';
		if ($project !== 'X')
		{
			echo'<li><a href="#access" data-toggle="tab"><i class="fa fa-universal-access" aria-hidden="true"></i> Berechtigungen</a></li>
			<li><a href="#addinfos" data-toggle="tab"><i class="fa fa-bolt" aria-hidden="true"></i> Zusatzinformationen</a></li>
			<li><a href="#mail" data-toggle="tab"><i class="fa fa-envelope" aria-hidden="true"></i> Termineinladung</a></li>
			<li><a href="#booking" data-toggle="tab"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> Terminvorgaben</a></li>
			<li><a href="#block" data-toggle="tab"><i class="fa fa-clock-o" aria-hidden="true"></i> Blockliste</a></li>';
			$timeslots = $db_link->query('SELECT count(id) as ANZ, SUM(CASE WHEN timeslotcount > 0 then 1 else 0 END) AS tobook FROM _timeslots WHERE project_id = "'.$project.'" AND active = "1"')->fetch_object();
			$timeslots_count = ($timeslots->ANZ)!=0 ? 'disabled' : '';
			$timeslotstobook = ($timeslots->tobook == 0 OR $timeslots->tobook == NULL) ? '0' : '1';
			
			if($timeslotstobook == 1)
				echo'<li><a href="#book" data-toggle="tab"><i class="fa fa-book" aria-hidden="true"></i></i> '.$language['bookingcalendar'].'</a></li>';
				echo'<li><a href="#booking_list" data-toggle="tab"><i class="fa fa-book" aria-hidden="true"></i></i> '.$language['bookings'].'</a></li>';
		}
		echo '</ul>';
		echo'<div class="tab-content clearfix">
			<div class="tab-pane active" id="head">
			
				<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Projektstammdaten pflegen</h4>
				<div id="kopfdaten">
					<input id="id" type="hidden" class="form-control" disabled value="'.$data_head->id.'">
					<input id="user" type="hidden" class="form-control" disabled value="'.$user.'">
					<div class="control-group">
						<label  for="titel"  class="control-label  col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Titel</label>
						<div class="controls"><input id="titel" type="text" class="form-control" value="'.$data_head->name.'"></div>
					</div>	
					<div class="control-group">
						<label  for="beschreibung"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> '.$language['description'].'</label>
						<div class="controls"><textarea rows="8" id="beschreibung" type="text" class="form-control">'.$data_head->description.'</textarea></div>
					</div>
					
					<script type="text/javascript">
						$(function () {
							$("#startdate").datetimepicker({
								useCurrent: false, //Important! See issue #1075
								locale: "de",
								format: "YYYY-MM-DD",
								defaultDate: "'.$data_head->startdate.'",
								maxDate: "'.$data_head->enddate.'"
								
							});
							$("#choosedate").datetimepicker({
								useCurrent: false, //Important! See issue #1075
								locale: "de",
								format: "YYYY-MM-DD",
								defaultDate: "'.$today.'",
								maxDate: "'.$data_head->enddate.'"
								
							});
							$("#enddate").datetimepicker({
								useCurrent: false, //Important! See issue #1075
								locale: "de",
								format: "YYYY-MM-DD",
								defaultDate: "'.$data_head->enddate.'",
								minDate: "'.$data_head->startdate.'",
							});
							$("#startdate").on("dp.change", function (e) {
								$("#enddate").data("DateTimePicker").minDate(e.date);
							});
							$("#enddate").on("dp.change", function (e) {
								$("#startdate").data("DateTimePicker").maxDate(e.date);
							});
							
						});
					</script>
					
					
					<div class="control-group">
						<label  for="startdate"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Startdatum</label>
						<div class="controls" style="position:relative;"><input id="startdate" type="text" class="form-control"></div>
					</div>	
					<div class="control-group">
						<label  for="enddate"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Endedatum</label>
						<div class="controls" style="position:relative;"><input id="enddate" type="text" class="form-control"></div>
					</div>	
					<div class="control-group">
						<label  for="starttime"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Startzeit (je Tag) <small>[Nur änderbar wenn noch keine Terminblöcke definiert wurden]</small></label>
						<div class="controls"><input id="starttime" type="time" class="form-control timepicker" value="'.$data_head->starttime.'" '.$timeslots_count.'></div>
					</div>	
					<div class="control-group">	
						<label  for="endtime"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Endzeit (je Tag) <small>[Nur änderbar wenn noch keine Terminblöcke definiert wurden]</small></label>
						<div class="controls"><input id="endtime" type="time" class="form-control timepicker" value="'.$data_head->endtime.'" '.$timeslots_count.'></div>
					</div>		
					<button type="button" id="send" class="btn btn-default right"><i class="fa fa-floppy-o" aria-hidden="true"></i> Speichern</button>
				</div>
			</div>';
			echo'<div class="tab-pane" id="booking_list">';
				echo '<div class="col-lg-12 col-xs-12">';
					echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> aktuelle Buchungen</h4>';

					 	echo'<div class="control-group col-sm-6">
							<label  for="choosedate"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Datum wählen</label>
						<div class="controls" style="position:relative;"><input id="choosedate" type="text" class="form-control"></div>';

					echo'<button type="button" id="load-bookings" class="btn btn-default right col-sm-6"><i class="fa fa-floppy-o" aria-hidden="true"></i> Laden</button>';
					 echo '</div></div>';
					 echo '<div id="bookingbody" class="col-lg-12 col-xs-12"></div>';
	echo '<a href="https://itbooking/inc/p_pdfexport_last45days.php?id='.$project.'">Excelexport letzte 45 Tage</a>';
			echo '</div>';	
			echo'<div class="tab-pane" id="access">';
				echo '<div class="col-md-6">';
					echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Berechtigungen einrichten'; popo("Die Suche Überprüft live das LDAP-Verzeichnis nach Übereinstimmungen."); echo '</h4>';
					 echo '<div class="col-lg-12 col-xs-12">
						<form id="accinfo_form" method="POST" action="inc/p_load_user.php" target=_blank">
							<label>Benutzername <i class="fa fa-hand-o-right" aria-hidden="true"></i> automatische Suche</label>
							<div class="form-group input-group">
								<input type="text" id="accinfo_inp_groups" name="accinfo_inp_groups" class="form-control" autocomplete="off" autofocus>

							</div>
						</form>
					</div>
					<div id="accinfo_load">Ein automatische Suche erfolgt nach der Eingabe von mind. 3 Zeichen</div>
				</div>';	
				echo '<div class="col-md-6" style="border-left: 1px dotted #DDD;">';
					echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Berechtigungen entziehen</h4>';
					echo '<table id="dc-dbma-table" class="table table-hover-sm">';
						echo '<thead>';
							echo '<tr>';
								echo '<th>Username</th>';
								echo '<th>Name</th>';
								echo '<th>Löschen</th>';
							echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
						$row = $db_link->query('SELECT id, username FROM _authorisized_user_be WHERE project_id = "'.$project.'" ORDER BY ID DESC')->fetch_all(MYSQLI_ASSOC);
						foreach($row as $value)
							{
								if($value['username'] == $user)
								{
									echo'<tr>
										<td>'.$value['username'].'</td>
										<td>'.ldapFullname($value['username']).'</td>
										<td><button class="btn btn-default" disabled>akt. Benutzer</td>
									</tr>';
								}
								else{
									echo'<tr>
										<td>'.$value['username'].'</td>
										<td>'.ldapFullname($value['username']).'</td>
										<td><button class="btn btn-danger deleteuser" user= "'.$value['username'].'" row_id="'.$value['id'].'"><i class="fa fa-eraser" aria-hidden="true"></i> Rechte entfernen</td>
									</tr>';
								}
							}
					echo'</tbody></table>
				</div>	
			</div>';
			echo'<div class="tab-pane" id="addinfos">';
				echo '<div class="col-md-6">';
					echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Zusatzinformationen definieren'; popo("Informationen, welche bei einer Buchung zusätzlich abgefragt werden sollen."); echo '</h4>';
					 echo '<div class="col-lg-12 col-xs-12">
							<label for="addinfo_name">Titel</label>
							<div class="form-group input-group">
								<input type="text" id="addinfo_name" class="form-control" autocomplete="off" autofocus></input>
							<label for="response">Antworttyp</label>
								<select class="form-control" name="response" id="response">
									<option value="T">Text</option>
									<option value="A">Auswahlliste</option>
								</select>
								<br>
								<label for="required">Pflichtfeld?</label>			
								<select class="form-control" name="required" id ="required">
									<option value="1">Ja</option>
									<option value="0">Nein</option>
								</select>								
							</div>
						<button type="button" id="send-addinfos" class="btn btn-default right"><i class="fa fa-floppy-o" aria-hidden="true"></i> Speichern</button>
					</div>

				</div>';	
				echo '<div class="col-md-6" style="border-left: 1px dotted #DDD;">';
					echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> bestehende Zusatzinformationen'; popo("Typ T = Textfeld, Typ A = Auswahlliste, Pflicht 1 = Pflichtfeld, Pflicht 2 = kein Pflichtfeld"); echo '</h4>';
					echo '<table id="dc-dbma-table" class="table table-hover-sm">';
						echo '<thead>';
							echo '<tr>';
								echo '<th>Titel</th>';
								echo '<th>Typ</th>';
								echo '<th>Pflicht</th>';
								echo '<th>EDIT</th>';
								echo '<th>DEL</th>';
							echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
						$row = $db_link->query('SELECT id, name, response, required FROM _additionalinfos WHERE project_id = "'.$project.'" ORDER BY ID DESC')->fetch_all(MYSQLI_ASSOC);
						foreach($row as $value)
							{
									echo'<tr>
										<td>'.mb_convert_encoding($value['name'], 'UTF-8', 'auto').'</td>
										<td>'.$value['response'].'</td>
										<td>'.$value['required'].'</td>';
										if ($value['response'] == 'A')
											echo'<td><button type="button" id="edit-addinfos" class="btn btn-default"><i class="fa fa-pencil" aria-hidden="true"></i></button</td>';
										else
											echo '<td></td>';
										echo'<td><button type="button" id="delete-addinfos" row_id="'.$value['id'].'" class="btn btn-default"><i class="fa fa-trash" aria-hidden="true"></i></button</td>
									</tr>';
							}
					echo'</tbody></table>
					<div id="addinfo_response">
								<label for="select_response">Antwort Auswahllisten</label>
								<select class="form-control" name="select_response" id="select_response">';
										echo '<option value="x">Bitte auswählen</option>';
									foreach($row as $value)
									{
										if ($value['response'] == 'A')
										echo '<option value="'.$value['id'].'">'.$value['name'].'</option>';
									}

								echo'</select>';
					echo'</div>
					<div id="addinfo_response_edit"></div>
				</div>	
			</div>';			
			echo'<div class="tab-pane" id="booking">';
				$timeslots = $db_link->query('SELECT id, starttime, endtime, timeslotcount FROM _timeslots WHERE project_id = "'.$project.'" AND active = "1" ORDER BY ID ASC')->fetch_all(MYSQLI_ASSOC);
				if(isset($timeslots[0]))
				{	echo '<div class="col-md-6">';
						echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Terminblöcke löschen</h4>
						<button type="submit" id="time_inp_groups_delete" class="btn btn-danger" value="search"><i class="fa fa-calendar" aria-hidden="true"></i> Terminblöcke löschen</button>';
						echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Terminblöcke löschen und neu anlegen</h4>';
						echo '<div class="col-lg-12 col-xs-12">
							<form id="timeslot" method="POST" action="inc/p_load_time.php" target=_blank">
								<label>Termindauer (Minuten)</label>
								<div class="form-group input-group">
									<input type="text" id="time_inp_groups" name="time_inp_groups" class="form-control numbersOnly" autocomplete="off" autofocus>
								</div>
							</form>
						</div>
						<div id="time_load">Das Zeitfenster muss mindestens 10 Minuten betragen.</div>
					</div>
					<div class="col-md-6" style="border-left: 1px dotted #DDD;">';
						echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Anzahl Buchungen pro Terminblock';popo("Standardwert für alle Terminblöcke"); echo'</h4>

						<div class="form-group input-group">
							<div class="col-md-2 col-xs-12">
								<input type="text" id="time_inp_groups_count" name="time_inp_groups_count" class="form-control numbersOnly" value="'.$timeslots[0]['timeslotcount'].'" autocomplete="off" autofocus>
							</div><div class="col-md-4 col-xs-12">	
								<button type="submit" id="time_inp_groups_count_btn" class="btn btn-primary pull-left" value="search"><i class="fa fa-calendar" aria-hidden="true"></i> Anzahl Buchungen aktualisieren</button>
							</div>
						</div>';
						echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Aktuell definierte Terminblöcke</h4>';
						$i=1;
						echo '<table class="table table-hover-sm"><thead><tr><th>ID</th><th>Start</th><th>Ende</th><th>max. Buchungen</th></thead><tbody>';
							foreach($timeslots as $value)
							{
								echo'<tr><td>'.$i.'</td>';
								echo '<td>'.$value['starttime'].' Uhr</td>';
								echo '<td>'.$value['endtime'].' Uhr</td>';
								echo '<td><input slot_id="'.$value['id'].'" class="form-control single_maxbook" value="'.$value['timeslotcount'].'"></td>';
								$i++;
							}
						echo '</tbody></table>
					</div></div>';	
				}
				else
				{
					echo '<div class="col-md-6">';
						echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Terminblöcke anlegen</h4>';
							echo '<div class="col-lg-12 col-xs-12">
								<form id="timeslot" method="POST" action="inc/p_load_time.php" target=_blank">
									<label>Termindauer (Minuten)</label>
									<div class="form-group input-group">
										<input type="text" id="time_inp_groups" name="time_inp_groups" class="form-control numbersOnly" autocomplete="off" autofocus>
									</div>
								</form>
								<div id="time_load">Das Zeitfenster muss mindestens 10 Minuten betragen.</div>
							</div>
						</div>
					</div>';	
				}

				

			echo'<div class="tab-pane" id="block">';
				echo '<div class="col-md-6">';
					$blocked_dates = array();
					if($blocked_dates_row = $db_link->query('SELECT date FROM _blocked_dates WHERE project_id = "'.$project.'" ORDER BY date ASC')->fetch_all(MYSQLI_ASSOC))
					{
						foreach($blocked_dates_row as $row)
						array_push($blocked_dates, $row['date']);
					}
					else
						$blocked_dates = array();
						if($weekdays_active =$db_link->query('SELECT * FROM  _weekdays WHERE project_id = "'.$project.'"')->fetch_object());
					{
						echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> buchbare Wochentage</h4>';
						$checked = ($weekdays_active->mo == "1") ? 'checked' : '';
						echo'<label class="checkbox-inline"><input type="checkbox" class="weekdays" day="mo" '.$checked.'>Mo</label>';
						$checked = ($weekdays_active->di == "1") ? 'checked' : '';
						echo'<label class="checkbox-inline"><input type="checkbox" class="weekdays" day="di" '.$checked.'>Di</label>';
						$checked = ($weekdays_active->mi == "1") ? 'checked' : '';
						echo'<label class="checkbox-inline"><input type="checkbox" class="weekdays" day="mi" '.$checked.'>Mi</label>';
						$checked = ($weekdays_active->do == "1") ? 'checked' : '';
						echo'<label class="checkbox-inline"><input type="checkbox" class="weekdays" day="do" '.$checked.'>Do</label>';
						$checked = ($weekdays_active->fr == "1") ? 'checked' : '';
						echo'<label class="checkbox-inline"><input type="checkbox" class="weekdays" day="fr" '.$checked.'>Fr</label>';
						$checked = ($weekdays_active->sa == "1") ? 'checked' : '';
						echo'<label class="checkbox-inline"><input type="checkbox" class="weekdays" day="sa" '.$checked.'>Sa</label>';
						$checked = ($weekdays_active->so == "1") ? 'checked' : '';
						echo'<label class="checkbox-inline"><input type="checkbox" class="weekdays" day="so" '.$checked.'>So</label>';
						$weekdate_active = array($weekdays_active->so, $weekdays_active->mo, $weekdays_active->di, $weekdays_active->mi, $weekdays_active->do, $weekdays_active->fr, $weekdays_active->sa);
					}
					echo'<h4 href="#dayblock" data-toggle="collapse" style="cursor:pointer;"><i class="fa fa-hand-o-right" aria-hidden="true"></i> Tage für Buchung blockieren [<i class="fa fa-angle-double-down" aria-hidden="true"></i>]</h4>';
					//print_r($weekdate_active);
					echo '<div id="dayblock" class="collapse">';
						echo '<table class="table table-hover-sm"></thead><tr><th>Datum</th><th>Wochentag</th><th>Blockieren</th></tr></thead><tbody>';
						$tage = $language['tage'];
						$startdate = strtotime($data_head->startdate);
						$enddate = strtotime($data_head->enddate);
						while($startdate <= $enddate)
						{
							$day_temp = date('Y-m-d', $startdate);
							if($weekdate_active[date("w",strtotime($day_temp))]=="1")
							{
								if(!in_array($day_temp, $blocked_dates))
								{
									echo '<tr>';
										echo '<td>'.$day_temp.'</td>';
										echo '<td>'.$tage[date("w",strtotime($day_temp))].'</td>';
										echo '<td><button id="'.$day_temp.'" class="btn btn-sm btn-danger blockdate">Blockieren</button>';
									echo '</tr>';
								}
							}
							$startdate = strtotime( '+1 day', strtotime($day_temp));
						}
						echo '</tbody></table>';
					echo'</div>';					
				echo '</div>';
				echo '<div class="col-md-6" style="border-left: 1px dotted #DDD;">';
					echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> blockierte Tage</h4>';
					echo '<table class="table table-hover-sm"></thead><tr><th>Datum</th><th>Wochentag</th><th>Freigeben</th></tr></thead><tbody>';
						foreach($blocked_dates as $value)
						{
							echo '<tr>';
								echo '<td>'.$value.'</td>';
								echo '<td>'.$tage[date("w",strtotime($value))].'</td>';
								echo '<td><button id="'.$value.'" class="btn btn-sm btn-success unblockdate">Freigeben</button></td>';
							echo '</tr>';	
						}
					echo '</tbody></table>';
				echo'</div>';
			echo'</div>';
			if($timeslotstobook == 1)
				{
					echo'<div class="tab-pane" id="book">';
					echo'<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> '.$language['bookingcalendar'].'</h4>';
					bookingCalendar($project, $datum);
					echo'</div>';
				}
echo'
			<div class="tab-pane" id="mail">
			
				<h4><i class="fa fa-hand-o-right" aria-hidden="true"></i> Mailkommunikation'; popo('In der Einladung müssen Zeilenumbrüche mit \n ersetzt werden Bsp: Hallo, \nDanke für die Buchung'); echo'</h4>
				<div id="mailkommunikation">
					<div class="control-group">
						<label  for="mailsender"  class="control-label  col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Absender der Termineinladung</label>
						<div class="controls"><input id="mailsender" type="text" class="form-control" value="'.html_entity_decode($data_mail->sender).'"></div>
					</div>	
					<div class="control-group">
						<label  for="mailbetreff"  class="control-label  col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Betreff der Termineinladung</label>
						<div class="controls"><input id="mailbetreff" type="text" class="form-control" value="'.html_entity_decode($data_mail->subject).'"></div>
					</div>	
					<div class="control-group">
						<label  for="beschreibung"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Text in Termineinladung</label>
						<div class="controls"><textarea rows="8" id="mailbody" type="text" class="form-control">'.html_entity_decode($data_mail->body).'</textarea></div>
					</div>
					<div class="control-group">
						<label  for="mailort"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i>Ort</label>
						<div class="controls"><input id="mailort" type="text" class="form-control" value="'.html_entity_decode($data_mail->ort).'"></div>
					</div>
					<button type="button" id="send-mail" class="btn btn-default right"><i class="fa fa-floppy-o" aria-hidden="true"></i> Speichern</button>
				</div>
			</div>';				
	}
	else
		echo'<div class="alert alert-danger"><strong>Fehler!</strong> Du hast keine Berechtigungen zur Administration dieses Projekts.</div>';
	
};

function selectProject($db_link, $project, $datum)
{
	global $user;
	global $language;
	if(user_authorized(json_decode(getConfig('authorizedGroups', 'backend')))==TRUE)
	{
		echo '<h4 id="newproject" class="pr_load_be pull-right" p="X" style="cursor:pointer"><i class="fa fa-plus-circle" aria-hidden="true"></i> Projekt anlegen</h4>';
	}
	echo '<h4><i class="fa fa-pencil-square-o" aria-hidden="true"></i> '.$language["chooseproject"].'</h4>';
	if($row = $db_link->query('SELECT id, name, description FROM projects WHERE active = "1"')->fetch_all(MYSQLI_ASSOC))
        {
			foreach($row as $value)
			{
				echo'<div class="panel panel-default">
					<div class="panel-heading pr_load_fe" p="'.$value['id'].'" style="cursor:pointer"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> '.$value['name'].'</div>
					<div class="panel-body">
						'.nl2br(($value['description'])).'
					</div>';
					if($row1 = $db_link->query('SELECT username FROM _authorisized_user_be WHERE project_id = "'.$value['id'].'" AND username ="'.$user.'"')->fetch_object())
					{
					echo'<div class="panel-footer"><Panel footer><a style="cursor:pointer" class="pr_load_be" p="'.$value['id'].'">Projekt administrieren / Projektbackend öffnen</a></div>';	
					}
				echo'</div>';
			}
		}
    else
        echo("Es sind keine aktiven Projekte vorhanden.");
	if($row = $db_link->query('SELECT p.id, p.name, p.description, a.username FROM projects as p, _authorisized_user_be as a WHERE p.id = a.project_id AND a.username = "'.$user.'" AND p.active = "0"')->fetch_all(MYSQLI_ASSOC))
        {
			echo '<h4><i class="fa fa-pencil-square-o" aria-hidden="true"></i> inaktive Projekt mit administrativen Rechten von '.ldapFullname($user).'</h4>';
			foreach($row as $value)
			{
				echo'<div class="panel panel-danger">
					<div class="panel-heading pr_load_be" p="'.$value['id'].'" style="cursor:pointer">'.$value['name'].' (Projekt administrieren / Projektbackend öffnen)</div>
					<div class="panel-body">
						'.$value['description'].'
					</div>
				</div>';
			}
		}

};

?>
