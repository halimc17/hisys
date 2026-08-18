function detailExcel(divisi,pupuk,tahun, ev,tipe) {	
	param = "method=preview&tipe=" + tipe + "&divisi=" + divisi+ "&pupuk=" + pupuk+ "&tahun=" + tahun;
	// showDialog1('Preview', "<iframe frameborder=0 style='width:895px;height:400px'" +
	// 	" src='kebun_slave_5dosispupuk.php?" + param + "'></iframe>", '900', '400', ev);
	// var dialog = document.getElementById('dynamic1');
	// dialog.style.top = '50px';
	// dialog.style.left = '15%';

	alertify.popup("Preview","<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='kebun_slave_5dosispupuk.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}
function detailData(divisi,pupuk,tahun, ev, tipe) {
	param = "method=preview&tipe=" + tipe + "&divisi=" + divisi+ "&pupuk=" + pupuk+ "&tahun=" + tahun;
	title = "Data Detail";
	// showDialog1(title, "<iframe frameborder=0 style='width:895px;height:400px'" +
		// " src='kebun_slave_5dosispupuk.php?" + param + "'></iframe>", '', '', ev);
	// var dialog = document.getElementById('dynamic1');
	// dialog.style.top = '50px';
	// dialog.style.left = '15%';
	
	alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='kebun_slave_5dosispupuk.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}


function getPageDetail() {
	pg = document.getElementById('pagesdet');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddatadetail(paged);
}

function hitungjlh(sumber){
	dosis = document.getElementById('dosis').value;
	jumlah= document.getElementById('jumlah').value;
	pokok = document.getElementById('pokok').value;
	dosis =remove_comma_var(dosis);
	jumlah=remove_comma_var(jumlah);
	pokok =remove_comma_var(pokok);
	if(sumber=='dosis'){
		n = parseFloat(dosis)*parseFloat(pokok);
		if(isNaN(n)){n=0;}
		document.getElementById('jumlah').value=numberFormat(n,2);
	}else{
		n = parseFloat(jumlah)/parseFloat(pokok);
		if(isNaN(n)){n=0;}
		document.getElementById('dosis').value=numberFormat(n,2);
	}
}

function getluas() {
	blok     = document.getElementById('blok').value;
	
	param = 'method=getluas';
	param += '&blok=' + blok;
	
	tujuan = 'kebun_slave_5dosispupuk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					isi = con.responseText.split("##");
					document.getElementById('luas').value=isi[0];
					document.getElementById('pokok').value=isi[1];
					document.getElementById('jenistanah').value=isi[2];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdata(sumber) {
	kodeorg= document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	tt     = document.getElementById('tt').value;
	
	param = 'method=getdata';
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&tt=' + tt;
	param += '&sumber=' + sumber;

	tujuan = 'kebun_slave_5dosispupuk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					isi = con.responseText.split("##");
					if(sumber=='kodeorg'){
						document.getElementById('divisi').innerHTML=isi[0];
						document.getElementById('tt').innerHTML=isi[1];
						document.getElementById('blok').innerHTML=isi[2];
					}
					if(sumber=='divisi'){
						document.getElementById('tt').innerHTML=isi[1];
						document.getElementById('blok').innerHTML=isi[2];
					}
					if(sumber=='tt'){
						document.getElementById('blok').innerHTML=isi[2];
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
	param = 'method=loaddata&page=' + page;
	param += '&kodeorg=' + getValue('kodeorgs');
	param += '&divisi=' + getValue('divisis');
	param += '&tt=' + getValue('tts');
	param += '&pupuk=' + getValue('pupuks');
	
	tujuan = 'kebun_slave_5dosispupuk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
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

function add_new_data(){
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('upload').style.display = 'none';
	cleardetail();
	loaddatadetail();
}


function loaddatadetail(page) {
	param = 'method=loaddatadetail&page=' + page;
	param += '&kodeorg=' + getValue('kodeorgsdet');
	param += '&divisi=' + getValue('divisisdet');
	param += '&tt=' + getValue('ttsdet');
	param += '&pupuk=' + getValue('pupuksdet');
	
	tujuan = 'kebun_slave_5dosispupuk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					isdt = con.responseText.split("####");
					
					document.getElementById('detail').style.display = 'block';
					document.getElementById('containdet').innerHTML = isdt[0];
					document.getElementById('footDatadet').innerHTML = isdt[1];
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetail() {
	kodeorg   = document.getElementById('kodeorg').value;
	divisi    = document.getElementById('divisi').value;
	tt        = document.getElementById('tt').value;
	blok      = document.getElementById('blok').value;
	luas      = document.getElementById('luas').value;
	pokok     = document.getElementById('pokok').value;
	pupuk     = document.getElementById('pupuk').value;
	pupukold     = document.getElementById('pupukold').value;
	apl       = document.getElementById('apl').value;
	aplold       = document.getElementById('aplold').value;
	jenistanah= document.getElementById('jenistanah').value;
	bulan     = document.getElementById('bulan').value;
	bulanold     = document.getElementById('bulanold').value;
	bulan     = document.getElementById('bulan').value;
	tahun     = document.getElementById('tahun').value;
	tahunold     = document.getElementById('tahunold').value;
	jumlah     = document.getElementById('jumlah').value;
	dosis     = document.getElementById('dosis').value;
	
	method    = document.getElementById('method').value;
	if (kodeorg == '') {
		notif('kodeorg','Kode Organisasi wajib diisi.'); return;
	}
	if (divisi == '') {
		notif('divisi','Divisi wajib diisi.'); return;
	}
	if (tt == '') {
		notif('tt','Tahun Tanam dari wajib diisi.'); return;
	}
	if (blok == '') {
		notif('blok','Blok wajib diisi.'); return;
	}
	if (pupuk == '') {
		notif('pupuk','Jenis Pupuk wajib diisi.'); return;
	}
	if (apl == '') {
		notif('apl','Aplikasi wajib diisi.'); return;
	}
	if (dosis == '') {
		notif('dosis','Dosis wajib diisi.'); return;
	}
	if (jumlah == '') {
		notif('jumlah','Jumlah wajib diisi.'); return;
	}
	if (bulan == '') {
		notif('bulan','Bulan wajib diisi.'); return;
	}
	if (tahun == '') {
		notif('tahun','Tahun wajib diisi.'); return;
	}
	
	periode = tahun+'-'+bulan;
	periodeold = tahunold+'-'+bulanold;
	param = 'kodeorg=' + kodeorg + '&divisi=' + divisi;
	param += '&tt=' + tt + '&blok=' + blok;
	param += '&luas=' + luas + '&pokok=' + pokok;
	param += '&pupuk=' + pupuk + '&apl=' + apl;
	param += '&pupukold=' + pupukold + '&aplold=' + aplold;
	param += '&dosis=' + dosis + '&jumlah=' + jumlah;
	param += '&periode=' + periode;
	param += '&periodeold=' + periodeold;
	param += '&jenistanah=' + jenistanah;
	
	param += '&method=' + method;
	tujuan = 'kebun_slave_5dosispupuk.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					cleardetail();
					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cleardetail() {
	document.getElementById('kodeorg').disabled=false;
	document.getElementById('divisi').disabled=false;
	document.getElementById('tt').disabled=false;
	document.getElementById('blok').disabled=false;
	document.getElementById('blok').value='';
	document.getElementById('luas').value='';
	document.getElementById('pokok').value='';
	document.getElementById('method').value = 'insert';
	//hapuswarna('kodeorg#periode#jenis#bjrdari#bjrsampai#basis1#basis2#siapbasis#lebihbasis1#lebihbasis2#brondol');
}

function editdetail(kodeorg,divisi,blok,tahuntanam,luas,pokok,apl,jenistanah,dosis,jumlah,kodebarang,bln,thn){
	document.getElementById('kodeorg').disabled=true;
	document.getElementById('divisi').disabled=true;
	document.getElementById('blok').disabled=true;
	document.getElementById('tt').disabled=true;
	
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('divisi').value=divisi;
	document.getElementById('blok').value=blok;
	document.getElementById('tt').value=tahuntanam;
	document.getElementById('luas').value=luas;
	document.getElementById('pokok').value=pokok;
	document.getElementById('apl').value=apl;
	document.getElementById('aplold').value=apl;
	document.getElementById('jenistanah').value=jenistanah;
	document.getElementById('dosis').value=dosis;
	document.getElementById('jumlah').value=jumlah;
	document.getElementById('pupuk').value=kodebarang;
	document.getElementById('pupukold').value=kodebarang;
	document.getElementById('bulan').value=bln;
	document.getElementById('bulanold').value=bln;
	document.getElementById('tahun').value=thn;
	document.getElementById('tahunold').value=thn;
	document.getElementById('method').value = 'update';
}

function deldetail(blok,periode,pupuk,apl) {
	param = 'method=deletedetail' + '&blok=' + blok + '&periode=' + periode + '&pupuk=' + pupuk + '&apl=' + apl;
	tujuan = 'kebun_slave_5dosispupuk.php';
	// if(confirm(' Anda yakin ?')){
	// 	post_response_text(tujuan, param, respog);
	// }
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
					alertify.alert("Informasi",con.responseText);
				} else {
					getPageDetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function edit(kodeorg,divisi,pupuk,tahun) {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';

	document.getElementById('kodeorgsdet').value=kodeorg;
	document.getElementById('divisisdet').value=divisi;
	document.getElementById('pupuksdet').value=pupuk;
	document.getElementById('ttsdet').value=tahun;
	loaddatadetail();
}
function del(kodeorg,divisi,pupuk,tahun) {
	param = 'method=delete' + '&kodeorg=' + kodeorg + '&divisi=' + divisi + '&pupuk=' + pupuk+ '&tahun=' + tahun;
	tujuan = 'kebun_slave_5dosispupuk.php';
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
					alertify.alert("Informasi",con.responseText);
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

function posting(kodeorg,divisi,pupuk,tahun) {
	param = 'method=posting' + '&kodeorg=' + kodeorg + '&divisi=' + divisi + '&pupuk=' + pupuk+ '&tahun=' + tahun;
	tujuan = 'kebun_slave_5dosispupuk.php';
	// if(confirm(' Anda yakin ?')){
	// 	post_response_text(tujuan, param, respog);
	// }
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
					alertify.alert("Informasi",con.responseText);
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
function unposting(kodeorg,divisi,pupuk,tahun) {
	param = 'method=unposting' + '&kodeorg=' + kodeorg + '&divisi=' + divisi + '&pupuk=' + pupuk+ '&tahun=' + tahun;
	tujuan = 'kebun_slave_5dosispupuk.php';
	// if(confirm(' Anda yakin ?')){
	// 	post_response_text(tujuan, param, respog);
	// }
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
					alertify.alert("Informasi",con.responseText);
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

function notif(idkolom,isipesan){
	col = idkolom.split("#");
	n = col.length;
	for(i=0;i<n;i++){
		kolom=document.getElementById(col[i]);
		kolom.focus();
		kolom.style.borderColor='red';		
		kolom.style.backgroundColor='#F2F94D';
		kolom.style.fontWeight='bold';
	}
	alertify.alert("Informasi",isipesan);
}

function hapuswarna(id){
	col = id.split("#");
	n = col.length;
	for(i=0;i<n;i++){
		kolom=document.getElementById(col[i]);
		kolom.style.borderColor='';		
		kolom.style.backgroundColor='';
		kolom.style.fontWeight='';
	}
}


function displayList() {
	document.getElementById('kodeorgs').value='';
	document.getElementById('divisis').value='';
	document.getElementById('tts').value='';
	document.getElementById('pupuks').value='';
	
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('upload').style.display = 'none';
	
	loaddata(0);
}

function showupload(){
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'none';
	document.getElementById('upload').style.display = 'block';
	
	
    param = 'method=showupload';
    tujuan = 'kebun_slave_5dosispupuk.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert("Informasi",con.responseText);
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
    // if(confirm('Are you sure..?')){
    // document.getElementById('frm').submit();
    // }
	alertify.confirm("Warning","Are you sure..?",
		function(){
			document.getElementById('frm').submit();
		},
		function(){
			return;
		}
	);
}

function uploaddataall(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alertify.alert("Informasi",'Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Simpan semua ???")) {
		max = maxRow;
		uploaddata(1, maxRow);
	}
}
function uploaddata(currRow, maxRow) {
	kodeorg   = document.getElementById('tdkdorg_'+currRow).innerHTML;
	divisi    = document.getElementById('tddivisi_'+currRow).innerHTML;
	tt        = document.getElementById('tdtt_'+currRow).innerHTML;
	blok      = document.getElementById('tdblok_'+currRow).innerHTML;
	luas      = document.getElementById('tdluas_'+currRow).innerHTML;
	pokok     = document.getElementById('tdpokok_'+currRow).innerHTML;
	jenistanah= document.getElementById('tdtanah_'+currRow).innerHTML;
	pupuk     = document.getElementById('tdpupuk_'+currRow).innerHTML;
	apl       = document.getElementById('tdapl_'+currRow).innerHTML;
	dosis     = document.getElementById('tddosis_'+currRow).innerHTML;
	jumlah    = document.getElementById('tdjlh_'+currRow).innerHTML;
	periode   = document.getElementById('tdperiode_'+currRow).innerHTML;
	info   = document.getElementById('info_'+currRow).innerHTML;
	if(info!='OK'){
		alertify.alert("Informasi",'Data ada yang salah.'); return;
	}
	
    param = 'method=insert';
	param += '&kodeorg=' + kodeorg + '&divisi=' + divisi;
	param += '&tt=' + tt + '&blok=' + blok;
	param += '&luas=' + luas + '&pokok=' + pokok;
	param += '&pupuk=' + pupuk + '&apl=' + apl;
	param += '&dosis=' + dosis + '&jumlah=' + jumlah;
	param += '&periode=' + periode;
	param += '&jenistanah=' + jenistanah;
	
	
	tujuan = 'kebun_slave_5dosispupuk.php';
	document.getElementById('trpemel_'+currRow).style.backgroundColor='cyan';
	document.getElementById('btnupload2').disabled=true;
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
					document.getElementById('trpemel_' + currRow).style.backgroundColor = 'red';
				} else {
					if (currRow != undefined) {
						document.getElementById('trpemel_' + currRow).style.backgroundColor = '';
					}
					currRow += 1;
					sekarang = currRow;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						alert("Done"); 
						document.getElementById('btnupload2').disabled=false;
					} else {
						uploaddata(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
