function notifAlert(message,title,buttonName,callback){
	function alertDismissed() {
		// do something
	}
	if(typeof title === 'undefined'){
		title = "{alert}";
	}
	if(typeof buttonName === 'undefined'){
		buttonName = "{ok}";
	}
	if(typeof callback === 'undefined'){
		callback = alertDismissed;
	}
	title = translateScript(title);
	message = translateScript(message);
	buttonName = translateScript(buttonName);
	if(navigator.notification){
		// Jika Undefined, anda harus lakukan dengan Phonegap
		navigator.notification.alert(
			message,  			// message
			callback,		// callback
			title,            	// title
			buttonName			// buttonName
		);
	}else{
		alert(message);
	}
}

// Show a custom confirmation dialog
function notifConfirm(message,title,buttonLabels,callback) {
	// process the confirmation dialog result
	function onConfirm(button) {
		alert('You selected button ' + button);
	}
	if(typeof title === 'undefined'){
		title = "Confirmation";
	}
	if(typeof buttonLabels === 'undefined'){
		buttonLabels = ['{ok}','{batal}'];
	}
	if(typeof callback === 'undefined'){
		callback = onConfirm;
	}
	buttonLabels.toString();
	gabung = translateScript(title+'####'+message+'####'+buttonLabels);
	txtgabung = gabung.split('####');
	title = txtgabung[0];
	message = txtgabung[1];
	buttonLabels2 = txtgabung[2].split(',');
	// Jika Undefined, anda harus lakukan dengan Phonegap
	if(navigator.notification){
		navigator.notification.confirm(
			message, 	 	// message
			callback,		// callback to invoke with index of button pressed
			title,			// title
			buttonLabels2	// buttonLabels
		);
	}else{
		var res = confirm(message);
		if(typeof callback !== 'undefined'){
			callback(res);
		}
	}
}
function notifPrompt(message,title,buttonLabels,callback,defaultText){
	//function onPrompt(results) {
	//	alert("You selected button number " + results.buttonIndex + " and entered " + results.input1);
	//}
	if(typeof buttonLabels == 'undefined'){
	buttonLabels = ['{ok}','{keluar}'];
	}
	
	buttonLabels.toString();
	gabung = translateScript(title+'####'+message+'####'+buttonLabels);
	txtgabung = gabung.split('####');
	title = txtgabung[0];
	message = txtgabung[1];
	listButtonName = txtgabung[2].split(',');
	// Jika Undefined, anda harus lakukan dengan Phonegap
	if(navigator.notification){
		navigator.notification.prompt(
			message,  		// message
			callback,		// callback to invoke
			title,			// title
			listButtonName,	// buttonLabels
			defaultText		// defaultText
		);
	}else{
		var res = prompt(message,title);
		if(typeof callback !== 'undefined'){
			callback(res);
		}
	}
}
function notifBeep(tm){
	navigator.notification.beep(tm);
}
function notifVibrate(tm){
	navigator.notification.vibrate(tm);
}