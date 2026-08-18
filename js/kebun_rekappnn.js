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
function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = "300";
	height = "100";
	content =
	  "<iframe frameborder=0 width=100% height=100% src='" +
	  tujuan +
	  "'></iframe>";
	showDialog1(title, content, width, height, ev);
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
	//form();
	param = 'method=html' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekappnn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
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
function edit(div, tgl) {
	document.getElementById('div').value = div;
	document.getElementById('tgl').value = tgl;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	detail(div, tgl);
}
function deletedetail(div, tgl, blok) {
	param = 'method=deletedetail' + '&div=' + div + '&tgl=' + tgl + '&blok=' + blok;
	tujuan = 'kebun_slave_rekappnn.php';
	//if(confirm(' Anda yakin ingin menghapus nomor transaksi'))
	// {
	post_response_text(tujuan, param, respog);
	//}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loaddatadetail(div, tgl);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function del(div, tgl) {
	param = 'method=delete' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekappnn.php';
	if (confirm(' Anda yakin ingin menghapus nomor transaksi')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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
	tujuan = 'kebun_slave_rekappnn.php';
	if (confirm('Anda yakin ingin memposting transaksi unit ' + div + ' pada tanggal ' + tgl + ' ??')) {
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
function unposting(div, tgl, numrow) {
	param = 'method=unposting' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekappnn.php';
	if (confirm('Anda yakin ingin unposting transaksi unit ' + div + ' pada tanggal ' + tgl + ' ??')) {
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
function detail() {
	div = document.getElementById('div').value;
	tgl = document.getElementById('tgl').value;
	if (div == '' || tgl == '') {
		alertify.alert('Lengkapi Pengisian');
		return;
	}
	param = 'method=detail';
	param += '&div=' + div + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekappnn.php';
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
					loaddatadetail(div, tgl);
					
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
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
	tujuan = 'kebun_slave_rekappnn.php';
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
	
	setValue2('div',null);
}
function loaddatadetail(div, tgl) {
	document.getElementById('tomboldetail').disabled = true;
	document.getElementById('div').disabled = true;
	document.getElementById('tgl').disabled = true;
	param = 'method=loaddatadetail';
	param += '&div=' + div + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekappnn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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
function getdata() {
	blok = document.getElementById('blok').value;
	tgl = document.getElementById('tgl').value;
	param = 'method=getdata' + '&blok=' + blok + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekappnn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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
function savedetail() {
	div = document.getElementById('div').value;
	tgl = document.getElementById('tgl').value;
	blok = document.getElementById('blok').value;
	thntnm = document.getElementById('thntnm').value;
	luasaresta = document.getElementById('luasaresta').value;
	luaspnn = document.getElementById('luaspnn').value;
	tk = document.getElementById('tk').value;
	jjgpnn = document.getElementById('jjgpnn').value;
	afkirjjg = document.getElementById('afkirjjg').value;
	afkirket = document.getElementById('afkirket').value;
	bjr = document.getElementById('bjr').value;
	kgkebun = document.getElementById('kgkebun').value;
	brondol = document.getElementById('brondol').value;
	method = document.getElementById('method').value;
	if ((blok == '' || luasaresta == '' || jjgpnn == '') && afkirjjg == '') {
		alertify.alert('Blok, Jjg Panen wajib di isi !');
		return;
	}
	if ((jjgpnn != '' || jjgpnn == '0') && luaspnn == '0') {
		//alertify.alert('luas panen tidak boleh 0');
		//return;
	}
	if (parseFloat(luaspnn) > parseFloat(luasaresta)) {
		alertify.alert('Luas panen tidak boleh lebih besar dari luas areal');
		return;
	}
	if (jjgpnn != '' && luaspnn == '') {
		//alertify.alert('Jjg panen masih kosong');
		//return;
	}
	if ((kgkebun == '' || kgkebun == 0) && jjgpnn != '') {
		// alertify.alert('Kg Kebun tidak boleh kosong, silahkan cek Inputkan BJR');
		// return;
	}
	param = 'blok=' + blok + '&thntnm=' + thntnm + '&luasaresta=' + luasaresta + '&luaspnn=' + luaspnn;
	param += '&tk=' + tk + '&jjgpnn=' + jjgpnn + '&afkirjjg=' + afkirjjg + '&afkirket=' + afkirket;
	param += '&div=' + div + '&tgl=' + tgl + '&bjr=' + bjr + '&kgkebun=' + kgkebun;
	param += '&brondol=' + brondol;
	param += '&method=' + method;
	tujuan = 'kebun_slave_rekappnn.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					cleardetail();
					loaddatadetail(div, tgl);
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
	setValue2('blok',null);
	document.getElementById('thntnm').value = '';
	document.getElementById('luasaresta').value = '';
	document.getElementById('luaspnn').value = '';
	document.getElementById('tk').value = '';
	document.getElementById('jjgpnn').value = '';
	document.getElementById('afkirjjg').value = '';
	document.getElementById('afkirket').value = '';
	document.getElementById('bjr').value = '';
	document.getElementById('kgkebun').value = '';
	document.getElementById('brondol').value = '';
}


function editdetail(divisi,tanggal,blok,tahuntanam,luasproduksi,luaspanen,tenagakerja,jjgpanen,bjr,kgkebun,jjgafkir,keterangan,brondol){
	document.getElementById('div').value = divisi;
	document.getElementById('tgl').value = tanggal;
	document.getElementById('blok').value = blok;
	setValue2('blok',blok);
	document.getElementById('thntnm').value = tahuntanam;
	document.getElementById('luasaresta').value = luasproduksi;
	document.getElementById('luaspnn').value = luaspanen;
	document.getElementById('tk').value = tenagakerja;
	document.getElementById('jjgpnn').value = jjgpanen;
	document.getElementById('bjr').value = bjr;
	document.getElementById('kgkebun').value = kgkebun;
	document.getElementById('afkirjjg').value = jjgafkir;
	document.getElementById('afkirket').value = keterangan;
	document.getElementById('brondol').value = brondol;
	document.getElementById('method').value = 'updatedetail';
}