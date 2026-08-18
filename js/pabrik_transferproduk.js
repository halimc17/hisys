function cancelnoreferensi(){	
	 document.getElementById('noreferensi').value='';
}

function cancelnokontrak(){	
	 document.getElementById('nokontrak').value='';
}


function posting(notransaksi,noreferensi,tipe) {
	param='method=posting'+'&notransaksi='+notransaksi+'&tipe='+tipe+'&noreferensi='+noreferensi;
	tujuan = 'pabrik_transferproduk_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
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

function deleteht(notransaksi){
	param = 'method=deleteht';
	param+='&notransaksi='+notransaksi;
	tujuan = 'pabrik_transferproduk_slave.php';
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



function carinoreferensi(title,ev){
    content= "<div id=formcarinoreferensi style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    title=title;
    height='';
    width='';
	showDialog1(title,content,width,height,ev);	
    param='method=carinoreferensi';
    tujuan = 'pabrik_transferproduk_slave.php';
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
    param='method=carinoreferensi'+'&noreferensi='+noreferensi;
    tujuan = 'pabrik_transferproduk_slave.php';
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


function movecarinoreferensi(noreferensi,jumlah){
    document.getElementById('noreferensi').value=noreferensi;
    document.getElementById('jumlah').value=jumlah;
    closeDialog();
}



function carinokontrak(title,ev){
    content= "<div id=formcarinokontrak style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    title=title;
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    param='method=carinokontrak';
    tujuan = 'pmn_spk_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formcarinokontrak').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 	
}

function caridaftarnokontrak(){
    nokontrak=document.getElementById('daftarnokontrak').value;
    param='method=carinokontrak'+'&nokontrak='+nokontrak;
    tujuan = 'pmn_spk_slave.php';
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
                    document.getElementById('formcarinokontrak').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}


function movecarinokontrak(nokontrak){
    document.getElementById('nokontrak').value=nokontrak;
    closeDialog();
}



function gettangki(){
	param="";
	unit=document.getElementById('unit').value;
	method='gettangki';
    if(unit==''){
		alert('Field Was Empty');
        return false;
    }
	param+='unit='+unit+'&method='+method;
    tujuan='pabrik_transferproduk_slave.php';
    post_response_text(tujuan, param, respog);      
    
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else  {
					document.getElementById('kodetangki').innerHTML=con.responseText;
					document.getElementById('kodetangkitujuan').innerHTML=con.responseText;
				}
			} else  {
				busy_off();
                error_catch(con.status);
			}
		} 
	}
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


function save() {
	param = "";
	notransaksi= document.getElementById('notransaksi').value;
	tanggal= document.getElementById('tanggal').value;
	tipe= document.getElementById('tipe').value;
	tanggalmulai= document.getElementById('tanggalmulai').value;
	tanggalselesai= document.getElementById('tanggalselesai').value;
	
	jmmulai= document.getElementById('jmmulai').value;
	mnmulai= document.getElementById('mnmulai').value;
	jmselesai= document.getElementById('jmselesai').value;
	mnselesai= document.getElementById('mnselesai').value;
	
	unit= document.getElementById('unit').value;
	kodetangki= document.getElementById('kodetangki').value;
	kodetangkitujuan= document.getElementById('kodetangkitujuan').value;
	nokontrak= document.getElementById('nokontrak').value;
	jumlah= document.getElementById('jumlah').value;
	
	suhu1= document.getElementById('suhu1').value;
	tinggi1= document.getElementById('tinggi1').value;
	jumlah1= document.getElementById('jumlah1').value;
	ffa1= document.getElementById('ffa1').value;
	moisture1= document.getElementById('moisture1').value;
	dirt1= document.getElementById('dirt1').value;
	dobi1= document.getElementById('dobi1').value;
	broken1= document.getElementById('broken1').value;
	keterangan1= document.getElementById('keterangan1').value;
	
	suhu2= document.getElementById('suhu2').value;
	tinggi2= document.getElementById('tinggi2').value;
	jumlah2= document.getElementById('jumlah2').value;
	ffa2= document.getElementById('ffa2').value;
	moisture2= document.getElementById('moisture2').value;
	dirt2= document.getElementById('dirt2').value;
	dobi2= document.getElementById('dobi2').value;
	broken2= document.getElementById('broken2').value;
	keterangan2= document.getElementById('keterangan2').value;
	keterangan= document.getElementById('keterangan').value;
	noreferensi= document.getElementById('noreferensi').value;
	
	kodept= document.getElementById('kodept').value;
	
		jumlah2=remove_comma_var(jumlah2);
		jumlah1=remove_comma_var(jumlah1);
		
		
		jumlah= document.getElementById('jumlah').value;
		jumlah=remove_comma_var(jumlah);
	
	method            = document.getElementById('method').value;
	// if (tanggal == '' || kuantitas == '' || nobl == '') {
		// alert('Field Was Empty');
		// return false;
	// }

	param += 'notransaksi=' + notransaksi + '&tipe=' + tipe + '&tanggalmulai=' + tanggalmulai + '&tanggalselesai=' + tanggalselesai;
	param += '&unit=' + unit + '&kodetangki=' + kodetangki + '&kodetangkitujuan=' + kodetangkitujuan + '&nokontrak=' + nokontrak;

	param += '&jmmulai=' + jmmulai + '&mnmulai=' + mnmulai + '&jmselesai=' + jmselesai + '&mnselesai=' + mnselesai;
	
	param += '&suhu1=' + suhu1 + '&tinggi1=' + tinggi1 + '&jumlah1=' + jumlah1+ '&ffa1=' + ffa1;
	param += '&moisture1=' + moisture1 + '&dirt1=' + dirt1 + '&dobi1=' + dobi1+ '&broken1=' + broken1+ '&keterangan1=' + keterangan1;
	param += '&suhu2=' + suhu2 + '&tinggi2=' + tinggi2 + '&jumlah2=' + jumlah2+ '&ffa2=' + ffa2;
	param += '&moisture2=' + moisture2 + '&dirt2=' + dirt2 + '&dobi2=' + dobi2+ '&broken2=' + broken2+ '&keterangan2=' + keterangan2;
	
	param += '&method=' + method+ '&keterangan=' + keterangan+ '&noreferensi=' + noreferensi+ '&jumlah=' + jumlah+ '&tanggal=' + tanggal+ '&kodept=' + kodept;
	tujuan = 'pabrik_transferproduk_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cancel();
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function cancel(){
	document.getElementById('tanggal').value='';
	document.getElementById('notransaksi').value='';
	document.getElementById('tipe').value ='';
	document.getElementById('tanggal').disabled=false;
	document.getElementById('tipe').disabled=false;
	document.getElementById('tanggalmulai').value ='';
	document.getElementById('jmmulai').value ='';
	document.getElementById('mnmulai').value ='';
	document.getElementById('tanggalselesai').value ='';
	document.getElementById('jmselesai').value='';
	document.getElementById('mnselesai').value='';
	document.getElementById('unit').value ='';
	document.getElementById('kodetangki').value='';
	document.getElementById('kodetangkitujuan').value ='';
	document.getElementById('suhu1').value ='';
	document.getElementById('tinggi1').value='';
	document.getElementById('jumlah1').value='';
	document.getElementById('ffa1').value='';
	document.getElementById('moisture1').value='';
	document.getElementById('dirt1').value='';
	document.getElementById('dobi1').value='';
	document.getElementById('broken1').value='';
	document.getElementById('keterangan1').value='';
	document.getElementById('jumlah').value='';

	document.getElementById('suhu2').value ='';
	document.getElementById('tinggi2').value='';
	document.getElementById('jumlah2').value ='';
	document.getElementById('ffa2').value ='';
	document.getElementById('moisture2').value ='';
	document.getElementById('dirt2').value ='';
	document.getElementById('dobi2').value ='';
	document.getElementById('broken2').value ='';
	document.getElementById('keterangan2').value ='';
	document.getElementById('keterangan').value ='';
	document.getElementById('nokontrak').value ='';
	document.getElementById('noreferensi').value ='';
	document.getElementById('method').value ='insert';
}


function newdata(){
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	document.getElementById('detail').style.display='none';
	document.getElementById('detailhead').style.display='none';

	// document.getElementById('method').value='insert'; 
}


function printpdf(notransaksi) {
	tujuan='pabrik_transferproduk_slave.php';
	param = 'method=printpdf' + '&notransaksi=' + notransaksi;
	
	tujuan = tujuan+'?' + param;
	// alert(tujuan);
	
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog5(title, content, width, height, 'event');
}

function displaylist() {
	cancel();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('notransaksisch').value='';
    document.getElementById('kodetangkisch').value='';
    document.getElementById('tanggalmulaisch').value='';
    document.getElementById('tanggalselesaisch').value='';
	loaddata(0);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function carinokontrak(title,ev){
    content= "<div id=formcarinokontrak style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    title=title;
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    param='method=carinokontrak';
    tujuan = 'pmn_spk_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formcarinokontrak').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 	
}


function loaddata(num) {
	notransaksisch=document.getElementById('notransaksisch').value;
	kodetangkisch=document.getElementById('kodetangkisch').value;
	tanggalmulaisch=document.getElementById('tanggalmulaisch').value;
	tanggalselesaisch=document.getElementById('tanggalselesaisch').value;
	param = 'method=loaddata&page=' + num;
	param += '&notransaksisch=' + notransaksisch+'&kodetangkisch=' + kodetangkisch;
	param += '&tanggalmulaisch=' + tanggalmulaisch+'&tanggalselesaisch=' + tanggalselesaisch;
	tujuan = 'pabrik_transferproduk_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
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


function fillField(notransaksi) {
	param = 'method=getEditData' + '&notransaksi=' + notransaksi;
	tujuan = 'pabrik_transferproduk_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('method').value = 'update';
					// alert(con.responseText.split);
					ar = con.responseText.split("###");
					
					document.getElementById('notransaksi').value = ar[0];
					document.getElementById('tipe').value = ar[1];
					document.getElementById('tipe').disabled=true;
					document.getElementById('tanggalmulai').value = ar[2];
					document.getElementById('jmmulai').value = ar[3];
					document.getElementById('mnmulai').value = ar[4];
					document.getElementById('tanggalselesai').value = ar[5];
					document.getElementById('jmselesai').value = ar[6];
					document.getElementById('mnselesai').value = ar[7];
					document.getElementById('unit').value = ar[8];
					document.getElementById('kodetangki').value = ar[9];
					document.getElementById('kodetangkitujuan').value = ar[10];
					
					document.getElementById('suhu1').value = ar[11];
					document.getElementById('tinggi1').value = ar[12];
					document.getElementById('jumlah1').value = ar[13];
					document.getElementById('ffa1').value = ar[14];
					document.getElementById('moisture1').value = ar[15];
					document.getElementById('dirt1').value = ar[16];
					document.getElementById('dobi1').value = ar[17];
					document.getElementById('broken1').value = ar[18];
					document.getElementById('keterangan1').value = ar[19];
					
					document.getElementById('suhu2').value = ar[20];
					document.getElementById('tinggi2').value = ar[21];
					document.getElementById('jumlah2').value = ar[22];
					document.getElementById('ffa2').value = ar[23];
					document.getElementById('moisture2').value = ar[24];
					document.getElementById('dirt2').value = ar[25];
					document.getElementById('dobi2').value = ar[26];
					document.getElementById('broken2').value = ar[27];
					document.getElementById('keterangan2').value = ar[28];
					document.getElementById('nokontrak').value = ar[29];
					document.getElementById('keterangan').value = ar[30];
					document.getElementById('noreferensi').value = ar[31];
					document.getElementById('tanggal').value = ar[32];
					document.getElementById('jumlah').value = ar[33];
					document.getElementById('kodept').value = ar[34];
					

					document.getElementById('listdata').style.display='none';
					document.getElementById('header').style.display='block';
   
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}





















