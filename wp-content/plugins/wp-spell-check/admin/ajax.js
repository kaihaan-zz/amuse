function init_scan() {
	jQuery.ajax({
		url: ajax_object.ajax_url,
		type: "POST",
		data: {
			action: 'results_sc',
		},
		dataType: 'html',
		success: function(response) {
			if (response == 'true') { recheck_scan(); console.log(response); }
			else { window.setInterval( init_scan(),1000 ); console.log(response); }
		}
	});
}

function recheck_scan() {
	jQuery.ajax({
		url: ajax_object.ajax_url,
		type: "POST",
		data: {
			action: 'results_sc',
		},
		dataType: 'html',
		success: function(response) {
			if (response == 'true') { window.setInterval(recheck_scan(), 1000 ); console.log(response); }
			else { finish_scan(); console.log(response); }
		}
	});
}

function finish_scan() {
	jQuery.ajax({
		url: ajax_object.ajax_url,
		type: "POST",
		data: {
			action: 'finish_scan',
		},
		dataType: 'html',
		success: function(response) {
			window.location.href = encodeURI("?page=wp-spellcheck.php&wpsc-script=noscript&wpsc-scan-tab=" + ajax_object.wpsc_scan_tab);
		}
	});
}

window.setInterval( recheck_scan(),1000 );