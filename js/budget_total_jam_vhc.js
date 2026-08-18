function previewpdf(tahunbudget,kodeorg,tipe){
	jenis       = 'popup';
	param  = 'proses=loadDatadetail';
	param += '&kdUnit=' + kodeorg;
	param += '&thnBudget=' + tahunbudget;
	param += '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	ev          = 'event';
	title       = "Data Detail";
	
	// content = "<iframe frameborder=0 style='width:100%;min-height:400px' src='budget_slave_total_jam_vhc.php?"+param+"'></iframe>";
	// showDialog1(title,content,'900','400',ev);	
	
	printnopopup("budget_slave_total_jam_vhc.php?"+param);
}

function preview(tahunbudget,kodeorg,tipe){
	// width = '';
	// height = '';
	// content = "<fieldset><div id=contpreview align=center style=\"max-width:700px;max-height:200px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog1(title, content, width, height, ev);
	loadDatadetail('popup',tahunbudget,kodeorg,tipe);
}

function unposting(kodeorg,tahunbudget) {
	param = 'proses=unposting';
	param += '&tahunbudget=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_total_jam_vhc.php';
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
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function posting(kodeorg,tahunbudget) {
	param = 'proses=posting';
	param += '&tahunbudget=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_total_jam_vhc.php';
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
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function del(kodeorg,tahunbudget) {
	param  = 'proses=del';
	param += '&tahunbudget=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_total_jam_vhc.php';
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
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdetail(kodeorg,tahunbudget){
	document.getElementById('listdatadetail').style.display = 'block';
	document.getElementById('inputdetail').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	
	document.getElementById('thnBudget').value=tahunbudget;
	document.getElementById('kdTraksi').value=kodeorg;
	loadDatadetail();
}

function add_new_data(){
	document.getElementById('listdatadetail').style.display = 'block';
	document.getElementById('inputdetail').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	//batal();
}

function displayList() {
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('listdatadetail').style.display = 'none';
	document.getElementById('inputdetail').style.display = 'none';
	// batal();
	loaddata(0);
}


function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
	tahunbudget= document.getElementById('tahunbudgetsch').value;
	kodeorg    = document.getElementById('kodeorgsch').value;
	kodetraksi     = document.getElementById('kodewssch').value;
	
	param = 'proses=loaddata&page=' + page;
	param += '&tahunbudget=' + tahunbudget + '&kodeorg=' + kodeorg;
	param += '&kodetraksi=' + kodetraksi;
	tujuan = 'budget_slave_total_jam_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('containdata').innerHTML = isdt[0];
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

function getKdvhc(kdtrak, kdvh) {
	if ((kdtrak == 0) || (kdvh == 0)) {
		kdTraksi = document.getElementById('kdTraksi').value;
		param = 'kdTraksi=' + kdTraksi + '&proses=getKdVhc';
	} else {
		kdTraksi = kdtrak;
		kodevhc = kdvh;
		param = 'kdTraksi=' + kdTraksi + '&proses=getKdVhc';
		param += '&kdVhc=' + kodevhc;
	}
	tujuan = 'budget_slave_total_jam_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('kdVhc').innerHTML = con.responseText;
					if (kdtrak != '' || kdvh != '') {
						getData();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveHead() {
	thnBdget = document.getElementById('thnBudget').value;
	totJamThn = document.getElementById('totJamThn').value;
	kdTraksi = document.getElementById('kdTraksi').options[document.getElementById('kdTraksi').selectedIndex].value;
	kdVhc = document.getElementById('kdVhc').options[document.getElementById('kdVhc').selectedIndex].value;
	kdUnit = document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
	if (thnBdget == '' || totJamThn == '' || kdTraksi == '' || kdVhc == '' || kdUnit == '') {
		alert("Fields are required");
		return;
	}

	if (thnBdget.length < 4) {
		alert("Budget year incorrect");
		return;
	}
	param = 'kdTraksi=' + kdTraksi + '&proses=cekHead' + '&thnBudget=' + thnBdget + '&totJamThn=' + totJamThn + '&kdVhc=' + kdVhc + '&kdUnit=' + kdUnit;

	tujuan = 'budget_slave_total_jam_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					//document.getElementById('kdVhc').innerHTML=con.responseText;
					document.getElementById('thnBudget').disabled = true;
					document.getElementById('totJamThn').disabled = true;
					document.getElementById('kdTraksi').disabled = true;
					document.getElementById('kdVhc').disabled = true;
					document.getElementById('kdUnit').disabled = true;
					document.getElementById('saveDt').disabled = true;
					document.getElementById('printContainer').style.display = 'block';
					b = 1;
					//ar=con.responseText.split("###");
					for (a = 0; a <= 12; a++) {
						document.getElementById('jam_x' + b).disabled = true;
						document.getElementById('jam_x' + b).value = con.responseText;
						document.getElementById('jam_x' + b).disabled = false;
						b++;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function getData() {
	document.getElementById('printContainer').style.display = 'block';
	thnBudget = document.getElementById('thnBudget').value;
	kdTraksi = document.getElementById('kdTraksi').options[document.getElementById('kdTraksi').selectedIndex].value;
	kdVhc = document.getElementById('kdVhc').options[document.getElementById('kdVhc').selectedIndex].value;
	kdUnit = document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
	param = 'kdTraksi=' + kdTraksi + '&proses=getDataEdit' + '&thnBudget=' + thnBudget + '&kdVhc=' + kdVhc + '&kdUnit=' + kdUnit;
	tujuan = 'budget_slave_total_jam_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					b = 1;
					ar = con.responseText.split("###");
					for (a = 0; a <= 11; a++) {
						document.getElementById('jam_x' + b).disabled = true;
						document.getElementById('jam_x' + b).value = ar[a];
						document.getElementById('jam_x' + b).disabled = false;
						b++;
					}

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function saveHead2() {
	thnBudget = document.getElementById('thnBudget').value;
	totJamThn = document.getElementById('totJamThn').value;
	kdTraksi = document.getElementById('kdTraksi').options[document.getElementById('kdTraksi').selectedIndex].value;
	kdVhc = document.getElementById('kdVhc').options[document.getElementById('kdVhc').selectedIndex].value;
	kdUnit = document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
	pros = document.getElementById('proses').value;
	param = 'kdTraksi=' + kdTraksi + '&proses=' + pros + '&thnBudget=' + thnBudget + '&totJamThn=' + totJamThn + '&kdVhc=' + kdVhc + '&kdUnit=' + kdUnit;
	tujuan = 'budget_slave_total_jam_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					loadDatadetail();
					batal();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function saveJam(totRow) {
	strUrl = '';
	thnBudget = document.getElementById('thnBudget').value;
	totJamThn = document.getElementById('totJamThn').value;
	kdTraksi = document.getElementById('kdTraksi').value;
	kdVhc = document.getElementById('kdVhc').value;
	kdUnit = document.getElementById('kdUnit').value;
	pros = document.getElementById('proses').value;
	for (i = 1; i <= totRow; i++) {
		try {
			if (strUrl != '') {
				strUrl += '&arrJam[' + i + ']=' + document.getElementById('jam_x' + i).value;
			} else {
				strUrl += '&arrJam[' + i + ']=' + document.getElementById('jam_x' + i).value;
			}
		} catch (e) {}
	}
	param = 'kdTraksi=' + kdTraksi + '&proses=' + pros + '&thnBudget=' + thnBudget + '&totJamThn=' + totJamThn + '&kdVhc=' + kdVhc + '&totRow=' + totRow;
	param += '&kdUnit=' + kdUnit;
	if (strUrl != '') {
		param += strUrl;
	}
	tujuan = 'budget_slave_total_jam_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);

					loadDatadetail();
					batal();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function batalcari(){
	document.getElementById('kdVhcHead').value = '';
	document.getElementById('kdUnit').value = '';
	loadDatadetail();
}

function loadDatadetail(jenis,thnBudget,kdUnit,tipe) {
	param    = 'proses=loadDatadetail';
	if(jenis=='popup'){
		thnBudget=thnBudget;
		kdUnit=kdUnit;
	}else{		
		thnBudget= document.getElementById('thnBudget').value;
		kdUnit   = document.getElementById('kdTraksi').value;
		kdVhc    = document.getElementById('kdVhc').value;
	}
	if (thnBudget != '') {
		param += '&thnBudget=' + thnBudget;
	}
	if (kdVhc != '') {
		param += '&kdVhc=' + kdVhc;
	}
	if (kdUnit != '') {
		param += '&kdUnit=' + kdUnit;
	}
	
	param += '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	
	
	tujuan = 'budget_slave_total_jam_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('contain')!=undefined){						
						document.getElementById('contain').innerHTML = con.responseText;
						leftFixedTable();
					}
					if(jenis=='popup'){					
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
						//document.getElementById('contpreview').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function deleteData(tahunbudget, kodevhc, unitalokasi, kodetraksi) {

	param = 'kdTraksi=' + kodetraksi + '&proses=deleteData' + '&thnBudget=' + tahunbudget + '&kdUnit=' + unitalokasi + '&kdVhc=' + kodevhc;
	tujuan = 'budget_slave_total_jam_vhc.php';
	if (confirm("Delete, are you sure ?"))
		post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					// loadData();
					editdetail(kodetraksi,tahunbudget);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function fillField(tahunbudget, kodevhc, unitalokasi, kodetraksi, jumlahjam) {
	//getKdvhc(kodetraksi, kodevhc);
	document.getElementById('thnBudget').disabled = true;
	document.getElementById('tmblSave').disabled = true;
	document.getElementById('kdTraksi').disabled = true;
	document.getElementById('kdVhc').disabled = true;
	document.getElementById('kdUnit').disabled = true;
	document.getElementById('thnBudget').value = tahunbudget;
	document.getElementById('totJamThn').value = jumlahjam;
	document.getElementById('kdTraksi').value = kodetraksi;
	document.getElementById('kdUnit').value = unitalokasi;
	document.getElementById('proses').value = 'update';
	document.getElementById('saveDt').disabled = true;
	document.getElementById('totJamThn').disabled = false;
	
	setValue2('kdTraksi',kodetraksi);
	setValue2('kdUnit',unitalokasi);
	setValue2('kdVhc',kodevhc);
	
	getData();
}
function cariBast(num) {
	param = 'proses=loadData';
	param += '&page=' + num;
	tujuan = 'budget_slave_total_jam_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batal() {
	document.getElementById('proses').value = 'saveData';
	// document.getElementById('kdVhc').innerHTML = "<option value=''>" + pilih + "</option>";
	// document.getElementById('kdUnit').value = '';
	document.getElementById('totJamThn').value = '';
	//document.getElementById('thnBudget').value='';
	//document.getElementById('kdTraksi').value = '';
	document.getElementById('thnBudget').disabled = false;
	document.getElementById('totJamThn').disabled = false;
	document.getElementById('kdTraksi').disabled = false;
	document.getElementById('kdVhc').disabled = false;
	document.getElementById('kdUnit').disabled = false;
	document.getElementById('printContainer').style.display = 'none';
	document.getElementById('tmblSave').innerHTML = "";
	document.getElementById('tmblSave').innerHTML = "<button onclick='saveHead()' class='mybutton' name='saveDt' id='saveDt'>" + save + "</button>&nbsp;<button onclick='batal()' class='mybutton' name='btl' id='btl'>" + btl + "</button>";
	for (q = 1; q < 12; q++) {
		document.getElementById('jam_x' + q).value = '';
	}
	
	setValue2('kdVhc','');
	setValue2('kdUnit','');
	
	loadDatadetail();
}

function dataKeExcel(ev, tujuan) {
	kdBrg = document.getElementById('kdBrg').value;
	kdPbrk = document.getElementById('kdPbrk').value;
	tgl = document.getElementById('tglTrans').value;

	//gudang	=gudang.options[gudang.selectedIndex].value;
	judul = 'Report Ms.Excel';
	param = 'kdBrg=' + kdBrg + '&kdPbrk=' + kdPbrk + '&tgl=' + tgl;
	//alert(param);
	printFile(param, tujuan, judul, ev)
}
function dataKePDF(ev) {
	kdBrg = document.getElementById('kdBrg').value;
	kdPbrk = document.getElementById('kdPbrk').value;
	tgl = document.getElementById('tglTrans').value;

	tujuan = 'pabrik_slaveLaporanTimbanganPdf.php';
	judul = 'Report PDF';
	param = 'kdBrg=' + kdBrg + '&kdPbrk=' + kdPbrk + '&tgl=' + tgl;
	//alert(param);
	printFile(param, tujuan, judul, ev)
}
function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '700';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}