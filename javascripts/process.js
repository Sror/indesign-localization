/*jslint white: true, browser: true, undef: true, nomen: true, eqeqeq: true, plusplus: false, bitwise: true, regexp: true, strict: true, newcap: true, immed: true, maxerr: 14 */
/*global window: false, ActiveXObject: false*/

/*
The onreadystatechange property is a function that receives the feedback. It is important to note that the feedback
function must be assigned before each send, because upon request completion the onreadystatechange property is reset.
This is evident in the Mozilla and Firefox source.
*/

/* enable strict mode */
"use strict";

// global variables
var process,				// process element reference
	request,				// request object
	interval_id,			// interval ID
	interval = 5000,		// request interval in ms
	number_max = 3600,		// limit of how many times to request the server (this limit is needed only for this demo)
	number,					// current number of requests
	process_token,			// token may be either a real token from artwork uploading or artwork filename
	// method definition
	initXMLHttpClient,		// create XMLHttp request object in a cross-browser manner
	send_request,			// send request to the server
	request_handler,		// request handler (started from send_request)
	process_start,			// button start action
	process_end;				// button start action

// create XMLHttp request object in a cross-browser manner
initXMLHttpClient = function () {
	var XMLHTTP_IDS,
		xmlhttp,
		success = false,
		i;
	// Mozilla/Chrome/Safari/IE7+ (normal browsers)
	try {
		xmlhttp = new XMLHttpRequest(); 
	}
	// IE(?!)
	catch (e1) {
		XMLHTTP_IDS = [ 'MSXML2.XMLHTTP.5.0', 'MSXML2.XMLHTTP.4.0',
						'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP', 'Microsoft.XMLHTTP' ];
		for (i = 0; i < XMLHTTP_IDS.length && !success; i++) {
			try {
				success = true;
				xmlhttp = new ActiveXObject(XMLHTTP_IDS[i]);
			}
			catch (e2) {}
		}
		if (!success) {
			throw new Error('Unable to create XMLHttpRequest!');
		}
	}
	return xmlhttp;
};

// send request to the server
send_request = function () {
	if (number < number_max) {
		request.open('GET', 'modules/mod_art_process.php?token=' + process_token + '&rand=' + Math.random(), true);	// open asynchronus request
		request.onreadystatechange = request_handler;		// set request handler
		request.send(null);									// send request
		number++;											// increase counter
	} else {
		process_end();
	}
};

// request handler (started from send_request)
request_handler = function () {
	if (request.readyState === 4) { // if state = 4 (operation is completed)
		if(request.responseText != "")
			process.html('<div class="processlog">' + request.responseText + '</div>');
	}
};

process_start = function (processToken) {
	// set initial value for current number of requests
	number = 0;
	process_token = processToken;
	interval_id = false;
	//process = document.getElementById('processlog');
	process = $('#processlog');
    process.slideDown();
	request = initXMLHttpClient();
	if (!interval_id) {
		// start process
		interval_id = window.setInterval('send_request()', interval);
	}
};

process_end = function () {
	// abort current request if status is 1, 2, 3
	// 0: request not initialized 
	// 1: server connection established
	// 2: request received 
	// 3: processing request 
	// 4: request finished and response is ready
	if (0 < request.readyState && request.readyState < 4) {
		request.abort();
	}
	window.clearInterval(interval_id);
	interval_id = false;
};
