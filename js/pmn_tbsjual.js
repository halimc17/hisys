
function excel(notransaksi) {
	param = 'method=excel' + '&notransaksi=' + notransaksi;
	tujuan='pmn_tbsjual_slave.php';
	tujuan = tujuan+'?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog5(title, content, width, height, 'event');
}


function posting(notransaksi) {
	param='method=posting'+'&notransaksi='+notransaksi;
	tujuan = 'pmn_tbsjual_slave.php';
	alertify.confirm("Informasi","Posting Transaksi : "+notransaksi+" ???",
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
					alertify.alert('Informasi',con.responseText);
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


function editht(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'pmn_tbsjual_slave.php';
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
					document.getElementById('tanggal').value = ar[2];
					document.getElementById('kodecustomer').value = ar[3];
					document.getElementById('nokontrak').value = ar[4];
					document.getElementById('tanggaltbs1').value = ar[5];
					document.getElementById('tanggaltbs2').value = ar[6];
					document.getElementById('persenppn').value = ar[7];
					document.getElementById('persenpph').value = ar[8];
					document.getElementById('keteranganht').value = ar[9];
					

					document.getElementById('notransaksi').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('kodecustomer').disabled=true;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('tanggaltbs1').disabled=true;
					document.getElementById('tanggaltbs2').disabled=true;
					document.getElementById('saveht').disabled=true;
					document.getElementById('nokontrak').disabled=true;
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

function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}


function loaddata(num) {
	notransaksisch=document.getElementById('notransaksisch').value;
	tanggalmulaisch=document.getElementById('tanggalmulaisch').value;
	tanggalselesaisch=document.getElementById('tanggalselesaisch').value;
	nokontraksch=document.getElementById('nokontraksch').value;
	kodecustomersch=document.getElementById('kodecustomersch').value;
	param = 'method=loaddata&page=' + num;
	param += '&notransaksisch=' + notransaksisch;
	param += '&tanggalmulaisch=' + tanggalmulaisch+'&tanggalselesaisch=' + tanggalselesaisch;
	param += '&nokontraksch=' + nokontraksch+'&kodecustomersch=' + kodecustomersch;
	tujuan = 'pmn_tbsjual_slave.php';
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




function displaylist() {
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display='none';
	loaddata(0);
}

function newdata(){
	cancelht();
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	document.getElementById('detail').style.display='none';
}


function cancelht(){
	document.getElementById('notransaksisch').value='';
    document.getElementById('tanggalmulaisch').value='';
    document.getElementById('tanggalselesaisch').value='';
    document.getElementById('kodecustomersch').value='';
    document.getElementById('nokontraksch').value='';
	document.getElementById('unit').disabled=false;
	document.getElementById('kodecustomer').disabled=false;
	document.getElementById('tanggal').disabled=false;
	document.getElementById('tanggaltbs1').disabled=false;
	document.getElementById('tanggaltbs2').disabled=false;
	document.getElementById('persenppn').disabled=false;
	document.getElementById('persenpph').disabled=false;
	document.getElementById('keteranganht').disabled=false;
	document.getElementById('nokontrak').disabled=false;
	document.getElementById('saveht').disabled=false;
	
	document.getElementById('notransaksi').value='';
	document.getElementById('nokontrak').value='';
	document.getElementById('unit').value='';
	document.getElementById('kodecustomer').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tanggaltbs1').value='';
	document.getElementById('tanggaltbs2').value='';
	document.getElementById('persenppn').value='';
	document.getElementById('persenpph').value='';
	document.getElementById('keteranganht').value='';
	document.getElementById('detail').style.display='none';
}


function deleteht(notransaksi){
	param = 'method=deleteht';
	param+='&notransaksi='+notransaksi;
	tujuan = 'pmn_tbsjual_slave.php';
	alertify.confirm("Informasi","Hapus transaksi : "+notransaksi+" ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	// post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
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



function saveht() {
	
	param='';
	tanggal= document.getElementById('tanggal').value;
	unit= document.getElementById('unit').value;
	nokontrak= document.getElementById('nokontrak').value;
	kodecustomer= document.getElementById('kodecustomer').value;
	notransaksi= document.getElementById('notransaksi').value;
	
	if(tanggal==''){
		alert('Tanggal tidak boleh kosong');return;
	}if(nokontrak==''){
		alert('Nokontrak tidak boleh kosong');return;
	}if(kodecustomer==''){
		alert('Customer tidak boleh kosong');return;
	}
	if(unit==''){
		alert('unit tidak boleh kosong');return;
	}
	
	method = 'saveht';
	param += '&unit=' + unit + '&tanggal=' + tanggal+ '&kodecustomer=' + kodecustomer+ '&nokontrak=' + nokontrak+ '&notransaksi=' + notransaksi;
	param += '&method=' + method;
	// alert(param);
	tujuan = 'pmn_tbsjual_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('notransaksi').value=con.responseText;
					document.getElementById('detail').style.display='block';
					
					// document.getElementById('saveht').disabled=true;
					document.getElementById('detail').style.display='block';
					document.getElementById('unit').disabled=true;
					document.getElementById('kodecustomer').disabled=true;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('tanggaltbs1').disabled=true;
					document.getElementById('tanggaltbs2').disabled=true;
					document.getElementById('persenppn').disabled=true;
					document.getElementById('persenpph').disabled=true;
					document.getElementById('keteranganht').disabled=true;
					document.getElementById('nokontrak').disabled=true;
					document.getElementById('notransaksi').disabled=true;
					loaddatadt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
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
	unit=trim(document.getElementById('unit').value);
	kodecustomer=trim(document.getElementById('kodecustomer').value);
    tanggal=trim(document.getElementById('tanggal').value);
    tanggaltbs1=trim(document.getElementById('tanggaltbs1').value);
    tanggaltbs2=trim(document.getElementById('tanggaltbs2').value);
    persenppn=trim(document.getElementById('persenppn').value);
    persenpph=trim(document.getElementById('persenpph').value);
	keteranganht=trim(document.getElementById('keteranganht').value);
	nokontrak	= document.getElementById('nokontrak').value;
	
	notiket=trim(document.getElementById('notiket'+currRow).innerHTML);
	nospb=trim(document.getElementById('nospb'+currRow).innerHTML);
	spblct=trim(document.getElementById('spblct'+currRow).innerHTML);
	nokendaraan=trim(document.getElementById('nokendaraan'+currRow).innerHTML);
	tanggalpks=trim(document.getElementById('tanggalpks'+currRow).innerHTML);
	tanggalspb=trim(document.getElementById('tanggalspb'+currRow).innerHTML);
	kgmasuk=trim(document.getElementById('kgmasuk'+currRow).innerHTML);
	kgkeluar=trim(document.getElementById('kgkeluar'+currRow).innerHTML);
	kgbruto=trim(document.getElementById('kgbruto'+currRow).innerHTML);
	kgpotongan=trim(document.getElementById('kgpotongan'+currRow).innerHTML);
	kgnetto=trim(document.getElementById('kgnetto'+currRow).innerHTML);
	jjg=trim(document.getElementById('jjg'+currRow).innerHTML);
	bjr=trim(document.getElementById('bjr'+currRow).innerHTML);
	blok=trim(document.getElementById('blok'+currRow).innerHTML);
	tahuntanam=trim(document.getElementById('tahuntanam'+currRow).innerHTML);
	rpkg=trim(document.getElementById('rpkg'+currRow).innerHTML);
	totalrp =trim(document.getElementById('totalrp'+currRow).innerHTML);
	
	intiplasma =trim(document.getElementById('intiplasma'+currRow).innerHTML);
	statusblok =trim(document.getElementById('statusblok'+currRow).innerHTML);

	param+='&method=savedt';
	param+='&notransaksi='+notransaksi+'&unit='+unit+'&kodecustomer='+kodecustomer+'&tanggal='+tanggal+'&tanggaltbs1='+tanggaltbs1+'&tanggaltbs2='+tanggaltbs2+'&persenppn='+persenppn+'&persenpph='+persenpph+'&keteranganht='+keteranganht+'&nokontrak='+nokontrak+'&spblct='+spblct;
	param+='&notiket='+notiket+'&nospb='+nospb+'&nokendaraan='+nokendaraan+'&tanggalpks='+tanggalpks+'&tanggalspb='+tanggalspb+'&kgmasuk='+kgmasuk+'&kgkeluar='+kgkeluar+'&kgbruto='+kgbruto+'&kgpotongan='+kgpotongan+'&kgnetto='+kgnetto+'&jjg='+jjg+'&bjr='+bjr+'&blok='+blok+'&tahuntanam='+tahuntanam+'&rpkg='+rpkg+'&totalrp='+totalrp;
	param+='&intiplasma='+intiplasma+'&statusblok='+statusblok;
	param+='&currRow='+currRow;
	
	tujuan = 'pmn_tbsjual_slave.php';
	
	post_response_text(tujuan, param, respog);
	
	function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					 document.getElementById('row'+currRow).style.backgroundColor='red';
					// unlockScreen();
                } else {
					document.getElementById('row'+currRow).style.display='none';
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


function loaddatadt() {
	notransaksi= document.getElementById('notransaksi').value;
	nokontrak=document.getElementById('nokontrak').value;
	unit= document.getElementById('unit').value;
	tanggaltbs1= document.getElementById('tanggaltbs1').value;
	tanggaltbs2= document.getElementById('tanggaltbs2').value;
	kodecustomer= document.getElementById('kodecustomer').value;
	param = 'method=loaddatadt';
	param+='&notransaksi='+notransaksi+'&unit='+unit+'&nokontrak='+nokontrak;
	param+='&tanggaltbs1='+tanggaltbs1+'&tanggaltbs2='+tanggaltbs2+'&kodecustomer='+kodecustomer;
	// alert(param);
	tujuan = 'pmn_tbsjual_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listdatadt').innerHTML = con.responseText;
					 leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
