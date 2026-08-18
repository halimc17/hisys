function newdata(){
	cancelht();
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	document.getElementById('detail').style.display='none';
}


function getkgterima(no) {
	
	// kgselisih=document.getElementById('kgselisih'+no).innerHTML;
	
	kgkirim=document.getElementById('kgkirim'+no).innerHTML;
	kgterimaawal=document.getElementById('kgterimaawal'+no).innerHTML;
	kgtoleransi=document.getElementById('kgtoleransi'+no).innerHTML;
	kgtonbag=document.getElementById('kgtonbag'+no).value;
	kgterimaawal=remove_comma_var(kgterimaawal);
	kgtonbag=remove_comma_var(kgtonbag);
	kgkirim=remove_comma_var(kgkirim);
	kgtoleransi=remove_comma_var(kgtoleransi);
	
	kgterima=parseFloat(kgterimaawal)+parseFloat(kgtonbag);
	document.getElementById('kgterima'+no).innerHTML=numberFormat(kgterima);
	
	kgselisih=parseFloat(kgterima)-parseFloat(kgkirim);
	document.getElementById('kgselisih'+no).innerHTML=numberFormat(kgselisih);
	
	kgclaim=parseFloat(kgselisih)-parseFloat(kgtoleransi);
	document.getElementById('kgclaim'+no).innerHTML=numberFormat(kgclaim);
}



function cancelht(){
	document.getElementById('notransaksi').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('unit').value='';
	document.getElementById('tipe').value='';
	document.getElementById('tanggalkirim1').value='';
	document.getElementById('tanggalkirim2').value='';
	document.getElementById('nospk').value='';
	document.getElementById('keterangan').value='';
	document.getElementById('tanggal').disabled=false;
	document.getElementById('unit').disabled=false;
	document.getElementById('tipe').disabled=false;
	document.getElementById('tanggalkirim1').disabled=false;
	document.getElementById('tanggalkirim2').disabled=false;
	document.getElementById('nospk').disabled=false;
	document.getElementById('keterangan').disabled=false;
	document.getElementById('detail').style.display = 'none';
	document.getElementById('detail').value = '';
	getnospk();
}

function saveht() {
	param='';
	tanggal= document.getElementById('tanggal').value;
	unit= document.getElementById('unit').value;
	tipe= document.getElementById('tipe').value;
	tanggalkirim1= document.getElementById('tanggalkirim1').value;
	tanggalkirim2= document.getElementById('tanggalkirim2').value;
	nospk= document.getElementById('nospk').value;
	
	if(tanggal==''){
		alert('Tanggal tidak boleh kosong');return;
	}
	if(unit==''){
		alert('Unit tidak boleh kosong');return;
	}
	if(tipe==''){
		alert('Tipe SPK masih kosong');return;
	}
	
	
	method = 'saveht';
	param += '&unit=' + unit + '&tanggal=' + tanggal;
	param += '&tanggalkirim1=' + tanggalkirim1 + '&tanggalkirim2=' + tanggalkirim2;
	param += '&tipe=' + tipe + '&nospk=' + nospk;
	param += '&method=' + method;
	// alert(param);
	tujuan = 'pmn_batransport_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// ar = con.responseText.split("###");
					document.getElementById('notransaksi').value=con.responseText;
					// document.getElementById('saveht').disabled=true;
					document.getElementById('detail').style.display='block';
					// document.getElementById('saveht').disabled=false;
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
	tanggal= document.getElementById('tanggal').value;
	unit= document.getElementById('unit').value;
	tipe= document.getElementById('tipe').value;
	tanggalkirim1= document.getElementById('tanggalkirim1').value;
	tanggalkirim2= document.getElementById('tanggalkirim2').value;
	nospk= document.getElementById('nospk').value;
	
	method = 'loaddatadt';
	param = 'unit=' + unit + '&tanggal=' + tanggal+ '&notransaksi=' + notransaksi+'&tanggalkirim1=' + tanggalkirim1 + '&tanggalkirim2=' + tanggalkirim2+'&tipe=' + tipe + '&nospk=' + nospk;
	param += '&method=' + method;
	// alert(param);
	// return;
	tujuan = 'pmn_batransport_slave.php';
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




function getnospk(nospk) {

	unit=document.getElementById('unit').value;
	tipe=document.getElementById('tipe').value;
	if(nospk==undefined){
		nospk=document.getElementById('nospk').value;
	}
	method = 'getnospk';
	param='';
	param += '&unit=' + unit + '&tipe=' + tipe + '&nospk=' + nospk;
	param += '&method=' + method;
	
	tujuan = 'pmn_batransport_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
                } else {
                    document.getElementById('nospk').innerHTML = con.responseText;
                    if(nospk!=''){
						loaddatadt();
					} 
                }
            } else {
				busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getrpclaim(no) {
	
	kgclaim=document.getElementById('kgclaim'+no).innerHTML;
	rpkgclaim=document.getElementById('rpkgclaim'+no).value;
	kgclaim=remove_comma_var(kgclaim);
	rpkgclaim=remove_comma_var(rpkgclaim);
	rpclaim=parseFloat(kgclaim)*parseFloat(rpkgclaim);
	document.getElementById('rpclaim'+no).innerHTML=numberFormat(rpclaim);
}


/********************************************** detail *********************************/
/********************************************** detail *********************************/

maxf=0
sekarang=1;
function savedt(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}



function loopsave(currRow,maxRow) {
    param = "";
	
	notransaksi=trim(document.getElementById('notransaksi').value);
	tanggal=trim(document.getElementById('tanggal').value);
    tanggalkirim1=trim(document.getElementById('tanggalkirim1').value);
    tanggalkirim2=trim(document.getElementById('tanggalkirim2').value);
	nospk=trim(document.getElementById('nospk').value);
	unit=trim(document.getElementById('unit').value);
	tipe=trim(document.getElementById('tipe').value);
	keterangan=trim(document.getElementById('keterangan').value);
	
	tanggalkirimpks=trim(document.getElementById('tanggalkirimpks'+currRow).innerHTML);
	nokendaraan=trim(document.getElementById('nokendaraan'+currRow).innerHTML);
	
	nokontrak=trim(document.getElementById('nokontrak'+currRow).innerHTML);
	notiket=trim(document.getElementById('notiket'+currRow).innerHTML);
	kgkirim=trim(document.getElementById('kgkirim'+currRow).innerHTML);
	kgtonbag=trim(document.getElementById('kgtonbag'+currRow).value);
	kgterimaawal=trim(document.getElementById('kgterimaawal'+currRow).innerHTML);
	kgterima=trim(document.getElementById('kgterima'+currRow).innerHTML);
	kgselisih=trim(document.getElementById('kgselisih'+currRow).innerHTML);
	rpkg=trim(document.getElementById('rpkg'+currRow).innerHTML);
	rpjumlah=trim(document.getElementById('rpjumlah'+currRow).innerHTML);

	persentoleransi=trim(document.getElementById('persentoleransi'+currRow).innerHTML);
	kgtoleransi=trim(document.getElementById('kgtoleransi'+currRow).innerHTML);
	kgclaim=trim(document.getElementById('kgclaim'+currRow).innerHTML);
	rpkgclaim=trim(document.getElementById('rpkgclaim'+currRow).value);
	rpclaim=trim(document.getElementById('rpclaim'+currRow).innerHTML);
	transportir=trim(document.getElementById('transportir'+currRow).innerHTML);
	noakundebet=trim(document.getElementById('noakundebet'+currRow).innerHTML);
	kodebarang=trim(document.getElementById('kodebarang'+currRow).innerHTML);

    if(unit=='' || tanggal=='') {
            alert("Data tidak lengkap");return;
    } else {  
        param+='&method=savedt'+'&unit='+unit+'&tipe='+tipe+'&tanggal='+tanggal+'&notransaksi='+notransaksi;
		param+='&tanggalkirim1='+tanggalkirim1+'&tanggalkirim2='+tanggalkirim2+'&keterangan='+keterangan+'&nospk='+nospk;
		param+='&notiket='+notiket+'&nokontrak='+nokontrak;
		param+='&nokendaraan='+nokendaraan+'&tanggalkirimpks='+tanggalkirimpks;
		param+='&kgkirim='+kgkirim+'&kgselisih='+kgselisih+'&kgtonbag='+kgtonbag+'&kgterima='+kgterima+'&kgterimaawal='+kgterimaawal;
		param+='&rpkg='+rpkg+'&rpjumlah='+rpjumlah;
		param+='&persentoleransi='+persentoleransi+'&kgtoleransi='+kgtoleransi+'&kgclaim='+kgclaim+'&rpkgclaim='+rpkgclaim+'&rpclaim='+rpclaim+'&currRow='+currRow;
		param+='&transportir='+transportir+'&noakundebet='+noakundebet+'&kodebarang='+kodebarang;
		tujuan = 'pmn_batransport_slave.php';
		post_response_text(tujuan, param, respog);
		// document.getElementById('row'+currRow).style.backgroundColor='';
		// document.getElementById('row'+currRow).style.backgroundColor='';
		// document.getElementById('row'+currRow).style.display='none';
    }
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					 document.getElementById('row'+currRow).style.backgroundColor='red';
					unlockScreen();
                } else {
					// document.getElementById('row'+currRow).style.display='none';
					document.getElementById('row'+currRow).style.backgroundColor='blue';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
						alert('Done');
						loaddatadt();
                    } else {
						loopsave(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}


function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function displaylist() {
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('notransaksisch').value='';
    document.getElementById('tanggalmulaisch').value='';
    document.getElementById('tanggalselesaisch').value='';
	loaddata(0);
}

function loaddata(num) {
	document.getElementById('listdata').style.display = 'block';
	header=document.getElementById('header');
	if (header) {
		document.getElementById('header').style.display='none';
		document.getElementById('detail').style.display='none';
	}
	notransaksi=document.getElementById('notransaksisch').value;
	tanggalmulai=document.getElementById('tanggalmulaisch').value;
	tanggalselesai=document.getElementById('tanggalselesaisch').value;
	param = 'method=loaddata&page=' + num;
	param += '&notransaksi=' + notransaksi;
	param += '&tanggalmulai=' + tanggalmulai+'&tanggalselesai=' + tanggalselesai;
	tujuan = 'pmn_batransport_slave.php';
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
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function editht(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'pmn_batransport_slave.php';
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
					document.getElementById('unit').value = ar[1];
					document.getElementById('tipe').value = ar[2];
					document.getElementById('nospk').value = ar[3];
					document.getElementById('tanggal').value = ar[4];
					document.getElementById('tanggalkirim1').value = ar[5];
					document.getElementById('tanggalkirim2').value = ar[6];
					document.getElementById('keterangan').value = ar[7];

					document.getElementById('notransaksi').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('tipe').disabled=true;
					document.getElementById('nospk').disabled=true;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('tanggalkirim1').disabled=true;
					document.getElementById('tanggalkirim2').disabled=true;
					// document.getElementById('keterangan').disabled=true;
					document.getElementById('saveht').disabled=true;
					document.getElementById('listdata').style.display='none';
					document.getElementById('header').style.display='block';
					document.getElementById('detail').style.display='block';
					getnospk(ar[3]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}


function posting(notransaksi) {
	param = 'method=posting';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'pmn_batransport_slave.php';
	alertify.confirm("Informasi","Anda yakin???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
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


function deleteht(notransaksi) {
	param = 'method=deleteht';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'pmn_batransport_slave.php';
	alertify.confirm("Informasi","Anda yakin???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function pdfclaim(notransaksi) {
    param = "method=pdfclaim&notransaksi="+notransaksi;
	alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_batransport_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
}



function pdf(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'pmn_batransport_slave.php';
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
					var notransaksi = ar[0];
					var unit = ar[1];
					var tipe = ar[2];
					var nospk = ar[3];
					var tanggal = ar[4];
					var tanggalkirim1 = ar[5];
					var tanggalkirim2 = ar[6];

					printpdf(notransaksi,unit,tipe,nospk,tanggal,tanggalkirim1,tanggalkirim2);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function printpdf(notransaksi,unit,tipe,nospk,tanggal,tanggalkirim1,tanggalkirim2) {
	ev = 'event';
	
	method = 'pdfbaru';
	param = 'unit=' + unit + '&tanggal=' + tanggal+ '&notransaksi=' + notransaksi+'&tanggalkirim1=' + tanggalkirim1 + '&tanggalkirim2=' + tanggalkirim2+'&tipe=' + tipe + '&nospk=' + nospk;
	param += '&method=' + method;
	param += '&print=pdf';
	// alert(param);
	// return;
	tujuan = 'pmn_batransport_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
				 	title = 'Report PDF';
                    tujuan = tujuan + "?" + param;
                    alertify.popuppdf(title,"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
                    
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}