<?php

include("config.php");
include("func.php");
$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$lang = substr($lang, 0, 2)=='de' ? "de" : "en";

	require_once '../language/'.$lang.'.php';
	$mode = isset($_GET['mode']) ? $_GET['mode'] : '0';
if ($mode == 'storno')
{
	$row = $db_link->query('UPDATE _booking set status = "0" where id = "'.$_GET['wert'].'"');
}
else
{
$row = $db_link->query('SELECT p.id, p.user, p.booker, p.date, p.additional_infos, t.starttime, t.endtime from _booking as p, _timeslots as t WHERE p.timeslot_id = t.id AND p.status = "1" and p.project_id = "'.$_GET['id'].'" AND p.date = "'.$_GET['date'].'" ORDER BY p.date ASC, t.starttime ASC ')->fetch_all(MYSQLI_ASSOC);
	if(isset($row['0']))
				{
				
					echo '<table id="bookinglist" class="table table-sm table-striped"><thead><tr><th>Datum</th><th>Startzeit</th><th>Endzeit</th><th>Mitarbeiter</th><th>Username</th><th>Gebucht von</th><th>Zusatzinfos</th><th>Stornierung</th></tr></thead><tbody>';
					foreach ($row as $valn)
					{
							echo '<tr><td>'.$valn["date"].'</td><td>'.$valn["starttime"].'</td><td>'.$valn["endtime"].'</td><td>'.ldapFullname($valn['user']).'</td><td>'.$valn['user'].'<td>'.ldapFullname($valn['booker']).'</td><td>'.str_replace('____', '; ',$valn['additional_infos']).'</td><td><button type="button"  mode="storno" wert="'.$valn['id'].'" class="storno btn btn-default right col-sm-6" >X</button></td></tr>';
							
					}
					echo '</tbody></table>';
					echo'<a href="inc/p_pdfexport.php?id='.$_GET['id'].'&date='.$_GET['date'].'">Excelexport</a>';
				}
				else
					echo $language["nobookings"];

}
?>
<script>
	$('.storno').click(function () {
		$.get('inc/p_choosedate.php?mode=storno&wert=' + $(this).attr('wert'), function(data) {
			$('#bookingbody').html(data);
		});
	});

</script>