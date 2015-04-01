	function usuSaveUrl(site_url, form_id){
		//var send_email_id = jQuery("#send_email_id").val();
		//window.location = site_url + '/?send-email=true&send_email_id=' + send_email_id;
		jQuery(form_id).attr("action", site_url + '/?save-url=true');
		jQuery(form_id).submit();
	}