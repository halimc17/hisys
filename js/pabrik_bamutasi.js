function saveht() {
	param='';
	tanggal= document.getElementById('tanggal').value;
	tanggalberangkat= document.getElementById('tanggalberangkat').value;
	unit= document.getElementById('unit').value;
	kodebarang= document.getElementById('kodebarang').value;
	tipe= document.getElementById('tipe').value;
	kodept= document.getElementById('kodept').value;
	if(tanggal==''){
		alert('Tanggal tidak boleh kosong');return;
	}
	if(unit==''){
		alert('Unit tidak boleh kosong');return;
	}
	if(kodebarang==''){
		alert('Komoditi tidak boleh kosong');return;
	}
	if(tipe==''){
		alert('Tipe tidak boleh kosong');return;
	}
	
	// Add Abdul
	// Add Validasi harus isi Tanggal berangkat
	// Dikarenakan untuk pembentukan jurnal, jika lupa mengisi maka jurnal 00000/SDKM/
	if(tanggalberangkat==''){
		alert('Tanggal berangkat tidak boleh kosong!');return;
	}
	// End Abdul

	method = 'notransaksi';
	param += '&unit=' + unit + '&tanggal=' + tanggal+ '&kodebarang=' + kodebarang+ '&tipe=' + tipe+ '&kodept=' + kodept;
	param += '&method=' + method;
	tujuan = 'pabrik_bamutasi_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('notransaksi').value=con.responseText;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('transportir').disabled=true;
					document.getElementById('namakapal').disabled=true;
					document.getElementById('namaponton').disabled=true;
					document.getElementById('keteranganht').disabled=true;
					document.getElementById('jmberangkat').disabled=true;
					document.getElementById('tanggalberangkat').disabled=true;
					document.getElementById('mnberangkat').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('kodebarang').disabled=true;
					document.getElementById('kodept').disabled=true;
					document.getElementById('jmtiba').disabled=true;
					document.getElementById('tanggaltiba').disabled=true;
					document.getElementById('mntiba').disabled=true;
						document.getElementById('tanggalbongkar1').disabled=true;
						document.getElementById('jmbongkar1').disabled=true;
						document.getElementById('mnbongkar1').disabled=true;
					document.getElementById('tanggalbongkar2').disabled=true;
					document.getElementById('jmbongkar2').disabled=true;
					document.getElementById('mnbongkar2').disabled=true;
					document.getElementById('tipe').disabled=true;
					document.getElementById('unitreferensi').disabled=true;
					
					document.getElementById('detail').style.display='block';
					document.getElementById('listdatadt').innerHTML='';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function cancelht(){
		
	document.getElementById('tanggal').disabled=false;
	document.getElementById('transportir').disabled=false;
	document.getElementById('namakapal').disabled=false;
	document.getElementById('namaponton').disabled=false;
	document.getElementById('keteranganht').disabled=false;
	document.getElementById('jmberangkat').disabled=false;
	document.getElementById('tanggalberangkat').disabled=false;
	document.getElementById('mnberangkat').disabled=false;
	document.getElementById('unit').disabled=false;
	document.getElementById('kodebarang').disabled=false;
	document.getElementById('kodept').disabled=false;
	document.getElementById('jmtiba').disabled=false;
	document.getElementById('tanggaltiba').disabled=false;
	document.getElementById('mntiba').disabled=false;
	document.getElementById('tanggalbongkar1').disabled=false;
	document.getElementById('jmbongkar1').disabled=false;
	document.getElementById('mnbongkar1').disabled=false;
	document.getElementById('tanggalbongkar2').disabled=false;
	document.getElementById('jmbongkar2').disabled=false;
	document.getElementById('mnbongkar2').disabled=false;
	document.getElementById('tipe').disabled=false;
	document.getElementById('saveht').disabled=false;
	document.getElementById('transportasi').disabled=false;
	document.getElementById('unitreferensi').disabled=false;
	
	
	document.getElementById('noreferensi').value='';
	document.getElementById('unitreferensi').value='';
	document.getElementById('notransaksi').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('transportir').value='';
	document.getElementById('namakapal').value='';
	document.getElementById('namaponton').value='';
	document.getElementById('keteranganht').value='';
	document.getElementById('jmberangkat').value='00';
	document.getElementById('tanggalberangkat').value='';
	document.getElementById('mnberangkat').value='00';
	document.getElementById('kodebarang').value='';
	document.getElementById('kodept').value='';
	document.getElementById('jmtiba').value='00';
	document.getElementById('tanggaltiba').value='';
	document.getElementById('mntiba').value='';
	document.getElementById('tanggalbongkar1').value='';
	document.getElementById('jmbongkar1').value='00';
	document.getElementById('mnbongkar1').value='00';
	document.getElementById('tanggalbongkar2').value='';
	document.getElementById('jmbongkar2').value='00';
	document.getElementById('mnbongkar2').value='00';
	document.getElementById('tipe').value='';

	document.getElementById('detail').style.display='none';
	document.getElementById('method').value ='insert';
}

function editht(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'pabrik_bamutasi_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('method').value = 'update';
					// alert(con.responseText.split);
					ar = con.responseText.split("###");
					
					document.getElementById('notransaksi').value = ar[0];
					document.getElementById('tanggal').value = ar[1];
					document.getElementById('tipe').value = ar[2];
					document.getElementById('namakapal').value = ar[3];
					document.getElementById('namaponton').value = ar[4];
					document.getElementById('keteranganht').value = ar[5];
					document.getElementById('tanggalberangkat').value = ar[6];
					document.getElementById('jmberangkat').value = ar[7];
					document.getElementById('mnberangkat').value = ar[8];
					document.getElementById('unit').value = ar[9];
					document.getElementById('kodebarang').value = ar[10];
					document.getElementById('transportir').value = ar[11];
					document.getElementById('transportasi').value = ar[12];
					
					
					document.getElementById('tanggaltiba').value = ar[13];
					document.getElementById('jmtiba').value = ar[14];
					document.getElementById('mntiba').value = ar[15];
					
					document.getElementById('tanggalbongkar1').value = ar[16];
					document.getElementById('jmbongkar1').value = ar[17];
					document.getElementById('mnbongkar1').value = ar[18];
					
					document.getElementById('tanggalbongkar2').value = ar[19];
					document.getElementById('jmbongkar2').value = ar[20];
					document.getElementById('mnbongkar2').value = ar[21];
					
					document.getElementById('kodept').value = ar[22];
					document.getElementById('unitreferensi').value = ar[23];
					document.getElementById('noreferensi').value = ar[24];

					document.getElementById('notransaksi').disabled=true;
					document.getElementById('tanggal').disabled=true;
					// document.getElementById('pelabuhantujuan').disabled=true;
					document.getElementById('transportir').disabled=true;
					document.getElementById('namakapal').disabled=true;
					document.getElementById('namaponton').disabled=true;
					document.getElementById('keteranganht').disabled=true;
					document.getElementById('jmberangkat').disabled=true;
					document.getElementById('tanggalberangkat').disabled=true;
					document.getElementById('mnberangkat').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('saveht').disabled=true;
					document.getElementById('kodebarang').disabled=true;
					document.getElementById('transportasi').disabled=true;
					document.getElementById('kodept').disabled=true;
					
					document.getElementById('tanggaltiba').disabled=true;
					document.getElementById('jmtiba').disabled=true;
					document.getElementById('mntiba').disabled=true;
					
					document.getElementById('tanggalbongkar1').disabled=true;
					document.getElementById('jmbongkar1').disabled=true;
					document.getElementById('mnbongkar1').disabled=true;
					
					document.getElementById('tanggalbongkar2').disabled=true;
					document.getElementById('jmbongkar2').disabled=true;
					document.getElementById('mnbongkar2').disabled=true;
					document.getElementById('tipe').disabled=true;
					document.getElementById('unitreferensi').disabled=true;
					
					
					document.getElementById('listdata').style.display='none';
					document.getElementById('header').style.display='block';
					document.getElementById('detail').style.display='block';
					loaddatadt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function displaylist() {
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('notransaksisch').value='';
    document.getElementById('tanggalmulaisch').value='';
    document.getElementById('tanggalselesaisch').value='';
    document.getElementById('kodebarangsch').value='';
    document.getElementById('unitsch').value='';
    document.getElementById('kodeptsch').value='';
    document.getElementById('tipesch').value='';
	loaddata(0);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}



function loaddata(num) {
	notransaksi=document.getElementById('notransaksisch').value;
	tanggalmulai=document.getElementById('tanggalmulaisch').value;
	tanggalselesai=document.getElementById('tanggalselesaisch').value;
	kodept=document.getElementById('kodeptsch').value;
	unit=document.getElementById('unitsch').value;
	kodebarang=document.getElementById('kodebarangsch').value;
	tipe=document.getElementById('tipesch').value;
	param = 'method=loaddata&page=' + num;
	param += '&notransaksi=' + notransaksi+'&tanggalmulai=' + tanggalmulai+'&tanggalselesai=' + tanggalselesai;
	param += '&kodept=' + kodept+'&unit=' + unit+'&kodebarang=' + kodebarang+'&tipe=' + tipe;
	tujuan = 'pabrik_bamutasi_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					leftFixedTable();
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function newdata(){
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	document.getElementById('detail').style.display='none';
	cancelht();
	// document.getElementById('detailhead').style.display='none';
}



function getkapalponton(namakapal,namaponton) {
	transportir=document.getElementById('transportir').value;
	param = "method=getkapalponton";
	param += "&namakapal=" + namakapal;
	param += "&transportir=" + transportir;
	param += "&namaponton=" + namaponton;
	// alert(param);
	tujuan = 'pmn_spk_upload_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					ar=con.responseText.split("####");
					// alert(ar);
					document.getElementById('namakapal').innerHTML = ar[0];
					document.getElementById('namaponton').innerHTML = ar[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function posting(notransaksi) {
	param='method=posting'+'&notransaksi='+notransaksi;
	tujuan = 'pabrik_bamutasi_slave.php';
    // post_response_text(tujuan, param, respog);
	if(confirm("Posting data "+notransaksi+" ?")){
        post_response_text(tujuan, param, respon);	
    }
	function respon(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				} else {
					loaddata(0);
				}
			} else {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}


function unposting(notransaksi) {
	param='method=unposting'+'&notransaksi='+notransaksi;
	tujuan = 'pabrik_bamutasi_slave.php';
    if(confirm("Unposting data "+notransaksi+" ?")){
        post_response_text(tujuan, param, respon);	
    }
	function respon(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				} else {
					loaddata(0);
				}
			} else {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}



/*

function carinosip(title,ev){
    content= "<div id=formcarinosip style=\"max-height:250px;width:max-450;overflow:auto;\"></div>";
    title=title;
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    param='method=carinosip';
    tujuan = 'pabrik_bamutasi_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formcarinosip').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 	
}


function caridaftarnosip(){
    nosip=document.getElementById('daftarnosip').value;
    // nospk=document.getElementById('daftarnospk').value;
    tanggal=document.getElementById('tanggal').value;
    unit=document.getElementById('unit').value;
    pt=document.getElementById('kodept').value;
    param='method=carinosip'+'&nosip='+nosip+'&tanggal='+tanggal+'&unit='+unit+'&kodept='+pt;
    tujuan = 'pabrik_bamutasi_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formcarinosip').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}

*/



function getsipb(title,content){
	param='method=getsipb';
    tujuan = 'pabrik_bamutasi_slave.php';
	post_response_text(tujuan, param, respon);
	function respon(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				}
				else {
					// document.getElementById('formpencarian').innerHTML=con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('90%','85%'); 
					// findap();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}



function findsipb(){
	nosip=document.getElementById('nosipbfind').value;
	kodebarang=document.getElementById('kodebarang').value;
    tanggal=document.getElementById('tanggal').value;
    tanggaltiba=document.getElementById('tanggaltiba').value;
    unit=document.getElementById('unit').value;
    pt=document.getElementById('kodept').value;
    transportasi=document.getElementById('transportasi').value;
    param='method=findsipb'+'&nosip='+nosip+'&tanggal='+tanggal+'&unit='+unit+'&kodept='+pt+'&tanggaltiba='+tanggaltiba+'&kodebarang='+kodebarang+'&transportasi='+transportasi;
    tujuan = 'pabrik_bamutasi_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					// leftFixedTable();
					document.getElementById('formcarinosip').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function movecarinosip(nosip,kodebarang,sisaqty){
	
	//cek apakah kodebarang sama
	kodebarangparam=document.getElementById('kodebarang').value;
	if(kodebarangparam!=kodebarang){
		alert('Komoditi dokumen tidak sama dengan komoditi kontrak');return;
	}
    document.getElementById('nosip').value=nosip;
    document.getElementById('jumlah').value=sisaqty;
    // document.getElementById('kodetangki').value='';
    // document.getElementById('kodetangki').disabled=true;
    alertify.popup().destroy();
	
}



function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function carinoreferensi(title,ev){
	
	tipe=document.getElementById('tipe').value;
	if(tipe!='IN'){
		alert('Hanya untuk tipe transaksi penerimaan');return;
	}
	
    content= "<div id=formcarinoreferensi style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    title=title;
    height='';
    width='';
	showDialog1(title,content,width,height,ev);	
    param='method=carinoreferensi';
    tujuan = 'pabrik_bamutasi_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formcarinoreferensi').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}

function caridaftarnoreferensi(){
    noreferensi=document.getElementById('daftarnoreferensi').value;
	unit=document.getElementById('unit').value;
	unitreferensi=document.getElementById('unitreferensi').value;
    param='method=carinoreferensi'+'&noreferensi='+noreferensi+'&unit='+unit+'&unitreferensi='+unitreferensi;
    tujuan = 'pabrik_bamutasi_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formcarinoreferensi').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}


function movecarinoreferensi(noreferensi){
    document.getElementById('noreferensi').value=noreferensi;
    closeDialog();
}



function gettotal(){
	tipe=document.getElementById('tipe').value
	jumlah1=document.getElementById('jumlah1').value
	jumlah2=document.getElementById('jumlah2').value
	jumlah1=remove_comma_var(jumlah1);
	jumlah2=remove_comma_var(jumlah2);
	if(tipe=='OUT'){
		jumlah=parseFloat(jumlah1)-parseFloat(jumlah2);
	}else{
		jumlah=parseFloat(jumlah2)-parseFloat(jumlah1);
	}
	document.getElementById('jumlah').value=numberFormat(jumlah);
}


function getvol(no){
	param="";
	kodeorg=document.getElementById('unit').value;
	kodetangki=document.getElementById('kodetangki').value;
	suhu=document.getElementById('suhu'+no).value;
	tinggi=document.getElementById('tinggi'+no).value;
	proses='getVol';
    if(kodeorg=='' || kodetangki==''){
		alert('Field Was Empty');
        return false;
    }
	param+='kodeorg='+kodeorg+'&kodetangki='+kodetangki+'&proses='+proses;
	param += '&suhu=' + suhu+'&tinggi='+tinggi;
    tujuan='pabrik_slave_hasil.php'; //lempar kesini biar sama rumusnya
    post_response_text(tujuan, param, respog);  
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else  {
					var a=con.responseText.split("##");
					document.getElementById('jumlah'+no).value=a[0];
					gettotal();
				}
			} else  {
				busy_off();
                error_catch(con.status);
			}
		} 
	}
}

function deleteht(notransaksi){
	param = 'method=deleteht';
	param+='&notransaksi='+notransaksi;
	tujuan = 'pabrik_bamutasi_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


/********************************************** pdf *********************************/
/********************************************** pdf *********************************/

function pdf(notransaksi) {
	param = 'method=pdf' + '&notransaksi=' + notransaksi;
	tujuan='pabrik_bamutasi_slave.php';
	tujuan = tujuan+'?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog5(title, content, width, height, 'event');
}




/********************************************** detail *********************************/
/********************************************** detail *********************************/

function savedt() {
	param = "";
	
	
	
	
	
	
	
	notransaksi= document.getElementById('notransaksi').value;
	tanggal= document.getElementById('tanggal').value;
	transportir= document.getElementById('transportir').value;
	namakapal= document.getElementById('namakapal').value;
	namaponton= document.getElementById('namaponton').value;
	keteranganht= document.getElementById('keteranganht').value;
	jmberangkat= document.getElementById('jmberangkat').value;
	tanggalberangkat= document.getElementById('tanggalberangkat').value;
	mnberangkat= document.getElementById('mnberangkat').value;
	unit= document.getElementById('unit').value;
	kodebarang= document.getElementById('kodebarang').value;
	kodept= document.getElementById('kodept').value;
	jmtiba= document.getElementById('jmtiba').value;
	tanggaltiba= document.getElementById('tanggaltiba').value;
	mntiba= document.getElementById('mntiba').value;
		
	tanggalbongkar1= document.getElementById('tanggalbongkar1').value;
	jmbongkar1= document.getElementById('jmbongkar1').value;
	mnbongkar1= document.getElementById('mnbongkar1').value;
		
	tanggalbongkar2= document.getElementById('tanggalbongkar2').value;
	jmbongkar2= document.getElementById('jmbongkar2').value;
	mnbongkar2= document.getElementById('mnbongkar2').value;
	
	tipe	= document.getElementById('tipe').value;
	transportasi	= document.getElementById('transportasi').value;
	
	
	nosip	= document.getElementById('nosip').value;
	kodetangki= document.getElementById('kodetangki').value;
	keterangandt= document.getElementById('keterangandt').value;
	jumlah= document.getElementById('jumlah').value;
	jumlah=remove_comma_var(jumlah);
	
	
	suhu1= document.getElementById('suhu1').value;
	tinggi1= document.getElementById('tinggi1').value;
	jumlah1= document.getElementById('jumlah1').value;
	ffa1= document.getElementById('ffa1').value;
	moisture1= document.getElementById('moisture1').value;
	dirt1= document.getElementById('dirt1').value;
	dobi1= document.getElementById('dobi1').value;
	broken1= document.getElementById('broken1').value;
	broken1= document.getElementById('broken1').value;
	
	suhu2= document.getElementById('suhu2').value;
	tinggi2= document.getElementById('tinggi2').value;
	jumlah2= document.getElementById('jumlah2').value;
	ffa2= document.getElementById('ffa2').value;
	moisture2= document.getElementById('moisture2').value;
	dirt2= document.getElementById('dirt2').value;
	dobi2= document.getElementById('dobi2').value;
	broken2= document.getElementById('broken2').value;
	
	jumlah2=remove_comma_var(jumlah2);
	jumlah1=remove_comma_var(jumlah1);
	
	unitreferensi = document.getElementById('unitreferensi').value;
	noreferensi = document.getElementById('noreferensi').value;
	method = document.getElementById('method').value;
	
	param+='&notransaksi='+notransaksi+'&tanggal='+tanggal+'&transportasi='+transportasi+'&transportir='+transportir;
	param+='&namakapal='+namakapal+'&namaponton='+namaponton+'&keteranganht='+keteranganht;
	param+='&jmberangkat='+jmberangkat+'&tanggalberangkat='+tanggalberangkat+'&mnberangkat='+mnberangkat;
	param+='&jmtiba='+jmtiba+'&tanggaltiba='+tanggaltiba+'&mntiba='+mntiba;
	param+='&unit='+unit+'&kodebarang='+kodebarang+'&kodept='+kodept;
	
	param+='&tanggalbongkar1='+tanggalbongkar1+'&jmbongkar1='+jmbongkar1+'&mnbongkar1='+mnbongkar1;
	param+='&tanggalbongkar2='+tanggalbongkar2+'&jmbongkar2='+jmbongkar2+'&mnbongkar2='+mnbongkar2;
	param+='&nosip='+nosip+'&tipe='+tipe;
	param+='&jumlah='+jumlah+'&kodetangki='+kodetangki+'&keterangandt='+keterangandt;
	
	param += '&suhu1=' + suhu1 + '&tinggi1=' + tinggi1 + '&jumlah1=' + jumlah1+ '&ffa1=' + ffa1;
	param += '&moisture1=' + moisture1 + '&dirt1=' + dirt1 + '&dobi1=' + dobi1+ '&broken1=' + broken1;
	param += '&suhu2=' + suhu2 + '&tinggi2=' + tinggi2 + '&jumlah2=' + jumlah2+ '&ffa2=' + ffa2;
	param += '&moisture2=' + moisture2 + '&dirt2=' + dirt2 + '&dobi2=' + dobi2+ '&broken2=' + broken2;
	param += '&unitreferensi=' + unitreferensi + '&noreferensi=' + noreferensi;
	param += '&method=' + method;
	
	
	if(jumlah<=0){
		alert('Jumlah mutasi dibawah 0');return;
	}
	
	
	tujuan = 'pabrik_bamutasi_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					canceldt();
					loaddatadt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function editdt(notransaksi,nosip,kodetangki){
	param = 'method=geteditdt';
	param+='&notransaksi='+notransaksi+'&nosip='+nosip+'&kodetangki='+kodetangki;
	tujuan = 'pabrik_bamutasi_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('method').value = 'update';
					// alert(con.responseText.split);
					ar = con.responseText.split("###");
					
					document.getElementById('nosip').value=ar[0];
						document.getElementById('kodetangki').value=ar[1];
						document.getElementById('kodetangki').disabled=true;
						document.getElementById('keterangandt').value=ar[2];
						document.getElementById('jumlah').value=ar[3];
					
					document.getElementById('suhu1').value=ar[4];
					document.getElementById('tinggi1').value=ar[5];
					document.getElementById('jumlah1').value=ar[6];
					document.getElementById('ffa1').value=ar[7];
					document.getElementById('moisture1').value=ar[8];
					document.getElementById('dirt1').value=ar[9];
					document.getElementById('dobi1').value=ar[10];
					document.getElementById('broken1').value=ar[11];
						document.getElementById('suhu2').value =ar[12];
						document.getElementById('tinggi2').value=ar[13];
						document.getElementById('jumlah2').value =ar[14];
						document.getElementById('ffa2').value=ar[15];
						document.getElementById('moisture2').value=ar[16];
						document.getElementById('dirt2').value=ar[17];
						document.getElementById('dobi2').value =ar[18];
						document.getElementById('broken2').value =ar[19];
					document.getElementById('method').value ='update';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function canceldt(){
	document.getElementById('nosip').value ='';
		document.getElementById('kodetangki').value='';
		document.getElementById('keterangandt').value='';
		document.getElementById('jumlah').value='0';
	document.getElementById('suhu1').value ='';
	document.getElementById('tinggi1').value='';
	document.getElementById('jumlah1').value='0';
	document.getElementById('ffa1').value='';
	document.getElementById('moisture1').value='';
	document.getElementById('dirt1').value='';
	document.getElementById('dobi1').value='';
	document.getElementById('broken1').value='';
		document.getElementById('suhu2').value ='';
		document.getElementById('tinggi2').value='';
		document.getElementById('jumlah2').value ='0';
		document.getElementById('ffa2').value ='';
		document.getElementById('moisture2').value ='';
		document.getElementById('dirt2').value ='';
		document.getElementById('dobi2').value ='';
		document.getElementById('broken2').value ='';
		document.getElementById('method').value ='insert';
		document.getElementById('kodetangki').disabled=false;
}


function deletedt(notransaksi,nosip,kodetangki){
	param = 'method=deletedt';
	param+='&notransaksi='+notransaksi+'&nosip='+nosip+'&kodetangki='+kodetangki;
	tujuan = 'pabrik_bamutasi_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listdatadt').innerHTML = con.responseText;
					loaddatadt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loaddatadt(mode,namakapal,namaponton) {
	notransaksi= document.getElementById('notransaksi').value;
	param = 'method=loaddatadt';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'pabrik_bamutasi_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listdatadt').innerHTML = con.responseText;
					// if(mode=='edit'){
						// getkapalponton(namakapal,namaponton);
					// }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}













