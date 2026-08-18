function exportTableToExcel(tableID){
	var filename = tableID;
	
	var downloadLink;
	var dataType = 'application/vnd.ms-excel';
	var tableSelect = document.getElementById(tableID);
		tableSelect.border='1';
	var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

	filename = filename?filename+'.xls':'excel_data.xls';
	downloadLink = document.createElement("a");
	document.body.appendChild(downloadLink);

	if(navigator.msSaveOrOpenBlob){
		var blob = new Blob(['\ufeff', tableHTML], {
			type: dataType
		});
		navigator.msSaveOrOpenBlob( blob, filename);
	}else{
		downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
		downloadLink.download = filename;
		downloadLink.click();
	}
}

function unposting(tahunbudget,kodeorg,kodevhc) {
	param = 'proses=unposting' + '&kodevhc=' + kodevhc;
	param += '&tahunbudget=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_vhc.php';
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


function posting(tahunbudget,kodeorg,kodevhc) {
	param = 'proses=posting' + '&kodevhc=' + kodevhc;
	param += '&tahunbudget=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_vhc.php';
	if (confirm('Jika ada update biaya traksi maka rupiah yang sudah teralokasi akan direkalkulasi, Apakah anda yakin ??')) {
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

function editsdm(kunci,kodebudget,volume,jumlah,rupiah){
	document.getElementById('jmlh_1').value=jumlah;
	document.getElementById('jmlhk_1').value=volume;
	document.getElementById('kdBudget').value=kodebudget;
	document.getElementById('totBiaya').value=rupiah;
	document.getElementById('index').value=kunci;
	document.getElementById('proses').value='update';
}
function editmat(kunci,kodebudget,kodebarang,namabarang,satuanj,jumlah,rupiah){
	document.getElementById('kdBudgetM').value=kodebudget;
	setValue2('kdBudgetM',kodebudget);
	document.getElementById('kdBarang').value=kodebarang;
	document.getElementById('jmlh_2').value=jumlah;
	document.getElementById('totHarga').value=rupiah;
	document.getElementById('satuan').innerHTML=satuanj;
	document.getElementById('namaBrg').innerHTML=namabarang;
	document.getElementById('index').value=kunci;
	document.getElementById('proses').value='update';
}
function editsrv(kunci,kodebudget,kodews,jumlah,rupiah){
	document.getElementById('kdBudgetS').value=kodebudget;
	document.getElementById('kdWorkshop').value=kodews;
	document.getElementById('jmlh_3').value=jumlah;
	document.getElementById('totHargaJam').value=rupiah;
	document.getElementById('index').value=kunci;
	document.getElementById('proses').value='update';
}

function editoth(kunci,kodebudget,noakun,rupiah, kodebarang, jumlah){
	document.getElementById('kdBudgetB').value=kodebudget;
	document.getElementById('noAkun').value=noakun;
	document.getElementById('totBiayaB').value=rupiah;
	document.getElementById('kodebaranglain').value=kodebarang;
	document.getElementById('kuantitas').value=jumlah;
	document.getElementById('index').value=kunci;
	document.getElementById('proses').value='update';
}

function add_new_data(){
	document.getElementById('listDatHeader').style.display = 'none';
	document.getElementById('formIsian').style.display = 'block';
	document.getElementById('detailformIsian').style.display = 'block';
	
	document.getElementById('containDataSDM').innerHTML = '';
	document.getElementById('containDataBrg').innerHTML = '';
	document.getElementById('containDataSrvc').innerHTML = '';
	document.getElementById('containDataLain').innerHTML = '';
	document.getElementById('kdTraksi').disabled = false;
	document.getElementById('kodeVhc').disabled = false;
	// setValue2('kdTraksi',null);
	// setValue2('kodeVhc',null);
	
	clearSdm();
	clearMat();
	clearLain();	
	clearService();
}

function displayList() {
	document.getElementById('listDatHeader').style.display = 'block';
	document.getElementById('formIsian').style.display = 'none';
	document.getElementById('detailformIsian').style.display = 'none';
	loaddata(0);
}

function copybudgetall(maxRow, tahunbudget, kodevhcsumber, tipebudget) {
	if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Simpan semua ???")) {
		max = maxRow;
		copybudget(1, maxRow, tahunbudget, kodevhcsumber, tipebudget);
	}
}
function copybudget(currRow, maxRow, tahunbudget, kodevhcsumber, tipebudget) {
	vhctujuan= document.getElementById('kdvhccopy'+currRow).innerHTML;
	param = 'vhctujuan=' + vhctujuan + '&tahunbudget=' + tahunbudget + '&kodevhcsumber=' + kodevhcsumber + '&tipebudget=' + tipebudget + '&proses=copyblok';
	tujuan = 'budget_slave_vhc.php';
	
	document.getElementById('row'+currRow).style.backgroundColor='cyan';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
				} else {
					if (currRow != undefined) {
						document.getElementById('row' + currRow).style.display = 'none';
					}
					currRow += 1;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						loaddata();
						alertify.alert("Data sudah di copy, silahkan click tombol Refresh");
					} else {
						copybudget(currRow, maxRow, tahunbudget, kodevhcsumber, tipebudget);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function viewOtherBlok(tahunbudget, kodeVhc, tipebudget, noakun, kodeorg, volume, satuanvolume, rotasi, ev,sts) {
	// width = '720';
	// height = '415';
	// content = "<fieldset><div id=containerd style=\"width:700px;height:400px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "Copy";
	// showDialog5(title, content, width, height, ev);
	
	param = 'tahunbudget=' + tahunbudget + '&kodeVhc=' + kodeVhc + '&tipebudget=' + tipebudget + '&noakun=' + noakun + '&kodeorg=' + kodeorg + '&volume=' + volume + '&satuanvolume=' + satuanvolume + '&rotasi=' + rotasi + '&proses=form_otherblok';
	
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Copy",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','70%');
					isiviewOtherBlok(tahunbudget, kodeVhc, tipebudget, noakun, kodeorg, volume, satuanvolume, rotasi, ev,sts);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function isiviewOtherBlok(tahunbudget, kodeVhc, tipebudget, noakun, kodeorg, volume, satuanvolume, rotasi, ev,sts) {
	param = 'tahunbudget=' + tahunbudget + '&kodeVhc=' + kodeVhc + '&tipebudget=' + tipebudget + '&noakun=' + noakun + '&kodeorg=' + kodeorg + '&volume=' + volume + '&satuanvolume=' + satuanvolume + '&rotasi=' + rotasi + '&proses=otherblok';
	jeniskend = document.getElementById('ttcopyallblok').value;
	param += '&jeniskend=' + jeniskend;
	
	if(sts=='filter'){
		traksi = document.getElementById('divisicopyallblok').value;
		param += '&traksi=' + traksi;
	}
	
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerdx').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getKdvhc(kdtrk, kdvhc) {
	if ((kdtrk == '') && (kdvhc == '')) {
		kdTraksi = document.getElementById('kdTraksi').value;
		param = 'kdTraksi=' + kdTraksi + '&proses=getVhc';
	} else {
		kdTraksi = kdtrk;
		kdVhc = kdvhc;
		param = 'kdTraksi=' + kdTraksi + '&proses=getVhc' + '&kdVhc=' + kdVhc;
	}
	
	thnBdget  = document.getElementById('thnBudget').value;
	param += '&thnBdget=' + thnBdget;
	
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('kodeVhc').innerHTML = '';
					document.getElementById('kodeVhc').innerHTML = con.responseText;
					if ((kdtrk != '') && (kdvhc != '')) {
						saveData();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function gethargabaranglain() {
	thnBdget  = document.getElementById('thnBudget').value;
	kdOrg     = document.getElementById('kdTraksi').value;
	kdVhc     = document.getElementById('kodeVhc').value;
	tipeBudget= document.getElementById('tipeBudget').value;
	kodebarang= document.getElementById('kodebaranglain').value;
	
	param = 'thnBudget=' + thnBdget + '&kdOrg=' + kdOrg + '&proses=gethargabaranglain' + '&kdVhc=' + kdVhc + '&tipeBudget=' + tipeBudget+ '&kodebarang=' + kodebarang;
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(kodebarang!=''){
						document.getElementById('kuantitas').disabled=false;
					}else{
						document.getElementById('kuantitas').disabled=true;
					}
					
					jumlah = document.getElementById('kuantitas').value;
					if(con.responseText!=''){
						rupiah = parseFloat(con.responseText)*parseFloat(jumlah);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveData() {
	thnBdget  = document.getElementById('thnBudget').value;
	kdOrg     = document.getElementById('kdTraksi').value;
	kdVhc     = document.getElementById('kodeVhc').value;
	tipeBudget= document.getElementById('tipeBudget').value;
	
	param = 'thnBudget=' + thnBdget + '&kdOrg=' + kdOrg + '&proses=cekSave' + '&kdVhc=' + kdVhc + '&tipeBudget=' + tipeBudget;
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					ar = con.responseText.split("###");
					document.getElementById('hkEfektif').value = ar[0];
					document.getElementById('kdWorkshop').innerHTML = ar[1];
					document.getElementById('kdBudget').innerHTML = ar[2];
					document.getElementById('kodebaranglain').innerHTML = ar[3];
					document.getElementById('thnBudget').disabled = true;
					document.getElementById('kdTraksi').disabled = true;
					document.getElementById('kodeVhc').disabled = true;
					document.getElementById('saveData').disabled = true;
					document.getElementById('formIsian').style.display = 'block';
					document.getElementById('listDatHeader').style.display = 'none';
					loadDataSdm(1);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function jumlahkan(x) {
	thnBdget= document.getElementById('thnBudget').value;
	kdOrg   = document.getElementById('kdTraksi').value;
	param = 'thnBudget=' + thnBdget + '&kdOrg=' + kdOrg + '&kdVhc=' + kdVhc;
	if (x == 1) {
		personel = document.getElementById('jmlh_' + x).value;
		hkEfektip= document.getElementById('hkEfektif').value;
		kdGol    = document.getElementById('kdBudget').value;
		param += '&proses=getUpah' + '&jmlhPerson=' + personel + '&kdGol=' + kdGol + '&hkEfektif=' + hkEfektip;
	}
	if (x == 2) {
		kdBudget= document.getElementById('kdBudgetM').value;
		kdBrg   = document.getElementById('kdBarang').value;
		jmlhBrg = document.getElementById('jmlh_' + x).value;
		param += '&kdBudget=' + kdBudget + '&kdBrg=' + kdBrg + '&jmlhBrg=' + jmlhBrg + '&proses=getHarga';
	}
	if (x == 3) {
		kdBudgetS = document.getElementById('kdBudgetS').value;
		kdWorkshop= document.getElementById('kdWorkshop').value;
		jmlhJam   = document.getElementById('jmlh_' + x).value;
		param += '&kdBudgetS=' + kdBudgetS + '&kdWorkshop=' + kdWorkshop + '&jmlhJam=' + jmlhJam + '&proses=getBiayaService';
	}
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (x == 1) {
						document.getElementById('totBiaya').value = con.responseText;
						hk = parseFloat(personel)*parseFloat(hkEfektip);
						if(isNaN(hk)){hk=0;}
						document.getElementById('jmlhk_1').value = numberFormat(hk);
					}
					if (x == 2) {
						document.getElementById('totHarga').value = con.responseText;
					}
					if (x == 3) {
						dtFloat = trim(con.responseText);
						document.getElementById('totHargaJam').value = dtFloat;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveBudget(x) {
	thnBdget  = document.getElementById('thnBudget').value;
	kdOrg     = document.getElementById('kdTraksi').value;
	tipeBudget= document.getElementById('tipeBudget').value;
	kdVhc     = document.getElementById('kodeVhc').value;
	param = 'thnBudget=' + thnBdget + '&kdOrg=' + kdOrg + '&kdVhc=' + kdVhc + '&tipeBudget=' + tipeBudget;
	if (x == 1) {
		personel = document.getElementById('jmlh_' + x).value;
		hkEfektip= document.getElementById('hkEfektif').value;
		kdGol    = document.getElementById('kdBudget').value;
		totBiaya = document.getElementById('totBiaya').value;
		totBiaya = remove_comma_var(totBiaya);
		param += '&proses=saveSdm' + '&jmlhPerson=' + personel + '&kdGol=' + kdGol + '&hkEfektif=' + hkEfektip + '&totBiaya=' + totBiaya;
	}
	if (x == 2) {
		kdBudget = document.getElementById('kdBudgetM').value;
		kdBrg    = document.getElementById('kdBarang').value;
		jmlhBrg  = document.getElementById('jmlh_' + x).value;
		totHarga = document.getElementById('totHarga').value;
		satuanBrg= document.getElementById('satuan').innerHTML;
		totHarga = remove_comma_var(totHarga);
		param += '&kdBudget=' + kdBudget + '&kdBrg=' + kdBrg + '&jmlhBrg=' + jmlhBrg + '&totHarga=' + totHarga + '&proses=saveMat' + '&satuanBrg=' + satuanBrg;
	}
	if (x == 3) {
		kdBudgetS  = document.getElementById('kdBudgetS').value;
		kdWorkshop = document.getElementById('kdWorkshop').value;
		jmlhJam    = document.getElementById('jmlh_' + x).value;
		totHargaJam= document.getElementById('totHargaJam').value;
		totHargaJam = remove_comma_var(totHargaJam);
		param += '&kdBudgetS=' + kdBudgetS + '&kdWorkshop=' + kdWorkshop + '&jmlhJam=' + jmlhJam + '&proses=saveService' + '&totHargaJam=' + totHargaJam;
	}
	if (x == 4) {
		kdBudgetB= document.getElementById('kdBudgetB').value;
		noAkun   = document.getElementById('noAkun').value;
		totBiayaB= document.getElementById('totBiayaB').value;
		totBiayaB= remove_comma_var(totBiayaB);
		kodebarang= document.getElementById('kodebaranglain').value;
		kuantitas= document.getElementById('kuantitas').value;
		param += '&kdBudgetB=' + kdBudgetB + '&noAkun=' + noAkun + '&totBiayaB=' + totBiayaB + '&proses=saveLain';
		param += '&kodebarang=' + kodebarang + '&kuantitas=' + kuantitas;
	}
	
	param += '&method=' + getValue('proses');
	param += '&index=' + getValue('index');
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (x == 1) {
						clearSdm();
						loadDataSdm();
					}else if (x == 2) {
						clearMat();
						loadDtMaterail();
					}else if (x == 4) {
						clearLain();
						loadDtLain();
					}else if (x == 3) {
						clearService();
						loadDtService();
					}else{
						loaddatattlbiaya();						
					}
					document.getElementById('index').value = '';
					document.getElementById('proses').value = '';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatattlbiaya(e,tipebudget,tahunbudget,kodeorg,kodevhc,tipe){
	if(tipe=='popup'){
		thnBudget = tahunbudget;
		kdTraksi  = kodeorg;
		tipeBudget= tipebudget;
		kodeVhc   = kodevhc;
	}else{		
		tipeBudget= document.getElementById('tipeBudget').value;
		thnBudget = document.getElementById('thnBudget').value;
		kdTraksi  = document.getElementById('kdTraksi').value;
		kodeVhc   = document.getElementById('kodeVhc').value;
	}
	
	param = 'tipeBudget=' + tipeBudget + '&proses=loaddatattlbiaya';
	param += '&thnBudget=' + thnBudget;
	param += '&kdTraksi=' + kdTraksi;
	param += '&kodeVhc=' + kodeVhc;
	param += '&jenis=' + tipe;
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='popup'){
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					if(document.getElementById('containerttlbiaya')!=undefined){						
						document.getElementById('containerttlbiaya').innerHTML = con.responseText;
					}
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function clearSdm() {
	document.getElementById('jmlh_1').value = '';
	document.getElementById('jmlhk_1').value = '';
	document.getElementById('kdBudget').value = '';
	document.getElementById('totBiaya').value = '0';
}
function getKlmpkbrg(e) {
	klmpkBrg = document.getElementById('kdBudgetM').value;
	e = document.getElementById('kdBarang').value;
	if(e.length<'9'){		
		document.getElementById('kdBarang').value = klmpkBrg.substr(2,3);
	}
	
	if(e.length>='9' && klmpkBrg.substr(2,3)!=e.substr(0,3)){
		document.getElementById('kdBarang').value = klmpkBrg.substr(2,3);
	}

	if(klmpkBrg.substr(2,1)=='8'){
		document.getElementById('totHarga').onkeypress=null;
	}
}
function clearMat() {
	document.getElementById('jmlh_2').value = '';
	document.getElementById('namaBrg').innerHTML = '';
	document.getElementById('kdBarang').value = document.getElementById('kdBudgetM').value.substr(2,3);
	//document.getElementById('kdBudgetM').value = '';
	document.getElementById('totHarga').value = '0';
	document.getElementById('satuan').innerHTML = '';
}
function clearLain() {
	document.getElementById('kodebaranglain').value = '';
	document.getElementById('kuantitas').value = '';
	document.getElementById('noAkun').value = '';
	document.getElementById('totBiayaB').value = '0';
}
function clearService() {
	document.getElementById('jmlh_3').value = '';
	//document.getElementById('kdWorkshop').value = '';
	//document.getElementById('kdBudgetS').value = '';
	document.getElementById('totHargaJam').value = '0';
}
function newData() {
	document.getElementById('thnBudget').disabled = false;
	document.getElementById('kdTraksi').disabled = false;
	document.getElementById('kodeVhc').disabled = false;
	document.getElementById('saveData').disabled = false;
	// setValue2('kdTraksi',null);
	// setValue2('kodeVhc',null);
	document.getElementById('containDataSDM').innerHTML = "";
	document.getElementById('containDataBrg').innerHTML = "";
	document.getElementById('containDataSrvc').innerHTML = "";
	document.getElementById('containDataLain').innerHTML = "";
	document.getElementById('thnBudgetTutup').innerHTML = "";

}
function deleteSdm(id, hal) {
	param = 'idData=' + id + '&proses=delData';
	tujuan = 'budget_slave_vhc.php';
	if (confirm("Delete, are you sure?")) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (hal == 1) {
						loadDataSdm();
					}
					if (hal == 2) {
						loadDtMaterail();
					}
					if (hal == 4) {
						loadDtLain();
					}
					if (hal == 3) {
						loadDtService();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function searchBrg(title, content, ev) {
	klmpk = document.getElementById('kdBudgetM').value;
	idKlmpk = "<input type='hidden' id='idKlmpk' value='" + klmpk + "' />"
	content = content + idKlmpk;
	width = '500';
	height = '400';
	//showDialog1(title, content, width, height, ev);
	
	alertify.popup("Find",content).set({'resizable':true,'maximizable':true}).resizeTo('50%','70%');
	findBrg();
}
function findBrg() {
	klmpkBrg = document.getElementById('idKlmpk').value;
	nmBrg    = document.getElementById('nmBrg').value;
	kdBarang = document.getElementById('kdBarang').value;
	thnBudget= document.getElementById('thnBudget').value;
	kdTraksi = document.getElementById('kdTraksi').value;
	
	param = 'klmpkBrg=' + klmpkBrg + '&nmBrg=' + nmBrg + '&proses=getBarang';
	param += '&thnBudget=' + thnBudget;
	param += '&kdTraksi=' + kdTraksi;
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerBarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function setData(kdbrg, namaBarang, sat, hargaSatuan){
	klmpk="M-"+kdbrg.substr(0,3);
	der=document.getElementById('kdBudgetM');
	for (a = 0; a < der.length; a++) {
		if (der.options[a].value == klmpk) {
			der.options[a].selected = true;
		}
	}
	document.getElementById('kdBarang').value = kdbrg;
	document.getElementById('namaBrg').innerHTML = namaBarang;
	document.getElementById('satuan').innerHTML = sat;
	document.getElementById('hargasatuan_2').value = hargaSatuan;
	// closeDialog();
	alertify.popup().destroy();
}

function form() {
	width = '';
	height = '';
	content = "<fieldset><div id=contpreview align=center style=\"min-width:700px;max-height:500px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
}

function getdatadetail(tipe,tipebudget,tahunbudget,kodeorg,kodevhc){	
	//form();
	if(tipe=='sdm'){
		loadDataSdm('',tipebudget,tahunbudget,kodeorg,kodevhc,'popup');
	}
	if(tipe=='mat'){
		loadDtMaterail('',tipebudget,tahunbudget,kodeorg,kodevhc,'popup');
	}
	if(tipe=='srv'){
		loadDtService('',tipebudget,tahunbudget,kodeorg,kodevhc,'popup');
	}
	if(tipe=='oth'){
		loadDtLain('',tipebudget,tahunbudget,kodeorg,kodevhc,'popup');
	}
	if(tipe=='rkp'){
		loaddatattlbiaya('',tipebudget,tahunbudget,kodeorg,kodevhc,'popup');
	}
}

function loadDataSdm(b,tipebudget,tahunbudget,kodeorg,kodevhc,tipe) {
	if(tipe=='popup'){
		thnBdget  = tahunbudget;
		kdOrg     = kodeorg;
		tipeBudget= tipebudget;
		kdVhc     = kodevhc;
	}else{		
		thnBdget  = document.getElementById('thnBudget').value;
		kdOrg     = document.getElementById('kdTraksi').value;
		tipeBudget= document.getElementById('tipeBudget').value;
		kdVhc     = document.getElementById('kodeVhc').value;
	}
	
	param = 'thnBudget=' + thnBdget + '&kdOrg=' + kdOrg + '&kdVhc=' + kdVhc + '&tipeBudget=' + tipeBudget + '&proses=loadDataSdm';
	param += '&jenis='+tipe;
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='popup'){
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					if(document.getElementById('containDataSDM')!=undefined){						
						document.getElementById('containDataSDM').innerHTML = con.responseText;
					}
					
					// leftFixedTable();
					if (b == 1) {
						loadDtMaterail(b);
					} else {
						if(tipe!='popup'){							
							loaddatattlbiaya();
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadDtMaterail(c,tipebudget,tahunbudget,kodeorg,kodevhc,tipe) {
	if(tipe=='popup'){
		thnBdget  = tahunbudget;
		kdOrg     = kodeorg;
		tipeBudget= tipebudget;
		kdVhc     = kodevhc;
	}else{	
		thnBdget  = document.getElementById('thnBudget').value;
		kdOrg     = document.getElementById('kdTraksi').value;
		tipeBudget= document.getElementById('tipeBudget').value;
		kdVhc     = document.getElementById('kodeVhc').value;
	}
	param = 'thnBudget=' + thnBdget + '&kdOrg=' + kdOrg + '&kdVhc=' + kdVhc + '&tipeBudget=' + tipeBudget + '&kdVhc=' + kdVhc;
	param += '&proses=loadDataMat';
	param += '&jenis='+tipe;
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='popup'){
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					if(document.getElementById('containDataBrg')!=undefined){						
						document.getElementById('containDataBrg').innerHTML = con.responseText;
					}
					
					leftFixedTable();
					
					if (c == 1) {
						loadDtService(c);
					} else {
						if(tipe!='popup'){							
							loaddatattlbiaya();
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadDtService(d,tipebudget,tahunbudget,kodeorg,kodevhc,tipe) {
	if(tipe=='popup'){
		thnBdget  = tahunbudget;
		kdOrg     = kodeorg;
		tipeBudget= tipebudget;
		kdVhc     = kodevhc;
	}else{		
		thnBdget  = document.getElementById('thnBudget').value;
		kdOrg     = document.getElementById('kdTraksi').value;
		tipeBudget= document.getElementById('tipeBudget').value;
		kdVhc     = document.getElementById('kodeVhc').value;
	}
	param = 'thnBudget=' + thnBdget + '&kdOrg=' + kdOrg + '&kdVhc=' + kdVhc + '&tipeBudget=' + tipeBudget + '&kdVhc=' + kdVhc;
	param += '&proses=loadDtService';
	param += '&jenis='+tipe;
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='popup'){
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					if(document.getElementById('containDataSrvc')!=undefined){						
						document.getElementById('containDataSrvc').innerHTML = con.responseText;
					}
					
					// leftFixedTable();
					
					if (d == 1) {
						loadDtLain();
					} else {
						if(tipe!='popup'){							
							loaddatattlbiaya();
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadDtLain(e,tipebudget,tahunbudget,kodeorg,kodevhc,tipe) {
	if(tipe=='popup'){
		thnBdget  = tahunbudget;
		kdOrg     = kodeorg;
		tipeBudget= tipebudget;
		kdVhc     = kodevhc;
	}else{	
		thnBdget  = document.getElementById('thnBudget').value;
		kdOrg     = document.getElementById('kdTraksi').value;
		tipeBudget= document.getElementById('tipeBudget').value;
		kdVhc     = document.getElementById('kodeVhc').value;
	}
	param = 'thnBudget=' + thnBdget + '&kdOrg=' + kdOrg + '&kdVhc=' + kdVhc + '&tipeBudget=' + tipeBudget + '&kdVhc=' + kdVhc;
	param += '&proses=loadDtLain';
	param += '&jenis='+tipe;
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='popup'){
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					if(document.getElementById('containDataLain')!=undefined){						
						document.getElementById('containDataLain').innerHTML = con.responseText;
					}
					// leftFixedTable();
					if(tipe!='popup'){							
						loaddatattlbiaya();
					}
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
	param = 'proses=loaddata';
	thnbgt= document.getElementById('thnBudgetHead').value;
	kdVhc = document.getElementById('kdVhcHead').value;
	kodeorg = document.getElementById('kodeorgsch').value;
	kodetrk = document.getElementById('kodewssch').value;
	param += '&kodeorg=' + kodeorg;
	param += '&kodetrk=' + kodetrk;

	if (thnbgt != '') {
		param += '&thnBudget=' + thnbgt;
	}
	if (kdVhc != '') {
		param += '&kdVhc=' + kdVhc;
	}
	param += '&page=' + page;
	tujuan = 'budget_slave_vhc.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('listDatHeader').style.display = 'block';
					document.getElementById('formIsian').style.display = 'none';
					isdt = con.responseText.split("####");
					document.getElementById('listDatHeader2').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}


function filFieldHead(thnbdget, kdtrk, kdvhc) {
	document.getElementById('thnBudget').value = thnbdget;
	document.getElementById('kdTraksi').value = kdtrk;
	document.getElementById('listDatHeader').style.display = 'none';
	document.getElementById('formIsian').style.display = 'block';
	document.getElementById('detailformIsian').style.display = 'block';
	
	document.getElementById('containDataSDM').innerHTML = '';
	document.getElementById('containDataBrg').innerHTML = '';
	document.getElementById('containDataSrvc').innerHTML = '';
	document.getElementById('containDataLain').innerHTML = '';
	
	setValue2('kdTraksi',kdtrk);
	setValue2('kodeVhc',kdvhc);
	
	clearSdm();
	clearMat();
	clearLain();	
	clearService();
	
	
	getKdvhc(kdtrk, kdvhc);
}

function hapushead(thnbdget, kdtrk, kdVhc){
    param = 'proses=hapushead';
    param += '&thnbdget=' + thnbdget;
    param += '&kdtrk=' + kdtrk;
    param += '&kdVhc=' + kdVhc;
    tujuan = 'budget_slave_vhc.php';
	if(confirm('Anda Yakin ???')){
		post_response_text(tujuan, param, respog);
	}
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
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

function reloadframe(){
	window.location.reload();
}

function showformupload(ev) {
	ev = 'event';
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;'></div></fieldset>";
	showDialog2(title, content, width, height, ev);
}

function showupload(ev){
	tahun  = document.getElementById('thnBudget').value;
	kodeorg= document.getElementById('kdTraksi').value;
	tipebgt= document.getElementById('tipeBudget').value;
	kodevhc= document.getElementById('kodeVhc').value;
	if(tahun==''){
		alertify.alert("Tahun wajib diisi."); return;
	}
	if(kodeorg==''){
		alertify.alert("Kode traksi wajib diisi."); return;
	}
	
	// showformupload(ev);
	param  = 'proses=showupload';
	param += '&tahun=' + tahun;
	param += '&kodevhc=' + kodevhc;
	tujuan = 'budget_slave_vhc.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    // document.getElementById('contUpload').innerHTML=con.responseText;
					alertify.popup().destroy();
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}


function fileSelected(jenis){
	tahun  = document.getElementById('thnBudget').value;
	kodeorg= document.getElementById('kdTraksi').value;
	tipebgt= document.getElementById('tipeBudget').value;
	kodevhc= document.getElementById('kodeVhc').value;
	
	var file = document.getElementById('upload').files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("tahun", tahun);
	formdata.append("kodeorg", kodeorg);
	formdata.append("tipebgt", tipebgt);
	formdata.append("kodevhc", kodevhc);
	formdata.append("jenis", jenis);
	
	if(jenis=='simpan'){
		if(confirm("Hanya barang yang memiliki harga yg akan disimpan.")){			
			busy_on();
			var con = createXMLHttpRequest();
			con.open("POST", "budget_slave_vhc.php?proses=fileSelected", true);
			con.onreadystatechange = eval(respon);
			con.send(formdata);
		}
	}else{
		busy_on();
		var con = createXMLHttpRequest();
		con.open("POST", "budget_slave_vhc.php?proses=fileSelected", true);
		con.onreadystatechange = eval(respon);
		con.send(formdata);
	}
	
    
    function respon(){
        if (con.readyState == 4){
			if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
					if(jenis=='simpan'){
						// closeDialog2();
						alertify.popup().destroy();
						loadDtMaterail();
						alertify.alert("Done");
					}else{						
						document.getElementById('listfiles').innerHTML=con.responseText;
						//leftFixedTable();
					}
                }
            }else{
				busy_off();
                error_catch(con.status);
            }
        }
    }
}

function downloadmaster(){
	tahun  = document.getElementById('thnBudget').value;
	kodeorg= document.getElementById('kdTraksi').value;
	tipebgt= document.getElementById('tipeBudget').value;
	kodevhc= document.getElementById('kodeVhc').value;
	
	param  = "proses=downloadmaster&tahun="+tahun+"&kodeorg="+kodeorg+"&tipebgt="+tipebgt;
	param += '&kodevhc=' + kodevhc;
	ev   = 'event';
	title="Master Data";
	showDialog1(title,"<iframe frameborder=0 style='width:890px;min-height:400px'"+"src='budget_slave_vhc.php?"+param+"'></iframe>",'900','400',ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}
