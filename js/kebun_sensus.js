function excel(ev, tujuan) {
	thn = document.getElementById('thnex').value;
	judul = 'Report Ms.Excel';
	param = 'method=excel' + '&thn=' + thn;
	//printFile(param, tujuan, judul, ev);
	
	printnopopup(tujuan+"?"+param);
}
function detail(thn, divisi, sms, sms2, stblok, ev) {
	param = 'method=detail' + '&thn=' + thn + '&divisi=' + divisi + '&sms=' + sms + '&sms2=' + sms2 + '&stblok=' + stblok;
	title = "Data Detail";
	alertify.popuppdf("Preview","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_sensus.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
	// showDialog1(title, "<iframe frameborder=0 style='width:1045px;height:395px'" +
		// " src='kebun_slave_sensus.php?" + param + "'></iframe>", '1050', '400', ev);
	// var dialog = document.getElementById('dynamic1');
	// dialog.style.top = '50px';
	// dialog.style.left = '15%';
}
function posting(thn, divisi, sms, stblok) {
	param = 'method=posting' + '&thn=' + thn + '&divisi=' + divisi + '&sms=' + sms + '&stblok=' + stblok;
	tujuan = 'kebun_slave_sensus.php';
	if (confirm('Anda yakin ingin memposting ??? \nData yang sudah diposting tidak bisa di edit dan di delete.')) {
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
function del(thn, divisi, sms, stblok) {
	param = 'method=delete' + '&thn=' + thn + '&divisi=' + divisi + '&sms=' + sms + '&stblok=' + stblok;
	tujuan = 'kebun_slave_sensus.php';
	if (confirm('Anda yakin ingin menghapus data 1 (satu) Divisi dan 1 (satu) Semester ??? \nJika hanya ingin menghapus sebagian / beberapa data saja gunakan tombol edit.')) {
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
function displaylist() {
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('upload').style.display = 'none';
	document.getElementById('thnsch').value = '';
	document.getElementById('smssch').value = '';
	document.getElementById('stbloksch').value = '';
	document.getElementById('divisisch').value = '';
	loaddata(0);
}
function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(num) {
	//pembuat = trim(document.getElementById('pembuat').value);
	thnsch = document.getElementById('thnsch');
	thnsch = thnsch.options[thnsch.selectedIndex].value;
	smssch = document.getElementById('smssch');
	smssch = smssch.options[smssch.selectedIndex].value;
	stbloksch = document.getElementById('stbloksch');
	stbloksch = stbloksch.options[stbloksch.selectedIndex].value;
	divisisch = document.getElementById('divisisch');
	divisisch = divisisch.options[divisisch.selectedIndex].value;
	param = 'method=loaddata&page=' + num;
	if (thnsch != '') {
		param += '&thnsch=' + thnsch;
	}
	if (smssch != '') {
		param += '&smssch=' + smssch;
	}
	if (stbloksch != '') {
		param += '&stbloksch=' + stbloksch;
	}
	if (divisisch != '') {
		param += '&divisisch=' + divisisch;
	}
	tujuan = 'kebun_slave_sensus.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
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
function edit(thn, divisi, sms, stblok, sms2, mode) {
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	document.getElementById('thn').value = thn;
	document.getElementById('sms').value = sms;
	document.getElementById('sms2').value = sms2;
	document.getElementById('divisi').value = divisi;
	document.getElementById('stblok').value = stblok;
	savehead(thn, divisi, sms, stblok, sms2, mode);
}
function cekthn() {
	thn = document.getElementById('thn').value;
	var today = new Date();
	thns = today.getFullYear();
	thnl = parseFloat(thns) - 1;
	thnd = parseFloat(thns) + 1;
	val = Math.abs(thns - thn);
	if (val > 1) {
		alert('Tahun yang diizinkan hanya : ' + thnl + ' , ' + thns + ' dan ' + thnd + '  ');
		document.getElementById('thn').value = '';
	}
}
function newdata() //indra
{
	document.getElementById('header').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('upload').style.display = 'none';
	document.getElementById('thn').value = '';
	document.getElementById('sms').value = '';
	document.getElementById('sms2').value = '';
	document.getElementById('divisi').value = '';
	document.getElementById('stblok').value = '';
	document.getElementById('divisi').disabled = false;
	document.getElementById('thn').disabled = false;
	document.getElementById('sms').disabled = false;
	document.getElementById('sms2').disabled = false;
	document.getElementById('stblok').disabled = false;
	document.getElementById('savehead').disabled = false;
}
function cancel() {
	newdata();
}

//	savehead(thn,divisi,sms,stblok,sms2,mode);
function savehead(thn, divisi, sms, stblok, sms2, mode) {
	thn = document.getElementById('thn').value;
	sms = document.getElementById('sms').value;
	sms2 = document.getElementById('sms2').value;
	divisi = document.getElementById('divisi').value;
	stblok = document.getElementById('stblok').value;

	if (thn == '' || sms == '' || sms2 == '' || divisi == '' || stblok == '') {
		alert('Lengkapi terlebih dahulu pengisian form diatas.');
		return;
	}
	if (parseInt(sms) > parseInt(sms2)) {
		alert('Periode Dari Tidak Boleh Lebih Dari Periode Tujuan');
		return;
	}
	param = 'method=detailinput' + '&thn=' + thn + '&sms=' + sms + '&sms2=' + sms2 + '&divisi=' + divisi + '&stblok=' + stblok + '&mode=' + mode;
	tujuan = 'kebun_slave_sensus.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('thn').disabled = true;
					document.getElementById('sms').disabled = true;
					document.getElementById('sms2').disabled = true;
					document.getElementById('divisi').disabled = true;
					document.getElementById('stblok').disabled = true;
					//document.getElementById('savehead').disabled = true;
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detailinput').innerHTML = con.responseText;
					//location.reload();
					//cancel();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
// function hitungbjr(no, sms) {
// 	x = trim(document.getElementById('jjg' + no).value);
// 	y = trim(document.getElementById('kg' + no).value);
// 	z = y / x;
// 	if (isNaN(z)) {
// 		z = 0;
// 	}
// 	if (x == 0 || x == '') {
// 		z = 0;
// 	}
// 	document.getElementById('bjr' + no).value = parseFloat(z).toFixed(2);
// }
////////////////////////////////////////////
// function sebarjjg(no, sms, row) {
// 	jjg = trim(document.getElementById('jjg' + no).value);
// 	proporsi = jjg / 4;
// 	if (sms == 1) {
// 		for (i = 1; i <= 4; i++) {
// 			document.getElementById('sebaranjjg' + no + '#' + i).value = parseFloat(proporsi).toFixed(0);
// 		}
// 	} else if (sms == 2) {
// 		for (i = 5; i <= 8; i++) {
// 			document.getElementById('sebaranjjg' + no + '#' + i).value = parseFloat(proporsi).toFixed(0);
// 		}
// 	} else {
// 		for (i = 9; i <= 12; i++) {
// 			document.getElementById('sebaranjjg' + no + '#' + i).value = parseFloat(proporsi).toFixed(0);
// 		}
// 	}
// 	sebaranpersenjjg(row, sms);
// }
// function sebar(no, sms, row) {
// 	kg = trim(document.getElementById('kg' + no).value);
// 	proporsi = kg / 4;
// 	if (sms == 1) {
// 		for (i = 1; i <= 4; i++) {
// 			document.getElementById('sebaran' + no + '#' + i).value = parseFloat(proporsi).toFixed(2);
// 		}
// 	} else if (sms == 2) {
// 		for (i = 5; i <= 8; i++) {
// 			document.getElementById('sebaran' + no + '#' + i).value = parseFloat(proporsi).toFixed(2);
// 		}
// 	} else {
// 		for (i = 9; i <= 12; i++) {
// 			document.getElementById('sebaran' + no + '#' + i).value = parseFloat(proporsi).toFixed(2);
// 		}
// 	}
// 	sebaranpersen(row, sms);
// }
////////////////////////////////////////////
////////////////////////////////////////////
// function totpersenjjg(sms) {
// 	if (sms == 1) {
// 		mulai = 1;
// 		selesai = 4;
// 	} else if (sms == 2) {
// 		mulai = 5;
// 		selesai = 8;
// 	} else {
// 		mulai = 9;
// 		selesai = 12;
// 	}
// 	totaljjg = 0;
// 	for (i = mulai; i <= selesai; i++) {
// 		persenjjg = trim(document.getElementById('persenjjg' + i).value);
// 		// if(isNaN(persen))
// 		// {
// 		// persen=0;
// 		// }
// 		totaljjg += parseFloat(persenjjg);
// 	}
// 	document.getElementById('totalpersenjjg').value = parseFloat(totaljjg).toFixed(2);
// }
// function totpersen(sms) {
// 	if (sms == 1) {
// 		mulai = 1;
// 		selesai = 4;
// 	} else if (sms == 2) {
// 		mulai = 5;
// 		selesai = 8;
// 	} else {
// 		mulai = 9;
// 		selesai = 12;
// 	}
// 	total = 0;
// 	for (i = mulai; i <= selesai; i++) {
// 		persen = trim(document.getElementById('persen' + i).value);
// 		// if(isNaN(persen))
// 		// {
// 		// persen=0;
// 		// }
// 		total += parseFloat(persen);
// 	}
// 	document.getElementById('totalpersen').value = parseFloat(total).toFixed(2);
// }
// function sebaranpersen(row, sms,id,val) {
// 	totalpersen = trim(document.getElementById('totalpersen').value);
// 	if(id!=undefined){
// 		if(id.length>8){
// 			var idjjg = id.substr(9,2);
// 		}else{
// 			var idjjg = id.substr(6,2);
// 		}
// 		document.getElementById('persenjjg'+idjjg).value=val;		
// 	}
// 	if(totalpersen>100){
// 		alert('Total persen lebih dari 100 %'); 
// 		document.getElementById(id).value='0';
// 		document.getElementById('persenjjg'+idjjg).value='0';
// 		totpersen(sms);
// 	}
// 	if (sms == 1) {
// 		mulai = 1;
// 		selesai = 4;
// 	} else if (sms == 2) {
// 		mulai = 5;
// 		selesai = 8;
// 	} else {
// 		mulai = 9;
// 		selesai = 12;
// 	}
// 	for (no = 1; no <= row; no++) {
// 		kg = trim(document.getElementById('kg' + no).value);
// 		if (isNaN(kg)) {
// 			kg = 0;
// 		}
// 		for (i = mulai; i <= selesai; i++) {
// 			persen = trim(document.getElementById('persen' + i).value);
// 			nilai = persen / totalpersen * kg;
// 			document.getElementById('sebaran' + no + '#' + i).value = parseFloat(nilai).toFixed(2);
// 		}
// 		//document.getElementById('sebaran'+no+'#'+i).value=parseFloat(proporsi,2);
// 	}
// }
// function sebaranpersenjjg(row, sms,id,val) {
// 	totalpersenjjg = trim(document.getElementById('totalpersenjjg').value);
// 	if(id!=undefined){
// 		if(id.length>8){
// 			var idkg = id.substr(9,2);
// 		}else{
// 			var idkg = id.substr(6,2);
// 		}
// 		document.getElementById('persen'+idkg).value=val;		
// 	}
// 	if(totalpersenjjg>100){
// 		alert('Total persen lebih dari 100 %'); 
// 		document.getElementById(id).value='0';
// 		document.getElementById('persen'+idkg).value='0';
// 		totpersenjjg(sms);
// 	}
	
// 	if (sms == 1) {
// 		mulai = 1;
// 		selesai = 4;
// 	} else if (sms == 2) {
// 		mulai = 5;
// 		selesai = 8;
// 	} else {
// 		mulai = 9;
// 		selesai = 12;
// 	}
// 	for (no = 1; no <= row; no++) {
// 		jjg = trim(document.getElementById('jjg' + no).value);
// 		if (isNaN(jjg)) {
// 			jjg = 0;
// 		}
// 		for (i = mulai; i <= selesai; i++) {
// 			persenjjg = trim(document.getElementById('persenjjg' + i).value);
// 			nilaijjg = persenjjg / totalpersenjjg * jjg;
// 			document.getElementById('sebaranjjg' + no + '#' + i).value = parseFloat(nilaijjg).toFixed();
// 		}
// 	}
// }
maxf = 0
	sekarang = 1;
function saveall(maxRow, mode) {
	maxf = maxRow;
	loopsave(1, maxRow, mode);
}
function loopsave(currRow, maxRow, mode) {
	thn = document.getElementById('thn').value;
	divisi = document.getElementById('divisi').value;
	sms = document.getElementById('sms').value;
	sms2 = document.getElementById('sms2').value;
	stblok = document.getElementById('stblok').value;
	blok = trim(document.getElementById('blok' + currRow).innerHTML);
	luas = trim(document.getElementById('luas' + currRow).innerHTML);
	pokok = trim(document.getElementById('pokok' + currRow).innerHTML);
	jjg = trim(document.getElementById('jjg' + currRow).value);
	kg = trim(document.getElementById('kg' + currRow).value);
	bjr = trim(document.getElementById('bjr' + currRow).value);
	kerapatan = trim(document.getElementById('kerapatan' + currRow).value);
	pokok = remove_comma_var(pokok);
	
	param = 'thn=' + thn + '&divisi=' + divisi + '&blok=' + blok + '&sms=' + sms + '&sms2=' + sms2;
	param += "&method=savedata" + '&luas=' + luas + '&pokok=' + pokok + '&jjg=' + jjg + '&kg=' + kg + '&bjr=' + bjr + '&stblok=' + stblok + '&mode=' + mode+ '&kerapatan=' + kerapatan;

	for (i = parseInt(sms); i <= parseInt(sms2); i++) {
		if (i == 1) {
			sebaran1		= trim(document.getElementById('sebaran' + currRow + '#1').value);
			sebaranjjg1 	= trim(document.getElementById('sebaranjjg' + currRow + '#1').value);
			sebaranbjr1 	= trim(document.getElementById('sebaranbjr' + currRow + '#1').value);
			param += '&sebaran1=' + sebaran1;
			param += '&sebaranjjg1=' + sebaranjjg1;
			param += '&sebaranbjr1=' + sebaranbjr1;
		}
		if (i == 2) {
			sebaran2		= trim(document.getElementById('sebaran' + currRow + '#2').value);
			sebaranjjg2 	= trim(document.getElementById('sebaranjjg' + currRow + '#2').value);
			sebaranbjr2 	= trim(document.getElementById('sebaranbjr' + currRow + '#2').value);
			param += '&sebaran2=' + sebaran2;
			param += '&sebaranjjg2=' + sebaranjjg2;
			param += '&sebaranbjr2=' + sebaranbjr2;
		}
		if (i == 3) {
			sebaran3		= trim(document.getElementById('sebaran' + currRow + '#3').value);
			sebaranjjg3 	= trim(document.getElementById('sebaranjjg' + currRow + '#3').value);
			sebaranbjr3 	= trim(document.getElementById('sebaranbjr' + currRow + '#3').value);
			param += '&sebaran3=' + sebaran3;
			param += '&sebaranjjg3=' + sebaranjjg3;
			param += '&sebaranbjr3=' + sebaranbjr3;
		}
		if (i == 4) {
			sebaran4		= trim(document.getElementById('sebaran' + currRow + '#4').value);
			sebaranjjg4 	= trim(document.getElementById('sebaranjjg' + currRow + '#4').value);
			sebaranbjr4 	= trim(document.getElementById('sebaranbjr' + currRow + '#4').value);
			param += '&sebaran4=' + sebaran4;
			param += '&sebaranjjg4=' + sebaranjjg4;
			param += '&sebaranbjr4=' + sebaranbjr4;
		}
		if (i == 5) {
			sebaran5		= trim(document.getElementById('sebaran' + currRow + '#5').value);
			sebaranjjg5 	= trim(document.getElementById('sebaranjjg' + currRow + '#5').value);
			sebaranbjr5 	= trim(document.getElementById('sebaranbjr' + currRow + '#5').value);
			param += '&sebaran5=' + sebaran5;
			param += '&sebaranjjg5=' + sebaranjjg5;
			param += '&sebaranbjr5=' + sebaranbjr5;
		}
		if (i == 6) {
			sebaran6		= trim(document.getElementById('sebaran' + currRow + '#6').value);
			sebaranjjg6 	= trim(document.getElementById('sebaranjjg' + currRow + '#6').value);
			sebaranbjr6 	= trim(document.getElementById('sebaranbjr' + currRow + '#6').value);
			param += '&sebaran6=' + sebaran6;
			param += '&sebaranjjg6=' + sebaranjjg6;
			param += '&sebaranbjr6=' + sebaranbjr6;
		}
		if (i == 7) {
			sebaran7		= trim(document.getElementById('sebaran' + currRow + '#7').value);
			sebaranjjg7 	= trim(document.getElementById('sebaranjjg' + currRow + '#7').value);
			sebaranbjr7 	= trim(document.getElementById('sebaranbjr' + currRow + '#7').value);
			param += '&sebaran7=' + sebaran7;
			param += '&sebaranjjg7=' + sebaranjjg7;
			param += '&sebaranbjr7=' + sebaranbjr7;
		}
		if (i == 8) {
			sebaran8		= trim(document.getElementById('sebaran' + currRow + '#8').value);
			sebaranjjg8 	= trim(document.getElementById('sebaranjjg' + currRow + '#8').value);
			sebaranbjr8 	= trim(document.getElementById('sebaranbjr' + currRow + '#8').value);
			param += '&sebaran8=' + sebaran8;
			param += '&sebaranjjg8=' + sebaranjjg8;
			param += '&sebaranbjr8=' + sebaranbjr8;
		}
		if (i == 9) {
			sebaran9		= trim(document.getElementById('sebaran' + currRow + '#9').value);
			sebaranjjg9 	= trim(document.getElementById('sebaranjjg' + currRow + '#9').value);
			sebaranbjr9 	= trim(document.getElementById('sebaranbjr' + currRow + '#9').value);
			param += '&sebaran9=' + sebaran9;
			param += '&sebaranjjg9=' + sebaranjjg9;
			param += '&sebaranbjr9=' + sebaranbjr9;
		}
		if (i == 10) {
			sebaran10		= trim(document.getElementById('sebaran' + currRow + '#10').value);
			sebaranjjg10 	= trim(document.getElementById('sebaranjjg' + currRow + '#10').value);
			sebaranbjr10 	= trim(document.getElementById('sebaranbjr' + currRow + '#10').value);
			param += '&sebaran10=' + sebaran10;
			param += '&sebaranjjg10=' + sebaranjjg10;
			param += '&sebaranbjr10=' + sebaranbjr10;
		}
		if (i == 11) {
			sebaran11		= trim(document.getElementById('sebaran' + currRow + '#11').value);
			sebaranjjg11 	= trim(document.getElementById('sebaranjjg' + currRow + '#11').value);
			sebaranbjr11 	= trim(document.getElementById('sebaranbjr' + currRow + '#11').value);
			param += '&sebaran11=' + sebaran11;
			param += '&sebaranjjg11=' + sebaranjjg11;
			param += '&sebaranbjr11=' + sebaranbjr11;
		}
		if (i == 12) {
			sebaran12		= trim(document.getElementById('sebaran' + currRow + '#12').value);
			sebaranjjg12 	= trim(document.getElementById('sebaranjjg' + currRow + '#12').value);
			sebaranbjr12 	= trim(document.getElementById('sebaranbjr' + currRow + '#12').value);
			param += '&sebaran12=' + sebaran12;
			param += '&sebaranjjg12=' + sebaranjjg12;
			param += '&sebaranbjr12=' + sebaranbjr12;
		}
	}
	
	tujuan = 'kebun_slave_sensus.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
	//lockScreen('wait');
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
					if (currRow > maxRow) {
						alert('Done');
						//cancel();
						//closeDialog();
						//loadData();
						//document.location.reload();
						//document.getElementById('infoDisplay').innerHTML='';
					} else {
						loopsave(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
				// document.getElementById('lanjut').style.display='';
				//unlockScreen();
			}
		}
	}
}

function showupload(){
	thn   = document.getElementById('thn').value;
	divisi= document.getElementById('divisi').value;
	sms   = document.getElementById('sms').value;
	sms2   = document.getElementById('sms2').value;
	stblok= document.getElementById('stblok').value;
	
	if(thn=='' || divisi=='' || sms=='' || sms2=='' || stblok==''){
		alert("Tahun, rentang periode, status dan divisi wajib diisi.");
		return;
	}

	if (parseInt(sms) > parseInt(sms2)) {
		alert('Periode Dari Tidak Boleh Lebih Dari Periode Tujuan');
		return;
	}

	document.getElementById('header').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'none';
	document.getElementById('upload').style.display = 'block';
	document.getElementById('detail').style.display = 'none';
	
	
    param = 'method=showupload';
	param += '&thn=' + thn + '&divisi=' + divisi + '&stblok=' + stblok + '&sms=' + sms + '&sms2=' + sms2;
	
    tujuan = 'kebun_slave_sensus.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                } else {
					document.getElementById('viewupload').innerHTML = "";
                    document.getElementById('viewupload').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function submitFile(){
    if(confirm('Are you sure..?')){
		document.getElementById('frm').submit();
		leftFixedTable();
    }
}

function simpanall(maxRow){  
	if(maxRow =='' || maxRow ==0){
        alert('Data tidak ditemukan, proses dibatalkan.');
        return;
    }
	if(confirm("Simpan semua ???")){
		simpanupload(1,maxRow,1);
	}
}

function simpanupload(currRow,maxRow,currcol){
	currcol= parseFloat(currcol);
	temperr= document.getElementById('temperr').value;
	thn    = document.getElementById('thn_'+currRow).innerHTML;
	sms    = document.getElementById('sms_'+currRow).innerHTML;
	sms2   = document.getElementById('sms2_'+currRow).innerHTML;
	sts    = document.getElementById('sts_'+currRow).innerHTML;
	div    = document.getElementById('div_'+currRow).innerHTML; 
	blok   = document.getElementById('blok_'+currRow).innerHTML;
	tt     = document.getElementById('tt_'+currRow).innerHTML;
	luas   = document.getElementById('luas_'+currRow).innerHTML;
	pokok  = document.getElementById('pokok_'+currRow).innerHTML;
	semester= document.getElementById('semester_'+currRow).innerHTML;
		
	bln    = document.getElementById('bln_'+currRow).innerHTML;
	jjg    = document.getElementById('jjg_'+currRow).innerHTML;
	kg     = document.getElementById('kg_'+currRow).innerHTML;
	bjr     = document.getElementById('bjr_'+currRow).innerHTML;
	kerapatan     = document.getElementById('kerapatan_'+currRow).innerHTML;

	if (jjg != null || jjg != undefined) {
		jjg    = document.getElementById('jjg_'+currRow).innerHTML;
	}
	if (kg != null || kg != undefined) {
		kg     = document.getElementById('kg_'+currRow).innerHTML;
	}
	if (bjr != null || bjr != undefined) {
		bjr     = document.getElementById('bjr_'+currRow).innerHTML;
	}
	if (kerapatan != null || kerapatan != undefined) {
		kerapatan     = document.getElementById('kerapatan_'+currRow).innerHTML;
	}

	jjg    = remove_comma_var(jjg);
	kg     = remove_comma_var(kg);
	bjr     = remove_comma_var(bjr);
	kerapatan     = remove_comma_var(kerapatan);
	
	param = "";
	param += "thn="+thn;
	param += "&sms="+sms;
	param += "&sms2="+sms2;
	param += "&sts="+sts;
	param += "&div="+div;
	param += "&blok="+blok;
	param += "&tt="+tt;
	param += "&luas="+luas;
	param += "&pokok="+pokok;
	param += "&bln="+bln;
	param += "&jjg="+jjg;
	param += "&kg="+kg;
	param += "&bjr="+bjr;
	param += "&kerapatan="+kerapatan;
	param += "&semester="+semester;
	param += "&currow="+currRow;
	param += "&currcol="+currcol;
	param += "&method=uploaddata";
	
	if(temperr!='0'){
		alert("Masih terdapat data yang salah."); return;
	}
	
	tujuan='kebun_slave_sensus.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					unlockScreen();
				} else {
					if(currcol!=undefined){
						document.getElementById('trsns_'+currRow).style.backgroundColor='cyan';
					}
					// currcol+=1;
					// if(currcol>11){						
						currRow+=1;
						if((currRow>maxRow) || (maxRow == undefined)){
							alert("Done");
						} else {
							simpanupload(currRow,maxRow,1);
						}
					// }else{
					// 	simpanupload(currRow,maxRow,currcol);
					// }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}