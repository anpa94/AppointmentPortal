<?php

include("config.php");
include("func.php");

if(strlen($_POST['search']) < '2')
    {
        echo'Das Zeitfenster muss mindestens 10 Minuten betragen.';
    }
    else
    {
		$row = $db_link->query('SELECT starttime, endtime FROM projects WHERE id = "'.$_POST['p'].'"')->fetch_object();
		$starttime_temp = strtotime($row->starttime);
		$duration = intval($_POST['search']);
		$endtime_temp = strtotime('+'.$duration.' minutes', $starttime_temp);
		echo '<button class="btn btn-success addtimeslots" id="'.$_POST['p'].'" time="'.$_POST['search'].'"><i class="fa fa-bullhorn" aria-hidden="true"></i> Terminblöcke speichern</button><br>(alle zuvor definierten Terminblöcke werden gelöscht)</br>';
		echo '<div class="well well-sm">Tagesbeginn <i class="fa fa-hand-o-right" aria-hidden="true"><b></i> '.$row->starttime.' Uhr </b><i class="fa fa-chevron-left" aria-hidden="true"></i><i class="fa fa-chevron-right" aria-hidden="true"></i> Tagesende <i class="fa fa-hand-o-right" aria-hidden="true"></i><b> '.$row->endtime.' Uhr</b></div>';
		echo '<br>';
		echo '<h4>Terminblöcke</h4>';
		$i=1;
		echo '<table class="table table-hover-sm"><thead><tr><th>ID</th><th>Start</th><th>Ende</th></thead><tbody>';
		while($endtime_temp <= strtotime($row->endtime))
		{
			echo'<tr><td>'.$i.'</td>';
			echo '<td>'.date("H:i:s",$starttime_temp).' Uhr</td>';
			echo '<td>'.date("H:i:s",$endtime_temp).' Uhr</td></tr>';
			$starttime_temp = ($endtime_temp);
			$endtime_temp_1 = date("H:i:s",$endtime_temp);
			$endtime_temp = strtotime('+'.$duration.' minutes', $starttime_temp);
			$i++;
		}
		echo '</tbody></table>';
    }
 ?>
 <script>
$('.addtimeslots').click(function () {
		var data = 'mode=addtimeslot&time=' + $(this).attr('time') + '&id=' + $('#id').val();
		$.ajax({
			url: 'inc/p_kopfdaten_send.php',
			type: "GET",
			data: data,
			success: function (reqCode) {
				if (reqCode>0) {
					history.pushState('', '', '?p=' + reqCode + '&mode=loadProjectBackend#booking');
					$.ajax(
					{
						method: 'POST',
						url: 'sites/ajax/ajax_home.php',
						data:
						{
							p: reqCode,
							mode: 'loadProjectBackend'
						},
						success: function(data)
						{
							$('#main').html(data).promise().done(function() {init();});
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Die Terminblöcke wurden erfolgreich gespeichert / aktualisiert.</div>');
						}
					});
					
				} else{
		            $('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Fehler. Die Terminblöcke konnten nicht gespeichert werden.</div>');
		        }
			}
		});
		return false;
	});	
 </script>    