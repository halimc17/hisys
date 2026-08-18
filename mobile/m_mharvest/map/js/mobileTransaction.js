 /*
 syn gps
 */

/*SQLITE DOCUMENTATION================================================================

  1.  Function structure:
    a. Select (query):
        db.transaction(function (TX) {
           TX.executeSql("SELECT * from people where shirt='Green';", //String Query
                    [],                                               // array of values for the ? placeholders
                    dataHandler,                                      // Data handler callback
                    errorHandler                                      // error handle callback, see function updateLogininfor for detail
                    );
        },
        null1, //callback on errror with no throw parameter                                  
        null2  //callback on success with no throw parameter
        );

    b. special sign '?':
      var name = 'jdoe';
      var shirt = 'mauve';
      db.transaction(
          function (transaction) {
              transaction.executeSql("UPDATE people set shirt=? where name=?;",
                  [ shirt, name ]);             // array of values for the ? placeholders
          ,dataHandler,ErrorHandler}
          , errorHandler, successHandler
      );

======================================================================================*/

var errorMaster = [];
var reportSyncMaster = [];
var enableUploadGps= '';
if(typeof sessionStorage.ip=='undefined'){
	sessionStorage.ip="182.23.67.40/"; //first
}
if(typeof sessionStorage.server=='undefined'){
	sessionStorage.server="ksp"; //first
}
if(typeof sessionStorage.username=='undefined'){
	sessionStorage.username="tim.owl6"; //first
}
if(typeof sessionStorage.password=='undefined'){
	sessionStorage.password="Ksp2019"; //first
}
if(typeof sessionStorage.nama=='undefined'){
	sessionStorage.nama="Test User"; //first
}
if(typeof sessionStorage.karyawanid=='undefined'){
	sessionStorage.karyawanid="0000000001"; //first
}
if(typeof sessionStorage.jabatan=='undefined'){
	sessionStorage.jabatan="0"; //first
}
if(typeof sessionStorage.subbagian=='undefined'){
	sessionStorage.subbagian=""; //first
}
if(typeof sessionStorage.kebun=='undefined'){
	sessionStorage.kebun="KAGE"; //first
}
if(typeof sessionStorage.pt=='undefined'){
	sessionStorage.pt=""; //first
}
if(typeof sessionStorage.lang=='undefined'){
	sessionStorage.lang="ID"; //first
}

//GPS
if(typeof sessionStorage.latitude=='undefined'){
	sessionStorage.latitude=""; //first
}
// Current GPS
if(typeof sessionStorage.longitude=='undefined'){
	sessionStorage.longitude=""; //first
}

if(typeof sessionStorage.altitude =='undefined'){
	sessionStorage.altitude = ""; //first
}
if(typeof sessionStorage.accuracy =='undefined'){
	sessionStorage.accuracy = ""; //first
}
if(typeof sessionStorage.logged  =='undefined'){
	sessionStorage.logged = getTanggalx(); //first
}

// Last GPS
if(typeof sessionStorage.lastlatitude=='undefined'){
	sessionStorage.lastlatitude=""; //first
}

if(typeof sessionStorage.lastlongitude=='undefined'){
	sessionStorage.lastlongitude=""; //first
}

if(typeof sessionStorage.lastaltitude =='undefined'){
	sessionStorage.lastaltitude = ""; //first
}
if(typeof sessionStorage.lastaccuracy =='undefined'){
	sessionStorage.lastaccuracy = ""; //first
}

if(typeof sessionStorage.printer =='undefined'){
	sessionStorage.printer = ""; //first
}
if(typeof sessionStorage.imei =='undefined'){
	sessionStorage.imei = "12313"; //first
}//358280099956928
if(typeof sessionStorage.developer =='undefined'){
	sessionStorage.developer = ""; //first
}
if(typeof sessionStorage.version =='undefined'){
	sessionStorage.version = "1213"; //first
}
if(typeof sessionStorage.versionname =='undefined'){
	sessionStorage.versionname = "123123"; //first
}
if(typeof sessionStorage.lockaApp =='undefined'){
	sessionStorage.lockaApp = ""; //first
}
if(typeof sessionStorage.printQR =='undefined'){
	sessionStorage.printQR = "FALSE"; //first
}
if(typeof sessionStorage.buttonQR =='undefined'){
	sessionStorage.buttonQR = "FALSE"; //first
}
if(typeof sessionStorage.baseActivity =='undefined'){
	sessionStorage.baseActivity = "TPH"; //first
	var variableTagLokasiKerjaPanen = "tph"
}
if(typeof sessionStorage.enableUploadGps =='undefined'){
	sessionStorage.enableUploadGps = "0"; //first
}
if(typeof sessionStorage.LocalFileSystem =='undefined'){
	sessionStorage.LocalFileSystem = ""; //first
}
//public
function getConfigPath(ip){
	var config={
	  http:"http://",
	  path:"/owl/mobile"
	};
	var configDebug={
	  http:"http://",
	  path:"/mmldev/mobile"
	}
	var configDebugParnaraya={
	  http:"http://",
	  path:"/parnaraya/mobile"
	}
	
	if(ip == "182.23.67.40"){
		return configDebugParnaraya;
	}else{
		return config;
	}
}
var app = {
    initialize: function() {
        this.bindEvents();
    },
    bindEvents: function() {
        document.addEventListener('deviceready', this.onDeviceReady, false);
    },
    onDeviceReady: function() {

		if(sessionStorage.developer == "true"){
			writeConsole();
		}
		
		versionCode = AppVersion.build;
		versionName = AppVersion.version;
		sessionStorage.version = versionCode;
		sessionStorage.versionname = versionName;
		
		onOnlineNoAlert(openUpdater);
		window.broadcaster.addEventListener( "DatecsPrinter.connectionStatus", function(e) {
			if (e.isConnected) {
				if(e.isConnected == "false"){
					hideProgress();
					notifAlert("Silahkan hubungi Administrator untuk sambungkan printer bluetooth!","Gagal","{ok}");
				}
			}
		});
		if(sessionStorage.imei == ""){
			cordova.plugins.IMEI(function (err, imei) {
				sessionStorage.imei = imei;
			});
		}
		/*
		if(document.getElementById('language')){
			var langNode = document.getElementById('language');
			langNode.setAttribute('src',"lang/"+sessionStorage.lang+".js");
			scaningScriptJava('language');
		}
		window.history.back(history.length);
		sessionStorage.panel = "home";*/
    }
};
function writeConsole(){
	var old = console.log;
	if(document.getElementById('lihatlogdata')){
		var loggerHTML = document.getElementById('lihatlogdata');
		if(document.getElementById('lihatlog').style.display != "none"){
			console.log = function (message) {
				if (typeof message == 'object') {
					loggerHTML.innerHTML += (JSON && JSON.stringify ? JSON.stringify(message) : message) + '<br />';
				} else {
					loggerHTML.innerHTML += message + '<br />';
				}
				document.getElementById('lihatlog').scrollTop = document.getElementById('lihatlog').scrollHeight - document.getElementById('lihatlog').clientHeight;
			}
		}
	}
}
function stopWriteConsole(){
	if(document.getElementById('lihatlogdata')){
		var loggerHTML = document.getElementById('lihatlogdata');
		loggerHTML.innerHTML ="";
		console.log = function (message) {};
	}
}
//Set the two dates 

function checkTidakPernahSyncData(){
	console.log("Apps Lock : "+sessionStorage.lockaApp);
	db.transaction(function (tx) {
		tx.executeSql("SELECT sql FROM sqlite_master WHERE (tbl_name = 'kebun_panen' or tbl_name = 'kebun_spbht') AND type = 'table'", [], function(tx, rss){ 
			if(rss.rows.length == 2){
				
				var qry = 'Select tanggal from kebun_panen where synchronized = "" UNION Select tanggal from kebun_spbht where synchronized = "" order by tanggal ASC limit 1';
				tx.executeSql(qry, [], function(tx, rs){
					if(rs.rows.length>0){
						//showProgress();
						diff = 0;
						for(i=0; i<rs.rows.length; i++) {
							var y2k  = new Date(rs.rows.item(i).tanggal);
							var today= new Date();
							diff = parseInt(Date.dateDiff('w', y2k, today));
							diffDay = parseInt(Date.dateDiff('d', y2k, today));
						}
						if(diffDay > 7){
							hideProgress();
							sessionStorage.lockaApp = "LOCK";
							//notifAlert("Aplikasi ini sudah expired. "+diffDay+" Hari");
							setTimeout(function(){
								if(navigator.app.exitApp){
									navigator.app.exitApp();
								}
							},1000);
						}else{
							hideProgress();
							if(diffDay >= 2){
								notifAlert("Harap segera lakukan sinkonisasi data transaksi tgl: "+rs.rows.item(0).tanggal+" ");
							}
						}
					}else{
						sessionStorage.lockaApp = "";
					}
				}, function(tx,error){
					hideProgress();
					errorHandler(tx,error);
				});
			}
		});
	},null,null);
}
function checkAlterTableAfterUpdate(){
	/*
	console.log("check Alter Table ");
	var listTBL = [
	// tambahan untuk menuju version 1.0.8
		{table:"kebun_spbdt",colom:[{name:"nik",type:"TEXT"},{name:"nik1",type:"TEXT"},{name:"tglpanen",type:"TEXT"}]}
	];
	db.transaction(function (tx) {
		console.log("checking..");
		for(i=0; i<listTBL.length; i++){
			console.log(listTBL[i].table+" checking..");
			var namatable = listTBL[i].table;
			var colmnData  = listTBL[i].colom;
			tx.executeSql("SELECT sql FROM sqlite_master WHERE tbl_name = '"+listTBL[i].table+"' AND type = 'table'", [], function(tx, rs){ 
				if(rs.rows.length > 0){
					listColom = rs.rows.item(0).sql.replace(/^[^\(]+\(([^\)]+)\)/g, '$1').replace(/ [^,]+/g, '').split(',');
					var nameColumn = new Array();
					for(x=0; x<colmnData.length; x++){
						if(listColom.indexOf(colmnData[x].name) == -1){
							nameColumn.push(colmnData[x].name+" "+colmnData[x].type);
						};
					}
					if(nameColumn.length > 0){
						//clone
						qryClone = "CREATE TABLE IF NOT EXISTS "+namatable+"_temp ("+rs.rows.item(0).sql.replace(/^[^\(]+\(([^\)]+)\)/g, '$1')+")";
						tx.executeSql(qryClone, [],null, function(tx,error){errorHandler(tx,error);});
						
						//copydata
						tx.executeSql("INSERT INTO "+namatable+"_temp FROM (select * from "+namatable+"); ", [],null, function(tx,error){errorHandler(tx,error);});
						
						//Drop Table old
						tx.executeSql("DROP TABLE IF EXISTS "+namatable+";", [],null, function(tx,error){errorHandler(tx,error);});
						//Create Table Alter 
						//console.log(nameColumn);
						Tambahcolumn = nameColumn.join(",");
						qryClone = " CREATE TABLE IF NOT EXISTS "+namatable+" ("+rs.rows.item(0).sql.replace(/^[^\(]+\(([^\)]+)\)/g, '$1')+", "+Tambahcolumn+");";
						
						tx.executeSql(qryClone, [],null, function(tx,error){errorHandler(tx,error);});
						
						//copydata ke New Alter Table
						tx.executeSql("INSERT INTO "+namatable+" FROM (select * from "+namatable+"_temp); ", [],null, function(tx,error){errorHandler(tx,error);});
						
						//Drop Table old
						tx.executeSql("DROP TABLE IF EXISTS "+namatable+"_temp;", [],null, function(tx,error){errorHandler(tx,error);});
					}
				}
				
			});
		}
	});
	*/
	/*
	for(i=0; i<listTBL.length; i++){
		Check_tabel(listTBL[i].table,listTBL[i]);
	}*/
	
}
function alterTableExec(data){
	dataColmn = data.colmn;
	console.log(dataColmn);
	db.transaction(function (tx) {
		if(dataColmn.length > 0){
			for(ix=0; ix<dataColmn.length; ix++){
				qry = "ALTER TABLE "+data.table+" ADD COLUMN "+dataColmn[ix].name+" "+dataColmn[ix].type;
				console.log(qry);
				tx.executeSql(qry, [], function(tx, rs){
					console.log(rs.message);
					qObj.resolve({ error: true});
				}); 
			}
		}
	});
}
//checkAlterTableAfterUpdate();


function checkUpdateAplikasi(callback){
	if(sessionStorage.version == ""){
		versionCode = AppVersion.build;
		versionName = AppVersion.version;
		sessionStorage.version = versionCode;
		sessionStorage.versionname = versionName;
	}
	console.log(AppVersion);
	var result = '<div class="submenu innerTab">'; 
	result +='<div class="innerForm open-form">';
	result +='<label>Versi Aplikasi : '+sessionStorage.versionname+' </label>';
	result +='<label>Versi Number : '+sessionStorage.version+' </label>';
	result +='<button onclick="openUpdater(\'button\');" class="col-12 ">{cekupdateapp}</button>';
	result +='</div>';
	result +='</div>';
	frame_panel('checkupdateform','{cekupdateapp}',result);
}
function openUpdater(via){
	if(typeof waktuopenUpdater !== "undefined"){
		clearInterval(waktuopenUpdater);
	}
	param = "";
	if(sessionStorage.version == ""){
		versionCode = AppVersion.build;
		versionName = AppVersion.version;
		sessionStorage.version = versionCode;
		sessionStorage.versionname = versionName;
	}	//first
			
	if(sessionStorage.server != ""){
		param='';
		var tujuan=sessionStorage.server+'/checkversion.php?appid=owlmobile';
		if(typeof via !== "undefined" && via == "button"){
			post_response_text(tujuan+"&method=json", param, respon);
		}else{
			post_response_textGPS(tujuan+"&method=json", param, respon);
		}
		function respon() {
			if(typeof via !== "undefined" && via == "button"){
				hideProgress();
			}
			if (con.readyState == 4) {
				if (con.status == 200) {
					result = JSON.parse(con.responseText);
					console.log(result);
					var updateversionNumber = "";
					if(result){
						updateversionNumber = result.appversion;
						name = result.nameapp;
						url = result.urlapp;
					}
					if(updateversionNumber != ""){
						if(parseInt(updateversionNumber) > parseInt(versionCode)){
							paramXML = "&method=xml&server="+sessionStorage.ip;
							console.log(tujuan+paramXML);
							checkAppUpdate(tujuan+paramXML);
						}else{
							if(typeof via !== "undefined" && via == "button"){
								notifAlert("Versi Aplikasi Anda sudah yang terbaru.");
							}
						}
					}
				}
			}
		}
		/*
		var updateXml = sessionStorage.server+"/android/update/version.xml";
		fileXml = readXml(updateXml);
		update = fileXml.getElementsByTagName("update");
		var updateversionNumber = "";
		var updatename = "";
		var updateurl = "";
		
		if(update.length>0){
			updateversionNumber = update[0].getElementsByTagName("version")[0].childNodes[0].nodeValue;
			name = update[0].getElementsByTagName("name")[0].childNodes[0].nodeValue;
			url = update[0].getElementsByTagName("url")[0].childNodes[0].nodeValue;
		}
		if(updateversionNumber != ""){
			//alert(updateversionNumber+" - "+versionCode);
			if(parseInt(updateversionNumber) > parseInt(versionCode)){
				checkAppUpdate(updateXml);
			}
		}
		*/
	}
}
function checkAppUpdate(updateUrl){
	window.AppUpdate.checkAppUpdate(onSuccess, onFail, updateUrl);
	var me = this;
	function onFail(){
		notifAlert(arguments.msg,arguments.code);
	}
	function onSuccess(){
		db.transaction(function (tx) {
            tx.executeSql("UPDATE data_version SET version = '0'",[],null,function(tx,error){errorHandler(tx,error);});
		},null,null);
	}
}
app.initialize();


function viewUUIDNumber(){
	if(device.uuid){
		notifAlert(device.uuid,"Uniq App Number");
	}else{
		notifAlert("UUID Undefined");
	}
}

function viewImeiNumber(){
	cordova.plugins.IMEI(function (err, imei) {
		sessionStorage.imei = imei;
		n = imei.replace(/(\d{4})(\d{4})(\d{4})(\d{3})/, "$1 $2 $3 $4");
		notifAlert(n,"IMEI Number");
	});
}


//
/*** Language Setting ***/

/*** END ***/
function errorHandler(tx,error){
  switch(error.code){
    //case 5:
    //     notifAlert('Table not found. Mohon lakukan sinkronisasi/pembaruan master data'); 
    //    break;
    default:
  notifAlert(error.message,'{error}');    
  }  
  return false;
}

function openServer(e){
	var serverInput = document.getElementById('server');
	if(e.checked == true){
		serverInput.type = "text";
	}else{
		serverInput.type = "hidden";
	}
}
function resetApps(){
  if(confirm('{apakahyakin}?')){
    db.transaction(function (tx) {
            tx.executeSql("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name", [], function(tx, rs){  
             err=0;
              for(var i=0; i<rs.rows.length; i++) {
                    tablename=rs.rows.item(i).name;
                    if(tablename.substring(0,1)!='_'){
                      try{
                        tx.executeSql('DROP TABLE IF EXISTS '+tablename,[],null,function(tx,error){errorHandler(tx,error);});
						clearTime();
                      }catch(e){
                        err=1;
                      }
                    }
              };
				sessionStorage.username		="";
				sessionStorage.password		="";
				sessionStorage.karyawanid	="";
				sessionStorage.jabatan		="";
				sessionStorage.nama			="";
				sessionStorage.kebun		="";
				sessionStorage.pt			="";
				sessionStorage.subbagian	="";
				sessionStorage.lang			="ID";
				sessionStorage.logged 		="";
             if(err==0){
				notifAlert('{sukses}','{pesan}');
             }  
            }, function(tx,error){
              errorHandler(tx,error);
            });
      },null,null);    
  }
	//#
	//reCheck Data base
	closeAllPanel();
	menuside_start();
	Check_tabel('loginonfo');
	Check_tabel('menumobile');
}
function set_from_developer(){
	var htmlx = "";
	db.transaction(function (tx){
		tx.executeSql('SELECT * FROM setting_developer', [], function(tx, rs){  
				if(rs.rows.length > 0){
					var checked = "";
					for(var i=0; i<rs.rows.length;i++) {
						switch(rs.rows.item(i).code){
							case'6':// buka print barcode
								sessionStorage.printQR = rs.rows.item(i).checked;
							break;
							case'7':// buka print barcode
								sessionStorage.buttonQR = rs.rows.item(i).checked;
							break;
							case'8':// Base Activity Blok / TPH
								sessionStorage.baseActivity = rs.rows.item(i).checked;
								switch(sessionStorage.baseActivity){
									case'TPH':
										variableTagLokasiKerjaPanen = "tph";
									break;
									case'BLOCK':
										variableTagLokasiKerjaPanen = "blok";
									break;
								}
							break;
							case'9':
								// uniq add new Field
								sessionStorage.spbQT = rs.rows.item(i).checked;
							break;
							case'10':
								// uniq add new Field
								sessionStorage.spbFT = rs.rows.item(i).checked;
							break;
							case'11':
								sessionStorage.maxLengPrinting = rs.rows.item(i).checked;
							break;
						}
					}
				}
			});
	},null,null);
	
}
function refresh_identitas(){
	
	checkTidakPernahSyncData();
	checkAlterTableAfterUpdate();
	set_from_developer();
	server = '';
	username = '';
	password = '';
	console.log("Refresh Identitas");
	if(sessionStorage.server == '' || sessionStorage.username == '' || sessionStorage.password == '' || sessionStorage.nama == '' || sessionStorage.kebun == ''){
		db.transaction(function (tr){
			tr.executeSql('SELECT loginonfo.*,organisasi.induk FROM loginonfo LEFT JOIN organisasi ON loginonfo.lokasitugas = organisasi.kodeorganisasi LIMIT 1', [], function(tr, rsr){  
				if(rsr.rows.length > 0){
					svr = "";
					uname = "";
					passw = "";
					for(var x=0; x<rsr.rows.length; x++) {
						svr  		= rsr.rows.item(x).server;
						uname 		= rsr.rows.item(x).username;
						passw		= rsr.rows.item(x).password;
						karyawanid	= rsr.rows.item(x).karyawanid;
						jabatan		= rsr.rows.item(x).jabatan;
						nama		= rsr.rows.item(x).nama;
						jobloc		= rsr.rows.item(x).lokasitugas;
						induk		= rsr.rows.item(x).induk;
						subbagian	= rsr.rows.item(x).subbagian;
						lang		= rsr.rows.item(x).lang;
						loggeddate		= rsr.rows.item(x).loggeddate;
					}
					var configIP = getConfigPath(svr);
					sessionStorage.server = configIP.http+svr+configIP.path;
					sessionStorage.ip = svr;
					sessionStorage.username = uname;
					sessionStorage.password = passw;
					sessionStorage.karyawanid = karyawanid;
					sessionStorage.jabatan = jabatan;
					sessionStorage.nama = nama;
					sessionStorage.kebun = jobloc;
					sessionStorage.pt = induk;
					sessionStorage.subbagian = subbagian;
					sessionStorage.lang = lang;
					sessionStorage.logged = loggeddate;
					
					
					refreshLastGPS(sessionStorage.username);
					
					window.history.back(history.length);
					sessionStorage.panel = "home";
					if(document.getElementById('server')){
						document.getElementById('server').value=svr; 
						document.getElementById('username').value=uname; 
						document.getElementById('password').value=passw;		
					}
					
					if(svr == "" || nama == "" || passw == ""){
						nextStepLogin(1);
						if(document.getElementById('server')){
							document.getElementById('username').value 	= uname;
							document.getElementById('password').value 	= passw;
						}
					}else{
						//create_identity(sessionStorage.nama,sessionStorage.kebun);
						// jika login terakhir selisih login pertama dengan hari ini 90 hari
						var LastLogged  = new Date(sessionStorage.logged);
						var today= new Date();
						diffDay = parseInt(Date.dateDiff('d', LastLogged, today));
						if(diffDay > 90 ){
							logout();
						}else{
							tr.executeSql('CREATE TABLE IF NOT EXISTS data_version(version TEXT)',[],null,function(tr,error){errorHandler(tr,error);});;
							tr.executeSql('SELECT version FROM data_version LIMIT 1', [], function(tr, rsr){  
								version = "";
								if(rsr.rows.length >0){
									version = rsr.rows.item(0).version;
								}
								if(version == "" || version == "0"){
									//syn apabila version 0;
									synMasterData(sessionStorage.server,sessionStorage.username,sessionStorage.password);
								}
							});
							
						}
					}
					
				}else{
					//Jika data tidak sinkron dengan sessionStorage
					notifAlert("Silahkan, masukkan ulang Nama Pengguna dan Sandi!",'{perhatian}');
					sessionStorage.username		="";
					sessionStorage.password		="";
					sessionStorage.karyawanid	="";
					sessionStorage.jabatan		="";
					sessionStorage.nama			="";
					sessionStorage.kebun		="";
					sessionStorage.pt			="";
					sessionStorage.subbagian	="";
					sessionStorage.lang			="ID";
					sessionStorage.logged 		="";
					nextStepLogin(1);
					create_identity(sessionStorage.nama,sessionStorage.kebun);
				}
			});
		});
	}else{
		create_identity(sessionStorage.nama,sessionStorage.kebun);
		refreshLastGPS(sessionStorage.username);
	}
	if(typeof sessionStorage.version == "undefined" || sessionStorage.version == ""){
		versionCode = AppVersion.build;
		versionName = AppVersion.version;
		sessionStorage.version = versionCode;
		sessionStorage.versionname = versionName; //first
	}
	if(sessionStorage.server !== ""){
		//onOnlineNoAlert(openUpdater);
	}
	if(document.getElementById('language')){
		var langNode = document.getElementById('language');
		langNode.setAttribute('src',"lang/"+sessionStorage.lang+".json");
		scaningScriptJava(langNode);
		
	}
	if(sessionStorage.imei == ""){
		if(cordova.plugins.IMEI){
			cordova.plugins.IMEI(function (err, imei) {
				sessionStorage.imei = imei;
			});
		}else{
			sessionStorage.imei = "no-imei";
		}
	}
}
function openconsolebox(e){
	if(document.getElementById("lihatlog")){
		lihatlog = document.getElementById("lihatlog");
		if(e.checked == true){
			db.transaction(function (tx){
				tx.executeSql('UPDATE setting_developer SET checked = "1" where code = "1" ',[],null,function(tx,error){errorHandler(tx,error);});
			});
			lihatlog.style.display = "block";
			writeConsole();
		}else{
			db.transaction(function (tx){
				tx.executeSql('UPDATE setting_developer SET checked = "0" where code = "1" ',[],null,function(tx,error){errorHandler(tx,error);});
			});
			lihatlog.style.display = "none";
			stopWriteConsole();
		}
	}
}
function logout(){
	sessionStorage.username		="";
	sessionStorage.password		="";
	sessionStorage.karyawanid	="";
	sessionStorage.jabatan		="";
	sessionStorage.nama			="";
	sessionStorage.kebun		="";
	sessionStorage.pt			="";
	sessionStorage.subbagian	="";
	sessionStorage.lang			="ID";
	sessionStorage.logged 		="";
	db.transaction(function (tx){
		tx.executeSql('DELETE FROM loginonfo',[],null,function(tx,error){errorHandler(tx,error);});
	},null,null);
	refresh_identitas();
}

function updateLoginInfo(datax,server,ip,username,password,lang){
	
	var arrlist = new Array();
	try{
		arrlist = JSON.parse(datax);
		user = arrlist.user;	
		menu = arrlist.menu;//many
			if(menu.length > 0 ){
				db.transaction(function (tx) {
					console.log("update data",menu);
					tx.executeSql('DROP TABLE IF EXISTS loginonfo',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('CREATE TABLE IF NOT EXISTS loginonfo (server TEXT,username TEXT,password TEXT,karyawanid TEXT,jabatan TEXT,nama TEXT,lokasitugas TEXT,subbagian TEXT,lang TEXT,loggeddate TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('CREATE TABLE IF NOT EXISTS menumobile (id TEXT,type TEXT,caption TEXT,caption2 TEXT,caption3 TEXT,action TEXT,formjs TEXT,parent TEXT,urut TEXT,hide TEXT)',[],null,function(tx,error){errorHandler(tx,error);});	
					tx.executeSql('DELETE FROM menumobile',[],null,function(tx,error){errorHandler(tx,error);});
					for(i=0; i<menu.length; i++){
						tx.executeSql('INSERT INTO menumobile (id,type,caption,caption2,caption3,action,formjs,parent,urut,hide) VALUES("'+menu[i].id+'","'+menu[i].type+'","'+menu[i].caption+'","'+menu[i].caption2+'","'+menu[i].caption3+'","'+menu[i].action+'","'+menu[i].formjs+'","'+menu[i].parent+'","'+menu[i].urut+'","'+menu[i].hide+'")',[],null,function(tx,error){errorHandler(tx,error);});
					}
					tx.executeSql('INSERT INTO loginonfo (server,username,password,karyawanid,jabatan,nama,lokasitugas,subbagian,lang,loggeddate) VALUES("'+ip+'","'+username+'","'+password+'","'+user.karyawanid+'","'+user.kodejabatan+'","'+user.namakaryawan+'","'+user.lokasitugas+'","'+user.subbagian+'","'+lang+'","'+getTanggalx()+'")',[],function(tx,rs){
						var configIP = getConfigPath(ip);
						sessionStorage.server 		= configIP.http+ip+configIP.path;
						sessionStorage.ip 			= ip;
						sessionStorage.username 	= username.toUpperCase();
						sessionStorage.password 	= password;
						sessionStorage.karyawanid 	= user.karyawanid;
						sessionStorage.jabatan 	  	= user.kodejabatan;
						sessionStorage.nama 		= user.namakaryawan;
						sessionStorage.kebun 		= user.lokasitugas;
						sessionStorage.subbagian 	= user.subbagian;
						sessionStorage.pt 			= user.pt;
						sessionStorage.lang 		= lang;
						sessionStorage.logged 		= getTanggalx();
							
						create_identity(sessionStorage.nama,sessionStorage.kebun);
						setTimeout(function(){ synMasterData(server,username,password);}, 1000);
					},function(tx,error){errorHandler(tx,error);});		
				},null,closeTransitionpanelLogin);
			}else{
				notifAlert('{nothaveaccess}','{perhatian}');
			}
	
	}catch(e){
		console.log(datax);
	}
}
function closeTransitionpanelLogin(){
	closeTransition('panelLogin');
}


function changepasswordExec(){
	//stop Propaganda event
	ev = this.event;
	stopPropaganda(ev);
	
	function alphanumeric(inputtxt){
		var Number = /[0-9]/i;
		var letter = /[a-z]/i;
		if(inputtxt.match(letter) && inputtxt.match(Number)){
		   return true;
		}else{ 
		  
		   return false; 
		}
	}
	var currentpassword = getValue('currentpassword');
	var newpassword = getValue('newpassword');
	var retypepassword = getValue('retypepassword');
	var fromlogin = getValue('logged');

	if(alphanumeric(newpassword) === true){
		if(currentpassword == ""){
			notifAlert('{isisandilama}');
			document.getElementById('currentpassword').focus();
		}else if(newpassword == ""){
			notifAlert('{isisandibaru}');
			document.getElementById('newpassword').focus();
		}else if(newpassword.length < 8){
			notifAlert('{karaktersandi}');
			document.getElementById('newpassword').focus();
		}else if(retypepassword == ""){
			notifAlert('{retypesandi}');
			document.getElementById('retypepassword').focus();
		}else if(currentpassword !== sessionStorage.password){
			notifAlert('{falsesandilama}');
			document.getElementById('currentpassword').focus();
		}else if(newpassword == currentpassword){
			notifAlert('{lamabarusandi}');
			document.getElementById('newpassword').focus();
		}else if(newpassword !== retypepassword){
			notifAlert('{lamabarusandire}');
			document.getElementById('retypepassword').focus();
		}else{
			param='method=updatepasword&username='+sessionStorage.username+'&password='+sessionStorage.password+'&newpassword='+newpassword+'&uuid='+sessionStorage.imei; 
			tujuan=sessionStorage.server+'/owlMobile.php';
			post_response_text(tujuan, param, respon); 
		} 		 
	}else{
		notifAlert("{karaktersandi}"); 
		return false;
	}
	function respon() {
        if (con.readyState == 4) {
            hideProgress();
            if (con.status == 200) {
                if (!isSaveResponse(con.responseText)) {
                    notifAlert(con.responseText);
                } else {
					result = con.responseText;
					console.log(con.responseText);
					
					if(result == "true"){
						if(typeof fromlogin !== "undefined" && fromlogin == "login"){
							if(document.getElementById("panelLogin")){
								document.getElementById('changeform').remove();
								document.getElementById("loginform").style.display = "block";
								document.getElementById("password").value = "";
								document.getElementById("panelLoginjumbotron").innerHTML = "Login";
							}else{
								frame_panel('panelLogin','{login}');
							}
						}else{
							notifAlert("{success}",'{pesan}','{ok}');
							closePanel();
							sessionStorage.password  = newpassword;
						}
					}else{
						notifAlert("{gagalsandi}",'{error}','{ok}');
					}
                }
            } else {
                error_catch(con.status);
            }
        }
    } 
}
//#
function create_identity(nama,jobloc){
	//if(typeof document.getElementById('nama_user') !== 'undefined'){
	//	document.getElementById('nama_user').innerHTML = "<span><i class='icon-set menu-user'></i>  "+nama+"</span>";
	//}
	if(document.getElementById('lokasi_user')){
		document.getElementById('lokasi_user').innerHTML = "<span><i class='icon-set menu-jobloc'></i>  "+jobloc+"</span>";
	}
	if(document.getElementById('lblmenulogin')){
		document.getElementById('lblmenulogin').innerHTML = translateScript("{masuk}");
	}
	if(document.getElementById('lblmenuutama')){
	document.getElementById('lblmenuutama').innerHTML = translateScript("{menuutama}");
	}
	if(document.getElementById('lblmenuabout')){
	document.getElementById('lblmenuabout').innerHTML = "About";//translateScript("{about}");
	}
	if(window.location.href.search("map.html") != -1){
		if(typeof document.getElementById('menumodule') !== 'undefined'){
			
			divMenu = document.getElementById('menumodule');
			divMenu.innerHTML = "";
			var menu = ['Perusahaan','Kebun'];
			var value = [sessionStorage.pt,sessionStorage.kebun];
			for(i=0; i<menu.length; i++){
				li = document.createElement("li");
				li.setAttribute("class","list-menuside");
				li.innerHTML = "<div>"+menu[i]+"</div><div class='detail'>"+value[i]+"</div>";
				divMenu.appendChild(li);
			}
			
		}
	}
}
//#
function checkLoginonfo(table_name,RSrows){
	if(RSrows.length !== 0){
		refresh_identitas();
	}else{
		nextStepLogin(1);  
		set_from_developer();
	}
}
//#
function checkMenumobile(table_name,RSrows){
	if(RSrows.length !== 0){
		getUserMenu();
	}else{
		//notifAlert("{belumlogin}",'{pesan}');
	}
}
//#
function Check_tabel(tableName,alter){
	 db.transaction(function (tx) {
		tx.executeSql("SELECT name FROM sqlite_master WHERE type='table' and name='"+tableName+"' ORDER BY name", [], function(tx, rs){ 
			if(rs.rows.length > 0){
				err=0;
				if(typeof alter !== "undefined"){
					alterTableExec(alter);
				}else{
					switch (tableName){
						case 'loginonfo':
							checkLoginonfo(tableName,rs.rows);
						break;
						case 'menumobile':
							checkMenumobile(tableName,rs.rows);
						break;
					}
				}
			}else{
				switch (tableName){
					case 'loginonfo':
						checkLoginonfo(tableName,rs.rows);
					break;
					case 'menumobile':
						checkMenumobile(tableName,rs.rows);
					break;
				}
			}
		});
	});
}
//# Menu Detail dilakukan Create setelah klik menu utama Author : Atwal
function create_menudetail(myId,caption2){
		var both = document.getElementById('body_'+caption2);
		html = "<div class='fieldset' id='fieldsetmenu"+myId+"'>";
		html += "</div>";
		if(both){
			both.innerHTML = html;
		}
		db.transaction(function (tx){
			tx.executeSql('SELECT * FROM menumobile where parent="'+myId+'" order by urut asc', [], function(tx, rs){
				var subboth = document.getElementById('fieldsetmenu'+myId);
				var subhtml = "";
				if(rs.rows.length>0){
					for(var j=0; j<rs.rows.length; j++){
						myId2 = "";
						myId2parent = rs.rows.item(j).parent;
						myId2 = rs.rows.item(j).id;
						
						
						var legend = document.createElement("legend");
						txt = document.createTextNode(rs.rows.item(j).caption); 
						legend.appendChild(txt); 
						var subhtml = document.createElement("div");
						subhtml.setAttribute('id','submenu'+myId2);
						subhtml.setAttribute('class','submenu');
						subboth.appendChild(legend); 
						subboth.appendChild(subhtml); 
						create_submenu(myId2);
					}
				}
			}, function(tx,error){errorHandler(tx,error);});
		},null,null); 
		
}
function create_submenu(myId2){
	var submenu = document.getElementById('submenu'+myId2);
	db.transaction(function (tx){
		tx.executeSql('SELECT * FROM menumobile where parent="'+myId2+'" order by urut asc', [], function(tx, rs){
			if(rs.rows.length>0){
				var menuhtml = "";
				for(var k=0; k<rs.rows.length; k++){
					if(rs.rows.item(k).caption3!=="null"){
						funcSplit=(rs.rows.item(k).caption3).split(",");
						var newFunc = "";
						param = "";
						for(var l=0; l<funcSplit.length; l++){
							if(l==0){
								newFunc += funcSplit[l]+"";
							}
							if((funcSplit.length)>0 && l!==0 && l!==(funcSplit.length)){
								if(l==1){
									param += funcSplit[l];
								}else{
									param += ","+funcSplit[l];
								}
							}
							if(l==(funcSplit.length-1)){
								newFunc += "";
							}
						}
					}
					myId3 = rs.rows.item(k).parent;
					menuhtml += "<a onclick=\"frame_panel('"+rs.rows.item(k).caption2+"','"+rs.rows.item(k).caption+"',this);\" newfunction='"+newFunc+"' data-param='"+param+"' formjs='"+rs.rows.item(k).formjs+"' jsaction='"+rs.rows.item(k).action+"' ><span class='laporan'>"+rs.rows.item(k).caption+"</span></a>";
				}
				if(submenu){
					submenu.innerHTML = menuhtml;
				}
			}
		}, function(tx,error){errorHandler(tx,error);});
	},null,null); 
}
function getUserMenu(){
	var myId = "";
	var myId2 = "";
	
	
	var mainmenu = "";
	if(window.location.href.search("graphic.html") != -1){
		var typemenu = "mastergraphic";
		mainmenu += "<div style='padding:20px;'><div class='row'><div id='dashbord_home1' class='col-xs-12 m-b-20'></div>";
		mainmenu += "<div id='dashbord_home2' class='col-xs-12 m-b-20'></div></div>";
		mainmenu += "<div id='dashbord_home3' class='col-xs-12 m-b-20'></div></div>";
	}else{
		var typemenu = "master";
	}
	db.transaction(function (tx){
		tx.executeSql('SELECT * FROM menumobile where type = "'+typemenu+'" order by urut asc', [], function(tx, rs){
			//document.getElementById('detailmenu').innerHTML = "";
			num_mainmenu = 0;
			for(var i=0; i<rs.rows.length; i++){
				num_mainmenu +=1;
				myId = rs.rows.item(i).id;
				caption2 = rs.rows.item(i).caption2;
				mainmenu += "<a class='col-xs-4 col-sm-3 col-md-2 col-xl-1 home-menu no-padding hm_gray' onclick=\"frame_panel('"+rs.rows.item(i).caption2+"','"+rs.rows.item(i).caption+"',this);\" newfunction='create_menudetail' data-param='"+myId+","+caption2+"' jsaction='"+rs.rows.item(i).action+"' formjs='"+rs.rows.item(i).formjs+"' ><div class='icon_home img"+rs.rows.item(i).caption2+"'></div><span class=title_mainmenu>"+rs.rows.item(i).caption+"</span></a>";
			}
			
			//#
			mainmenu += "<a class='col-xs-4 col-sm-3 col-md-2 col-xl-1 home-menu no-padding hm_gray' onclick=\"frame_panel('panelsetup','{setup}');\"><div class='icon_home imgpanelsetup'></div><span class=title_mainmenu>Setup</span></a>";
			mainmenu += "<a class='col-xs-4 col-sm-3 col-md-2 col-xl-1 home-menu no-padding hm_gray' newfunction='writeVersion' data-param='' onclick=\"frame_panel('about','{about}',this);\"><div class='icon_home imgabout'></div><span class=title_mainmenu>About</span></a>";
			
			//mainmenu += "<a class='col-xs-4 col-sm-3 col-md-2 col-xl-1 home-menu no-padding hm_gray' newfunction='writeVersion' data-param='' onclick='openprofile();	' ><div class='icon_home imgprofile'></div><span class=title_mainmenu>"+translateScript("{profile}")+"</span></a>";
			
			if(sessionStorage.developer == "true"){
				mainmenu += "<a class='col-xs-4 col-sm-3 col-md-2 col-xl-1 home-menu no-padding hm_gray' onclick=\"frame_panel('paneldeveloper','Setup Developer',this);\" newfunction='listtabledatadeveloper' data-param=''><div class='icon_home_clean imgpaneldeveloper'></div><span class=title_mainmenu>Dev</span></a>";	
			}
			
			/*if(num_mainmenu%2==1){
			mainmenu += "<a class='col-xs-4 col-sm-3 col-md-2 col-xl-1 home-menu no-padding hm_gray'><div class='icon_home'></div><span class=title_mainmenu></span></a>";
			}*/
			document.getElementById('home').innerHTML = mainmenu;
			if(document.getElementById('dashbord_home3')){
				var dashbord_home1 = document.getElementById('dashbord_home1');
				var dashbord_home2 = document.getElementById('dashbord_home2');
				var dashbord_home3 = document.getElementById('dashbord_home3');
				canvas = document.createElement('canvas');
				canvas.id = "myChart";
				dashbord_home3.appendChild(canvas);
				data = {
					labels : ['2019', '2018', '2017', '2016', '2015', '2014'],
					data : [19, 10, 3, 7, 2, 3]
				};
				reCreateGraphic(canvas,'bar',data);
				
				canvas1 = document.createElement('canvas');
				canvas1.id = "myChart1";
				dashbord_home1.appendChild(canvas1);
				data2 = {
					labels : ['2019', '2018', '2017'],
					data : [19, 10, 3]
				};
				reCreateRadial(canvas1,'doughnut',data2);
				
				canvas2 = document.createElement('canvas');
				canvas2.id = "myChart2";
				dashbord_home2.appendChild(canvas2);
				data3 = {
					labels : ['2019', '2018', '2017'],
					data : [7, 2, 3]
				};
				reCreateRadial(canvas2,'pie',data3);
			}
			
			
			overflow_home();
		}, function(tx,error){errorHandler(tx,error);});
	},synGpsLocation,null); 
}
function reCreateGraphic(ele,tipe,data){
	if(typeof myChart !== 'undefined'){
		myChart.destroy();
	}
	var ctx = ele.getContext('2d');
	var myChart = new Chart(ctx, {
		type: tipe,
		data: {
			labels: data.labels,
			datasets: [{
				label: '# Report Tahunan',
				data: data.data,
				backgroundColor: [
					'rgba(255, 99, 132, 0.5)',
					'rgba(54, 162, 235, 0.5)',
					'rgba(255, 206, 86, 0.5)',
					'rgba(75, 192, 192, 0.5)',
					'rgba(153, 102, 255, 0.5)',
					'rgba(255, 159, 64, 0.5)'
				],
				borderColor: [
					'rgba(255, 99, 132, 1)',
					'rgba(54, 162, 235, 1)',
					'rgba(255, 206, 86, 1)',
					'rgba(75, 192, 192, 1)',
					'rgba(153, 102, 255, 1)',
					'rgba(255, 159, 64, 1)'
				],
				borderWidth: 1
			},{
			  label: 'Line Dataset',
			  data: data.data,
			  borderColor: ['rgba(0,0,0,0.8)'],
			  backgroundColor: ['rgba(255, 99, 132, 0)'],
			  // Changes this dataset to become a line
			  type: 'line',
			  borderWidth: 2
			}]
		}
	});
}
function reCreateRadial(ele,tipe,data){
	if(typeof myChart !== 'undefined'){
		myChart.destroy();
	}
	var ctx = ele.getContext('2d');
	var myChart = new Chart(ctx, {
		type: tipe,
		data: {
			labels: data.labels,
			datasets: [{
				label: '# Report Tahunan',
				data: data.data,
				backgroundColor: [
					'rgba(255, 99, 132, 0.5)',
					'rgba(54, 162, 235, 0.5)',
					'rgba(255, 206, 86, 0.5)',
					'rgba(75, 192, 192, 0.5)',
					'rgba(153, 102, 255, 0.5)',
					'rgba(255, 159, 64, 0.5)'
				],
				borderColor: [
					'rgba(255, 99, 132, 1)',
					'rgba(54, 162, 235, 1)',
					'rgba(255, 206, 86, 1)',
					'rgba(75, 192, 192, 1)',
					'rgba(153, 102, 255, 1)',
					'rgba(255, 159, 64, 1)'
				],
				borderWidth: 1
			}]
		}
	});
}
function openprofile(){
	strhtml = "<div class=fieldset><legend>{login}</legend>";
	strhtml += "<table>";
	strhtml += "<tr><td>{username} </td><td>:</td><td> "+sessionStorage.username+"</td></tr>";
	strhtml += "<tr><td>{namakaryawan} </td><td>:</td><td> "+sessionStorage.nama+"</td></tr>";
	strhtml += "<tr><td>{lokasi} </td><td>:</td><td> "+sessionStorage.subbagian+"</td></tr>";
	strhtml += "<tr><td>{kebun}/{pabrik} </td><td>:</td><td> "+sessionStorage.kebun+"</td></tr>";
	strhtml += "<tr><td>{bahasa} </td><td>:</td><td> "+sessionStorage.lang+"</td></tr>";
	strhtml += "<tr><td>{appver} </td><td>:</td><td> "+sessionStorage.versionname+"</td></tr>";
	strhtml += "<tr><td>IMEI</td><td>:</td><td> "+sessionStorage.imei+"</td></tr>";
	strhtml += "<tr><td>IP SERVER</td><td>:</td><td> "+sessionStorage.ip+"</td></tr>";
	strhtml += "</table>";
	strhtml += "</div>";
	strhtml += '<div class="submenu">';
	strhtml += '<a onclick="openFormForgotPassword();" newfunction="" data-param="" jsaction=""><span class="laporan"><font color="#fff300"><i class="fa fa-key"></i></font>   {gantipassword}</span></a>';
	//strhtml += '<a onclick="openFormForgotPassword();" newfunction="" data-param="" jsaction=""><span class="laporan">Forgot Password</span></a>';
	strhtml += '<a onclick="checkUpdateAplikasi();" newfunction="" data-param="" jsaction=""><span class="laporan"><font color="#fff300"><i class="fa fa-android"></i></font>   '+translateScript("{cekupdateapp}")+'</span></a>';
	//strhtml += '<a onclick="frame_panel(\'gpsCanvas\',\'Test Interval\',this);" newfunction="testInterval" data-param="" formjs="testInterval" jsaction=""><span class="laporan"><font color="#fff300"><i class="fa fa-map-pin" ></i></font> Test Interval</span></a>';
	strhtml += "</div>";
	frame_panel('profile','User Profile',strhtml);
}
function listtabledatadeveloper(){
	var htmlx = "";
	db.transaction(function (tx){
		tx.executeSql('SELECT * FROM setting_developer', [], function(tx, rs){  
				if(rs.rows.length > 0){
					var checked = "";
					for(var i=0; i<rs.rows.length;i++) {
						switch(rs.rows.item(i).code){
							default:
								htmlx += '<div class="col-xs-8">'+rs.rows.item(i).nama+'</div><div class="col-xs-4">';
								htmlx += rs.rows.item(i).checked;
							break;
							case'1':
								if(rs.rows.item(i).checked == "1"){
									checked = "checked";
								}
								if(typeof document.getElementById("lihatlog") != "undefined"){
									lihatlog = document.getElementById("lihatlog");
									lihatlog.style.display = "block";
									//writeConsole();	
								}
								htmlx += '<div class="col-xs-8">'+rs.rows.item(i).nama+'</div><div class="col-xs-4">';
								htmlx += '<input type="checkbox" value="'+rs.rows.item(i).code+'" onclick="openconsolebox(this);" '+checked+'>';
							break;
							case'5':
								if(rs.rows.item(i).checked == "VIEW"){
									htmlx += '<div class="col-xs-8">'+rs.rows.item(i).nama+'</div><div class="col-xs-4">';
									htmlx += '<button class="col-12 m-b-10 m-r-0" onclick="frame_panel(\'opentablesqlite\',\'Open Db\',this);" newfunction="opentablesqlite" data-param="" jsaction=""><span class="laporan">'+rs.rows.item(i).checked+'</span></button>';
								}
							break;
						}
						htmlx += '</div>';
					}
					if(document.getElementById("listtabledatadeveloper")){
						document.getElementById("listtabledatadeveloper").innerHTML = htmlx;		
					}
				}
			});
			
	},null,null);
	
}

function opentablesqlite(){
	var select_table = document.getElementById("select_table");
	var table_terlarang = ['LOGINONFO','SETTING_DEVELOPER','SETUP_PARAMETERAPPL','GPS_INTERVAL','GPS_LOCATION'];
	db.transaction(function (tx) {
		tx.executeSql("SELECT tbl_name FROM sqlite_master WHERE type = 'table'", [], function(tx, rs){ 
			if(rs.rows.length > 0){
				
				select = '<option value="RUNQUERY">Run Query</option>';
				for(var i=1; i<rs.rows.length; i++){
					if(table_terlarang.indexOf(rs.rows.item(i).tbl_name.toUpperCase()) == -1){
						select += '<option value="'+rs.rows.item(i).tbl_name+'">'+rs.rows.item(i).tbl_name+'</option>';
					}
				}
				select_table.innerHTML = select;
			}
		});
				
	});		
	
}
function runQueryExec(event){
	stopPropaganda(event);
	if(document.getElementById('runquerystr')){
		qwry = document.getElementById('runquerystr').value;
		document.getElementById('runquerystr_temp').value = qwry;
		//document.getElementById('viewtablesqlite_list').innerHTML = "";
		var multiQuery = qwry.split(";");
		var arrId = new Array();
		var arrQwry = new Array();
		if(multiQuery.length>0){
			for(iq = 0; iq<multiQuery.length;iq++){
				console.log(multiQuery[iq]);
				if(multiQuery[iq].trim() != ""){
					botID = 'viewtablesqlite_list_'+iq;
					arrId.push(botID);
					arrQwry.push(multiQuery[iq].trim());
				}
				//execIsitablesqlite(multiQuery[iq],1,'query','viewtablesqlite_list_'+iq);
			}
			createElement(arrQwry,arrId,'query');
		}
		closePanel();
	}
}
function loadlastQuery(){
	if(document.getElementById('runquerystr_temp')){
		qwry = document.getElementById('runquerystr_temp').value;
		if(document.getElementById('runquerystr')){
			document.getElementById('runquerystr').value = qwry;
		}
	}
}
function viewisitablesqlite(tbl_name){
	if(tbl_name == "RUNQUERY"){
		html  = '<div class="row m-r-10 m-l-10">';
		html += '<textarea id="runquerystr" class="col-12 m-b-10 m-t-10 m-r-0" style="padding:0px;height:300px;"></textarea>';
		html += '<button onclick="runQueryExec(event);" class="col-12 m-b-10 m-t-10 m-r-0">RUN</button>';
		html += '</div>';
		frame_panel(tbl_name,'Run Query',html,'loadlastQuery');
	}else{
		var arrId = new Array();
		var arrQwry = new Array();
		botID = 'viewtablesqlite_list_0';
		arrId.push(botID);
		arrQwry.push(tbl_name);
		createElement(arrQwry,arrId,'table');
	}
}
function createElement(arrQwry,arrId,type){
	if(arrId.length>0){
		viewtablesqlite_list = document.getElementById('viewtablesqlite_list');
		viewtablesqlite_list.innerHTML = "";
		viewtablesqlite_list.style.marginBottom = "50px";
		for(i = 0; i<arrId.length;i++){
			wrap = document.createElement("div");
			selector = document.createElement("div");
			selector.id = arrId[i]+"_selector";
			textarea = document.createElement("textarea");
			textarea.id = arrId[i]+"_qwry";
			textarea.style.display = "none";
			textarea.value = arrQwry[i];
			div = document.createElement("div");
			div.id = arrId[i];
			wrap.appendChild(textarea);
			wrap.appendChild(selector);
			wrap.appendChild(div);
			wrap.style.marginBottom = "20px";
			viewtablesqlite_list.appendChild(wrap);
			execIsitablesqlite(arrQwry[i],1,type,arrId[i]);
		}
	}
}
function execIsitablesqlite_ref(page,jenisSelect,bothid){
	if(document.getElementById(bothid+"_qwry")){
		tbl_name = document.getElementById(bothid+"_qwry").value;
		execIsitablesqlite(tbl_name,page,jenisSelect,bothid);
	}
}
function execIsitablesqlite(tbl_name,page,jenisSelect,bothid){
	if(document.getElementById('Refresh_opentablesqlite')){
		document.getElementById('Refresh_opentablesqlite').innerHTML = "";
	}
	var querySelect = "";
	if(typeof jenisSelect !== "undefined"){
		switch(jenisSelect){
			case'table':
				querySelect = "SELECT sql FROM sqlite_master WHERE tbl_name = '"+tbl_name+"' AND type = 'table'";
			break;
			case'query':
				querySelect = tbl_name;
			break;
		}
	}
	var arrExectName = ['INSERT','UPDATE','DELETE'];
	var exectName = querySelect.split(/\s+/g)[0];
	db.transaction(function (tx) {
		console.log("Query "+exectName);
		//console.log(arrExectName.indexOf(exectName.toUpperCase()));
		tx.executeSql(querySelect, [], function(tx, rs){
			if(arrExectName.indexOf(exectName.toUpperCase()) == -1){
				if(rs.rows.length > 0){
					var nameColumn = new Array();
					var extCol = new Array();
					var filter = false;
					if(jenisSelect == "query"){
						listColom = Object.keys(rs.rows.item(0)); 
						for(x=0; x<listColom.length; x++){
							nameColumn.push(listColom[x].toUpperCase());
							extCol.push("TXT");
						}
						var selectQuery = tbl_name;
					}else{
						var selectQuery = "SELECT * FROM "+tbl_name;
						listColom = rs.rows.item(0).sql.replace(/^[^\(]+\(([^\)]+)\)/g, '$1').split(',');
						for(x=0; x<listColom.length; x++){
							colobj = listColom[x].trim().split(/\s+/g);
							nameColumn.push(listColom[x].trim().replace(/ [^,]+/g, '').toUpperCase());
							ext = "";
							for(var ix=1; ix<colobj.length; ix++){
								ext += colobj[ix];
							}
							extCol.push(ext);
						}
					}
					//console.log(nameColumn);
					if(typeof page == "undefined"){
						page = 1;
					}
					limit = 40;
					tx.executeSql(selectQuery, [], function(tx, rs){ 
						var pagination = "";
						jmlpage = 1;
						if(parseInt(rs.rows.length) > 0){
							jml = parseInt(rs.rows.length);
							jmlpage = Math.ceil(jml/limit);//2
						};
						
						OFFSET = limit*(page-1);
						loadSqliteTable(selectQuery,limit,OFFSET,nameColumn,extCol,listColom,bothid,jenisSelect,tbl_name);
						selectPage = '<div class="col-6 p-r-0 p-l-0">';
						selectPage += '<a onclick="downloadPrintTable(\'excel\',\''+bothid+'.xls\',\'\',\''+bothid+'\');"><img src="images/excel.ico" style="width:50px;"></a>'; 
						selectPage += '</div>'; 
						if(jmlpage > 1){
							jenisselectcalll = "";
							if(typeof jenisSelect !== "undefined"){
								jenisselectcalll = ",'"+jenisSelect+"'";
							}
							selectPage += '<div class="col-6 p-r-0 p-l-0">';
							selectPage += '<select id="'+bothid+'halaman" onchange="execIsitablesqlite_ref(this.value'+jenisselectcalll+',\''+bothid+'\');">';
							for(i=1; i<=jmlpage; i++){
								if(page == i){
									selectPage += '<option value="'+i+'" selected>Page '+i+'</option>'; 
								}else{
									selectPage += '<option value="'+i+'">Page '+i+'</option>'; 
								}
							}
							selectPage += '</select>'; 
							selectPage += '</div>'; 
						}
						if(document.getElementById(bothid+'_selector')){
							document.getElementById(bothid+'_selector').setAttribute("onclick","");
							document.getElementById(bothid+'_selector').innerHTML = selectPage;
							document.getElementById(bothid+'_selector').style.width = "auto";
						}
					});
				}
			}else{
				console.log("Exec Name : Change Data!")
			}
		},dataHandler,errorHandler);
	}, errorHandler, successHandler);
}
function dataHandler(res){
	console.log("dataHandler:");
	console.log(res);
}
function errorHandler(res){
	console.log("errorHandler:");
	console.log(res);
}
function successHandler(res){
	console.log("successHandler:");
	console.log("Success");
}
function loadSqliteTable(selectQuery,limit,OFFSET,nameColumn,extCol,listColom,bothid,jenisSelect,tbl_name){
	showProgress();
	var pagination = "LIMIT "+limit+" OFFSET "+OFFSET+" ";
	db.transaction(function (tx) {
		tx.executeSql(selectQuery+" "+pagination+" ", [], function(tx, rs){ 
			var data=new Array();
			var act=new Array();
			var edit=new Array();
			if(rs.rows.length > 0){
				num = OFFSET;
				for(i=0; i<rs.rows.length; i++){
					data[num]=new Array();
					dataVal=new Array();
					where=new Array();
					extAll=new Array();
					for(x=0; x<listColom.length; x++){
						titik2 = "";
						content = "";
						datacontent = rs.rows.item(i)[listColom[x].trim().replace(/ [^,]+/g, '')];
						if(datacontent != "" && datacontent != null){
							if(datacontent.length > 10){
								titik2 = "...";
							}
							if(extCol[x].toUpperCase() == "BLOB" ){
								content = '<img onclick="viewImageSqlite(this)" src="'+datacontent+'" style="height:30px;" >'
							}else{
								if(datacontent.length > 10){
									content = datacontent.substr(0,10)+titik2;
								}else{
									content = datacontent;
								}
							}
						
							if(extCol[x].toUpperCase() != "BLOB" ){
								where.push(listColom[x].trim().replace(/ [^,]+/g, '')+"="+datacontent);
								//extAll.push(extCol[x].toUpperCase());
							}
						}
						if(extCol[x].toUpperCase() != "BLOB" ){
							dataVal.push(listColom[x].trim().replace(/ [^,]+/g, '')+"="+datacontent);
							extAll.push(extCol[x].toUpperCase());
						}
						
						data[num][x] = content;
					}
					paramVal 	= dataVal.join("*");
					paramWhere = where.join("*");
					paramExt = extAll.join("*");
					if(jenisSelect !== "query"){
						edit[num]	= [{'name':'edittablesqlite','param':[tbl_name,paramVal,paramWhere,paramExt]}];
						act[num]	= {'EDIT':edit[num]};
					}
					num++;
				}
				console.log(data);
				printTablemultiAct(nameColumn,data,bothid,'',act); 
			}
			hideProgress();
		});
	});
}
function viewImageSqlite(e){
	var html = '<div style="text-align: center;padding-top:20px;"><img src="'+e.src+'" style="max-width:100%;"></div>';
	frame_panel('panelSqliteViewFoto','View Foto',html,'');
}
function edittablesqlite(table,values,where,ext){
	isi = '<div class="submenu innerTab">'; 
	isi +='<div class="innerForm open-form">';
	isi += '<input id="tablenamesqlite" type="hidden" value="'+table+'">';
	isi += '<input id="valsqlite" type="hidden" value="'+values+'">';
	isi += '<input id="wheresqlite" type="hidden" value="'+where+'">';
		console.log(values);
	listvalues = values.split("*");
	listext = ext.split("*");
	console.log(values);
	for(var i=0; i<listvalues.length; i++){
		data = listvalues[i].split("=");
		id	=	data[0];
		val	=	data[1];
		if(listext[i] == "INTEGER" || listext[i] == "INT"){
			isi += '<label>'+id+'</label><input id="'+id+'" type="number" value="'+val+'">';
		}else{
			isi += '<label>'+id+'</label><input id="'+id+'" type="text" value="'+val+'">';
		}
	}
	isi +='<br>';
	isi +='<button onclick="simpan_valuetable();" class="col-12 ">SAVE</button>';
	isi +='<br>';
	isi +='</div></div>';
	frame_panel('edittable'+table,'Table '+table,isi,'loadtable');
}
function loadtable(){
}
function simpan_valuetable(){
	var tablenamesqlite = document.getElementById('tablenamesqlite').value;
	wheresqlite = document.getElementById('wheresqlite').value;
	valsqlite = document.getElementById('valsqlite').value;
	list = valsqlite.split("*");
	listWhere = wheresqlite.split("*");
	var sql = "UPDATE "+tablenamesqlite+" SET ";
	where = "";
	for(var i=0; i<listWhere.length; i++){
		dataW = listWhere[i].split("=");
		idW	=	dataW[0];
		valW	=	dataW[1];
		if(i==0){
			where += idW+"='"+valW+"' ";
		}else{
			where += "and "+idW+"='"+valW+"' ";
		}
	}
	for(var i=0; i<list.length; i++){
		data = list[i].split("=");
		id	=	data[0];
		val	=	data[1];
		ele =document.getElementById(id);
		if(ele.type == "number"){
			valueInput = parseInt(document.getElementById(id).value);
		}else{
			valueInput = document.getElementById(id).value;
		}
		if(i==0){
			sql += id+"='"+valueInput+"'";
			//where += id+"='"+val+"' ";
		}else{
			sql += ","+id+"='"+valueInput+"'";
			//where += "and "+id+"='"+val+"' ";
		}
	}
	sql += " Where "+where+"";
	db.transaction(function (tx) {
		tx.executeSql(sql, [], null, function(tx,error){errorHandler(tx,error);});
		if(document.getElementById("halaman")){
			hal = document.getElementById("halaman").value;
			viewisitablesqlite(tablenamesqlite,hal);
		}else{
			viewisitablesqlite(tablenamesqlite);
		}
		closePanel();
	});
}
function writeVersion(){
	if(document.getElementById('version')){
		if(sessionStorage.version == ""){
			versionCode = AppVersion.build;
			versionName = AppVersion.version;
			sessionStorage.version = versionCode;
			sessionStorage.versionname = versionName;
		}
		document.getElementById('version').innerHTML = sessionStorage.versionname;
	}
}
function forgotpassword(){
	title = "Forgot Password";
	message = "Do you want to request for the RESET PASSWORD to Admin";
	buttonLabels = ['{ok}','{batal}'];
	notifConfirm(message,title,buttonLabels,openFormForgotPassword);
}


function loadLogged(logged){
	if(document.getElementById("logged")){
		document.getElementById("logged").value = logged;
	}
}
function openFormForgotPassword(login){
	if(typeof login !== "undefined"){
		var login = "";
	}
	var divChange = document.createElement("div");
	divChange.id = "changeform";
	//'<span><i class="fa fa-eye" onclick="lookPassword(\'currentpassword\',this);" aria-hidden="true"></i></span>'+
	var html = '<br/><div class="fieldset" id="fieldsetmenu7">'+
			'{ubahsandiket}'+
			'<input type=\'hidden\' id=\'logged\' value="'+login+'">'+
			'</div>'+
			'{sandilama} : '+
			'<input type=\'password\' id=\'currentpassword\'>'+
			'<br/>'+
			'<div class="fieldset" id="fieldsetmenu7">'+
			'{sandibaru} :<input type=\'password\' id=\'newpassword\'>'+//<i class="fa fa-eye" aria-hidden="true"></i>
			'{confsandibaru} :<input type=\'password\' id=\'retypepassword\'>'+
			'</div>'+
			'<button class="col-12 m-t-20" onclick=\'changepasswordExec()\'>{simpan}</button>';
	if(document.getElementById("body_panelLogin")){
		divChange.innerHTML = translateScript(html);
		document.getElementById("loginform").style.display = "none";
		document.getElementById("body_panelLogin").appendChild(divChange);
		document.getElementById("panelLoginjumbotron").innerHTML = "Ubah Sandi" ;
	}else{
		frame_panel('changepassword','{gantipassword}',translateScript(html));
	}
}
function lookPassword(passId,e){
	eye = e.getElementsByClassName("fa");
	if(document.getElementById(passId)){
		pass = document.getElementById(passId);
		if(pass.type == "password"){
			pass.type = "text";
			if(eye.length > 0){
				eye[0].classList.add("fa-eye");//fa-eye-slash
				eye[0].classList.remove("fa-eye-slash");
			}
		}else{
			pass.type = "password";
			if(eye.length > 0){
				eye[0].classList.remove("fa-eye");//fa-eye-slash
				eye[0].classList.add("fa-eye-slash");
			}
		}
	}
}
function sendingForgotPassToAdmin(data){
	if(data === 1){
		param='method=sendforgotpassword&username='+sessionStorage.username+'&password='+sessionStorage.password+'&karyawanid='+sessionStorage.karyawanid+'&uuid='+sessionStorage.imei; 
		tujuan=sessionStorage.server+'/owlMobile.php';
		post_response_text(tujuan, param, respon); 
    }
    function respon() {
        if (con.readyState == 4) {
            hideProgress();
            if (con.status == 200) {
                if (!isSaveResponse(con.responseText)) {
                    notifAlert(con.responseText);
                } else {
					notifAlert(con.responseText,'{pesan}');
                }
            } else {
                error_catch(con.status);
            }
        }
    } 

}
//#
function menuside_start(){
	//Setup
	mainmenu = "<span class='col-xs-4 col-sm-3 col-md-2 col-xl-1 home-menu no-padding hm_gray' onclick=\"frame_panel('panelsetup','Setup');\"><div class='icon_home imgpanelsetup'></div><span class=title_mainmenu>Setup</span></span>";
	//About
	mainmenu += "<span class='col-xs-4 col-sm-3 col-md-2 col-xl-1 home-menu no-padding hm_gray' newfunction='writeVersion' data-param='' onclick=\"frame_panel('about','About',this);\"><div class='icon_home imgabout'></div><span class=title_mainmenu>About</span></span>";
	
	document.getElementById('home').innerHTML = mainmenu;
}
/*
function resultGPS(){
	
	lat		= document.getElementById('lat').value;
	lng 	= document.getElementById('lng').value;
	lat2 	= document.getElementById('lat2').value;
	lng2 	= document.getElementById('lng2').value;
	
	var Resmeasure = measure(lat,lng,lat2,lng2) + " Meter";
	document.getElementById('gpsresult').innerHTML = Resmeasure
}

	
function TestGPS(){
	function GPSBerhasil(position){
		var lat = position.coords.latitude;
		var lng = position.coords.longitude;
		var alt = position.coords.altitude;
		var acc = position.coords.accuracy;
		
		html = '<input id="lat" value="'+lat+'">';
		html += '<input id="lng" value="'+lng+'">';
		html += '<input id="lat2">';
		html += '<input id="lng2">';
		html += '<button onclick="resultGPS();" >test get meter</button>';
		html += '<div id="gpsresult" ></div>';
		
		document.getElementById('body_test').innerHTML = html;
	}
	navigator.geolocation.getCurrentPosition(GPSBerhasil, GPStdkBerhasil, GPSgeoOptions);
	
}*/
function getLoginInfo(){
    db.transaction(function (tx) 
        {  
            tx.executeSql('CREATE TABLE IF NOT EXISTS loginonfo (server TEXT, username TEXT,password TEXT,karyawanid TEXT,jabatan TEXT,nama TEXT,lokasitugas TEXT,subbagian TEXT,lang TEXT,loggeddate TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
        },null,null);
    db.transaction(function (tx) {
            tx.executeSql('SELECT * FROM loginonfo', [], function(tx, rs){  
            //notifAlert(rs.rows.length.toString());
              for(var i=0; i<rs.rows.length; i++) {
                    svr  =rs.rows.item(i).server;
                    uname=rs.rows.item(i).username;
                    passw=rs.rows.item(i).password;
                    karyawanid=rs.rows.item(i).karyawanid;
                    jabatan=rs.rows.item(i).jabatan;
                    nama=rs.rows.item(i).nama;
                    lokasitugas=rs.rows.item(i).lokasitugas;
                    subbagian=rs.rows.item(i).subbagian;
					if(document.getElementById('server')){
						document.getElementById('server').value=svr; 
					}
					if(document.getElementById('username')){
						document.getElementById('username').value=uname; 
					}
					if(document.getElementById('password')){
						document.getElementById('password').value=passw;
					}					
              };
             if(rs.rows.length>0){
                 //set login onfo object
                 lastChar=svr.substr(svr.length-1);
				 
                 if(lastChar=="/"){
					 var configIP = getConfigPath(svr.substr(0,svr.length-1));
                     sessionStorage.server=configIP.http+svr.substr(0,svr.length-1)+configIP.path;
                 }else{
					 var configIP = getConfigPath(svr);
                    sessionStorage.server=configIP.http+svr+configIP.path;
                 }
                 sessionStorage.username=uname;
                 sessionStorage.password=passw;  
                 sessionStorage.karyawanid=karyawanid;  
                 sessionStorage.jabatan=jabatan;  
                 sessionStorage.nama=nama;  
                 sessionStorage.kebun=lokasitugas;  
				 sessionStorage.subbagian = subbagian;
				 getUserMenu();
				 
              }else{
                notifAlert('Informasi Nama Pengguna dan Sandi tidak ditemukan.','{perhatian}');
              }
            }, function(tx,error){
              errorHandler(tx,error);
            });
      },null,null); 
	  synGpsLocation();
}

function verifyLoginInfo(){
	
	//stop Propaganda event
	ev = this.event;
	stopPropaganda(ev);
	
	ip			=trim(document.getElementById('server').value);
	username	=trim(document.getElementById('username').value);
	password	=trim(document.getElementById('password').value);
	bahasa		= document.getElementById('bahasa').value;
	var configIP = getConfigPath(ip);
	sessionStorage.username=username.toUpperCase();
	sessionStorage.password=password;  
	sessionStorage.server=configIP.http+ip+configIP.path;
	sessionStorage.ip=ip;
	sessionStorage.lang=bahasa;
	if(sessionStorage.server.length<21){
		notifAlert('Alamat IP Server belum benar','{error}');
	}else if(password=='' || username==''){
		notifAlert('{missmatch}');		
	}else if(password.length<6 || username.length<6){
		notifAlert('{missmatch}');
	}else{
		if(typeof sessionStorage.imei !== "undefined" && sessionStorage.imei !==""){
			param='method=getprofile2&username='+username+'&password='+password+'&server='+sessionStorage.server+'&uuid='+sessionStorage.imei+'&bahasa='+bahasa;
			tujuan=sessionStorage.server+'/owlMobile.php';
			post_response_text(tujuan, param, responLogin);
			console.log(tujuan+"?"+param);
		}else{
			if(typeof cordova.plugins.IMEI !== "undefined"){
				console.log("Check IMEI");
				cordova.plugins.IMEI(function (err, imei) {
					sessionStorage.imei = imei;
					param='method=getprofile2&username='+username+'&password='+password+'&server='+sessionStorage.server+'&uuid='+imei+'&bahasa='+bahasa;
					tujuan=sessionStorage.server+'/owlMobile.php';
					post_response_text(tujuan, param, responLogin);
					console.log(tujuan+"?"+param);
				});
			}else{
			
				param='method=getprofile2&username='+username+'&password='+password+'&server='+sessionStorage.server+'&uuid='+sessionStorage.imei+'&bahasa='+bahasa;
				tujuan=sessionStorage.server+'/owlMobile.php';
				console.log(tujuan+"?"+param);
				post_response_text(tujuan, param, responLogin);
			
			}
		}
      
    }
    function responLogin() {
        if (con.readyState == 4) {
            hideProgress();
			  console.log("Login Proccesing..");
            if (con.status == 200) {
                if (!isSaveResponse(con.responseText)) {
                    notifAlert(con.responseText);
                } else {
					var arrlislogged = new Array();
					try{
						arrlislogged = JSON.parse(con.responseText);
						user = arrlislogged.user;	
						if(user.logged == "0"){
							openFormForgotPassword("login");
						}else{
							updateLoginInfo(con.responseText,sessionStorage.server,sessionStorage.ip,sessionStorage.username,sessionStorage.password,sessionStorage.lang);
						}
					}catch(e){
						console.log(arrlislogged);
					}
					if(document.getElementById('language')){
						langNode = document.getElementById('language');
						langNode.setAttribute('src',"lang/"+sessionStorage.lang+".json");
						scaningScriptJava(langNode);
						
					}
					notifAlert("{success}",'{pesan}');
                }
            } else {
                error_catch(con.status);
            }
        }
    }     
}

function synMasterData(server,username,password){
//stop Propaganda event
ev = this.event;
stopPropaganda(ev);
var iserver		= sessionStorage.server;
var iIp			= sessionStorage.ip;
var iusername	= sessionStorage.username;
var ipassword	= sessionStorage.password;
 if(server){
	iserver = server;
 }
 if(username){
	iusername = username;
 }
 if(password){
	ipassword = password;
 }
 db.transaction(function (tx) {
	tx.executeSql('CREATE TABLE IF NOT EXISTS data_version(version TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
	tx.executeSql('SELECT * FROM data_version', [], function(tx, rs){  
		version ="0";
		if(rs.rows.length >0){
			version = rs.rows.item(0).version;
		}
		if(sessionStorage.version == ""){
			versionCode = AppVersion.build;
			versionName = AppVersion.version;
			sessionStorage.version = versionCode;
			sessionStorage.versionname = versionName;
		}
		param='method=synchronize&tipeData=master&username='+iusername+'&password='+ipassword+'&version='+version+'&appversion='+sessionStorage.version+'&appversionname='+sessionStorage.versionname+'&uuid='+sessionStorage.imei;
		post_response_text(iserver+'/owlMobile.php', param, respog);
		
	}, function(tx,error){
	  errorHandler(tx,error);
	});
},null,null); 
  
  function respog(){
      if(con.readyState==4){
        hideProgress();
        if (con.status == 200) {
            if (!isSaveResponse(con.responseText)) {
				console.log(con.responseText);
                notifAlert(con.responseText,'{error}');
            }else{
				try{
					arrResult=JSON.parse(con.responseText);
					
					if(arrResult.appversion == "changing"){
						if(arrResult.appversionstatusappupdate == "URGENT-DB" || arrResult.appversionstatusappupdate == "URGENT-APP"){
							notifAlert("Silahkan cek Update aplikasi, Versi terbaru "+arrResult.appversionname,"Done");
							checkUpdateAplikasi();
						}else{
							if(arrResult.appversionstatusappupdate == "MUST-UPDATE"){
								if(arrResult.dataversion == "same"){
									call = mustUpdateAplikasi;
								}else{
									call = mustUpdateAplikasiSyn;
								}
								buttonLabels = ['{ok}','{batal}'];
								notifConfirm("Apakah Anda ingin Update Aplikasi ?","UPDATE",buttonLabels,call);
							}else{
								if(arrResult.dataversion == "same"){
									notifAlert("Data master tidak ada perubahan. Silahkan Lanjutkan transaksi Anda","Done");
								}else{
									console.log('Syncronize Start..');
									simpanMaster_axec(arrResult.masterdata,0,'continues');
								}
							}
						}
					}else{
						if(arrResult.dataversion == "same" && arrResult.appversion == "same"){
							notifAlert("Data master tidak ada perubahan. Silahkan Lanjutkan transaksi Anda","Done");
						}else if(arrResult.appversion == "same" && arrResult.dataversion == "changing"){
							console.log('Syncronize Start..');
							simpanMaster_axec(arrResult.masterdata,0,'continues');
						}
					}
					
				}catch(e){
					console.log('Syncronize Error!');
				}
            }
        }
        else {
            error_catch(con.status);
        }
      } 
   }    
}

function mustUpdateAplikasiSyn(result){
	if(result){
		openUpdater('button');
	}else{
		simpanMaster_axec(arrResult.masterdata,0,'continues');
	}
}
function mustUpdateAplikasi(result){
	if(result){
		checkUpdateAplikasi();
	}
}
function simpanMaster_axec(data,num,lastdata){
	showProgress();
	function error_text(command,table,data){
		text = "ERROR ";
		text += "("+command+") ";
		text += ": "+table+" ";
		text += ", Data("+data+") ";
		result = text;
		return result;
	}
	function reportSync_text(table,jml,result){
		if(result == "Done"){
			text = table+" : "+result+" ("+jml+") \n";
		}else{
			text = "";
		}
		result = text;
		return result;
	}
	if(lastdata === "continues"){
		var _number = 0 + parseInt(num);
		switch (num){
			  case 0:
				var ds	= data.karyawan;
				_number++;
				 //1 datakaryawan
					console.log('datakaryawan');
					db.transaction(function (tx) 
					{
					tx.executeSql('DROP TABLE IF EXISTS datakaryawan',[],null,function(tx,error){errorHandler(tx,error);});  
					tx.executeSql('CREATE TABLE IF NOT EXISTS datakaryawan (karyawanid TEXT, nik TEXT, lokasitugas TEXT, subbagian TEXT,namakaryawan TEXT,namakaryawan2 TEXT,tipekaryawan INTEGER,namajabatan TEXT,kodejabatan TEXT,pemanen TEXT,perawatan TEXT,kemandoran TEXT)',[],null,function(tx,error){errorHandler(tx,error);}); 
					//tx.executeSql('DELETE FROM datakaryawan',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								
								tx.executeSql('INSERT INTO datakaryawan VALUES("'+ds[x].karyawanid+'", "'+ds[x].nik+'", "'+ds[x].lokasitugas+'","'+ ds[x].subbagian+'","'+ ds[x].namakaryawan+'","'+ ds[x].namakaryawan2+'","'+ds[x].tipekaryawan+'","'+ds[x].namajabatan+'","'+ds[x].kodejabatan+'","'+ds[x].pemanen+'","'+ds[x].perawatan+'","'+ds[x].kemandoran+'")',[],simpanMaster_axec(data,_number,lastdata),
									 function(err){
										//console.log(err);
										errorMaster.push(error_text('INSERT','datakaryawan',err));
									 }
								);
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{karyawan}');
							reportSyncMaster.push(reportSync_text('{karyawan}',ds.length,ress));
							//console.log(reportSyncMaster);
						}else{
							ress = "No Data";
						}
					}catch(e){
						ress = "No Data";
						errorMaster.push(error_text('INSERT','datakaryawan',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
				
			  break;
			  case 1:
				//gudang
				//console.log('gudang');
				var ds	= data.gudang;
				_number++;
				    //2 organisasi
					db.transaction(function (tx) 
					{ 
						tx.executeSql('DROP TABLE IF EXISTS organisasi',[],null,function(tx,error){errorHandler(tx,error);});       
						tx.executeSql('CREATE TABLE IF NOT EXISTS organisasi (kodeorganisasi TEXT, induk TEXT,namaorganisasi TEXT, tipeorganisasi TEXT, sertifikat TEXT, inisialisasiorganisasi TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
						//tx.executeSql('DELETE FROM organisasi',[],null,function(tx,error){errorHandler(tx,error);});
						lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								  tx.executeSql('INSERT INTO organisasi VALUES("'+ds[x].kodeorganisasi+'","'+ds[x].induk+'","'+ds[x].namaorganisasi+'","'+ds[x].tipe+'","'+ds[x].sertifikat+'","'+ds[x].inisialisasiorganisasi+'")',
								 [],simpanMaster_axec(data,_number,lastdata),
									 function(err){
										errorMaster.push(error_text('INSERT','organisasi',err));
									 }
								 );
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{organisasi}');
							// reportSyncMaster.push(reportSync_text('{organisasi}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','organisasi',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 2:
				//blok
				console.log('blok');
				var ds	= data.blok;
				var blokreal = [];
				_number++;
					db.transaction(function (tx) 
					{
					tx.executeSql('DROP TABLE IF EXISTS setup_blok',[],null,function(tx,error){errorHandler(tx,error);});       
					tx.executeSql('CREATE TABLE IF NOT EXISTS setup_blok (kodeblok TEXT, tahuntanam INTEGER, statusblok TEXT,luasareaproduktif REAL,kelaspohon TEXT,jumlahpokok REAL,topografi TEXT,kemandoran TEXT,latitude TEXT,longitude TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					//tx.executeSql('DELETE FROM setup_blok',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								if(ds[x].kodeorg){
									blokreal.push(ds[x].kodeorg);
									tx.executeSql('INSERT INTO setup_blok VALUES("'+ds[x].kodeorg+'",'+ds[x].tahuntanam+',"'+ds[x].statusblok+'",'+ds[x].luasareaproduktif+',"'+ds[x].kelaspohon+'",'+ds[x].jumlahpokok+',"'+ds[x].topografi+'","'+ds[x].kemandoran+'","'+ds[x].latitude+'","'+ds[x].longitude+'")',
									[],simpanMaster_axec(data,_number,lastdata),
										function(arr){
											errorMaster.push(error_text('INSERT','setup_blok',arr));
										}
									); 
								}else{
									simpanMaster_axec(data,_number,lastdata);
								}
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(blokreal.length > 0){
							ress = "Done";
							//mssg = translateScript('{setupblok}');
							reportSyncMaster.push(reportSync_text('Blok',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','setup_blok',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});  
			  break;
			  case 3:
				//barang
				console.log('barang');
				var ds	= data.barang;
				_number++;
				//4 master barang 
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS log_5masterbarang',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS log_5masterbarang (kodebarang TEXT, namabarang TEXT, satuan TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					//tx.executeSql('DELETE FROM log_5masterbarang',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								tx.executeSql('INSERT INTO log_5masterbarang VALUES("'+ds[x].kodebarang+'","'+ds[x].namabarang+'","'+ds[x].satuan+'")',
								[],simpanMaster_axec(data,_number,lastdata),
									function(err){
										errorMaster.push(error_text('INSERT','log_5masterbarang',err));
									}
								);
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{masterbarang}');
							reportSyncMaster.push(reportSync_text('{masterbarang}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','log_5masterbarang',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				}); 
			  break;
			  case 4:
				//kendaraan
				console.log('kendaraan');
				var ds	= data.kendaraan;
				_number++;
				    //5 VHC Master  
					db.transaction(function (tx) 
					{
					  tx.executeSql('DROP TABLE IF EXISTS vhc_5master',[],null,function(tx,error){errorHandler(tx,error);});      
					  tx.executeSql('CREATE TABLE IF NOT EXISTS vhc_5master (kodevhc TEXT, detailvhc TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					 // tx.executeSql('DELETE FROM vhc_5master',[],null,function(tx,error){errorHandler(tx,error);});
					  lastdata = "";
					try{
					  if(ds.length > 0){
						for(var x=0;x<ds.length;x++){
							if(x === ds.length-1){
								lastdata = "continues";
							}
						   tx.executeSql('INSERT INTO vhc_5master VALUES("'+ds[x].kodevhc+'","'+ds[x].detailvhc+'")',
						   [],simpanMaster_axec(data,_number,lastdata),
							   function(err){
									errorMaster.push(error_text('INSERT','vhc_5master',err));
							   }
						   );
						}
						  }else{
							simpanMaster_axec(data,_number,'continues');
						  }
						 if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{kendaraan}');
							reportSyncMaster.push(reportSync_text('{kendaraan}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','vhc_5master',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 5:
				//kegiatan
				console.log('kegiatan');
				var ds	= data.kegiatan;
				
				_number++;
				 //6 Master kegiatan 
					db.transaction(function (tx) 
					{
						tx.executeSql('DROP TABLE IF EXISTS setup_kegiatan',[],null,function(tx,error){errorHandler(tx,error);});      
						tx.executeSql('CREATE TABLE IF NOT EXISTS setup_kegiatan (kodekegiatan TEXT,namakegiatan TEXT,satuan TEXT,kelompok TEXT,noakun TEXT,premi TEXT,kodeklasifikasi TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
						//tx.executeSql('DELETE FROM setup_kegiatan',[],null,function(tx,error){errorHandler(tx,error);});
						lastdata = "";
						try{
							if(ds.length > 0){
								for(var x=0;x<ds.length;x++){
									if(x === ds.length-1){
										lastdata = "continues";
									}
									tx.executeSql('INSERT INTO setup_kegiatan VALUES("'+ds[x].kodekegiatan+'","'+ds[x].namakegiatan+'","'+ds[x].satuan+'","'+ds[x].kelompok+'","'+ds[x].noakun+'","'+ds[x].premi+'","'+ds[x].kodeklasifikasi+'")',[],simpanMaster_axec(data,_number,lastdata),
									function(err){
										errorMaster.push(error_text('INSERT','setup_kegiatan',err));
									});
								}
							}else{
								simpanMaster_axec(data,_number,'continues');
							}
							if(ds.length > 0){
								ress = "Done";
								//mssg = translateScript('{masterkegiatan}');
								reportSyncMaster.push(reportSync_text('{masterkegiatan}',ds.length,ress));
							}else{
								ress = "No Data";
							}
						}catch(e){
							//console.log(ds);
							ress = "No Data";
							errorMaster.push(error_text('INSERT','setup_kegiatan',ress));
							simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
						}
					});
			  break;
			  case 6:
				//customer
				console.log('customer');
				var ds	= data.customer;
				_number++;
				 db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS pmn_4customer',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS pmn_4customer (kodecustomer TEXT,namacustomer TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					//tx.executeSql('DELETE FROM pmn_4customer',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								tx.executeSql('INSERT INTO pmn_4customer VALUES("'+ds[x].kodecustomer+'","'+ds[x].namacustomer+'")',[],simpanMaster_axec(data,_number,lastdata),
									function(err){
										errorMaster.push(error_text('INSERT','pmn_4customer',err));
									}
								);
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{customer}');
							reportSyncMaster.push(reportSync_text('{customer}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','pmn_4customer',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 7:
				//basispanen
				console.log('basispanen');
				var ds	= data.basispanen;
				_number++;
				//8 Basis panen 
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS basis_panen',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS basis_panen (afdeling TEXT,jenispremi TEXT,kelaspohon TEXT,basis TEXT,premilebihbasis TEXT,premilibur TEXT,premiliburcapaibasis TEXT,topografi TEXT,premitopografi TEXT,premibrondolan TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					//tx.executeSql('DELETE FROM basis_panen',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								 tx.executeSql('INSERT INTO basis_panen VALUES("'+ds[x].afdeling+'","'+ds[x].jenispremi+'","'+ds[x].kelaspohon+'","'+ds[x].basis+'","'+ds[x].premilebihbasis+'","'+ds[x].premilibur+'","'+ds[x].premiliburcapaibasis+'","'+ds[x].topografi+'","'+ds[x].premitopografi+'","'+ds[x].premibrondolan+'")',[],simpanMaster_axec(data,_number,lastdata),
									 function(err){
										errorMaster.push(error_text('INSERT','basis_panen',err));
									 }
								 );
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{basispanen}');
							reportSyncMaster.push(reportSync_text('{basispanen}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','basis_panen',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				}); 
			  break;
			  case 8:
				//bjr
				console.log('bjr');
				var ds	= data.bjr;
				_number++;
				 //9 BJR 
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS kebun_bjr',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS kebun_bjr (kodeorg TEXT,kelaspohon TEXT,bjr TEXT,tahunproduksi TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					//tx.executeSql('DELETE FROM kebun_bjr',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								 tx.executeSql('INSERT INTO kebun_bjr VALUES("'+ds[x].kodeorg+'","'+ds[x].kelaspohon+'","'+ds[x].bjr+'","'+ds[x].tahunproduksi+'")',
								 [],simpanMaster_axec(data,_number,lastdata),
									 function(err){
										errorMaster.push(error_text('INSERT','kebun_bjr',err));
									 }
								 );
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{bjr}');
							reportSyncMaster.push(reportSync_text('{bjr}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','kebun_bjr',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				}); 
			  break;
			  case 9:
				//kodedendapanen
				console.log('kodedendapanen');
				var ds	= data.kodedendapanen;
				
				_number++;
				  //10 Kode Denda 
					db.transaction(function (tx) 
					{
						tx.executeSql('DROP TABLE IF EXISTS kebun_kodedenda',[],null,function(tx,error){errorHandler(tx,error);});      
						tx.executeSql('CREATE TABLE IF NOT EXISTS kebun_kodedenda (iddenda TEXT,kodedenda TEXT,deskripsi TEXT,satuan TEXT,lockjjg TEXT,nourut TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
						//tx.executeSql('DELETE FROM kebun_kodedenda',[],null,function(tx,error){errorHandler(tx,error);});
						lastdata = "";
						try{
							if(ds.length > 0){
								for(var x=0;x<ds.length;x++){
									if(x === ds.length-1){
										lastdata = "continues";
									}
									 tx.executeSql('INSERT INTO kebun_kodedenda VALUES("'+ds[x].iddenda+'","'+ds[x].kodedenda+'","'+ds[x].deskripsi+'","'+ds[x].satuan+'","'+ds[x].lockjjg+'","'+ds[x].nourut+'")',
									 [],simpanMaster_axec(data,_number,lastdata),
										 function(err){
											errorMaster.push(error_text('INSERT','kebun_kodedenda',err));
										 }
									 );
								}
							}else{
								simpanMaster_axec(data,_number,'continues');
							}
							if(ds.length > 0){
								ress = "Done";
								//mssg = translateScript('{kodedenda}');
								reportSyncMaster.push(reportSync_text('{kodedenda}',ds.length,ress));
							}else{
								ress = "No Data";
							}
						}catch(e){
							//console.log(ds);
							ress = "No Data";
							errorMaster.push(error_text('INSERT','kebun_kodedenda',ress));
							simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
						}
					});
			  break;
			  case 10:
				//dendapanen
				console.log('dendapanen');
				var ds	= data.dendapanen;
				_number++;
				//11 Denda panen
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS kebun_denda',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS kebun_denda (kodeorg TEXT,kodedenda TEXT,jenisdenda TEXT,denda TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					//tx.executeSql('DELETE FROM kebun_denda',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								 tx.executeSql('INSERT INTO kebun_denda VALUES("'+ds[x].kodeorg+'","'+ds[x].kodedenda+'","'+ds[x].jenisdenda+'","'+ds[x].denda+'")',
								 [],simpanMaster_axec(data,_number,lastdata),
									function(err){
										errorMaster.push(error_text('INSERT','kebun_denda',err));
									}
								);
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							reportSyncMaster.push(reportSync_text('{denda}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','kebun_denda',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 11:
				//kelaspohon
				console.log('kelaspohon');
				var ds	= data.kelaspohon;
				_number++;
				//12 Kelas Pohon
				
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS kebun_kelaspohon',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS kebun_kelaspohon (kelas TEXT,basisbulan TEXT,basishari TEXT,nama TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					//tx.executeSql('DELETE FROM kebun_kelaspohon',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								tx.executeSql('INSERT INTO kebun_kelaspohon VALUES("'+ds[x].kelas+'","'+ds[x].basisbulan+'","'+ds[x].basishari+'","'+ds[x].nama+'")',
								[],simpanMaster_axec(data,_number,lastdata),
								function(err){
									errorMaster.push(error_text('INSERT','kebun_kelaspohon',err));
								}
								);
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							reportSyncMaster.push(reportSync_text('{kelaspohon}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','kebun_kelaspohon',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 12:
				//gps
				console.log('gps');
				var ds	= data.gps;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS gps_interval',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS gps_interval (interval TEXT,enableupload TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					//tx.executeSql('DELETE FROM gps_interval',[],null,function(tx,error){errorHandler(tx,error);});
					try{					
						 if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								tx.executeSql('INSERT INTO gps_interval VALUES("'+ds[x].interval+'","'+ds[x].enableupload+'")',
								[],simpanMaster_axec(data,_number,lastdata),
								function(err){
									errorMaster.push(error_text('INSERT','gps_interval',err));
								}
								);
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//reportSyncMaster.push(reportSync_text('gpsi',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','gps_interval',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 13:
				//setupaproval
				console.log('setupaproval');
				var ds	= data.setupaproval;
				_number++;
				//14 aproval
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS setup_approval',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS setup_approval (kodeunit TEXT,kode_approval TEXT,level TEXT,applikasi TEXT,karyawanid TEXT,namakaryawan TEXT,nik TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					//tx.executeSql('DELETE FROM setup_approval',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								 tx.executeSql('INSERT INTO setup_approval VALUES("'+ds[x].kodeunit+'","'+ds[x].kode_approval+'","'+ds[x].level+'","'+ds[x].applikasi+'","'+ds[x].karyawanid+'","'+ds[x].namakaryawan+'","'+ds[x].nik+'")',[],simpanMaster_axec(data,_number,lastdata),
								 function(err){
									errorMaster.push(error_text('INSERT','setup_approval',err));
								 }
								 );
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							reportSyncMaster.push(reportSync_text('{setupapproval}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','setup_approval',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 14:
				//prht
				console.log('prht');
				var ds	= data.prht;
				_number++;
				//15 log_prapoht
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS log_prapoht',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS log_prapoht(nopp TEXT,'+
									'tanggal TEXT,'+
									'dibuat TEXT,'+
									'namadibuat TEXT,'+
									'close TEXT,'+
									'persetujuan1 TEXT,'+
									'persetujuan2 TEXT,'+
									'persetujuan3 TEXT,'+
									'persetujuan4 TEXT,'+
									'persetujuan5 TEXT,'+
										'namapersetujuan1 TEXT,'+
										'namapersetujuan2 TEXT,'+
										'namapersetujuan3 TEXT,'+
										'namapersetujuan4 TEXT,'+
										'namapersetujuan5 TEXT,'+
									'hasilpersetujuan1 TEXT,'+
									'hasilpersetujuan2 TEXT,'+
									'hasilpersetujuan3 TEXT,'+
									'hasilpersetujuan4 TEXT,'+
									'hasilpersetujuan5 TEXT,'+
										'komentar1 TEXT,'+
										'komentar2 TEXT,'+
										'komentar3 TEXT,'+
										'komentar4 TEXT,'+
										'komentar5 TEXT,'+
									'tglp1 TEXT,'+
									'tglp2 TEXT,'+
									'tglp3 TEXT,'+
									'tglp4 TEXT,'+
									'tglp5 TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM log_prapoht',[],null,function(tx,error){errorHandler(tx,error);});	
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								tx.executeSql('INSERT INTO log_prapoht VALUES("'+ds[x].nopp+'","'+ds[x].tanggal+'","'+ds[x].dibuat+'","'+ds[x].namadibuat+'","'+ds[x].close+'","'+ds[x].persetujuan1+'","'+ds[x].persetujuan2+'","'+ds[x].persetujuan3+'","'+ds[x].persetujuan4+'","'+ds[x].persetujuan5+'","'+ds[x].namapersetujuan1+'","'+ds[x].namapersetujuan2+'","'+ds[x].namapersetujuan3+'","'+ds[x].namapersetujuan4+'","'+ds[x].namapersetujuan5+'","'+ds[x].hasilpersetujuan1+'","'+ds[x].hasilpersetujuan2+'","'+ds[x].hasilpersetujuan3+'","'+ds[x].hasilpersetujuan4+'","'+ds[x].hasilpersetujuan5+'","'+ds[x].komentar1+'","'+ds[x].komentar2+'","'+ds[x].komentar3+'","'+ds[x].komentar4+'","'+ds[x].komentar5+'","'+ds[x].tglp1+'","'+ds[x].tglp2+'","'+ds[x].tglp3+'","'+ds[x].tglp4+'","'+ds[x].tglp5+'")',
								[],simpanMaster_axec(data,_number,lastdata),
								function(err){
									errorMaster.push(error_text('INSERT','prht',err));
								});
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{permintaanpembelian}');
							reportSyncMaster.push(reportSync_text('{permintaanpembelian}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','prht',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 15:
				//prdt
				console.log('prdt');
				var ds	= data.prdt;
				_number++;
				//16 log_prapodt
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS log_prapodt',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS log_prapodt (nopp TEXT,kodebarang TEXT,namabarang TEXT,jumlah TEXT,satuan TEXT,keterangan TEXT,status TEXT,alasanstatus TEXT,ditolakoleh TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM log_prapodt',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
						for(var x=0;x<ds.length;x++){
							if(x === ds.length-1){
								lastdata = "continues";
							}
							tx.executeSql('INSERT INTO log_prapodt VALUES("'+ds[x].nopp+'","'+ds[x].kodebarang+'","'+ds[x].namabarang+'","'+ds[x].jumlah+'","'+ds[x].satuanpp+'","'+ds[x].keterangan+'","'+ds[x].status+'","'+ds[x].alasanstatus+'","'+ds[x].ditolakoleh+'")',
							[],simpanMaster_axec(data,_number,lastdata),
							function(err){
								errorMaster.push(error_text('INSERT','log_prapodt',err));
							});
						}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{permintaanpembeliandetail}');
							reportSyncMaster.push(reportSync_text('{permintaanpembeliandetail}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','log_prapodt',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 16:
				//purchaser
				console.log('purchaser');
				var ds	= data.purchaser;
				_number++;
				//17 purch
				  db.transaction(function (tx) 
					{
						tx.executeSql('DROP TABLE IF EXISTS pur_karyawan',[],null,function(tx,error){errorHandler(tx,error);});      
						tx.executeSql('CREATE TABLE IF NOT EXISTS pur_karyawan (karyawanid TEXT,namakaryawan TEXT,bagian TEXT,nik TEXT,tipekaryawan TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM pur_karyawan',[],null,function(tx,error){errorHandler(tx,error);});
						lastdata = "";
						try{
							if(ds.length > 0){
								for(var x=0;x<ds.length;x++){
									if(x === ds.length-1){
										lastdata = "continues";
									}
								 tx.executeSql('INSERT INTO pur_karyawan VALUES("'+ds[x].karyawanid+'","'+ds[x].namakaryawan+'","'+ds[x].bagian+'","'+ds[x].nik+'","'+ds[x].tipekaryawan+'")',[],simpanMaster_axec(data,_number,lastdata),
									function(err){
										errorMaster.push(error_text('INSERT','pur_karyawan',err));
									}
								 );
								}
							}else{
								simpanMaster_axec(data,_number,'continues');
							}
							if(ds.length > 0){
								ress = "Done";
								//mssg = translateScript('{purchaser}');
								reportSyncMaster.push(reportSync_text('{purchaser}',ds.length,ress));
							}else{
								ress = "No Data";
							}
						}catch(e){
							//console.log(ds);
							ress = "No Data";
							errorMaster.push(error_text('INSERT','pur_karyawan',ress));
							simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
						}
					});
			  break;
			  case 17:
				//ijin
				console.log('ijin');
				var ds	= data.ijin;
				_number++;
				//sdm_ijin
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS sdm_ijin',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS sdm_ijin (karyawanid TEXT,namakaryawan TEXT,tanggal TEXT,keperluan TEXT,keterangan TEXT,persetujuan1 TEXT,namapersetujuan1 TEXT,stpersetujuan1 TEXT,komenst1 TEXT,waktupengajuan TEXT,jenisijin TEXT,hrd TEXT,namahrd TEXT,stpersetujuanhrd TEXT,periodecuti TEXT,darijam TEXT,sampaijam TEXT,jumlahhari TEXT,komenst2 TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM sdm_ijin',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
							tx.executeSql('INSERT INTO sdm_ijin VALUES("'+ds[x].karyawanid+'","'+ds[x].namakaryawan+'","'+ds[x].tanggal+'","'+ds[x].keperluan+'","'+ds[x].keterangan+'","'+ds[x].persetujuan1+'","'+ds[x].namapersetujuan1+'","'+ds[x].stpersetujuan1+'","'+ds[x].komenst1+'","'+ds[x].waktupengajuan+'","'+ds[x].jenisijin+'","'+ds[x].hrd+'","'+ds[x].namahrd+'","'+ds[x].stpersetujuanhrd+'","'+ds[x].periodecuti+'","'+ds[x].darijam+'","'+ds[x].sampaijam+'","'+ds[x].jumlahhari+'","'+ds[x].komenst2+'")',
							[],simpanMaster_axec(data,_number,lastdata),
							function(err){
								errorMaster.push(error_text('INSERT','sdm_ijin',err));
							}
							);
						}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{ijin}');
							reportSyncMaster.push(reportSync_text('{ijin}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','sdm_ijin',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 18:
				//pjdinas
				console.log('pjdinas');
				var ds	= data.pjdinas;
				_number++;
				//sdm_perdin
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS sdm_pjdinasht',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS sdm_pjdinasht(notransaksi TEXT,'+
									'karyawanid TEXT,'+
									'namakaryawan TEXT,'+
									'tanggalbuat TEXT,'+
									'tanggalperjalanan TEXT,'+
									'kodeorg TEXT,'+
									'tujuan1 TEXT,'+
									'tugas1 TEXT,'+
										'tujuan2 TEXT,'+
										'tugas2 TEXT,'+
										'tujuan3 TEXT,'+
										'tugas3 TEXT,'+
										'tujuanlain TEXT,'+
										'tugaslain TEXT,'+
									'pesawat TEXT,'+
									'darat TEXT,'+
									'laut TEXT,'+
									'mess TEXT,'+
									'hotel TEXT,'+
										'tanggalkembali TEXT,'+
										'hrd TEXT,'+
										'namahrd TEXT,'+
										'statushrd TEXT,'+
										'tanggalhrd TEXT,'+
										'uangmukatemp TEXT,'+
										'uangmuka TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM sdm_pjdinasht',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO sdm_pjdinasht VALUES("'+ds[x].notransaksi+'","'+ds[x].karyawanid+'","'+ds[x].namakaryawan+'","'+ds[x].tanggalbuat+'","'+ds[x].tanggalperjalanan+'","'+ds[x].kodeorg+'","'+ds[x].tujuan1+'","'+ds[x].tugas1+'","'+ds[x].tujuan2+'","'+ds[x].tugas2+'","'+ds[x].tujuan3+'","'+ds[x].tugas3+'","'+ds[x].tujuanlain+'","'+ds[x].tugaslain+'","'+ds[x].pesawat+'","'+ds[x].darat+'","'+laut+'","'+ds[x].mess+'","'+ds[x].hotel+'","'+ds[x].tanggalkembali+'","'+ds[x].hrd+'","'+ds[x].namahrd+'","'+ds[x].statushrd+'","'+ds[x].tanggalhrd+'","'+ds[x].uangmuka+'","'+ds[x].uang+'")',
						 [],simpanMaster_axec(data,_number,lastdata),
						 function(err){
							errorMaster.push(error_text('INSERT','sdm_pjdinasht',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{perjalanandinas}');
							reportSyncMaster.push(reportSync_text('{perjalanandinas}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','sdm_pjdinasht',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
					
				});
			  break;
			  case 19:
				//gudangtransaksi
				console.log('Gudang Transaksi');
				var ds	= data.gudangtransaksi;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS gudangtransaksi',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS gudangtransaksi(afdeling TEXT,'+
									'kodegudang TEXT,'+
									'status TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM gudangtransaksi',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO gudangtransaksi VALUES("'+ds[x].afdeling+'","'+ds[x].kodegudang+'","'+ds[x].status+'")',
						 [],simpanMaster_axec(data,_number,lastdata),//lanjut ke syncronize selanjutnya
						 function(err){
							errorMaster.push(error_text('INSERT','gudangtransaksi',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
						}
						if(ds.length > 0){
							ress = "Done";
							reportSyncMaster.push(reportSync_text('Gudang Transaksi',ds.length,ress));
							//mssg = translateScript();
							reportSyncMaster.push(reportSync_text('{gudang} {transaksi}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','gudangtransaksi',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			   case 20:
				//kegiatannorma
				console.log('kegiatannorma Transaksi');
				var ds	= data.kegiatannorma;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS setup_kegiatannorma',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS setup_kegiatannorma(kodekegiatan TEXT,'+
									'kelompok TEXT,'+
									'tipeanggaran TEXT,'+
									'kodebarang TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM setup_kegiatannorma',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
								for(var x=0;x<ds.length;x++){
									if(x === ds.length-1){
										lastdata = "continues";
									}
							 tx.executeSql('INSERT INTO setup_kegiatannorma VALUES("'+ds[x].kodekegiatan+'","'+ds[x].kelompok+'","'+ds[x].tipeanggaran+'","'+ds[x].kodebarang+'")',
							 [],simpanMaster_axec(data,_number,lastdata),//lanjut ke syncronize selanjutnya
							 function(err){
								errorMaster.push(error_text('INSERT','setup_kegiatannorma',err));
							 }
							 );
							}
						}else{
							simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{normaaktifitas}');
							reportSyncMaster.push(reportSync_text('{normaaktifitas}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','setup_kegiatannorma',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 21:
				//klasifikasi
				console.log('klasifikasi');
				var ds	= data.klasifikasi;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS klasifikasi',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS klasifikasi(kodeklasifikasi TEXT,'+
									'namaklasifikasi TEXT,'+
									'tipeklasifikasi TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM klasifikasi',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO klasifikasi VALUES("'+ds[x].kodeklasifikasi+'","'+ds[x].namaklasifikasi+'","'+ds[x].tipeklasifikasi+'")',
						 [],simpanMaster_axec(data,_number,lastdata),
						 function(err){
							errorMaster.push(error_text('INSERT','Classification',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							reportSyncMaster.push(reportSync_text('{klasifikasi}',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						ress = "No Data";
						//console.log(ds);
						errorMaster.push(error_text('INSERT','Classification',ress));
						simpanMaster_axec(data,_number,'continues');
					}
				});
			  break;
			  case 22:
				//userpin
				console.log('userpin');
				var ds	= data.userpin;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS userpin',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS userpin(karyawanid TEXT,'+
									'pin TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM userpin',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
							 tx.executeSql('INSERT INTO userpin VALUES("'+ds[x].karyawanid+'","'+ds[x].token+'")',
							 [],simpanMaster_axec(data,_number,lastdata),//Berhenti
							 function(err){
								errorMaster.push(error_text('INSERT','User Pin',err));
							 });
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							reportSyncMaster.push(reportSync_text('User Pin',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','User Pin',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  
			  case 23:
				//kemandoran
				console.log('Kemandoran');
				var ds	= data.kebun_5mandor;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS kemandoran',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS kemandoran(mandorid TEXT,'+
									'karyawanid TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM kemandoran',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
								tx.executeSql('INSERT INTO kemandoran VALUES("'+ds[x].mandorid+'","'+ds[x].karyawanid+'")',
								[],simpanMaster_axec(data,_number,lastdata),//Berhenti
								function(err){
									errorMaster.push(error_text('INSERT','Kemandoran',err));
								});
							}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							reportSyncMaster.push(reportSync_text('User Group',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Kemandoran',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  
			  case 24:
				//kemandoran blok
				console.log('Kemandoran Blok');
				var ds	= data.kebun_5mandor_blok;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS kemandoran_blok',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS kemandoran_blok(mandorid TEXT,'+
									'blok TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM kemandoran_blok',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO kemandoran_blok VALUES("'+ds[x].mandorid+'","'+ds[x].kodeorg+'")',
						 [],simpanMaster_axec(data,_number,lastdata),
						 function(err){
							errorMaster.push(error_text('INSERT','Kemandoran Blok',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							reportSyncMaster.push(reportSync_text('Block Group',ds.length,ress));
						}else{
							ress = "No Data";
					}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Kemandoran Blok',ress));
						simpanMaster_axec(data,_number,'continues');//lanjut ke syncronize selanjutnya
					}
				});
			  break;
			  case 25:
				//Kontrak kegiatan
				console.log('Kontrak kegiatan');
				var ds	= data.log_spk;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS log_spk',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS log_spk(kodeorg TEXT,'+
									'notransaksi TEXT,'+
									'supplierid TEXT,'+
									'namasupplier TEXT,'+
									'kodekegiatan TEXT,'+
									'divisi TEXT,'+
									'kodeblok TEXT,'+
									'satuan TEXT,'+
									'dari TEXT,'+
									'sampai TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM log_spk',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO log_spk VALUES("'+ds[x].kodeorg+'","'+ds[x].notransaksi+'","'+ds[x].supplierid+
																	'","'+ds[x].namasupplier+'","'+ds[x].kodekegiatan+'","'+ds[x].divisi+
																	'","'+ds[x].kodeblok+'","'+ds[x].satuan+'","'+ds[x].dari+'","'+ds[x].sampai+'")',
						 [],simpanMaster_axec(data,_number,lastdata),//continues
						 function(err){
						 errorMaster.push(error_text('INSERT','log_spk',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');//continues
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							reportSyncMaster.push(reportSync_text('Contract Work',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','log_spk',ress));
						simpanMaster_axec(data,_number,'continues');//Berhenti
					}
				});
			  break;
			   case 26:
				//Mutu Ancak
				console.log('Mutu Ancak');
				var ds	= data.setup_mutu;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS setup_mutu',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS setup_mutu(idjenis TEXT,kodemutu TEXT,'+
									'jenis TEXT,namamutu TEXT,satuan TEXT,satuan2 TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM setup_mutu',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO setup_mutu VALUES("'+ds[x].idjenis+'","'+ds[x].kodemutu+'","'+ds[x].jenis+'","'+ds[x].namamutu+'","'+ds[x].satuan+'","'+ds[x].satuan2+'")',
						 [],simpanMaster_axec(data,_number,lastdata),//continues
						 function(err){
						 errorMaster.push(error_text('INSERT','setup_mutu',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');//continues
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							reportSyncMaster.push(reportSync_text('Mutu Ancak',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Setup Mutu Ancak',ress));
						simpanMaster_axec(data,_number,'continues');//Berhenti
					}
				});
			  break;
			   case 27:
				//Setup Hama
				console.log('Kode Hama');
				var ds	= data.setup_hama;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS setup_hama',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS setup_hama(idhama TEXT,kodehama TEXT,'+
									'namahama TEXT,satuan TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM setup_hama',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO setup_hama VALUES("'+ds[x].idhama+'","'+ds[x].kodehama+'","'+ds[x].namahama+'","'+ds[x].satuan+'")',
						 [],simpanMaster_axec(data,_number,lastdata),//continues
						 function(err){
						 errorMaster.push(error_text('INSERT','Kode Hama',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');//continues
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							reportSyncMaster.push(reportSync_text('Kode Hama',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Setup Hama',ress));
						simpanMaster_axec(data,_number,'continues');//Berhenti
					}
				});
			  break;
			  case 28:
				//Setup TPH
				console.log('Kode TPH');
				var ds	= data.setup_tph;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS setup_tph',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS setup_tph(kode TEXT,'+
									'keterangan TEXT,'+
									'kodeorg TEXT,'+
									'latitude TEXT,'+
									'longitude TEXT,'+
									'luas TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM setup_tph',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO setup_tph VALUES("'+ds[x].kode+'","'+ds[x].keterangan+'","'+ds[x].kodeorg+'","'+ds[x].latitude+'","'+ds[x].longitude+'","'+ds[x].luas+'")',
						 [],simpanMaster_axec(data,_number,lastdata),//continues
						 function(err){
						 errorMaster.push(error_text('INSERT','Kode TPH',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');//continues
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							reportSyncMaster.push(reportSync_text('Kode TPH',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Kode TPH',ress));
						simpanMaster_axec(data,_number,'continues');//Berhenti
					}
				});
			  break;
			  case 29:
				//Transaksi Grading
				console.log('Setup Grading');
				var ds	= data.setup_grading;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS setup_grading',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS setup_grading('+
									'kodegrading TEXT,'+
									'namagrading TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM setup_grading',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO setup_grading VALUES("'+ds[x].kodegrading+'","'+ds[x].namagrading+'")',
						 [],simpanMaster_axec(data,_number,lastdata),
						 function(err){
							errorMaster.push(error_text('INSERT','Setup Grading',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							reportSyncMaster.push(reportSync_text('Setup Grading',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Setup Grading',ress));
						simpanMaster_axec(data,_number,'continues');
					}
				});
			  break;
			  case 30:
				//Data Version
				console.log('Data Version');
				var ds	= data.data_version;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS data_version',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS data_version('+
									'version TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					tx.executeSql('DELETE FROM data_version',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO data_version VALUES("'+ds[x].version+'")',
						 [],simpanMaster_axec(data,_number,lastdata),//Berhenti
						 function(err){
							errorMaster.push(error_text('INSERT','Data version',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');//Berhenti
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							// reportSyncMaster.push(reportSync_text('Data version',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Data version',ress));
						simpanMaster_axec(data,_number,'continues');//Berhenti
					}
				});
			  break;
			  case 31:
				console.log('Kode kemandoran');
				var ds	= data.kebun_5kemandoran;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS kebun_5kemandoran',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS kebun_5kemandoran ('+
									'kodemandor TEXT,namamandor TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO kebun_5kemandoran VALUES("'+ds[x].kodemandor+'","'+ds[x].namamandor+'")',
						 [],simpanMaster_axec(data,_number,lastdata),//Berhenti
						 function(err){
							errorMaster.push(error_text('INSERT','Kode kemandoran',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');//Berhenti
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							reportSyncMaster.push(reportSync_text('Kode kemandoran',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Kode kemandoran',ress));
						simpanMaster_axec(data,_number,'continues');//Berhenti
					}
				});
			  break;
			  
			  case 32:
				console.log('Setup Parameter Aplikasi');
				var ds	= data.setup_parameterappl;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS setup_parameterappl',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql('CREATE TABLE IF NOT EXISTS setup_parameterappl (kodeaplikasi TEXT, kodeparameter TEXT , kodeorg TEXT, keterangan TEXT, nilai TEXT);',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO setup_parameterappl VALUES("'+ds[x].kodeaplikasi+'","'+ds[x].kodeparameter+'","'+ds[x].kodeorg+'","'+ds[x].keterangan+'","'+ds[x].nilai+'")',
						 [],simpanMaster_axec(data,_number,lastdata),//Berhenti
						 function(err){
							errorMaster.push(error_text('INSERT','Setup Parameter Aplikasi',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');//Berhenti
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							// reportSyncMaster.push(reportSync_text('Setup Parameter Aplikasi',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Setup Parameter Aplikasi',ress));
						simpanMaster_axec(data,_number,'continues');//Berhenti
					}
				});
			  break;
			  
			  case 33:
				console.log('Setup pemanen baru');
				var ds	= data.datakaryawan_baru;
				_number++;
				db.transaction(function (tx) 
				{
					strCreate9=	'CREATE TABLE IF NOT EXISTS setup_pemanen_baru(karyawanid TEXT,nik TEXT,'+ 
									'lokasitugas TEXT,subbagian TEXT,namakaryawan TEXT,namakaryawan2 TEXT,status INT)';
					tx.executeSql('DROP TABLE IF EXISTS setup_pemanen_baru',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql(strCreate9,[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO setup_pemanen_baru VALUES("'+ds[x].karyawanid+'","'+ds[x].no_identitas+'","'+ds[x].unicode+'","'+ds[x].unicode+ds[x].div_code+'","'+ds[x].nama+'","'+ds[x].no_pemanen+'","0")',
						 [],simpanMaster_axec(data,_number,lastdata),//Berhenti
						 function(err){
							errorMaster.push(error_text('INSERT','Setup Pemanen Baru',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');//Berhenti
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							// reportSyncMaster.push(reportSync_text('Setup Parameter Aplikasi',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Setup Pemanen Baru',ress));
						simpanMaster_axec(data,_number,'continues');//Berhenti
					}
				});
			  break; 
			  
			  case 34:
				console.log('Last No SPB');
				var ds	= data.data_lastnospb;
				_number++;
				db.transaction(function (tx) 
				{
					strCreate=	'CREATE TABLE IF NOT EXISTS data_lastnospb(lastnospb TEXT,updateby TEXT)';
					tx.executeSql('DROP TABLE IF EXISTS data_lastnospb',[],null,function(tx,error){errorHandler(tx,error);});      
					tx.executeSql(strCreate,[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO data_lastnospb VALUES("'+ds[x].lastnospb+'","'+sessionStorage.username.toUpperCase()+'")',
						 [],simpanMaster_axec(data,_number,lastdata),//Berhenti
						 function(err){
							errorMaster.push(error_text('INSERT','Last No SPB',err));
						 }
						 );
						}
						}else{
							simpanMaster_axec(data,_number,'continues');//Berhenti
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							// reportSyncMaster.push(reportSync_text('Setup Parameter Aplikasi',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						errorMaster.push(error_text('INSERT','Last No SPB',ress));
						simpanMaster_axec(data,_number,'continues');//Berhenti
					}
				});
			  break;
			  
			  case 35:
				console.log('Data Setting developer');
				var ds	= data.setting_developer;
				_number++;
				db.transaction(function (tx) 
				{
					tx.executeSql('DROP TABLE IF EXISTS setting_developer',[],null,function(tx,error){errorHandler(tx,error);}); 
					tx.executeSql('CREATE TABLE IF NOT EXISTS setting_developer ( code TEXT, nama TEXT ,checked TEXT, updateby TEXT);',[],null,function(tx,error){errorHandler(tx,error);});
					lastdata = "";
					try{
						if(ds.length > 0){
							for(var x=0;x<ds.length;x++){
								if(x === ds.length-1){
									lastdata = "continues";
								}
						 tx.executeSql('INSERT INTO setting_developer VALUES ("'+ds[x].id+'","'+ds[x].name+'","'+ds[x].value+'","'+ds[x].updateby.toUpperCase()+'");',
						 [],sync_done(lastdata),//Berhenti
						 function(err){
							//errorMaster.push(error_text('INSERT','Data version',err));
						 }
						 );
						}
						}else{
							sync_done('continues');//Berhenti
						}
						if(ds.length > 0){
							ress = "Done";
							//mssg = translateScript('{klasifikasi}');
							//reportSyncMaster.push(reportSync_text('Data version',ds.length,ress));
						}else{
							ress = "No Data";
						}
					}catch(e){
						//console.log(ds);
						ress = "No Data";
						//errorMaster.push(error_text('INSERT','Data version',ress));
						sync_done('continues');//Berhenti
					}
				});
			  break;
		}
	}
	if(typeof sessionStorage.server == 'undefined' || sessionStorage.server == ""){ 
		refresh_identitas();
	}
	
}
function sync_done(lastdata){
	if(lastdata === "continues"){
		hideProgress();
		text_report = "\n";
		for(i=0; i<reportSyncMaster.length; i++){
			text_report += reportSyncMaster[i];
		}
		notifAlert(text_report,'Sync Complete!');
		errorMaster = [];
		reportSyncMaster = [];
		console.log('Syncronize Done');
		getUserMenu();
		set_from_developer();
	}
}
function searchingOnTable(locate,e){
	showProgress();
	locateTable =  document.getElementById(locate);
	table = locateTable.getElementsByTagName("table");
	
	var filter, tr, td, i, txtValue;
	filter = e.value.toUpperCase();
	for (x=0; x<table.length; x++){
		 tbody = table[x].getElementsByTagName("tbody");
		 tr = tbody[0].getElementsByTagName("tr");
		for (i = 0; i < tr.length; i++) {
			ada = false;
			td = tr[i].getElementsByTagName("td");	
			for (ii = 0; ii < td.length; ii++) {
				txtValue = td[ii].textContent || td[ii].innerText;
				if (txtValue.toUpperCase().indexOf(filter) > -1) {
					ada = true;
					console.log("ada");
				}
							
			}
			if (ada === true) {
				tr[i].style.display = "";
			} else {
				tr[i].style.display = "none";
			}
			
		}
		if((table.length-1) == x){
			hideProgress();
		}
	}
}
function showMasterData(tipedata){
	var div = document.createElement("div");
	serachData = document.createElement("input");
	serachData.type = "search";
	serachData.className = "searchon col-12";
	serachData.setAttribute("onsearch","searchingOnTable(\'masterDataDisplay\',this);");
	serachData.setAttribute("placeholder","Search..");
	parent = document.getElementById('masterDataDisplay').parentNode;
	parent.insertBefore(serachData,document.getElementById('masterDataDisplay'));
	searchon = parent.getElementsByClassName("searchon");
	if(searchon.length > 0){
		hsearch = searchon[0].clientHeight;
		parent.style.marginTop = hsearch+"px";
	}
   switch(tipedata) {	
    case 'karyawan':
        var col=['{nama}','{nik}','Unit','{divisi}'];
		var bothId = document.getElementById('masterDataDisplay');
		var option = {
			eleParent:bothId,
			title:'',
			header:col,
			numrow:false
		}
		asynPrintTable(option,function(originEvent){	
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM datakaryawan order by namakaryawan', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] = rs.rows.item(i).namakaryawan;
                            data[i][1] = rs.rows.item(i).nik;
                            data[i][2] = (rs.rows.item(i).subbagian).substr(0,4);
                            data[i][3] = (rs.rows.item(i).subbagian).substr(4,2);
                      }; 
                      //printTablemultiAct(col,data,'masterDataDisplay','');  
					  var newdata = {
						  data :data
					  }
					  originEvent.exec(newdata); 
					  originEvent.build();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null); 
		});
        break;
    case 'blok':
        var col=['Unit','{divisi}','{blok}','{tahuntanam}','{luas}','SPH','{pokok}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM setup_blok order by kodeblok', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
							jumlahpokok = 0;
							luasareaproduktif = 0;
							if(rs.rows.item(i).jumlahpokok != null){
								jumlahpokok = rs.rows.item(i).jumlahpokok;
							}
							if(rs.rows.item(i).luasareaproduktif != null){
								luasareaproduktif = rs.rows.item(i).luasareaproduktif;
							}
							sph = Math.round(jumlahpokok/luasareaproduktif);
                            data[i][0] = (rs.rows.item(i).kodeblok).substr(0,4);
                            data[i][1] = (rs.rows.item(i).kodeblok).substr(4,2);
                            data[i][2] = (rs.rows.item(i).kodeblok).substr(6,4);
                            data[i][3] =rs.rows.item(i).tahuntanam;
                            data[i][4] =rs.rows.item(i).luasareaproduktif;
                            data[i][5] =sph;
                            data[i][6] =rs.rows.item(i).jumlahpokok;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null); 
        break;
    case 'barang':
        var col=['{kode}','{namabarang}','{satuan}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM log_5masterbarang', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodebarang;
                            data[i][1] =rs.rows.item(i).namabarang;
                            data[i][2] =rs.rows.item(i).satuan;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay',''); 
						hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null); 
        break;
    case 'organisasi':
        var col=['{kode}','{organisasi}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM organisasi', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodeorganisasi;
                            data[i][1] =rs.rows.item(i).namaorganisasi;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null); 
        break;        
    case 'kegiatan':
        var col=['{kode}','{kegiatan}','{satuan}','{kelompok}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM setup_kegiatan', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodekegiatan;
                            data[i][1] =rs.rows.item(i).namakegiatan;
                            data[i][2] =rs.rows.item(i).satuan;
                            data[i][3] =rs.rows.item(i).kelompok;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break;
    case 'kendaraan':
        var col=['{kode}','Unit'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM vhc_5master', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodevhc;
                            data[i][1] =rs.rows.item(i).detailvhc;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break;
    case 'custommer':
        var col=['{kode}','{custumer}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM pmn_4customer', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodecustomer;
                            data[i][1] =rs.rows.item(i).namacustomer;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break;                
    case 'bjr':
        var col=['{blok}','{kelaspohon}','{bjr}','{tahun}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM kebun_bjr', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodeorg;
                            data[i][1] =rs.rows.item(i).kelaspohon;
                            data[i][2] =rs.rows.item(i).bjr;
                            data[i][3] =rs.rows.item(i).tahunproduksi;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break;  
    case 'basis':
        var col=['{jenis}','{kelaspohon}','{basis}','{premi} {lebih}','{premilibur}','{premilibur} {capaibasis}','{topografi}','{premi} {topografi}','{premi} {brondolan}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM basis_panen', [], function(tx, rs){  
                      var data=new Array();
						  for(var i=0; i<rs.rows.length; i++) {
								data[i] =new Array();
								data[i][0] =rs.rows.item(i).jenispremi;
								data[i][1] =rs.rows.item(i).kelaspohon;
								data[i][2] =rs.rows.item(i).basis;
								data[i][3] =rs.rows.item(i).premilebihbasis;
								data[i][4] =rs.rows.item(i).premilibur;
								data[i][5] =rs.rows.item(i).premiliburcapaibasis;
								data[i][6] =rs.rows.item(i).topografi;
								data[i][7] =rs.rows.item(i).premitopografi;
								data[i][8] =rs.rows.item(i).premibrondolan;
						  };
						printTablemultiAct(col,data,'masterDataDisplay','');
						hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break;  
    case 'kelas':
   var col=['{kelas}','{basisbulan}','{basishari}','{nama}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM kebun_kelaspohon', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kelas;
                            data[i][1] =rs.rows.item(i).basisbulan;
                            data[i][2] =rs.rows.item(i).basishari;
                            data[i][3] =rs.rows.item(i).nama;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay',''); 
					  hideProgress();					  
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break;  
    case 'kodedenda':
        var col=['{kodedenda}','{deskripsi}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM kebun_kodedenda', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodedenda;
                            data[i][1] =rs.rows.item(i).deskripsi;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break; 
    case 'dendapanen':
        var col=['{organisasi}','{kode}','{jenis}','{denda}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM kebun_denda', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodeorg;
                            data[i][1] =rs.rows.item(i).kodedenda;
                            data[i][2] =rs.rows.item(i).jenisdenda;
                            data[i][3] =rs.rows.item(i).denda;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break; 
		case 'mutuancak':
        var col=['Kode Mutu','Nama','{satuan}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM setup_mutu where idjenis not in (21,22)', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodemutu;
                            data[i][1] =rs.rows.item(i).namamutu;
                            data[i][2] =rs.rows.item(i).satuan;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break; 
		case 'kodehama':
        var col=['Kode Hama','Nama','{satuan}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM setup_hama', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodehama;
                            data[i][1] =rs.rows.item(i).namahama;
                            data[i][2] =rs.rows.item(i).satuan;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break;
		case 'kodetph':
        var col=['Unit','{divisi}','{blok}','{tph}','{luas}'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM setup_tph', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] = (rs.rows.item(i).kode).substr(0,4);
                            data[i][1] = (rs.rows.item(i).kode).substr(4,2);
                            data[i][2] = (rs.rows.item(i).kode).substr(6,4);
                            data[i][3] = (rs.rows.item(i).kode).substr(10,2);
                            data[i][4] =rs.rows.item(i).luas;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break;
		case 'kodekemandoran':
        var col=['Kode Mandor','Nama Mandor'];
            db.transaction(function (tx) {
                    tx.executeSql('SELECT * FROM kebun_5kemandoran', [], function(tx, rs){  
                      var data=new Array();
                      for(var i=0; i<rs.rows.length; i++) {
                            data[i] =new Array();
                            data[i][0] =rs.rows.item(i).kodemandor;
                            data[i][1] =rs.rows.item(i).namamandor;
                      }; 
                      printTablemultiAct(col,data,'masterDataDisplay','');  
					  hideProgress();
                    }, function(tx,error){
                      errorHandler(tx,error);
                    });
              },null,null);
        break;
    default:
        break;  
    }
}

var kebun={
    kode:'',
    pt:''
}
function setKebun(){
     /*/ambil inisial kebun (TIDAK PERLU SUDAH DI AMBIL DARI LOGIN)
    db.transaction(function (tx) {
            str='SELECT kodeblok FROM setup_blok limit 1';                      
            tx.executeSql(str, [], function(tx, rs){  
              for(var i=0; i<rs.rows.length; i++) {
                    kebun.kode= rs.rows.item(i).kodeblok.substr(0,4);
              };
      },null,null);
    }, function(tx,error){
      errorHandler(tx,error);
    });    */  
    db.transaction(function (tx) {
            str1='SELECT induk FROM organisasi where kodeorganisasi="'+sessionStorage.kebun+'"';                      
            tx.executeSql(str1, [], function(tx, rs1){  
              for(var i=0; i<rs1.rows.length; i++) {
                    kebun.pt= rs1.rows.item(i).induk;
              };                         
            }, function(tx,error){
              errorHandler(tx,error);
            });
      },null,null);    
}
function syntransaksibook(tgl,tiperansaksi,notransaksi,flag){
	if(typeof flag !== 'undefined'){
		flagstr = flag;
	}else{
		flagstr = "-";
	}
	frame_panel('panelSynTrans','Synchronize Transaction','','loadTransact('+tgl+','+tiperansaksi+','+notransaksi+','+flagstr+')');
}
function loadTransact(tgl,tipeTr,notrans,flag){
	if (typeof createTablesBKM === "function") { 
		createTablesBKM();
	}
	if (typeof createTablesSPB === "function") { 
		createTablesSPB();
	}
	if (typeof createTablesPanen === "function") { 
		createTablesPanen();
	}
	if(typeof tgl !== 'undefined'){
		setValue('tanggalTrx',tgl);
	}else{
		tgl=getValue('tanggalTrx');
	}
	if(typeof tipeTr !== 'undefined'){
		setValue('tiperansaksi',tipeTr);
	}else{
		tipeTr=getValue('tiperansaksi');
	}
	if(typeof flag !== 'undefined'){
		setValue('flagTrx',flag);
	}else{
		flag=getValue('flagTrx');
	}
	where = "";
    if(validateDate(tgl)){
        switch(tipeTr){
			case 'mutuhancak':
				db.transaction(function (tx) {
					dz=document.getElementById('nomorTransaksi');
					dz.length=0;
					if(typeof notrans !== 'undefined'){
						where = notrans;
					}
					tx.executeSql('SELECT * FROM kebun_mutuht where synchronized="" and tanggal="'+tgl+'" and upper(updateby)="'+sessionStorage.username.toUpperCase()+'"', [], function(tx, rs){  
					  if(rs.rows.length>0){
						  for(var i=0; i<rs.rows.length; i++) {
							  if(where == rs.rows.item(i).notransaksi){
								dz.options[dz.length]= new Option(rs.rows.item(i).notransaksi+" - "+rs.rows.item(i).tanggal,rs.rows.item(i).notransaksi,false,true);
							  }else{
								dz.options[dz.length]= new Option(rs.rows.item(i).notransaksi+" - "+rs.rows.item(i).tanggal,rs.rows.item(i).notransaksi,false,false);
							  }
						  };
					  }else{
								dz.options[dz.length]= new Option('Data is Empty','',false,false);
					  }  
					}, function(tx,error){
					  errorHandler(tx,error);
					});
			  },null,null); 
			break;
            case 'bkm':
                    db.transaction(function (tx) {
                            dz=document.getElementById('nomorTransaksi');
                            dz.length=0;
							if(typeof notrans !== 'undefined'){
								where = notrans;
							}
                            tx.executeSql('SELECT a.notransaksi,a.tanggal,b.kodeorg,c.namakegiatan FROM kebun_aktifitas a '+
							'left join kebun_prestasi b on a.notransaksi = b.notransaksi '+
							'left join setup_kegiatan c on b.kodekegiatan = c.kodekegiatan '+
							'where a.synchronized="" and a.tanggal="'+tgl+'" and upper(a.updateby)="'+sessionStorage.username.toUpperCase()+'"', [], function(tx, rs){  
                              if(rs.rows.length>0){
                                  for(var i=0; i<rs.rows.length; i++) {
									  if(where == rs.rows.item(i).notransaksi){
                                        dz.options[dz.length]= new Option(rs.rows.item(i).notransaksi+" - "+rs.rows.item(i).kodeorg+" || "+rs.rows.item(i).namakegiatan,rs.rows.item(i).notransaksi,false,true);
									  }else{
									    dz.options[dz.length]= new Option(rs.rows.item(i).notransaksi+" - "+rs.rows.item(i).kodeorg+" || "+rs.rows.item(i).namakegiatan,rs.rows.item(i).notransaksi,false,false);
									  }
                                  };  
                              }else{
                                        dz.options[dz.length]= new Option('Data is Empty','',false,false);
                              }
                            }, function(tx,error){
                              errorHandler(tx,error);
                            });
                      },null,null);            
                break;
            case 'spb':
                    db.transaction(function (tx) {
                            dz=document.getElementById('nomorTransaksi');
                            dz.length=0;
							if(typeof notrans !== 'undefined'){
								where = notrans;
							}
                            tx.executeSql('SELECT * FROM kebun_spbht where synchronized="" and tanggal="'+tgl+'" and upper(updateby)="'+sessionStorage.username.toUpperCase()+'"', [], function(tx, rs){  
                              if(rs.rows.length>0){
                                  for(var i=0; i<rs.rows.length; i++) {
									  if(where == rs.rows.item(i).nospb){
                                        dz.options[dz.length]= new Option(rs.rows.item(i).nospb+" - "+rs.rows.item(i).afdeling,rs.rows.item(i).nospb,false,true);
									  }else{
										dz.options[dz.length]= new Option(rs.rows.item(i).nospb+" - "+rs.rows.item(i).afdeling,rs.rows.item(i).nospb,false,false);
									  }
                                  };
                              }else{
                                        dz.options[dz.length]= new Option('Data is Empty','',false,false);
                              }  
                            }, function(tx,error){
                              errorHandler(tx,error);
                            });
                      },null,null); 
                break;  
            case 'panen':
                    db.transaction(function (tx) {
                            dz=document.getElementById('nomorTransaksi');
                            dz.length=0;
							if(typeof notrans !== 'undefined'){
								where = notrans;
							}
							whereClose = "";
							if(flag == "1"){
								//verify
								whereClose = ' and verify <> "0" ';
							}else if(flag == "0"){
								whereClose = ' and verify = "0" ';
							}
                            tx.executeSql('SELECT a.notransaksi,a.verify,a.tanggal,b.namakaryawan as leader,b.namakaryawan2 as leader2,a.nikmandor,a.nikmandor1 FROM kebun_panen a '+
							'left join datakaryawan b on a.nikmandor = b.karyawanid '+
							'where synchronized="" and tanggal="'+tgl+'" and upper(updateby)="'+sessionStorage.username.toUpperCase()+'" '+whereClose, [], function(tx, rs){  
                              if(rs.rows.length>0){
                                  for(var i=0; i<rs.rows.length; i++) {
										if(flag == "1"){
											//verify
											nottransaksiDB = rs.rows.item(i).verify;
										}else if(flag == "0"){
											nottransaksiDB = rs.rows.item(i).notransaksi;
										}
									  if(where == rs.rows.item(i).notransaksi){
                                        dz.options[dz.length]= new Option(rs.rows.item(i).notransaksi+" || "+rs.rows.item(i).leader+" - "+rs.rows.item(i).nikmandor1,rs.rows.item(i).notransaksi,false,true);
									  }else{
										dz.options[dz.length]= new Option(rs.rows.item(i).notransaksi+" || "+rs.rows.item(i).leader+" - "+rs.rows.item(i).nikmandor1,rs.rows.item(i).notransaksi,false,false);
									  }
                                  };
                              }else{
                                        dz.options[dz.length]= new Option('Data is Empty','',false,false);
                              }  
                            }, function(tx,error){
                              errorHandler(tx,error);
                            });
                      },null,null); 
                break;                                        
        }
	
    }else if(tgl==''){

    }
    else{
        if(tgl!=''){
            notifAlert('{tanggalsalah}','{error}');
            setValue('tanggalTrx','');
        }
    }
}

function loadSelectKaryawan(idelem,strquery,selection,addnewOpt){
	var selectValue = "";
	if(typeof selection !== 'undefined'){
		selectValue = selection;
	}
	
	 db.transaction(function (tx){
        ds=idelem;
        ds.length=0;
        ds.options[ds.length]=new Option("","",false,false);
            tx.executeSql(strquery, [], function(tx, rs){  
              for(var i=0; i<rs.rows.length; i++){
					position = rs.rows.item(i).lokasitugas;
					if(rs.rows.item(i).subbagian.trim() != ""){
						position = rs.rows.item(i).subbagian;
					}
					value_key = rs.rows.item(i).karyawanid;
					nama2 = "";
					if(rs.rows.item(i).namakaryawan2 != ""){
						nama2 = " / "+rs.rows.item(i).namakaryawan2;
					}
					if(rs.rows.item(i).karyawanid !== "other"){
						value_text = rs.rows.item(i).namakaryawan+nama2+" || ["+position+"] "+rs.rows.item(i).nik;
					}else{
						value_text = "Other..";
					}
					if(rs.rows.item(i).karyawanid == selectValue){
						ds.options[ds.length]= new Option(value_text,value_key,false,true);
					}else{
						ds.options[ds.length]= new Option(value_text,value_key,false,false);
					}
              };
			  
            }, function(tx,error){
              errorHandler(tx,error);
            });
    },null,null);
}
function loadSelect(idelem,strquery,selection){
	var selectValue = "";
	if(typeof selection !== 'undefined'){
		selectValue = selection;
	}
	 db.transaction(function (tx){
        ds=idelem;
        ds.length=0;
        ds.options[ds.length]=new Option("","",false,false);
            tx.executeSql(strquery, [], function(tx, rs){  
              for(var i=0; i<rs.rows.length; i++) {
				if(rs.rows.item(i).key == selectValue){
					val = translateScript(rs.rows.item(i).val);
					ds.options[ds.length]= new Option(val,rs.rows.item(i).key,false,true);
				}else{
					val = translateScript(rs.rows.item(i).val);
					ds.options[ds.length]= new Option(val,rs.rows.item(i).key,false,false);
				}
              };
            }, function(tx,error){
              errorHandler(tx,error);
            });
    },null,null);
}




//GPS special script for tracking location=============================================================
var cordWatchGPS;
var statusStopAndGoWatchGPS;
var waktu;
var gpsKu;
var numgpsKu = new Array();
var statusGetGPS = -1;
var optionsWatchGPS = {
	enableHighAccuracy: true,
	maximumAge: 30000,
	timeout: 27000
};
function successWatchGPS(pos) {
	
	var crd = pos.coords;
	var lat =	crd.latitude;
	var lng = crd.longitude;
	var alt = crd.altitude;
	var acc = crd.accuracy;
	numgpsKu.push(lat+"<br><br>"+lng);
	sessionStorage.latitude 	= lat;
	sessionStorage.longitude 	= lng;
	sessionStorage.altitude 	= alt;
	sessionStorage.accuracy 	= acc;
	var dName=sessionStorage.imei; 
	var tanggal= tanggalSekarang();
	var time= timeSekarang();
	
	var meter = 100;
	var username = "";
	if(sessionStorage.username != "undefined" || sessionStorage.username != ""){
		username = sessionStorage.username.toUpperCase();
	}
	if(username != ""){
		if(sessionStorage.lastlatitude != "" && sessionStorage.lastlongitude != ""){
			var jarak = measure(sessionStorage.lastlatitude, sessionStorage.lastlongitude, lat, lng);// meter
			if(jarak > meter){  
				console.log("Save Melebihi jarak : "+lat+" , "+lng);
				saveGPSLocation(username,lat,lng,alt,acc,dName,tanggal,time);
				createLastGPS(lat,lng,alt,acc);
			}else{
				console.log(lat+" , "+lng);
			}
		}else{
			console.log("Save : "+lat+" , "+lng);
			saveGPSLocation(username,lat,lng,alt,acc,dName,tanggal,time);
			createLastGPS(lat,lng,alt,acc);
		}
	}
	statusGetGPS = 1;
}



function continueGPS() {
	clearInterval(waktu);
	console.log('GPS Active');
    db.transaction(function (tx) {
    tx.executeSql('CREATE TABLE IF NOT EXISTS gps_interval (interval TEXT,enableupload TEXT)',[],null,function(tx,error){errorHandler(tx,error);});        
          tx.executeSql('SELECT * FROM gps_interval', [], function(tx, rs){ 
            var interVal=0; 
            var enableUploadGps=0; 
            for(var i=0; i<rs.rows.length; i++) {
                interVal =parseInt(rs.rows.item(i).interval);
                enableUploadGps=parseInt(rs.rows.item(i).enableupload);
            };   
            if(enableUploadGps == "1"){
				if(!gpsKu){
					//gpsKu=setInterval(function(){startGPS()},1000);
					gpsKu=setInterval(function(){checkEnableGPS()},1000);
					//checkEnableGPS(); 
				}
            }else{
				if(gpsKu){
					clearInterval(gpsKu);
				}
			}
          }, function(tx,error){
            errorHandler(tx,error);
          });
    },null,null); 
}

function errorWatchGPS(err) {
	statusGetGPS = 2;
	//setTimeout(function(){getGPSNow("on"); }, 1000);
	console.log('ERROR(' + err.code + '): ' + err.message);
}
function getGPSNow(type){
	console.log('GPS '+type.toUpperCase());
	if(type == "off"){
		//if(cordWatchGPS){
		//	navigator.geolocation.clearWatch(cordWatchGPS);
		//}
		//stopGps();
		statusStopAndGoWatchGPS = type;
		//gpsKu = setInterval(function(){checkEnableGPS()},1000);
	}else{
		//cordWatchGPS = navigator.geolocation.watchPosition(successWatchGPS, errorWatchGPS, optionsWatchGPS);
		statusGetGPS = 0;
		statusStopAndGoWatchGPS = type;
		navigator.geolocation.getCurrentPosition(successWatchGPS, errorWatchGPS, optionsWatchGPS);
		
	}
}
function checkEnableGPS(){
	console.log("Diagnostic GPS");
	if(cordova.plugins.diagnostic.isGpsLocationEnabled){
		cordova.plugins.diagnostic.isGpsLocationEnabled(function(enabled){
			if(enabled == true){
				calldialogEnableGPS();
				if(statusGetGPS !== 0){
					getGPSNow('on');
				}
			}else{
				calldialogErrorGPS();
				getGPSNow('off');
			}
		}, function(error){
			console.log("GPS ENABLE :" + JSON.stringify(error));
		});
	}else{
		console.log("Error : diagnostic Gps Location Enabled");
	}
}
function refreshLastGPS(username){
	db.transaction(function (tx) {
		tx.executeSql('CREATE TABLE IF NOT EXISTS gps_location ('+
		   'username TEXT,'+
		   'latitude TEXT,'+
		   'longitude TEXT,'+
		   'altitude TEXT,'+
		   'devicename TEXT,'+
		   'tanggal TEXT,'+
		   'waktu TEXT,'+
		   'synchronized TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
		qry ="select latitude,longitude,altitude from gps_location where upper(username) = '"+username.toUpperCase()+"' order by tanggal,waktu DESC limit 1 ";
		tx.executeSql(qry, [], function(tx, rs){
			if(rs.rows.length > 0){
				 createLastGPS(rs.rows.item(0).latitude,rs.rows.item(0).longitude,rs.rows.item(0).altitude,sessionStorage.accuracy);
			}else{
				if(sessionStorage.latitude != "" &&	sessionStorage.longitude != ""){
					createLastGPS(sessionStorage.latitude,sessionStorage.longitude,sessionStorage.altitude ,sessionStorage.accuracy);
				}
			}
		}, function(tx,error){errorHandler(tx,error);});
	},null,null);
}
function createLastGPS(lat,lng,alt,acc){
	// Last GPS
	sessionStorage.lastlatitude=lat; 
	sessionStorage.lastlongitude=lng; 
	sessionStorage.lastaltitude = alt; 
	sessionStorage.lastaccuracy = acc; 
	
}
function calldialogErrorGPS(err){
	//hideProgress();
	/*title = "Your GPS is Disabled.";
	message = "this app needs to be enable to works. Use GPS, with wifi or 3G. Please Turn on GPS..!";
	buttonLabels = ["Cancel","Later","Activefy"];
	notifConfirm(message,title,buttonLabels,onConfirmdialogErrorGPS);*/
	if(typeof err !== "undefined"){
		//notifAlert('ERROR(' + err.code + '): ' + err.message);
		console.log('ERROR(' + err.code + '): ' + err.message);
	}
	obj=document.getElementsByClassName('progress');
	if(obj.length>0){
		obj[0].style.display='block';
		obj[0].setAttribute('breakfor','gps');
		obj[0].style.background='#A1DCEE';
		image=obj[0].getElementsByTagName('img');
		if(image.length>0){
			image[0].setAttribute("progress-img",image[0].src);
			image[0].src = "images/GPS-disabled.jpg";
			image[0].style = "width: 100%;position: fixed;left: 0px;right: 0px;top: 0px;bottom: 0px;height: auto;";
		}
	}
}
function stopGps(){
    clearInterval(gpsKu);
}
function calldialogEnableGPS(){
	obj=document.getElementsByClassName('progress');
	if(obj.length>0){
		if(obj[0].getAttribute('breakfor') != null && obj[0].getAttribute('breakfor') == 'gps'){
			obj[0].style.display='none';
			obj[0].setAttribute('breakfor','loader');
			obj[0].style.background=null;
			image=obj[0].getElementsByTagName('img');
			if(image.length>0){
				image[0].src = "images/loading_owl.gif";
				image[0].style = "";
			}
		}
	}
}
function onConfirmdialogErrorGPS(button){
	switch(button){
		case 1: 
			alert('Cancel');
		break;//cancel
		case 2: 
			alert('Later');
		break;//neutro option
		case 3: 
			alert('Go');
		break;//user go to configuration
	}
}
function saveGPSLocation(username,lat,lng,alt,acc,dName,tanggal,waktu){
	db.transaction(function (tx) {
		tx.executeSql('CREATE TABLE IF NOT EXISTS gps_location ('+
					   'username TEXT,'+
					   'latitude TEXT,'+
					   'longitude TEXT,'+
					   'altitude TEXT,'+
					   'devicename TEXT,'+
					   'tanggal TEXT,'+
					   'waktu TEXT,'+
					   'synchronized TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
		tx.executeSql('DELETE FROM gps_location where synchronized="1"',[],null,function(tx,error){errorHandler(tx,error);});
	
		tx.executeSql('INSERT INTO gps_location VALUES("'+username+'","'+lat+'","'+lng+'","'+alt+'","'+dName+'","'+tanggal+'","'+waktu+'","")',
		[],null,function(tx,error){errorHandler(tx,error);});
		sessionStorage.lastlatitude = lat;
		sessionStorage.lastlongitude = lng;
		sessionStorage.lastaltitude = alt;
		sessionStorage.lastaccuracy = acc;
	}); 
}
/*
//check if gps is allowed by the server evry 10 secons

//var waktu=setInterval(function(){continueGPS()},10000);//check if gps is allowed by the server evry 10 secons

function startGPS(){
	var GPSgeoOptions = {
		enableHighAccuracy: true,
		maximumAge: 30000,
		timeout: 27000
	};
      if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(GPSBerhasil, GPStdkBerhasil, GPSgeoOptions);
    } else {
        //also notifAlert nothing
        //notifAlert("Geolocation services are not supported by your web browser.");
    }
	
	function GPSBerhasil(position) {
		var lat = position.coords.latitude;
		var lng = position.coords.longitude;
		var alt = position.coords.altitude;
		var acc = position.coords.accuracy;
		sessionStorage.latitude 	= lat;
		sessionStorage.longitude 	= lng;
		sessionStorage.altitude 	= alt;
		sessionStorage.accuracy 	= acc;
		
		console.log(lat+" , "+lng);
		
	   // var dName='unknown'; try{dName=device.uuid;}catch(e){}
		
		var dName='unknown'; 
		try{
			dName=device.uuid;
		}catch(e){
			
		}
			//var dName=	device.mobile();
		
		var tanggal='00-00-00';
		var time='00:00:00';
		//var tgl=new Date();
		var d = new Date();
		var y = d.getFullYear();
		var m = d.getMonth()+1;
		var d = d.getDate();
		var h = d.getHours();
		var i = d.getMinutes();
		var s = d.getSeconds();
		tanggal = y+"-"+m.lpad(2,"0")+"-"+d.lpad(2,"0");
		time   =  h.lpad(2,"0")+':'+i.lpad(2,"0")+':'+s.lpad(2,"0");


		if(sessionStorage.lastlatitude == "" && sessionStorage.lastlongitude == ""){
				saveGPSLocation(lat,lng,alt,acc,dName,tanggal,time);
		}else{
			var jarak = measure(sessionStorage.lastlatitude, sessionStorage.lastlongitude, lat, lng);// meter
			if(jarak > 500){
				saveGPSLocation(lat,lng,alt,acc,dName,tanggal,time);
			}
		}
			   
		 
	}
	function GPStdkBerhasil(error) {
		//notifAlert nothing so user not distrubed
		console.log(error);
	}
}*/
document.addEventListener("deviceready",function(){
	console.log('deviceready');
	refresh_identitas();
	
	//if(!gpsKu){
		//waktu = setInterval(function(){continueGPS()},1000);
	//}
	//waktuopenUpdater = setInterval(function(){openUpdater()},3000);
	
},false);


