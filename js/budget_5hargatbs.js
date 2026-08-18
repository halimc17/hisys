function getbarang(e){
	if(e=='TBS'){
		document.getElementById('tahuntanam').disabled = false;
		setValue2('tahuntanam',null);
	}else{
		document.getElementById('tahuntanam').disabled = true;
		setValue2('tahuntanam',null);
	}
}


function add_new_data(){
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cancel();
}

function html(tahun,kodeorg,namabarang) {
	//form();
	param = 'method=html'  + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&namabarang=' + namabarang;
	tujuan = 'budget_slave_5hargatbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('10%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function displayList() {
	document.getElementById('tahunsch').value = '';
	document.getElementById('kodeorgsch').value = '';

	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('contdetail').style.display = 'none';
	loaddata(0);
}
function edit(tahun,kodeorg,kodebarang) {
	document.getElementById('tahun').value = tahun;
	//document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('tahuntanam').disabled = false;
	//setValue2('kodeorg',kodeorg);
	setValue2('namabarang',kodebarang);
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	getbgtkode();
}
function deletedetail(tahun,kodebarang,kodeorg,tahuntanam, pabrik){
	param = 'method=deletedetail' + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&namabarang=' + kodebarang;
	param += '&tahuntanam=' + tahuntanam;
	param += '&pabrik=' + pabrik;
	tujuan = 'budget_slave_5hargatbs.php';
	if(confirm(' Anda yakin ???')){
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetail(tahun,kodeorg);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function del(tahun,kodeorg,namabarang) {
	param = 'method=delete' + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&namabarang=' + namabarang;
	tujuan = 'budget_slave_5hargatbs.php';
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
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function formposting(tahun,kodeorg,namabarang) {
	param = 'method=formposting' + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&namabarang=' + namabarang;
	tujuan = 'budget_slave_5hargatbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup("Detail","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('700px','350px');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function posting(tahun,kodeorg,namabarang) {
	plasmatbs  = document.getElementById('plasmatbs').value;
	afiliasitbs= document.getElementById('afiliasitbs').value;
	externaltbs= document.getElementById('externaltbs').value;
	externalcpo= document.getElementById('externalcpo').value;
	externalpk = document.getElementById('externalpk').value;
	
	param = 'method=posting' + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&namabarang=' + namabarang;
	param += '&plasmatbs=' + plasmatbs;
	param += '&afiliasitbs=' + afiliasitbs;
	param += '&externaltbs=' + externaltbs;
	param += '&externalcpo=' + externalcpo;
	param += '&externalpk=' + externalpk;
	tujuan = 'budget_slave_5hargatbs.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function unposting(tahun,kodeorg,namabarang) {
	param = 'method=unposting' + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&namabarang=' + namabarang;
	tujuan = 'budget_slave_5hargatbs.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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
function insertdetail() {
	tahun     = document.getElementById('tahun').value;
	periode   = document.getElementById('bulan').value;
	kodeorg   = document.getElementById('kodeorg').value;
	namabarang= document.getElementById('namabarang').value;
	tahuntanam= document.getElementById('tahuntanam').value;
	nilai     = document.getElementById('nilai').value;
	method    = document.getElementById('method').value;

	validate([
        ["tahun","Tahun tidak boleh kosong."],
        ["bulan","Bulan tidak boleh kosong."],
        ["namabarang","Nama Barang tidak boleh kosong"],
        ["kodeorg","Kode organisasi tidak boleh kosong"],
        ["nilai","Rupiah tidak boleh kosong"]
	]);

	param = 'method='+method;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&namabarang=' + namabarang + '&nilai=' + nilai;
	param += '&tahuntanam=' + tahuntanam;
	param += '&periode=' + periode;
	
	tujuan = 'budget_slave_5hargatbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetail(tahun,kodeorg);
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
	
	param = 'method=loaddata&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'budget_slave_5hargatbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cancel() {
	document.getElementById('contdetail').style.display = 'none';
	document.getElementById('tahun').disabled = false;
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('namabarang').disabled = false;
	document.getElementById('nilai').value = '';
	document.getElementById('method').value = 'insert';
	
	setValue2('namabarang',null);
	setValue2('kodeorg',null);
}
function loaddatadetail(tahun, kodeorg) {
	tahun     = document.getElementById('tahun').value;
	kodeorg   = document.getElementById('kodeorg').value;
	
	
	param = 'method=loaddatadetail';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	
	tujuan = 'budget_slave_5hargatbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contdetail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getbgtkode(thntnm) {
	tahun     = document.getElementById('tahun').value;
	kodeorg   = document.getElementById('kodeorg').value;
	namabarang= document.getElementById('namabarang').value;
	
	param = 'method=getbgtkode';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&thntnm=' + thntnm;
	tujuan = 'budget_slave_5hargatbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(namabarang=='TBS'){
						if(thntnm!='' && thntnm!=undefined){
							document.getElementById('tahuntanam').disabled = true;							
							setValue2('tahuntanam',thntnm);
						}else{							
							document.getElementById('tahuntanam').disabled = false;
						}
						document.getElementById('tahuntanam').innerHTML = con.responseText;	
					}else{
						document.getElementById('tahuntanam').disabled = true;
						setValue2('tahuntanam',null);
					}
					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdetail(tahunbudget,kodebarang,kodeorg,tahuntanam,jumlah){
	document.getElementById('tahun').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('namabarang').disabled = true;
	document.getElementById('tahuntanam').disabled = true;
	document.getElementById('tahun').value = tahunbudget;
	document.getElementById('nilai').value = jumlah;
	document.getElementById('method').value = 'updatedetail';
	setValue2('kodeorg',kodeorg);
	setValue2('namabarang',kodebarang);
	getbgtkode(tahuntanam);
}