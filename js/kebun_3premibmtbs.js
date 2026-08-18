function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {
	prdlist = document.getElementById('prdlist').value;
	unitlist = document.getElementById('unitlist').value;
	afdlist = document.getElementById('afdlist').value;
	kontlist = document.getElementById('kontlist').value;
	param = 'proses=loaddata&page=' + page;
	if (prdlist != '') {
		param += '&prdlist=' + prdlist;
	}
	if (unitlist != '') {
		param += '&unitlist=' + unitlist;
	}
	if (afdlist != '') {
		param += '&afdlist=' + afdlist;
	}
	param += '&kontlist=' + kontlist;
	tujuan = 'kebun_slave_3premibmtbs_list.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('printContainerlist').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function batal() {
	document.getElementById('prd').value = '';
	document.getElementById('unit').value = '';
	document.getElementById('afd').value = '';
	document.getElementById('printContainer').innerHTML = '';
}

function batallist() {
	document.getElementById('prdlist').value = '';
	document.getElementById('unitlist').value = '';
	document.getElementById('afdlist').value = '';
	loaddata();
}

function numberFormat(number, digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	//Seperates the components of the number
	var components = (parseFloat(number).toFixed(digit)).split(".");
	//Comma-fies the first part
	components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	//Combines the two sections
	return components.join(".");
}

function del(notransaksi, prd, unit, keg,kontanan) {
	param = 'proses=deleteTrans&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit + '&keg=' + keg+ '&kontanan=' + kontanan;
	tujuan = 'kebun_slave_save_3premibmtbs.php';
	if (confirm('Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:98.5%\"><div id=containerView style=\"width:100%;max-height:450px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}

function view(notransaksi, prd, unit, keg, tipe,afd) {
	alertify.popup().destroy();
	param = 'proses=view&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit + '&keg=' + keg + '&tipe=' + tipe+ '&afd=' + afd;
	tujuan = 'kebun_slave_3premibmtbs_list.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();

					//document.getElementById('containerView').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function previewexcel(notransaksi, prd, unit, keg, tipe, afd){
	param = 'proses=view&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit + '&keg=' + keg + '&tipe=' + tipe+ '&afd=' + afd;
	tujuan = 'kebun_slave_3premibmtbs_list.php' + "?" + param;
	width = '';
	height = '';
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	printFile(param,tujuan,title,ev);

}

function getAfd() {
	unit = document.getElementById('unit').value;
	param='proses=getAfd';
	param+='&unit='+unit;
	tujuan = 'kebun_slave_save_3premibmtbs.php';

	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('afd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getAfd2() {
	unit = document.getElementById('unitlist').value;
	param='proses=getAfd2';
	param+='&unitlist='+unit;
	tujuan = 'kebun_slave_3premibmtbs_list.php';

	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('afdlist').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(notransaksi, prd, unit, keg,kontanan, baris) {
	param = 'notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit + '&keg=' + keg+ '&kontanan=' + kontanan;
	tujuan = 'kebun_slave_save_posting3premibmtbs.php';
	if (confirm('Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containerView').innerHTML = con.responseText;
					// document.getElementById('tr_' + baris).cells[15].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[16].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[17].innerHTML = "<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posted'>";
					loaddata(0);
					alert('Posting Success.');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unposting(notransaksi, prd, unit, keg, baris) {
	param = 'proses=unposting&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit + '&keg=' + keg;
	tujuan = 'kebun_slave_3premibmtbs_list.php';
	if (confirm('Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containerView').innerHTML = con.responseText;
					// document.getElementById('tr_' + baris).cells[15].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[16].innerHTML = "<img src=images/application/application_delete.png class=resicon class=zImgBtn height='30'  title='Please Reload Frame'>";
					// document.getElementById('tr_' + baris).cells[17].innerHTML = "<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Please Reload Frame'>";
					loaddata(0);
					alert('Unposting Success.');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gettglkontan(jenis,id){
	if(jenis=='KONTAN'){
		document.getElementById(id).style.display='';
	}else{
		document.getElementById(id).style.display='none';
	}
}

function deleteTrans(maxRow) {
	notransaksi = document.getElementById('notransaksi').value;
	prd = document.getElementById('prd').value;
	unit = document.getElementById('unit').value;
	kontanan = document.getElementById('kontanan').value;
	param = 'proses=deleteTrans&maxRow=' + maxRow + '&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit+ '&kontanan=' + kontanan; 
	tujuan = 'kebun_slave_save_3premibmtbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					saveAll(maxRow);
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
function saveAll(maxRow) {
	maxf = maxRow;
	const el = document.querySelector('[id^="rowkeg_"]');
	const idName = el.id;
	const num = parseInt(idName.split('_')[1].replace(/[a-zA-Z]/g, ''), 10);
	loopsave(num, maxf);
}

function loopsave(currRow, maxRow) {
	notransaksi = document.getElementById('notransaksi').value;
	prd = document.getElementById('prd').value;
	unit = document.getElementById('unit').value;
	afd = document.getElementById('afd').value;
	// kontanan = document.getElementById('kontanan').value;
	console.log('row '+currRow);
	keg = document.getElementById('rowkeg_' + currRow).innerHTML;
	kary = document.getElementById('rowkary_' + currRow).innerHTML;
	nospb = document.getElementById('rownospb_' + currRow).innerHTML;
	sesi = document.getElementById('rowsesi_' + currRow).innerHTML;
	tgl = document.getElementById('rowtgl_' + currRow).innerHTML;
	jjgkry = document.getElementById('jjgkry_' + currRow).innerHTML;
	bjrwb = document.getElementById('bjrwb_' + currRow).innerHTML;
	kgwb = document.getElementById('rowkgwb_' + currRow).innerHTML;
	nilai1hk = document.getElementById('nilai1hk_' + currRow).innerHTML;
	hk = document.getElementById('rowhk_' + currRow).innerHTML;
	rphk = document.getElementById('rowrphk_' + currRow).innerHTML;
	rppremi = document.getElementById('rowrpprmi_' + currRow).innerHTML;
	kontanan = document.getElementById('kontanan_' + currRow).innerHTML;

	if (prd == '' || unit == '' || afd == '') {
		alert("Data tidak lengkap");
		return;
	} else {
		param = 'notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit + '&afd=' + afd + '&keg=' + keg + '&kary=' + kary + '&tgl=' + tgl; 
		param+= '&kgwb=' + kgwb + '&jjgkry=' + jjgkry +'&bjrwb=' + bjrwb +'&nilai1hk=' + nilai1hk + '&rppremi=' + rppremi+ '&rphk=' + rphk+ '&kontanan=' + kontanan;
		param+= '&sesi=' + sesi + '&nospb=' + nospb+ '&hk=' + hk;
		param += "&proses=savedata";
		tujuan = 'kebun_slave_save_3premibmtbs.php';
		post_response_text(tujuan, param, respog);
		//document.getElementById('row'+currRow).style.display='none';
		document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('row' + currRow).style.display = 'none';
					currRow += 1;
					sekarang = currRow;
					if (currRow > maxRow) {
						alert('Done');
						document.getElementById('printContainer').innerHTML = '';
						loaddata(0);
					} else {
						loopsave(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function hitungpremi(currRow){
	kgwb = document.getElementById('rowkgwb_' + currRow).innerHTML;
	bss = document.getElementById('rowbss_' + currRow).value;
	hargarplb = document.getElementById('hrgarplb_' + currRow).innerHTML;
	lbbssold = document.getElementById('rowlbbss_' + currRow).innerHTML;
	rplbbssold = document.getElementById('rowrplb_' + currRow).innerHTML;
	ttlkglb = document.getElementById('ttlkglb').innerHTML;
	ttlrplb = document.getElementById('ttlrplb').innerHTML;
	
	ttlkglb = remove_comma_var(ttlkglb);
	ttlrplb = remove_comma_var(ttlrplb);
	lbbssold = remove_comma_var(lbbssold);
	rplbbssold = remove_comma_var(rplbbssold);
	kgwb = remove_comma_var(kgwb);
	bss = remove_comma_var(bss);
	if(bss==''){
		bss=0;
		document.getElementById('rowbss_' + currRow).value=0;
	}
	kglb = parseFloat(kgwb)-parseFloat(bss);
	hargarplb = remove_comma_var(hargarplb);
	if(kglb<0){
		kglb=0;
		alert("Jumlah basis terlalu besar");
	}
	document.getElementById('rowlbbss_' + currRow).innerHTML=numberFormat(kglb);
	rppremilb = parseFloat(kglb)*parseFloat(hargarplb);
	document.getElementById('rowrplb_' + currRow).innerHTML=numberFormat(rppremilb);
	totalkg=0;
	if(lbbssold<kglb){
		pluskg = parseFloat(kglb)-parseFloat(lbbssold);
		totalkg = parseFloat(ttlkglb)+parseFloat(pluskg);
	}else{
		minkg = parseFloat(lbbssold)-parseFloat(kglb);
		totalkg = parseFloat(ttlkglb)-parseFloat(minkg);
	}
	document.getElementById('ttlkglb').innerHTML=numberFormat(totalkg,0);
	
	totalrp=0;
	if(rplbbssold<rppremilb){
		pluskg = parseFloat(rppremilb)-parseFloat(rplbbssold);
		totalrp = parseFloat(ttlrplb)+parseFloat(pluskg);
	}else{
		minrp = parseFloat(rplbbssold)-parseFloat(rppremilb);
		totalrp = parseFloat(ttlrplb)-parseFloat(minrp);
	}
	document.getElementById('ttlrplb').innerHTML=numberFormat(totalrp,0);
}
