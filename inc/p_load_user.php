<?php

include("config.php");
include("func.php");

if($_POST['search'] == '*')
    {
        echo '<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<strong>Fehler!</strong> Ungültige Eingabe!
			</div>';
    }
    else
	if(strlen($_POST['search']) < '3')
	{
		echo 'Ein automatische Suche erfolgt nach der Eingabe von mind. 3 Zeichen';
	}
	else
    {
		$result = search_ldap('(&(objectCategory=person)(objectClass=user)(samAccountName=' . $_POST['search'] . '*))');
        unset($result['count']);
        echo '<table class="table table-hover">';
            echo '<thead>';
				echo '<tr>';
                    echo '<th>Username</th>';
					echo '<th>Name</th>';
					echo '<th>Berechtigen</th>';
				echo '</tr>';
			echo '</thead>';
            echo '<tbody>';      
                foreach($result as $user)
                {
                     echo '<tr><td>' . $user['samaccountname'][0] . '</td><td>' . $user['cn'][0] . '</td><td><button class="btn btn-success adduser" id="'.$_POST['p'].'" user="'.$user['samaccountname'][0].'"><i class="fa fa-bullhorn" aria-hidden="true"></i> Rechte vergeben</td></tr>';
                }
            echo '</tbody>';
        echo '</table>';
    }
 ?>
 <script>
$('.adduser').click(function () {
		var data = 'mode=adduser&user=' + $(this).attr('user') + '&id=' + $('#id').val();
		$.ajax({
			url: 'inc/p_kopfdaten_send.php',
			type: "GET",
			data: data,
			success: function (reqCode) {
				if (reqCode>0) {
					history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'loadProjectBackend' }, 'access'));
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
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Der Account wurde erfolgreich berechtigt das Projetk zu administrieren.</div>');
						}
					});
					
				} else{
		            $('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Fehler. Ist der Account ggf. schon berechtigt?</div>');
		        }
			}
		});
		return false;
	});	
 </script>    