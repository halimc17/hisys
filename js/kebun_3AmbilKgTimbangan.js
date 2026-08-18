function gettgl(){
	tgl = document.getElementById('tglData').value;
	document.getElementById('tglData2').value=tgl;
}

function saveData(tipe) {
	kdOrg = document.getElementById('idKbn').value;
	tgl = document.getElementById('tglData').value;
	tgl2 = document.getElementById('tglData2').value;
	if (tgl == '') {
		alert("Please Insert Date/Tanggal");
		return;
	}
	if (tgl.substr(-7, 7) != tgl2.substr(-7, 7)) {
		alert("Start and End Date must be same month !!");
		return;
	}

	param = 'kdOrg=' + kdOrg + '&tgl=' + tgl + '&tipe=' + tipe;
	param += '&tgl2=' + tgl2;
	param += '&intex=' + getValue('intex')
	param += '&proses=getData'
	tujuan = 'kebun_slave_3AmbilKgTimbangan.php';
	if(tipe!='excel'){
		post_response_text(tujuan, param, respon);
	}else{
		printnopopup(tujuan+'?'+param);
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// Success Response
					//alertify.alert(con.responseText);
					document.getElementById('result').style.display = 'block';
					document.getElementById('list_ganti').innerHTML = con.responseText;
					document.getElementById('idKbn').disabled = true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function postingbro() {
	kdOrg = document.getElementById('idKbn').value;
	tgl = document.getElementById('tglData').value;
	tgl2 = document.getElementById('tglData2').value;
	if (tgl == '') {
		alert("Please Insert Date/Tanggal");
		return;
	}
	if (tgl.substr(-7, 7) != tgl2.substr(-7, 7)) {
		alert("Start and End Date must be same month !!");
		return;
	}

	param = 'kdOrg=' + kdOrg + '&tgl=' + tgl;
	param += '&tgl2=' + tgl2;
	param += '&intex=' + getValue('intex')
	param += '&proses=postingbro'
	tujuan = 'kebun_slave_3AmbilKgTimbangan.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// Success Response
					alertify.alert(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

maxf = 0
	sekarang = 1;
function postingall(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	maxRowBlok = document.getElementById('rows_dt').value;
	if (confirm("Proses ini akan memproporsi Kg Pabrik ke blok, lanjutkan ?")) {
		maxf = maxRowBlok;
		loopsave(1, maxRowBlok);
	}
}

function loopsave(currRow, maxRowBlok) {
	maxRowDiv = document.getElementById('rows_induk').value;

	noSpb = trim(document.getElementById('nospb_' + currRow).innerHTML);

	blokecil = trim(document.getElementById('blok_' + currRow).innerHTML);
	indukblok = trim(document.getElementById('indukblok_' + currRow).innerHTML);
	tglPanen = trim(document.getElementById('tglpanen_' + currRow).innerHTML);
	notph = trim(document.getElementById('notph_' + currRow).innerHTML);
	sesi = trim(document.getElementById('sesi_' + currRow).innerHTML);
	pemanen = trim(document.getElementById('pemanen_' + currRow).innerHTML);
	jjgblokcl = trim(document.getElementById('jjgblkx_' + currRow).innerHTML);
	bjrblkcl = trim(document.getElementById('bjrblk_' + currRow).innerHTML);
	kgbjrblkcl = trim(document.getElementById('kgbjr_' + currRow).innerHTML);
	brdblkcl = trim(document.getElementById('brdblk_' + currRow).innerHTML);
	kgbrutoblkcl = trim(document.getElementById('kgwbbrutobrd_' + currRow).innerHTML);
	kgnettoblkcl = trim(document.getElementById('kgwbnettobrd_' + currRow).innerHTML);
	totalkgblkcl = trim(document.getElementById('totalkg_' + currRow).innerHTML);	
	param = 'noSpb=' + noSpb;	
	param += '&blokecil=' + blokecil;
	param += '&indukblok=' + indukblok;
	param += '&tglPanen=' + tglPanen;
	param += '&notph=' + notph;
	param += '&sesi=' + sesi;
	param += '&pemanen=' + pemanen;
	param += '&tglPanen=' + tglPanen;
	param += '&jjgblokcl=' + jjgblokcl;
	param += '&bjrblkcl=' + bjrblkcl;
	param += '&kgbjrblkcl=' + kgbjrblkcl;
	param += '&brdblkcl=' + brdblkcl;
	param += '&kgbrutoblkcl=' + kgbrutoblkcl;
	param += '&kgnettoblkcl=' + kgnettoblkcl;
	param += '&totalkgblkcl=' + totalkgblkcl;
	param += '&intex=' + getValue('intex');
	param += '&kodoerg=' + getValue('idKbn');
	param += '&proses=PostingData';
	tujuan = 'kebun_slave_3AmbilKgTimbangan.php';

	post_response_text(tujuan, param, respog);
	document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('row' + currRow).style.display = 'none';
					currRow += 1;
					sekarang = currRow;
					if (currRow > maxRowBlok) {
						postingall2(maxRowDiv)
					} else {
						loopsave(currRow, maxRowBlok);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

maxf = 0
	sekarang = 1;
function postingall2(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	maxRowDiv = document.getElementById('rows_induk').value;
	maxf = maxRowDiv;
	loopsave2(1, maxRowDiv);
}

function loopsave2(currRow, maxRowDiv) {
	noSpb = trim(document.getElementById('nospbdiv_' + currRow).innerHTML);
	indukblok = trim(document.getElementById('indukblokdiv_' + currRow).innerHTML);
	tglPanen = trim(document.getElementById('tglpanendiv_' + currRow).innerHTML);
	totkgbjrbesar = trim(document.getElementById('totkgbjrindk_' + currRow).innerHTML);
	bjrbesar = trim(document.getElementById('bjrblokbesar_' + currRow).innerHTML);
	kgbrutobesar = trim(document.getElementById('kgwbindk_' + currRow).innerHTML);
	kgnettobesar = trim(document.getElementById('kgwbnettoindk_' + currRow).innerHTML);
	totalkgbesar = trim(document.getElementById('totalkgindk_' + currRow).innerHTML);

	param = '&noSpb=' 			+ noSpb;
	param += '&indukblok=' 		+ indukblok;
	param += '&tglPanen=' 		+ tglPanen;
	param += '&totkgbjrbesar=' 	+ totkgbjrbesar;
	param += '&bjrbesar=' 		+ bjrbesar;
	param += '&kgbrutobesar=' 	+ kgbrutobesar;
	param += '&kgnettobesar=' 	+ kgnettobesar;
	param += '&totalkgbesar=' 	+ totalkgbesar;
	param += '&intex=' + getValue('intex');
	param += '&kodoerg=' + getValue('idKbn');
	param += '&proses=PostingData2';
	tujuan = 'kebun_slave_3AmbilKgTimbangan.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row_' + currRow).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('row_' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('row_' + currRow).style.display = 'none';
					currRow += 1;
					sekarang = currRow;
					if (currRow > maxRowDiv) {
						saveData();
						alert('Done');
					} else {
						loopsave2(currRow, maxRowDiv);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function cancelSave() {
	document.getElementById('list_ganti').innerHTML = '';
	document.getElementById('idKbn').disabled = false;
	document.getElementById('tglData').disabled = false;
	document.getElementById('tglData2').disabled = false;
	document.getElementById('dtl_pem').disabled = false;
	document.getElementById('dtl_pem2').disabled = false;
	document.getElementById('btnexcel').disabled = false;
	document.getElementById('idKbn').value = '';
	document.getElementById('tglData').value = '';
	document.getElementById('tglData2').value = '';
	document.getElementById('result').style.display = 'none';

}