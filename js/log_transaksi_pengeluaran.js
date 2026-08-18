function setSloc(x) {
	gudang = document.getElementById('sloc').value;
	if (gudang != '') {
		tglstart = document.getElementById(gudang + '_start').value;
		tglend = document.getElementById(gudang + '_end').value;
		tglstart = tglstart.substr(6, 2) + "-" + tglstart.substr(4, 2) + "-" + tglstart.substr(0, 4);
		tglend = tglend.substr(6, 2) + "-" + tglend.substr(4, 2) + "-" + tglend.substr(0, 4);
		document.getElementById('displayperiod').innerHTML = tglstart + " - " + tglend;
	}
	if (gudang != '') {
		if (x == 'simpan') {
			document.getElementById('sloc').disabled = true;
			document.getElementById('btnsloc').disabled = true;
			document.getElementById('pemilikbarang').disabled = true;
			tujuan = 'log_slave_getBastNumber.php';
			param = 'gudang=' + gudang;
			post_response_text(tujuan, param, respog);
		} else {
			document.getElementById('tblpic').innerHTML = '';
			document.getElementById('tblpic2').innerHTML = '';
			document.getElementById('nodok').value = '';
			document.getElementById('norequest').value = '';
			document.getElementById('sloc').disabled = false;
			document.getElementById('imgnorequest').style.display = '';
			document.getElementById('pemilikbarang').disabled = false;
			document.getElementById('pemilikbarang').innerHTML = "<option value=''></option>";
			document.getElementById('containerlist').innerHTML = "";
			// document.getElementById('sloc').options[0].selected = true;
			// document.getElementById('untukunit').options[0].selected = true;
			document.getElementById('btnsloc').disabled = false;
			
			setValue2('untukunit',null);
			setValue2('penerima',null);
			setValue2('sloc',null);
			kosongkan();
		}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					document.getElementById('nodok').value = trim(con.responseText);
					setValue2('untukunit',null);
					setValue2('penerima',null);
					
					getBastList(gudang);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}





function loadSubunit(induk, penerimax, karyawanid) {
	tanggal=document.getElementById('tanggal').value;
	penerima = penerimax;
	param = 'induk=' + induk + '&subunitx=' + karyawanid+ '&tanggal=' + tanggal;
	document.getElementById('subunit').innerHTML = '';
	document.getElementById('blok').innerHTML = '';
	tujuan = 'log_slave_getSubUnitOption.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					valSplit = con.responseText.split("####");
					document.getElementById('subunit').innerHTML = valSplit[0];
					document.getElementById('blok').innerHTML = valSplit[0];
					document.getElementById('tipeorg').value = valSplit[1];
					document.getElementById('mesin').innerHTML = valSplit[2];
					//loadMesin(induk);
					loadKaryawan(induk, penerima, karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdept(jns_pekerjan,kodedept,lokasi_kerja) {
	lokasi_kerja = getValue('untukunit');	
	param = 'jnsPekerjaan=' + jns_pekerjan + '&proses=getdept'
	param += '&kodeorg=' + lokasi_kerja;	
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
function loadKaryawan(induk, penerima, karyawanid) {
	unit = document.getElementById('untukunit').value;
	subunit = document.getElementById('subunit').value;
	tanggal = document.getElementById('tanggal').value;
	bisakosong = '1';
	param = 'unit=' + unit + '&subunit=' + subunit + '&penerima=' + penerima + '&karyawanid=' + karyawanid+'&tanggal='+tanggal+'&bisakosong='+bisakosong;
	tujuan = 'log_slave_getKaryawanOption.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					exp = con.responseText.split('####');
					document.getElementById('blok').options[0].selected = true;
					document.getElementById('penerima').innerHTML = exp[0];
					document.getElementById('karyawanid').innerHTML = exp[1];
					getKegiatan(induk);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function blankdepartmen(val) {
	if (val != '') {
		document.getElementById('karyawanid').selectedIndex = 0;
	}
}
function blankkaryawanid(val) {
	if (val != '') {
		document.getElementById('departemen').selectedIndex = 0;
	}
}
function loadMesin(induk) {
	param = 'induk=' + induk;
	tujuan = 'log_slave_getMesinOption.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('blok').options[0].selected = true;
					document.getElementById('mesin').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getKegiatan(blok, x) {
	subunit = document.getElementById('subunit').value;
	kodebarang = document.getElementById('kodebarang').value;
	blok_get = document.getElementById('blok').value;
	if(blok_get == ''){
		blok = blok;
	}else{
		blok = blok_get;
	}
	untukunit = document.getElementById('untukunit').value;
	statusblok = document.getElementById('statusblok').value;
	param = 'blok=' + blok + '&jenis=' + x + '&subunit=' + subunit;
	param += '&untukunit=' + untukunit;
	param += '&statusblok=' + statusblok;
	param += '&kodebarang=' + kodebarang;
	tujuan = 'log_slave_getKegiatanBlok.php';
	if (x == 'TRAKSI') {
		//document.getElementById('blok').options[0].selected = true;
		document.getElementById('kmhm').disabled = false;
		
	} else if (x == 'BLOK') {
		document.getElementById('kmhm').disabled = true;
		document.getElementById('kmhm').value = '';
		// document.getElementById('mesin').options[0].selected = true;
	}
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('kegiatan').innerHTML = con.responseText;
					getSegment();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getStatusblok(blok) {
	tanggal=document.getElementById('tanggal').value;
	untukunit = document.getElementById('untukunit').value;
	induk = document.getElementById('subunit').value;
	param = 'induk=' + induk + '&untukunit=' + untukunit+ '&tanggal=' + tanggal+ '&blok=' + blok;
	tujuan = 'log_slave_getStatusblok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(blok.length > 3){
						data = con.responseText.split("####");
						document.getElementById('statusblok').innerHTML = data[0];
						getKegiatan(blok);
					}else{
						data = con.responseText.split("####");
						document.getElementById('blok').innerHTML = data[1];
						document.getElementById('mesin').innerHTML = data[3];
						getKegiatan(blok);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadBlock(induk) {
	tanggal=document.getElementById('tanggal').value;
	untukunit = document.getElementById('untukunit').value;
	param = 'induk=' + induk + '&untukunit=' + untukunit+ '&tanggal=' + tanggal;;
	document.getElementById('blok').innerHTML = '';
	document.getElementById('mesin').options[0].selected = true;
	tujuan = 'log_slave_getSubUnitOption.php';
	if (induk != '')
		post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('blok').innerHTML = data[0];
					document.getElementById('mesin').innerHTML = data[2];
					getStatusblok(induk);
					// if(induk.length > 6){
					// 	// document.getElementById('statusblok').innerHTML = "";
					// 	// setValue2('statusblok','');
					// 	// document.getElementById('statusblok').disabled = true;
					// 	// getKegiatan(induk);
					// 	getStatusblok(induk);
					// }else{
					// 	// document.getElementById('statusblok').disabled = false;
					// 	getStatusblok(induk);
					// }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getPT(gudang) {
	param = 'gudang=' + gudang;
	tujuan = 'log_slave_gudangGetPTOption.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('pemilikbarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function disableHeader() {
	document.getElementById('tanggal').disabled = true;
	document.getElementById('untukunit').disabled = true;
	document.getElementById('penerima').disabled = false;
	document.getElementById('catatan').disabled = true;
	document.getElementById('departemen').disabled = true;
	document.getElementById('karyawanid').disabled = true;
	tipeorg = document.getElementById('tipeorg').value;
	// if(tipeorg == 'KEBUN'){
	// 	document.getElementById('subunit').disabled=false;
	// }else{
	// 	document.getElementById('subunit').disabled=true;
	// }
}
function enableHeader() {
	document.getElementById('tanggal').disabled = false;
	document.getElementById('untukunit').disabled = false;
	document.getElementById('penerima').disabled = false;
	document.getElementById('catatan').disabled = false;
	document.getElementById('departemen').disabled = false;
	document.getElementById('karyawanid').disabled = false;
	document.getElementById('subunit').disabled = false;
}
function showWindowBarang(title, ev) {
	content = "<div style='width:100%;'>";
	content += "<center><fieldset>" + title + "&nbsp;<input type=text id=txtnamabarang class=myinputtext style=width:200px onkeypress=\"return enterEuy(event);\" maxlength=35>&nbsp;<button class=mybutton onclick=goCariBarang()>Search</button> ";
	content += "<div id=containercari style='overflow:auto;max-height:25%;min-width:300px'></div></center></fieldset></div>";
	//display window
	width = 'auto';
	height = 'auto';
	//showDialog1(title, content, width, height, ev);
	
	alertify.popup("Cari Barang",content).set({'resizable':true,'maximizable':true}).resizeTo('30%','70%');
	
	document.getElementById("txtnamabarang").focus();
}
function enterEuy(evt) {
	key = getKey(evt);
	if (key == 13) {
		goCariBarang();
	} else {
		return tanpa_kutip(evt);
	}
}
function loadField(kode, nama, satuan) {
	document.getElementById('kodebarang').value = kode;
	document.getElementById('namabarang').value = nama;
	document.getElementById('satuan').value = satuan;
	// closeDialog();
	alertify.popup().destroy();
}
function kosongkan() {
	document.getElementById('kodebarang').value = '';
	document.getElementById('olbBlok').value = '';
	document.getElementById('namabarang').value = '';
	document.getElementById('penerima').value = '';
	document.getElementById('catatan').value = '';
	document.getElementById('satuan').value = '';
	document.getElementById('qty').value = 0;
	document.getElementById('blok').innerHTML = "<option value=''></option>";
	// document.getElementById('mesin').options[0].selected = true;
	// document.getElementById('kegiatan').options[0].selected = true;
	document.getElementById('subunit').innerHTML = "<option value=''></option>";
	document.getElementById('bastcontainer').innerHTML = "";
	
	setValue2('mesin',null);
	setValue2('kegiatan',null);
	
	enableHeader();
}
function nextItem2() {
	param = 'method=nextItem';
	tujuan = 'log_slave_getpic.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('tblpic').innerHTML = '';
					document.getElementById('tblpic2').innerHTML = '';
					nextItem();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function nextItem() {
	document.getElementById('kodebarang').disabled = false;
	document.getElementById('satuan').disabled = false;
	document.getElementById('namabarang').disabled = false;
	//document.getElementById('blok').disabled=false;
	document.getElementById('kodebarang').value = '';
	document.getElementById('namabarang').value = '';
	document.getElementById('satuan').value = '';
	document.getElementById('kmhm').value = '';
	document.getElementById('qty').value = '';
	// document.getElementById('subunit').disabled=false;
	document.getElementById('method').value = 'insert';
	document.getElementById('mesin').options[0].selected = true;
	document.getElementById('kegiatan').options[0].selected = true;
	
	setValue2('mesin',null);
	setValue2('kegiatan',null);
}
function bastBaru() {
	nextItem();
	kosongkan();
	setSloc('simpan');
	//document.getElementById('untukunit').options[0].selected = true;
	
	
	document.getElementById('bastcontainer').innerHTML = '';
}
function goCariBarang() {
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	nodok = document.getElementById('nodok').value;
	tanggal = document.getElementById('tanggal').value;
	if (nodok == '') {
		alertify.alert('Document Number is Obligatory');
	} else {
		txtcari = trim(document.getElementById('txtnamabarang').value);
		pemilikbarang = document.getElementById('pemilikbarang');
		pemilikbarang = pemilikbarang.options[pemilikbarang.selectedIndex].value;
		if (document.getElementById('nodok') == '') {
			alertify.alert('Document number is obligatory');
		} else
			if (pemilikbarang.length < 3) {
				alertify.alert('Googs Owner(PT) is obligatory');
			} else {
				if (txtcari.length < 1) {
					alertify.alert('material name min. 1 char');
				} else {
					param = 'txtcari=' + txtcari + '&pemilikbarang=' + pemilikbarang;
					param += '&gudang=' + gudang;
					param += '&tanggal=' + tanggal;
					tujuan = 'log_slave_cariBarang.php';
					post_response_text(tujuan, param, respog);
				}
			}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containercari').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveItemBast() {
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	tanggal = document.getElementById('tanggal').value;
	x = tanggal;
	_start = document.getElementById(gudang + '_start').value;
	_end = document.getElementById(gudang + '_end').value;
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("-", "");
	}
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("/", "");
	}
	curdateY = x.substr(4, 4).toString();
	curdateM = x.substr(2, 2).toString();
	curdateD = x.substr(0, 2).toString();
	curdate = curdateY + curdateM + curdateD;
	curdate = parseInt(curdate);
	if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
		alertify.alert('Date out of range')
	} else {
		nodok = trim(document.getElementById('nodok').value);
		norequest = trim(document.getElementById('norequest').value);
		tanggal = trim(document.getElementById('tanggal').value);
		kodebarang = trim(document.getElementById('kodebarang').value);
		penerima = trim(document.getElementById('penerima').value);
		catatan = trim(document.getElementById('catatan').value);
		satuan = trim(document.getElementById('satuan').value);
		qty = trim(document.getElementById('qty').value);
		method = trim(document.getElementById('method').value);
		blok = document.getElementById('blok').value;
		segment = document.getElementById('segment');
		segment = trim(segment.options[segment.selectedIndex].value);
		mesin = document.getElementById('mesin');
		mesin = trim(mesin.options[mesin.selectedIndex].value);
		untukunit = document.getElementById('untukunit');
		untukunit = trim(untukunit.options[untukunit.selectedIndex].value);
		subunit = document.getElementById('subunit');
		subunit = trim(subunit.options[subunit.selectedIndex].value);
		kegiatan = document.getElementById('kegiatan');
		kegiatan = trim(kegiatan.options[kegiatan.selectedIndex].value);
		gudang = trim(document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value);
		pemilikbarang = trim(document.getElementById('pemilikbarang').options[document.getElementById('pemilikbarang').selectedIndex].value);
		dept = trim(document.getElementById('dept').value);
		
		statusblok = trim(document.getElementById('statusblok').options[document.getElementById('statusblok').selectedIndex].value);
		
		
		// departemen =trim(document.getElementById('departemen').options[document.getElementById('departemen').selectedIndex].value);
		// karyawanid =trim(document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value);
		olbBlok = document.getElementById('olbBlok').value;
		oldmesin = document.getElementById('oldmesin').value;
		kmhm = trim(document.getElementById('kmhm').value); kmhm=remove_comma_var(kmhm);	
		if (nodok == '') {
			alertify.alert('Document Number is obligatory');
			return;
		}
		if (untukunit == '') {
			alertify.alert('Bussiness unit(Unit) is obligatory');
			return;
		}
		if (kegiatan == '') {
			alertify.alert('Activity is obligatory');
			return;
		}
		if (penerima == '') {
			alertify.alert('Recipient name is obligatory');
			return;
		}
		if (kodebarang == '' || satuan == '' || parseFloat(qty) < 0.001) {
			alertify.alert('Material, UOM and volume is obligatory');
			return;
		}
		// if(departemen=='' && karyawanid=='')
		// {
		// alertify.alert('Departemen atau Nama karyawan harus dipilih.');return;
		// }
		
		if (kegiatan.substr(0, 5) == '41102') {
			if (mesin == '') {
				alertify.alert('Untuk Kegiatan akun transit harap mengisikan kendaraan');
				return;
			}
		}
		if (confirm('Are you sure?')) {
			param = 'nodok=' + nodok + '&norequest=' + norequest + '&tanggal=' + tanggal + '&kodebarang=' + kodebarang;
			param += '&penerima=' + penerima + '&satuan=' + satuan + '&qty=' + qty;
			param += '&blok=' + blok + '&mesin=' + mesin + '&untukunit=' + untukunit;
			param += '&gudang=' + gudang + '&pemilikbarang=' + pemilikbarang;
			param += '&catatan=' + catatan + '&kegiatan=' + kegiatan;
			param += '&segment=' + segment + '&olbBlok=' + olbBlok;
			param += '&subunit=' + subunit + '&method=' + method;
			param += '&oldmesin=' + oldmesin;
			param += '&kmhm=' + kmhm;
			param += '&dept=' + dept;
			param += '&statusblok=' + statusblok;
			tujuan = 'log_slave_saveBast.php';
			post_response_text(tujuan, param, respog);
			disableHeader();
			document.getElementById('qty').style.backgroundColor = 'red';
		}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('qty').style.backgroundColor = '#ffffff';
					document.getElementById('bastcontainer').innerHTML = con.responseText;
					nextItem();
					document.getElementById('method').value = 'insert';
					document.getElementById('subunit').value = '';
					document.getElementById('blok').innerHTML = '';
					setValue2('subunit',null);
					document.getElementById('tblpic').innerHTML = '';
					document.getElementById('tblpic2').innerHTML = '';
					clearallpic();
					getKegiatan(untukunit);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getBastList(gudang) {
	param = 'gudang=' + gudang;
	tujuan = 'log_slave_getBastList.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerlist').innerHTML = con.responseText;
					clearallpic();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function delBast(notransaksi, kodebarang, kodeblok,norequest,kodemesin) {
	untukunit = document.getElementById('untukunit');
	untukunit = trim(untukunit.options[untukunit.selectedIndex].value);
	pemilikbarang = document.getElementById('pemilikbarang');
	pemilikbarang = trim(pemilikbarang.options[pemilikbarang.selectedIndex].value);
	param = 'nodok=' + notransaksi + '&kodebarang=' + kodebarang;
	param += '&delete=true&blok=' + kodeblok + '&pemilikbarang=' + pemilikbarang;
	param += '&untukunit=' + untukunit;
	param += '&norequest=' + norequest;
	param += '&kodemesin=' + kodemesin;
	tujuan = 'log_slave_saveBast.php';
	if (confirm('Deleting Document ' + notransaksi + ', are you sure..?'))
		post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('bastcontainer').innerHTML = con.responseText;
					clearallpic();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editBast(kodebarang, namabarang, satuan, jumlah, kodeblok, kodekegiatan, kodemesin, kodesegment,kmhm) {
	// EDITNYA SUSAH KEBANYAKAN ONCHANGE, SEKALIAN AJA DIMATIKAN, KALAU MAU EDIT TINGGAL DEL TERUS BUAT BARU
	
	//set blok karena merupakan primary
	// document.getElementById('blok').innerHTML="<option value=''></option><option value='"+kodeblok+"'>"+kodeblok+"</option>";
	document.getElementById('mesin').value = kodemesin;
	document.getElementById('kodebarang').value = kodebarang;
	document.getElementById('namabarang').value = namabarang;
	document.getElementById('satuan').value = satuan;
	document.getElementById('olbBlok').value = kodeblok;
	document.getElementById('oldmesin').value = kodemesin;
	// document.getElementById('subunit').value=kodeblok;
	document.getElementById('kodebarang').disabled = true;
	document.getElementById('satuan').disabled = true;
	document.getElementById('namabarang').disabled = true;
	//document.getElementById('blok').disabled=true;
	//	document.getElementById('subunit').disabled=true;
	document.getElementById('qty').value = jumlah;
	document.getElementById('kmhm').value = kmhm;
	if(kodemesin!=''){		
		document.getElementById('kmhm').disabled = false;
	}
	
	
	blk = document.getElementById('blok');
	for (x = 0; x < blk.length; x++) {
		if (blk.options[x].value == kodeblok) {
			blk.options[x].selected = true;
		}
	}
	sbdt = kodeblok.substr(0, 6);
	subunit = document.getElementById('subunit');
	for (x = 0; x < subunit.length; x++) {
		if (subunit.options[x].value == sbdt) {
			subunit.options[x].selected = true;
		}
	}
	
	

	keg = document.getElementById('kegiatan');
	for (x = 0; x < keg.length; x++) {
		if (keg.options[x].value == kodekegiatan) {
			keg.options[x].selected = true;
		}
	}
	//document.getElementById('kegiatan').innerHTML="<option value='"+kodekegiatan+"'>"+kodekegiatan+"</option>";
	
	segment = document.getElementById('segment');
	for (x = 0; x < segment.length; x++) {
		if (segment.options[x].value == kodesegment) {
			segment.options[x].selected = true;
		}
	}
	document.getElementById('method').value = 'update';
	nodok = document.getElementById('nodok').value;
	norequest = document.getElementById('norequest').value;
	qty = document.getElementById('qty').value;
	param = 'method=editBast&nodok=' + nodok + '&norequest=' + norequest + '&kodebarang=' + kodebarang + '&qty=' + qty;
	tujuan = 'log_slave_getpic.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('tblpic').innerHTML = con.responseText;
					disableHeader();
					if(kodeblok!=''){
						x='BLOK';
						getKegiatan(kodeblok, x);
					}else{
						x='TRAKSI';
						getKegiatan(kodemesin, x);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function delXBapb(nodok) {
	if (confirm('Deleting Doc: ' + nodok + ', Are sure..?')) {
		param = 'notransaksi=' + nodok;
		tujuan = 'log_slave_deleteBapb.php'; //file ini berfungsi untuk penerimaan dan pengeluaran
		if (confirm('All data in this document will be removed. Continue ?')) {
			post_response_text(tujuan, param, respog);
		}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
					setSloc('simpan');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editXBast(notransaksi, untukunit, subunit, sbuni, tanggal, namapenerima, keterangan, tipeorg, norequest) {
	nextItem();
	document.getElementById('nodok').value = notransaksi;
	document.getElementById('norequest').value = norequest;
	document.getElementById('tanggal').value = tanggal;
	document.getElementById('penerima').value = namapenerima;
	document.getElementById('catatan').value = keterangan;
	document.getElementById('tipeorg').value = tipeorg;
	
	
	if (norequest != '') {
		document.getElementById('imgnorequest').style.display = 'none';
	}
	subunitx = subunit;
	if ((namapenerima.substr(0, 3) == '000') && (namapenerima.length == 10)) {}
	else {
		if (namapenerima != 'masyarakat')
			document.getElementById('catatan').value += ' received by:' + namapenerima;
	}
	unt = document.getElementById('untukunit');
	for (x = 0; x < unt.length; x++) {
		if (unt.options[x].value == untukunit) {
			unt.options[x].selected = true;
		}
	}
	
	setValue2('untukunit',untukunit);
	setValue2('penerima',namapenerima);
	
	tabAction(document.getElementById('tabFRM0'), 0, 'FRM', 1); //jangan tanya darimana
	// loadSubunit(untukunit,'0','0');
	tujuan = 'log_slave_saveBast.php';
	param = 'nodok=' + notransaksi + '&displayonly=true';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('bastcontainer').innerHTML = con.responseText;
					disableHeader();
					editloadSubunit(untukunit, namapenerima, sbuni);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editloadSubunit(induk, penerimax, subunitx) {
	penerima = penerimax;
	tanggal=document.getElementById('tanggal').value;
	param = 'induk=' + induk + '&subunitx=' + subunitx+ '&tanggal=' + tanggal;
	document.getElementById('subunit').innerHTML = '';
	document.getElementById('blok').innerHTML = '';
	tujuan = 'log_slave_getSubUnitOption.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					
					valSplit = con.responseText.split("####");
					document.getElementById('subunit').innerHTML = valSplit[0];
					editloadKaryawan(induk, penerima);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editloadKaryawan(induk, penerima) {
	unit = document.getElementById('untukunit').value;
	subunit = document.getElementById('subunit').value;
	tanggal = document.getElementById('tanggal').value;
	param = 'unit=' + unit + '&subunit=' + subunit + '&penerima=' + penerima+'&tanggal='+tanggal;
	tujuan = 'log_slave_getKaryawanOption.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('penerima').innerHTML = con.responseText;
					loadBlock(subunit);
					// getKegiatan(induk);
					// document.getElementById('blok').options[0].selected=true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cariBast(num) {
	tex = trim(document.getElementById('txtbabp').value);
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	if (gudang == '') {
		alertify.alert('Storage Location  is obligatory')
	} else {
		param = 'gudang=' + gudang;
		param += '&page=' + num;
		if (tex != '')
			param += '&tex=' + tex;
		tujuan = 'log_slave_getBastList.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerlist').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function previewBast(notransaksi, ev) {
	param = 'notransaksi=' + notransaksi;
	tujuan = 'log_slave_print_bast_pdf.php?' + param;
	//display window
	title = notransaksi;
	width = '800';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	//showDialog2(title, content, width, height, ev);
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_print_bast_pdf.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');	
}
/**
 * getSegment
 * Mengambil Segment sesuai bloknya, lookup ke tabel proporsi segment
 * Jika tidak ada maka return nilai default '0000000001'
 */
function previewhtml(notransaksi) {
	param = "notransaksi=" + notransaksi;
	post_response_text("log_slave_print_bast.php", param, respog);
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
function getSegment() {
	var blok = getValue('blok');
	subunit = document.getElementById('subunit').value;
	param = "";
	if (blok != '') {
		param = "kodeblok=" + blok;
		post_response_text("log_slave_getSegmentBlok.php", param, respog);
	} else {
		// getStatusblok(subunit);
		//getvhc();
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('segment').innerHTML = con.responseText;
					// getStatusblok(subunit);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getvhc() {
	var blok = getValue('subunit');
	param = "kodeblok=" + blok;
	// if(blok!=''){
	post_response_text("log_slave_getvhc.php", param, respog);
	// }
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('mesin').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getpic(ev) {
	nodok = document.getElementById('nodok').value;
	kodebarang = document.getElementById('kodebarang').value;
	untukunit = document.getElementById('untukunit').value;
	qty = document.getElementById('qty').value;
	if (nodok == '') {
		alertify.alert('Document Number is Obligatory');
		return false;
	}
	if (kodebarang == '') {
		alertify.alert("Kode barang belum diisi.");
		return;
	}
	if (qty == '0' || qty == '') {
		alertify.alert("Jumlah tidak boleh 0 atau kosong.");
		return;
	}
	content = "<div style='width:100%;'>";
	content += "<fieldset><div id=divpic style='overflow:auto;max-height:317px;min-width:300px'></div></fieldset>";
	width = 'auto';
	height = 'auto';
	showDialog1('PIC/Department', content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	document.getElementById('dynamic1').style.left = (pos[0] - 600) + 'px';
	param = 'method=getpicform&kodebarang=' + kodebarang + '&qty=' + qty + '&untukunit=' + untukunit;
	tujuan = 'log_slave_getpic.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('divpic').innerHTML = con.responseText;
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
// joki
function getpic2(ev) {
	nodok = document.getElementById('nodok').value;
	kodebarang = document.getElementById('kodebarang').value;
	untukunit = document.getElementById('untukunit').value;
	subunit = document.getElementById('subunit').value;
	qty = document.getElementById('qty').value;

	kegiatan = document.getElementById('kegiatan').value;

	if (nodok == '') {
		alertify.alert('Document Number is Obligatory');
		return false;
	}
	if (kodebarang == '') {
		alertify.alert("Kode barang belum diisi.");
		return;
	}
	if (kegiatan == '') {
		alertify.alert("Kegiatan belum dipilih.");
		return;
	}
	if (kegiatan.substr(0,1) != '6') {
		alertify.alert("Kegiatan harus akaun kepala 6.");
		return;
	}
	
	if (qty == '0' || qty == '') {
		alertify.alert("Jumlah tidak boleh 0 atau kosong.");
		return;
	}
	
	content = "<div style='width:100%;'>";
	content += "<fieldset><div id=divpic2 style='overflow:auto;max-height:317px;min-width:300px'></div></fieldset>";
	width = 'auto';
	height = 'auto';
	showDialog1('Tambah Presentase', content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	document.getElementById('dynamic1').style.left = (pos[0] - 600) + 'px';
	param = 'method=getpicform&kodebarang=' + kodebarang + '&qty=' + qty + '&untukunit=' + untukunit + '&subunit=' + subunit;
	tujuan = 'log_slave_getpic2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('divpic2').innerHTML = con.responseText;
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function addpic2(totalloop) {
	// picpic = document.getElementById('picpic').options[document.getElementById('picpic').selectedIndex].value;
	// qtypresentase = document.getElementById('qtypresentase').value;
	kodebarang = document.getElementById('kodebarang').value;
	qty = document.getElementById('qty').value;
	// if (picpic == '') {
	// 	alertify.alert("Nama karyawan belum dipilih.");
	// 	return;
	// }
	if (qty == '0' || qty == '') {
		alertify.alert("Jumlah tidak boleh 0 atau kosong.");
		return;
	}
	// if (qtypresentase == '0' || qtypresentase == '') {
	// 	alertify.alert("Jumlah tidak boleh 0 atau kosong.");
	// 	return;
	// }
	// if (qtypresentase > 100) {
	// 	alertify.alert("Jumlah presentase tidak boleh lebih dari 100.");
	// 	return;
	// }
	strUrl="";
	for(var i=1;i<=totalloop;i++){
		let qtypresentase = document.getElementById('qtypresentase_' + i);
		if (!qtypresentase.disabled) {

			if(trim(document.getElementById('qtypresentase_'+i).value) <= 0 ){
				alert('Presentasi harus lebih dari 0');
				return false;
			}

			if(trim(document.getElementById('qtypemakaian_'+i).value) <= 0 ){
				alert('Presentasi harus lebih dari 0');
				return false;
			}

			// qtypic = parseFloat(trim(document.getElementById('qtypresentase_'+i).value)) * parseFloat(qty) / 100;

			strUrl +='&picpic[]='+trim(document.getElementById('picpic_'+i).value)+'&qtypresentase[]='+trim(document.getElementById('qtypresentase_'+i).value)+'&qtypic[]='+trim(document.getElementById('qtypemakaian_'+i).value);
		}

    }

	param = 'method=addpic2&kodebarang=' + kodebarang + '&qty=' + qty;
	param += strUrl;
	tujuan = 'log_slave_getpic2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('picpic').selectedIndex = 0;
					// setValue2('picpic',null);
					// document.getElementById('qtypresentase').value = '0';
					valSplit = con.responseText.split("####");
					document.getElementById('trpic2').innerHTML = valSplit[0];
					document.getElementById('tblpic2').innerHTML = valSplit[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clearallpic2() {
	param = 'method=nextItem';
	tujuan = 'log_slave_getpic2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('tblpic2').innerHTML = '';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ceklis_P(no, count) {
    let checkedCount = 0;
	qty = document.getElementById('qty').value;

    // First, count how many checkboxes are checked
    for (let i = 1; i <= count; i++) {
        let ar = document.getElementById('pic_p_' + i);
        if (ar.checked) {
            checkedCount++;
        }
    }

    // Calculate the maximum value for each input based on the number of checked checkboxes
    let maxPerInput = Math.floor(100 / checkedCount);
    let remainder = 100 % checkedCount;

    // Enable/disable inputs and set their max values
    for (let i = 1; i <= count; i++) {
        let ar = document.getElementById('pic_p_' + i);
        let qtypresentase = document.getElementById('qtypresentase_' + i);
        let qtypemakaian = document.getElementById('qtypemakaian_' + i);

        if (ar.checked) {
            qtypresentase.disabled = false;
            qtypemakaian.disabled = false;
            if (i <= remainder) {
                qtypresentase.max = maxPerInput + 1; // Distribute remainder evenly
            } else {
                qtypresentase.max = maxPerInput;
            }
        } else {
            qtypresentase.disabled = true;
            qtypemakaian.disabled = true;
            qtypresentase.value = 0;
            qtypemakaian.value = 0;
        }
    }

    // Ensure the total does not exceed 100
    let total = 0;
	let totalpemakaian = 0;
    for (let i = 1; i <= count; i++) {
        let qtypresentase = document.getElementById('qtypresentase_' + i);
        let qtypemakaian = document.getElementById('qtypemakaian_' + i);
        if (!qtypresentase.disabled) {
            let value = parseInt(qtypresentase.value) || 0;
            total += value;
        }
        if (!qtypemakaian.disabled) {
            let valuepemekaian = parseInt(qtypemakaian.value) || 0;
            totalpemakaian += valuepemekaian;
        }
    }

    if (total > 100) {
        alert("Total nilai tidak boleh melebihi 100.");
        for (let i = 1; i <= count; i++) {
            let qtypresentase = document.getElementById('qtypresentase_' + i);
            // if (!qtypresentase.disabled) {
            //     qtypresentase.value = 0;
            // }
        }
    }
    if (totalpemakaian > qty) {
		alert("Nilai tidak boleh melebih jumlah pemakain");
        for (let i = 1; i <= count; i++) {
            let qtypemakaian = document.getElementById('qtypemakaian_' + i);
            // if (!qtypemakaian.disabled) {
            //     qtypemakaian.value = 0;
            // }
        }
    }
}

function validateQty(input, no, count) {
	
	let total = 0;
	let totalpemakaian = 0;
	qty = document.getElementById('qty').value;

	// presentase
    for (let i = 1; i <= count; i++) {
        let qtypresentase = document.getElementById('qtypresentase_' + i);
		let qtypemakaian = document.getElementById('qtypemakaian_' + i);

        if (!qtypresentase.disabled) {
            let value = parseInt(qtypresentase.value) || 0;
            total += value;
        }

		if (!qtypemakaian.disabled) {
            let valuepemekaian = parseInt(qtypemakaian.value) || 0;
            totalpemakaian += valuepemekaian;
        }

    }

    if (total > 100) {
        alert("Nilai tidak boleh melebihi 100");
        input.value = 0;
    }

    if (totalpemakaian > qty) {
        alert("Nilai tidak boleh jumlah pemakain");
        input.value = 0;
    }


    ceklis_P(no, count); // Recalculate the total and check if it exceeds 100
}



// end joki

function setblankdepartment() {
	document.getElementById('departemenpic').selectedIndex = 0;
}
function setblankpic() {
	document.getElementById('picpic').selectedIndex = 0;
}
function addpic() {
	picpic = document.getElementById('picpic').options[document.getElementById('picpic').selectedIndex].value;
	departemenpic = document.getElementById('departemenpic').options[document.getElementById('departemenpic').selectedIndex].value;
	qtypic = document.getElementById('qtypic').value;
	kodebarang = document.getElementById('kodebarang').value;
	qty = document.getElementById('qty').value;
	if (picpic == '' && departemenpic == '') {
		alertify.alert("Nama karyawan atau Departemen belum dipilih.");
		return;
	}
	if (qtypic == '0' || qtypic == '') {
		alertify.alert("Jumlah tidak boleh 0 atau kosong.");
		return;
	}
	param = 'method=addpic&picpic=' + picpic + '&departemenpic=' + departemenpic + '&qtypic=' + qtypic + '&kodebarang=' + kodebarang + '&qty=' + qty;
	tujuan = 'log_slave_getpic.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('picpic').selectedIndex = 0;
					document.getElementById('departemenpic').selectedIndex = 0;
					setValue2('departemenpic',null);
					setValue2('picpic',null);
					document.getElementById('qtypic').value = '0';
					valSplit = con.responseText.split("####");
					document.getElementById('trpic').innerHTML = valSplit[0];
					document.getElementById('tblpic').innerHTML = valSplit[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletepic2(kodebarang, qty, picpic) {
	param = 'method=deletepic2&picpic=' + picpic + '&kodebarang=' + kodebarang + '&qty=' + qty;
	tujuan = 'log_slave_getpic2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('picpic').selectedIndex = 0;
					// document.getElementById('qtypresentase').value = '0';
					valSplit = con.responseText.split("####");
					document.getElementById('trpic2').innerHTML = valSplit[0];
					document.getElementById('tblpic2').innerHTML = valSplit[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function clearallpic() {
	param = 'method=nextItem';
	tujuan = 'log_slave_getpic.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('tblpic').innerHTML = '';
					clearallpic2();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function searchrequest(ev) {
	nodok = document.getElementById('nodok').value;
	if (nodok == '') {
		alertify.alert('Document Number is Obligatory');
		return false;
	}
	content = "<div style='width:100%;'>";
	content += "<fieldset><div id=divpic style='overflow:auto;max-height:317px;'></div></fieldset>";
	width = 'auto';
	height = 'auto';
	showDialog1('Search No. Request', content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	document.getElementById('dynamic1').style.left = (pos[0]) + 'px';
	param = 'method=searchrequest';
	tujuan = 'log_slave_getpic.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('divpic').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function carinorequest() {
	crnorequest = document.getElementById('crnorequest').value;
	param = 'method=carinorequest&crnorequest=' + crnorequest;
	tujuan = 'log_slave_getpic.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('listnorequest').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function showdetail(norequest) {
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	pemilikbarang = document.getElementById('pemilikbarang').options[document.getElementById('pemilikbarang').selectedIndex].value;
	param = 'method=showdetail&norequest=' + norequest + '&gudang=' + gudang + '&pemilikbarang=' + pemilikbarang;
	tujuan = 'log_slave_getpic.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('listnorequest').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletepicrequest(norequest, kodebarang, picpic, departemenpic, qtypic, urut) {
	jumlahpermintaan = document.getElementById('jumlahpermintaan_' + urut).value;
	param = 'method=deletepicrequest&norequest=' + norequest + '&kodebarang=' + kodebarang + '&picpic=' + picpic + '&departemenpic=' + departemenpic + '&=qtypic' + qtypic + '&urut=' + urut;
	tujuan = 'log_slave_getpic.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('tablepicrequest_' + kodebarang).innerHTML = con.responseText;
					hasil = parseFloat(jumlahpermintaan) - parseFloat(qtypic);
					document.getElementById('jumlahpermintaan_' + urut).value = hasil;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function insertnorequest(norequest, row) {
	tanggal = document.getElementById('tanggal').value;
	x = tanggal;
	_start = document.getElementById(gudang + '_start').value;
	_end = document.getElementById(gudang + '_end').value;
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("-", "");
	}
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("/", "");
	}
	curdateY = x.substr(4, 4).toString();
	curdateM = x.substr(2, 2).toString();
	curdateD = x.substr(0, 2).toString();
	curdate = curdateY + curdateM + curdateD;
	curdate = parseInt(curdate);
	if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
		alertify.alert('Date out of range');
		return;
	}
	untukunit = document.getElementById('untukunit');
	untukunit = trim(untukunit.options[untukunit.selectedIndex].value);
	if (untukunit == '') {
		alertify.alert('Bussiness unit(Unit) is obligatory');
		return;
	}
	penerima = trim(document.getElementById('penerima').value);
	if (penerima == '') {
		alertify.alert('Recipient name is obligatory');
		return;
	}
	strUrl = '';
	for (i = 1; i <= row; i++) {
		trkodebarang = document.getElementById('trkodebarang_' + i).innerHTML;
		jumlahstok = document.getElementById('jumlahstok_' + i).value;
		jumlahpermintaan = document.getElementById('jumlahpermintaan_' + i).value;
		strUrl += '&kodebarang[]=' + trkodebarang + '&jumlahstok[]=' + jumlahstok + '&jumlahpermintaan[]=' + jumlahpermintaan;
	}
	param = 'method=insertnorequest&norequest=' + norequest;
	param += strUrl;
	tujuan = 'log_slave_getpic.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = JSON.parse(con.responseText);
					document.getElementById('catatan').value = data['head']['keterangan'];
					for (i = 0; i < data['detail'].length; i++) {
						saveitemrequest(data['detail'][i]['kodebarang'], data['detail'][i]['satuan'], data['detail'][i]['jumlah'], data['detail'][i]['subunit'], data['detail'][i]['kodeblok'], data['detail'][i]['kodemesin'], data['detail'][i]['kodekegiatan'], norequest);
					}
					document.getElementById('norequest').value = norequest;
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveitemrequest(kodebarang, satuan, qty, subunit, blok, mesin, kegiatan, norequest) {
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	tanggal = document.getElementById('tanggal').value;
	nodok = trim(document.getElementById('nodok').value);
	tanggal = trim(document.getElementById('tanggal').value);
	penerima = trim(document.getElementById('penerima').value);
	catatan = trim(document.getElementById('catatan').value);
	method = 'insert';
	segment = document.getElementById('segment');
	segment = trim(segment.options[segment.selectedIndex].value);
	untukunit = document.getElementById('untukunit');
	untukunit = trim(untukunit.options[untukunit.selectedIndex].value);
	gudang = trim(document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value);
	pemilikbarang = trim(document.getElementById('pemilikbarang').options[document.getElementById('pemilikbarang').selectedIndex].value);
	olbBlok = document.getElementById('olbBlok').value;
	if (confirm('Are you sure?')) {
		param = 'nodok=' + nodok + '&norequest=' + norequest + '&tanggal=' + tanggal + '&kodebarang=' + kodebarang;
		param += '&penerima=' + penerima + '&satuan=' + satuan + '&qty=' + qty;
		param += '&blok=' + blok + '&mesin=' + mesin + '&untukunit=' + untukunit;
		param += '&gudang=' + gudang + '&pemilikbarang=' + pemilikbarang;
		param += '&catatan=' + catatan + '&kegiatan=' + kegiatan;
		param += '&segment=' + segment + '&olbBlok=' + olbBlok;
		param += '&subunit=' + subunit + '&method=' + method;
		tujuan = 'log_slave_saveBast.php';
		post_response_text(tujuan, param, respog);
		disableHeader();
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					nextItem();
					document.getElementById('bastcontainer').innerHTML = con.responseText;
					document.getElementById('method').value = 'insert';
					document.getElementById('subunit').value = '';
					document.getElementById('blok').value = '';
					clearallpic();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getvhc() {
	var blok = getValue('subunit');
	param = "kodeblok=" + blok;
	// if(blok!=''){
	post_response_text("log_slave_getvhc.php", param, respog);
	// }

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('mesin').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showupload(ev) {
	showformupload(ev);
	nodok = document.getElementById('nodok').value;
	kodebarang = document.getElementById('kodebarang').value;
	param = 'method=showupload&notransaksi='+nodok+'&kodebarang='+kodebarang;
	tujuan = 'log_slave_penerimaanUpload.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfilesx(nodok,kodebarang);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function loadfilesx(nodok,kodebarang) {
	param = 'method=loadfiles&notransaksi='+nodok+'&kodebarang='+kodebarang;
	tujuan = 'log_slave_penerimaanUpload.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfilestop') !== null) {
						document.getElementById('listfilestop').innerHTML = con.responseText;
					}
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('listfilesview') !== null) {
						document.getElementById('listfilesview').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function save_filex(){
	var file = document.getElementById("upload").files[0];
    var notransaksi = document.getElementById('nodok').value;
    var kodebarang = document.getElementById('kodebarangupload').innerHTML;
    var jenisupload = document.getElementById('kriteriaefil').value;
    var formdata = new FormData();
    formdata.append("notransaksi", notransaksi);
    formdata.append("kodebarang", kodebarang);
    formdata.append("jenisupload", jenisupload);
    formdata.append("file", file);
    formdata.append("fileupload", document.getElementById("upload").value);
    //alert(document.getElementById("filex").value);
    if (document.getElementById("upload").value == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "log_slave_penerimaanUpload.php?method=submitfilex", true);
    busy_on();
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfilesx(notransaksi,kodebarang);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefilex(notransaksi, namafile) {
	//alert(namafile);
    var kodebarang = document.getElementById('kodebarangupload').innerHTML;
	param = 'method=deletefilex&notransaksi=' + notransaksi + '&namafile=' + namafile;
	tujuan = 'log_slave_penerimaanUpload.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfilesx(notransaksi,kodebarang);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
