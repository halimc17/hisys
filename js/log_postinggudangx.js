function getkodegudang(){
	pt     = document.getElementById('kodeorgsch').value;
	
	param = 'method=getkodegudang';
	param += '&pt=' + pt;
	tujuan = 'log_slave_postinggudangx.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					document.getElementById('kodegdgsch').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function previewDocument(tipe, notransaksi, ev) {
	param = 'notransaksi=' + notransaksi;
	switch (tipe) {
	case 1:
		tujuan = 'log_slave_print_bapb_pdf.php?' + param;
		break;
	case 2:
		tujuan = 'log_slave_print_retur_pdf.php?' + param;
		break;
	case 3:
		tujuan = 'log_slave_print_received_pdf.php?' + param;
		break;
	case 5:
		tujuan = 'log_slave_print_bast_pdf.php?' + param;
		break;
	case 7:
		tujuan = 'log_slave_print_mutasi_pdf.php?' + param;
		break;
	default:
		alertify.alert("Unknown document type");
	}

	title = notransaksi;
	// width = '900';
	// height = '400';
	// content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog2(title, content, width, height, ev);
	
	alertify.popuppdf(title,"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}


function getlistnotif(sumber){
	pt     = document.getElementById('kodeorgsch').value;
	gudang = document.getElementById('kodegdgsch').value;
	nodok  = document.getElementById('nodoksch').value;
	nopo   = document.getElementById('noposch').value;
	asal   = document.getElementById('asalsch').value;
	noref  = document.getElementById('noreffsch').value;
	tanggal= document.getElementById('tanggalsch').value;
	tipe   = document.getElementById('tipesch').value;
	
	param = 'method=getlistnotif';
	param += '&pt=' + pt + '&gudang=' + gudang;
	param += '&nodok=' + nodok + '&nopo=' + nopo;
	param += '&asal=' + asal + '&noref=' + noref;
	param += '&tanggal=' + tanggal + '&tipe=' + tipe;
	param += '&sumber=' + sumber;
	
	tujuan = 'log_slave_postinggudangx.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					if(parseFloat(con.responseText)>'0'){						
						document.getElementById('countnotif').style.display = '';
						document.getElementById('countnotif').innerHTML = con.responseText;
					}else{
						document.getElementById('countnotif').style.display = 'none';
					}
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}



function add_new_data(){
	document.getElementById('contdetail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	
	document.getElementById('tombolcari').setAttribute('onclick','listposting()');
	document.getElementById('tombolbatalcari').setAttribute('onclick','batalcaripost()');
	document.getElementById('kodeorgsch').setAttribute('onchange','getkodegudang()');
	document.getElementById('kodegdgsch').setAttribute('onchange','listposting()');
	document.getElementById('nodoksch').setAttribute('onkeypress','enterkey(event,listposting())');
	document.getElementById('noposch').setAttribute('onkeypress','enterkey(event,listposting())');
	document.getElementById('asalsch').setAttribute('onchange','listposting()');
	document.getElementById('noreffsch').setAttribute('onkeypress','enterkey(event,listposting())');
	document.getElementById('tanggalsch').setAttribute('onchange','listposting()');
	document.getElementById('tipesch').setAttribute('onchange','listposting()');
	
	batalcaripost();
	listposting();
}

function displayList() {
	document.getElementById('listData').style.display = 'block';
	document.getElementById('contdetail').style.display = 'none';
	
	document.getElementById('tombolcari').setAttribute('onclick','loaddata(0)');
	document.getElementById('tombolbatalcari').setAttribute('onclick','batalcari()');
	
	document.getElementById('kodeorgsch').setAttribute('onchange','getkodegudang()');
	document.getElementById('kodegdgsch').setAttribute('onchange','loaddata()');
	document.getElementById('nodoksch').setAttribute('onkeypress','enterkey(event,loaddata())');
	document.getElementById('noposch').setAttribute('onkeypress','enterkey(event,loaddata())');
	document.getElementById('asalsch').setAttribute('onchange','loaddata()');
	document.getElementById('noreffsch').setAttribute('onkeypress','enterkey(event,loaddata())');
	document.getElementById('tanggalsch').setAttribute('onchange','loaddata()');
	document.getElementById('tipesch').setAttribute('onchange','loaddata()');
	
	batalcari();
	loaddata(0);
}

function getPagePost() {
	pg = document.getElementById('pageslog');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	listposting(paged);
}

function listposting(page){
	pt     = document.getElementById('kodeorgsch').value;
	gudang = document.getElementById('kodegdgsch').value;
	nodok  = document.getElementById('nodoksch').value;
	nopo   = document.getElementById('noposch').value;
	asal   = document.getElementById('asalsch').value;
	noref  = document.getElementById('noreffsch').value;
	tanggal= document.getElementById('tanggalsch').value;
	tipe   = document.getElementById('tipesch').value;
	
	param = 'method=listposting';
	param += '&page=' + page;
	param += '&pt=' + pt + '&gudang=' + gudang;
	param += '&nodok=' + nodok + '&nopo=' + nopo;
	param += '&asal=' + asal + '&noref=' + noref;
	param += '&tanggal=' + tanggal + '&tipe=' + tipe;
	tujuan = 'log_slave_postinggudangx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contdetail').innerHTML = con.responseText;
					getlistnotif('post');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showposting(notransaksi,no) {
	// form();
	param = 'method=showposting'  + '&notransaksi=' + notransaksi;
	tujuan = 'log_slave_postinggudangx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					getmark(no);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getmark(no){
	namacol = document.getElementsByName('baris[]');
	for (var r = 0; r < namacol.length; r++) {
		namacol[r].style.backgroundColor="";
	}
	
	dis = document.getElementById('row_'+no).style.backgroundColor;
	if(dis!=''){
		document.getElementById('row_'+no).style.backgroundColor="";		
	}else{		
		document.getElementById('row_'+no).style.backgroundColor="cyan";
	}
	
}

function batalpost(){
	// // closeDialog();
	alertify.popup().destroy();
}

function posting(notransaksi) {
	param = 'method=posting' + '&notransaksi=' + notransaksi;
	tujuan = 'log_slave_save_postinggudangx.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					// closeDialog();
					listposting();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postingall(maxRow){
	if(maxRow =='' || maxRow ==0){
        alertify.alert('Data tidak ditemukan, proses dibatalkan.');
        return;
    }
	if(confirm("Anda yakin ???")){
		savedetail(1,maxRow);
	}
}

function savedetail(currRow,maxRow){
	notransaksi=document.getElementById('notran'+currRow).innerHTML;
	param = "";
	param += "&notransaksi="+notransaksi;
	param += '&method=posting';
	tujuan='log_slave_save_postinggudangx.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('btnpostall').disabled=true;
	if(currRow!=undefined){
		document.getElementById('row_' + currRow).style.backgroundColor='cyan';
	}
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
					if(currRow!=undefined){
						document.getElementById('row_' + currRow).style.backgroundColor = 'red';
					}
                    unlockScreen();
                } else {
					if(currRow != undefined){
						document.getElementById('row_' + currRow).style.backgroundColor='';
					}
					currRow+=1;
                    if((currRow>maxRow) || (maxRow == undefined)){
						listposting();
						document.getElementById('btnpostall').disabled=false;
					} else {
						savedetail(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function form() {
	width = '720';
	height = '';
	content = "<fieldset><div id=containerd align=center style=\"width:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
}
function html(tahun,kodeorg) {
	// form();
	param = 'method=html'  + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'log_slave_postinggudangx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
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
function loaddata(page) {
	pt     = document.getElementById('kodeorgsch').value;
	gudang = document.getElementById('kodegdgsch').value;
	nodok  = document.getElementById('nodoksch').value;
	nopo   = document.getElementById('noposch').value;
	asal   = document.getElementById('asalsch').value;
	noref  = document.getElementById('noreffsch').value;
	tanggal= document.getElementById('tanggalsch').value;
	tipe   = document.getElementById('tipesch').value;
	
	param = 'method=loaddata&page=' + page;
	param += '&pt=' + pt + '&gudang=' + gudang;
	param += '&nodok=' + nodok + '&nopo=' + nopo;
	param += '&asal=' + asal + '&noref=' + noref;
	param += '&tanggal=' + tanggal + '&tipe=' + tipe;
	tujuan = 'log_slave_postinggudangx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
					getlistnotif('load');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batalcari() {
	document.getElementById('kodeorgsch').value='';
	document.getElementById('kodegdgsch').value='';
	document.getElementById('nodoksch').value='';
	document.getElementById('noposch').value='';
	document.getElementById('asalsch').value='';
	document.getElementById('noreffsch').value='';
	document.getElementById('tanggalsch').value='';
	document.getElementById('tipesch').value='';
	loaddata();
}

function batalcaripost() {
	document.getElementById('kodeorgsch').value='';
	document.getElementById('kodegdgsch').value='';
	document.getElementById('nodoksch').value='';
	document.getElementById('noposch').value='';
	document.getElementById('asalsch').value='';
	document.getElementById('noreffsch').value='';
	document.getElementById('tanggalsch').value='';
	document.getElementById('tipesch').value='';
	listposting();
}