function searchBrg(tab, title, content, ev) {
	qwe = document.getElementById('kodebarang');
	qweV = qwe.value;
	alertify.popup("Find",content).set({'resizable':true,'maximizable':true}).resizeTo('50%','70%');
	if (qweV == '') {
	}else {
		document.getElementById('no_brg').value = qweV;
		findBrg(tab);
	}
}

function getnoakun() {
	dept    = trim(document.getElementById('dept').value);
	kodeorg = trim(document.getElementById('kodeorg').value);
	
	param = '';
	param += '&method=getnoakun';
	param += '&dept=' + dept;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					document.getElementById('jenisbiaya').innerHTML = '';
					alertify.alert(con.responseText);
				} else {
					document.getElementById('jenisbiaya').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function findBrg(tab) {
	txt = trim(document.getElementById('no_brg').value);
	thnBudget = trim(document.getElementById('tahunbudget').value);
	param = 'tab=1&txtfind=' + txt +'&thnBudget=' + thnBudget;
	
	
	param += '&thnBudget=' + thnBudget;
	tujuan = 'budget_slave_ws_biaya_barang.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function setBrg(tab, no_brg, namabrg, satuan, nomor) {
	document.getElementById('kodebarang').value = no_brg;
	document.getElementById('namabarang').value = namabrg;
	alertify.popup().destroy();
	kalikanRp('kodebarang');
}


function unposting(tahunbudget,kodeorg, tipebudget) {
	param = 'method=unposting';
	param += '&tahun=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	param += '&tipebudget=' + tipebudget;
	tujuan = 'budget_slave_by_umum.php';
	alertify.confirm("Warning","Anda yakin ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					showposting();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function posting(tahunbudget,kodeorg, tipebudget){
	param = 'method=posting';
	param += '&tahun=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	param += '&tipebudget=' + tipebudget;
	tujuan = 'budget_slave_by_umum.php';
	alertify.confirm("Warning","Anda yakin ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					showposting();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function showposting(){
	tahun  = document.getElementById('tahunpostsch').value;
	kodeorg= document.getElementById('kodeorgpostsch').value;
	
	param  = 'method=showposting';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contpostingdata').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function sebarkan(row,maxrow,jenis){
	row   = document.getElementById('awalsebar').value;
	maxrow= document.getElementById('akhirsebar').value;
	
	if(maxrow =='' || maxrow ==0){
		alertify.alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if(jenis=='1'){
		//per arus kas
		alertify.confirm("Warning","Anda yakin ???",
			function(){
				sebararuskas(row,maxrow);
			},
			function(){
				return;
			}
		);
	}else if(jenis=='2'){
		//per dept
		alertify.confirm("Warning","Anda yakin ???",
			function(){
				sebardept(row,maxrow);
			},
			function(){
				return;
			}
		);
	}else{
		//per detail
		alertify.confirm("Warning","Anda yakin ???",
			function(){
				sebardetail(row,maxrow);
			},
			function(){
				return;
			}
		);
	}
}


function sebardept(row,maxrow){
	row  = parseFloat(row);
	param= '';
	tahun= document.getElementById('tahun'+row).innerHTML;
	dept = document.getElementById('dept'+row).innerHTML;
	
	for (i = 1; i <= 12; i++) {
		persen= document.getElementById('persen_'+i).value;
		param += '&persen[' + i + ']=' + persen;
	}

	param += '&tahun=' + tahun;
	param += '&dept=' + dept;
	param += '&method=sebardept';

	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	
	document.getElementById('rowsebar'+row).style.backgroundColor='cyan';
	document.getElementById('chkboxsebar'+row).checked=true;
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('rowsebar'+row).style.display='none';
					row+=1;
                    if((row>maxrow) || (maxrow == undefined)){
						alertify.alert("done");
						getPageSbr();
						if(maxrow != undefined){
							//document.getElementById('awalbaris').value=row;
						}
					} else {
						sebardept(row,maxrow);
                    }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function sebararuskas(row,maxrow){
	row    = parseFloat(row);
	param  = '';
	tahun  = document.getElementById('tahun'+row).innerHTML;
	aruskas= document.getElementById('aruskas'+row).innerHTML;
	
	for (i = 1; i <= 12; i++) {
		persen= document.getElementById('persen_'+i).value;
		param += '&persen[' + i + ']=' + persen;
	}

	param += '&tahun=' + tahun;
	param += '&aruskas=' + aruskas;
	param += '&method=sebararuskas';

	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	
	document.getElementById('rowsebar'+row).style.backgroundColor='cyan';
	document.getElementById('chkboxsebar'+row).checked=true;
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('rowsebar'+row).style.display='none';
					row+=1;
                    if((row>maxrow) || (maxrow == undefined)){
						alertify.alert("done");
						getPageSbr();
						if(maxrow != undefined){
							//document.getElementById('awalbaris').value=row;
						}
					} else {
						sebararuskas(row,maxrow);
                    }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function sebardetail(row,maxrow){
	row     = parseFloat(row);
	param  = '';
	for (i = 1; i <= 12; i++) {
		persen= document.getElementById('persen_'+i).value;
		param += '&persen[' + i + ']=' + persen;
	}
	
	index= document.getElementById('index'+row).innerHTML;
	param += '&index[]=' + index;
	
	document.getElementById('rowsebar'+row).style.backgroundColor='cyan';
	document.getElementById('chkboxsebar'+row).checked=true;
	
	param += '&method=sebardetail';
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('rowsebar'+row).style.display='none';
					row+=1;
                    if((row>maxrow) || (maxrow == undefined)){
						alertify.alert("done");
						getPageSbr();
						if(maxrow != undefined){
							//document.getElementById('awalbaris').value=row;
						}
					} else {
						sebardetail(row,maxrow);
                    }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function hapuspersen(){
	for(i=1;i<=12;i++){
		document.getElementById('persen_'+i).value=0;
	}
}
function add_new_data(){
	document.getElementById('inputdata').style.display = 'block';
	//document.getElementById('contdetail').style.display = 'block';
	document.getElementById('container0').style.display = 'block';
	
	
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';
	document.getElementById('listsebaran').style.display = 'none';
	document.getElementById('formcarisebaran').style.display = 'none';
	
	// document.getElementById('listdatasdm').innerHTML = "";
	// document.getElementById('listdatamat').innerHTML = "";
	// document.getElementById('listdataalat').innerHTML = "";
	// document.getElementById('listdatakont').innerHTML = "";
	// document.getElementById('listdatavhc').innerHTML = "";
	
	//batalheader();
}

function displayList() {
	document.getElementById('formcari').style.display = 'block';
	document.getElementById('container0').style.display = 'block';
	//document.getElementById('contdetail').style.display = 'none';
	document.getElementById('inputdata').style.display = 'none';
	
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('listsebaran').style.display = 'none';
	document.getElementById('formcarisebaran').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';
	updateTab();
}

function add_sebaran(){
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('container0').style.display = 'none';
	document.getElementById('listsebaran').style.display = 'block';
	document.getElementById('formcarisebaran').style.display = 'block';
	document.getElementById('inputdata').style.display = 'none';
	//document.getElementById('contdetail').style.display = 'none';
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';
	showsebaran();
}

function add_posting(){
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('container0').style.display = 'none';
	document.getElementById('listsebaran').style.display = 'none';
	document.getElementById('formcarisebaran').style.display = 'none';
	document.getElementById('inputdata').style.display = 'none';
	//document.getElementById('contdetail').style.display = 'none';
	document.getElementById('contposting').style.display = 'block';
	document.getElementById('formcariposting').style.display = 'block';
	showposting();
}

function showsebaran(page){
	tahun    = document.getElementById('tahunbudgetsbr').value;
	kodeorg  = document.getElementById('kodeorgsbr').value;
	dept     = document.getElementById('deptsbr').value;
	aruskas  = document.getElementById('aruskassbr').value;
	noakun   = document.getElementById('akunsbr').value;
	ket      = document.getElementById('ketsbd').value;
	sebaran  = document.getElementById('sebaran').value;
	jlhbaris = document.getElementById('jlhbaris').value;
	tampilkan= document.getElementById('tampilkan').value;
	
	
	param  = 'method=showsebaran&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&dept=' + dept + '&aruskas=' + aruskas;
	param += '&noakunsch=' + noakun + '&ket=' + ket;
	param += '&sebaran=' + sebaran + '&jlhbaris=' + jlhbaris;
	param += '&tampilkan=' + tampilkan;
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tampilkan=='2'){
						document.getElementById('headdept').style.display="";
						document.getElementById('arusdept').style.display="none";
						document.getElementById('akundept').style.display="none";
					}else if(tampilkan=='1'){
						document.getElementById('arusdept').style.display="";
						document.getElementById('headdept').style.display="none";
						document.getElementById('akundept').style.display="none";
					}else{
						document.getElementById('headdept').style.display="none";
						document.getElementById('arusdept').style.display="none";
						document.getElementById('akundept').style.display="";
					}
					isdt = con.responseText.split("####");
					//document.getElementById('listsebaran').innerHTML = con.responseText;
					document.getElementById('containsebar').innerHTML = isdt[0];
					document.getElementById('footDatasebar').innerHTML = isdt[1];
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPageSbr() {
	pg = document.getElementById('pagessbr');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	showsebaran(paged);
}

function getkodevhc() {
	tahunbudget= document.getElementById('tahunbudget').value;
	kodeorg    = document.getElementById('kodeorg').value;
	
	param = 'method=getkodevhc&tahunbudget=' + tahunbudget+ '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					dt = con.responseText.split("####");
					document.getElementById('kodevhc').innerHTML = dt[0];
					document.getElementById('kodebarang').innerHTML = dt[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getakun(){
	aruskas=getValue('aruskas');
	tipebudget=getValue('tipebudget');
	
	param = 'method=getakun' + '&aruskas=' + aruskas+ '&tipebudget=' + tipebudget;
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('jenisbiaya').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getaruskas(aruskas){
	keluarmasuk=getValue('keluarmasuk');
	tipebudget=getValue('tipebudget');
	jenisbiaya=getValue('jenisbiaya');
	
	param = 'method=getaruskas' + '&keluarmasuk=' + keluarmasuk+ '&tipebudget=' + tipebudget+ '&noakun=' + jenisbiaya+ '&aruskas=' + aruskas;
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('aruskas').innerHTML=con.responseText;
					//document.getElementById('jenisbiaya').innerHTML='';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function bersihkanDonk() {
	for (zx = 1; zx < 13; zx++) {
		document.getElementById('ss' + zx).value = 0;
	}
}

function sebarkanall(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Sebaran Seluruhnya ???")) {
		sebarkanBoo(1, maxRow);
	}
}

function sebarkanBoo(currRow, maxRow) {
	//sebarkanBoo(kunci, baris, obj, rupe, fis)
	if (maxRow != undefined) {
		document.getElementById('chkboxsebar'+ currRow).checked=true;
	}
	baris = currRow;
	kunci = document.getElementById('kunci'+ currRow).value;
	obj = document.getElementById('chkboxsebar'+ currRow);
	rupe = document.getElementById('rupiah'+ currRow).value;
	fis = document.getElementById('jlh'+ currRow).value;
	
	
	document.getElementById('baris' + baris).style.backgroundColor = 'orange';
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
		param = 'cekapa=sebarDoong&kunci=' + kunci;
		param += '&var1=' + (var1 / zz) + '&var2=' + (var2 / zz) + '&var3=' + (var3 / zz) + '&var4=' + (var4 / zz) + '&var5=' + (var5 / zz);
		param += '&var6=' + (var6 / zz) + '&var7=' + (var7 / zz) + '&var8=' + (var8 / zz) + '&var9=' + (var9 / zz) + '&var10=' + (var10 / zz);
		param += '&var11=' + (var11 / zz) + '&var12=' + (var12 / zz) + '&rupe=' + rupe + '&fis=' + fis;
		tujuan = 'budget_slave_by_umum.php';
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
					if (currRow != undefined) {
						document.getElementById('baris' + currRow).style.backgroundColor = 'green';
						document.getElementById('baris' + currRow).style.display = 'none';
					}
					currRow += 1;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						alert('Done, untuk melihat data yang sudah disebarkan pastikan filter Sebaran terisi Seluruhnya atau Yes');
						updateTabs();
					} else {
						sebarkanBoo(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
				document.getElementById('baris' + baris).style.backgroundColor = 'red';
			}
		}
	}
}

// function sebarkanBoo(kunci, baris, obj, rupe, fis) {
	// document.getElementById('baris' + baris).style.backgroundColor = 'orange';
	// var1 = parseInt(document.getElementById('ss1').value);
	// var2 = parseInt(document.getElementById('ss2').value);
	// var3 = parseInt(document.getElementById('ss3').value);
	// var4 = parseInt(document.getElementById('ss4').value);
	// var5 = parseInt(document.getElementById('ss5').value);
	// var6 = parseInt(document.getElementById('ss6').value);
	// var7 = parseInt(document.getElementById('ss7').value);
	// var8 = parseInt(document.getElementById('ss8').value);
	// var9 = parseInt(document.getElementById('ss9').value);
	// var10 = parseInt(document.getElementById('ss10').value);
	// var11 = parseInt(document.getElementById('ss11').value);
	// var12 = parseInt(document.getElementById('ss12').value);
	// zz = var1 + var2 + var3 + var4 + var5 + var6 + var7 + var8 + var9 + var10 + var11 + var12;
	// if (zz && zz > 0) {
		// param = 'cekapa=sebarDoong&kunci=' + kunci;
		// param += '&var1=' + (var1 / zz) + '&var2=' + (var2 / zz) + '&var3=' + (var3 / zz) + '&var4=' + (var4 / zz) + '&var5=' + (var5 / zz);
		// param += '&var6=' + (var6 / zz) + '&var7=' + (var7 / zz) + '&var8=' + (var8 / zz) + '&var9=' + (var9 / zz) + '&var10=' + (var10 / zz);
		// param += '&var11=' + (var11 / zz) + '&var12=' + (var12 / zz) + '&rupe=' + rupe + '&fis=' + fis;
		// tujuan = 'budget_slave_by_umum.php';
		// if (obj.checked)
			// post_response_text(tujuan, param, respog);
	// } else {
		// alert('Distribution incorrect');
	// }

	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alertify.alert(con.responseText);
					// document.getElementById('baris' + baris).style.backgroundColor = 'red';
				// } else {
					// //                    updateTab4();
					// document.getElementById('baris' + baris).style.backgroundColor = 'green';
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
				// document.getElementById('baris' + baris).style.backgroundColor = 'red';
			// }
		// }
	// }
// }

function simpan() {
	kodebudget  = document.getElementById('kodebudget');
	jumlahbiaya = document.getElementById('jumlahbiaya');
	tipebudget  = document.getElementById('tipebudget');
	tahunbudget = document.getElementById('tahunbudget');
	jenisbiayaV  = document.getElementById('jenisbiaya').value;
	ket         = document.getElementById('ketUmum');
	ktrngan     = ket.value;
	kodebudgetV = kodebudget.value;
	jumlahbiayaV= jumlahbiaya.value;
	tipebudgetV = tipebudget.value;
	tahunbudgetV= tahunbudget.value;
	kodevhc     = document.getElementById('kodevhc').value;
	jamperthn   = document.getElementById('jamperthn').value;
	method      = document.getElementById('method').value;
	id          = document.getElementById('idbgt').value;
	jamperthnold= document.getElementById('jamperthnold').value;
	aruskas     = getValue('aruskas');
	kodeorg     = getValue('kodeorg');
	dept        = getValue('dept');
	kodebarang  = getValue('kodebarang');
	jlhbarang   = getValue('jlhbarang');
	
	
	
	if (tipebudgetV == '') {
		alert('Budget type is empty.');
		return;
	}
	if (tahunbudgetV == '') {
		alert('Budget year is empty.');
		return;
	}
	if (kodebudgetV == '') {
		alert('Budget code is empty.');
		return;
	}
	if (jenisbiayaV == '') {
		alert('Cost type is empty.');
		return;
	}
	if ((jumlahbiayaV == '') || (parseFloat(jumlahbiayaV) == 0)) {
		alert('Amount is empty.');
		return;
	}

	param = 'tahunbudget=' + tahunbudgetV + '&tipebudget=' + tipebudgetV + '&jenisbiaya=' + jenisbiayaV + '&kodebudget=' + kodebudgetV + '&jumlahbiaya=' + jumlahbiayaV + '&ktrngan=' + ktrngan + '&kodevhc=' + kodevhc + '&jamperthn=' + jamperthn+'&aruskas='+aruskas;
	param2 = 'cekapa=cekclose&tahunbudget=' + tahunbudgetV + '&tipebudget=' + tipebudgetV + '&jenisbiaya=' + jenisbiayaV + '&kodebudget=' + kodebudgetV + '&jumlahbiaya=' + jumlahbiayaV + '&ktrngan=' + ktrngan + '&kodevhc=' + kodevhc + '&jamperthn=' + jamperthn+'&aruskas='+aruskas;
	param += '&kodeorg=' + kodeorg;
	param += '&dept=' + dept;
	param += '&cekapa=' + method;
	param += '&id=' + id;
	param += '&jamperthnold=' + jamperthnold;
	param += '&kodebarang=' + kodebarang;
	param += '&jlhbarang=' + jlhbarang;
	tujuan = 'budget_slave_by_umum.php';
	//tambah baru
	post_response_text(tujuan, param2, respon2);
	function respon2() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					//ada error
					alertify.alert(con.responseText);
				} else {
					//tidak ada error, cek response
 					if (con.responseText == '') {
						post_response_text(tujuan, param, respon);
					} else {
						alertify.alert(con.responseText);
					}
					document.getElementById('jumlahbiaya').disabled=false;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					//ada error
					alertify.alert(con.responseText);
				} else {
					//tidak ada error, cek response
					if (con.responseText == '') {
						alertify.alert('Done');
						document.getElementById('jumlahbiaya').value = '';
						//document.getElementById('jenisbiaya').value = '';
						document.getElementById('ketUmum').value = '';
						document.getElementById('kodevhc').value = '';
						document.getElementById('jamperthn').value = '';
						document.getElementById('method').value = 'saveatas';
						document.getElementById('idbgt').value = '';
						document.getElementById('kodebarang').value = '';
						document.getElementById('namabarang').value = '';
						document.getElementById('jlhbarang').value = '';
						document.getElementById('rpbarang').value = '';
						setValue2('kodevhc',null);
						
						updateTab();
					} else {
						alertify.alert(con.responseText);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function updateTabs2() {
	document.getElementById('container2').innerHTML = '';
	document.getElementById('tutup2').disabled = true;
}

function updateTahun() {
	hidden0 = document.getElementById('hidden0');
	hidden0V = hidden0.value;
	hidden1 = document.getElementById('hidden1');
	hidden1V = hidden1.value;
	hidden2 = document.getElementById('hidden2');
	hidden2V = hidden2.value;
	param = 'cekapa=updatetahun';
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					//ada error
					alertify.alert(con.responseText);
				} else {
					//tidak ada error, cek response
					if (con.responseText == '') {
						//                        alert('Done');
					} else {
						document.getElementById('pilihtahun0').innerHTML = con.responseText;
						document.getElementById('pilihtahun0').value = hidden0V;
						document.getElementById('pilihtahun1').innerHTML = con.responseText;
						document.getElementById('pilihtahun1').value = hidden1V;
						document.getElementById('pilihtahun2').innerHTML = con.responseText;
						document.getElementById('pilihtahun2').value = hidden2V;
						
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function updateTab() {
	tipebudgetV  = document.getElementById('tipebudget').value;
	akun        = document.getElementById('pilihakun0').value;
	ket         = document.getElementById('pilihket0').value;
	tahunbudget = document.getElementById('tahunbudget');
	tahunbudgetV= tahunbudget.value;
	jenisbiayaV  = document.getElementById('jenisbiaya').value;
	pilihtahun0V = document.getElementById('pilihtahun0').value;
	aruskas     = getValue('aruskassch');
	kodeorg     = getValue('kodeorgsch');
	dept        = getValue('deptsch');
	
	param = 'cekapa=tab&tipebudget=' + tipebudgetV + '&tahunbudget=' + tahunbudgetV + '&jenisbiaya=' + jenisbiayaV + '&pilihtahun0=' + pilihtahun0V + '&akun=' + akun + '&ket=' + ket;
	param += '&kodeorg=' + kodeorg;
	param += '&dept=' + dept;
	param += '&aruskas=' + aruskas;
	
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container0').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function updateTabs(tahunsebelah) {
	akun = document.getElementById('pilihakun1').value;
	ket = document.getElementById('pilihket1').value;
	sebaran = document.getElementById('kdsebaranData').value;

	tipebudget = document.getElementById('tipebudget');
	tipebudgetV = tipebudget.value;
	tahunbudget = document.getElementById('tahunbudget');
	tahunbudgetV = tahunbudget.value;
	jenisbiaya = document.getElementById('jenisbiaya');
	jenisbiayaV = jenisbiaya.options[jenisbiaya.selectedIndex].value;
	pilihtahun1 = document.getElementById('pilihtahun1');
	pilihtahun1V = pilihtahun1.options[pilihtahun1.selectedIndex].value;
	aruskas     = getValue('aruskassch1');
	kodeorg     = getValue('kodeorgsch1');
	dept	     = getValue('deptsch1');
	
	param = 'cekapa=tabs&tipebudget=' + tipebudgetV + '&tahunbudget=' + tahunbudgetV + '&jenisbiaya=' + jenisbiayaV + '&pilihtahun1=' + pilihtahun1V + '&akun=' + akun + '&ket=' + ket + '&sebaran=' + sebaran;
	param += '&kodeorg=' + kodeorg;
	param += '&dept=' + dept;
	param += '&aruskas=' + aruskas;
	
	tujuan = 'budget_slave_by_umum.php';
	//alert(tujuan+' '+param);
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//                    alertify.alert(con.responseText);
					document.getElementById('container1').innerHTML = con.responseText;
					updateTahun();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function persiapantutup2() {
	updateTab2();
	document.getElementById('tutup2').disabled = false;

}

function updateTab2(apa) {
	pilihtahun2 = document.getElementById('pilihtahun2');
	pilihtahun2V = pilihtahun2.options[pilihtahun2.selectedIndex].value;
	param = 'cekapa=tab2&pilihtahun2=' + pilihtahun2V;
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container2').innerHTML = con.responseText;
					//                    if(apa=='all')updateTab5('all');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function tutup2(row) {
	kunci = document.getElementById('kunci_' + row).value;
	param = 'cekapa=tutup&kunci=' + kunci;
	tujuan = 'budget_slave_by_umum.php';
	if (confirm('Tutup?\nJika sudah Tutup, tidak dapat menambah/mengubah data.'))
		post_response_text(tujuan, param, respon);
	document.getElementById('baris_' + row).style.backgroundColor = 'orange';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					//ada error
					alertify.alert(con.responseText);
					document.getElementById('baris_' + row).style.backgroundColor = 'red';
				} else {
					//tidak ada error, hilangkan baris
					document.getElementById('baris_' + row).style.display = 'none';
					try {
						//coba, apakah baris terakhir
						x = row + 1;
						if (document.getElementById('baris_' + x)) {
							//kalo bukan, looping ke awal fungsi
							row = x;
							tutup2(row);
						} else {
							//baris terakhir, hapus header, berikan pesan DONE
							alert('Done');
							document.getElementById('baris_0').style.display = 'none';
							updateTab0('all');
						}
					} catch (e) {
						document.getElementById('baris_0').style.display = 'none';
						updateTab0('all');
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deleteRow(kunci) { {
		param = 'cekapa=delete&kunci=' + kunci;
		tujuan = 'budget_slave_by_umum.php';
		if (confirm('Delete?'))
			post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alert('Done.');
					updateTab();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function sebaranumum(kunci, ev) {
	param = 'cekapa=sebaran&kunci=' + kunci;
	tujuan = 'budget_slave_by_umum.php' + "?" + param;
	width = '200';
	height = '300';

	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1('Sebaran ' + kunci, content, width, height, ev);

}

function ubahNilai(persen, total, source) {
	comp = 'persenPrdksi';
	tot = 0;
	for (x = 1; x < 13; x++) {
		if (document.getElementById(comp + x).value == '')
			document.getElementById(comp + x).value = 0;
		tot += parseFloat(document.getElementById(comp + x).value);
		document.getElementById(source + x).value = 0;
	}
	if (tot > 0) {
		for (x = 1; x < 13; x++) {
			document.getElementById(source + x).value = 0;
		}
	}
	for (x = 1; x < 13; x++) {
		if (document.getElementById(comp + x).value != '' || document.getElementById(comp + x).value != 0) {
			z = parseFloat(document.getElementById(comp + x).value);
			if (tot > 0)
				document.getElementById(source + x).value = ((z / tot) * total).toFixed(2);
		}
	}
}
function clearForm() {
	if (confirm("Delete, are you sure?")) {
		for (sr = 1; sr < 13; sr++) {
			document.getElementById('brt_x' + sr).value = '';
			document.getElementById('persenPrdksi' + sr).value = '';
		}
	} else {
		return;
	}
}

function simpansebaran(kunci, total, ev) {
	strUrl = '';

	for (i = 1; i <= 12; i++) {
		try {
			if (strUrl != '') {
				strUrl += '&arrBrt[' + i + ']=' + document.getElementById('brt_x' + i).value;
			} else {
				strUrl += '&arrBrt[' + i + ']=' + document.getElementById('brt_x' + i).value;
			}
		} catch (e) {}
	}
	param = 'cekapa=insertDistribusi&kunci=' + kunci + '&totalSetahn=' + total;
	// alert(param);
	if (strUrl != '') {
		param += strUrl;
	}

	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert('Done');

					parent.updateTabs();
					parent.closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function angka_doangsamaminus(e) //only numeric e is event
{
	key = getKey(e);
	//    if((key<48 || key>57) && (key!=8 && key != 45 && key != 150 && key!=46  && key!=127 && key!=true)) // 45 hypen
	if ((key < 48 || key > 57) && (key != 8 && key != 150 && key != 46 && key != 127 && key != true))
		return false;
	else {
		return true;
	}
}

function kalikanRp(sumber) {
	kodevhc = document.getElementById('kodevhc').value;
	if (kodevhc == '') {
		document.getElementById('jamperthn').disabled = true;
		document.getElementById('jamperthn').value = '';
	} else {
		document.getElementById('jamperthn').disabled = false;
	}
	kodebarang = document.getElementById('kodebarang').value;
	if (kodebarang == '') {
		document.getElementById('jlhbarang').disabled = true;
		document.getElementById('jlhbarang').value = '';
	} else {
		document.getElementById('jlhbarang').disabled = false;
	}
	
	
	
	
	jlhbarang = document.getElementById('jlhbarang').value;
	jamperthn = document.getElementById('jamperthn').value;
	
	tahunbudget= document.getElementById('tahunbudget').value;
	kodeorg    = document.getElementById('kodeorg').value;
	
	
	param  = 'cekapa=vhc&kodevhc=' + kodevhc + '&jamperthn=' + jamperthn;
	param += '&kodebarang=' + kodebarang + '&jlhbarang=' + jlhbarang;
	param += '&tahunbudget=' + tahunbudget+ '&kodeorg=' + kodeorg;

	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					dt = con.responseText.split("####");
					if(sumber=='kodevhc'){						
						document.getElementById('kodebarang').value = '';
						document.getElementById('namabarang').value = '';
						document.getElementById('jlhbarang').value = '';
						document.getElementById('rpbarang').value = '';
						document.getElementById('jlhbarang').disabled = true;
						
						document.getElementById('rpvra').value = dt[0];
						document.getElementById('rpvra').value = dt[3];
					}
					if(sumber=='kodebarang'){						
						document.getElementById('kodevhc').value = '';
						document.getElementById('rpvra').value = '';
						document.getElementById('jamperthn').value = '';
						document.getElementById('jamperthn').disabled = true;
						
						document.getElementById('rpbarang').value = dt[2];
						document.getElementById('rpbarang').value = dt[3];
						
						if(parseFloat(trim(dt[3]))>'0'){
							document.getElementById('rpbarang').disabled = true;
						}else{
							document.getElementById('rpbarang').disabled = false;
						}
					}
					if(jlhbarang == '' && jamperthn ==''){
						document.getElementById('jumlahbiaya').value = 0;
					}else{						
						document.getElementById('jumlahbiaya').value = parseFloat(dt[0])+parseFloat(dt[2]);
					}
					document.getElementById('jumlahbiaya').disabled = true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function hitungrupiah(){
	rpbarang = document.getElementById('rpbarang').value;
	jlhbarang = document.getElementById('jlhbarang').value;
	
	document.getElementById('jumlahbiaya').value = parseFloat(rpbarang)*parseFloat(jlhbarang);
}

function fillfield(id){
	param = 'method=fillfield' + '&id=' + id;
	tujuan = 'budget_slave_by_umum.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("##");
					document.getElementById('tahunbudget').value=data[0];
					document.getElementById('kodebudget').value=data[1];
					document.getElementById('kodeorg').value=data[2];
					document.getElementById('dept').value=data[3];
					document.getElementById('aruskas').value=data[4];
					document.getElementById('jenisbiaya').value=data[5];
					document.getElementById('kodevhc').value=data[6];
					document.getElementById('jamperthnold').value=data[7];
					
					$('#dept').val(trim(data[3])).trigger('change');
					//$('#aruskas').val(trim(data[4])).trigger('change');
					$('#jenisbiaya').val(trim(data[5])).trigger('change');
					if(data[6]!=''){
						$('#kodevhc').val(trim(data[6])).trigger('change');
						
						document.getElementById('jamperthn').disabled=false;
						document.getElementById('jamperthn').value=data[7];
						document.getElementById('rpvra').value=data[8]/data[7];
						document.getElementById('jumlahbiaya').disabled=true;
					}else{
						document.getElementById('jumlahbiaya').disabled=false;
						document.getElementById('jamperthn').disabled=true;
						$('#kodevhc').val(null).trigger('change');
					}
					document.getElementById('jumlahbiaya').value=data[8];
					document.getElementById('ketUmum').value=data[9];
					if(data[10]!=''){
						$('#kodevhc').val(null).trigger('change');
						document.getElementById('namabarang').value=data[11];
						document.getElementById('kodebarang').value=data[10];
						document.getElementById('jlhbarang').value=data[7];
						document.getElementById('rpbarang').value=data[8]/data[7];
						document.getElementById('jlhbarang').disabled=false;
						document.getElementById('jumlahbiaya').disabled=true;
					}else{
						document.getElementById('jumlahbiaya').disabled=false;
						document.getElementById('jlhbarang').disabled=true;
						document.getElementById('kodebarang').value='';
						document.getElementById('namabarang').value='';
					}
					
					document.getElementById('keluarmasuk').value=data[12];
					document.getElementById('idbgt').value=id;
					document.getElementById('method').value='editatas';
					document.getElementById('inputdata').style.display = 'block';
					
					getaruskas(trim(data[4]));
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
