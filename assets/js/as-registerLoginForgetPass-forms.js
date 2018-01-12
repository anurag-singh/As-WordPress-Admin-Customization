jQuery(document).ready(function($){
	$('#user_register_t_and_cc').click(function() {
		alert('clicked');
		$('#user_register_t_and_cc').prop('indeterminate', true);
	});


	// Login form
	jQuery("#as-login").submit(function(){
		event.preventDefault();
		$("#as-login").validate({
			rules: {
				// simple rule, converted to {required:true}
				//name: "required",
				// compound rule
				user_email: {
					required: true,
					email: true
				}
			}

			,submitHandler: function() {	// If form validated
				var email = $('#user_email').val();
				var pass = $('#user_pass').val();
				var ajax_url = ajax_object.ajax_url;
				var dashboardUrl = ajax_object.home_url + '/dashboard/';

				$.ajax({
					url: ajax_url
					,type: 'POST'
					,dataType: 'json'
					,data: {
						action: 'get_user_logged_in'
						,user_email: email
						,user_pass: pass
					}
					,beforeSend : function() {
						jQuery("#form-action-status").addClass("alert-info");
						jQuery("#form-action-status").html("Sending.");
						jQuery("#form-action-status").show();
					}
					,success: function(response){
						jQuery("#form-action-status").hide();
						jQuery("#form-action-status").removeClass("alert-info");
						jQuery("#form-action-status").html(response.msg);
						if(response.status == 1) {
	                        jQuery("#form-action-status").addClass("alert-success");
							jQuery("#form-action-status").show();
	                        setTimeout(function() {
	                        	window.location.replace(dashboardUrl);	// Redirect user
	                        }, 3000);
	                    } else {
	                        jQuery("#form-action-status").addClass("alert-danger");
	                    	jQuery("#form-action-status").show();
	                        setTimeout(function() {
	                            jQuery('#form-action-status').fadeOut();
	                            jQuery("#form-action-status").removeClass("alert-danger");
	                        }, 5000);

	                    }
					}
					,error: function(xhr) { // if error occured
						jQuery("#form-action-status").show();
			        	jQuery("#form-action-status").html(xhr.statusText + xhr.responseText);
			        	alert("Error occured.please try again");
			    },
				});



			}
		});
	});
	// Login form


	// Forget password form
	jQuery("#as-forget-password").submit(function(){
		event.preventDefault();
		$("#as-forget-password").validate({
			rules: {
				user_email: {
					required: true,
					email: true
				}
			}

			,submitHandler: function() {	// If form validated
				var email = $('#user_email').val();
				var ajax_url = ajax_object.ajax_url;
				var loginPageUrl = ajax_object.home_url + '/login/';

				$.ajax({
					url: ajax_url
					,type: 'POST'
					,dataType: 'json'
					,data: {
						action: 'send_user_password_reset_email'
						,user_email: email
					}
					,beforeSend : function() {
						jQuery("#form-action-status").addClass("alert-info");
						jQuery("#form-action-status").show();
						jQuery("#form-action-status").html("Sending.");
					}
					,success: function(response){
						jQuery("#form-action-status").hide();
						jQuery("#form-action-status").removeClass("alert-info");
						jQuery("#form-action-status").html(response.msg);
						if(response.status == 1) {
	                        jQuery("#form-action-status").addClass("alert-success");
							jQuery("#form-action-status").show();
	                        setTimeout(function() {
	                        	window.location.replace(loginPageUrl);	// Redirect user
	                        }, 3000);
	                    } else {
	                        jQuery("#form-action-status").addClass("alert-danger");
	                        jQuery("#form-action-status").show();
	                        setTimeout(function() {
	                            jQuery('#form-action-status').fadeOut();
	                            jQuery("#form-action-status").removeClass("alert-danger");
	                        }, 5000);

	                    }
					}
					,error: function(xhr) { // if error occured
						jQuery("#form-action-status").show();
			        	jQuery("#form-action-status").html(xhr.statusText + xhr.responseText);
			        	alert("Error occured.please try again");
			    },
				});



			}
		});
	});

	// Forget password form


	// Register form
	$("#as-register").submit(function(){
		event.preventDefault();
		$("#as-register").validate({

			rules: {
				user_register_first_name: {
					required: true,
					minlength: 2,
				}
				,user_register_email: {
					required: true,
					minlength: 6,
					email: true
				}
				,user_register_password: {
					required: true,
					minlength: 6,
				}
				,user_register_password_confirmation: {
			      equalTo: "#user_register_password"
			    }
				,user_register_t_and_c: {
					required: true,
				}
			}

			,submitHandler: function() {	// If form validated
				var ajax_url = ajax_object.ajax_url;
				var loginPageUrl = ajax_object.home_url + '/login/';

				var register_f_name = $("#user_register_first_name").val();
				var register_l_name = $("#user_register_last_name").val();
				var register_email = $("#user_register_email").val();
				var register_password = $("#user_register_password").val();


				$.ajax({
					url: ajax_url
					,type: 'POST'
					,dataType: 'json'
					,data: {
						action: 'register_user_for_site'
						,register_user_f_name: register_f_name
						,register_user_l_name: register_l_name
						,register_user_email: register_email
						,register_user_password: register_password

					}
					,beforeSend : function() {
						jQuery("#form-action-status").addClass("alert-info");
						jQuery("#form-action-status").show();
						jQuery("#form-action-status").html("Sending.");
					}
					,success: function(response){
						jQuery("#form-action-status").hide();
						jQuery("#form-action-status").removeClass("alert-info");
						jQuery("#form-action-status").html(response.msg);
						if(response.status == 1) {
	                        jQuery("#form-action-status").addClass("alert-success");
							jQuery("#form-action-status").show();
	                        setTimeout(function() {
	                        	window.location.replace(loginPageUrl);	// Redirect user
	                        }, 3000);
	                    } else {
	                        jQuery("#form-action-status").addClass("alert-danger");
	                        jQuery("#form-action-status").show();
	                        setTimeout(function() {
	                            jQuery('#form-action-status').fadeOut();
	                            jQuery("#form-action-status").removeClass("alert-danger");
	                        }, 5000);

	                    }
					}
					,error: function(xhr) { // if error occured
						jQuery("#form-action-status").show();
			        	jQuery("#form-action-status").html(xhr.statusText + xhr.responseText);
			        	alert("Error occured.please try again");
			    	},
				});
			}

		});
	});
	// Register form
});