function openQrFrame(){
	var content = "";
	var lp = (30/150); // line persent
	wW = window.innerWidth;
	wH = (window.innerHeight-90-47);
	var rotation = "";
	 
	if(wW<wH){
		rotation = "p";
		rectW = (wW*60/100);
		rectH = rectW;
	}else{
		rotation = "l";
		rectH = (wH*60/100);
		rectW = rectH;
	}
	var lineW = (rectW*lp);
	var halfLine = (lineW/2);
	if(rotation == "p"){
		rectX = halfLine;
		rectY = halfLine;
	}
	w = (rectW+lineW);
	h = (rectH+lineW);
	var marginTop = ((wH-h)/2);
	midY = (rectY+(rectH/2));
	midX = (rectX+(rectW/2));
	content += '<div class="cover-qracanner" style="margin-top:'+marginTop+'px;width:'+rectW+'px;height:'+rectH+'px;">';
	content += '<svg width="'+w+'" height="'+h+'" style="margin-top:-'+halfLine+'px;margin-left:-'+halfLine+'px;">';
	content += '<rect x="'+rectX+'" y="'+rectY+'" width="'+rectW+'" height="'+rectH+'" style="fill:white;stroke:white;stroke-width:1.5;stroke-opacity:0.5;fill-opacity:0" />';
	content += '<line x1="'+(rectX-halfLine)+'" y1="'+midY+'" x2="'+(rectX+halfLine)+'" y2="'+midY+'" style="stroke:white;stroke-width:1.5;stroke-opacity:0.5;" />';
	
	content += '<line x1="'+(rectX+rectW-halfLine)+'" y1="'+midY+'" x2="'+(rectX+rectW+halfLine)+'" y2="'+midY+'" style="stroke:white;stroke-width:1.5;stroke-opacity:0.5;" />';
	content += '<line x1="'+midX+'" y1="'+(rectY-halfLine)+'" x2="'+midX+'" y2="'+(rectY+halfLine)+'" style="stroke:white;stroke-width:1.5;stroke-opacity:0.5;" />';
	content += '<line x1="'+midX+'" y1="'+(rectY+rectH-halfLine)+'" x2="'+midX+'" y2="'+(rectY+rectH+halfLine)+'" style="stroke:white;stroke-width:1.5;stroke-opacity:0.5;" />';

	content += '<line x1="'+rectX+'" y1="'+rectY+'" x2="'+(rectX+rectW)+'" y2="'+rectY+'" style="stroke:red;stroke-width:2;stroke-opacity:0.5;">';
	content += '<animate attributeName="y1" from="'+rectY+'" to="'+(rectY+rectH)+'" begin="0s" dur="3s" repeatCount="indefinite"/>';
	content += '<animate attributeName="y2" from="'+rectY+'" to="'+(rectY+rectH)+'" begin="0s" dur="3s" repeatCount="indefinite"/>';
	content += '</line>';
	content += '</svg></div><div class="tools-qracanner"><div class="row 	m-b-20"><div class="col-xs-6"><button class="btn-qracanner pull-right blitz" onclick="lightEnabled(this);"><i class="fa fa-bolt" aria-hidden="true"></i></button></div><div class="col-xs-6"><button class="btn-qracanner switch-camera m-l-15" onclick="switch_camera(this);"><i class="fa fa-camera" aria-hidden="true"></i></button></div></div></div>';
	
	frame_panel('panelScanQrFrame','QR Reader',content,'openJendelaQr()');
}

function closeQrFrame(type){
	if(typeof type !== 'undefined' && type == "hide"){
		panelScanQrFrame = document.getElementById('panelScanQrFrame');
		panelScanQrFrame.style.opacity = 0;
	}else if(typeof type !== 'undefined' && type == "denied"){
		QRScanner.destroy();
	}else{
		QRScanner.destroy();
		closePanel('panelScanQrFrame');
	}
}
function lightEnabled(ele){
	QRScanner.getStatus(function(status){
		if(status.showing == true){
			if(status.lightEnabled == false){
				QRScanner.enableLight(function(err, status){
					ele.classList.add("active");
					if(err){
						notifAlert(err._message,'{error}');
					}
				});
			}else{
				QRScanner.disableLight(function(err, status){
					ele.classList.remove("active");
					if(err){
						notifAlert(err._message,'{error}');
					}
				});
			}
		}	
	});
}
function switch_camera(ele){
	var camera = 0;
	var lastCam = 1;
	QRScanner.getStatus(function(status){
		if(status.showing == true){
			if(status.currentCamera == camera){
				camera = 1;
				lastCam = 0;
			}
			QRScanner.useCamera(camera, function(err, status){
				ele.classList.remove("active_"+lastCam);
				ele.classList.add("active_"+camera);
				if(err){
					notifAlert(err._message,'{error}');
				}
			});
		}
	});
}
function openJendelaQr(){
	var body = document.body;
	var html = body.parentNode;
	var homePanel = document.getElementById('owl-content');
	body.style.backgroundColor = "transparent";
	html.style.backgroundColor = "transparent";
	homePanel.style.display = "none";
	var panel = document.getElementsByClassName('panel');
	if(panel.length > 0){
		for(i=0; i<panel.length; i++){
			if(panel[i].id !== 'panelScanQrFrame'){
				panel[i].classList.add("gulung_panel");
			}else{
				//frame
				panel[i].style.backgroundColor = "transparent";
				panel[i].style.opacity = 1;
				panel[i].classList.add("scanqr-mode");
				document.getElementById('body_panelScanQrFrame').style.backgroundColor = "transparent";
			}
		}
	}
}
function closeJendelaQr(type){
	var body = document.body;
	var html = body.parentNode;
	var homePanel = document.getElementById('owl-content');
	body.style.backgroundColor = null;
	html.style.backgroundColor = null;
	homePanel.style.display = null;
	var panel = document.getElementsByClassName('panel');
	if(panel.length > 0){
		for(i=0; i<panel.length; i++){
			if(panel[i].id !== 'panelScanQrFrame'){
				panel[i].classList.remove("gulung_panel");
			}else{
				//frame
				closeQrFrame(type);
			}
		}
	}
}
function testscanQRbtn(text){
	notifAlert(text,"Result");
	
	/*
	openQrFrame();
	QRScanner.scan(function(err, text){
		alert(text);
		closeJendelaQr();
		closePanel();
	});
	QRScanner.show();
	*/
}
function execFuncQRScanner(functionName, context , args) {
  var argText = [];
  var namespaces = [];
  if(args && args !== ''){
	argText.push(args);
  }
  if(functionName && functionName !== ''){
	namespaces = functionName.split(".");
  }
  if(namespaces.length > 0){
	  var func = namespaces.pop();
	  for(var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	  }
	  return context[func].apply(context, argText);
  }else{
	return true;
  }
}

function scanQrContinue(newFunction){
	if(!isCordova()){
		scanForBrowser(newFunction);
		return false;
	}
	QRScanner.getStatus(function(status){
		if(!status.prepared){
			QRScanner.prepare(readCode);
		}else{
			openQrFrameContinue();
		}
	});
	function scanedQr(err, text){
		if (err){
		   notifAlert(err._message,'{error}');
		   closeQrFrame();
		}else{
			QRScanner.hide(function(status){
				if(!status.showing){
					closeJendelaQr("hide");
					if(typeof newFunction !== 'undefined' && typeof newFunction == 'function'){
						eval(newFunction(text));
					}else if(typeof newFunction !== 'undefined' && typeof newFunction == 'string'){
						execFuncQRScanner(newFunction, window, text);
					}
				}
			});
		}
	}
	function openQrFrameContinue(){
		QRScanner.show(function(status){
			if(status.showing){
				if(document.getElementById('panelScanQrFrame')){
					openJendelaQr();
				}else{
					openQrFrame();
				}
				QRScanner.scan(scanedQr);
			}
		});
		
	}
	function readCode(err, status){
		if (err){
		   notifAlert(err._message,'{error}');
		}else{
			if(!status.authorized && status.canOpenSettings){
				if(confirm("Would you like to enable QR code scanning? You can allow camera access in your settings.")){
				  QRScanner.openSettings();
				}
			}else if (status.authorized){
				openQrFrameContinue();
			} else if(status.denied) {
				 notifAlert("Scan Qr ditolak",'{error}');
				 closeJendelaQr("hide");
			} else {
				console.log(status.status);
				closeJendelaQr("hide");
			}
		}
	}
}
function scanForBrowser(newFunction) {
	var txt;
	var scanQRValue = prompt("Please enter your text:", "");
	if (scanQRValue !== null) {
		txt = scanQRValue;
		if(typeof newFunction !== 'undefined' && typeof newFunction == 'function'){
			eval(newFunction(txt));
		}else if(typeof newFunction !== 'undefined' && typeof newFunction == 'string'){
			execFuncQRScanner(newFunction, window, text);
		}
	}
	
}
function scanQr(newFunction){
	if(!isCordova()){
		scanForBrowser(newFunction);
		return false;
	}
	QRScanner.prepare(readCode);
	function readCode(err, status){
		if (err){
		   notifAlert(err._message,'{error}');
		}else{
			if(!status.authorized && status.canOpenSettings){
				if(confirm("Would you like to enable QR code scanning? You can allow camera access in your settings.")){
				  QRScanner.openSettings();
				}
			}else if (status.authorized){
				openQrFrame();
				QRScanner.scan(function(err, text){
					if (err){
					   notifAlert(err._message,'{error}');
					}else{;
						closeJendelaQr();
						if(typeof newFunction !== 'undefined' && typeof newFunction == 'function'){
							eval(newFunction(text));
						}else if(typeof newFunction !== 'undefined' && typeof newFunction == 'string'){
							execFuncQRScanner(newFunction, window, text);
						}
					}
				});
				QRScanner.show();
				
			} else if(status.denied) {
				 notifAlert("Scan Qr ditolak",'{error}');
			} else {
				console.log(status.status);
			}
		}
	}
}

function testScaningQR(){
	frame_panel('panelScanQr','QR Reader');
	QRScanner.prepare(readCode);
	function readCode(err, status) {
		if (err) {
		   // here we can handle errors and clean up any loose ends.
		    alert(err);
		 }
		if (status.authorized) {
			// W00t, you have camera access and the scanner is initialized.
			// QRscanner.show() should feel very fast.
			
		} else if(status.denied) {
			 alert(status.denied);
			// The video preview will remain black, and scanning is disabled. We can
			// try to ask the user to change their mind, but we'll have to send them
			// to their device settings with `QRScanner.openSettings()`.
		} else {
			alert(status.status);
			// we didn't get permission, but we didn't get permanently denied. (On
			// Android, a denial isn't permanent unless the user checks the "Don't
			// ask again" box.) We can ask again at the next relevant opportunity.
		}
	}
}