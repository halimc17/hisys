function showbutton(){
	document.getElementById('formuploaddt').style.display = 'block';
}
function del(kunci) {
	param = 'method=del';
	param += '&kunci=' + kunci;
	tujuan = 'bgt_slave_kapitalx.php';
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

function unposting(tahunbudget,kodeorg) {
	param = 'method=unposting';
	param += '&tahun=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_kapitalx.php';
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
					showposting();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function posting(tahunbudget,kodeorg,sebaran,varkg){
	if(sebaran=='x' && varkg!=0){
		alert("Total rupiah tidak sama dengan sebaran,\nTerdapat selisih Rp. "+varkg);
		return;
	}
	param = 'method=posting';
	param += '&tahun=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_kapitalx.php';
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
					showposting();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function bataladd(){
	document.getElementById('tahunbudget').disabled=false;
	document.getElementById('kodeorg').disabled=false;
	//document.getElementById('continputdata').innerHTML='';
	//getblok();
}
function hapuspersen(){
	for(i=1;i<=12;i++){
		document.getElementById('persen_'+i).value=0;
	}
	ubahNilai();
}

function ubahNilai(){
	total= document.getElementById('totalrpx').innerHTML;
	total= remove_comma_var(total);
	tot = 0;
	for (x = 1; x < 13; x++) {
		if (document.getElementById('persen_' + x).value == ''){
			document.getElementById('persen_' + x).value = 0;
		}
		tot += parseFloat(document.getElementById('persen_' + x).value);
	}
	
	if (tot > 0) {
		for (x = 1; x < 13; x++) {
			document.getElementById('k' + x).innerHTML = 0;
		}
	}
	for (x = 1; x < 13; x++) {
		if (document.getElementById('persen_' + x).value != '' || document.getElementById('persen_' + x).value != 0) {
			z = parseFloat(document.getElementById('persen_' + x).value);
			if (tot > 0){
				document.getElementById('k' + x).innerHTML = numberFormat((z / tot) * total,2);
			}
		}
	}
}

function saveHeader() {
	kaliKan();
	tahunbudget = getValue('tahunbudget');
	kodeorg     = getValue('kodeorg');
	jeniskapital= getValue('jeniskapital');
	keterangan  = getValue('keterangan');
	jumlah      = getValue('jumlah');
	harga       = getValue('harga');
	total       = getValue('totalrp');
	lokasi      = getValue('lokasi');
	aruskas     = getValue('aruskas');
	id          = getValue('idbgt');
	kodebarang  = document.getElementById('kodebarang').value;
	flagbarang  = getValue('flagbarang');
	
	method      = document.getElementById('method').value;
	harga       = remove_comma_var(harga);
	total       = remove_comma_var(total);
	jumlah      = remove_comma_var(jumlah);
	
	
	param = '';
	param += '&tahunbudget=' + tahunbudget + '&kodeorg=' + kodeorg + '&jeniskapital=' + jeniskapital + '&keterangan=' + keterangan;
	param += '&jumlah=' + jumlah + '&harga=' + harga + '&total=' + total + '&lokasi=' + lokasi;
	param += '&aruskas=' + aruskas;
	param += '&id=' + id;
	param += '&kodebarang=' + kodebarang;
	param += '&method=' + method;
	
	validate([
        ["tahunbudget","Tahun Budget tidak boleh kosong."],
        ["kodeorg","Kode Organisasi tidak boleh kosong"],
        ["jeniskapital","Jenis Kapital tidak boleh kosong"],
        ["lokasi","Lokasi tidak boleh kosong"],
        ["aruskas","Aruskas tidak boleh kosong"],
        ["jumlah","Jumlah tidak boleh kosong"],
        ["totalrp","Total Rp tidak boleh kosong"],
        ["keterangan","keterangan tidak boleh kosong"]
	]);
	if(flagbarang>0){
		validate([
			["kodebarang","Kode Barang tidak boleh kosong."]
		]);
	}
	

	tujuan = 'bgt_slave_kapitalx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					kalenderisasi(trim(con.responseText));
					document.getElementById('tahunsch').value=tahunbudget;
					document.getElementById('listData').style.display = 'block';
					document.getElementById('kodeorgsch').value = kodeorg;
					
					//setValue('tahunsch',tahunbudget);
					setValue2('kodeorgsch',kodeorg);
					document.getElementById('keterangan').value = '';
					document.getElementById('jumlah').value = ''
					document.getElementById('idbgt').value = ''
					document.getElementById('totalrp').value = ''
					document.getElementById('method').value = 'simpan'
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function kalenderisasi(kunci,tutup) {
	param = 'method=kalenderisasi&kunci=' + kunci;
	param += '&tutup=' + tutup;
	tujuan = 'bgt_slave_kapitalx.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Sebarkan",con.responseText).set({
						'resizable':true,
						'maximizable':true,
							onclose:function(){
								loaddata()
							}
					}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function simpansebaran(kunci){
	param    = '';
	
	for (i = 1; i <= 12; i++) {
		rp = document.getElementById('k'+i).innerHTML;
		rp = remove_comma_var(rp);
		param += '&k[' + i + ']=' + rp;
	}

	param += '&kunci=' + kunci;
	param += '&method=simpansebaran';

	tujuan = 'bgt_slave_kapitalx.php';
	if(confirm("Anda yakin ???")){
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText)
				} else {
					alertify.popup().destroy();
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function batalsimpan(){
	document.getElementById('kgtbs').value='';
	document.getElementById('oerpersen').value='';
	document.getElementById('kerpersen').value='';
	for (i = 1; i <= 12; i++) {
		document.getElementById('kg'+i).value='';
	}
}

function numberFormat(number,digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	var components = (parseFloat(number).toFixed(digit)).split(".");
	components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	return components.join(".");
}

function addZero(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}



function editdetail(id){
	param  = 'method=editdetail';
	param += '&id=' + id;
	tujuan = 'bgt_slave_kapitalx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('inputdata').style.display = 'block';
					//document.getElementById('contdetail').style.display = 'block';
					document.getElementById('listData').style.display = 'block';
					document.getElementById('formcari').style.display = 'none';
					
					dt = con.responseText.split("##");
					document.getElementById('tahunbudget').value=dt[0];
					document.getElementById('kodeorg').value=dt[1];
					document.getElementById('jeniskapital').value=dt[2];
					document.getElementById('aruskas').value=dt[3];
					//document.getElementById('kodebarang').value=dt[4];
					document.getElementById('keterangan').value=dt[5];
					document.getElementById('jumlah').value=dt[6];
					document.getElementById('harga').value=dt[7];
					document.getElementById('totalrp').value=dt[8];
					document.getElementById('lokasi').value=dt[9];
					document.getElementById('idbgt').value=id;
					
					setValue2('kodeorg',dt[1]);
					setValue2('jeniskapital',dt[2]);
					setValue2('lokasi',dt[9]);
					// setValue2('aruskas',dt[3]);
					// setValue2('kodebarang',dt[4]);
					
					document.getElementById('tahunbudget').disabled=true;
					document.getElementById('kodeorg').disabled=true;
					getaruskas('jeniskapital','aruskas','',dt[3],dt[4]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	
}

function add_new_data(){
	document.getElementById('inputdata').style.display = 'block';
	//document.getElementById('contdetail').style.display = 'block';
	//document.getElementById('listData').style.display = 'none';
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';
	bataladd();
}

function add_sebaran(){
	document.getElementById('inputdata').style.display = 'none';
	//document.getElementById('contdetail').style.display = 'none';
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('contposting').style.display = 'block';
	document.getElementById('formcariposting').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	showposting();
}

function displayList() {
	document.getElementById('formcari').style.display = 'block';
	document.getElementById('listData').style.display = 'block';
	//document.getElementById('contdetail').style.display = 'none';
	document.getElementById('inputdata').style.display = 'none';
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';
	loaddata(0);
}

function showposting(){
	tahun  = document.getElementById('tahunpostsch').value;
	kodeorg= document.getElementById('kodeorgpostsch').value;
	
	param  = 'method=showposting';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_kapitalx.php';
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
function formupload(){
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	tt     = document.getElementById('tt').value;
	
	param  = 'method=formupload';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	tujuan = 'bgt_slave_kapitalx.php';
	judul = 'excel';
	ev    = 'event';
	printFile(param, tujuan, judul, ev)
}

function adddata(){
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	
	param  = 'method=adddata';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_kapitalx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contdetail').style.display = 'block';
					document.getElementById('listData').style.display = 'block';
					//document.getElementById('continputdata').innerHTML = con.responseText;
					document.getElementById('tahunsch').innerHTML="<option value='"+ tahun +"'>"+ tahun +"</option>"
					document.getElementById('kodeorgsch').value = kodeorg;
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getmark(no){
	namacol = document.getElementsByName('baris[]');
	for (var r = 0; r < namacol.length; r++) {
		namacol[r].style.backgroundColor="";
	}
	
	dis = document.getElementById('notran'+no).style.backgroundColor;
	if(dis!=''){
		document.getElementById('notran'+no).style.backgroundColor="";		
	}else{		
		document.getElementById('notran'+no).style.backgroundColor="cyan";
	}
	
}

function form() {
	width = '720';
	height = '';
	content = "<fieldset><div id=containerd align=center style=\"width:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
}
function html(tahun,kodeorg) {
	form();
	param = 'method=html'  + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_kapitalx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
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
	tahun  = document.getElementById('tahunsch').value;
	kodeorg= document.getElementById('kodeorgsch').value;
	sebaran= document.getElementById('sebaransch').value;
	
	
	param  = 'method=loaddata&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&sebaran=' + sebaran;
	tujuan = 'bgt_slave_kapitalx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
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

function loadexcel(page) {
	tahun  = document.getElementById('tahunsch').value;
	kodeorg= document.getElementById('kodeorgsch').value;
	sebaran= document.getElementById('sebaransch').value;
	
	
	
	param  = 'method=loaddata&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&sebaran=' + sebaran;
	param += '&jenis=excel';
	
	tujuan= 'bgt_slave_kapitalx.php';
	judul = 'excel';
	ev    = 'event';
	printFile(param, tujuan, judul, ev)
}

function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '300';
	height = '100';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1(title, content, width, height, ev);
}

function batalcari() {
	document.getElementById('kodeorgsch').value='';
	document.getElementById('divisisch').value='';
	document.getElementById('ttsch').value='';
	document.getElementById('sebaransch').value='';
	document.getElementById('ipsch').value='';
	
	setValue2('divisisch',null);
	setValue2('sebaransch',null);
	loaddata(0);
}

function getunit(sumber){
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	jenis = document.getElementById('jenis').value;

	param = 'method=getunit';
	param += '&kodeorg=' + kodeorg;
	param += '&jenis=' + jenis;
	param += '&tahun=' + tahun;
	tujuan = 'bgt_slave_kapitalx.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					document.getElementById('kodeunit').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function gettbskebun(sumber){
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	jenis = document.getElementById('jenis').value;
	kodeunit = document.getElementById('kodeunit').value;

	param = 'method=gettbskebun';
	param += '&kodeorg=' + kodeorg;
	param += '&jenis=' + jenis;
	param += '&tahun=' + tahun;
	param += '&kodeunit=' + kodeunit;
	tujuan = 'bgt_slave_kapitalx.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
					batalsimpan();
				}else{
					if(jenis!=0){						
						data = con.responseText.split("##");
						document.getElementById('kgtbs').value = trim(data[0]);
						for(i=1;i<=12;i++){						
							document.getElementById('kg'+i).value = trim(data[i]);
						}
					}
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function getlokasi() {
	kodeorg = document.getElementById('kodeorg').value;
	
	param = 'method=getlokasi';
	param += '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_kapitalx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('lokasi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getaruskas(idsumber,idtujuan,akun,aruskas,kodebarang){
	kodebgt = document.getElementById(idsumber).value;
    param = 'kodebgt=' + kodebgt;
    param += '&akun=' + akun;
    param += '&aruskas=' + aruskas;
    param += '&kodebarang=' + kodebarang;
    param += '&method=getaruskas';
    tujuan = 'bgt_slave_kapitalx.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					data=con.responseText.split("####");
					if(trim(data[2])=='1'){
						document.getElementById('kodebarang').disabled=false;						
					}else{
						document.getElementById('kodebarang').disabled=true;
					}
					document.getElementById('flagbarang').value = trim(data[2]);
                    document.getElementById(idtujuan).innerHTML = data[0];
                    document.getElementById('kodebarang').innerHTML = data[1];
					setValue2('kodebarang',kodebarang);
					
					// alert(data[0]);
					//setharga();
					//alert("masuk");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function gethargabarang(kodebarang){
	tahunbudget= document.getElementById('tahunbudget').value;
	kodeorg    = document.getElementById('kodeorg').value;
	
	validate([
        ["tahunbudget","Tahun Budget tidak boleh kosong."],
        ["kodeorg","Kode Organisasi tidak boleh kosong"]
	]);

    param = 'kodebarang=' + kodebarang;
    param += '&tahunbudget=' + tahunbudget;
    param += '&kodeorg=' + kodeorg;
    param += '&method=gethargabarang';
    tujuan = 'bgt_slave_kapitalx.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					document.getElementById('harga').value = trim(con.responseText);
					if(trim(con.responseText)>'0'){
						document.getElementById('harga').disabled=true;
					}else{
						document.getElementById('harga').disabled=false;						
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function kaliKan() {
	harga = getValue('harga');
	jumlah= getValue('jumlah');
	
	harga =remove_comma_var(harga);
	jumlah=remove_comma_var(jumlah);
	
	document.getElementById('totalrp').value = numberFormat(harga * jumlah);
}