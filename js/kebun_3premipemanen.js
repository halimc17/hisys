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
	tujuan = 'kebun_slave_save_3premipemanen.php';
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
function del(notransaksi, prd, unit) {
	param = 'proses=deleteTrans&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit;
	tujuan = 'kebun_slave_save_3premipemanen.php';
	if (confirm(' Anda yakin ???')) {
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
	content = "<div id=containerView style=\"width:100%;max-height:450px;overflow:auto;\"></div>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}
function view(notransaksi, prd, unit,divisi,tipe) {
	form();
	param = 'proses=view&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit+ '&tipe=' + tipe+ '&divisi=' + divisi;
	tujuan = 'kebun_slave_save_3premipemanen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerView').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewexcel(notransaksi, prd, unit,divisi,tipe){
	param = 'proses=view' + '&prd=' + prd + '&unit=' + unit+ '&notransaksi=' + notransaksi+ '&tipe=' + tipe;
	tujuan = 'kebun_slave_save_3premipemanen.php' + "?" + param;
	width = '';
	height = '';
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	
	printFile(param,tujuan,title,ev);
}

function unposting(notransaksi, prd, unit, baris) {
	param = 'proses=unposting&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit;
	tujuan = 'kebun_slave_save_3premipemanen.php';
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
					// document.getElementById('tr_' + baris).cells[17].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[18].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[19].innerHTML = "<img src=images/application/application_delete.png class=resicon class=zImgBtn height='30'  title='Please Reload Frame'>";
					// document.getElementById('tr_' + baris).cells[20].innerHTML = "<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Please Reload Frame'>";
					alert('Unposting Success.');
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function posting(notransaksi, prd, unit, baris) {
	param = 'notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit;
	tujuan = 'kebun_slave_save_posting3premipemanen.php';
	if (confirm('Posting akan memakan waktu cukup lama dan pastikan koneksi anda stabil, ingin tetap melanjutkan ???')) {
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
					// document.getElementById('tr_' + baris).cells[17].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[18].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[19].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[20].innerHTML = "<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posted'>";
					alert('Posting Success.');
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deleteTrans(maxRow) {
	notransaksi = document.getElementById('notransaksi').value;
	prd = document.getElementById('prd').value;
	unit = document.getElementById('unit').value;
	tgl1 = document.getElementById('tgl1').value;
	tgl2 = document.getElementById('tgl2').value;
	param = 'proses=deleteTrans&maxRow=' + maxRow + '&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit;
	param += '&tgl1=' + tgl1;
	param += '&tgl2=' + tgl2;
	tujuan = 'kebun_slave_save_3premipemanen.php';
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
	loopsave(1, maxRow);
}
function loopsave(currRow, maxRow) {
	notransaksi   = document.getElementById('notransaksi').value;
	prd           = document.getElementById('prd').value;
	unit          = document.getElementById('unit').value;
	afd           = document.getElementById('afd').value;
	tahap         = document.getElementById('tahap').value;
	tgl1          = document.getElementById('tgl1').value;
	tgl2          = document.getElementById('tgl2').value;
	topografi     = document.getElementById('topografi_' + currRow).innerHTML;
	tglpnn        = document.getElementById('tglpnn_' + currRow).innerHTML;
	rowkary       = document.getElementById('rowkary_' + currRow).innerHTML;
	rowmdr        = document.getElementById('rowmdr_' + currRow).innerHTML;
	rowkrn        = document.getElementById('rowkrn_' + currRow).innerHTML;
	rowtt         = document.getElementById('rowtt_' + currRow).innerHTML;
	rowjjg        = document.getElementById('rowjjg_' + currRow).innerHTML;
	rowkg         = document.getElementById('rowkg_' + currRow).innerHTML;
	rowkgbss      = document.getElementById('rowkgbss_' + currRow).innerHTML;
	rowkglb1      = document.getElementById('rowkglb1_' + currRow).innerHTML;
	rowrplb1      = document.getElementById('rowrplb1_' + currRow).innerHTML;
	rowkgbrd      = document.getElementById('rowkgbrd_' + currRow).innerHTML;
	rowrpbrd      = document.getElementById('rowrpbrd_' + currRow).innerHTML;
	rowtopo       = document.getElementById('rowtopo_' + currRow).innerHTML;
	rowdenda      = document.getElementById('rowdenda_' + currRow).innerHTML;
	if (prd == '' || unit == '' || afd == '') {
		alert("Data tidak lengkap");
		return;
	} else {
		param = 'notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit + '&afd=' + afd + '&rowkary=' + rowkary + '&rowmdr=' + rowmdr + '&rowtt=' + rowtt + '&tglpnn=' + tglpnn + '&rowjjg=' + rowjjg + '&rowkg=' + rowkg + '&rowkgbss=' + rowkgbss + '&rowkglb1=' + rowkglb1 + '&rowrplb1=' + rowrplb1 + '&rowkgbrd=' + rowkgbrd + '&rowrpbrd=' + rowrpbrd + '&rowdenda=' + rowdenda + '&rowkrn=' + rowkrn + '&topografi=' + topografi+ '&rowtopo=' + rowtopo+ '&tahap=' + tahap;
		param += "&proses=savedata";
		param += '&tgl1=' + tgl1;
		param += '&tgl2=' + tgl2;
		tujuan = 'kebun_slave_save_3premipemanen.php';
		post_response_text(tujuan, param, respog);
		document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
		//lockScreen('wait');
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

function gethitungpremi(currRow){
	kgbruto = document.getElementById('rowkgbruto_' + currRow).innerHTML;
	potbrd = document.getElementById('potbrd_' + currRow).value;
	basiskg = document.getElementById('rowkgbss_' + currRow).innerHTML;
	hargalbbss = document.getElementById('rowhargarplb1_' + currRow).innerHTML;
	premibrondol = document.getElementById('rowrpbrd_' + currRow).innerHTML;
	premihadir = document.getElementById('rowtopo_' + currRow).innerHTML;
	denda = document.getElementById('rowdendalama_' + currRow).value;
	hargabrondol = document.getElementById('rowhargabrd_' + currRow).innerHTML;
	kgbruto = remove_comma_var(kgbruto);
	potbrd = remove_comma_var(potbrd);
	basiskg = remove_comma_var(basiskg);
	hargalbbss = remove_comma_var(hargalbbss);
	premibrondol = remove_comma_var(premibrondol);
	premihadir = remove_comma_var(premihadir);
	denda = remove_comma_var(denda);
	hargabrondol = remove_comma_var(hargabrondol);
	if(potbrd==''){potbrd=0;}
	if(hargabrondol=='' || hargabrondol==0){alert("Harga Rupiah / Kg Brondolan belum ada, silahkan tambah di Kebun - Setup - Ongkos Panen"); return;}
	
	
	kgnetto = parseFloat(kgbruto)-parseFloat(potbrd);
	document.getElementById('rowkg_' + currRow).innerHTML=numberFormat(kgnetto,2);
	
	kglbbss = parseFloat(kgnetto)-parseFloat(basiskg);
	document.getElementById('rowkglb1_' + currRow).innerHTML=numberFormat(kglbbss,2);
	 
	rppremilb = parseFloat(hargalbbss)*parseFloat(kglbbss);
	document.getElementById('rowrplb1_' + currRow).innerHTML=numberFormat(rppremilb,2);
	
	rpdendabrd = parseFloat(hargabrondol)*parseFloat(potbrd);
	document.getElementById('rowdenda_' + currRow).innerHTML='';
	document.getElementById('rowdenda_' + currRow).innerHTML=numberFormat((parseFloat(denda)+parseFloat(rpdendabrd)),2);
	
	gtotal = parseFloat(rppremilb)+parseFloat(premibrondol)+parseFloat(premihadir)-parseFloat(denda);
	document.getElementById('gtotal_' + currRow).innerHTML=numberFormat(gtotal,2);
	
}




/*******************************************************************
********************************************************************
********************************************************************/
// function del(tanggal,karyid,jabatanlist,unitlist){
   	// param='proses=delete'+'&tanggal='+tanggal+'&karyid='+karyid+'&jabatanlist='+jabatanlist+'&unitlist='+unitlist;
    // tujuan='kebun_slave_premimandoranlistdelete.php';
    // if(confirm(' Anda yakin ???')){
        // post_response_text(tujuan, param, respog);	
    // }
    // function respog(){
		// if(con.readyState==4){
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
						// alert(con.responseText);
				// }else{
					// zPreview('kebun_slave_premimandoranlist','##tgl1list##tgl2list##unitlist##jabatanlist##afdlist','printContainerlist');
				// }
			// }else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }	
    // }
// }