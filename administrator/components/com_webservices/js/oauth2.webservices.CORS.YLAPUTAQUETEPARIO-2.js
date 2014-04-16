jQuery(document).ready(function ($){

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

	// Create CORS Object
	$.extend({ 
		createCORSRequest: function(method, url) {
			var xhr = new XMLHttpRequest();
			if ("withCredentials" in xhr) {
				// XHR for Chrome/Firefox/Opera/Safari.
				xhr.open(method, url, true);
			} else if (typeof XDomainRequest != "undefined") {
				// XDomainRequest for IE.
				xhr = new XDomainRequest();
				xhr.open(method, url);
			} else {
				// CORS not supported.
				xhr = null;
			}
			return xhr;
		}
	});

	// Declare Ajax Object
	$.extend({ 
		doAjax: function () {

			var url = jQuery.trim($('#url').val());

			// Fetch the post data
			var postData = $.fetchPostData();

			// Define the beforeSend function
			var beforeSendData = function(xhr) {
				var base64 = Base64.encode($('#oauth_client_id').val());
				xhr.setRequestHeader('Authorization', 'Bearer ' + base64);
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

				xhr.withCredentials = true;

			};


			$.ajax({
				url: url,
				dataType: 'jsonp',
				beforeSend : beforeSendData,
				data: 'id=10',
				jsonp: 'jsonp_callback',
				success: function (data) {
					alert(data);
					console.log(data);
					$.dump(data);
				},
			});


/*
			//
			// Request the temporary token
			//
			$.ajax({ type: "GET",	 url: url, data: postData,
				//beforeSend : beforeSendData,
				dataType : 'jsonp',
        jsonpCallback: 'tokenResponse',
        jsonp: 'callback',
				xhrFields: {
					// The 'xhrFields' property sets additional fields on the XMLHttpRequest.
					// This can be used to set the 'withCredentials' property.
					// Set the value to 'true' if you'd like to pass cookies to the server.
					// If this is enabled, your server must respond with the header
					// 'Access-Control-Allow-Credentials: true'.
					withCredentials: true 
				},
				headers: {
					//'Origin': 'http://moncada.matware.com.ar'
				},
				//async : false,
				//crossDomain : true,

				complete: function(response) {
					console.log(response.responseText);

					// Decode the JSON response
					returnRequest = JSON.decode(response.responseText);

					// Add new values to new ajax request
					postData.oauth_code = returnRequest.oauth_code;
					postData.oauth_response_type = 'authorise';

					//
					// Request the authorization token
					//
					$.ajax({ 	type: "POST",	url: $('#url').val(), dataType : 'jsonp',	data: postData,	beforeSend : beforeSendData,
						complete: function(response) {
							// Decode the JSON response
							returnRequest = JSON.decode(response.responseText);

							// Add new values to new ajax request
							postData.oauth_code = returnRequest.oauth_code;
							postData.oauth_response_type = 'token';

							//
							// Request the access_token
							//
							$.ajax({ 	type: "POST",	url: $('#url').val(), dataType : 'jsonp',	data: postData,	beforeSend : beforeSendData,
								complete: function(response) {
									// Set the access token reponse to HTML
									$('#returnDiv').html(response.responseText);
								}
							}); // end ajax
						}

					}); // end ajax


				}



			}); // end ajax
*/



/*
	//// XMLHttpRequest

			//var cors = $.createCORSRequest();

			var xhr = $.createCORSRequest('GET', url);
			if (!xhr) {
				alert('CORS not supported');
				return;
			}

			// Response handlers.
			xhr.onload = function() {
				var text = xhr.responseText;
				var title = getTitle(text);
				alert('Response from CORS request to ' + url + ': ' + title);
			};

			xhr.onerror = function() {
				alert('Woops, there was an error making the request.');
			};

			var base64 = Base64.encode($('#oauth_client_id').val());
			xhr.setRequestHeader('Authorization', 'Bearer ' + base64);

			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			//xhr.setRequestHeader("Content-length", params.length);
			xhr.setRequestHeader("Connection", "close");

			//beforeSend : beforeSendData,

			xhr.send();
*/




		}

	});

	// Add onclick event to request the token
	document.id('ajax-button').addEvent('click', function(e) {
		// Prevent the propagation
		e.stopPropagation();
		e.preventDefault();
		// Run the OAuth2 access token request
		$.doAjax();
	});
});
