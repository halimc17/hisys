function copy() {
	kodeorg  = document.getElementById('kodeorgdari').value;
	periode  = document.getElementById('periodedari').value;
	jenis    = document.getElementById('jenisdari').value;
	periodeke= document.getElementById('periodeke').value;
	

	param = 'method=copy';
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&jenis=' + jenis;
	param += '&periodeke=' + periodeke;

	tujuan = 'kebun_slave_5premipanentt.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
					// loaddata(0);
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
	kodeorg = document.getElementById('kodeorgsch').value;
	periode = document.getElementById('periodesch').value;
	jenis = document.getElementById('jenissch').value;
	param = 'method=loaddata&page=' + page;
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&jenis=' + jenis;

	tujuan = 'kebun_slave_5premipanentt.php';
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
	cleardetail();
	loaddatadetail(kodeorg);
}


function loaddatadetail(kodeorg, tgl) {
	// console.log('kena');
	param = 'method=loaddatadetail';
	param += '&kodeorg=' + kodeorg;
	tujuan = 'kebun_slave_5premipanentt.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetail() {
	kodeorg     = document.getElementById('kodeorg').value;
	periode     = document.getElementById('periode').value;
	jenis       = document.getElementById('jenis').value;
	tahuntanam  = document.getElementById('tahuntanam').value;
	basis1      = document.getElementById('basis1').value;
	basis2      = document.getElementById('basis2').value;
	siapbasis   = document.getElementById('siapbasis').value;
	siapbasis2   = document.getElementById('siapbasis2').value;
	tidakbasis  = document.getElementById('tidakbasis').value;
	lebihbasis1 = document.getElementById('lebihbasis1').value;
	lebihbasis2 = document.getElementById('lebihbasis2').value;
	brondol     = document.getElementById('brondol').value;
	rphk     = document.getElementById('rphk').value;
	method      = document.getElementById('method').value;
	if (kodeorg == '') {
		notif('kodeorg','Kode Organisasi wajib diisi.'); return;
	}
	if (periode == '') {
		notif('periode','Periode wajib diisi.'); return;
	}
	if (tahuntanam == '') {
		notif('tahuntanam','tahuntanam dari wajib diisi.'); return;
	}

	if(basis1==''){basis1=0;}
	if(basis2==''){basis2=0;}
	if(siapbasis==''){siapbasis=0;}
	if(siapbasis2==''){siapbasis2=0;}
	if(lebihbasis1==''){lebihbasis1=0;}
	if(lebihbasis2==''){lebihbasis2=0;}
	if(brondol==''){brondol=0;}
	if(tidakbasis==''){tidakbasis=0;}
	if(rphk==''){rphk=0;}
	
	if (basis1 == '0' && basis2=='0' && siapbasis=='0' && lebihbasis1=='0' && lebihbasis2=='0' && brondol=='0') {
		notif('basis1#basis2#siapbasis#lebihbasis1#lebihbasis2#brondol','Basis 1, Basis 2, Premi Siap Basis, Lebih Basis 1, Lebih Basis 2 dan Brondolan salah satu harus terisi.'); return;
	}
	
	param = 'kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&jenis=' + jenis + '&tahuntanam=' + tahuntanam;
	param += '&basis1=' + basis1;
	param += '&siapbasis2=' + siapbasis2;
	param += '&basis2=' + basis2 + '&siapbasis=' + siapbasis;
	param += '&lebihbasis1=' + lebihbasis1 + '&lebihbasis2=' + lebihbasis2;
	param += '&brondol=' + brondol;
	param += '&tidakbasis=' + tidakbasis;
	param += '&rphk=' + rphk;
	param += '&method=' + method;
	tujuan = 'kebun_slave_5premipanentt.php';
	// console.log(param);

	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cleardetail();
					loaddatadetail(kodeorg);
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
	document.getElementById('periode').disabled=false;
	document.getElementById('jenis').disabled=false;
	document.getElementById('tahuntanam').disabled=false;
	
	//document.getElementById('kodeorg').value='';
	// document.getElementById('periode').value='';
	document.getElementById('tidakbasis').value='';
	document.getElementById('tahuntanam').value='';
	document.getElementById('basis1').value='';
	document.getElementById('basis2').value='';
	document.getElementById('siapbasis').value='';
	document.getElementById('lebihbasis1').value='';
	document.getElementById('lebihbasis2').value='';
	document.getElementById('brondol').value='';
	document.getElementById('rphk').value='';
	document.getElementById('method').value = 'insert';
	hapuswarna('kodeorg#periode#jenis#tahuntanam#basis1#basis2#siapbasis#lebihbasis1#lebihbasis2#brondol');
	
	setValue2('tahuntanam',null);
}

function editdetail(kodeorg,periode,jenispremi,tahuntanam,basis1,basis2,premibasis,premilebihbasis1,premilebihbasis2,premibrondolan,tidakbasis,premibasis2,rphk) {
	document.getElementById('kodeorg').disabled=true;
	document.getElementById('periode').disabled=true;
	document.getElementById('jenis').disabled=true;
	document.getElementById('tahuntanam').disabled=true;
	
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('periode').value=periode;
	document.getElementById('jenis').value=jenispremi;
	document.getElementById('tahuntanam').value=tahuntanam;
	setValue2('kodeorg',kodeorg);
	setValue2('periode',periode);
	setValue2('jenis',jenispremi);
	setValue2('tahuntanam',tahuntanam);
	document.getElementById('basis1').value=basis1;
	document.getElementById('basis2').value=basis2;
	document.getElementById('tidakbasis').value=tidakbasis;
	document.getElementById('siapbasis').value=premibasis;
	document.getElementById('siapbasis2').value=premibasis2;
	document.getElementById('lebihbasis1').value=premilebihbasis1;
	document.getElementById('lebihbasis2').value=premilebihbasis2;
	document.getElementById('brondol').value=premibrondolan;
	document.getElementById('rphk').value=rphk;
	document.getElementById('method').value = 'update';
}

function deldetail(kodeorg,periode,jenis,tahuntanam,tempat) {
	param = 'method=deletedetail' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&jenis=' + jenis + '&tahuntanam=' + tahuntanam;
	// console.log(param);
	tujuan = 'kebun_slave_5premipanentt.php';
	if(confirm(' Anda yakin ?')){
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (tempat === 'detail') {
						loaddatadetail(kodeorg);
					} else {
						loaddata(0);
					}
					// getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function edit(kodeorg,periode,jenispremi,tahuntanam,basis1,basis2,premibasis,premilebihbasis1,premilebihbasis2,premibrondolan,tidakbasis,siapbasis2, rphk) {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	
	document.getElementById('kodeorg').disabled=true;
	document.getElementById('periode').disabled=true;
	document.getElementById('jenis').disabled=true;
	document.getElementById('tahuntanam').disabled=true;
	
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('periode').value=periode;
	document.getElementById('jenis').value=jenispremi;
	document.getElementById('tahuntanam').value=tahuntanam;
	setValue2('kodeorg',kodeorg);
	setValue2('periode',periode);
	setValue2('jenis',jenispremi);
	setValue2('tahuntanam',tahuntanam);
	
	document.getElementById('basis1').value=basis1;
	document.getElementById('basis2').value=basis2;
	document.getElementById('tidakbasis').value=tidakbasis;
	document.getElementById('siapbasis').value=premibasis;
	document.getElementById('siapbasis2').value=siapbasis2;
	document.getElementById('lebihbasis1').value=premilebihbasis1;
	document.getElementById('lebihbasis2').value=premilebihbasis2;
	document.getElementById('brondol').value=premibrondolan;
	document.getElementById('rphk').value=rphk;
	document.getElementById('method').value = 'update';
	loaddatadetail(kodeorg);
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
	alert(isipesan);
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
	document.getElementById('kodeorgsch').value='';
	document.getElementById('periodesch').value='';
	document.getElementById('jenissch').value='';
	
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}

function getlibur(val){
	// if(val=='LIBUR'){
		// document.getElementById('basis1').disabled=true;
		// document.getElementById('basis2').disabled=true;
		// document.getElementById('siapbasis').disabled=true;
		// document.getElementById('lebihbasis1').disabled=true;
	// }else{
		// document.getElementById('basis1').disabled=false;
		// document.getElementById('basis2').disabled=false;
		// document.getElementById('siapbasis').disabled=false;
		// document.getElementById('lebihbasis1').disabled=false;
	// }
}

function ambiltt(){
	kodeorg = document.getElementById('kodeorg').value;
	method = 'ambiltt';
	param = 'kodeorg=' + kodeorg;
	param += '&method=' + method;
	// alert (param);
	tujuan = 'kebun_slave_5premipanentt.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tahuntanam').innerHTML = con.responseText;
					// document.getElementById('tahuntanam').readonly=true;
				}
			} else {
				busy_off();
				error_catch(con.status);

			}
		}
	}
}