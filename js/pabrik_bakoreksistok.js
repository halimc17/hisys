
function cancelht(){
	document.getElementById('unit').disabled=false;
	document.getElementById('tanggal').disabled=false;
	document.getElementById('jm').value='00';
	document.getElementById('tanggal').value='';
	document.getElementById('mn').value='00';
	document.getElementById('notransaksi').value='';
	document.getElementById('kodept').value='';
	document.getElementById('kodebarang').value='';
	document.getElementById('kodetangki').value='';
	document.getElementById('keteranganht').value='';
	document.getElementById('tipe').value='';
	document.getElementById('jumlah').value='0';
	document.getElementById('method').value ='insert';
	
	// param += '&=' + unitsch+'&kodeptsch=' + kodeptsch+'&tipesch=' + tipesch+'&kodebarangsch=' + kodebarangsch+'&=' + kodetangkisch;
	document.getElementById('kodeptsch').value='';
	document.getElementById('tipesch').value='';
	document.getElementById('kodebarangsch').value='';
	document.getElementById('kodetangkisch').value='';
	
}

function editht(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'pabrik_bakoreksistok_slave.php';
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
					document.getElementById('kodept').value = ar[2];
					document.getElementById('tipe').value = ar[3];
					document.getElementById('kodebarang').value = ar[4];
					document.getElementById('kodetangki').value = ar[5];
					document.getElementById('tanggal').value = ar[6];
					document.getElementById('jm').value = ar[7];
					document.getElementById('mn').value = ar[8];
					document.getElementById('jumlah').value = ar[9];
					document.getElementById('keteranganht').value = ar[10];
					document.getElementById('notransaksi').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('listdata').style.display='none';
					document.getElementById('header').style.display='block';
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

function displaylist() {
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('notransaksisch').value='';
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



function loaddata(num) {
	notransaksisch=document.getElementById('notransaksisch').value;
	tanggalmulaisch=document.getElementById('tanggalmulaisch').value;
	tanggalselesaisch=document.getElementById('tanggalselesaisch').value;
	unitsch=document.getElementById('unitsch').value;
	kodeptsch=document.getElementById('kodeptsch').value;
	tipesch=document.getElementById('tipesch').value;
	kodebarangsch=document.getElementById('kodebarangsch').value;
	kodetangkisch=document.getElementById('kodetangkisch').value;
	param = 'method=loaddata&page=' + num;
	param += '&notransaksisch=' + notransaksisch+'&tanggalselesaisch=' + tanggalselesaisch+'&tanggalmulaisch=' + tanggalmulaisch;
	param += '&unitsch=' + unitsch+'&kodeptsch=' + kodeptsch+'&tipesch=' + tipesch+'&kodebarangsch=' + kodebarangsch+'&kodetangkisch=' + kodetangkisch;
	tujuan = 'pabrik_bakoreksistok_slave.php';
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
	cancelht();
	// document.getElementById('detailhead').style.display='none';
}

function posting(notransaksi) {
	param='method=posting'+'&notransaksi='+notransaksi;
	tujuan = 'pabrik_bakoreksistok_slave.php';
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
	tujuan = 'pabrik_bakoreksistok_slave.php';
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

// function pdf(notransaksi) {
	// param = 'method=pdf' + '&notransaksi=' + notransaksi;
	// tujuan='pabrik_bakoreksistok_slave.php';
	// tujuan = tujuan+'?' + param;
	// content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	// width = '820';
	// height = '500';
	// title = "";
	// showDialog5(title, content, width, height, 'event');
// }


function pdf(notransaksi) {
	param = 'method=pdf' + '&notransaksi=' + notransaksi;
	alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pabrik_bakoreksistok_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
}




function saveht() {
	param = "";
	
	
	notransaksi= document.getElementById('notransaksi').value;
	keteranganht= document.getElementById('keteranganht').value;
	jm= document.getElementById('jm').value;
	tanggal= document.getElementById('tanggal').value;
	mn= document.getElementById('mn').value;
	unit= document.getElementById('unit').value;
	kodebarang= document.getElementById('kodebarang').value;
	kodept= document.getElementById('kodept').value;
	kodetangki= document.getElementById('kodetangki').value;
	tipe= document.getElementById('tipe').value;
	jumlah= document.getElementById('jumlah').value;
		jumlah=remove_comma_var(jumlah);
	method = document.getElementById('method').value;
	
	param+='&notransaksi='+notransaksi+'&keteranganht='+keteranganht;
	param+='&jm='+jm+'&tanggal='+tanggal+'&mn='+mn+'&kodetangki='+kodetangki+'&jumlah='+jumlah;
	param+='&unit='+unit+'&kodebarang='+kodebarang+'&kodept='+kodept+'&tipe='+tipe;
	param += '&method=' + method;
	
	if(jumlah<=0){
		alert('Jumlah dibawah 0');return;
	}
	tujuan = 'pabrik_bakoreksistok_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cancelht();
					// document.getElementById('notransaksi').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}






