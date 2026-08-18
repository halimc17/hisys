function getjumlah(sumber){
	awal = document.getElementById('kmhm_awal').value;
	akhir= document.getElementById('kmhm_akhir').value;
	jlhhm= document.getElementById('jlhhm').value;
	
	if(sumber=='jumlah'){
		hmakhir = parseFloat(awal)+parseFloat(jlhhm);
		document.getElementById('kmhm_akhir').value=hmakhir;
	}else{		
		jumlah = parseFloat(akhir)-parseFloat(awal);
		document.getElementById('jlhhm').value=jumlah;
	}	
}

function tutupdtlpre(){
	document.getElementById('contdetailpremi').style.display='none';
	document.getElementById('tomboldetailpremi').innerHTML='Rincian';
	e = document.getElementById('tomboldetailpremi');
	e.setAttribute("onclick","detailpremi()");
}

function detailpremi(){
	document.getElementById('contdetailpremi').style.display='block';
	document.getElementById('tomboldetailpremi').innerHTML='Close';
	e = document.getElementById('tomboldetailpremi');
	e.setAttribute("onclick","tutupdtlpre()");
	
	kodetraksi = document.getElementById('kodetraksi').value;
	posisi = document.getElementById('posisi').value;
	notrans = document.getElementById('no_trans_opt').value;
	tglTrans = document.getElementById('tgl_pekerjaan').value;
	kar = document.getElementById('kode_karyawan').value;
	jenisvhc = document.getElementById('jns_vhc').value;
	param = 'proses=getDetailPremi' + '&tglTrans=' + tglTrans;
	param += '&notransaksi=' + notrans;
	param += '&posisi=' + posisi;
	param += '&kodetraksi=' + kodetraksi;
	param += '&kar=' + kar;
	param += '&jenisvhc=' + jenisvhc;
	param += '&jenis=detail';
	
	tujuan = 'vhc_detailPekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containDetailOperator').innerHTML = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPremi() {

	kodetraksi = document.getElementById('kodetraksi').value;
	posisi = document.getElementById('posisi').value;
	notrans = document.getElementById('no_trans_opt').value;
	tglTrans = document.getElementById('tgl_pekerjaan').value;
	kar = document.getElementById('kode_karyawan').value;
	jenisvhc = document.getElementById('jns_vhc').value;
	param = 'proses=getPremi' + '&tglTrans=' + tglTrans;
	param += '&notransaksi=' + notrans;
	param += '&posisi=' + posisi;
	param += '&kodetraksi=' + kodetraksi;
	param += '&kar=' + kar;
	param += '&jenisvhc=' + jenisvhc;
	tujuan = 'vhc_detailPekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('prmiOprt').value = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function get_kd(notrans) {
	//alert("test");
	if (notrans == '') {
		jns_id = document.getElementById('jns_vhc').value;
		traksi_id = document.getElementById('kodetraksi').value;
		strAll = 'jns_id=' + jns_id + '&traksi_id=' + traksi_id + '&proses=getKodeVhc';
	} else {
		/*jnsid=jns;
		kd_vhc=kdvhc;*/
		strAll = 'no_trans=' + notrans;
		strAll += '&proses=getKodeVhc';

	}
	//alert(param);
	param = strAll;
	//alert(param);
	tujuan = 'vhc_slave_save_pekerjaan.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('kde_vhc').innerHTML = data[0];
					load_data_pekerjaan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}


function getkodekend() {
	kodetraksi = document.getElementById('kodetraksi').value;
	
	param = 'proses=getkodekend';
	param += '&kodetraksi=' + kodetraksi;
	
	tujuan = 'vhc_slave_save_pekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi = con.responseText.split("##");
					document.getElementById('jns_vhc').innerHTML=trim(isi[0]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function fillField(noTrans, Thn) {
	unlock_header_form();
	notrn = noTrans;
	param = 'no_trans=' + notrn + '&proses=getData';
	tujuan = 'vhc_slave_save_pekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					ar = con.responseText.split("####");
					document.getElementById('no_trans').value = ar[0];
					document.getElementById('no_trans_pekerjaan').value = ar[0];
					document.getElementById('no_trans_opt').value = ar[0];
					document.getElementById('jns_vhc').value = ar[1];
					document.getElementById('kodetraksi').value = ar[7];
					//document.getElementById('kde_vhc').value=KdVhc;
					document.getElementById('tgl_pekerjaan').value = ar[2];
					document.getElementById('tgl_pekerjaan').disabled = true;
					//document.getElementById('kmhm_awal').value=ar[3];
					//document.getElementById('kmhm_akhir').value=ar[4];
					//document.getElementById('stn').value=ar[5];
					document.getElementById('jns_bbm').value = ar[3];
					document.getElementById('jmlh_bbm').value = ar[4];
					document.getElementById('KbnId').disabled = true;
					document.getElementById('KbnId').value = ar[5];
					//document.getElementById('thnKntrk').value=ar[9];
					document.getElementById('kode_karyawan').innerHTML = ar[6];

					bersih_form_pekerjaan();
					clear_operator();
					if (ar[6] == '') {
						ar[6] = "<option value''></options>";
					}
					//document.getElementById('noKntrk').innerHTML=ar[10];
					document.getElementById('proses').value = 'update_head';
					get_kd(noTrans);
					
					window.scrollTo({
					  top: 0,
					  left: 0,
					  behavior: 'smooth'
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	/*  document.getElementById('no_trans').value=noTrans;
	document.getElementById('no_trans_pekerjaan').value=noTrans;
	document.getElementById('no_trans_opt').value=noTrans;
	document.getElementById('jns_vhc').value=jnsVhc;
	//document.getElementById('kde_vhc').value=KdVhc;
	document.getElementById('tgl_pekerjaan').value=tglKrja;
	document.getElementById('kmhm_awal').value=kmhmA;
	document.getElementById('kmhm_akhir').value=kmhmR;
	document.getElementById('stn').value=sat;
	document.getElementById('jns_bbm').value=jnsBbm;
	document.getElementById('jmlh_bbm').value=jmlhBbm;
	document.getElementById('thnKntrk').value=Thn;
	//document.getElementById('noKntrk').value=nkntrk;

	document.getElementById('proses').value='update_head';
	get_kd(noTrans);*/
}
function createNew() {
	get_notransaksi();
	//load_data_pekerjaan();
	//document.getElementById('create_new').style.display='none';
	document.getElementById('done_entry').disabled = true;
	document.getElementById('save_kepala').disabled = false;
	document.getElementById('cancel_kepala').disabled = false;
	document.getElementById('proses').value = 'insert_header';
	//document.getElementById('premiStat').disabled=false;
	document.getElementById('jns_vhc').disabled = false;
	document.getElementById('kodetraksi').disabled = false;
	document.getElementById('kde_vhc').disabled = false;
	document.getElementById('tgl_pekerjaan').disabled = false;
	document.getElementById('kmhm_awal').disabled = false;
	document.getElementById('kmhm_akhir').disabled = false;
	document.getElementById('stn').disabled = false;
	document.getElementById('jns_bbm').disabled = false;
	document.getElementById('jmlh_bbm').disabled = false;
	//document.getElementById('noKntrk').disabled=false;
	//document.getElementById('thnKntrk').disabled=false;
	//document.getElementById('noKntrk').innerHTML='';
	//document.getElementById('thnKntrk').value='';
}
function get_notransaksi() {
	kdOrg = document.getElementById('KbnId').options[document.getElementById('KbnId').selectedIndex].value;
	param = 'proses=get_no_transaksi' + '&kdOrg=' + kdOrg;
	tujuan = 'vhc_slave_save_pekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					ac = con.responseText.split("####");
					document.getElementById('no_trans').value = ac[0];
					ar = document.getElementById('no_trans').value;
					document.getElementById('no_trans_pekerjaan').value = ar;
					document.getElementById('no_trans_opt').value = ar;
					document.getElementById('kode_karyawan').innerHTML = ac[1];
					load_data();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkegiatan(){
	jenis_vhc = document.getElementById('jns_vhc').value;
	kde_vhc = document.getElementById('kde_vhc').value;
	param = 'jenis_vhc=' + jenis_vhc + '&proses=getkodekegiatan';
	param += '&kde_vhc=' + kde_vhc;
	
	tujuan = 'vhc_slave_save_pekerjaan.php';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
	
					document.getElementById('jns_kerja').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function save_header() {
	//jns_vhc,kde_vhc,tgl_pekerjaan,kmhm_awal,kmhm_akhir,stn,jns_bbm,jmlh_bbm

	jenis_vhc = document.getElementById('jns_vhc').options[document.getElementById('jns_vhc').selectedIndex].value;
	if (document.getElementById('kde_vhc').options[document.getElementById('kde_vhc').selectedIndex].value != '') {
		kdVhc = document.getElementById('kde_vhc').options[document.getElementById('kde_vhc').selectedIndex].value;
	} else {
		kdVhc = '';
	}
	kodeOrg = document.getElementById('KbnId').options[document.getElementById('KbnId').selectedIndex].value;
	tgl_kerja = document.getElementById('tgl_pekerjaan').value;

	jns_bbm = document.getElementById('jns_bbm').options[document.getElementById('jns_bbm').selectedIndex].value;
	jmlh = document.getElementById('jmlh_bbm').value;
	pro = document.getElementById('proses');
	no_trans = document.getElementById('no_trans').value;
	param = 'jns_id=' + jenis_vhc + '&kode_vhc=' + kdVhc + '&tglKerja=' + tgl_kerja + '&kodeOrg=' + kodeOrg;
	param += '&jnsBbm=' + jns_bbm + '&jumlah=' + jmlh + '&proses=' + pro.value + '&no_trans=' + no_trans;
	//alert(param);
	tujuan = 'vhc_slave_save_pekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					//document.getElementById('contain').value=con.responseText;
					/*isidt=0;
					if(con.responseText!='')
				{
					isidt=con.responseText;
					}
					document.getElementById('kmhm_awal').disabled=true;
					document.getElementById('kmhm_awal').value=isidt;*/

					isidt = 0;
					if (con.responseText != '') {
						isidt = con.responseText.split("####");
						document.getElementById('kmhm_awal').disabled = false;
					} else {
						document.getElementById('kmhm_awal').disabled = false;
					}
					//document.getElementById('kmhm_awal').disabled=true;
					document.getElementById('kmhm_awal').value = isidt[0];
					document.getElementById('jns_kerja').innerHTML = isidt[1];

					
					if (pro.value == 'insert_header') {
						//kmhm_awal
						lock_header_form();
					} else if (pro.value == 'update_head') {
					
						lock_header_form(); //clear_form();
					}
					load_data();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function lock_header_form() {
	//jns_vhc,kde_vhc,tgl_pekerjaan,kmhm_awal,kmhm_akhir,stn,jns_bbm,jmlh_bbm
	document.getElementById('jns_vhc').disabled = true;
	document.getElementById('kodetraksi').disabled = true;
	document.getElementById('kde_vhc').disabled = true;
	document.getElementById('tgl_pekerjaan').disabled = true;

	document.getElementById('jns_bbm').disabled = true;
	document.getElementById('jmlh_bbm').disabled = true;
	document.getElementById('save_kepala').disabled = true;
	document.getElementById('cancel_kepala').disabled = true;
	document.getElementById('done_entry').disabled = false;
	//document.getElementById('thnKntrk').disabled=true;
	//document.getElementById('noKntrk').disabled=true;
	//document.getElementById('premiStat').disabled=true;
	document.getElementById('KbnId').disabled = true;
	
}
function unlock_header_form() {
	document.getElementById('jns_vhc').disabled = false;
	document.getElementById('kodetraksi').disabled = false;
	document.getElementById('kde_vhc').disabled = false;
	document.getElementById('tgl_pekerjaan').disabled = false;
	//  document.getElementById('kmhm_awal').disabled=false;
	//  document.getElementById('kmhm_akhir').disabled=false;
	//  document.getElementById('stn').disabled=false;
	document.getElementById('jns_bbm').disabled = false;
	document.getElementById('jmlh_bbm').disabled = false;
	document.getElementById('save_kepala').disabled = false;
	document.getElementById('cancel_kepala').disabled = false;
	document.getElementById('done_entry').disabled = true;
	document.getElementById('KbnId').disabled = false;
	//document.getElementById('create_new').style.display='none';
	//document.getElementById('thnKntrk').disabled=false;
	//document.getElementById('noKntrk').disabled=false;
	//document.getElementById('premiStat').disabled=false;
}
function clear_form() {
	document.getElementById('no_trans').value = '';
	document.getElementById('jns_vhc').value = '';
	document.getElementById('kodetraksi').value = '';
	document.getElementById('kde_vhc').innerHTML = "<option value=''>" + dataKdvhc + "</option>";
	document.getElementById('tgl_pekerjaan').value = '';

	document.getElementById('jns_bbm').value = '';
	document.getElementById('jmlh_bbm').value = '0';
	document.getElementById('save_kepala').value = '';
	document.getElementById('cancel_kepala').value = '';
	document.getElementById('KbnId').value = '';
	document.getElementById('KbnId').disabled = false;
}
function doneEntry() {
	if (confirm("Are you sure..?")) {
		cancel_kepala_form();
		bersih_form_pekerjaan();
		clear_operator();
	} else {
		return;
	}
}
function cancel_kepala_form() {
	clear_form();
	document.getElementById('save_kepala').disabled = true;
	document.getElementById('cancel_kepala').disabled = true;
	document.getElementById('done_entry').disabled = true;
	//document.getElementById('create_new').style.display='block';
	document.getElementById('no_trans_pekerjaan').value = '';
	document.getElementById('no_trans_opt').value = '';
}
function load_data() {
	//alert("test");
	param = 'proses=load_data_header';
	tujuan = 'vhc_slave_save_pekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//Get data convert to json - author : Atwal
					//data = JSON.parse(con.responseText);
					//Go to function create html  - author : Atwal
					//load_data_exc(data);
					//alert(con.responseText);
					data = con.responseText.split("####");
					//document.getElementById('contain').value=data[0];

					document.getElementById('tgl_cari').value = '';
					document.getElementById('txtCari').value = '';
					document.getElementById('kodevhc_cari').value = '';
					leftFixedTable();
					getUmr();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function load_data_exc(data) {
	var contain = document.getElementById('contain');
	var foot = document.getElementById('containfoot');
	//html modification  - author : Atwal
	html = "";
	if (data.tbody.length > 0) {
		for (i = 0; i < data.tbody.length; i++) {
			html += '<tr class=rowcontent>';
			html += '<td align=center>' + data.tbody[i].no + '</td>';
			html += '<td align=center>' + data.tbody[i].notransaksi + '</td>';
			html += '<td align=center>' + data.tbody[i].jenisvhc + '</td>';
			html += '<td align=center>' + data.tbody[i].kodevhc + '</td>';
			html += '<td align=center>' + data.tbody[i].tanggal + '</td>';
			html += '<td align=center>' + data.tbody[i].namabarang + '</td>';
			html += ' <td align=center>' + data.tbody[i].jlhbbm + '</td>';
			html += ' <td align=center>' + data.tbody[i].img + '</td>';
		}

		htmlfoot = '<tr class=rowheader><td colspan=8 align=center>';
		htmlfoot += ((parseInt(data.tfoot.page) * parseInt(data.tfoot.limit)) + 1) + ' to ' + ((parseInt(data.tfoot.page) + 1) * parseInt(data.tfoot.limit)) + ' Of ' + data.tfoot.jlhbrs;
		htmlfoot += '<br /><button class=mybutton onclick=cariBast(' + (parseInt(data.tfoot.page) - 1) + ');>' + data.tfoot.pref + '</button>';
		htmlfoot += '<button class=mybutton onclick=cariBast(' + (parseInt(data.tfoot.page) + 1) + ');>' + data.tfoot.lanjut + '</button>';
		htmlfoot += "</td></tr>";
		//Mengirim HTML by ID  - author : Atwal
		contain.innerHTML = html;
		foot.innerHTML = htmlfoot;
	} else {
		contain.innerHTML = "<tr class=rowheader><td colspan=8 align=left>Data Kosong</td></tr>";
	}
}

function batalcariDataTransaksi(){
	document.getElementById('tgl_cari').value='';
	document.getElementById('txtCari').value='';
	document.getElementById('kodevhc_cari').value='';
	document.getElementById('statusInputan').value='';
	cariDataTransaksi();
}

function cariDataTransaksi() {
	txtTgl      = document.getElementById('tgl_cari').value;
	txtCari     = document.getElementById('txtCari').value;
	kodevhc_cari= document.getElementById('kodevhc_cari').value;
	statData    = document.getElementById('statusInputan').options[document.getElementById('statusInputan').selectedIndex].value;
	param = "txtTgl=" + txtTgl + "&txtCari=" + txtCari + '&statData=' + statData + '&kodevhc_cari=' + kodevhc_cari;
	param += "&proses=cariTransaksi";
	//alert(param);
	tujuan = 'vhc_slave_save_pekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('contain').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function cariData(num) {
	txtTgl = document.getElementById('tgl_cari').value;
	txtCari = document.getElementById('txtCari').value;
	statData = document.getElementById('statusInputan').options[document.getElementById('statusInputan').selectedIndex].value;

	kodevhc_cari = document.getElementById('kodevhc_cari').value;
	param = "txtTgl=" + txtTgl + "&txtCari=" + txtCari + '&statData=' + statData + '&kodevhc_cari=' + kodevhc_cari;

	///param="txtTgl="+txtTgl+"&txtCari="+txtCari+'&statData='+statData;
	param += "&proses=cariTransaksi";
	param += '&page=' + num;
	//alert(param);
	tujuan = 'vhc_slave_save_pekerjaan.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function load_data_operator() {
	//alert(document.getElementById('no_trans_opt').value);
	if (document.getElementById('no_trans_opt').value != '') {
		no_tans = document.getElementById('no_trans_opt').value;
		param = 'proses=load_data_opt';
		param += '&notrans=' + no_tans;
		//alert(param);
		tujuan = 'vhc_detailPekerjaan.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						//alert(con.responseText);
						document.getElementById('containOperator').innerHTML = con.responseText;
						//load_data_pekerjaan();+
						noTrans = document.getElementById('no_trans_opt').value;
						getKmAkhir();
						//  getKntrk(thn,nokntrak);
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		post_response_text(tujuan, param, respog);
	}
}
function load_data_pekerjaan() {
	//alert(document.getElementById('no_trans_pekerjaan').value);
	if (document.getElementById('no_trans_pekerjaan').value != '') {
		no_trans = document.getElementById('no_trans_pekerjaan').value;
		param = 'notrans=' + no_trans;
		param += '&proses=load_data_kerjaan';
		//alert(param);
		tujuan = 'vhc_detailPekerjaan.php';

		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						//alert(con.responseText);
						document.getElementById('containPekerja').innerHTML = con.responseText;
						leftFixedTable();
						load_data_operator();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		post_response_text(tujuan, param, respog);
	}

}

function getKntrk(thn, nokntrak) {
	if ((thn == '') && (nokntrak == '')) {
		//alert("masuk");
		thnKntrk = document.getElementById('thnKntrk').options[document.getElementById('thnKntrk').selectedIndex].value;
		param = 'thnKntrk=' + thnKntrk + '&proses=getKntrk';
	} else {
		thnKntrk = thn;
		noKntrak = nokntrak;
		param = 'thnKntrk=' + thnKntrk + '&proses=getKntrk' + '&noKntrak=' + noKntrak;
	}
	tujuan = 'vhc_detailPekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);

					document.getElementById('noKntrk').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function searchLok(title, content, ev) {
	width = '500';
	height = '400';
	showDialog1(title, content, width, height, ev);
}
function findLok() {
	txt = trim(document.getElementById('txtinputan').value);
	if (txt == '') {
		alert('Text is obligatory');
	} else if (txt.length < 3) {
		alert('Too short');
	} else {
		param = 'txtinputan=' + txt + '&proses=cari_lokasi';
		tujuan = 'vhc_slave_save_pekerjaan.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function throwThisRow(kd_org, nm_org) {
	document.getElementById('lokasi_kerja_nm').value = nm_org;
	document.getElementById('lokasi_kerja').value = kd_org;
	closeDialog();
}
function fillFieldKrj(jnsKrj, lokKrj, brtMuat, jmlhRit, ktr, bya, kmawl, kmakhr, stn, segment, nmSegment,kodedept) {
	//document.getElementById('jns_kerja').value = jnsKrj;
	document.getElementById('old_jnskerja').value = jnsKrj;
	document.getElementById('brt_muatan').value = brtMuat;
	document.getElementById('jmlh_rit').value = jmlhRit;
	document.getElementById('biaya').value = bya;
	document.getElementById('ket').value = ktr;
	document.getElementById('kmhm_awal').value = kmawl;
	document.getElementById('kmhm_akhir').value = kmakhr;
	document.getElementById('stn').value = stn;
	
		//$('#blok').select2();

	document.getElementById('proses_pekerjaan').value = 'update_kerja';
	document.getElementById('old_jnskerja').value = jnsKrj;
	document.getElementById('oldbrt_muatan').value = brtMuat;
	setValue('kodesegment', segment);
	setValue('kodesegment_name', nmSegment);
	setValue2('jns_kerja',jnsKrj);

	jumlah = parseFloat(kmakhr)-parseFloat(kmawl);
	document.getElementById('jlhhm').value=jumlah;


	if (lokKrj.length > 4) {
		if(lokKrj.substr(0,3)=='S20'){
			kd = lokKrj;
			
			//document.getElementById('lokasi_kerja').value = kd;
			document.getElementById('old_lokkerja').value = kd;
			document.getElementById('blok').value = '';
			setValue2('lokasi_kerja',kd);
		}else{
			kd = lokKrj;


			//document.getElementById('lokasi_kerja').value = kd.substring(0, 4);
			getBlok(kd.substring(0, 4), kd,jnsKrj,kodedept);
			//setValue2('lokasi_kerja',kd.substring(0,4));
			document.getElementById('old_lokkerja').value = kd;

			document.getElementById('dept').value = kodedept;
		}
		
	} else {

		document.getElementById('old_lokkerja').value = lokKrj;
		//document.getElementById('lokasi_kerja').value = lokKrj;
		setValue2('lokasi_kerja',lokKrj);
		getBlok(0,0,0,kodedept);
		// document.getElementById('blok').innerHTML="<option value=''>"+dataKdvhc+"</option>";
	}
}
function save_pekerjaan() {
	//no_trans_pekerjaan,jns_kerja,lokasi_kerja,muatan,brt_muatan,jmlh_rit,ket
	dcek = document.getElementById('save_kepala');
	if (dcek.disabled != true) {
		alert("Please confirm header first\nSilahkan click Simpan terlebih dahulu pada tab Header");
		return;
	}
	notrans = document.getElementById('no_trans_pekerjaan').value;
	if (notrans == '') {
		alert("Please clik New")
		return;
	}
	jns_pekerjan = document.getElementById('jns_kerja').options[document.getElementById('jns_kerja').selectedIndex].value;
	if (document.getElementById('old_jnskerja').value == '') {
		document.getElementById('old_jnskerja').value = jns_pekerjan;
	}
	jlhhm       = document.getElementById('jlhhm').value;
	kmhm_aw     = document.getElementById('kmhm_awal').value;
	kmhm_ak     = document.getElementById('kmhm_akhir').value;
	satuan      = document.getElementById('stn').options[document.getElementById('stn').selectedIndex].value;
	oldkerja    = document.getElementById('old_jnskerja').value;
	locationKerj= document.getElementById('lokasi_kerja').options[document.getElementById('lokasi_kerja').selectedIndex].value;
	brtmuatan   = document.getElementById('brt_muatan').value;
	jmlh_rit    = document.getElementById('jmlh_rit').value;
	keterangan  = document.getElementById('ket').value;
	pro         = document.getElementById('proses_pekerjaan');
	bya         = document.getElementById('biaya').value;
	Blok        = document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	dept        = document.getElementById('dept').options[document.getElementById('dept').selectedIndex].value;
	kodesegment = getValue('kodesegment');

	param = 'notrans=' + notrans + '&jnsPekerjaan=' + jns_pekerjan + '&locationKerja=' + locationKerj + '&biaya=' + bya;
	param += '&brtmuatan=' + brtmuatan + '&jmlhRit=' + jmlh_rit + '&ket=' + keterangan + '&proses=' + pro.value + '&oldjnsPekerjaan=' + oldkerja;
	param += '&kmhmAwal=' + kmhm_aw + '&kmhmAkhir=' + kmhm_ak + '&satuan=' + satuan + '&kodesegment=' + kodesegment + '&oldSegment=' + getValue('oldSegment');

	if (document.getElementById('oldbrt_muatan').value != '') {
		oldbrt_muatan = document.getElementById('oldbrt_muatan').value;
		param += '&oldbrt_muatan=' + oldbrt_muatan;
	}

	if (document.getElementById('old_lokkerja').value != '') {
		old_lokKerja = document.getElementById('old_lokkerja').value;
		param += '&old_lokKerja=' + old_lokKerja;
	}
	if (document.getElementById('old_blok').value != '') {
		oldBlok = document.getElementById('old_blok').value;
		param += '&oldBlok=' + oldBlok;
	}

	if (Blok != '') {
		param += '&Blok=' + Blok;
	}
	if (dept != '') {
		param += '&dept=' + dept;
	}
	
	tujuan = 'vhc_detailPekerjaan.php';
	// if(satuan=='HM' && jlhhm>'24'){
	// 	alertify.alert("HM yang anda masukkan terlalu besar untuk kegiatan satu hari.");
	// 	return;
	// }
	
	// if(satuan=='KM' && jlhhm>'500'){
	// 	if(confirm("Jumlah KM yg anda masukkan lebih dari 500 KM, Anda yakin ???")){			
	// 		post_response_text(tujuan, param, respog);
	// 	}else{
	// 		return;
	// 	}
	// }
	post_response_text(tujuan, param, respog);
	//alert(param);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					//document.getElementById('container').innerHTML=con.responseText;
					bersih_form_pekerjaan();
					isidt = 0;
					if (con.responseText != '') {
						isidt = parseInt(con.responseText);
					}
					document.getElementById('kmhm_awal').disabled = false;
					document.getElementById('kmhm_awal').value = isidt;

					load_data_pekerjaan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function delHead(noTran) {
	notrans = noTran;
	param = 'no_trans=' + notrans + '&proses=deleteHead';
	tujuan = 'vhc_slave_save_pekerjaan.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					//document.getElementById('contain').value=con.responseText;
					load_data();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Header dan detail wil be deleted, are you sure?")) {
		post_response_text(tujuan, param, respog);
	} else {
		return;
	}
}

function bersih_form_pekerjaan() {
	document.getElementById('proses_pekerjaan').value = 'insert_pekerjaan';
	document.getElementById('jns_kerja').value = '';
	document.getElementById('jns_kerja').disabled = false;
	document.getElementById('lokasi_kerja').selectedIndex = 0;
	document.getElementById('lokasi_kerja').disabled = false;
	document.getElementById('brt_muatan').value = 0;
	document.getElementById('jmlh_rit').value = 0;
	document.getElementById('ket').value = '';
	document.getElementById('blok').value = "<option value=''>" + dataKdvhc + "</options>";
	document.getElementById('blok').selectedIndex = 0;
	document.getElementById('biaya').value = 0;
	//document.getElementById('kmhm_awal').value=0;
	document.getElementById('kmhm_akhir').value = 0;
	document.getElementById('stn').selectedIndex = 0;
	document.getElementById('oldbrt_muatan').value = ''
	setValue('kodesegment', '');
	setValue('kodesegment_name', '');
	setValue2('jns_kerja', '');
	getKmAkhir();
}
function delDataKrj(noTrans, jnsKerja, blok, segment, beratmuatan) {
	no_trans = document.getElementById('no_trans_pekerjaan').value = noTrans;
	jns_kerja = document.getElementById('jns_kerja').value = jnsKerja;
	param = 'notrans=' + no_trans + '&jnsPekerjaan=' + jns_kerja
		 + '&Blok=' + blok + '&kodesegment=' + segment + '&beratmuatan=' + beratmuatan
		 + '&proses=deleteKrj';
	tujuan = 'vhc_detailPekerjaan.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					load_data_pekerjaan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Delete, are you sure?")) {
		post_response_text(tujuan, param, respog);
	} else {
		return;
	}

}
stat_opt = 0;
function delData(noTrans, Kdkry) {
	no_trans = document.getElementById('no_trans_opt').value = noTrans;
	kdKry = document.getElementById('kode_karyawan').value = Kdkry;
	pros = document.getElementById('prosesOpt');
	//pros.value=;
	param = 'noOptrans=' + no_trans + '&kdKry=' + kdKry + '&proses=delete_opt';
	tujuan = 'vhc_detailPekerjaan.php';

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					//document.getElementById('containPekerja').innerHTML=con.responseText;
					load_data_operator();
					clear_operator();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Delete, are you sure?")) {
		post_response_text(tujuan, param, respog);
	} else {
		return;
	}
}
function clear_operator() {
	// document.getElementById('kode_karyawan').value = '';
	// setValue2('kode_karyawan',null);
	document.getElementById('uphOprt').value = 0;
	document.getElementById('prmiOprt').value = 0;
	document.getElementById('pnltyOprt').value = 0;
	document.getElementById('ketOprt').value = "";
	document.getElementById('prosesOpt').value = 'insert_operator';
}
function save_operator() {

	jenisvhc = document.getElementById('jns_vhc').value;

	notrans = document.getElementById('no_trans_opt').value;
	kdKry = document.getElementById('kode_karyawan').options[document.getElementById('kode_karyawan').selectedIndex].value;
	posisi = document.getElementById('posisi').options[document.getElementById('posisi').selectedIndex].value;
	uphoprt = document.getElementById('uphOprt').value;
	prmiOprt = document.getElementById('prmiOprt').value;
	pnltyOprt = document.getElementById('pnltyOprt').value;
	tglTrans = document.getElementById('tgl_pekerjaan').value;
	ketOprt = document.getElementById('ketOprt').value;
	pros = document.getElementById('prosesOpt');

	if (kdKry == '') {
		alert('Nama Karyawan wajib di isi !');
		return;
	}

	param = 'notrans=' + notrans + '&kdKry=' + kdKry + '&posisi=' + posisi + '&jenisvhc=' + jenisvhc;
	param += '&proses=' + pros.value + '&pnltyOprt=' + pnltyOprt + '&prmiOprt=' + prmiOprt + '&uphOprt=' + uphoprt + '&tglTrans=' + tglTrans + '&ketOprt=' + ketOprt;
	tujuan = 'vhc_detailPekerjaan.php';
	//alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alert(con.responseText);
					//document.getElementById('containPekerja').innerHTML=con.responseText;
					load_data_operator();
					clear_operator();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function cariBast(num) {
	param = 'proses=load_data_header';
	param += '&page=' + num;
	tujuan = 'vhc_slave_save_pekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//Get data convert to json - author : Atwal
					data = JSON.parse(con.responseText);
					//Go to function create html  - author : Atwal
					load_data_exc(data);
					//document.getElementById('contain').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cariBastKrj(num) {
	param = 'proses=load_data_kerjaan';
	param += '&page=' + num;
	tujuan = 'vhc_detailPekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containPekerja').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cariBastOpt(num) {
	param = 'proses=load_data_opt';
	param += '&page=' + num;
	tujuan = 'vhc_detailPekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containOperator').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getUmr() {
	//kdKry
	kdkry = document.getElementById('kode_karyawan').options[document.getElementById('kode_karyawan').selectedIndex].value;
	tanggal = document.getElementById('tgl_pekerjaan').value;
	tahun = tanggal.substr(6, 4);
	param = 'proses=getUmr' + '&kdKry=' + kdkry + '&tahun=' + tahun + '&tglTrans=' + tanggal;
	tujuan = 'vhc_detailPekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('uphOprt').value = trim(data[0]);
					if(trim(data[1])!=4){						
						document.getElementById('uphOprt').value = 0;
						document.getElementById('uphOprt').disabled = true;
					}else{
						document.getElementById('uphOprt').disabled = false;
					}
					getKmAkhir();
					getPremi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getSatuan(jns_pekerjan) {
	param = 'jnsPekerjaan=' + jns_pekerjan + '&proses=getSatuan'
		tujuan = 'vhc_detailPekerjaan.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					dtIsi = con.responseText.split("####");
					document.getElementById('satuan').innerHTML = dtIsi[0];
					document.getElementById('lokasi_kerja').innerHTML = dtIsi[1];
					getBlok(0, 0,jns_pekerjan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getBlok(kdkbn, kdblok,jns_pekerjan,kodedept) {

	if (document.getElementById('jns_kerja').value == '') {
		alert("Jenis Pekerjaan harus diisi terlebih dahulu!");
		document.getElementById('lokasi_kerja').selectedIndex = 0;
		return false;
	}

	if ((kdkbn == '') && (kdblok == '')) {
		locationKerja = document.getElementById('lokasi_kerja').options[document.getElementById('lokasi_kerja').selectedIndex].value;
		jnsPekerjaan = document.getElementById('jns_kerja').options[document.getElementById('jns_kerja').selectedIndex].value;
		param = 'locationKerja=' + locationKerja + '&jnsPekerjaan=' + jnsPekerjaan + '&proses=getBlok';
	} else {
		locationKerja = kdkbn;
		Blok = kdblok;
		jnsPekerjaan = document.getElementById('jns_kerja').options[document.getElementById('jns_kerja').selectedIndex].value;
		param = 'locationKerja=' + locationKerja + '&jnsPekerjaan=' + jnsPekerjaan + '&Blok=' + Blok + '&proses=getBlok';
	}
	tujuan = 'vhc_detailPekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('blok').innerHTML = con.responseText;
					document.getElementById('old_blok').value = kdblok;
					$('#blok').select2();
					setTimeout(function(){	
						getdept(getValue('jns_kerja'),kodedept,getValue('lokasi_kerja'));
					}, 500);					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}


function getdept(jns_pekerjan,kodedept,lokasi_kerja) {
	param = 'jnsPekerjaan=' + jns_pekerjan + '&proses=getdept'
	param += '&kodeorg=' + lokasi_kerja;
	param += '&kodedept=' + kodedept;
		tujuan = 'vhc_detailPekerjaan.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {					
					document.getElementById('dept').innerHTML = con.responseText;
					// if (kodedept==undefined) {
					  // document.getElementById('dept').innerHTML = con.responseText;

					// }
					// else
					// {
						// setValue2('dept',kodedept);
						
					// }
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getKmAkhir() {
	var kodevhc = getValue('kde_vhc'),
	param = "proses=getKmAkhir&kodevhc=" + kodevhc;
	tujuan = 'vhc_slave_save_pekerjaan.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					setValue('kmhm_awal', con.responseText);
					console.log(parseFloat(con.responseText) > 0);
					if (parseFloat(con.responseText) > 0) {
						getById('kmhm_awal').disabled = false;
					} else {
						getById('kmhm_awal').disabled = false;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function enter(e) {
	key = getKey(e);
	if (key == 13) {
		cariDataTransaksi();
		return true;
	} else {
		return tanpa_kutip_dan_sepasi(e);
	}
}