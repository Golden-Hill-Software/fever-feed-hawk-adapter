/*
 * test.js
 */

/*
 * If value is false, disable all form elements. If value is true, enable all form
 * elements.
 */
function setFormElementsEnabled(value) {
	var elements = document.querySelectorAll('input');
	for( var i = 0 ; i < elements.length; i++ ) {
		elements.item(i).disabled = !value;
	}
}

/*
 * Remove all child elements from the specified element.
 */
function clearChildElements(element) {
	while(element.childNodes.length > 0) {
		element.removeChild(element.childNodes.item(element.childNodes.length-1));
	}
}

/*
 * Set the current status message to the specified string.
 */
function setCurrentStatus(str) {
	var currentStatusPara = document.querySelector('#currentStatusPara');
	if (currentStatusPara != null) {
		clearChildElements(currentStatusPara);
		currentStatusPara.appendChild(document.createTextNode(str));
	}
}

/*
 * Show that the test failed with the specified error message.
 */
function testFailed(message) {
	setCurrentStatus(message);
	setFormElementsEnabled(true);
}

/*
 * Show that the test succeeded with the specified message.
 */
function testSucceeded(message) {
	setCurrentStatus(message);
	setFormElementsEnabled(true);
}

/*
 * Derive the Adapter URL based on the URL of the HTML page.
 */
function relativeUrlForAdapter() {
	var thisUrl = document.location.href;
	lastSlash = thisUrl.lastIndexOf('/');
	if (lastSlash == -1) {
		return thisUrl + '/adapter.php';
	} else {
		return thisUrl.substring(0, lastSlash) + '/adapter.php';
	}
}

/*
 * Request and sanity check the subscription list with the specified username and password.
 */
function requestSubscriptionList(email, password) {
	var xhr = new XMLHttpRequest();
	xhr.open('POST', 'adapter.php');
	xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
	var args = { auth: { email: email, password: password }, operation: "list_subscriptions" };
	var args_json = JSON.stringify(args);
	xhr.onload = function() {
		if (xhr.status === 200) {
			var jsonResponse = null;
			try {
				jsonResponse = JSON.parse(xhr.responseText);
			} catch (err) {
				testFailed('Unable to parse the response.');
				return;
			}
			if (!jsonResponse.authenticated) {
				testFailed('The email address and password combination was rejected.');
			} else if (jsonResponse.response == null) {
				testFailed('The subscription list was not specified.');
			} else {
				testSucceeded('Test succeeded.');
				var adapterUrl = relativeUrlForAdapter();
				var adapterUrlPara = document.querySelector('#adapterUrlPara');
				clearChildElements(adapterUrlPara);
				var link = document.createElement('a');
				link.setAttribute('href', adapterUrl);
				link.appendChild(document.createTextNode(adapterUrl));
				adapterUrlPara.appendChild(document.createTextNode('URL of Fever-Feed Hawk Adapter: '));
				adapterUrlPara.appendChild(link);
			}
		} else {
			testFailed('The request to retrieve the subscription list failed.  Fever-Feed Hawk Adapter returned a status of ' + xhr.status + '. Check the server log for details.');
		}
	};
	xhr.onerror = function() {
		testFailed('The request to Fever-Feed Hawk Adapter failed.');
	};
	xhr.ontimeout = xhr.onerror;
	xhr.send(args_json);
}

/*
 * Initialize the page.
 */

var form = document.querySelector('form');
if (document.location.protocol.toLowerCase() != 'https:') {
	setCurrentStatus('Fever-Feed Hawk Adapter and this test script requires HTTPS.');
} else if (form != null) {
	form.style.display = 'block';
}

var emailInput = document.querySelector('#emailInput');
var passwordInput = document.querySelector('#passwordInput');

var form = document.querySelector('form');
if (form != null) {
	form.onsubmit = function() {
	
		var email = null;
		var password = null;
	
		if (emailInput != null) {
			email = emailInput.value.trim();
		}
		if (passwordInput != null) {
			password = passwordInput.value;
		}
	
		if ((email == null) || (email.length == 0)) {
			setCurrentStatus('Fever Email Address not specified.');
			if (emailInput != null) {
				emailInput.focus();
				return;
			}
		}
	
		if ((password == null) || (password.length == 0)) {
			setCurrentStatus('Fever Password not specified.');
			if (passwordInput != null) {
				passwordInput.focus();
				return;
			}
		}
	
		setFormElementsEnabled(false);
		setCurrentStatus('Performing test\u2026');
	
		requestSubscriptionList(email, password);
	};
}