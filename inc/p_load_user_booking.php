<?php

include("config.php");
include("func.php");
$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$lang = substr($lang, 0, 2)=='de' ? "de" : "en";

	require_once '../language/'.$lang.'.php';
if($_POST['search'] == '*')
    {
        echo '<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				'.$language['error'].'
			</div>';
    }
    else
	if(strlen($_POST['search']) < '3')
	{
		echo '<div class="alert  alert alert-info" style="margin:15px">';
		echo '<i class="fa fa-info" aria-hidden="true"></i> '.$language['autosearch'].'';
		echo '</div>';
	}
	else
    {
		$result = search_ldap('(&(objectCategory=person)(objectClass=user)(samAccountName=' . $_POST['search'] . '*))');
        unset($result['count']);
		//print_r($result);
		if(isset($result[0]))
		{
			echo '<table class="table table-hover">';
				echo '<thead>';
					echo '<tr>';
						echo '<th>Name</th>';
						echo '<th>Abteilung</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';      
					foreach($result as $user)
					{
						$department = isset($user['title'][0]) ? $user['title'][0] : '';
						 echo '<tr class="adduser" id="'.$_POST['p'].'" sam="'. $user['samaccountname'][0] .'" user="'.ldapFullname($user['samaccountname'][0]).'" style="cursor:pointer;"><td><b>' . $user['cn'][0] . '</b></td><td>' . $department . '</td></tr>';
					}
				echo '</tbody>';
			echo '</table>';
		}
		else
		{
			echo '<div class="alert  alert alert-danger" style="margin:15px">';
			echo '<i class="fa fa-exclamation" aria-hidden="true"></i> Kein Mitarbeiter gefunden!';
			echo '</div>';
		}

    }
 ?>
 <script>
$('.adduser').click(function () {
		$('#user_res').attr('user', $(this).attr('sam'));
		$('#user_res').attr('value', $(this).attr('user'));
		$('#user_res').val($(this).attr('user'));
		$('#user_res_load').empty();
		$('#user_res').parent('div').removeClass('has-error');
		return false;
        });

</script>
 