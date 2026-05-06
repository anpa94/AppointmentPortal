<?php

include("config.php");
include("func.php");
if($_GET['id'] !== 'x')
{
    $row = $db_link->query("SELECT id, name from  `_additionalinfos_response` WHERE add_id = '".$_GET['id']."'")->fetch_all(MYSQLI_ASSOC);
	if(array_key_exists('0',$row))
	{
		echo '<table id="dc-dbma-table" class="table table-hover-sm">';
		echo '<thead>';
			echo '<tr>';
				echo '<th>Titel</th>';
				echo '<th>EDIT</th>';
				echo '<th>DEL</th>';
			echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
	
		foreach ($row as $value)
		{
			echo'<tr>
				<td>'.mb_convert_encoding($value['name'], 'UTF-8', 'auto').'</td>';
				echo'<td><button type="button" id="edit-addinfos" class="btn btn-default"><i class="fa fa-pencil" aria-hidden="true"></i></button</td>';
				echo'<td><button type="button" id="edit-addinfos" class="btn btn-default"><i class="fa fa-pencil" aria-hidden="true"></i></button</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}
	else
		echo'Keine Antworten für Auswahlliste vorhanden.<br>';
	echo'<label for="addinfo_name_response">Titel</label>
		<div class="form-group input-group">
			<input type="text" id="addinfo_name_response" class="form-control" autocomplete="off" autofocus></input>';
	echo '<button type="button" row_id="'.$_GET['id'].'" id="send-addinfos-response" class="btn btn-default right"><i class="fa fa-floppy-o" aria-hidden="true"></i> Speichern</button>';
}
else
	echo "Bitte Auswahlliste auswählen";
?>

<script>
		$('#send-addinfos-response').click(function () {
		if($.trim($('#addinfo_name_response').val()) === ''){
			$('#addinfo_name_response').parent('div').addClass('has-error');
		}
		else
			{
						var data = 'mode=addinfo_response&titel=' + $('#addinfo_name_response').val() + '&id=' + $('#id').val()  + '&row_id=' + $(this).attr('row_id');
						$.ajax({
							url: 'inc/p_kopfdaten_send.php',
							type: "GET",
							data: data,
							success: function (reqCode) {
								if (reqCode>0) {
									history.pushState('', '', '?p=' + reqCode + '&mode=loadProjectBackend#addinfos');
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
											$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Der Antworttyp der Auswahlliste wurde erfolgreich gespeichert</div>');
										}
									});
									
								} else{
									$('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Bei der Verabeitung der Kopfdaten trat ein Fehler auf</div>');
								}
							}
						});
					}
	});	
</script>