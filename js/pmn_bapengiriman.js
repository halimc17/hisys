function saveht() {
	param='';
	tanggal= document.getElementById('tanggal').value;
	unit= document.getElementById('unit').value;
	kodebarang= document.getElementById('kodebarang').value;
	if(tanggal==''){
		alert('Tanggal tidak boleh kosong');return;
	}
	if(unit==''){
		alert('Unit tidak boleh kosong');return;
	}
	if(kodebarang==''){
		alert('Komoditi tidak boleh kosong');return;
	}
	method = 'notransaksi';
	param += '&unit=' + unit + '&tanggal=' + tanggal+ '&kodebarang=' + kodebarang;
	param += '&method=' + method;
	tujuan = 'pmn_bapengiriman_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('saveht').disabled=true;
					document.getElementById('notransaksi').value=con.responseText;
					document.getElementById('detail').style.display='block';
					document.getElementById('notransaksi').disabled=true;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('pelabuhantujuan').disabled=true;
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
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function cancelht(){
	document.getElementById('notransaksi').disabled=false;
	document.getElementById('tanggal').disabled=false;
	document.getElementById('pelabuhantujuan').disabled=false;
	document.getElementById('transportir').disabled=false;
	document.getElementById('namakapal').disabled=false;
	document.getElementById('namaponton').disabled=false;
	document.getElementById('keteranganht').disabled=false;
	document.getElementById('jmberangkat').disabled=false;
	document.getElementById('tanggalberangkat').disabled=false;
	document.getElementById('mnberangkat').disabled=false;
	document.getElementById('unit').disabled=false;
	document.getElementById('saveht').disabled=false;
	document.getElementById('kodebarang').disabled=false;
	document.getElementById('notransaksi').value = '';
	document.getElementById('tanggal').value = '';
	document.getElementById('pelabuhantujuan').value = '';
	document.getElementById('namakapal').value = '';
	document.getElementById('namaponton').value = '';
	document.getElementById('keteranganht').value = '';
	document.getElementById('tanggalberangkat').value = '';
	document.getElementById('jmberangkat').value = '';
	document.getElementById('mnberangkat').value = '';
	document.getElementById('unit').value = '';
	document.getElementById('kodebarang').value = '';
	document.getElementById('transportir').value = '';
	document.getElementById('detail').style.display='none';
	document.getElementById('method').value ='insert';
}

function editht(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'pmn_bapengiriman_slave.php';
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
					document.getElementById('pelabuhantujuan').value = ar[2];
					document.getElementById('namakapal').value = ar[3];
					document.getElementById('namaponton').value = ar[4];
					document.getElementById('keteranganht').value = ar[5];
					document.getElementById('tanggalberangkat').value = ar[6];
					document.getElementById('jmberangkat').value = ar[7];
					document.getElementById('mnberangkat').value = ar[8];
					document.getElementById('unit').value = ar[9];
					document.getElementById('kodebarang').value = ar[10];
					document.getElementById('transportir').value = ar[11];

					document.getElementById('notransaksi').disabled=true;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('pelabuhantujuan').disabled=true;
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
	
    document.getElementById('tanggalpasang1sch').value='';
    document.getElementById('tanggalpasang2sch').value='';
    document.getElementById('tanggalmuat1sch').value='';
    document.getElementById('tanggalmuat2sch').value='';
    document.getElementById('nokontraksch').value='';
	loaddata(0);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}



function loaddata(num) {
	notransaksisch=document.getElementById('notransaksisch').value;
	tanggalmulaisch=document.getElementById('tanggalmulaisch').value;
	tanggalselesaisch=document.getElementById('tanggalselesaisch').value;
	
	tanggalpasang1sch=document.getElementById('tanggalpasang1sch').value;
	tanggalpasang2sch=document.getElementById('tanggalpasang2sch').value;
	
	tanggalmuat1sch=document.getElementById('tanggalmuat1sch').value;
	tanggalmuat2sch=document.getElementById('tanggalmuat2sch').value;
	nokontraksch=document.getElementById('nokontraksch').value;
	
	
	param = 'method=loaddata&page=' + num;
	param += '&notransaksisch=' + notransaksisch;
	param += '&tanggalmulaisch=' + tanggalmulaisch+'&tanggalselesaisch=' + tanggalselesaisch;
	param += '&tanggalpasang1sch=' + tanggalpasang1sch+'&tanggalpasang2sch=' + tanggalpasang2sch;
	param += '&tanggalmuat1sch=' + tanggalmuat1sch+'&tanggalmuat2sch=' + tanggalmuat2sch+'&nokontraksch=' + nokontraksch;
	tujuan = 'pmn_bapengiriman_slave.php';
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


function newdata(){
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	document.getElementById('detail').style.display='none';
	// document.getElementById('detailhead').style.display='none';
}




function posting(notransaksi) {
	param='method=posting'+'&notransaksi='+notransaksi;
	tujuan = 'pmn_bapengiriman_slave.php';
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



function carinokontrak(title,ev){
    content= "<div id=formcarinokontrak style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    title=title;
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    param='method=carinokontrak';
    tujuan = 'pmn_bapengiriman_slave.php';
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
    tanggal=document.getElementById('tanggal').value;
    unit=document.getElementById('unit').value;
    tanggalmuat2=document.getElementById('tanggalmuat2').value;
    jmmuat2=document.getElementById('jmmuat2').value;
    mnmuat2=document.getElementById('mnmuat2').value;
    param='method=carinokontrak'+'&nokontrak='+nokontrak+'&tanggal='+tanggal+'&unit='+unit;
	param += '&mnmuat2=' + mnmuat2+'&jmmuat2='+jmmuat2+'&tanggalmuat2='+tanggalmuat2;
    tujuan = 'pmn_bapengiriman_slave.php';
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

function movecarinokontrak(nokontrak,kodept,tanggal,kodecustomer,kodebarangkontrak,sisaqty){
	
	//cek apakah kodebarang sama
	kodebarangparam=document.getElementById('kodebarang').value;
	if(kodebarangparam!=kodebarangkontrak){
		alert('Komoditi dokumen tidak sama dengan komoditi kontrak');return;
	}
    document.getElementById('kodept').value=kodept;
    document.getElementById('nokontrak').value=nokontrak;
    document.getElementById('kodecustomer').value=kodecustomer;
    document.getElementById('jumlah').value=sisaqty;
    closeDialog();
	
}


function gettotal(){
	jumlah1=document.getElementById('jumlah1').value
	jumlah2=document.getElementById('jumlah2').value
	selisih=document.getElementById('selisih').value
	jumlah1=remove_comma_var(jumlah1);
	jumlah1=remove_comma_var(jumlah1);
	selisih=remove_comma_var(selisih);
	jumlah=parseFloat(jumlah1)-parseFloat(jumlah2)-parseFloat(selisih);
	document.getElementById('jumlah').value=numberFormat(jumlah);
}


function getvol(no){
	param="";
	kodeorg=document.getElementById('unit').value;
	kodetangki=document.getElementById('kodetangki').value;
	suhu=document.getElementById('suhu'+no).value;
	tinggi=document.getElementById('tinggi'+no).value;
	proses='getVol';
    if(kodeorg==''){
		alert('Unit tidak boleh kosong');
        return false;
    }

    if(kodetangki==''){
		alert('Kode tangki tidak boleh kosong');
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
	tujuan = 'pmn_bapengiriman_slave.php';
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

function pdfinternal(notransaksi) {
	param = 'method=pdfinternal' + '&notransaksi=' + notransaksi;
	tujuan='pmn_bapengiriman_slave.php';
	tujuan = tujuan+'?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	// showDialog5(title, content, width, height, 'event');
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}

function pdfexternal(notransaksi) {
	param = 'method=pdfexternal' + '&notransaksi=' + notransaksi;
	tujuan='pmn_bapengiriman_slave.php';
	tujuan = tujuan+'?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	//showDialog5(title, content, width, height, 'event');
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}



/********************************************** detail *********************************/
/********************************************** detail *********************************/

function savedt() {
	param = "";
	notransaksi= document.getElementById('notransaksi').value;
	tanggal= document.getElementById('tanggal').value;
	pelabuhantujuan= document.getElementById('pelabuhantujuan').value;
	transportir= document.getElementById('transportir').value;
	namakapal= document.getElementById('namakapal').value;
	namaponton= document.getElementById('namaponton').value;
	keteranganht= document.getElementById('keteranganht').value;
	jmberangkat= document.getElementById('jmberangkat').value;
	tanggalberangkat= document.getElementById('tanggalberangkat').value;
	mnberangkat= document.getElementById('mnberangkat').value;
	unit= document.getElementById('unit').value;
	kodebarang= document.getElementById('kodebarang').value;
		
		param+='&notransaksi='+notransaksi+'&tanggal='+tanggal+'&pelabuhantujuan='+pelabuhantujuan+'&transportir='+transportir;
		param+='&namakapal='+namakapal+'&namaponton='+namaponton+'&keteranganht='+keteranganht;
		param+='&jmberangkat='+jmberangkat+'&tanggalberangkat='+tanggalberangkat+'&mnberangkat='+mnberangkat;
		param+='&unit='+unit+'&kodebarang='+kodebarang;
	
	nokontrak	= document.getElementById('nokontrak').value;
	kodept= document.getElementById('kodept').value;
	kodecustomer= document.getElementById('kodecustomer').value;
	
		param+='&nokontrak='+nokontrak+'&kodept='+kodept+'&kodecustomer='+kodecustomer+'&kodebarang='+kodebarang;
	
	
	kodetangki= document.getElementById('kodetangki').value;
	keterangandt= document.getElementById('keterangandt').value;
	jumlah= document.getElementById('jumlah').value;
	jumlah=remove_comma_var(jumlah);
		param+='&jumlah='+jumlah+'&kodetangki='+kodetangki+'&keterangandt='+keterangandt;
	
	tanggalpasang1= document.getElementById('tanggalpasang1').value;
	jmpasang1= document.getElementById('jmpasang1').value;
	mnpasang1= document.getElementById('mnpasang1').value;
		param+='&tanggalpasang1='+tanggalpasang1+'&jmpasang1='+jmpasang1+'&mnpasang1='+mnpasang1;
	
	tanggalpasang2= document.getElementById('tanggalpasang2').value;
	jmpasang2= document.getElementById('jmpasang2').value;
	mnpasang2= document.getElementById('mnpasang2').value;
		param+='&tanggalpasang2='+tanggalpasang2+'&jmpasang2='+jmpasang2+'&mnpasang2='+mnpasang2;
	
	tanggalmuat1= document.getElementById('tanggalmuat1').value;
	jmmuat1= document.getElementById('jmmuat1').value;
	mnmuat1= document.getElementById('mnmuat1').value;
		param+='&tanggalmuat1='+tanggalmuat1+'&jmmuat1='+jmmuat1+'&mnmuat1='+mnmuat1;
	
	tanggalmuat2= document.getElementById('tanggalmuat2').value;
	jmmuat2= document.getElementById('jmmuat2').value;
	mnmuat2= document.getElementById('mnmuat2').value;
		param+='&tanggalmuat2='+tanggalmuat2+'&jmmuat2='+jmmuat2+'&mnmuat2='+mnmuat2;
	
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
	
	
	selisih= document.getElementById('selisih').value;
	selisih=remove_comma_var(selisih);
	
	method            = document.getElementById('method').value;
	
	param += '&suhu1=' + suhu1 + '&tinggi1=' + tinggi1 + '&jumlah1=' + jumlah1+ '&ffa1=' + ffa1;
	param += '&moisture1=' + moisture1 + '&dirt1=' + dirt1 + '&dobi1=' + dobi1+ '&broken1=' + broken1;
	param += '&suhu2=' + suhu2 + '&tinggi2=' + tinggi2 + '&jumlah2=' + jumlah2+ '&ffa2=' + ffa2;
	param += '&moisture2=' + moisture2 + '&dirt2=' + dirt2 + '&dobi2=' + dobi2+ '&broken2=' + broken2;
	param += '&method=' + method+ '&selisih=' + selisih;
	
	if(jumlah<=0){
		alert('Jumlah mutasi dibawah 0');return;
	}
	if(tanggalmuat1==''){
		alert('Tanggal pompa / tanggal muat masih kosong');return;
	}
	
	tujuan = 'pmn_bapengiriman_slave.php';
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

function editdt(notransaksi,nokontrak,kodetangki){
	param = 'method=geteditdt';
	param+='&notransaksi='+notransaksi+'&nokontrak='+nokontrak+'&kodetangki='+kodetangki;
	tujuan = 'pmn_bapengiriman_slave.php';
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
					
					document.getElementById('nokontrak').value=ar[0];
					document.getElementById('kodecustomer').value =ar[1];
					document.getElementById('kodept').value =ar[2];
						document.getElementById('kodetangki').value=ar[3];
						document.getElementById('kodetangki').disabled=true;
						document.getElementById('keterangandt').value=ar[4];
						document.getElementById('jumlah').value=ar[5];
					document.getElementById('tanggalpasang1').value =ar[6];
					document.getElementById('jmpasang1').value=ar[7];
					document.getElementById('mnpasang1').value=ar[8];
						document.getElementById('tanggalpasang2').value=ar[9];
						document.getElementById('jmpasang2').value =ar[10];
						document.getElementById('mnpasang2').value =ar[11];
					document.getElementById('tanggalmuat1').value =ar[12];
					document.getElementById('jmmuat1').value=ar[13];
					document.getElementById('mnmuat1').value=ar[14];
						document.getElementById('tanggalmuat2').value=ar[15];
						document.getElementById('jmmuat2').value=ar[16];
						document.getElementById('mnmuat2').value =ar[17];
					document.getElementById('suhu1').value=ar[18];
					document.getElementById('tinggi1').value=ar[19];
					document.getElementById('jumlah1').value=ar[20];
					document.getElementById('ffa1').value=ar[21];
					document.getElementById('moisture1').value=ar[22];
					document.getElementById('dirt1').value=ar[23];
					document.getElementById('dobi1').value=ar[24];
					document.getElementById('broken1').value=ar[25];
						document.getElementById('suhu2').value =ar[26];
						document.getElementById('tinggi2').value=ar[27];
						document.getElementById('jumlah2').value =ar[28];
						document.getElementById('ffa2').value=ar[29];
						document.getElementById('moisture2').value=ar[30];
						document.getElementById('dirt2').value=ar[31];
						document.getElementById('dobi2').value =ar[32];
						document.getElementById('broken2').value =ar[33];
						document.getElementById('selisih').value =ar[34];
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
	// document.getElementById('nokontrak').value ='';
	// document.getElementById('kodecustomer').value ='';
	// document.getElementById('kodept').value ='';
		document.getElementById('kodetangki').value='';
		document.getElementById('keterangandt').value='';
		document.getElementById('jumlah').value='0';
	// document.getElementById('tanggalpasang1').value ='';
	// document.getElementById('jmpasang1').value ='00';
	// document.getElementById('mnpasang1').value ='00';
		// document.getElementById('tanggalpasang2').value ='';
		// document.getElementById('jmpasang2').value ='00';
		// document.getElementById('mnpasang2').value ='00';
	// document.getElementById('tanggalmuat1').value ='';
	// document.getElementById('jmmuat1').value ='00';
	// document.getElementById('mnmuat1').value ='00';
		// document.getElementById('tanggalmuat2').value ='';
		// document.getElementById('jmmuat2').value ='00';
		// document.getElementById('mnmuat2').value ='00';
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
		document.getElementById('selisih').value =0;
		document.getElementById('method').value ='insert';
		document.getElementById('kodetangki').disabled=false;
}


function deletedt(notransaksi,nokontrak,kodetangki){
	param = 'method=deletedt';
	param+='&notransaksi='+notransaksi+'&nokontrak='+nokontrak+'&kodetangki='+kodetangki;
	tujuan = 'pmn_bapengiriman_slave.php';
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


function loaddatadt() {
	notransaksi= document.getElementById('notransaksi').value;
	param = 'method=loaddatadt';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'pmn_bapengiriman_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listdatadt').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}













