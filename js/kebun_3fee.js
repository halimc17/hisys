function gettanggal(){
	periode = document.getElementById('periode').value;
	periodebyr = document.getElementById('periodebyr').value;
	
	param = 'method=gettanggal';
	param += '&periode=' + periode;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'kebun_slave_3fee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					e = con.responseText.split("##");
					document.getElementById('tgl').value = e[0];
					document.getElementById('tglsd').value = e[1];
					if(periodebyr=='3'){
						document.getElementById('tgl').disabled = false;
						document.getElementById('tglsd').disabled = false;
					}else{
						document.getElementById('tgl').disabled = true;
						document.getElementById('tglsd').disabled = true;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getjurnal(pt,notransaksi,tgl1,tgl2){
	// width    = '900';
	// height   = '400';
	// title    = "Detail Jurnal";
	// content = "<fieldset><div id=containerjurnal align=center style=\"width:880px;height:385px;overflow:auto;\"></div></fieldset>";
    // ev = 'event';
    // showDialog1(title, content, width, height, ev); 
	
	param = 'pt=' + pt;
	param += '&nojurnal=' + notransaksi;
	param += '&periode=' + tgl1;
	param += '&periode1=' + tgl2;
	param += '&tipelaporan=html';
	tujuan = 'keu_laporanJurnal.php';

	//alertify.popup("Jurnal","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_laporanJurnal.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('containerjurnal').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}


function gettotal(currRow){
	rp_muat = trim(document.getElementById('rp_muat_'+currRow).value);
	rp_angkut = trim(document.getElementById('rp_angkut_'+currRow).value);
	potonganrp = trim(document.getElementById('potonganrp_'+currRow).value);
	rp_muat=remove_comma_var(rp_muat);
	rp_angkut=remove_comma_var(rp_angkut);
	potonganrp=remove_comma_var(potonganrp);
	
	
	
	netto=(parseFloat(rp_muat)+parseFloat(rp_angkut))-parseFloat(potonganrp);
	document.getElementById('ttlrp_'+currRow).value=netto;
	
}

function deletedetail(kodeorg, periode, nospb) {
	param = 'method=deletedetail' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&nospb=' + nospb;
	tujuan = 'kebun_slave_3fee.php';
	if(confirm(' Anda yakin ')){
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					detail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetail() {
	kodeorg = document.getElementById('kodeorg').value;
	periode = document.getElementById('periode').value;
	periodebyr = document.getElementById('periodebyr').value;
	
	param = 'method=loaddatadetail';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'kebun_slave_3fee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveAll(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Simpan semua ???")) {
		savedetail(1, maxRow);
	}
}
function savedetail(currRow, maxRow) {	
	param = "";
	method    = trim(document.getElementById('method').value);
	kodeorg   = trim(document.getElementById('kodeorg').value);
	periode   = trim(document.getElementById('periode').value);
	divisi    = trim(document.getElementById('divisi').value);
	periodebyr= trim(document.getElementById('periodebyr').value);
	tgl       = trim(document.getElementById('tgl').value);
	tglsd     = trim(document.getElementById('tglsd').value);
	
	
	jenisH    = trim(document.getElementById('jenisH').value);
	
	tanggal   = trim(document.getElementById('tgl_'+currRow).innerHTML);
	nospb     = trim(document.getElementById('nospb_'+currRow).innerHTML);
	blok      = trim(document.getElementById('blok_'+currRow).innerHTML);
	kgwbdet   = trim(document.getElementById('kgwbbyr_'+currRow).value);
	kgwbpks   = trim(document.getElementById('kgwbpks_'+currRow).innerHTML);
	pkstujuan = document.getElementById('pkstujuan_'+currRow).innerHTML;
	jenisvhc  = document.getElementById('jenisvhc_'+currRow).value;
	
	namafee   = trim(document.getElementById('namafee'+currRow).value);
	jenisfee  = trim(document.getElementById('jenisfee'+currRow).value);
	jenisfeex = trim(document.getElementById('jenisfeex'+currRow).innerHTML);
	harga     = trim(document.getElementById('harga'+currRow).innerHTML);
	rpfee     = trim(document.getElementById('rpfee'+currRow).value);
	persen    = trim(document.getElementById('persen'+currRow).value);

	
	param += '&tgl=' + tgl;
	param += '&tglsd=' + tglsd;
	param += '&jenisfee=' + jenisfee;
	param += '&jenisfeex=' + jenisfeex;
	param += '&rpfee=' + rpfee;
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&nospb=' + nospb;
	param += '&divisi=' + divisi;
	param += '&blok=' + blok;
	param += '&kgwbdet=' + kgwbdet;
	param += '&tanggal=' + tanggal;
	param += '&periodebyr=' + periodebyr;
	param += '&pkstujuan=' + pkstujuan;
	param += '&kgwbpks=' + kgwbpks;
	param += '&method=' + method;
	param += '&jenisvhc=' + jenisvhc;
	param += '&baris=' + currRow;
	param += '&jenisH=' + jenisH;
	param += '&harga=' + harga;
	param += '&persen=' + persen;
	param += '&namafee=' + namafee;
	
	tujuan = 'kebun_slave_3fee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('tr_' + currRow).style.backgroundColor = 'red';
					//unlockScreen();
				} else {
					if (currRow != undefined) {
						document.getElementById('tr_' + currRow).style.backgroundColor = 'cyan';
					}
					document.getElementById('tr_' + currRow).style.display = 'none';
					currRow += 1;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						alert('Done');
						loaddata();
						//detail();
					} else {
						savedetail(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detail() {
	kodeorg   = document.getElementById('kodeorg').value;
	periode   = document.getElementById('periode').value;
	periodebyr= document.getElementById('periodebyr').value;
	tgl       = document.getElementById('tgl').value;
	tglsd     = trim(document.getElementById('tglsd').value);
	divisi    = document.getElementById('divisi').value;
	jenisH    = document.getElementById('jenisH').value;
	if (kodeorg == '' || periode == '' || divisi=='' || periodebyr=='' ) {
		alert('Kode Organisasi, Divisi, Periode Bulan Wajib diisi.');
		return;
	}
	//document.getElementById('tomboldetail').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('periode').disabled = true;
	document.getElementById('tgl').disabled = true;
	document.getElementById('tglsd').disabled = true;
	document.getElementById('divisi').disabled = true;
	document.getElementById('periodebyr').disabled = true;
	document.getElementById('jenisH').disabled = true;
	param = 'method=detail';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&tgl=' + tgl;
	param += '&tglsd=' + tglsd;
	param += '&divisi=' + divisi;
	param += '&periodebyr=' + periodebyr;
	param += '&jenisH=' + jenisH;
	tujuan = 'kebun_slave_3fee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getdetailspb() {
	kodeorg    = document.getElementById('kodeorg').value;
	tglheader  = document.getElementById('tgl').value;
	periode    = document.getElementById('periode').value;
	periodebyr = document.getElementById('periodebyr').value;
	spk        = document.getElementById('spk').value;
	nospb      = document.getElementById('nospb').value;
	jnskend    = document.getElementById('jeniskend').value;
	
	param = 'method=getdetailspb';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&spk=' + spk;
	param += '&tglheader=' + tglheader;
	param += '&nospb=' + nospb;
	param += '&jnskend=' + jnskend;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'kebun_slave_3fee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi = con.responseText.split("##");
					document.getElementById('notiket').value=trim(isi[0]);
					document.getElementById('sopir').value=trim(isi[1]);
					document.getElementById('divisi').value=trim(isi[2]);
					document.getElementById('tanggal').value=trim(isi[3]);
					document.getElementById('nopol').value=trim(isi[4]);
					document.getElementById('jjg').value=trim(isi[5]);
					document.getElementById('kgwb').value=trim(isi[6]);
					document.getElementById('inputharga').innerHTML=trim(isi[7]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function hitungrupiah(i){
	kgwb   =document.getElementById('kgwb_'+i).innerHTML;
	kgwbbyr=document.getElementById('kgwbbyr_'+i).value;
	harga  =document.getElementById('harga'+i).innerHTML;
	kgwb   =remove_comma_var(kgwb);
	kgwbbyr=remove_comma_var(kgwbbyr);
	harga  =remove_comma_var(harga);
	
	if(parseFloat(kgwbbyr)>parseFloat(kgwb)){
		alert("Kg tidak boleh lebih dari Kg Netto.");
		kgwbbyr = kgwb;
		document.getElementById('kgwbbyr_'+i).value=kgwbbyr;
	}
	
	rupiah = parseFloat(kgwbbyr)*parseFloat(harga);
	if(isNaN(rupiah)){rupiah=0;}
	document.getElementById('rpfee'+i).value=rupiah;
}

function getharga(no,maxrow,nospbold) {
	blok    = document.getElementById('blok_'+no).innerHTML;
	namafee = document.getElementById('namafee'+no).value;
	jenisfee= document.getElementById('jenisfeex'+no).innerHTML;
	jenisvhc= document.getElementById('jenisvhc_'+no).value;
	nospb   = document.getElementById('nospb_'+no).innerHTML;
	kgwb    = document.getElementById('kgwbbyr_'+no).value;
	ttlrow  = document.getElementById('jumlahrow').value;
	jlspb   = document.getElementsByName(nospbold+'[]');
	jlhspb  = jlspb.length;
	
	if(maxrow=='' || maxrow==undefined){
		maxrow=ttlrow;
	}
	
	nox = parseFloat(no);
	n = (parseFloat(no)+1);
	if(n<=ttlrow){
		// for (i = n; i <= ttlrow; i++) {
			// document.getElementById('jenisvhc_' + i).value = jenisvhc;
		// }
		
		for (i = 0; i <= jlhspb; i++) {
			if(jlspb[i]!=undefined && nospb==nospbold){				
				jlspb[i].value = jenisvhc;
			}
		}
	}
	
	
	param = 'method=getharga';
	param += '&blok=' + blok;
	param += '&namafee=' + namafee;
	param += '&jenisfee=' + jenisfee;
	param += '&jenisvhc=' + jenisvhc;
	param += '&kgwb=' + kgwb;
	tujuan = 'kebun_slave_3fee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi = con.responseText.split("##");
					harga = value=trim(isi[0]);
					total = value=trim(isi[1]);
					
					document.getElementById('harga'+no).innerHTML=harga;
					document.getElementById('rpfee'+no).value=total;
					
					no = parseFloat(no)+1;
                    if((no>maxrow) || (maxrow == undefined)){
						
					} else {
						if(nospb==nospbold){							
							getharga(no,maxrow,nospbold);
						}
                    }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deldetail(no){
	document.getElementById('rpfee'+no).value='';
	document.getElementById('tr_'+no).style.display='none';
}
/* 
function gethargabackup(no) {
	blok = document.getElementById('blok_'+no).value;
	tujuan = document.getElementById('tujuan_'+no).value;
	pkstujuan = document.getElementById('pkstujuan_'+no).value;
	jnskend = document.getElementById('jnskend_'+no).value;
	ttlrow = document.getElementById('jumlahrow').value;
	
	nox = parseFloat(no);
	n = (parseFloat(no)+1);
	if(n<=ttlrow){
		for (i = n; i <= ttlrow; i++) {
			document.getElementById('tujuan_' + i).value = tujuan;
			document.getElementById('jnskend_' + i).value = jnskend;
		}		
	}
	
	param = 'method=getharga';
	param += '&blok=' + blok;
	param += '&tujuan=' + tujuan;
	param += '&pkstujuan=' + pkstujuan;
	param += '&jnskend=' + jnskend;
	tujuan = 'kebun_slave_3fee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi = con.responseText.split("##");
					rpmuat = value=trim(isi[0]);
					rpangkut = value=trim(isi[1]);
					kegmuat = value=trim(isi[2]);
					kegangkut = value=trim(isi[3]);
					i = "";
					for (i = nox; i <= ttlrow; i++) {
						document.getElementById('harga_muat_'+i).value=rpmuat;
						document.getElementById('harga_angkut_'+i).value=rpangkut;
						document.getElementById('kegmuat_'+i).value=kegmuat;
						document.getElementById('kegangkut_'+i).value=kegangkut;
						
						kgwb = document.getElementById('kgwb_'+i).value;
						kgwb=remove_comma_var(kgwb);
						
						totrpmuat = parseFloat(rpmuat)*parseFloat(kgwb);
						totrpangkut = parseFloat(rpangkut)*parseFloat(kgwb);
						
						if(isNaN(totrpmuat)){totrpmuat=0;}
						if(isNaN(totrpangkut)){totrpangkut=0;}
						
						document.getElementById('rp_muat_'+i).value=numberFormat(totrpmuat);
						document.getElementById('rp_angkut_'+i).value=numberFormat(totrpangkut);
						document.getElementById('ttlrp_'+i).value=numberFormat(totrpmuat+totrpangkut);
					}		
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
 */
function getkg() {
	jjgpnn = document.getElementById('jjgpnn').value;
	bjr = document.getElementById('bjr').value;
	kg = parseFloat(jjgpnn) * parseFloat(bjr);
	kg = parseFloat(kg).toFixed(0);
	if (kg == 'NaN') {
		kg = 0;
	}
	document.getElementById('kgkebun').value = kg;
}
function excel(ev, tujuan) {
	unitexp = document.getElementById('unitexp').value;
	perexp = document.getElementById('perexp').value;
	judul = 'Report Ms.Excel';
	param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
	printFile(param, tujuan, judul, ev);
}
function add_new_data(){
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	//cancelHead();
	//cancel();
	cancel();
}
function form() {
	width = '';
	height = '';
	content = "<fieldset><div id=containerd align=center style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog1(title, content, width, height, ev);
}
function viewdetailx(kodeorg, periode,nospb) {
	width = '720';
	height = '';
	content = "<fieldset><div id=container align=center style=\"width:700px;max-height:400px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
	
	param = 'method=viewdetailx' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&nospb=' + nospb;
	tujuan = 'kebun_slave_3fee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewexcel(kodeorg, divisi,periode,periodebyr,tgl,tglsd,jenis){
	param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&divisi=' + divisi+ '&periodebyr=' + periodebyr+ '&jenis=' + jenis;
	param += '&tgl=' + tgl;
	param += '&tglsd=' + tglsd;
	tujuan = 'kebun_slave_3fee.php' + "?" + param;
	width = '';
	height = '';
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	//printFile(param,tujuan,title,ev);
	
	printnopopup(tujuan);

}

function html(kodeorg, divisi,periode,periodebyr,tgl,tglsd,jenis) {
	// width = '';
	// height = '';
	// content = "<fieldset><div id=containerd align=center style=\"max-height:400px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "Detail HTML";
	// showDialog1(title, content, width, height, ev);
	
	param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&divisi=' + divisi+ '&periodebyr=' + periodebyr+ '&jenis=' + jenis;
	param += '&tgl=' + tgl;
	param += '&tglsd=' + tglsd;
	tujuan = 'kebun_slave_3fee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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
function displayList() {
	document.getElementById('divsch').value = '';
	document.getElementById('tglsch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}
function edit(kodeorg,divisi,periode,periodebyr,kud,tgl,tglsd) {
	document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('periode').value = periode;
	document.getElementById('periodebyr').value = periodebyr;
	document.getElementById('divisi').value = divisi;
	document.getElementById('tgl').value = tgl;
	document.getElementById('tglsd').value = tglsd;
	setValue2('kodeorg',kodeorg);
	setValue2('periode',periode);
	setValue2('periodebyr',periodebyr);
	setValue2('divisi',divisi);
	
	if(kud==''){
		setValue2('jenisH','lainnya');
	}else{
		setValue2('jenisH','tempunak');
	}
	
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	detail();
}

function del(kodeorg,divisi,periode,periodebyr,kud,tgl,tglsd) {
	param = 'method=delete' + '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&divisi=' + divisi;
	param += '&periodebyr=' + periodebyr;
	param += '&kud=' + kud;
	param += '&tgl=' + tgl;
	param += '&tglsd=' + tglsd;
	tujuan = 'kebun_slave_3fee.php';
	if (confirm(' Anda yakin ingin menghapus nomor transaksi')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
					// document.getElementById('contain').innerHTML = con.responseText;
					// loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function posting(kodeorg,divisi,periode,periodebyr,kud,tgl,tglsd, numrow) {
	param = 'method=posting' + '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&divisi=' + divisi;
	param += '&periodebyr=' + periodebyr;
	param += '&kud=' + kud;
	param += '&tgl=' + tgl;
	param += '&tglsd=' + tglsd;
	tujuan = 'kebun_slave_3fee.php';
	if (confirm('Anda yakin ingin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function unposting(kodeorg,divisi,periode,periodebyr,kud,nojurnal,tgl,tglsd, numrow) {
	param = 'method=unposting' + '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&divisi=' + divisi;
	param += '&periodebyr=' + periodebyr;
	param += '&kud=' + kud;
	param += '&nojurnal=' + nojurnal;
	param += '&tgl=' + tgl;
	param += '&tglsd=' + tglsd;
	tujuan = 'kebun_slave_3fee.php';
	if (confirm('Anda yakin ingin unposting ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
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
	divsch   = document.getElementById('divsch').value;
	tglsch   = document.getElementById('tglsch').value;
	nospb    = document.getElementById('nospbcr').value;
	param = 'method=loaddata&page=' + page;
	param += '&nospb=' + nospb;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	if (tglsch != '') {
		param += '&tglsch=' + tglsch;
	}
	tujuan = 'kebun_slave_3fee.php';
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
function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	// document.getElementById('periode').value = '';
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('periodebyr').disabled = false;
	document.getElementById('periode').disabled = false;
	
	periodebyr = document.getElementById('periodebyr').value;
	if(periodebyr=='3'){
		document.getElementById('tgl').disabled = false;
		document.getElementById('tglsd').disabled = false;
	}else{
		document.getElementById('tgl').disabled = true;
		document.getElementById('tglsd').disabled = true;
	}
	
	document.getElementById('jenisH').disabled = false;
	document.getElementById('divisi').disabled = false;
	// document.getElementById('tgl').value = '';
	// document.getElementById('tglsd').value = '';
	// setValue2('periode',null);
	// setValue2('periodebyr',null);
	// setValue2('kodeorg',null);
	// setValue2('divisi',null);
	// setValue2('jenisH',null);
}

function getdata() {
	blok = document.getElementById('blok').value;
	tgl = document.getElementById('tgl').value;
	param = 'method=getdata' + '&blok=' + blok + '&tgl=' + tgl;
	tujuan = 'kebun_slave_3fee.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//
					//
					isi = con.responseText.split("##");
					document.getElementById('thntnm').value = trim(isi[0]);
					document.getElementById('luasaresta').value = trim(isi[1]);
					document.getElementById('bjr').value = trim(isi[2]);
					getkg();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function cleardetail() {
	document.getElementById('blok').value = '';
	document.getElementById('blok').disabled = false;
	document.getElementById('thntnm').value = '';
	document.getElementById('luasaresta').value = '';
	document.getElementById('luaspnn').value = '';
	document.getElementById('tk').value = '';
	document.getElementById('jjgpnn').value = '';
	document.getElementById('afkirjjg').value = '';
	document.getElementById('afkirket').value = '';
	document.getElementById('bjr').value = '';
	document.getElementById('kgkebun').value = '';
}


function editdetail(divisi,tanggal,blok,tahuntanam,luasproduksi,luaspanen,tenagakerja,jjgpanen,bjr,kgkebun,jjgafkir,keterangan){
	document.getElementById('div').value = divisi;
	document.getElementById('tgl').value = tanggal;
	document.getElementById('blok').value = blok;
	document.getElementById('thntnm').value = tahuntanam;
	document.getElementById('luasaresta').value = luasproduksi;
	document.getElementById('luaspnn').value = luaspanen;
	document.getElementById('tk').value = tenagakerja;
	document.getElementById('jjgpnn').value = jjgpanen;
	document.getElementById('bjr').value = bjr;
	document.getElementById('kgkebun').value = kgkebun;
	document.getElementById('afkirjjg').value = jjgafkir;
	document.getElementById('afkirket').value = keterangan;
	document.getElementById('method').value = 'updatedetail';
}


function viewdetailbapp(notransaksi,kodeorg,tipeview,ev){
	width = '';
	height = '';
	content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	title = "";
	showDialog1(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
	document.getElementById('dynamic1').style.display = '';
	
	var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg+'&tipeview='+tipeview;
	
	param += '&method=rekapbapp';
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contRekap').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function view(nopengajuan,notransaksi,kodeorg,tanggal,termin,numRow,ev,tipe){
	param = "method=preview&tipe="+tipe+"&notransaksi="+notransaksi+"&nopengajuan="+nopengajuan+"&kodeorg="+kodeorg+"&tanggal="+tanggal+"&termin="+termin;
    width = '';
    height = '';
    content = "<fieldset><div id=contviewx style=\"height:400px;width:700px;overflow:auto;\"></div></fieldset>";
    title = "View";
    showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	// document.getElementById('dynamic2').style.right = (80) + 'px';
	document.getElementById('dynamic2').style.display = '';
	
    tujuan = 'log_slave_realisasispkx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('contviewx').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function showhidedetail(rows,total){
	for(var i = 1; i <= total; i++) {
		key = document.getElementById('tr_dt2_'+rows+'_'+i).style.display;
		if(key=='none'){
			document.getElementById('tr_dt2_'+rows+'_'+i).style.display='';
		}else{
			document.getElementById('tr_dt2_'+rows+'_'+i).style.display='none';
		}
    }
}

function UploadFile(notransaksi,tanggal,termin,numRow,ev,nopengajuan) {
	title = "List File";
	formajukan(title,ev);
	param = 'method=UploadFile' + '&notransaksi=' + notransaksi+ '&tanggal=' + tanggal+ '&termin=' + termin+ '&nopengajuan=' + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containervoid').innerHTML = con.responseText;
					loadfiles(notransaksi,termin,nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function formajukan(title,ev) {
	width = '';
	height = '';
	content = "<div id=containervoid ></div>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	// document.getElementById('dynamic2').style.right = (80) + 'px';
	document.getElementById('dynamic2').style.display = '';
}


function loadfiles(notransaksi,termin,nopengajuan) {
	param = 'method=loadfiles&notransaksi=' + notransaksi+ '&termin=' + termin+ '&nopengajuan=' + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('notransaksi').innerHTML;
	var pengajuanspk = document.getElementById('pengajuanspk').innerHTML;
	var tanggal = document.getElementById('tanggal').innerHTML;
	var termin = document.getElementById('terminup').innerHTML;
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	formdata.append("pengajuanspk", pengajuanspk);
	formdata.append("kriteriaefil", kriteriaefil);
	formdata.append("termin", termin);
	formdata.append("tanggal", tanggal);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	if (notransaksi == "" || pengajuanspk=="") {
		alert("warning : Nomor Transaksi di Perlukan !");
		return false;
	}
	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	busy_on();
	con.open("POST", "log_slave_realisasispkx.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('btnsubmit').disabled=false;
				} else {
					//=== Success Response
					alert('Uploaded Success.');
					document.getElementById('btnsubmit').disabled=false;
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,termin,pengajuanspk);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notransaksi, namafile,termin,nopengajuan) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	param += "&nopengajuan=" + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(notransaksi,termin,nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function form_ajukan(kodeorg,notransaksi, tanggal,termin, numrow,jlhrealisasi) {
	if(jlhrealisasi==0){
		alert("Gagal, Jumlah Realisasi masih 0");
		return false;
	}
	width = '300';
	height = '';
	content = "<fieldset style=width:280px><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:180px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog5(title, content, width, height, ev);
	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi + '&tanggal=' + tanggal + '&termin=' + termin + '&numrow=' + numrow+ '&kodeorg=' + kodeorg;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containeraju').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukan(ev) {
	kepada = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	tanggal = document.getElementById('tgljurnal').value;
	unit = document.getElementById('unitdt2').value;
	termin = document.getElementById('termin_aju').innerHTML;
	nopengajuan = document.getElementById('nopengajuan_aju').innerHTML;
	numrow = document.getElementById('numrow').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	param += "&tanggal="+tanggal;
	param += "&termin="+termin;
	param += "&nopengajuan="+nopengajuan;
	
	if (kepada == '') {
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					tipeview='viewhtml';
					viewdetail(notransaksi,unit,tipeview,ev)
					closeDialog5();
					alert('Sucses');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewdetail(notransaksi,kodeorg,tipeview,ev){
	width = '';
	height = '';
	content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	title = "";
	showDialog1(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
	document.getElementById('dynamic1').style.display = '';
	
	var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg+'&tipeview='+tipeview;
	
	param += '&method=rekapbapp';
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contRekap').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getapprovaldetail(nopengajuan,kodeorg,ev) {
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Detail Approval</legend><div id=contapp style=\"overflow:auto;width:100%;\"></div></fieldset>";
	title = "";
	showDialog4(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic4').style.top = pos[1] + 'px';
	// document.getElementById('dynamic4').style.left = (pos[0]-width) + 'px';
	document.getElementById('dynamic4').style.display = '';
	param = 'method=getapprovaldetail' + '&nopengajuan=' + nopengajuan + '&kodeorg=' + kodeorg;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contapp').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}