var timeOutTracking;

function checkIfGpsTableExist(){
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
  },null,null); 
}

function synTransakction(event)
{
	
	//stop Propaganda event
	if(typeof event !== "undefined"){
		stopPropaganda(event);
	}else{
		ev = this.event;
		stopPropaganda(ev);
	}

	//executed when user sinchronizing
	//function on mobileTransaction.js
	checkIfGpsTableExist(); 
	
	nomorTransaksi	=getValue('nomorTransaksi');
	tiperansaksi	=getValue('tiperansaksi');
	tanggalTrx		=getValue('tanggalTrx');
	flagTrx			=getValue('flagTrx');
	
	if(nomorTransaksi=='' || tiperansaksi=='' || tanggalTrx=='')
	{
		mssg = translateScript("{tidakmemilikidata}");
		alert(mssg);
	}
	else
	{
		if(!validateDate(tanggalTrx))
		{
			mssg = translateScript("{tanggalsalah}");
			alert(mssg);
		}
		else
		{
			if(tiperansaksi=='bkm'){
				synBkm(tanggalTrx,nomorTransaksi);				
			}else if(tiperansaksi=='spb'){
				synSpb(tanggalTrx,nomorTransaksi);
			}else if(tiperansaksi=='panen'){
				synPanen(tanggalTrx,nomorTransaksi,flagTrx);
			}else if(tiperansaksi=='mutuhancak'){
				synMutuHancak(tanggalTrx,nomorTransaksi);
			}
		}
	}		
}
function synMutuHancak(tgl,notrx){
	// Masih dalam Prosses
	var mutudt = new Array();
	var datamutuhancak ="";
	db.transaction(function (tx){
		var str='SELECT * FROM kebun_mutuht where notransaksi="'+notrx+'" and tanggal="'+tgl+'" limit 1';
        tx.executeSql(str, [], function(tx, rs){
			var strData='';
			for(var i=0; i<rs.rows.length; i++){
				strData+='&notransaksi='+rs.rows.item(i).notransaksi;
				strData+='&tanggal='+rs.rows.item(i).tanggal;
				strData+='&kodeorg='+sessionStorage.kebun;
				strData+='&deviceid='+sessionStorage.imei;
				datamutuhancak+=strData; 
				
				param='method=transaction&tipeData=mutuhancak&datatransfer=datautama&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei+'&uuid='+sessionStorage.imei;
				
				var str='SELECT * FROM kebun_mutu where notransaksi="'+notrx+'"';
				tx.executeSql(str, [], function(tx, rs){
					for(var i=0; i<rs.rows.length; i++){
						mutudt[i] = new Array();
						mutudt[i]['nik'] = rs.rows.item(i).nik;
						mutudt[i]['blok'] = rs.rows.item(i).blok;
						mutudt[i]['rotasi'] = rs.rows.item(i).rotasi;
						mutudt[i]['nourut'] = rs.rows.item(i).nourut;
						mutudt[i]['idjenis'] = rs.rows.item(i).kodemutu;
						mutudt[i]['jml'] = rs.rows.item(i).jml;
					};
					param += datamutuhancak;
					if(rs.rows.length>0){
						post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
					}else{
						mssg = translateScript("{tidakmemilikidata}");
						notifAlert(mssg);
					}
				}, function(tx,error){errorHandler(tx,error);});
			};
        }, function(tx,error){errorHandler(tx,error);});
	},null,null); 
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
					console.log(con.responseText);
				}else{
					
					try{
						arr = JSON.parse(con.responseText);
						notransaksi = arr.notransaksi;
						noref = arr.noref;
						tanggal = arr.tanggal;
						error = arr.err;
						
						if(error.err == "false"){
							console.log("Syn data Mutuhancak");
							insert_mutudetail(notransaksi,noref,tanggal,mutudt,0);
						}else{
							if(error.mssg){
								hideProgress();
								notifAlert(error.mssg);
							}
						}
					}catch(e){
						hideProgress();
						console.log(con.responseText);
						notifAlert("Syn Data Transaksi : error result Array Transaction header respond","{error}");
					}
				}
			}else {
				hideProgress();
				error_catch(con.status);
			}
		}else{
			error_catch(con.readyState);
			hideProgress();
		}
	}
}
function insert_mutudetail(notransaksi,noref,tanggal,mutudt,num){
	var nik = new Array();
	var blok = new Array();
	var rotasi = new Array();
	var nourut = new Array();
	var idjenis = new Array();
	var jml = new Array();
	var strData = "";
	limit = 50;
	forloop = (num + limit);
	if(forloop >= mutudt.length){
		forloop = mutudt.length;
	}
	var urut = 0;
	for(var i=num; i<forloop; i++){
		nik[urut] 		= mutudt[i]['nik'];
		blok[urut] 		= mutudt[i]['blok'];
		rotasi[urut] 	= mutudt[i]['rotasi'];
		nourut[urut] 	= mutudt[i]['nourut'];
		idjenis[urut] 	= mutudt[i]['idjenis'];
		jml[urut] 		= mutudt[i]['jml'];
		urut++;
	}; 
	strData+='&notransaksi='+notransaksi;
	strData+='&noref='+noref;
	strData+='&kodeorg='+sessionStorage.kebun;
	strData+='&tanggal='+tanggal;
	strData+='&nik='+nik;
	strData+='&blok='+blok;
	strData+='&sesi='+rotasi;
	strData+='&nourut='+nourut;
	strData+='&idjenis='+idjenis;
	strData+='&jml='+jml;
	param='method=transaction&tipeData=mutuhancak&datatransfer=datadetail&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei;
	param += strData;
	
	if(mutudt.length>0){
		post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
	}else{
		mssg = translateScript("{tidakmemilikidata}");
		hideProgress();
		notifAlert(mssg);
	}	
	
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200) {
				console.log(con.responseText);
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
					hideProgress();
				}else {
					  try{
						arr = JSON.parse(con.responseText);
						notransaksi = arr.notransaksi;
						noref = arr.noref;
						tanggal = arr.tanggal;
						error = arr.err;
						//{"notransaksi":"20190227\/KAGE\/MHC\/001","noref":"2019022710313109007206","tanggal":"2019-02-27","err":{"err":"false","mssg":"Berhasil Insert Detail Mutu Hancak"}}
						if(error.err == "false"){
							if(forloop < mutudt.length){
								insert_mutudetail(notransaksi,noref,tanggal,mutudt,forloop);
							}else{
								selesaisynmutuhancak(notransaksi,noref);
							}
						}else{
							if(error.mssg){
								hideProgress();
								notifAlert(error.mssg);
							}
						}
					}catch(e){
						hideProgress();
						console.log(con.responseText);
						console.log(e);
						notifAlert("Syn Data Transaksi : error result Array Transaction header respond.","{error}");
					}	
				}
			}else{
				hideProgress();
				error_catch(con.status);
			}
		}     
	}   
}
function selesaisynmutuhancak(notransaksi,noref){
	db.transaction(function (tx) {
	  var str='update kebun_mutuht set synchronized="'+notransaksi+'" where notransaksi="'+noref+'"';
		tx.executeSql(str, [], function(tx, rs){  
			loadTransact();
			hideProgress();
			notifAlert('{berhasil}','{pesan}');
		}, function(tx,error){
		  notifAlert('{gagal}','{pesan}');
		  errorHandler(tx,error);
		  hideProgress();
		});
  },null,null);   
  deleteData('MHC');
}
function synBkm(tgl,notrx){
	console.log("synBkm");
	//ambil data pada local database
    db.transaction(function (tx){
    	var str='SELECT * FROM kebun_aktifitas where notransaksi="'+notrx+'"';
        tx.executeSql(str, [], function(tx, rs){  
          var dataH="";
          for(var i=0; i<rs.rows.length; i++) {
          		//do not change the order of below data
          		dataH +="&notransaksi="+rs.rows.item(i).notransaksi;
          		dataH +="&tanggal="+rs.rows.item(i).tanggal;
                dataH +="&kelompok="+rs.rows.item(i).kelompok;
                dataH +="&kodeorg="+rs.rows.item(i).kodeorg;
                dataH +="&nikmandor="+rs.rows.item(i).nikmandor;
                dataH +="&nikmandor1="+rs.rows.item(i).nikmandor1;
                dataH +="&nikasisten="+rs.rows.item(i).nikasisten;
                dataH +="&kerani="+rs.rows.item(i).kerani;
                dataH +="&kodekegiatan="+rs.rows.item(i).kodekegiatan;
                dataH +="&nobkm="+rs.rows.item(i).nobkm;
          }; 
			param = 'method=transaction&tipeData=bkm&datatransfer=datautama&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei+dataH;
			//window.history.pushState({urlPath:param},"", param);
			post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
        }, function(tx,error){
          errorHandler(tx,error);
        });
	},null,null); 
	
	function respog(){
		hideProgress();
		if(con.readyState==4){
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					try{
						arr = JSON.parse(con.responseText);
						nodevice = arr.nodevice;
						notransaksi = arr.notransaksi;
						tanggal = arr.tanggal;
						error = arr.err;
						if(error.err == "false"){
							synBkmPrestasi(nodevice,notransaksi,tanggal);
						}else{
							notifAlert(error.mssg,"{error}");
						}
					}catch(e){
						notifAlert("Syn Data Transaksi : error result Array Transaction header respond","{error}");
					}
				}
			}else {
				error_catch(con.status);
			}
		}	
	}
}
function synBkmPrestasi(nodevice,notransaksi,tanggal){
	console.log("synBkmPrestasi");
	db.transaction(function (tx) {
		var str='SELECT * FROM kebun_prestasi where notransaksi="'+nodevice+'"';
		tx.executeSql(str, [], function(tx, rs){  
			var prestasiD=new Array();
			for(var i=0; i<rs.rows.length; i++) {
				prestasiD[i] = new Array();
				prestasiD[i][0] = rs.rows.item(i).kodekegiatan;
				prestasiD[i][1] = rs.rows.item(i).kodeorg;
				prestasiD[i][2] = rs.rows.item(i).jumlahhasilkerja;
				prestasiD[i][3] = rs.rows.item(i).jumlahhk;
			}; 

			var str2='SELECT * FROM kebun_kehadiran where notransaksi="'+nodevice+'"';
			tx.executeSql(str2, [], function(tx, rs){  
				var kehadiranD=new Array();
				for(var i=0; i<rs.rows.length; i++) {
					kehadiranD[i]=new Array();
					kehadiranD[i][0] =rs.rows.item(i).kodekegiatan;         		
					kehadiranD[i][1] =rs.rows.item(i).kodeorg;         		
					kehadiranD[i][2] =rs.rows.item(i).nik;         		
					kehadiranD[i][3] =rs.rows.item(i).jhk;
					kehadiranD[i][4] =rs.rows.item(i).hasilkerja;
					kehadiranD[i][5] =rs.rows.item(i).absensi;
					kehadiranD[i][6] =rs.rows.item(i).insentif;
					kehadiranD[i][7] =rs.rows.item(i).jam_overtime;
				}; 
				execBkmPrestasi(nodevice,notransaksi,tanggal,kehadiranD,0);
				
			}, function(tx,error){
			  errorHandler(tx,error);
			});
			
		}, function(tx,error){
		  errorHandler(tx,error);
		});
	},null,null); 
}
function execBkmPrestasi(nodevice,notransaksi,tanggal,kehadiranD,num){
	
	var limit = 50;
	forloop = (num + limit);
	if(forloop >= kehadiranD.length){
		forloop = kehadiranD.length;
	}
	var dtSendData		=	new Array();
	var kodekegiatan	=	new Array();
	var kodeorg			=	new Array();
	var nik				=	new Array();
	var jhk				=	new Array();
	var hasilkerja		=	new Array();
	var absensi			=	new Array();
	var insentif		=	new Array();
	var jam_overtime	=	new Array();
	var urut = 0;
	for(var x=num; x<forloop; x++) {
		kodekegiatan[urut] 	= kehadiranD[x][0];
		kodeorg[urut] 		= kehadiranD[x][1];
		nik[urut] 			= kehadiranD[x][2];
		jhk[urut] 			= kehadiranD[x][3];
		hasilkerja[urut] 	= kehadiranD[x][4];
		absensi[urut] 		= kehadiranD[x][5];
		insentif[urut] 		= kehadiranD[x][6];
		jam_overtime[urut] 	= kehadiranD[x][7];
		//dtSendData.push(data[x]);
		urut++;
	}
	
	param='method=transaction&tipeData=bkm&datatransfer=dataprestasi&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei+'&notransaksi='+notransaksi+'&nodevice='+nodevice+'&tanggal='+tanggal+'&nourut='+num+'&kodekegiatan='+kodekegiatan+'&kodeorg='+kodeorg+'&nik='+nik+'&jhk='+jhk+'&hasilkerja='+hasilkerja+'&absensi='+absensi+'&insentif='+insentif+'&jam_overtime='+jam_overtime;
	//console.log(param);
	//if(kodekegiatan.length>0){
	post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
	//}

	function respog(){
		hideProgress();
		if(con.readyState==4){
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					try{
						arr = JSON.parse(con.responseText);
						nodevice = arr.nodevice;
						notransaksi = arr.notransaksi;
						tanggal = arr.tanggal;
						error = arr.err;
						if(error.err == "false"){
							if(forloop < kehadiranD.length){
								execBkmPrestasi(nodevice,notransaksi,tanggal,kehadiranD,forloop);
							}else{
								synBkmMaterial(nodevice,notransaksi,tanggal);
							}
						}else{
							notifAlert(error.mssg,"{error}");
						}
					}catch(e){
						notifAlert("Syn Data Transaksi : error result Array Transaction header respond","{error}");
					}
				}
			}else {
				error_catch(con.status);
			}
		}     
	} 
}
function synBkmMaterial(nodevice,notransaksi,tanggal){
	console.log("synBkmMaterial");
	db.transaction(function (tx) {
		var str='SELECT * FROM kebun_pakaimaterial where notransaksi="'+nodevice+'"';
		tx.executeSql(str, [], function(tx, rs){  
			var datamaterial=new Array();
			for(var i=0; i<rs.rows.length; i++) {
				datamaterial[i] = new Array();
				datamaterial[i][0] = rs.rows.item(i).kodekegiatan;
				datamaterial[i][1] = rs.rows.item(i).kodeorg;
				datamaterial[i][2] = rs.rows.item(i).gudang;
				datamaterial[i][3] = rs.rows.item(i).kodebarang;
				datamaterial[i][4] = rs.rows.item(i).kwantitasha;
				datamaterial[i][5] = rs.rows.item(i).kwantitas;
			}; 
			execBkmMaterial(nodevice,notransaksi,tanggal,datamaterial,0);
			
		}, function(tx,error){
		  errorHandler(tx,error);
		});
	},null,null); 
}
function execBkmMaterial(nodevice,notransaksi,tanggal,datamaterial,num){
	var limit = 50;
	forloop = (num + limit);
	if(forloop >= datamaterial.length){
		forloop = datamaterial.length;
	}
	var kodekegiatan	=	new Array();
	var kodeorg			=	new Array();
	var gudang			=	new Array();
	var kodebarang		=	new Array();
	var kwantitasha		=	new Array();
	var kwantitas		=	new Array();
	
	var urut = 0;
	for(var x=num; x<forloop; x++) {
		kodekegiatan[urut] 	= datamaterial[x][0];
		kodeorg[urut] 		= datamaterial[x][1];
		gudang[urut]		= datamaterial[x][2];
		kodebarang[urut]	= datamaterial[x][3];
		kwantitasha[urut] 	= datamaterial[x][4];
		kwantitas[urut]		= datamaterial[x][5];
		urut++;
	}
	
	param='method=transaction&tipeData=bkm&datatransfer=datamaterial&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei+'&notransaksi='+notransaksi+'&nodevice='+nodevice+'&tanggal='+tanggal+'&nourut='+num+'&kodekegiatan='+kodekegiatan+'&kodeorg='+kodeorg+'&gudang='+gudang+'&kodebarang='+kodebarang+'&kwantitasha='+kwantitasha+'&kwantitas='+kwantitas;
	//if(kodekegiatan.length>0){
		post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
	//}

	function respog(){
		hideProgress();
		if(con.readyState==4){
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					try{
						arr = JSON.parse(con.responseText);
						nodevice = arr.nodevice;
						notransaksi = arr.notransaksi;
						tanggal = arr.tanggal;
						error = arr.err;
						if(error.err == "false"){
							if(forloop < datamaterial.length){
								execBkmMaterial(nodevice,notransaksi,tanggal,datamaterial,forloop);
							}else{
								updateSyncedBKM(nodevice,notransaksi);
							}
						}else{
							notifAlert(error.mssg,"{error}");
						}
					}catch(e){
						notifAlert("Syn Data Transaksi : error result Array Transaction header respond","{error}");
					}
				}
			}else {
				error_catch(con.status);
			}
		}     
	} 
}
/*
function synBkmold(tgl,notrx){
	//ambil data pada local database
	var headerBKM='';
    db.transaction(function (tx) {
    	var str='SELECT * FROM kebun_aktifitas where notransaksi="'+notrx+'"';
        tx.executeSql(str, [], function(tx, rs){  
          var dataH=new Array();
          for(var i=0; i<rs.rows.length; i++) {
          		//do not change the order of below data
          		dataH[0] =rs.rows.item(i).notransaksi;
          		dataH[1] =rs.rows.item(i).tanggal;
                dataH[2] =rs.rows.item(i).kelompok;
                dataH[3] =rs.rows.item(i).kodeorg;
                dataH[4] =rs.rows.item(i).nikmandor;
                dataH[5] =rs.rows.item(i).nikmandor1;
                dataH[6] =rs.rows.item(i).nikasisten;
                dataH[7] =rs.rows.item(i).kerani;
                dataH[8] =rs.rows.item(i).kodekegiatan;
                dataH[9] =rs.rows.item(i).nobkm;
          }; 
  			headerBKM=dataH;	
        }, function(tx,error){
          errorHandler(tx,error);
        });
  },null,null); 
	var prestasiBKM='';
    db.transaction(function (tx) {
    	var str='SELECT * FROM kebun_prestasi where notransaksi="'+notrx+'"';
        tx.executeSql(str, [], function(tx, rs){  
           var prestasiD=new Array();
          for(var i=0; i<rs.rows.length; i++) {
				prestasiD[0] = rs.rows.item(i).kodekegiatan;
				prestasiD[1] = rs.rows.item(i).kodeorg;
				prestasiD[2] = rs.rows.item(i).jumlahhasilkerja;
				prestasiD[3] = rs.rows.item(i).jumlahhk;
          }; 
  			prestasiBKM=prestasiD;	
        }, function(tx,error){
          errorHandler(tx,error);
        });
  },null,null); 
  
    var kehadiranBKM='';
    db.transaction(function (tx) {
    	var str='SELECT * FROM kebun_kehadiran where notransaksi="'+notrx+'"';
        tx.executeSql(str, [], function(tx, rs){  
           var kehadiranD=new Array();
          for(var i=0; i<rs.rows.length; i++) {
          		kehadiranD[i]=new Array(3);
          		if(i>0){
                	kehadiranD[i][0] ='|'+rs.rows.item(i).nik;
          		}else{
                	kehadiranD[i][0] =rs.rows.item(i).nik;
          		}          		
				kehadiranD[i][1] =rs.rows.item(i).jhk;
				kehadiranD[i][2] =rs.rows.item(i).hasilkerja;
				kehadiranD[i][3] =rs.rows.item(i).absensi;
				kehadiranD[i][4] =rs.rows.item(i).insentif;
				kehadiranD[i][5] =rs.rows.item(i).jam_overtime;
          }; 
  			kehadiranBKM=kehadiranD;	
        }, function(tx,error){
          errorHandler(tx,error);
        });
  },null,null); 

    var materialBKM='';
    db.transaction(function (tx) {
    	var str='SELECT * FROM kebun_pakaimaterial where notransaksi="'+notrx+'"';
        tx.executeSql(str, [], function(tx, rs){  
		var materialD=new Array();
		for(var i=0; i<rs.rows.length; i++) {
			materialD[i]=new Array(4);
			if(i>0){
				materialD[i][0] ='|'+rs.rows.item(i).gudang;
			}else{
				materialD[i][0] =rs.rows.item(i).gudang;
			}
			materialD[i][1] =rs.rows.item(i).kodebarang;
			materialD[i][2] =rs.rows.item(i).kwantitas;
			materialD[i][3] =rs.rows.item(i).kwantitasha;
		};        
	materialBKM=materialD;	
	param='method=transaction&tipeData=bkm&username='+sessionStorage.username+'&password='+sessionStorage.password+
	'&header='+headerBKM+'&prestasi='+prestasiBKM+'&kehadiran='+kehadiranBKM+'&material='+materialBKM+'&uuid='+sessionStorage.imei;

	//window.history.pushState({urlPath:param},"", param);
	post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
        }, function(tx,error){
          errorHandler(tx,error);
        });
  },null,null); 
    function respog(){
        hideProgress();
		if(con.readyState==4)
			{
			  if (con.status == 200) {
					  if (!isSaveResponse(con.responseText)) {
							  alert('ERROR TRANSACTION,\n' + con.responseText);
					  }
					  else {
						updateSyncedBKM(con.responseText);
					  }
			  }
			  else {
					  error_catch(con.status);
			  }
		}	
    }
	deleteData('BKM');
}
*/
function deleteData(switchCase){
	var dateNow = tanggalSekarang();
	switch(switchCase){
		case'BKM':
			db.transaction(function (tx) {
				var str="SELECT notransaksi FROM kebun_aktifitas where synchronized <> '' and lastupdate < date('"+dateNow+"', '-45 days') ";
				tx.executeSql(str, [], function(tx, rs){  
					for(var i=0; i<rs.rows.length; i++) {
						//console.log('Delete BKM notransaksi : '+rs.rows.item(i).notransaksi+' tanggal : '+rs.rows.item(i).lastupdate);
						var notransaksi = rs.rows.item(i).notransaksi;
						tx.executeSql('DELETE FROM kebun_aktifitas where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_prestasi where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_kehadiran where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_pakaimaterial where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
					}; 
				}, function(tx,error){
				  errorHandler(tx,error);
				});
			},null,null); 
			
		break; 
		case'PNN':
			//kebun_panen,kebun_panendt
			db.transaction(function (tx) {
				var str="SELECT notransaksi FROM kebun_panen where lastupdate < date('"+dateNow+"', '-45 days')  and synchronized <> ''";
				tx.executeSql(str, [], function(tx, rs){  
				  for(var i=0; i<rs.rows.length; i++) {
						//console.log('Delete PANEN notransaksi : '+rs.rows.item(i).notransaksi+' tanggal : '+rs.rows.item(i).lastupdate);
						var notransaksi = rs.rows.item(i).notransaksi;
						tx.executeSql('DELETE FROM kebun_panen where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_panendt where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_grading where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_kondisi_buah where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_gerdang where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_absen_panen where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_bkmsign where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
				  }; 
				}, function(tx,error){
				  errorHandler(tx,error);
				});
			},null,null); 
		break; 
		case'SPB':
			//kebun_spbht,kebun_spbdt
			db.transaction(function (tx) {
				var str="SELECT nospb FROM kebun_spbht where lastupdate < date('"+dateNow+"', '-45 days')  and synchronized <> ''";
				tx.executeSql(str, [], function(tx, rs){  
				  for(var i=0; i<rs.rows.length; i++) {
						//console.log('Delete SPB nospb : '+rs.rows.item(i).nospb+' tanggal : '+rs.rows.item(i).lastupdate);
						var nospb = rs.rows.item(i).nospb;
						tx.executeSql('DELETE FROM kebun_spbht where nospb="'+nospb+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_spbdt where nospb="'+nospb+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_spbtkbm where nospb="'+nospb+'"',[],null,function(tx,error){errorHandler(tx,error);});
				  }; 
				}, function(tx,error){
				  errorHandler(tx,error);
				});
			},null,null); 
		break; 
		case'MHC':
			//kebun_mutuht,kebun_mutu
			db.transaction(function (tx) {
				var str="SELECT notransaksi FROM kebun_mutuht where lastupdate < date('"+dateNow+"', '-45 days')  and synchronized <> ''";
				tx.executeSql(str, [], function(tx, rs){  
				  for(var i=0; i<rs.rows.length; i++) {
						var notransaksi = rs.rows.item(i).notransaksi;
						tx.executeSql('DELETE FROM kebun_mutuht where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
						tx.executeSql('DELETE FROM kebun_mutu where notransaksi="'+notransaksi+'"',[],null,function(tx,error){errorHandler(tx,error);});
				  }; 
				}, function(tx,error){
				  errorHandler(tx,error);
				});
			},null,null); 
		break; 
	}
}
function updateSyncedBKM(nodevice,notransaksi){
	console.log("updateSyncedBKM");
    db.transaction(function (tx) {
    	var str='update kebun_aktifitas set synchronized="'+notransaksi+'" where notransaksi="'+nodevice+'"';
        tx.executeSql(str, [], function(tx, rs){  
           loadTransact();
		   notifAlert('{berhasil}');
           synGpsLocation();	
		   if(document.getElementById('panelBKM')){
				if(typeof tampilkanListBkm === 'function'){
					tampilkanListBkm();
				}
			}
        }, function(tx,error){
			notifAlert('{gagal}','{error}');		  
			errorHandler(tx,error);
        });
  },null,null); 	
  deleteData('BKM');
}

function synGpsLocation(){
	checkIfGpsTableExist();
	//timeOutTracking=setTimeout(synGpsLocation,60000);
	 db.transaction(function (tx){
    	var str='SELECT * FROM gps_location where synchronized=""';
        tx.executeSql(str, [], function(tx, rs){
          var dtGps=new Array();
		  if(rs.rows.length > 0){
			  for(var i=0; i<rs.rows.length; i++) {
				dtGps[i]=new Array();
				if(i>0){
					dtGps[i][0] ="|"+rs.rows.item(i).username;        		
				}else{
					dtGps[i][0] =rs.rows.item(i).username;
				}
				dtGps[i][1] =rs.rows.item(i).latitude;
				dtGps[i][2] =rs.rows.item(i).longitude;
				dtGps[i][3] =rs.rows.item(i).altitude;
				dtGps[i][4] =rs.rows.item(i).devicename;
				dtGps[i][5] =rs.rows.item(i).tanggal;
				dtGps[i][6] =rs.rows.item(i).waktu;
			  }; 
			  ecexGpsLocation(dtGps,0);
		  }
        }, function(tx,error){
          errorHandler(tx,error);
        });
  },null,null); 
}	
function ecexGpsLocation(data,num){
	var limit = 50;
	forloop = (num + limit);
	if(forloop >= data.length){
		forloop = data.length;
	}
	var dtSendGps=new Array();
	for(var x=num; x<forloop; x++) {
		dtSendGps.push(data[x]);
	}
	
   	param='method=transaction&tipeData=gps&username='+sessionStorage.username+'&password='+sessionStorage.password+'&dtGps='+dtSendGps+'&uuid='+sessionStorage.imei;
	if(dtSendGps.length>0){
		post_response_textGPS(sessionStorage.server+'/owlMobile.php', param, respog);
	}

  function respog(){
    if(con.readyState==4){
        if (con.status == 200) {
            if (!isSaveResponse(con.responseText)){
                //alert('ERROR TRANSACTION,\n' + con.responseText);
            }else{
				if(forloop < data.length){
					ecexGpsLocation(data,forloop);
				}else{
					db.transaction(function (tx) {
                      var str='delete from gps_location';
						  tx.executeSql(str, [],null, null);
					 },null,null);
				}
            }
        }else {
            //error_catch(con.status);
        }
      }     
  } 
}

function synSpb(tanggalTrx,nomorTransaksi){
    var headerSPB='';
	var tkbmSPB='';
	var detailF='';
    db.transaction(function (tx) {
      var str='SELECT * FROM kebun_spbht where nospb="'+nomorTransaksi+'"';
	  ext = ".jpeg";
        tx.executeSql(str, [], function(tx, rs){  
          var dataH=new Array();
          for(var i=0; i<rs.rows.length; i++) {
              //do not change the order of below data
                dataH[0] =rs.rows.item(i).nospb;
                dataH[1] =rs.rows.item(i).tujuan;
                dataH[2] =rs.rows.item(i).penerimatbs;
                dataH[3] =rs.rows.item(i).tanggal;
                dataH[4] =rs.rows.item(i).afdeling;
                dataH[5] =rs.rows.item(i).driver;
                dataH[6] =rs.rows.item(i).nopol;
                dataH[7] =rs.rows.item(i).ffbdocument;
                dataH[8] =rs.rows.item(i).kraniproduksi;
				dataH[9] =rs.rows.item(i).nospb+ext;
				dataH[10] =rs.rows.item(i).lat;
                dataH[11] =rs.rows.item(i).lon;
                dataH[12] =rs.rows.item(i).alt;
                dataH[13] =rs.rows.item(i).acr;  
                dataH[14] =sessionStorage.imei;  
          }; 
        headerSPB=dataH;  
		
		var str='SELECT * FROM kebun_spbtkbm where nospb="'+nomorTransaksi+'"';
        tx.executeSql(str, [], function(tx, rs){  
          var dataT=new Array();
          for(var i=0; i<rs.rows.length; i++) {
              dataT[i]=new Array(4);
			  if(i>0){
                  dataT[i][0] ='####'+rs.rows.item(i).nospb;
              }else{
                  dataT[i][0] =rs.rows.item(i).nospb;
              }    
			  
                dataT[i][1] =rs.rows.item(i).karyawanid;
                dataT[i][2] =rs.rows.item(i).namakaryawan;
                dataT[i][3] =rs.rows.item(i).jumlahjjg;
                dataT[i][4] =rs.rows.item(i).field1;// kode tambahan
          }; 
        tkbmSPB=dataT;  
		
		param='method=transaction&tipeData=spb&datatransfer=datautama&username='+sessionStorage.username+'&password='+sessionStorage.password+
              '&dtHeader='+headerSPB+'&dtTkbm='+tkbmSPB+'&uuid='+sessionStorage.imei; 
		post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
		
		
        }, function(tx,error){
          errorHandler(tx,error);
        });
		
        }, function(tx,error){
          errorHandler(tx,error);
        });
	},null,null); 
  

	function respog(){
		if(con.readyState==4)
		  {
			if (con.status == 200) {
				console.log(con.responseText);
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
					hideProgress();
				}else {
					try{
						arr = JSON.parse(con.responseText);
						serverno = arr.serverno;
						notransaksi = arr.notransaksi;
						tanggal = arr.tanggal;
						afdeling = arr.afdeling;
						error = arr.err;
						
						if(error.err == "false"){
							 var detailD=new Array();
								db.transaction(function (tx) {
								  var str='SELECT * FROM kebun_spbdt where nospb="'+notransaksi+'"';
									tx.executeSql(str, [], function(tx, rs){  
									   var detailF=new Array();
									  for(var i=0; i<rs.rows.length; i++) {
											detailD[i]=new Array();
											detailD[i][0] =rs.rows.item(i).blok;
											detailD[i][1] =rs.rows.item(i).jjg;
											detailD[i][2] =rs.rows.item(i).brondolan;
											detailD[i][3] =rs.rows.item(i).mentah;
											detailD[i][4] =rs.rows.item(i).busuk;
											detailD[i][5] =rs.rows.item(i).matang;
											detailD[i][6] =rs.rows.item(i).lewatmatang;
											detailD[i][7] =rs.rows.item(i).nospbref;
											detailD[i][8] =rs.rows.item(i).rotasi;
											detailD[i][9] =rs.rows.item(i).nik;
											detailD[i][10] =rs.rows.item(i).tglpanen;
											detailD[i][11] =rs.rows.item(i).kodemandor;
											detailD[i][12] =rs.rows.item(i).nikmandor;
											detailD[i][13] =rs.rows.item(i).notransaksipanen;
									  }; 
									  syndetailspb(notransaksi,serverno,tanggal,afdeling,detailD,0);
									}, function(tx,error){
									  errorHandler(tx,error);
									});
							  },null,null);
						}else{
							hideProgress();
							console.log(con.responseText);
							if(error.mssg){
								notifAlert(error.mssg);
							}
						}
					}catch(e){
						hideProgress();
						console.log(con.responseText);
						notifAlert("Syn Data Transaksi : error result Array Transaction header respond","{error}");
					}
					  
					
				}
			}else{
				hideProgress();
				error_catch(con.status);
			}
		}     
	}
  
}

function syndetailspb(notransaksi,serverno,tanggal,afd,data,num){
	var datadetail ="";
	limit = 50;
	forloop = (num + limit);
	if(forloop >= data.length){
		forloop = data.length;
	}
	var blok = new Array();
	var jjg = new Array();
	var brondolan = new Array();
	var mentah = new Array();
	var busuk = new Array();
	var matang = new Array();
	var lewatmatang = new Array();
	var nospbref = new Array();
	var rotasi = new Array();
	var nik = new Array();
	var tglpanen = new Array();
	var kodemandor = new Array();
	var nikmandor = new Array();
	var notransaksipanen = new Array();
	var urut = 0;
	for(var x=num; x<forloop; x++){
		blok[urut] = data[x][0];
		jjg[urut] = data[x][1];
		brondolan[urut] = data[x][2];
		mentah[urut] = data[x][3];
		busuk[urut] = data[x][4];
		matang[urut] = data[x][5];
		lewatmatang[urut] = data[x][6];
		nospbref[urut] = data[x][7];
		rotasi[urut] = data[x][8];
		nik[urut] = data[x][9];
		tglpanen[urut] = data[x][10];
		kodemandor[urut] = data[x][11];
		nikmandor[urut] = data[x][12];
		notransaksipanen[urut] = data[x][13];
		urut++;
	}
	datadetail += '&kodeorg='+sessionStorage.kebun;
	datadetail += '&afdeling='+afd;
	datadetail += "&tanggal="+tanggal;
	datadetail += "&blok="+blok;
	datadetail += "&jjg="+jjg;
	datadetail += "&brondolan="+brondolan;
	datadetail += "&mentah="+mentah;
	datadetail += "&busuk="+busuk;
	datadetail += "&matang="+matang;
	datadetail += "&lewatmatang="+lewatmatang;
	datadetail += "&nospbref="+nospbref;
	datadetail += "&rotasi="+rotasi;
	datadetail += "&nik="+nik;
	datadetail += "&tglpanen="+tglpanen;
	datadetail += "&kodemandor="+tglpanen;
	datadetail += "&tglpanen="+tglpanen;
	datadetail += "&kodemandor="+kodemandor;
	datadetail += "&nikmandor="+nikmandor;
	datadetail += "&notransaksipanen="+notransaksipanen;
	

	param='method=transaction&tipeData=spb&datatransfer=datadetailspb&username='+sessionStorage.username+'&password='+sessionStorage.password+
	'&notransaksi='+notransaksi+'&serverno='+serverno+'&uuid='+sessionStorage.imei+datadetail; 
	if(data.length>0){
		post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
	}else{
		hideProgress();
		mssg = translateScript("{tidakmemilikidata}");
		notifAlert(mssg);
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200) {
				console.log(con.responseText);
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
					hideProgress();
				}else {
					  try{
						arr = JSON.parse(con.responseText);
						serverno = arr.serverno;
						notransaksi = arr.notransaksi;
						tanggal = arr.tanggal;
						afdeling = arr.afdeling;
						error = arr.err;
						if(error.err == "false"){
							if(forloop < data.length){
								syndetailspb(notransaksi,serverno,tanggal,afdeling,data,forloop);
							}else{
								selesaisynspb(notransaksi,serverno);
							}
						}else{
							hideProgress();
							console.log(con.responseText);
							if(error.mssg){
								notifAlert(error.mssg);
							}
						}
					}catch(e){
						hideProgress();
						console.log(con.responseText);
						notifAlert("Syn Data Transaksi : error result Array Transaction detail respond","{error}");
					}	
				}
			}else{
				hideProgress();
				error_catch(con.status);
			}
		}     
	}     
  
}
function selesaisynspb(notransaksi,serverno){
	hideProgress();
	db.transaction(function (tx) {
		  var str='update kebun_spbht set synchronized="'+serverno+'" where nospb="'+notransaksi+'"';
			tx.executeSql(str, [], function(tx, rs){  
				loadTransact();
				if(document.getElementById('dataSPB')){
					tampilkanListSpb();
				}
				notifAlert('{berhasil}','{pesan}');
				var updatelastnumber='update data_lastnospb set lastnospb="'+notransaksi+'"';
				tx.executeSql(updatelastnumber, [], function(tx, rs){}, function(tx,error){});
				deleteData('SPB');
				tryUploadPhoto(notransaksi); //Upload tidak di pakai untuk MRI
			}, function(tx,error){
			  notifAlert('{gagal}','{pesan}'); 
			  errorHandler(tx,error);
			  hideProgress();
			});
	  },null,null);    
}
function tryUploadPhoto(nomorTransaksi){
    //get File on spb
    db.transaction(function (tx) {
      var str='SELECT * FROM kebun_spbht where nospb="'+nomorTransaksi+'"';
        tx.executeSql(str, [], function(tx, rs){  
           var detailF=new Array();
           var nFile=new Array();
          for(var i=0; i<rs.rows.length; i++) {
                  detailF[i]=rs.rows.item(i).spbfile;//BLOB image DB
                  nFile[i]=rs.rows.item(i).nospb;
          };
          if(rs.rows.length>0 ){
            FileToUpload=detailF;
            NewFilename=nFile;
            uploadCounter=0;
			if(FileToUpload.length>0){
				sendImage(FileToUpload[uploadCounter],NewFilename[uploadCounter]);
			}
			synGpsLocation();
          }else{
            notifAlert('{berhasil}','{pesan}');
            hideProgress();
            synGpsLocation();
          }
        }, function(tx,error){
          errorHandler(tx,error);
        });
  },null,null); 
   

}
//upload Files:
//var FileToUpload=new Array();
//var NewFilename=new Array();
var uploadCounter;
function sendImage(dataUri,Filename){
	if(dataUri !== ""){
		tipeGambar = "jpeg";
		param='method=transaction&tipeData=images&username='+sessionStorage.username+'&password='+sessionStorage.password+
				  '&dtImage='+dataUri+'&filename='+Filename+'&tipeGambar='+tipeGambar+'&uuid='+sessionStorage.imei;
		post_response_textGPS(sessionStorage.server+'/owlMobile.php', param, respog);  
		function respog(){
		  if(con.readyState==4){
			  if (con.status == 200) {
				  if (!isSaveResponse(con.responseText)) {
					  alert('ERROR TRANSACTION,\n' + con.responseText);
					  hideProgress();
				  }else{
					notifAlert('{berhasil}','{pesan}');
					hideProgress();
					synGpsLocation();
				  }
			  }else{
				  console.log(con.responseText);
				  error_catch(con.status);
			  }
			}     
		}
	}
}
function sendImage_old(file,newFilename){
  var ext=file.split(".");
  var tipeGambar='jpg';
  if(ext[1]){
    ext[1]=ext[1].toLowerCase();
    if(ext[1]=='png'){
      tipeGambar='png';
    }else if(ext[1]=='bmp'){
      tipeGambar='bmp';
    }else if(ext[1]=='gif'){
      tipeGambar='gif';
    }

    function getDataUri(url, callback) {
        var image = new Image();

        image.onload = function () {
            var canvas = document.createElement('canvas');
            canvas.width = this.naturalWidth; // or 'width' if you want a special/scaled size
            canvas.height = this.naturalHeight; // or 'height' if you want a special/scaled size
            canvas.getContext('2d').drawImage(this, 0, 0);

            // Get raw image data cleanned.
               if(tipeGambar=='jpg'){
                //1.0 means 100% of images, if you want the size 50% then 0.5
                //callback(canvas.toDataURL('image/jpeg',1.0).replace(/^data:image\/(png|jpg|bmp|gif);base64,/, ''));
                callback(canvas.toDataURL('image/jpeg',1.0));
              }else{
                //callback(canvas.toDataURL('image/'+tipeGambar,1.0).replace(/^data:image\/(png|jpg|bmp|gif);base64,/, ''));
                callback(canvas.toDataURL('image/'+tipeGambar,1.0));
              }
            // ... or get as Data URI
            //callback(canvas.toDataURL('image/png',1.0));
        };

        image.src = url;
    }

    // Usage
    getDataUri(file, function(dataUri) {
          param='method=transaction&tipeData=images&username='+sessionStorage.username+'&password='+sessionStorage.password+
              '&dtImage='+dataUri+'&filename='+newFilename+'&tipeGambar='+tipeGambar+'&uuid='+sessionStorage.imei;
          post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);        
    });
    function respog(){
      if(con.readyState==4)
        {
          if (con.status == 200) {
              if (!isSaveResponse(con.responseText)) {
                  alert('ERROR TRANSACTION,\n' + con.responseText);
                  hideProgress();
              }
              else {
                  uploadCounter+=1;                
                  if(uploadCounter<FileToUpload.length){
                    sendImage(FileToUpload[uploadCounter],NewFilename[uploadCounter]);
                  }else{
                    hideProgress();
                    loadTransact();
                    synGpsLocation();
                    hideProgress();
                    alert(translateScript('{berhasil}'));
                  }
              }
          }
          else {
              error_catch(con.status);
          }
        }     
    }     
  }else{
      uploadCounter+=1;                
      if(uploadCounter<FileToUpload.length){
        sendImage(FileToUpload[uploadCounter],NewFilename[uploadCounter]);
      }else{
        hideProgress();
        loadTransact();
        synGpsLocation();
        hideProgress();
        alert(translateScript('{berhasil}'));
      }
  } 
}

function syn_proses_status(nomorTransaksi){
	var prosess_syn = "true";
	db.transaction(function (tx){
		syn_proses_status=	'CREATE TABLE IF NOT EXISTS syn_proses_status(notransaksi TEXT,nourut INT,namatransaksi TEXT,namafunction TEXT,param TEXT,status INT)';
		tx.executeSql(syn_proses_status, [], null, function(tx,error){errorHandler(tx,error);});
		var str='SELECT * FROM syn_proses_status where notransaksi="'+nomorTransaksi+'" and status <> "1" order by nourut asc limit 1';
		tx.executeSql(str, [], function(tx, rs){
			if(rs.rows.length > 0 ){
				for(var i=0; i<rs.rows.length; i++){
					prosess_syn = rs.rows.item(i).namafunction+"("+rs.rows.item(i).param+")";
				}
			}else{
				prosess_syn = "true";
			}
			return prosess_syn;
		}, function(tx,error){errorHandler(tx,error);});
	},null,null); 
}
function execSyn_proses(action,nomorTransaksi,nourut,status,namaProses,namafunct,param){
	switch(action.toLowerCase()){
		case 'insert':
			db.transaction(function (tx){
				syn_proses_status=	'CREATE TABLE IF NOT EXISTS syn_proses_status(notransaksi TEXT,nourut INT,namatransaksi TEXT,namafunction TEXT,param TEXT,status INT);';
				tx.executeSql(syn_proses_status, [], null, function(tx,error){errorHandler(tx,error);});
				syn_proses_check='SELECT * FROM syn_proses_status where notransaksi= "'+nomorTransaksi+'";';
				tx.executeSql(syn_proses_check, [], function(tx, rs){
					console.log(rs.rows.length);
					if(rs.rows.length <= 0){
						InsertSynStat = "INSERT INTO syn_proses_status (notransaksi,nourut,namatransaksi,namafunction,param,status) values "+
						" ('"+nomorTransaksi+"','"+nourut+"','"+namaProses+"','"+namafunct+"','"+namafunct+"','"+status+"'); ";
						tx.executeSql(InsertSynStat, [], null, function(tx,error){errorHandler(tx,error);});
					}
				}, function(tx,error){errorHandler(tx,error);});
			},null,null); 
		break;
		case 'update':
			db.transaction(function (tx){
				updateSynStat = "update syn_proses_status set status = '"+status+"' "+
				" where notransaksi = '"+nomorTransaksi+"' and nourut = '"+nourut+"' ";
				tx.executeSql(updateSynStat, [], null, function(tx,error){errorHandler(tx,error);});
			},null,null); 
		break;
	}
}
function synPanen(tanggalTrx,nomorTransaksi,flagTrx){
	console.log("synPanen");
	var datapanen='';
	var panendt = new Array();
	db.transaction(function (tx){
		//proses
		//execSyn_proses('insert',nomorTransaksi,'1','0','Syn Panen Proses 1','synPanen',tanggalTrx+','+nomorTransaksi);
		if(flagTrx != "0"){
			whereheader = " and verify <> '0' ";// list verify
		}else{
			whereheader = " and verify = '0' ";// list panen
		}
		var str='SELECT * FROM kebun_panen where notransaksi="'+nomorTransaksi+'" '+whereheader+' limit 1';
        tx.executeSql(str, [], function(tx, rs)
		{
			var strData='';
			for(var i=0; i<rs.rows.length; i++){
				strData+='&notransaksi='+rs.rows.item(i).notransaksi;
				strData+='&tanggal='+rs.rows.item(i).tanggal;
				strData+='&nobkm='+rs.rows.item(i).nobkm;
				strData+='&kodeorg='+sessionStorage.kebun;
				strData+='&nikmandor='+rs.rows.item(i).nikmandor;
				strData+='&nikmandor1='+rs.rows.item(i).nikmandor1;
				strData+='&nikasisten='+rs.rows.item(i).nikasisten;
				strData+='&kerani='+rs.rows.item(i).kerani;
				strData+='&updateby='+rs.rows.item(i).updateby;
				strData+='&lastupdate='+rs.rows.item(i).lastupdate;
				strData+='&verify='+rs.rows.item(i).verify;
				strData+='&deviceid='+sessionStorage.imei;
				datapanen+=strData; 
				if(rs.rows.item(i).verify != "0"){
					var notranfordetail = rs.rows.item(i).verify;// list verify
				}else{
					var notranfordetail = rs.rows.item(i).notransaksi// list detail
				}
					
				// gerdang
				var str='SELECT * FROM kebun_gerdang where notransaksi="'+notranfordetail+'"';
				var strData='';
				var nik_gerdang = new Array();
				var gerdang_gerdang = new Array();
				tx.executeSql(str, [], function(tx, rs){
					for(var i=0; i<rs.rows.length; i++){
						nik_gerdang[i]= rs.rows.item(i).nik;
						gerdang_gerdang[i]= rs.rows.item(i).gerdang;
					}; 
					strData+='&nik_gerdang='+nik_gerdang;
					strData+='&gerdang_gerdang='+gerdang_gerdang;
					datapanen+=strData;
					
					param='method=transaction&tipeData=panen&datatransfer=datautama&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei+'&uuid='+sessionStorage.imei;
					
					
					var str='SELECT * FROM kebun_panendt where notransaksi="'+notranfordetail+'"';
					tx.executeSql(str, [], function(tx, rs){
						for(var i=0; i<rs.rows.length; i++){
							panendt[i] = new Array();
							panendt[i]['nik'] = rs.rows.item(i).nik;
							panendt[i]['blok'] = rs.rows.item(i).blok;
							panendt[i]['rotasi'] = rs.rows.item(i).rotasi;
							panendt[i]['tahuntanam'] = rs.rows.item(i).tahuntanam;
							panendt[i]['jjgpanen'] = rs.rows.item(i).jjgpanen;
							panendt[i]['luaspanen'] = rs.rows.item(i).luaspanen;
							panendt[i]['brondolanpanen'] = rs.rows.item(i).brondolanpanen;
							panendt[i]['status'] = rs.rows.item(i).status;
							panendt[i]['lat'] = rs.rows.item(i).lat;
							panendt[i]['long'] = rs.rows.item(i).long;
							panendt[i]['cetakan'] = rs.rows.item(i).cetakan;
						};
						param += datapanen;
						if(rs.rows.length>0){
							post_response_text(sessionStorage.server+'/owlMobile.php', param, respogsynPanen);
						}else{
							mssg = translateScript("{tidakmemilikidata}");
							notifAlert(mssg);
						}
					}, function(tx,error){errorHandler(tx,error);});
					
				}, function(tx,error){errorHandler(tx,error);});
			};
        }, function(tx,error){errorHandler(tx,error);});
	},null,null); 
	
	function respogsynPanen(){
		hideProgress();
		if(con.readyState==4){
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
					console.log(con.responseText);
				}else{
					
					try{
						arr = JSON.parse(con.responseText);
						notransaksi = arr.notransaksi;
						tanggal = arr.tanggal;
						verify = arr.verify;
						noref = arr.noref;
						error = arr.err;
						
						if(error.err == "false"){
							//execSyn_proses('update',noref,'1','1');
							console.log("Syn data panen");
							insert_panendetail(notransaksi,noref,verify,tanggal,panendt,0);
						}else{
							if(error.mssg){
								notifAlert(error.mssg);
							}
						}
					}catch(e){
						console.log(con.responseText);
						notifAlert("Syn Data Transaksi : error result Array Transaction header respond","{error}");
					}
				}
			}
			else 
			{
				error_catch(con.status);
			}
		}     
	}
	
}
function insert_panendetail(notransaksi,noref,verify,tanggal,panendt,num){
	var strData='';
	var nik = new Array();
	var blok = new Array();
	var rotasi = new Array();
	var tahuntanam = new Array();
	var jjgpanen = new Array();
	var luaspanen = new Array();
	var brondolanpanen = new Array();
	var status = new Array();
	var latitude = new Array();
	var longitude = new Array();
	var cetakan = new Array();
	limit = 50;
	forloop = (num + limit);
	if(forloop >= panendt.length){
		forloop = panendt.length;
	}
	var urut = 0;
	for(var i=num; i<forloop; i++){
		
		nik[urut] = panendt[i]['nik'];
		blok[urut] = panendt[i]['blok'];
		rotasi[urut] = panendt[i]['rotasi'];
		tahuntanam[urut] = panendt[i]['tahuntanam'];
		jjgpanen[urut] = panendt[i]['jjgpanen'];
		luaspanen[urut] = panendt[i]['luaspanen'];
		brondolanpanen[urut] = panendt[i]['brondolanpanen'];
		status[urut] = panendt[i]['status'];
		latitude[urut] = panendt[i]['lat'];
		longitude[urut] = panendt[i]['long'];
		cetakan[urut] = panendt[i]['cetakan'];
		urut++;
	}; 
	strData+='&notransaksi='+notransaksi;
	strData+='&noref='+noref;
	strData+='&verify='+verify;
	strData+='&kodeorg='+sessionStorage.kebun;
	strData+='&tanggal='+tanggal;
	strData+='&nik='+nik;
	strData+='&blok='+blok;
	strData+='&sesi='+rotasi;
	strData+='&tahuntanam='+tahuntanam;
	strData+='&jjgpanen='+jjgpanen;
	strData+='&luaspanen='+luaspanen;
	strData+='&brondolanpanen='+brondolanpanen;
	strData+='&status='+status;
	strData+='&lat='+latitude;
	strData+='&long='+longitude;
	strData+='&cetakan='+cetakan;
			
	param='method=transaction&tipeData=panen&datatransfer=datadetail&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei;
	param += strData;
	
	if(panendt.length>0){
		post_response_text(sessionStorage.server+'/owlMobile.php', param, respoginsert_panendetail);
	}else{
		mssg = translateScript("{tidakmemilikidata}");
		notifAlert(mssg);
	}
		
	function respoginsert_panendetail(){
		hideProgress();
		if(con.readyState==4){
			if (con.status == 200){
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
					console.log(con.responseText);
				}else{
					console.log("Respon data detail panen");	
					try{
						arr = JSON.parse(con.responseText);
						det_notransaksi = arr.notransaksi;
						det_tanggal = arr.tanggal;
						det_verify = arr.verify;
						det_noref = arr.noref;
						det_error = arr.err;
						
						if(det_error.err == "false"){
							if(forloop < panendt.length){
								insert_panendetail(det_notransaksi,det_noref,det_verify,det_tanggal,panendt,forloop);
							}else{
								//notifAlert("Success!");
								optional_panen(det_notransaksi,det_noref,det_verify,det_tanggal);
							}
						}else{
							if(det_error.mssg){
								notifAlert(det_error.mssg);
							}
						}
					}catch(e){
						console.log(con.responseText);
						notifAlert("Syn Data Transaksi : error result Array Transaction data panen"+e,"{error}");
					}
				}
			}else{
				error_catch(con.status);
			}
		}     
	}	
}
function optional_panen(notransaksi,noref,verify,tanggal){
	console.log("optional_panen");
	//execSyn_proses('insert',nomorTransaksi,'2','0','Syn Panen Proses 2','insert_optional_panen',notransaksi+','+noref);
	if(verify != "0"){
		notransfordetail = verify;
	}else{
		notransfordetail = noref;
	}
	//console.log(notransfordetail);
	var optionaldata = new Array();
	var urut = 0;
	//var strData='method=transaction&tipeData=panen&datatransfer=dataoptional&username='+sessionStorage.username+'&password='+sessionStorage.password+'&notransaksi='+notransaksi+'&noref='+noref+'&verify='+verify+'&uuid='+sessionStorage.imei;
	db.transaction(function (tx) {
		query='SELECT * FROM kebun_grading where notransaksi="'+notransfordetail+'"';
		tx.executeSql(query, [], function(tx, rs){
			if(rs.rows.length > 0){
				for(var i=0; i<rs.rows.length; i++){
					optionaldata[urut] = new Array();
					optionaldata[urut]['blok_grading'] = rs.rows.item(i).blok;
					optionaldata[urut]['nik_grading'] = rs.rows.item(i).nik;
					optionaldata[urut]['sesi_grading'] = rs.rows.item(i).rotasi;
					optionaldata[urut]['kode_grading'] = rs.rows.item(i).kodegrading;
					optionaldata[urut]['jumlah_grading'] = rs.rows.item(i).jml;
					urut++;
				}
			}
			query='SELECT * FROM kebun_kondisi_buah where notransaksi="'+notransfordetail+'"';
			tx.executeSql(query, [], function(tx, rs){
				if(rs.rows.length > 0){
					for(var i=0; i<rs.rows.length; i++){
						optionaldata[urut] = new Array();
						optionaldata[urut]['blok_hama'] = rs.rows.item(i).blok;
						optionaldata[urut]['nik_hama'] = rs.rows.item(i).nik;
						optionaldata[urut]['sesi_hama'] = rs.rows.item(i).rotasi;
						optionaldata[urut]['kode_hama'] = rs.rows.item(i).kodehama;
						optionaldata[urut]['jumlah_hama'] = rs.rows.item(i).jml;
						urut++;
					}
				}
				insert_optional_panen(notransaksi,noref,verify,tanggal,optionaldata,0);
				/*
				query='SELECT * FROM kebun_mutu where notransaksi="'+notransfordetail+'"';
				tx.executeSql(query, [], function(tx, rs){
					if(rs.rows.length > 0){
						for(var i=0; i<rs.rows.length; i++){
							optionaldata[urut] = new Array();
							optionaldata[urut]['blok_mutu'] = rs.rows.item(i).blok;
							optionaldata[urut]['nik_mutu'] = rs.rows.item(i).nik;
							optionaldata[urut]['sesi_mutu'] = rs.rows.item(i).rotasi;
							optionaldata[urut]['nourut_mutu'] = rs.rows.item(i).nourut;
							optionaldata[urut]['kode_mutu'] = rs.rows.item(i).kodemutu;
							optionaldata[urut]['jumlah_mutu'] = rs.rows.item(i).jml;
							urut++;
						}
					}
					
				}, function(tx,error){errorHandler(tx,error);});*/
			}, function(tx,error){errorHandler(tx,error);});
		}, function(tx,error){errorHandler(tx,error);});
	},null,null);
	
}
function insert_optional_panen(notransaksi,noref,verify,tanggal,optionaldata,num){
	var strData='';
	
	var blok_grading = new Array();
	var nik_grading= new Array();
	var kode_grading= new Array();
	var sesi_grading= new Array();
	var jumlah_grading = new Array();
	var blok_hama= new Array();
	var kode_hama= new Array();
	var nik_hama= new Array();
	var sesi_hama= new Array();
	var jumlah_hama = new Array();
	var blok_mutu= new Array();
	var nik_mutu= new Array();
	var sesi_mutu= new Array();
	var nourut_mutu= new Array();
	var kode_mutu= new Array();
	var jumlah_mutu = new Array();
	limit = 50;
	forloop = (num + limit);
	if(forloop >= optionaldata.length){
		forloop = optionaldata.length;
	}
	var urut = 0;
	var urut1 = 0;
	var urut2 = 0;
	for(var i=num; i<forloop; i++){
		if(typeof optionaldata[i]['blok_grading'] !== "undefined"){
			blok_grading[urut] = optionaldata[i]['blok_grading'];
			nik_grading[urut] = optionaldata[i]['nik_grading'];
			kode_grading[urut] = optionaldata[i]['kode_grading'];
			sesi_grading[urut] = optionaldata[i]['sesi_grading'];
			jumlah_grading[urut] = optionaldata[i]['jumlah_grading'];
			urut++;
		}
		if(typeof optionaldata[i]['blok_hama'] !== "undefined"){
			blok_hama[urut1] = optionaldata[i]['blok_hama'];
			kode_hama[urut1] = optionaldata[i]['kode_hama'];
			nik_hama[urut1] = optionaldata[i]['nik_hama'];
			sesi_hama[urut1] = optionaldata[i]['sesi_hama'];
			jumlah_hama[urut1] = optionaldata[i]['jumlah_hama'];
			urut1++;
		}
		/*
		if(typeof optionaldata[i]['blok_mutu'] !== "undefined"){
			blok_mutu[urut2] = optionaldata[i]['blok_mutu'];
			nik_mutu[urut2] = optionaldata[i]['nik_mutu'];
			sesi_mutu[urut2] = optionaldata[i]['sesi_mutu'];
			nourut_mutu[urut2] = optionaldata[i]['nourut_mutu'];
			kode_mutu[urut2] = optionaldata[i]['kode_mutu'];
			jumlah_mutu[urut2] = optionaldata[i]['jumlah_mutu'];
			urut2++;
		}*/
		
	}; 
	
	strData+='&notransaksi='+notransaksi;
	strData+='&noref='+noref;
	strData+='&verify='+verify;
	strData+='&kodeorg='+sessionStorage.kebun;
	strData+='&tanggal='+tanggal;
	
	strData+='&blok_grading='+blok_grading;
	strData+='&nik_grading='+nik_grading;
	strData+='&kode_grading='+kode_grading;
	strData+='&sesi_grading='+sesi_grading;
	strData+='&jumlah_grading='+jumlah_grading;
	
	strData+='&blok_hama='+blok_hama;
	strData+='&kode_hama='+kode_hama;
	strData+='&nik_hama='+nik_hama;
	strData+='&sesi_hama='+sesi_hama;
	strData+='&jumlah_hama='+jumlah_hama;
	/*
	strData+='&blok_mutu='+blok_mutu;
	strData+='&nik_mutu='+nik_mutu;
	strData+='&sesi_mutu='+sesi_mutu;
	strData+='&nourut_mutu='+nourut_mutu;
	strData+='&kode_mutu='+kode_mutu;
	strData+='&jumlah_mutu='+jumlah_mutu;
	*/
	param='method=transaction&tipeData=panen&datatransfer=dataoptional&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei;
	param += strData;
	console.log(param);
	post_response_text(sessionStorage.server+'/owlMobile.php', param, respoginsert_optional_panen);
	function respoginsert_optional_panen(){
		hideProgress();
		if(con.readyState==4){
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					try{
						arr = JSON.parse(con.responseText);
						notransaksi = arr.notransaksi;
						verify = arr.verify;
						noref = arr.noref;
						error = arr.err;
						if(error.err == "false"){
							if(forloop < optionaldata.length){
								insert_optional_panen(notransaksi,noref,verify,tanggal,optionaldata,forloop);
							}else{
								proporsiupah(notransaksi,noref,verify,tanggal);
							}
						}else{
							if(error.mssg){
								notifAlert(error.mssg);
							}
						}
					}catch(e){
						console.log(con.responseText);
						notifAlert("Syn Data Transaksi : error result Array detail","{error}");
					}
				}
			}else {
				error_catch(con.status);
			}
		}     
	}
}
function proporsiupah(notransaksi,noref,verify,tanggal){
	console.log("proporsiupah");
	var strData="";
	strData+='&notransaksi='+notransaksi;
	strData+='&noref='+noref;
	strData+='&verify='+verify;
	strData+='&kodeorg='+sessionStorage.kebun;
	strData+='&tanggal='+tanggal;
	param='method=transaction&tipeData=panen&datatransfer=proporsiupah&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei;
	param += strData;
	post_response_text(sessionStorage.server+'/owlMobile.php', param, respogproporsiupah);
	
	function respogproporsiupah(){
		hideProgress();
		if(con.readyState==4){
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					try{
						arr = JSON.parse(con.responseText);
						notransaksi = arr.notransaksi;
						verify = arr.verify;
						noref = arr.noref;
						error = arr.err;
						if(error.err == "false"){
							if(typeof arr.lanjut != "undefined" && arr.lanjut == "true"){
								insert_image_panen(notransaksi,noref,verify,tanggal);
							}else{
								doneFlagSynPanen(notransaksi,noref,verify,tanggal);
							}
						}else{
							if(error.mssg){
								notifAlert(error.mssg);
							}
						}
					}catch(e){
						console.log(con.responseText);
						notifAlert("Syn Data Transaksi : error result Array detail","{error}");
					}
				}
			}else {
				error_catch(con.status);
			}
		}     
	}
}

function selesaiSynPanen(notransaksi,noref,verify,tanggal){
	if(verify != "0"){
		where  = ' and verify = "'+verify+'"';
	}else{
		where  = ' and verify = "0"';
	}
	db.transaction(function (tx){
		var str='update kebun_panen set synchronized="'+notransaksi+'" where notransaksi="'+noref+'"'+where;
		console.log(str);
		tx.executeSql(str, [], 
		function(tx, rs){
			notifAlert('{berhasil}','{pesan}');
			if(verify != "0"){
				flag  = '1';
			}else{
				flag  = '0';
			}
			loadTransact(tanggal,'panen','',flag);
			if(document.getElementById('panelPanen')){
				if(typeof tampilkanListPanen === 'function'){
					tampilkanListPanen();
				}
			}
			if(document.getElementById('panelPanenChecker')){
				if(typeof tampilkanListPanenChecker === 'function'){
					tampilkanListPanenChecker();
				}
			}
		},function(tx,error){
			notifAlert(translateScript('{gagal}')); 
			errorHandler(tx,error);
		});
	},null,null);
	synGpsLocation();
	deleteData('PNN');	
}

function insert_image_panen(notransaksi,noref,verify,tanggal){
	db.transaction(function (tx) {
		var str='SELECT foto FROM kebun_panendt where notransaksi="'+noref+'"';
		var strData=new Array();
		var d=new Array();
		tx.executeSql(str, [], function(tx, rs){
			for(var i=0; i<rs.rows.length; i++){
				d['filename'] = rs.rows.item(i).blok+rs.rows.item(i).nik+rs.rows.item(i).sesi;
				d['tipegambar'] = "jpeg";
				d['foto'] = rs.rows.item(i).foto;
				strData.push(d);
			}; 
			execInsert_image_panen(notransaksi,noref,verify,tanggal,strData,0);
		}, function(tx,error){errorHandler(tx,error);});
	},null,null);
}
function execInsert_image_panen(notransaksi,noref,verify,tanggal,strData,num){
	limit = 1;
	forloop = (num + limit);
	if(forloop >= strData.length){
		forloop = strData.length;
	}
	var urut = 0;
	var foto = "";
	foto+='&notransaksi='+notransaksi;
	foto+='&noref='+noref;
	foto+='&verify='+verify;
	foto+='&kodeorg='+sessionStorage.kebun;
	foto+='&tanggal='+tanggal;
	for(var i=num; i<forloop; i++){
		foto += "&filename="+strData[i]["filename"];
		foto += "&tipegambar="+strData[i]["tipegambar"];
		foto += "&foto="+strData[i]["foto"];
	}
	
	param='method=transaction&tipeData=panen&datatransfer=dataphoto&username='+sessionStorage.username+'&password='+sessionStorage.password+'&verify='+verify+'&uuid='+sessionStorage.imei;
	param+=foto;
	if(foto != ""){
		post_response_text(sessionStorage.server+'/owlMobile.php', param, responseUploadImage);
	}
	function responseUploadImage(){
		hideProgress();
		if(con.readyState==4){
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					try{
						arr = JSON.parse(con.responseText);
						notransaksi = arr.notransaksi;
						verify = arr.verify;
						noref = arr.noref;
						error = arr.err;
						if(error.err == "false"){
							if(forloop < strData.length){
								execInsert_image_panen(notransaksi,noref,verify,tanggal,strData,forloop);
							}else{
								doneFlagSynPanen(notransaksi,noref,verify,tanggal);
							}
						}else{
							if(error.mssg){
								notifAlert(error.mssg);
							}
						}
					}catch(e){
						notifAlert("Syn Data Transaksi : "+con.responseText,"{error}");
					}
				}
			}else {
				error_catch(con.status);
			}
		}     
	}
}
function doneFlagSynPanen(notransaksi,noref,verify,tanggal){
	console.log("syndone");
	var strData="";
	strData ='&notransaksi='+notransaksi;
	strData+='&noref='+noref;
	strData+='&verify='+verify;
	strData+='&kodeorg='+sessionStorage.kebun;
	strData+='&tanggal='+tanggal;
	param='method=transaction&tipeData=panen&datatransfer=syndone&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei;
	param += strData;
	post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
	
	function respog(){
		hideProgress();
		if(con.readyState==4){
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					try{
						arr = JSON.parse(con.responseText);
						notransaksi = arr.notransaksi;
						tanggal = arr.tanggal;
						verify = arr.verify;
						noref = arr.noref;
						error = arr.err;
						if(error.err == "false"){
							//execSyn_proses('update',noref,'2','1');
							selesaiSynPanen(notransaksi,noref,verify,tanggal);
						}else{
							if(error.mssg){
								notifAlert(error.mssg);
							}
						}
					}catch(e){
						console.log(con.responseText);
						notifAlert("Syn Data Transaksi : error result Array detail","{error}");
					}
				}
			}else {
				error_catch(con.status);
			}
		}     
	}
	
}
function upload_signature_panen(notransaksi,serverNo){
	function respog(){
		hideProgress();
		if(con.readyState==4){
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					arTex=con.responseText.split('|');
					notransaksi=arTex[0];
					serverNo=arTex[1];
                    db.transaction(function (tx) 
					{
						//serverNo = "";
						var str='update kebun_panen set synchronized="'+serverNo+'" where notransaksi="'+notransaksi+'"';
                       tx.executeSql(str, [], 
						function(tx, rs)
						{
							alert(translateScript('{berhasil}'));
							loadTransact();
							if(document.getElementById('panelPanen')){
								if(typeof tampilkanListPanen === 'function'){
									tampilkanListPanen();
								}
							}
                        }, 
						function(tx,error)
						{
							alert(translateScript('{gagal}')); 
							errorHandler(tx,error);
                        });
						
					},null,null);
				}
			}else {
				error_catch(con.status);
			}
		}     
	}
	
	var str='SELECT * FROM kebun_bkmsign where notransaksi="'+notransaksi+'"';
	var strData='&notransaksi='+notransaksi+'&serverNo='+serverNo;
	 db.transaction(function (tx) {
		tx.executeSql(str, [], function(tx, rs){
			for(var i=0; i<rs.rows.length; i++){
				if(rs.rows.item(i).ttd1 != ""){
					strData+='&nama[]='+notransaksi+'_gangleader';
					strData+='&filettd[]='+rs.rows.item(i).ttd1;
					strData+='&keterangan[]=Gang Leader';
				}
				if(rs.rows.item(i).ttd2 != ""){
					strData+='&nama[]='+notransaksi+'_conductor';
					strData+='&filettd[]='+rs.rows.item(i).ttd2;
					strData+='&keterangan[]=Conductor';
				}
				if(rs.rows.item(i).ttd3 != ""){
					strData+='&nama[]='+notransaksi+'_manager';
					strData+='&filettd[]='+rs.rows.item(i).ttd3;
					strData+='&keterangan[]=Manager/Head Division';
				}
			}; 
			param='method=transaction&tipeData=panenimg&username='+sessionStorage.username+'&password='+sessionStorage.password+'&uuid='+sessionStorage.imei;
			param+=strData;
			if(rs.rows.length>0){
				post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
			}else{
				mssg = translateScript("{tidakmemilikidata}");
				alert(mssg);
			}
		}, function(tx,error){errorHandler(tx,error);});
	},null,null);
	
}
function clearTime(){
	clearInterval(timeOutTracking);
}