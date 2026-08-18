function getHk() {
	thnBudget = document.getElementById('thnAnggran').value;
	param = 'thnBudget=' + thnBudget + '&proses=getHk';
	tujuan = 'bgt_slave_alokasi_supervisi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//	alert(con.responseText);
					document.getElementById('hkEfektif').value = con.responseText;
					kalikan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function kalikan() {
	jmlhOrg = document.getElementById('uphSupervisi').value;
	jmlhPersonel = document.getElementById('jmlhPersonel').value;
	hk = document.getElementById('hkEfektif').value;
	totHk = (jmlhOrg * jmlhPersonel) * hk;
	if (isNaN(totHk)) {
		totHk = 0;
	}
	document.getElementById('totUpah').value = totHk;
}

function tampilKan() {
	thnBudget = document.getElementById('thnAnggran').value;
	uphSprvisi = document.getElementById('uphSupervisi').value;
	jmlhPerson = document.getElementById('jmlhPersonel').value;
	hk = document.getElementById('hkEfektif').value;
	totUpah = document.getElementById('totUpah').value;
	divisi = document.getElementById('divisi').value;
	jenis = document.getElementById('jenis').value;
	alokasi = document.getElementById('alokasi').value;
	param = 'thnBudget=' + thnBudget + '&proses=getPreview' + '&uphSprvisi=' + remove_comma_var(uphSprvisi) + '&jmlhPerson=' + jmlhPerson + '&hkEfektif=' + hk;
	param += '&totUpah=' + totUpah;
	param += '&jenis=' + jenis;
	param += '&divisi=' + divisi;
	param += '&alokasi=' + alokasi;
	tujuan = 'bgt_slave_alokasi_supervisi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//	alert(con.responseText);
					document.getElementById('listPrevData').style.display = 'block';
					//document.getElementById('tmblSimpanSemua').style.display='block';
					//document.getElementById('save_kepalaBr').disabled = true;
					document.getElementById('uphSupervisi').disabled = true;
					document.getElementById('saveAwal').disabled = false;
					document.getElementById('jmlhPersonel').disabled = true;
					document.getElementById('thnAnggran').disabled = true;
					document.getElementById('containDetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function batalBr() {
	document.getElementById('save_kepalaBr').disabled = false;
	document.getElementById('thnAnggran').disabled = false;
	document.getElementById('uphSupervisi').disabled = false;
	document.getElementById('jmlhPersonel').disabled = false;
	document.getElementById('uphSupervisi').value = '';
	document.getElementById('jmlhPersonel').value = '';
	document.getElementById('totUpah').value = '';
	document.getElementById('listPrevData').style.display = 'none';
	document.getElementById('containDetail').innerHTML = '';
	// document.getElementById('containDetail').innerHTML='';
}

var baris = 1;
function saveAll(x) {
	document.getElementById('saveAwal').disabled = true;

	thnBudget = document.getElementById('thnAnggran').value;
	kBlok = document.getElementById('kdBlok_' + x).innerHTML;
	kgtn = document.getElementById('keg_' + x).innerHTML;
	noakn = document.getElementById('noakun_' + x).innerHTML;
	kegiatan = document.getElementById('kegiatanalk_' + x).innerHTML;
	jmlHk = document.getElementById('jmlhHk_' + x).innerHTML;
	hkSprvisi = document.getElementById('hkSupervisi_' + x).value;
	rpsuperVisi = document.getElementById('superVisi_' + x).value;
	volKeg = document.getElementById('vol_' + x).innerHTML;
	satKeg = document.getElementById('satuan_' + x).innerHTML;
	rotasi = document.getElementById('rotsi_' + x).innerHTML;
	totRow = document.getElementById('jmlhRow').value;
	divisi = document.getElementById('divisi').value;
	totUpah = document.getElementById('totUpah').value;
	jmlhPerson = document.getElementById('jmlhPersonel').value;
	hkEfektif = document.getElementById('hkEfektif').value;

	param = 'proses=insertAll' + '&thnBudget=' + thnBudget + '&kdBlok=' + kBlok + '&kgtn=' + kgtn;
	param += '&noakn=' + noakn + '&jmlHk=' + jmlHk + '&hkSprvisi=' + hkSprvisi;
	param += '&rpsuperVisi=' + rpsuperVisi + '&volKeg=' + volKeg + '&satKeg=' + satKeg + '&rotasi=' + rotasi;
	param += '&kegiatan=' + kegiatan;
	param += '&baris=' + x;
	param += '&divisi=' + divisi;
	param += '&jenis=' + jenis;
	param += '&totUpah=' + totUpah;
	param += '&jmlhPerson=' + jmlhPerson;
	param += '&hkEfektif=' + hkEfektif;

	tujuan = 'bgt_slave_alokasi_supervisi.php';
	
	
	if (x == 1){
		if(confirm('Are you sure ?')){			
			post_response_text(tujuan, param, respog);
		}
	}else{
		post_response_text(tujuan, param, respog);
	}
	
	
	function respog() {
		document.getElementById('rew_' + x).style.backgroundColor = 'orange';
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('rew_' + x).style.backgroundColor = 'red';
					document.getElementById('lnjutTmbl').style.display = '';

				} else {
					b = x;
					baris = x;
					row = x + 1;
					x = row;

					if (x <= totRow) {
						document.getElementById('rew_' + b).style.backgroundColor = 'green';
						document.getElementById('rew_' + b).style.display = 'none';
						saveAll(x);
					} else {
						//displayList();
						document.getElementById('rew_' + b).style.backgroundColor = 'green';
						batalBr();
						alert('Done');
						document.getElementById('saveAwal').disabled = false;
					}
				}
			} else {
				busy_off();
				document.getElementById('lnjutTmbl').style.display = '';
				error_catch(con.status);
			}
		}
	}

}

function lanjutkan() {
	saveAll(baris);
	document.getElementById('lnjutTmbl').style.display = 'none';
}

function prevSebaran() {
	thnBudget = document.getElementById('thnBudget').value;
	divisi = document.getElementById('divisisebar').value;
	jenis = document.getElementById('jenissebar').value;
	param = 'thnBudget=' + thnBudget + '&proses=getPreviewSebaran';
	param += '&divisi=' + divisi;
	param += '&jenis=' + jenis;
	tujuan = 'bgt_slave_alokasi_supervisi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//	alert(con.responseText);
					document.getElementById('contentSebaran').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function sebarulang(ttlbaris){
	var1 = parseInt(document.getElementById('ss1').value);
	var2 = parseInt(document.getElementById('ss2').value);
	var3 = parseInt(document.getElementById('ss3').value);
	var4 = parseInt(document.getElementById('ss4').value);
	var5 = parseInt(document.getElementById('ss5').value);
	var6 = parseInt(document.getElementById('ss6').value);
	var7 = parseInt(document.getElementById('ss7').value);
	var8 = parseInt(document.getElementById('ss8').value);
	var9 = parseInt(document.getElementById('ss9').value);
	var10 = parseInt(document.getElementById('ss10').value);
	var11 = parseInt(document.getElementById('ss11').value);
	var12 = parseInt(document.getElementById('ss12').value);
	ttl = var1 + var2 + var3 + var4 + var5 + var6 + var7 + var8 + var9 + var10 + var11 + var12;
	
	for(i=1;i<=ttlbaris;i++){
		ttlrp = parseInt(document.getElementById('hrg_' + i).innerHTML);
		for(n=1;n<=12;n++){
			s = parseInt(document.getElementById('ss'+n).value);
			e = s/ttl*ttlrp;
			document.getElementById('sbrn_'+n+'_'+i).value=e;
		}		
	}
	
	
	
}



var brsSebaran = 1;
function saveSebaran(x) {
	document.getElementById('tmblPrev').disabled = true;
	document.getElementById('thnBudget').disabled = true;
	kunci = document.getElementById('key_' + x).innerHTML;
	rupe = document.getElementById('hrg_' + x).innerHTML;
	fis = document.getElementById('vol_' + x).innerHTML;
	totRow = document.getElementById('jmlhRow').value;

	//param='proses=insertAllData'+'&kunci='+kunci;
	var1 = parseInt(document.getElementById('ss1').value);
	var2 = parseInt(document.getElementById('ss2').value);
	var3 = parseInt(document.getElementById('ss3').value);
	var4 = parseInt(document.getElementById('ss4').value);
	var5 = parseInt(document.getElementById('ss5').value);
	var6 = parseInt(document.getElementById('ss6').value);
	var7 = parseInt(document.getElementById('ss7').value);
	var8 = parseInt(document.getElementById('ss8').value);
	var9 = parseInt(document.getElementById('ss9').value);
	var10 = parseInt(document.getElementById('ss10').value);
	var11 = parseInt(document.getElementById('ss11').value);
	var12 = parseInt(document.getElementById('ss12').value);
	zz = var1 + var2 + var3 + var4 + var5 + var6 + var7 + var8 + var9 + var10 + var11 + var12;
	if (zz && zz > 0) {
		param = 'proses=sebarDoong&kunci=' + kunci;
		param += '&var1=' + (var1 / zz) + '&var2=' + (var2 / zz) + '&var3=' + (var3 / zz) + '&var4=' + (var4 / zz) + '&var5=' + (var5 / zz);
		param += '&var6=' + (var6 / zz) + '&var7=' + (var7 / zz) + '&var8=' + (var8 / zz) + '&var9=' + (var9 / zz) + '&var10=' + (var10 / zz);
		param += '&var11=' + (var11 / zz) + '&var12=' + (var12 / zz) + '&rupe=' + rupe + '&fis=' + fis;
		tujuan = 'bgt_budget_slave_kebun.php';

		// post_response_text(tujuan, param, respog);
	} else {
		alert('Sebaran salah');
	}
	//	alert(param);
	//        return;
	//tujuan='bgt_slave_alokasi_supervisi.php';
	if (x == 1 && confirm('Are you sure ?')) {
		post_response_text(tujuan, param, respog);
	} else if (x != 1) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		document.getElementById('save_kepala').disabled = true;
		document.getElementById('rewBr_' + x).style.backgroundColor = 'orange';
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('rewBr_' + x).style.backgroundColor = 'red';
					document.getElementById('lnjutSebaran').style.display = '';
				} else {

					b = x;
					brsSebaran = x;
					row = x + 1;
					x = row;

					if (x <= totRow) {
						document.getElementById('rewBr_' + b).style.backgroundColor = 'green';
						document.getElementById('rewBr_' + b).style.display = 'none';
						saveSebaran(x);
					} else {

						document.getElementById('tmblPrev').disabled = false;
						document.getElementById('thnBudget').disabled = false;
						document.getElementById('save_kepala').disabled = false;
						document.getElementById('contentSebaran').innerHTML = '';
						alert('Done');
					}
				}
			} else {
				busy_off();
				document.getElementById('lnjutSebaran').style.display = '';
				error_catch(con.status);
			}
		}
	}

}

function reSave() {
	saveSebaran(brsSebaran);
	document.getElementById('lnjutSebaran').style.display = 'none';
}
function clearForm(d) {
	if (confirm("Clear data ?"))
		for (mulai = 1; mulai <= 12; mulai++) {
			document.getElementById('sbrn_' + mulai + '_' + d).value = '';
		}
}
function delAll() {
	thnBudget = document.getElementById('thnBudgetUlg').value;
	divisi = document.getElementById('divisidel').value;
	jenis = document.getElementById('jenisdel').value;
	
	param = 'thnBudget=' + thnBudget + '&proses=deleteAll';
	param += '&divisi=' + divisi;
	param += '&jenis=' + jenis;
	tujuan = 'bgt_slave_alokasi_supervisi.php';
	if (confirm("Anda yakin ingin menghapus !!!")){		
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//	alert(con.responseText);
					alert("Done");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function bersihkanDonk(ttlbaris) {
	for (zx = 1; zx < 13; zx++) {
		document.getElementById('ss' + zx).value = 0;
	}
	sebarulang(ttlbaris);
}
function sebarkanBoo(kunci, baris, obj, rupe, fis) {
	document.getElementById('rewBr_' + baris).style.backgroundColor = 'orange';
	var1 = parseInt(document.getElementById('ss1').value);
	var2 = parseInt(document.getElementById('ss2').value);
	var3 = parseInt(document.getElementById('ss3').value);
	var4 = parseInt(document.getElementById('ss4').value);
	var5 = parseInt(document.getElementById('ss5').value);
	var6 = parseInt(document.getElementById('ss6').value);
	var7 = parseInt(document.getElementById('ss7').value);
	var8 = parseInt(document.getElementById('ss8').value);
	var9 = parseInt(document.getElementById('ss9').value);
	var10 = parseInt(document.getElementById('ss10').value);
	var11 = parseInt(document.getElementById('ss11').value);
	var12 = parseInt(document.getElementById('ss12').value);
	zz = var1 + var2 + var3 + var4 + var5 + var6 + var7 + var8 + var9 + var10 + var11 + var12;
	if (zz && zz > 0) {
		param = 'proses=sebarDoong&kunci=' + kunci;
		param += '&var1=' + (var1 / zz) + '&var2=' + (var2 / zz) + '&var3=' + (var3 / zz) + '&var4=' + (var4 / zz) + '&var5=' + (var5 / zz);
		param += '&var6=' + (var6 / zz) + '&var7=' + (var7 / zz) + '&var8=' + (var8 / zz) + '&var9=' + (var9 / zz) + '&var10=' + (var10 / zz);
		param += '&var11=' + (var11 / zz) + '&var12=' + (var12 / zz) + '&rupe=' + rupe + '&fis=' + fis;
		tujuan = 'bgt_budget_slave_kebun.php';
		if (obj.checked)
			post_response_text(tujuan, param, respog);
	} else {
		alert('Sebaran salah');
	}
	//============

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('baris' + baris).style.backgroundColor = 'red';
				} else {
					document.getElementById('rewBr_' + baris).style.backgroundColor = 'green';
				}
			} else {
				busy_off();
				error_catch(con.status);
				document.getElementById('rewBr_' + baris).style.backgroundColor = 'red';
			}
		}
	}
}