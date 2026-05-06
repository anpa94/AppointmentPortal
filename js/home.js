var activeMainRequest = null;
var activePartialRequest = null;

if (typeof getUrlParameters !== 'function') {
	function getUrlParameters() {
		var params = {};
		var search = window.location.search.substring(1);
		if (!search) {
			return params;
		}

		search.split('&').forEach(function (part) {
			if (!part) {
				return;
			}
			var pair = part.split('=');
			var key = decodeURIComponent(pair[0] || '');
			if (!key) {
				return;
			}
			params[key] = decodeURIComponent(pair[1] || '');
		});

		return params;
	}
}

if (typeof buildPortalUrl !== 'function') {
	function buildPortalUrl(params, hash) {
		var safeParams = params || {};
		var url = window.location.pathname;
		if (safeParams.mode === 'ProjectBackend' && safeParams.p) {
			url = '/ProjectBackend/' + encodeURIComponent(safeParams.p);
		} else {
			var query = Object.keys(safeParams).filter(function (key) {
				return safeParams[key] !== undefined && safeParams[key] !== null && safeParams[key] !== '';
			}).map(function (key) {
				return encodeURIComponent(key) + '=' + encodeURIComponent(safeParams[key]);
			}).join('&');
			url = query ? '?' + query : window.location.pathname;
		}
		if (hash) {
			return url + '#' + hash.replace(/^#/, '');
		}

		return url;
	}
}

function setMainLoading(isLoading) {
	$('#main').attr('aria-busy', isLoading ? 'true' : 'false');
	if (isLoading) {
		$('#main').addClass('is-loading');
	} else {
		$('#main').removeClass('is-loading');
	}
}

function buildHomeRequestFromUrl() {
	var path = window.location.pathname.replace(/^\/+|\/+$/g, '').split('/');
	if (path.length >= 2 && path[0] === 'ProjectBackend') {
		return { mode: 'ProjectBackend', p: decodeURIComponent(path[1]) };
	}

	var urlParams = getUrlParameters();
	var projectId = urlParams.p;
	if (projectId === undefined || projectId === null || projectId === '') {
		return { mode: 'selectProject' };
	}

	return {
		mode: urlParams.mode || 'loadProject',
		p: projectId,
		d: urlParams.d
	};
}

function loadMainContent(ajaxData, nextUrl, onDone) {
	if (nextUrl) {
		history.pushState('', '', nextUrl.replace(/#$/, ''));
	}
	if (activeMainRequest && activeMainRequest.readyState !== 4) {
		activeMainRequest.abort();
	}

	setMainLoading(true);
	activeMainRequest = $.ajax({
		method: 'POST',
		url: 'sites/ajax/ajax_home.php',
		data: ajaxData
	}).done(function (data) {
		$('#main').html(data).promise().done(function() { init(); });
		if (typeof onDone === 'function') {
			onDone();
		}
	}).fail(function (xhr, status) {
		if (status !== 'abort') {
			$('#message').html('<div class="alert alert-danger" role="alert">Seite konnte nicht geladen werden. Bitte erneut versuchen.</div>');
		}
	}).always(function () {
		setMainLoading(false);
	});
}

function debounce(fn, waitMs) {
	var timer = null;
	return function () {
		var context = this;
		var args = arguments;
		clearTimeout(timer);
		timer = setTimeout(function () {
			fn.apply(context, args);
		}, waitMs);
	};
}

$(function() {
	loadMainContent(buildHomeRequestFromUrl());
	window.addEventListener('popstate', function () {
		loadMainContent(buildHomeRequestFromUrl());
	});
});




function init()
{
	$('[data-toggle="tooltip"]').tooltip();
	
	if ($.fn.dataTable && $('#bookinglist').length) {
		$('#bookinglist').dataTable({
			"language": {
				"url": "datatables/German.json"
			},
			"paging": false,
			"ordering": true,
			"info": true
		});
	}
	
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
		if (e.target.hash) {
			history.replaceState('', '', buildPortalUrl(getUrlParameters(), e.target.hash));
		}
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
    $("#accinfo_inp_groups").keyup(debounce(function(e)
    {
        e.preventDefault();
		if (activePartialRequest && activePartialRequest.readyState !== 4) {
			activePartialRequest.abort();
		}
        activePartialRequest = $.ajax({
			url: "inc/p_load_user.php",
			method: "POST",
			data: {
				p: $('#id').val(),
				search: $("#accinfo_inp_groups").val()
			}
		}).done(function (data) {
			$('#accinfo_load').html(data);
		});
    }, 250));

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
					history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'booking'));
					$.ajax(
					{
						method: 'POST',
						url: 'sites/ajax/ajax_home.php',
						data:
						{
							p: reqCode,
							mode: 'ProjectBackend'
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
		var pid = $(this).attr('p');
		loadMainContent({ p: pid, mode: 'loadProject' }, buildPortalUrl({ p: pid, mode: 'loadProject' }));
	});
	$('.pr_load_be').click(function()
	{
		var pid = $(this).attr('p');
		loadMainContent({ p: pid, mode: 'ProjectBackend' }, buildPortalUrl({ p: pid, mode: 'ProjectBackend' }));
	});
	$('.weekchange').click(function()
	{
		var projectId = $('#id').val();
		var dateValue = $(this).attr('datum');
		var mode = $('#mode').val();
		loadMainContent(
			{ p: projectId, mode: mode, d: dateValue },
			buildPortalUrl({ p: projectId, d: dateValue, mode: mode }, 'book')
		);
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
									history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'head'));
									$.ajax(
									{
										method: 'POST',
										url: 'sites/ajax/ajax_home.php',
										data:
										{
											p: reqCode,
											mode: 'ProjectBackend'
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
					var data = 'mode=updateProjectMail&subject=' + encodeURIComponent($('#mailbetreff').val()) + '&sender=' + encodeURIComponent($('#mailsender').val()) + '&ort=' + encodeURIComponent($('#mailort').val()) + '&id=' + $('#id').val()  + '&body='  + encodeURIComponent($('#mailbody').val()) + '&mailtype=' + encodeURIComponent($('#mailtype').val());
						$.ajax({
							url: 'inc/p_kopfdaten_send.php',
							type: "GET",
							data: data,
							success: function (reqCode) {
								if (reqCode>0) {
									history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'mail'));
									$.ajax(
									{
										method: 'POST',
										url: 'sites/ajax/ajax_home.php',
										data:
										{
											p: reqCode,
											mode: 'ProjectBackend'
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
									history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'addinfos'));
									$.ajax(
									{
										method: 'POST',
										url: 'sites/ajax/ajax_home.php',
										data:
										{
											p: reqCode,
											mode: 'ProjectBackend'
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
					history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'addinfos'));
					$.ajax(
					{
						method: 'POST',
						url: 'sites/ajax/ajax_home.php',
						data:
						{
							p: reqCode,
							mode: 'ProjectBackend'
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
					history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }));
					$.ajax(
					{
						method: 'POST',
						url: 'sites/ajax/ajax_home.php',
						data:
						{
							p: reqCode,
							mode: 'ProjectBackend'
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
					history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'access'));
					$.ajax(
					{
						method: 'POST',
						url: 'sites/ajax/ajax_home.php',
						data:
						{
							p: reqCode,
							mode: 'ProjectBackend'
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
					history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'block'));
					$.ajax(
					{
						method: 'POST',
						url: 'sites/ajax/ajax_home.php',
						data:
						{
							p: reqCode,
							mode: 'ProjectBackend'
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
					history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'block'));
					$.ajax(
					{
						method: 'POST',
						url: 'sites/ajax/ajax_home.php',
						data:
						{
							p: reqCode,
							mode: 'ProjectBackend'
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
					history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'booking'));
					$.ajax(
					{
						method: 'POST',
						url: 'sites/ajax/ajax_home.php',
						data:
						{
							p: reqCode,
							mode: 'ProjectBackend'
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
					history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'block'));
					$.ajax(
					{
						method: 'POST',
						url: 'sites/ajax/ajax_home.php',
						data:
						{
							p: reqCode,
							mode: 'ProjectBackend'
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
					history.pushState('', '', buildPortalUrl({ p: reqCode, mode: 'ProjectBackend' }, 'booking'));
					$.ajax(
					{
						method: 'POST',
						url: 'sites/ajax/ajax_home.php',
						data:
						{
							p: reqCode,
							mode: 'ProjectBackend'
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
