<?php
require_once 'config.php';
require_once 'func.php';
	$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$lang = substr($lang, 0, 2)=='de' ? "de" : "en";
	require_once '../language/'.$lang.'.php';
$timeslots = $db_link->query('SELECT starttime, endtime FROM _timeslots WHERE id = "'.$_GET['slot'].'"')->fetch_object();
$addinfos = $db_link->query('SELECT id, name, response, required FROM _additionalinfos WHERE project_id = "'.$_GET['p'].'"')->fetch_all(MYSQLI_ASSOC);

echo'<input id="booker_res" type="hidden" class="form-control" user="'.$_GET['user'].'" hidden>';
echo'<input id="p_res" type="hidden" class="form-control" p="'.$_GET['p'].'" hidden>';
echo'<input id="mode_res" type="hidden" class="form-control" mode="'.$_GET['modus'].'" hidden>';
echo'<input id="d_res" type="hidden" class="form-control" d="'.$_GET['d'].'" hidden>';
echo '<div id="modal_message"><div class="alert alert-info">'.$language['reserveinfo'].'</div></div>';
echo'<div class="control-group">
    <label  for="user_res"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> '.$language['name'].'</label>
    <div class="controls col-sm-6"><input id="user_res" type="text" class="form-control" user="'.$_GET['user'].'" value="'.ldapFullname($_GET['user']).'"></div>
</div>';
echo'<div class="clearfix"></div>';
echo'<div id="user_res_load" class="clearfix"></div>';
$counter=1;
foreach ($addinfos as $add)
{
    if($add['response'] == 'T')
    {
        $req = $add['required'] == '1' ? 'required' : '';
        echo'<div class="control-group">
        <label  for="'.$add['id'].'"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> '.$add['name'].'</label>
        <div class="controls col-sm-6"><input id="counter'.$counter.'" type="text" class="'.$req.' form-control" lab="'.$add['name'].'" value=""></div>
    </div>';
    }
    elseif($add['response'] == 'A')
    {
        $req = $add['required'] == '1' ? 'required' : '';
        $select = $db_link->query('SELECT name FROM _additionalinfos_response WHERE add_id = "'.$add['id'].'"')->fetch_all(MYSQLI_ASSOC);
        echo'<div class="control-group"><label for="'.$add['id'].'" class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> '.$add['name'].'</label>
        <div class ="col-sm-6"><select class="'.$req.' form-control" name="'.$add['id'].'" id="counters'.$counter.'">';
                echo '<option value="x"> '.$language['choose'].'</option>';
        foreach ($select as $sel)
            echo '<option value="'.$add['name'].':'.mb_convert_encoding($sel['name'], 'UTF-8', 'auto').'">'.mb_convert_encoding($sel['name'], 'UTF-8', 'auto').'</option>';
    
        echo'</select></div>
        </div>';
    }
}
echo'<div class="control-group">
    <label  for="datum_res"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i>  '.$language['date'].'</label>
    <div class="controls col-sm-6"><input id="datum_res" type="text" class="form-control" datum="'.$_GET['datum'].'" value="'.date('d.m.Y', strtotime($_GET['datum'])).'" disabled></div>
</div>';
echo'<div class="control-group">
    <label  for="timeslot_res"  class="control-label col-sm-6"><i class="fa fa-angle-double-right" aria-hidden="true"></i> '.$language['timeslot'].' '.$language['hhtime'].'</label>';
    echo'<div class="controls col-sm-6"><input id="timeslot_res" type="text" class="form-control" slot= "'.$_GET['slot'].'" value="'.date("H:i", strtotime($timeslots->starttime)).' Uhr - '.date("H:i", strtotime($timeslots->endtime)).' Uhr" disabled></div>
</div>';

echo'<button id="reserve_res" class="btn btn-success"><i class="fa fa-check-square-o" aria-hidden="true"></i> '.$language['reserve'].'</button>';
?>
<script>
$('input:text').click(
function(){
    $(this).val('');
    $(this).attr('value', '');
    $(this).attr('user', '');
});

$("#user_res").keyup(function(e)
{
    e.preventDefault();
    $('#user_res_load').load("inc/p_load_user_booking.php",
    {
        p: $('#id').val(),
        search: $("#user_res").val()
    }, function(){});
});
$('#reserve_res').click(function () {
    if($.trim($('#user_res').attr('user')) === ''){
        $('#user_res').parent('div').addClass('has-error');
		$('#counters1').parent('div').removeClass('has-error');
    }
		if($('#counters1').val() == 'x' && $('#counters1').hasClass("required"))
		{
			$('#user_res').parent('div').removeClass('has-error');
			$('#counters1').parent('div').addClass('has-error');
		}

    else
    {
		var addi = '';
		if(typeof $('#counters1').val()  !== "undefined") 
		{
			if ($('#counters1').val() !== 'x')
			{
				addi= addi + $('#counters1').val()+ ' ';
			}
		}
		if(typeof $('#counters2').val()  !== "undefined") 
		{
			if ($('#counters2').val() !== 'x')
			{
				addi= addi + $('#counters2').val()+ ' ';
			}
		}
		if(typeof $('#counters3').val()  !== "undefined") 
		{
			if ($('#counters3').val() !== 'x')
			{
				addi= addi + $('#counters3').val()+ ' ';
			}
		}
		if(typeof $('#counters4').val()  !== "undefined") 
		{
			if ($('#counters4').val() !== 'x')
			{
				addi= addi + $('#counters4').val() + ' ';
			}
		}
		if(typeof $('#counters5').val()  !== "undefined") 
		{
			if ($('#counters5').val() !== 'x')
			{
				addi= addi + $('#counters5').val()+ ' ';
			}
		}	
		if(typeof $('#counter1').val()  !== "undefined")
		{
			if($.trim($('#counter1').val()  !== ''))
			{
				addi= addi + $('#counter1').attr('lab') +':'+$('#counter1').val()+ ' ';
			}
		}
		if(typeof $('#counter2').val()  !== "undefined")
		{
			if($.trim($('#counter2').val()  !== ''))
			{
				addi= addi + $('#counter2').attr('lab') +':'+$('#counter2').val()+ ' ';
			}
		}
		if(typeof $('#counter3').val()  !== "undefined")
		{
			if($.trim($('#counter3').val()  !== ''))
			{
				addi= addi + $('#counter3').attr('lab') +':'+$('#counter3').val()+ ' ';
			}
		}
		if(typeof $('#counter4').val()  !== "undefined")
		{
			if($.trim($('#counter4').val()  !== ''))
			{
				addi= addi + $('#counter4').attr('lab') +':'+$('#counter4').val()+ ' ';
			}
		}
		if(typeof $('#counter5').val()  !== "undefined")
		{
			if($.trim($('#counter5').val()  !== ''))
			{
				addi= addi + $('#counter5').attr('lab') +':'+$('#counter5').val()+ ' ';
			}
		}
	
        $('#reserve_res').delay(3000).fadeOut(1000);
        var data = 'mode=reserve&user=' + $('#user_res').attr('user') + '&p=' + $('#p_res').attr('p') + '&booker=' + $('#booker_res').attr('user') + '&datum=' + $('#datum_res').attr('datum') + '&slot=' + $('#timeslot_res').attr('slot') + '&addi=' + addi;
        $.ajax({
            url: 'inc/p_kopfdaten_send.php',
            type: "GET",
            data: data,
            success: function (reqCode) {
                    history.pushState('', '', '?p=' + $('#p_res').attr('p') + '&d=' + $('#d_res').attr('d') + '&mode=' + $('#mode_res').attr('mode') +'#book');
                if (reqCode>0) {        
                    $.ajax(
                    {
                        method: 'POST',
                        url: 'sites/ajax/ajax_home.php',
                        data: 
                        {
                            p: $('#p_res').attr('p'),
                            mode: $('#mode_res').attr('mode'),
                            d: $('#d_res').attr('d')
                        },
                        success: function(data)
                        {
                            $('#main').html(data).promise().done(function() {init();});
                            $('.modal').modal('toggle');
                            $('#message').html('<div class="alert alert-success" role="alert">Die Buchung war erfolgreich. // The booking was successful.</div>');
                        }                      
                    });
                    
                } else{
                    $.ajax(
                    {
                        method: 'POST',
                        url: 'sites/ajax/ajax_home.php',
                        data: 
                        {
                            p: $('#p_res').attr('p'),
                            mode: $('#mode_res').attr('mode'),
                            d: $('#d_res').attr('d')
                        },
                        success: function(data)
                        {
                            $('#main').html(data).promise().done(function() {init();});
                            $('.modal').modal('toggle');
                            $('#message').html('<div class="alert alert-danger" role="alert">Die Buchungsanfrage wurde abgelehnt. Zeitgleich wurde der Termin von einem anderen Mitarbeiter reserviert. // The booking request was rejected. At the same time, the appointment was reserved by another employee.</div>');
                        }                      
                    });
                }
            }
        });
    }
    return false;
});
</script>