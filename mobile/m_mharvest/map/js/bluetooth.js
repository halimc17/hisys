/*** 
//connect 
bluetoothSerial.connect(macAddress_or_uuid, connectSuccess, connectFailure);
//connectInsecure
bluetoothSerial.connectInsecure(macAddress, connectSuccess, connectFailure);

//disconnect
bluetoothSerial.disconnect([success], [failure]);
//write 
bluetoothSerial.write(data, success, failure);

// Status bluetooth
bluetoothSerial.isConnected(success, failure);

/*** Example 
// string
bluetoothSerial.write("hello, world", success, failure);

// array of int (or bytes)
bluetoothSerial.write([186, 220, 222], success, failure);

// Typed Array
var data = new Uint8Array(4);
data[0] = 0x41;
data[1] = 0x42;
data[2] = 0x43;
data[3] = 0x44;
bluetoothSerial.write(data, success, failure);

// Array Buffer
bluetoothSerial.write(data.buffer, success, failure);
// available
bluetoothSerial.available(success, failure);
// Example 
bluetoothSerial.available(function (numBytes) {
    console.log("There are " + numBytes + " available to read.");
}, failure);

// read
bluetoothSerial.read(success, failure);

// Example 
bluetoothSerial.read(function (data) {
    console.log(data);
}, failure);
// readUntil
bluetoothSerial.readUntil('\n', success, failure);
// List
bluetoothSerial.list(success, failure);
//result 
[{
    "class": 276,
    "id": "10:BF:48:CB:00:00",
    "address": "10:BF:48:CB:00:00",
    "name": "Nexus 7"
}, {
    "class": 7936,
    "id": "00:06:66:4D:00:00",
    "address": "00:06:66:4D:00:00",
    "name": "RN42"
}]
// Example 
bluetoothSerial.list(function(devices) {
    devices.forEach(function(device) {
        console.log(device.id);
    })
}, failure);
***/
/*
function connactingBluetooth(id){
	showProgress();
	bluetoothSerial.connect(id, connectSuccess, connectFailure);
	function connectSuccess(){
		if(document.getElementById(id)){
			btn_connection = document.getElementsByClassName('btn_connection');
			for(i=0; i<btn_connection.length; i++){
				btn_connection[i].style.background = false;
			}
			document.getElementById(id).innerHTML = "Connected";
			document.getElementById(id).style.background = "#225289";
		}
		sessionStorage.printer = id;
		hideProgress();
	}
	function connectFailure(){
		notifAlert("Gagal koneksi","{perhatian}");
		hideProgress();
	}
	BTPrinter.list(function(data){
		console.log(data); //list of printer in data array
		
	},function(err){
        console.log("Error");
        console.log(err);
    })
}*/

function checkconnectionPrinter(reconnect){
	//showProgress();
	/*
	if(document.getElementById(btnid)){
		var newACt = "frame_panel('listBluetooth','List Printer',this);";
		document.getElementById(btnid).setAttribute("oldFunction",document.getElementById(btnid).getAttribute("onclick"));
		document.getElementById(btnid).setAttribute("oldtext",document.getElementById(btnid).innerHTML);
		document.getElementById(btnid).setAttribute("newfunction","listBluetooth");
		document.getElementById(btnid).setAttribute("data-param",btnid);
		document.getElementById(btnid).setAttribute("onclick",newACt);
		document.getElementById(btnid).innerHTML = "Connect Printer Anda!";
	}
	*/
	//document.getElementById(btnid).innerHTML = sessionStorage.infoprinter;
	window.broadcaster.addEventListener( "DatecsPrinter.connectionStatus", function(e) {
		if (e.isConnected) {
			if(e.isConnected == "false"){
				hideProgress();
				notifAlert("Sambungkan koneksi Printer bluetooth dengan Gadget Anda!","Gagal","{ok}");
			}
		}
		/*
		if (!e.hasPaper) {
			notifAlert("Masukkan Kertas ke printer Anda!");
		}
		if (e.lowBattery) {
			notifAlert("Battery printer lemah, silahkan diisi daya!");
		}*/
		//document.getElementById(btnid).innerHTML = JSON.stringify(e);
	});
}
function printGlobalConnecting(newFunction,time){
	if(typeof time !== "undefined"){
		time = 5000;
	}
	waitingForprint('wait');
	if(typeof sessionStorage.printer != "undefined" && sessionStorage.printer != ""){
		var address = sessionStorage.printer;
	}else{
		var address = "";
	}
	if(address != ""){
		console.log("Print with Address in Session");
		window.DatecsPrinter.connect(
			address, 
		function(){
			setTimeout(
			function(){ 
				waitingForprint('done');
			}, time);
			executeFunctionByName(newFunction, window, '');
		},function(error){
			notifAlert("Error Connection : "+error.message);
			hideProgress();
		});
	}else{
		console.log("Print with Address in DB");
		strCreateTable=	'CREATE TABLE IF NOT EXISTS setup_printer(address TEXT,updateby TEXT)';
		db.transaction(function (tx) {
			tx.executeSql(strCreateTable, [], null, function(tx,error){errorHandler(tx,error);});
			strSelecty='SELECT address from setup_printer where upper(updateby) = "'+sessionStorage.username.toUpperCase()+'" limit 1';
			tx.executeSql(strSelecty, [], function(tx, rs){  
				console.log("Print Start");
				i = 0;
				if(rs.rows.length == 0){
					notifAlert("Sambungkan koneksi Printer bluetooth dengan Gadget Anda!","Gagal","{ok}");
					hideProgress();
				}else{
					address = rs.rows.item(i).address;
					window.DatecsPrinter.connect(
						address, 
					function(){
						setTimeout(
						function(){ 
							waitingForprint('done');
						}, time);
						executeFunctionByName(newFunction, window, '');
					},
					function(error){
						notifAlert("Error Connection  : "+error.message);
						hideProgress();
					});
				}
			}, function(tx,error){
				hideProgress();
			  errorHandler(tx,error);
			});
		},null,null);
	}
}
function printGlobal(newFunction,time){
	printGlobalConnecting(newFunction,time);
	/*window.DatecsPrinter.disconnect(function (success) {
		printGlobalConnecting(newFunction,time);
	}, function (error) {
		
	});*/
	
}
function connactingBluetooth(address,btnid){
	showProgress();
	window.DatecsPrinter.connect(
		address, 
	function(){
		hideProgress();
		if(document.getElementById(address)){
			btn_connection = document.getElementsByClassName('btn_connection');
			for(i=0; i<btn_connection.length; i++){
				if(btn_connection[i].getAttribute('id') != address){
					console.log(btn_connection[i].getAttribute('id'));
					btn_connection[i].style.background = null;
					btn_connection[i].innerHTML = "Connect";
					btn_connection[i].setAttribute("onclick",'connactingBluetooth(\''+btn_connection[i].getAttribute('id')+'\');');
				}
			}
			document.getElementById(address).innerHTML = "Connected";
			document.getElementById(address).style.background = "#225289";
			document.getElementById(address).setAttribute("onclick",'disconnectPrinter(\''+address+'\');');
		}
		if(typeof btnid !== "undefined"){
			/*
			if(document.getElementById(btnid)){
				document.getElementById(btnid).innerHTML = document.getElementById(btnid).getAttribute("oldtext");
				document.getElementById(btnid).setAttribute("onclick",document.getElementById(btnid).getAttribute("oldfunction")*/
			closePanel();
		}
		sessionStorage.printer = address;
		strCreateTable=	'CREATE TABLE IF NOT EXISTS setup_printer(address TEXT,updateby TEXT)';
		db.transaction(function (tx) {
			tx.executeSql(strCreateTable, [], null, function(tx,error){errorHandler(tx,error);});
			strSelecty='SELECT address from setup_printer where upper(updateby) = "'+sessionStorage.username.toUpperCase()+'" limit 1';
			tx.executeSql(strSelecty, [], function(tx, rs){  
				i = 0;
				if(rs.rows.length == 0){
					strInsert = " INSERT into setup_printer (address,updateby) values ('"+address+"','"+sessionStorage.username.toUpperCase()+"');";
					tx.executeSql(strInsert, [], null, function(tx,error){errorHandler(tx,error);});
				}else if(rs.rows.length > 0 && rs.rows.item(i).address != address){
					strInsert = " UPDATE setup_printer set address='"+address+"' where upper(updateby) = '"+sessionStorage.username.toUpperCase()+"';";
					tx.executeSql(strInsert, [], null, function(tx,error){errorHandler(tx,error);});
				}
			}, function(tx,error){
			  errorHandler(tx,error);
			});
		},null,null);
	},
	function(){
		hideProgress();
		notifAlert(JSON.stringify(error));
	});
	
}

function disconnectPrinter(btnid){
	window.DatecsPrinter.disconnect(function (success) {
		if(typeof btnid !== "undefined"){
			if(document.getElementById(btnid)){
				document.getElementById(btnid).style.background = null;
				document.getElementById(btnid).innerHTML = "Connect";
				document.getElementById(btnid).setAttribute("onclick",'connactingBluetooth(\''+btnid+'\');');
			}
		}
	}, function (error) {
		notifAlert(JSON.stringify(error));
	});
}
function listBluetooth(btnid){
	showProgress();
	/*
	BTPrinter.list(function(devices){
		html = JSON.stringify(devices);
		for(i=0; i<devices.length; i++){
			if(devices[i].type == "3"){
				html += '<div class="row col-12 p-t-10 p-l-0 p-r-0" style="border-top:1px solid #ccc;margin:auto;">';
				html += '<div class="col-12 m-b-20 m-t-20 m-r-0 m-l-0"><i class="fa fa-print m-l-10 m-r-10"></i><font>Name : '+devices[i].name+'</font></div>';
				html += '<button id="'+devices[i].address+'" onclick="connactingBluetooth(\''+devices[i].name+'\',\''+devices[i].address+'\');" class="btn_connection col-12 m-b-10 m-r-0" >'+devices[i].name+'</button>';
				html += "</div>";
			}
		}
		document.getElementById('body_listBluetooth').innerHTML = html;
		hideProgress();
	},function(err){
       html = JSON.stringify(err);
		document.getElementById('body_listBluetooth').innerHTML = html;
		hideProgress();
    });*/
	newParam ="";
	if(typeof btnid !== "undefined"){
		newParam = ",'"+btnid+"'";
	}
	
	try{	
		db.transaction(function (tx) {
			strCreateTable=	'CREATE TABLE IF NOT EXISTS setup_printer(address TEXT,updateby TEXT)';
			tx.executeSql(strCreateTable, [], null, function(tx,error){errorHandler(tx,error);});
			strSelecty='SELECT address from setup_printer where upper(updateby) = "'+sessionStorage.username.toUpperCase()+'" limit 1';
			tx.executeSql(strSelecty, [], function(tx, rs){  
				if(rs.rows.length > 0){
					sessionStorage.printer = rs.rows.item(0).address;
				}
				window.DatecsPrinter.listBluetoothDevices(function(devices){
					//window.DatecsPrinter.connect(devices[0].address, function() {printSomeTestText();},function() {alert(JSON.stringify(error));});
					console.log(devices);
					var onclick = "";
					var html = "";
					var jml = 0;
				
						for(i=0; i<devices.length; i++){
							style =  "";
							text =  "Connect";
							onclick = 'connactingBluetooth(\''+devices[i].address+'\''+newParam+');';
						
							if(sessionStorage.printer == devices[i].address){
								style = "style=\"background:#225289;\"";
								text =  "Connected";
								onclick = 'disconnectPrinter(\''+devices[i].address+'\');';
							}
							
							if(devices[i].type == "3"){
								//html += "<p><font color='red'>ID : "+devices[i].address+"</font></p>";
								//html += "<p><font>Class : "+devices[i].type+"</font></p>";
								//html += "<p><font>Address : "+devices[i].address+"</font></p>";
								html += '<div class="row col-12 p-t-10 p-l-0 p-r-0" style="border-top:1px solid #ccc;margin:auto;">';
								html += '<div class="col-12 m-b-20 m-t-20 m-r-0 m-l-0"><i class="fa fa-print m-l-10 m-r-10"></i><font>Name : '+devices[i].name+'</font></div>';
								html += '<button id="'+devices[i].address+'" onclick="'+onclick+'" class="btn_connection col-12 m-b-10 m-r-0" '+style+'>'+text+'</button>';
								html += "</div>";
							}else{
								html += '<div class="row col-12 p-t-10 p-l-0 p-r-0" style="border-top:1px solid #ccc;margin:auto;">';
								html += '<div class="col-12 m-b-20 m-t-20 m-r-0 m-l-0"><i class="fa fa-question-circle-o" aria-hidden="true"></i><font>Name : '+devices[i].name+'</font></div>';
								html += '<button id="'+devices[i].address+'" onclick="'+onclick+'" class="btn_connection col-12 m-b-10 m-r-0" '+style+'>'+text+'</button>';
								html += "</div>";
							}
							jml++;
						}
						if(jml == 0){
							html += "Bluetooth Printer tidak ditemukan.<br>";
							html += "<div id='listBluetooth_status'></div>";
						}
						document.getElementById('body_listBluetooth').innerHTML = html;
						//JSON.stringify(devices);
						},function (error) {
							  if(error.errorCode == '2'){
								notifAlert(error.message,"Connecting","{ok}",listBluetooth);
							  }else{
								notifAlert(JSON.stringify(error));
							  }
							  hideProgress();
						  }
						);
				
				hideProgress();
			}, function(tx,error){
			  errorHandler(tx,error);
			});
		},null,null);
	}catch(e){
		console.log(e);
	}
		  
		
		/*
			bluetoothSerial.list(function(devices) {
		var html = "";
		devices.forEach(function(device) {
			style =  "";
			text =  "Connect";
			if(sessionStorage.printer !== "" && sessionStorage.printer == device.id){
				style = "style=\"background:#225289;\"";
				text =  "Connected";
			}
			if(device.class == "1664"){
				//html += "<p><font color='red'>ID : "+device.id+"</font></p>";
				//html += "<p><font>Class : "+device.class+"</font></p>";
				//html += "<p><font>Address : "+device.address+"</font></p>";
				html += '<div class="row col-12 p-t-10 p-l-0 p-r-0" style="border-top:1px solid #ccc;margin:auto;">';
				html += '<div class="col-12 m-b-20 m-t-20 m-r-0 m-l-0"><i class="fa fa-print m-l-10 m-r-10"></i><font>Name : '+device.name+'</font></div>';
				html += '<button id="'+device.id+'" onclick="connactingBluetooth(\''+device.id+'\');" class="btn_connection col-12 m-b-10 m-r-0" '+style+'>'+text+'</button>';
				html += "</div>";
				console.log(device);
			}
		})
		document.getElementById('body_listBluetooth').innerHTML = html;
	}, failure);
	*/
}
function failure(e){
	console.log("Failure",e);
}

function openBluetoothSetting(){
	html = '<div class="row m-l-10 m-r-10">';
	html += '<div class="col-6 p-r-0 p-l-0"><div class="rounded-div-yellow m-t-20" onclick="frame_panel(\'listBluetooth\',\'List Printer\',this);" newfunction="listBluetooth" data-param=""></div></div>';
	html += '<div class="col-6 p-r-0 p-l-0"><div class="rounded-div-green m-t-20" onclick="testPrint();"><i class="fa fa-print"></i></div></div>';
	html += '</div>';
	
	frame_panel('openBluetoothSetting','Bluetooth Printer',html);
}
function getDataUriForPrint(url, callback) {
    var image = new Image();

    image.onload = function () {
        var canvas = document.createElement('canvas');
        canvas.width = this.naturalWidth; // or 'width' if you want a special/scaled size
        canvas.height = this.naturalHeight; // or 'height' if you want a special/scaled size
        canvas.getContext('2d').drawImage(this, 0, 0);
        // Get raw image data
        //callback(canvas.toDataURL('image/png').replace(/^data:image\/(png|jpg);base64,/, ''));

        // ... or get as Data URI
        callback(canvas.toDataURL('image/png'));
    };
    image.src = url;
}
// Usage
function createprint(){
	var oQRCode = new QRCode("qrcode", {
		text : "http://www.owl-plantation.com",
		width : 200,
		height : 200
	});
	
}
function printtest(){
	showProgress();
	var logo = document.getElementById('logo');
	src = resizeImg(logo,100,100);
	var image = new Image();
	  image.onload = function() {
		  var canvas = document.createElement('canvas');
		  canvas.height = image.height;
		  canvas.width = image.width;
		  var context = canvas.getContext('2d');
		  context.drawImage(image, 0, 0);
		  var imageData = canvas.toDataURL('image/jpeg').replace(/^data:image\/(png|jpg|jpeg);base64,/, ""); //remove mimetype
		  window.DatecsPrinter.printImage(
			  imageData, //base64
			  canvas.width, 
			  canvas.height, 
			  1, 
			  function() {
					printSomeTestText();
			  },
			  function(error) {
				  notifAlert(JSON.stringify(error));
			  }
		  )
	  };
	  image.src = src.toDataURL('image/jpeg');
}

function printSomeTestText(){
	window.DatecsPrinter.printText("{br}{center}{b}PT. Origin Wiracipta Lestari{/b}{br}{br}", 'ISO-8859-1', 
	function() {
		printMyImage();
	});
	/*
	window.broadcaster.addEventListener( "DatecsPrinter.connectionStatus", function(e) {
		if (e.isConnected) {
			
		}else{
			notifAlert("Printer Not Connected Yet!");
		}
	});*/
}

function printMyImage() {
	var qrcode = document.getElementById('qrcode').getElementsByTagName("img");
	image = qrcode[0].getAttribute("src");
	height = 200;
	width = 200;
	var imageData = image.replace(/^data:image\/(png|jpg|jpeg);base64,/, ""); //remove mimetype
	window.DatecsPrinter.printImage(
		imageData, //base64
		width, 
		height, 
		1, 
		function() {
			printSomeTestText_1();
		},
		function(error) {
			alert(JSON.stringify(error));
		}
	);
}
function printSomeTestText_1(){
	window.DatecsPrinter.printText("{br}{br}{center}www.owl-plantation.com{br}{br}{br}{br}{br}{br}{br}{br}", 'ISO-8859-1', 
    function() {
		hideProgress();
		notifAlert("{berhasil}");
	});
}
function testPrint(){
	getDataUriForPrint('img/minlogo.jpg', function(dataUri) {
		isi_test = "<br\><center><img id=\"logo\" src=\""+dataUri+"\"></center><br\>";
		isi_test += "<center id=\"companyname\">PT Origin Wiracipta Lestari</center><br\>";
		isi_test += "<center id=\"qrcode\"></center><br\><br\>";
		isi_test += "<center><button class=\"btn_connection col-12 m-b-10 m-r-0\" onclick=\"printtest();\" >Test Print</button></center>\n";
		frame_panel('images_photo','Bluetooth Printer',isi_test,'createprint');
	});
	
	/*
	bluetoothSerial.isConnected(success, failure);
	function failure(){
		notifAlert("Printer Not Connected","{perhatian}");
	}
	function success(){
		getDataUriForPrint('img/minlogo.png', function(dataUri) {
			cordova.plugins.zbtprinter.printImage(convertDataURIToBinary(dataUri), sessionStorage.printer,
				function(success) { 
					alert("Print ok"); 
				}, function(fail) { 
					alert(fail); 
				}
			);
		});
		
		function berhasil(){
			notifAlert("Success","{berhasil}");
		}
		function gagal(){
			notifAlert("Print failure","ERROR");
		}
	}*/
	
}
