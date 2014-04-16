jQuery(document).ready(function ($){

	// Hide resource button
	$('#resource').hide();

	// Extend jQuery to add random string generator
	$.extend({ 
		randomKey: function (length, special) {
		  var iteration = 0;
		  var randomKey = "";
		  var randomNumber;
		  if(special == undefined){
		      var special = false;
		  }
		  while(iteration < length){
		      randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
		      if(!special){
		          if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
		          if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
		          if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
		          if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
		      }
		      iteration++;
		      randomKey += String.fromCharCode(randomNumber);
		  }
		  return randomKey;
		}
	});

	// Extend jQuery to add JSON HTTP request
	$.extend({ 
		fetchPostData: function () {

			// Get the values from html input's
			var username = $('#oauth_client_id').val();
			var password = $('#oauth_client_secret').val();
			var random_key = $.randomKey(36);

			// Encode the data to send
			var user_encode = Base64.encode(username +':'+ random_key);
			var pass_encode = Base64.encode($.randomKey(36)) +':'+ Base64.encode(password+':'+random_key);

			// Prepare the return object
			$.postData = {};
			$.postData.oauth_client_id = user_encode;
			$.postData.oauth_client_secret = Base64.encode(pass_encode);
			$.postData.oauth_response_type = 'temporary';
			$.postData.oauth_signature_method = 'PLAINTEXT';

			return $.postData;
		}
	});

	// Declare Authorization Object
	$.extend({ 
		doAuthorise: function () {

			// Fetch the post data
			var postData = $.fetchPostData();

			// Define the beforeSend function
			var beforeSendData = function(xhr) {
							var base64 = Base64.encode($('#oauth_client_id').val());
							xhr.setRequestHeader('Authorization', 'Bearer ' + base64);
			};

			//
			// Request the temporary token
			//
			$.ajax({ 	type: "POST",	url: $('#url').val(),	dataType : 'jsonp',	data: postData,	beforeSend : beforeSendData,
				complete: function(response) {

					// Decode the JSON response
					returnRequest = response.responseJSON;

					// Add new values to new ajax request
					postData.oauth_code = returnRequest.oauth_code;
					postData.oauth_response_type = 'authorise';

					//
					// Request the authorization token
					//
					$.ajax({ 	type: "POST",	url: $('#url').val(), dataType : 'jsonp',	data: postData,	beforeSend : beforeSendData,
						complete: function(response) {

							// Decode the JSON response
							returnRequest = response.responseJSON;

							// Add new values to new ajax request
							postData.oauth_code = returnRequest.oauth_code;
							postData.oauth_response_type = 'token';

							//
							// Request the access_token
							//
							$.ajax({ 	type: "POST",	url: $('#url').val(), dataType : 'jsonp',	data: postData,	beforeSend : beforeSendData,
								complete: function(response) {

									// Hide resource button
									$('#resource').show();

									var html = '<b>Access Token:</b>'+response.responseJSON.access_token;
									html = html + '<br><b>Expires In:</b>'+response.responseJSON.expires_in;
									html = html + '<br><b>Refresh Token:</b>'+response.responseJSON.refresh_token;

									// Set the access token reponse to HTML
									$('#returnDiv').html(html);
								}
							}); // end ajax
						}

					}); // end ajax
				}

			}); // end ajax
		}

	});

	// Declare Resource Object
	$.extend({ 
		getResource: function () {

			// Get the values from html input's
			var username = $('#oauth_client_id').val();
			var client_id = Base64.encode(username);

			// Define the beforeSend function
			var beforeSendData = function(xhr) {
							xhr.setRequestHeader('Authorization', 'Bearer ' + client_id);
			};

			//
			// Request the resource
			//
			$.ajax({ 	type: "GET", 	url: $('#url').val(),	dataType : 'jsonp',	beforeSend : beforeSendData,
				complete: function(response) {

					var json_html = $('#returnDiv').html();
					$('#returnDiv').html(json_html+'<br><br>'+response.responseJSON);

				}
			}); // end ajax
		}

	});

	// Add onclick event to request the token
	document.id('authorise').addEvent('click', function(e) {
		// Prevent the propagation
		e.stopPropagation();
		e.preventDefault();

		// Run the OAuth2 access token request
		$.doAuthorise();
	});

	// Add onclick event to request the token
	document.id('resource').addEvent('click', function(e) {
		// Prevent the propagation
		e.stopPropagation();
		e.preventDefault();

		// Run the resource function
		$.getResource();
	});

});
