/**
 * @author repindra.ginting
 */
function getSubKlBarang() {
	mayor = document.getElementById('kelompokbarang').options[document.getElementById('kelompokbarang').selectedIndex].value;
	param = 'mayor=' + mayor + '&method=getSubKlBarang';
	tujuan = 'log_slave_get_material_number.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('caption').innerHTML = document.getElementById('kelompokbarang').options[document.getElementById('kelompokbarang').selectedIndex].text;
	ser = document.getElementById('optcari');
	for (g = 0; g < ser.length; g++) {
		if (ser.options[g].value == mayor) {
			ser.options[g].selected = true;
		}
	}
	ser = document.getElementById('kelompokbarang');
	for (g = 0; g < ser.length; g++) {
		if (ser.options[g].value == mayor) {
			ser.options[g].selected = true;
		}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('subkelompokbarang').disabled = false;
					document.getElementById('subkelompokbarang').innerHTML = con.responseText;
					document.getElementById('kodebarang').value = '';
					getMaterialMember(mayor);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getSubKlBarang2() {
	mayor = document.getElementById('optcari').options[document.getElementById('optcari').selectedIndex].value;
	param = 'mayor=' + mayor + '&method=getSubKlBarang';
	tujuan = 'log_slave_get_material_number.php';
	post_response_text(tujuan, param, respog);
	ser = document.getElementById('optcari');
	for (g = 0; g < ser.length; g++) {
		if (ser.options[g].value == mayor) {
			ser.options[g].selected = true;
		}
	}
	ser = document.getElementById('kelompokbarang');
	for (g = 0; g < ser.length; g++) {
		if (ser.options[g].value == mayor) {
			ser.options[g].selected = true;
		}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('subkelompokbarangcr').disabled = false;
					document.getElementById('subkelompokbarangcr').innerHTML = con.responseText;
					cariBarang();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getMaterialNumber() {
	kelompokbarang = document.getElementById('kelompokbarang').options[document.getElementById('kelompokbarang').selectedIndex].value;
	subkelompokbarang = document.getElementById('subkelompokbarang').options[document.getElementById('subkelompokbarang').selectedIndex].value;
	param = 'mayor=' + kelompokbarang + '&subkelompokbarang=' + subkelompokbarang + '&method=getKodeMaterial';
	tujuan = 'log_slave_get_material_number.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kodebarang').value = con.responseText;
					// getMaterialMember(mayor);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getMaterialMember(mayor) {
	param = 'mayor=' + mayor;
	tujuan = 'log_slave_get_material_member.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('caption').innerHTML = document.getElementById('kelompokbarang').options[document.getElementById('kelompokbarang').selectedIndex].text;
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	document.getElementById('method').value = 'insert';
}
function fillField(kelompokbarang, subkelompokbarang, kodebarang, namabarang, satuan, minstok, nokartubin, konversi, inisial, jenis, statusbarang,ongkir,idkategori,kodevhc) {
	showontop();
	kel = document.getElementById('kelompokbarang');
	for (g = 0; g < kel.length; g++) {
		if (kel.options[g].value == kelompokbarang) {
			kel.options[g].selected = true;
		}
	}
	subkel = document.getElementById('subkelompokbarang');
	for (g = 0; g < subkel.length; g++) {
		if (subkel.options[g].value == subkelompokbarang) {
			subkel.options[g].selected = true;
		}
	}
	document.getElementById("kelompokbarang").disabled = true;
	document.getElementById("subkelompokbarang").disabled = true;
	document.getElementById('kodebarang').value = kodebarang;
	sat = document.getElementById('satuan');
	for (g = 0; g < sat.length; g++) {
		if (sat.options[g].value == satuan) {
			sat.options[g].selected = true;
		}
	}
	document.getElementById('statusbarang').value = statusbarang;
	document.getElementById('namabarang').value = namabarang;
	document.getElementById('minstok').value = minstok;
	document.getElementById('nokartu').value = nokartubin;
	if (konversi == '1') {
		document.getElementById('konversi').options[0].selected = true;
	} else {
		document.getElementById('konversi').options[1].selected = true;
	}
	document.getElementById('method').value = 'update';
	document.getElementById('inisial').value = inisial;
	document.getElementById('jenis').value = jenis;
	document.getElementById('kategoriBarang').value = idkategori;
	document.getElementById('kodevhc').value = kodevhc;
	createqrcode(kodebarang);
	
	document.getElementById('ongkir').value = ongkir;
	// if(ongkir == 1) {
	// 	document.getElementById('ongkir').checked = true;
	// } else {
	// 	document.getElementById('ongkir').checked = false;
	// }
	showontop();
	getseasion(kodebarang);
}

function getseasion(kodebarang){
	param='method=getseasion&kodebarang='+kodebarang;
	tujuan = 'log_slave_get_material_number.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					// alert(con.responseText);
					alertify.alert('Informasi',con.responseText);
				}else{
					loadhargasatuan();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function delBarang(kodebarang, mayor) {
	tujuan = 'log_slave_get_material_member.php';
	param = 'kodebarang=' + kodebarang + '&mayor=' + mayor + '&method=delete';
	if (confirm('Deleting ' + kodebarang + ' .., Are you sure..?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					cancelBarang();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function createqrcode(kodebarang) {
	tujuan = 'log_slave_get_material_member.php';
	param = 'kodebarang='+kodebarang+'&method=createqrcode';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// cancelBarang();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancelBarang() {
	document.getElementById("kelompokbarang").selectedIndex = "0";
	document.getElementById("subkelompokbarang").selectedIndex = "0";
	// document.getElementById("subkelompokbarang").innerHTML = "<option value=''>-</option>";
	document.getElementById("kelompokbarang").disabled = false;
	document.getElementById("subkelompokbarang").disabled = false;
	document.getElementById('kodebarang').value = '';
	document.getElementById('namabarang').value = '';
	document.getElementById("satuan").selectedIndex = "0";
	document.getElementById("statusbarang").selectedIndex = "0";
	document.getElementById('minstok').value = '0';
	document.getElementById('nokartu').value = '';
	document.getElementById("konversi").selectedIndex = "0";
	document.getElementById('method').value = 'insert';
	document.getElementById('inisial').value = '';
	document.getElementById('jenis').value = '';
	document.getElementById('ongkir').checked = false;
	// getMaterialNumber();
	//get current number
	// kl		=document.getElementById('kelompokbarang');
	// kl		=trim(kl.options[kl.selectedIndex].value);
	// getMaterialNumber(kl);
	
	clearseasion();
}
function simpanBarangBaru() {
	tujuan = 'log_slave_get_material_member.php';
	kl = document.getElementById('kelompokbarang');
	kl = trim(kl.options[kl.selectedIndex].value);
	method = document.getElementById('method').value;
	kdbarang = trim(document.getElementById('kodebarang').value);
	nmbrg = trim(document.getElementById('namabarang').value);
	minstok = document.getElementById('minstok').value;
	nokartu = document.getElementById('nokartu').value;
	konversi = document.getElementById('konversi');
	konversi = konversi.options[konversi.selectedIndex].value;
	satuan = document.getElementById('satuan');
	satuan = satuan.options[satuan.selectedIndex].value;
	statusbarang = document.getElementById('statusbarang');
	statusbarang = statusbarang.options[statusbarang.selectedIndex].value;
	inisial = trim(document.getElementById('inisial').value);
	jenis = document.getElementById('jenis').value;
	kodevhc = document.getElementById('kodevhc').value;
	ongkir = 0;
	
	kategori = document.getElementById('kategoriBarang').value;

	// if(document.getElementById('ongkir').checked) {
	// 	ongkir = 1;
	// }
	ongkir = document.getElementById('ongkir');
	ongkir = ongkir.options[ongkir.selectedIndex].value;
	
	param = 'mayor=' + kl + '&kodebarang=' + kdbarang + '&namabarang=' + nmbrg;
	param += '&satuan=' + satuan + '&minstok=' + minstok + '&konversi=' + konversi;
	param += '&nokartu=' + nokartu + '&method=' + method;
	param += '&inisial=' + inisial + '&jenis=' + jenis + '&kodevhc=' + kodevhc;
	param += '&statusbarang=' + statusbarang + '&ongkir=' + ongkir + '&kategoribarang=' + kategori;
	trapproval=document.getElementById('trapproval').innerHTML;
	
	if(trapproval=='')
	{
		alert("Please contact administrator to setup Approval.");
		return;
	}
	
	var tbl = document.getElementById("trapproval");
	var row = parseFloat(tbl.rows.length)+1;
	strUrl = '';
	for(i=1;i<row;i++)
	{
		if(document.getElementById('persetujuan'+i).innerHTML=='')
		{
			alert("Please contact administrator to setup Approval.");
			return false;
		}
		persetujuan = document.getElementById('persetujuan'+i).options[document.getElementById('persetujuan'+i).selectedIndex].value;
		if(persetujuan=='')
		{
			alert("Please compelete Approval");
			return;
		}
		strUrl += '&persetujuan['+i+']='+persetujuan;
	}
	param += strUrl;
	
	if (confirm('Saving/Simpan ' + kdbarang + ' .., Are you sure..?')) {
		if (nmbrg == '' || kl == '' || kdbarang == '')
			alert('Material group/code/name is obligatory');
		else
			post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					alert('Done');
					cancelBarang();
					// increaseKodeBarang(kdbarang);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function increaseKodeBarang(kdbarang) {
	x = parseInt(kdbarang);
	x = x + 1;
	if (x < 10)
		x = '0000' + x;
	else if (x < 100)
		x = '000' + x;
	else if (x < 1000)
		x = '00' + x;
	else if (x < 10000)
		x = '0' + x;
	document.getElementById('kodebarang').value = x;
}
function cariBarang() {
	tujuan = 'log_slave_get_material_member.php';
	txtcari = document.getElementById('txtcari').value;
	ongroup = document.getElementById('optcari');
	mayor = ongroup.options[ongroup.selectedIndex].value;
	subkelompokbarangcr = document.getElementById('subkelompokbarangcr');
	subkelompokbarangcr = subkelompokbarangcr.options[subkelompokbarangcr.selectedIndex].value;
	jenissch = document.getElementById('jenissch').value;
	
	param = 'txtcari=' + txtcari + '&mayor=' + mayor + '&jenissch=' + jenissch+'&subkelompokbarangcr='+subkelompokbarangcr;
	param += '&kodebarang=' + getValue('txtcarikode');
	param += '&persetujuan=' + getValue('optcaripersetujuan');
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					//cancelBarang();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function masterbarangPDF(ev) {
	//ambil kelompok barang dari pilihan pada form
	klbarang = document.getElementById('kelompokbarang');
	klbarang = trim(klbarang.options[klbarang.selectedIndex].value);
	if (klbarang == '')
		alert('Choose material group');
	else {
		//nilai parameter
		namatable = 'log_5masterbarang';
		kondisi = 'kelompokbarang=\'' + klbarang + '\'';
		kolom = 'kelompokbarang,kodebarang,namabarang,satuan';
		//=========================
		param = 'table=' + namatable + '&kondisi=' + kondisi + '&kolom=' + kolom + '&klbarang=' + klbarang;
		content = "<iframe src=\"log_slave_5masterbarang_pdf.php?" + param + "\" style='width:498px;height:398px;'></iframe>";
		showDialog1("MASTER BARANG", content, '500', '400', ev);
	}
}
nameV = 'winbarang';
x = 0;
function editDetailbarang(kodebarang, ev) {
	x += 1;
	nx = nameV + x;
	tujuan = 'log_slave_edit_material_detail.php?kodebarang=' + kodebarang;
	content = "<iframe name=" + nx + " src=" + tujuan + " frameborder=0 width=590px height=490px></iframe>";
	// showDialog1("Edit Detail Barang:" + kodebarang, content, '700', '400', ev);
	alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':false,'message':content}).resizeTo('70%','70%').show();
}
function simpanPhoto() {
	nx = nameV + x;
	spec = eval(nx + ".document.getElementById('spec').value");
	kodebarang = eval(nx + ".document.getElementById('kodebarangx').value");
	kl = document.getElementById('kelompokbarang');
	kl = trim(kl.options[kl.selectedIndex].value);
	if (spec == '') {
		if (confirm('Spec is empty, Save..?')) {
			simpan(kodebarang, nx);
		}
	} else {
		simpan(kodebarang, nx);
	}
	function simpan(kodebarang, nx) {
		eval(nx + ".document.getElementById('" + kodebarang + "').action='log_slave_savePhotoBarang.php'");
		eval(nx + ".document.getElementById('" + kodebarang + "').submit()");
		getMaterialNumber(kl);
	}
	// alert(kl);
	// getSubKlBarang();
}
function viewDetailbarang(kodebarang, ev) {
	tujuan = 'log_slave_material_picture_detail.php?kodebarang=' + kodebarang;
	content = "<iframe name=disPhotobarang src=" + tujuan + " frameborder=0 width=100% height=95%></iframe>";
	// showDialog1("Detail:" + kodebarang, content, '800', '600', ev);
	
	alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':content}).resizeTo('70%','70%').show();
	// alertify.popup("Detail " + kodebarang,content).set({'resizable':true,'maximizable':true}).resizeTo('60%','80%');
}
function setInactive(kodebarang) {
	xstatus = document.getElementById('br' + kodebarang).checked == true ? 1 : 0;
	param = 'kodebarang=' + kodebarang + '&status=' + xstatus;
	tujuan = 'log_slave_update_masterBarang.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					if (document.getElementById('br' + kodebarang).checked == true)
						document.getElementById('br' + kodebarang).checked = false;
					else
						document.getElementById('br' + kodebarang).checked = true;
				} else {}
			} else {
				busy_off();
				error_catch(con.status);
				if (document.getElementById('br' + kodebarang).checked == true)
					document.getElementById('br' + kodebarang).checked = false;
				else
					document.getElementById('br' + kodebarang).checked = true;
			}
		}
	}
}
function formminstok(title, wdth, heig) {
	width = '';
	height = '';
	if (wdth != '') {
		width = wdth;
	}
	if (heig != '') {
		height = heig;
	}
	content = "<div id=containerData></div>";
	ev = 'event';
	showDialog4(title, content, width, height, ev);
}
function minstok(kodebarang, namabarang) {
	title = '';
	width = '';
	height = '';
	// formminstok(title, width, height);
	param = 'kodebarang=' + kodebarang + '&namabarang=' + namabarang;
	tujuan = 'log_slave_5minstok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('containerData').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					loadDataBarang(kodebarang);
					//loadDataBarang(idmerk);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadDataBarang(kodebarang, namabarang) {
	param = 'method=loadData';
	param += '&kodebarang_det=' + kodebarang;
	tujuan = 'log_slave_save_5minstok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerbarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveBarang(kodebarang_det) {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	kodebarang_det = document.getElementById('kodebarang_det').value;
	spesifikasi = document.getElementById('spesifikasi').value;
	minstok_det = document.getElementById('minstok_det').value;
	method = document.getElementById('methoddetail').value;
	if (pt == '' || minstok_det == '' || kodebarang_det == '') {
		alert('Field Was Empty');
		return;
	}
	param = 'pt=' + pt + '&kodebarang_det=' + kodebarang_det + '&spesifikasi=' + spesifikasi + '&minstok_det=' + minstok_det + '&method=' + method;
	tujuan = 'log_slave_save_5minstok.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cancelBarang();
					loadDataBarang(kodebarang_det);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deleteimage(pic,kodebarang){
	param = 'pic='+pic+'&kodebarang='+kodebarang+'&method=deleteimage';
	tujuan = 'log_slave_save_5minstok.php';
	
	if (confirm('Anda yakin hapus item ini???')) {
		post_response_text(tujuan, param, respog);
	}
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					viewDetailbarang(kodebarang,event);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function nonaktifbarang(kodebarang){
	param = 'kodebarang='+kodebarang+'&method=nonaktifbarang';
	tujuan = 'log_slave_save_5minstok.php';
	
	if (confirm('Anda yakin non-aktif item ini???')) {
		post_response_text(tujuan, param, respog);
	}
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					cariBarang();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cancelBarang_det() {
	document.getElementById('pt').value = '';
	document.getElementById('minstok_det').value = '';
	document.getElementById('method').value = 'insert';
}
function del(pt, kodebarang_det) {
	param = 'method=delete' + '&pt=' + pt + '&kodebarang_det=' + kodebarang_det;
	tujuan = 'log_slave_save_5minstok.php';
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
					document.getElementById('containerbarang').innerHTML = con.responseText;
					loadDataBarang(kodebarang_det);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cancelsearch() {
	document.getElementById('txtcari').value = '';
	document.getElementById('optcari').value = '';
	document.getElementById('jenissch').value = '';
	document.getElementById('subkelompokbarangcr').selectedIndex = '0';
	document.getElementById('subkelompokbarangcr').disabled = true;
}

function chooseTarget(hargasatuan){
	nhargasatuan=document.getElementById("hargasatuan").options[document.getElementById('hargasatuan').selectedIndex].text;
	
	param='method=chooseTarget&hargasatuan='+hargasatuan+'&nhargasatuan='+nhargasatuan;
	tujuan = 'log_slave_get_material_number.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					// alert(con.responseText);
					alertify.alert('Informasi',con.responseText);
				}else{
					loadhargasatuan();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadhargasatuan(){
	param='method=loadhargasatuan';
	tujuan = 'log_slave_get_material_number.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					// alert(con.responseText);
					alertify.alert('Informasi',con.responseText);
				}else{
					document.getElementById('listhargasatuan').innerHTML=con.responseText;
					loadopthargasatuan();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletehargasatuan(hargasatuan){
	param='method=deletehargasatuan&hargasatuan='+hargasatuan;
	tujuan = 'log_slave_get_material_number.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					// alert(con.responseText);
					alertify.alert('Informasi',con.responseText);
				}else{
					loadhargasatuan();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadopthargasatuan(){
	param='method=loadopthargasatuan';
	tujuan = 'log_slave_get_material_number.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					// alert(con.responseText);
					alertify.alert('Informasi',con.responseText);
				}else{
					document.getElementById('hargasatuan').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clearseasion(){
	param='method=clearseasion';
	tujuan = 'log_slave_get_material_number.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					// alert(con.responseText);
					alertify.alert('Informasi',con.responseText);
				}else{
					loadhargasatuan();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}