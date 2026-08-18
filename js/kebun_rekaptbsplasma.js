function getdivisi(kodeorg) {
	param = 'method=getdivisi' + '&kodeorg=' + kodeorg;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
	
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('divisi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedetail(kodeorg, periode, nospb) {
	param = 'method=deletedetail' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&nospb=' + nospb;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
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
	supplier = document.getElementById('supplier').value;
	
	param = 'method=loaddatadetail';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&supplier=' + supplier;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
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
	method = document.getElementById('method').value;
	kodeorg = document.getElementById('kodeorg').value;
	periode = document.getElementById('periode').value;
	supplier = document.getElementById('supplier').value;
	nospb = document.getElementById('nospb').value;
	divisi = document.getElementById('divisi').value;
	pekerjaan = document.getElementById('pekerjaan').value;
	kgwb = document.getElementById('kgwb').value;
	tanggal = document.getElementById('tanggal').value;
	
	blok = document.getElementById('blok_'+currRow).value;
	tujuan = document.getElementById('tujuan_'+currRow).value;
	kgwbdet = document.getElementById('kgwb_'+currRow).value;
	rp_muat = document.getElementById('rp_muat_'+currRow).value;
	rp_angkut = document.getElementById('rp_angkut_'+currRow).value;
	
	param = "";
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&supplier=' + supplier;
	param += '&nospb=' + nospb;
	param += '&divisi=' + divisi;
	param += '&pekerjaan=' + pekerjaan;
	param += '&blok=' + blok;
	param += '&tujuan=' + tujuan;
	param += '&kgwbdet=' + kgwbdet;
	param += '&rp_muat=' + rp_muat;
	param += '&rp_angkut=' + rp_angkut;
	param += '&kgwb=' + kgwb;
	param += '&tanggal=' + tanggal;
	param += '&method=' + method;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
	post_response_text(tujuan, param, respog);
	//document.getElementById('tr_' + currRow).style.display = 'none';
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
						//document.getElementById('tr_' + currRow).style.backgroundColor = 'cyan';
					}
					currRow += 1;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						alert('Done');
						loaddatadetail();
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
	kodeorg = document.getElementById('kodeorg').value;
	periode = document.getElementById('periode').value;
	supplier = document.getElementById('supplier').value;
	tgl = document.getElementById('tgl').value;
	if (kodeorg == '' || periode == '' || supplier=='') {
		alert('Kode Organisasi, Periode dan Kontraktor Wajib diisi !');
		return;
	}
	param = 'method=detail';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&supplier=' + supplier;
	param += '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getdetailspb() {
	kodeorg = document.getElementById('kodeorg').value;
	periode = document.getElementById('periode').value;
	supplier = document.getElementById('supplier').value;
	nospb = document.getElementById('nospb').value;
	
	param = 'method=getdetailspb';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&supplier=' + supplier;
	param += '&nospb=' + nospb;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
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
	rpmuat=document.getElementById('harga_muat_'+i).value;
	rpangkut=document.getElementById('harga_angkut_'+i).value;
	rpmuat=remove_comma_var(rpmuat);
	rpangkut=remove_comma_var(rpangkut);
	
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

function getharga(no) {
	blok = document.getElementById('blok_'+no).value;
	tujuan = document.getElementById('tujuan_'+no).value;
	ttlrow = document.getElementById('jumlahrow').value;
	
	n = (parseFloat(no)+1);
	if(n<=ttlrow){
		for (i = n; i <= ttlrow; i++) {
			document.getElementById('tujuan_' + i).value = tujuan;
		}		
	}
	
	param = 'method=getharga';
	param += '&blok=' + blok;
	param += '&tujuan=' + tujuan;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
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
					
					for (i = no; i <= ttlrow; i++) {
						document.getElementById('harga_muat_'+i).value=rpmuat;
						document.getElementById('harga_angkut_'+i).value=rpangkut;
						
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
function add_new_data() //indra
{
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	//cancelHead();
	//cancel();
	cancel();
}
function form() {
	width = '720';
	height = '';
	//nopp=document.getElementById('nopp_'+id).value;
	content = "<fieldset><div id=containerd align=center style=\"width:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog1(title, content, width, height, ev);
}
function html(div, tgl) {
	form();
	param = 'method=html' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
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
function edit(div, tgl) {
	document.getElementById('div').value = div;
	document.getElementById('tgl').value = tgl;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	detail(div, tgl);
}

function del(div, tgl) {
	param = 'method=delete' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
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
					document.getElementById('contain').innerHTML = con.responseText;
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function posting(div, tgl, numrow) {
	param = 'method=posting' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
	if (confirm('Anda yakin ingin memposting transaksi unit ' + div + ' pada tanggal ' + tgl + ' ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					x = document.getElementById('tr_' + numrow);
					//x.cells[9].innerHTML = '';
					x.cells[10].innerHTML = '';
					x.cells[11].innerHTML = '';
					x.cells[12].innerHTML = '';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function unposting(div, tgl, numrow) {
	param = 'method=unposting' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
	if (confirm('Anda yakin ingin unposting transaksi unit ' + div + ' pada tanggal ' + tgl + ' ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					x = document.getElementById('tr_' + numrow);
					//x.cells[9].innerHTML = '';
					x.cells[10].innerHTML = '';
					x.cells[11].innerHTML = '';
					x.cells[12].innerHTML = '';
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
	divsch = document.getElementById('divsch').value;
	tglsch = document.getElementById('tglsch').value;
	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	if (tglsch != '') {
		param += '&tglsch=' + tglsch;
	}
	tujuan = 'kebun_slave_rekaptbsplasma.php';
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
function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('div').disabled = false;
	document.getElementById('div').value = '';
	document.getElementById('tgl').disabled = false;
	document.getElementById('tgl').value = '';
}

function getdata() {
	blok = document.getElementById('blok').value;
	tgl = document.getElementById('tgl').value;
	param = 'method=getdata' + '&blok=' + blok + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekaptbsplasma.php';
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