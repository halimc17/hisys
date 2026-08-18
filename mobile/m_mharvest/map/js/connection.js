function checkConnection() {
    var networkState = navigator.connection.type;
    var states = {};
    states[Connection.UNKNOWN]  = '{koneksitidakdiketahui}';//koneksitidakdiketahui [LANG CODE]
    states[Connection.ETHERNET] = 'Ethernet connection';
    states[Connection.WIFI]     = 'WiFi connection';
    states[Connection.CELL_2G]  = 'Cell 2G connection';
    states[Connection.CELL_3G]  = 'Cell 3G connection';
    states[Connection.CELL_4G]  = 'Cell 4G connection';
    states[Connection.CELL]     = 'Cell generic connection';
    states[Connection.NONE]     = '{tidakadakoneksi}';//tidakadakoneksi [LANG CODE]
	notifAlert(states[networkState],"Connection type");
}
//checkConnection();
function onOnline(func) {
    // Handle the online event
    var networkState = navigator.connection.type;

    if (networkState !== Connection.NONE) {
		console.log("Device is Online.");
        if(typeof func === "function"){
			eval(func);
		}else{
			return true;
		}
    }else{
		//if (networkState == Connection.NONE){
			console.log("Device is Offline.");
			notifAlert("{tidakadakoneksi}","Connection");
		//}else if (networkState == Connection.UNKNOWN){
		//	console.log("Connection UNKNOWN.");
		//	notifAlert("{koneksitidakdiketahui}","Connection");
		//}
		return false;
	}
}
function onOnlineNoAlert(func) {
    // Handle the online event
    var networkState = navigator.connection.type;
    if (networkState !== Connection.NONE){
		console.log("Device is Online.");
        if(typeof func === "function"){
			eval(func);
		}
    }else{
		console.log("Device is Offline.");
	}
}
/*
document.addEventListener("offline", onOffline, false);
function onOffline() {
    // Handle the offline event
}*/
/*
document.addEventListener("online", onOnline, false);
function onOnline() {
    // Handle the online event
}
}*/