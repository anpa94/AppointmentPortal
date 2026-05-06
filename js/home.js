$(function()
{
	var ajaxData = {};
	if(getUrlParameter('p') === undefined)
		{
		ajaxData.mode = 'selectProject';
		}
	else
	{
		ajaxData.mode = 'loadProject';	
		ajaxData.mode = getUrlParameter('mode');
		ajaxData.p = getUrlParameter('p');
		ajaxData.d = getUrlParameter('d');
	}
	
	$.ajax(
	{
		method: 'POST',
		url: 'sites/ajax/ajax_home.php',
		data: ajaxData,
		success: function(data)
		{	
			$('#main').html(data).promise().done(function() {init();});
		}
	});

});




function init()
{
	$('[data-toggle="tooltip"]').tooltip();
	
	$('#bookinglisst').dataTable( {
		"language": {
			"url": "dataTables/German.json"
		},
		"paging":   false,
		"ordering": true,
		"info":     true

	} );
	
	$('.tdbookallowed').click(function () {
        BootstrapDialog.show({
			title: '<i class="fa fa-envelope-open-o" aria-hidden="true"></i> ' + $('#savereservation').val(),
            message: function(dialog) {
                var $message = $('<div></div>');
                var pageToLoad = dialog.getData('pageToLoad');
                $message.load(pageToLoad);
        
                return $message;
            },
            data: {
                'pageToLoad': 'inc/booking.php?datum=' + $(this).attr('tag') + '&slot=' + $(this).attr('slot') + '&p=' + $('#id').val() + '&user=' + $('#user').val() + '&d=' + $('#dud').val() + '&modus=' + $('#mode').val()
            }
		});
		return false;
	});

	$('#message').delay(12000).fadeOut(8000);	
	// Javascript to enable link to tab
	var url = document.location.toString();
	if (url.match('#')) {
		$('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
	} 

	// Change hash for page-reload
	$('.nav-tabs a').on('shown.bs.tab', function (e) {
		window.location.hash = e.target.hash;
	});

	$('.numbersOnly').keyup(function () { 
		this.value = this.value.replace(/[^0-9]/g,'');
	});
	
    $('.datepicker').datepicker({
		format: "yyyy-mm-dd",
		startDate: "2016-11-02",
		weekStart: 1,
		daysOfWeekDisabled: "0,6",
		autoclose: true,
		todayHighlight: true
	});

	$('.timepicker').timepicker({
        //template: default,
        showInputs: false,
		showMeridian: false,
        minuteStep: 15
	});
	$("#accinfo_form").submit(function(e)
    {
        e.preventDefault();
        $('#accinfo_load').load("inc/p_load_user.php",
        {
            mode: 'search',
			p: $('#id').val(),
            search: $("#accinfo_inp_groups").val()
        }, function(){});
    });
    $("#accinfo_inp_groups").keyup(function(e)
    {
        e.preventDefault();
        $('#accinfo_load').load("inc/p_load_user.php",
        {
			p: $('#id').val(),
            search: $("#accinfo_inp_groups").val()
        }, function(){});
    });

    $("#time_inp_groups").keyup(function(e)
    {
        e.preventDefault();
        $('#time_load').load("inc/p_load_time.php",
        {
			p: $('#id').val(),
            search: $("#time_inp_groups").val()
        }, function(){});
    });

	$(".single_maxbook").on('change', function(){
		var data = 'mode=single_maxbookchange&slotid=' + $(this).attr('slot_id') + '&id=' + $('#id').val() + '&wert=' +$(this).val();
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
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Die Anzahl der Buchungen wurde aktualisiert.</div>');
						}
					});
					
				} else{
					$('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Die Anzahl der Buchungen konnten nicht aktualisiert werden.</div>');
				}
			}
		});
	});
	
	$('.pr_load_fe').click(function()
	{
		history.pushState('', '', '?p=' + $(this).attr('p') + '&mode=loadProject');
		$.ajax(
		{
			method: 'POST',
			url: 'sites/ajax/ajax_home.php',
			data:
			{
				p: $(this).attr('p'),
				mode: 'loadProject'
			},
			success: function(data)
			{
				$('#main').html(data).promise().done(function() {init();});
			}
		});
	});
	$('.pr_load_be').click(function()
	{
		history.pushState('', '', '?p=' + $(this).attr('p') + '&mode=loadProjectBackend');
		$.ajax(
		{
			method: 'POST',
			url: 'sites/ajax/ajax_home.php',
			data:
			{
				p: $(this).attr('p'),
				mode: 'loadProjectBackend'
			},
			success: function(data)
			{
				$('#main').html(data).promise().done(function() {init();});
			}
		});
	});
	$('.weekchange').click(function()
	{
		history.pushState('', '', '?p=' + $('#id').val() + '&d=' + $(this).attr('datum') +'&mode=' + $('#mode').val() +'#book' );
		$.ajax(
		{
			method: 'POST',
			url: 'sites/ajax/ajax_home.php',
			data:
			{
				p: $('#id').val(),
				mode: $('#mode').val(),
				d: $(this).attr('datum')
			},
			success: function(data)
			{
				$('#main').html(data).promise().done(function() {init();});
			}
		});
	});	
	$('#send').click(function () {
		if($.trim($('#titel').val()) === ''){
			$('#titel').parent('div').addClass('has-error');
			$('#beschreibung').parent('div').removeClass('has-error');
			$('#starttime').parent('div').removeClass('has-error');
			$('#endtime').parent('div').removeClass('has-error');
		}
		else
			{
				$('#titel').parent('div').removeClass('has-error');
				$('#starttime').parent('div').removeClass('has-error');
				$('#endtime').parent('div').removeClass('has-error');
				if(encodeURIComponent($('#beschreibung').val()) === ''){
				
					$('#beschreibung').parent('div').addClass('has-error');
			}
			else
				{
					if(parseFloat($('#starttime').val()) >= parseFloat($('#endtime').val()+1))
					{
						$('#beschreibung').parent('div').removeClass('has-error');
						$('#titel').parent('div').removeClass('has-error');
						$('#starttime').parent('div').addClass('has-error');
						$('#endtime').parent('div').addClass('has-error');
					}
					else
					{
						var data = 'mode=updateProject&user=' + $('#user').val() + '&titel=' + $('#titel').val() + '&id=' + $('#id').val()  + '&beschreibung='  + encodeURIComponent($('#beschreibung').val()) + '&startdate='  + $('#startdate').val() + '&enddate='  + $('#enddate').val()+ '&starttime='  + $('#starttime').val() + '&endtime='  + $('#endtime').val();
						$.ajax({
							url: 'inc/p_kopfdaten_send.php',
							type: "GET",
							data: data,
							success: function (reqCode) {
								if (reqCode>0) {
									history.pushState('', '', '?p=' + reqCode + '&mode=loadProjectBackend#head');
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
											$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Das Projekt wurde erfolgreich angelegt / aktualisiert.</div>');
										}
									});
									
								} else{
									$('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Bei der Verarbeitung der Kopfdaten trat ein Fehler auf</div>');
								}
							}
						});
					}
				}
			}
		return false;
	});
	$('#send-mail').click(function () {
		if($.trim($('#mailbetreff').val()) === ''){
			$('#mailbetreff').parent('div').addClass('has-error');
			$('#mailsender').parent('div').removelass('has-error');
			$('#mailbody').parent('div').removeClass('has-error');
		}
		else
			{
				$('#mailbetreff').parent('div').removeClass('has-error');
				$('#mailsender').parent('div').removeClass('has-error');
				if(encodeURIComponent($('#mailbody').val()) === ''){
				
					$('#mailbody').parent('div').addClass('has-error');
			}
			else
				{
					if($.trim($('#mailsender').val()) === ''){
						$('#mailsender').parent('div').addClass('has-error');
						$('#mailbody').parent('div').removeClass('has-error');
						$('#mailbetreff').parent('div').removeClass('has-error');
					}
					else
					{
					var data = 'mode=updateProjectMail&subject=' + encodeURIComponent($('#mailbetreff').val()) + '&sender=' + encodeURIComponent($('#mailsender').val()) + '&ort=' + encodeURIComponent($('#mailort').val()) + '&id=' + $('#id').val()  + '&body='  + encodeURIComponent($('#mailbody').val());
						$.ajax({
							url: 'inc/p_kopfdaten_send.php',
							type: "GET",
							data: data,
							success: function (reqCode) {
								if (reqCode>0) {
									history.pushState('', '', '?p=' + reqCode + '&mode=loadProjectBackend#mail');
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
											$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Die Informationen wurden erfolgreich aktualisiert.</div>');
										}
									});
									
								} else{
									$('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Bei der Verarbeitung der Kopfdaten trat ein Fehler auf</div>');
								}
							}
						});
					}
				}
			}
		return false;
	});
	
	$('#send-addinfos').click(function () {
		if($.trim($('#addinfo_name').val()) === ''){
			$('#addinfo_name').parent('div').addClass('has-error');
		}
		else
			{
						var data = 'mode=addinfo&titel=' + $('#addinfo_name').val() + '&id=' + $('#id').val()  + '&response='+ $('#response').val() + '&required='+ $('#required').val();
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
											$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Die Zusatzinformationen wurden erfolgreich gespeichert.</div>');
										}
									});
									
								} else{
									$('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Bei der Verarbeitung der Kopfdaten trat ein Fehler auf</div>');
								}
							}
						});
					}
	});

	$('#delete-addinfos').click(function () {
		var data = 'mode=addinfo_del&id=' + $('#id').val() + '&row_id=' + $(this).attr('row_id');
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
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Die Zusatzinformation wurde erfolgreich gelöscht.</div>');
						}
					});
					
				} else{
					$('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Bei der Verarbeitung der Kopfdaten trat ein Fehler auf</div>');
				}
			}
		});
					
	});	
	$('#activate').click(function () {
		var data = 'mode=activate&id=' + $('#id').val() + '&wert=' + $(this).attr('wert');
		$.ajax({
			url: 'inc/p_kopfdaten_send.php',
			type: "GET",
			data: data,
			success: function (reqCode) {
				if (reqCode>0) {
					history.pushState('', '', '?p=' + reqCode + '&mode=loadProjectBackend');
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
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Der Projektstatus wurde erfolgreich angepasst.</div>');
						}
					});
					
				} else{
					$('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Der projektstatus konnte nicht angepasst werden.</div>');
				}
			}
		});
					
	});		
	$('.deleteuser').click(function () {
		var data = 'mode=deleteuser&user=' + $(this).attr('user') + '&id=' + $('#id').val() + '&row_id=' + $(this).attr('row_id');
		$.ajax({
			url: 'inc/p_kopfdaten_send.php',
			type: "GET",
			data: data,
			success: function (reqCode) {
				if (reqCode>0) {
					history.pushState('', '', '?p=' + reqCode + '&mode=loadProjectBackend#access');
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
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Der Berechtigungen wurden erfolgreich entzogen.</div>');
						}
					});
					
				} else{
		            $('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Fehler. Die Berechtigungen konnten nicht entzogen werden.</div>');
		        }
			}
		});
		return false;
	});
	$('.blockdate').click(function () {
		var data = 'mode=blockdate&day=' + $(this).attr('id') + '&id=' + $('#id').val();
		$.ajax({
			url: 'inc/p_kopfdaten_send.php',
			type: "GET",
			data: data,
			success: function (reqCode) {
				if (reqCode>0) {
					history.pushState('', '', '?p=' + reqCode + '&mode=loadProjectBackend#block');
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
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Datum erfolgreich blockiert</div>');
						}
					});
					
				} else{
		            $('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Fehler. Datum konnte nicht geblockt werden.</div>');
		        }
			}
		});
		return false;
	});
	$('.unblockdate').click(function () {
		var data = 'mode=unblockdate&day=' + $(this).attr('id') + '&id=' + $('#id').val();
		$.ajax({
			url: 'inc/p_kopfdaten_send.php',
			type: "GET",
			data: data,
			success: function (reqCode) {
				if (reqCode>0) {
					history.pushState('', '', '?p=' + reqCode + '&mode=loadProjectBackend#block');
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
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Datum erfolgreich freigegeben</div>');
						}
					});
					
				} else{
		            $('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Fehler. Datum konnte nicht freigegeben werden.</div>');
		        }
			}
		});
		return false;
	});	
	$('#time_inp_groups_delete').click(function () {
		var data = 'mode=deletetimeslots&id=' + $('#id').val();
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
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Die Definition der Terminblöcke wurde erfolgreich gelöscht.</div>');
						}
					});
					
				} else{
		            $('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Fehler. Die Terminblöcke konnten nicht gelöscht werden.</div>');
		        }
			}
		});
		return false;
	});
	$('.weekdays').change(function () {
		var data = 'mode=changeweekdates&id=' + $('#id').val() + '&day=' + $(this).attr("day");
		$.ajax({
			url: 'inc/p_kopfdaten_send.php',
			type: "GET",
			data: data,
			success: function (reqCode) {
				if (reqCode>0) {
					history.pushState('', '', '?p=' + reqCode + '&mode=loadProjectBackend#block');
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
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Die buchbaren Wochentage wurden erfolgreich aktualisiert.</div>');
						}
					});
					
				} else{
		            $('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Die buchbaren Wochentage konnten nicht aktualisiert werden.</div>');
		        }
			}
		});
		return false;
	});
	$('#select_response').change(function () {
		$.get('inc/p_addinfo_response.php?id=' + $(this).val(), function(data) {
			$('#addinfo_response_edit').html(data);
		});
	});
	$('#load-bookings').click(function () {
		$.get('inc/p_choosedate.php?date=' + $('#choosedate').val() + '&id=' + $('#id').val(), function(data) {
			$('#bookingbody').html(data);
		});
	});

	
	$('#time_inp_groups_count_btn').click(function () {
		var data = 'mode=addtime_count&counter=' + $('#time_inp_groups_count').val() + '&id=' + $('#id').val();
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
							$('#message').html('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Die Anzahl der maximalen Buchungen pro Zeitintervall wurde erfolgreich gespeichert</div>');
						}
					});
					
				} else{
		            $('#message').html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Fehler. Die Anzahl der Buchungen konnte nicht gespeichert werden.</div>');
		        }
			}
		});
		return false;
	});		

}