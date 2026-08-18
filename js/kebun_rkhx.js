function detailExcel(notransaksi,ev,tipe) {
	param = 'method=preview';
	param += '&notransaksi=' + notransaksi;
	param += '&tipe=' + tipe;
	
	showDialog1('Preview', "<iframe frameborder=0 style='width:895px;height:400px'" +
		" src='kebun_slave_rkhx.php?" + param + "'></iframe>", '900', '400', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}
function detailData(notransaksi,ev,tipe) {
	
	param = 'method=preview';
	param += '&notransaksi=' + notransaksi;
	param += '&tipe=' + tipe;
	tujuan='kebun_slave_rkhx.php';
	
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
					// title = tipe;
					// width = '';
					// height = '';
					// content = "<fieldset style=width:98%><legend>"+title+"</legend><div id=contviewdet style='overflow:auto;min-width:895px;height:400px;' ></div></fieldset>";
					// showDialog1(title, content, width, height, ev);
                    // document.getElementById('contviewdet').innerHTML=con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function getdata() {
	divisi   = document.getElementById('divisi').value;
	blok   = document.getElementById('blok').value;
	tgl   = document.getElementById('tgl').value;
	kegiatan   = document.getElementById('kegiatan').value;
	
	param = 'method=getdata';
	param += '&divisi=' + divisi;
	param += '&blok=' + blok;
	param += '&tgl=' + tgl;
	param += '&kegiatan=' + kegiatan;
	
	tujuan = 'kebun_slave_rkhx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("###");
					// document.getElementById('kegiatan').innerHTML = trim(isdt[0]);
					// document.getElementById('luas').value = trim(isdt[1]);
					// document.getElementById('pokok').value = trim(isdt[2]);
					// document.getElementById('bjr').value = trim(isdt[3]);

					document.getElementById('luas').value = trim(isdt[0]);
					document.getElementById('pokok').value = trim(isdt[1]);
					document.getElementById('bjr').value = trim(isdt[2]);
					// setValue('pres','');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdetailkeg() {
	divisi   = document.getElementById('divisi').value;
	// blok   = document.getElementById('blok').value;
	kegiatan   = document.getElementById('kegiatan').value;
	
	param = 'method=getdetailkeg';
	param += '&divisi=' + divisi;
	// param += '&blok=' + blok;
	param += '&statusblok=' + kegiatan;
	param += '&kegiatan=' + kegiatan;
	
	tujuan = 'kebun_slave_rkhx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("###");
					document.getElementById('sat').value = trim(isdt[0]);
					document.getElementById('barang').innerHTML = trim(isdt[1]);
					document.getElementById('jlhbrg').value = trim(isdt[2]);
					document.getElementById('blok').innerHTML = trim(isdt[3]);
					kolom=document.getElementById('barang');
					kolom.style.borderColor='';		
					kolom.style.backgroundColor='';
					kolom.style.fontWeight='';
					kolom.disabled=true;
						
					if(trim(isdt[2])>'0'){
						kolom=document.getElementById('barang');
						kolom.style.borderColor='red';		
						kolom.style.backgroundColor='#F2F94D';
						kolom.style.fontWeight='bold';
						kolom.disabled=false;
					}
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getjlhbrg(sumber){
	dosis = document.getElementById('dosis').value;
	pres = document.getElementById('pres').value;
	jlhbarang = document.getElementById('jlhbarang').value;
	if(pres==''){
		document.getElementById('jlhbarang').value='';
		document.getElementById('dosis').value='';
		notif('pres','Isikan prestasi terlebih dahulu');
		return;
	}
	barang = document.getElementById('barang').value;
	if(barang==''){
		document.getElementById('jlhbarang').value='';
		document.getElementById('dosis').value='';
		notif('barang','Isikan kode barang terlebih dahulu');
		return;
	}
	
	if(sumber=='dss'){
		n = parseFloat(dosis)*parseFloat(pres);
		if(isNaN(n)){n=0;}
		document.getElementById('jlhbarang').value=n;
	}else{
		n = parseFloat(jlhbarang)/parseFloat(pres);
		if(isNaN(n)){n=0;}
		document.getElementById('dosis').value=n;
	}
}
function getkgtbs(sumber){
	jjg= document.getElementById('jjg').value;
	kg = document.getElementById('kg').value;
	bjr= document.getElementById('bjr').value;
	kg = remove_comma_var(kg);
	jjg= remove_comma_var(jjg);
	if(sumber=='jjg'){
		n = parseFloat(jjg)*parseFloat(bjr);
		if(isNaN(n)){n=0;}
		document.getElementById('kg').value=numberFormat(n);
		i = parseFloat(n)/7000;
		if(isNaN(i)){i=0;}
		document.getElementById('truk').value=numberFormat(i);
	}else{
		n = parseFloat(kg)/parseFloat(bjr);
		if(isNaN(n)){n=0;}
		//document.getElementById('jjg').value=numberFormat(n);
		i = parseFloat(kg)/7000;
		if(isNaN(i)){i=0;}
		document.getElementById('truk').value=numberFormat(i);
	}
}
function cekpres(){
	luas = document.getElementById('luas').value;
	pokok= document.getElementById('pokok').value;
	sat  = document.getElementById('sat').value;
	pres = document.getElementById('pres').value;
	pres = parseFloat(pres);
	pokok= parseFloat(pokok);
	luas = parseFloat(luas);
	
	i = sat.toLowerCase();
	if(i=='ha' && pres>luas){
		document.getElementById('pres').value='';
		alertify.alert("Prestasi tidak boleh lebih besar dari luas blok.");
	}
	if((i=='pkk' || i=='pokok') && pres>pokok){
		document.getElementById('pres').value='';
		alertify.alert("Prestasi tidak boleh lebih besar dari jumlah pokok blok.");
	}
}
function totalhk(){
	kbl = document.getElementById('kbl').value;
	kht= document.getElementById('kht').value;
	khl  = document.getElementById('khl').value;
	bor = document.getElementById('bor').value;
	if(bor==''){bor=0;}
	if(khl==''){khl=0;}
	if(kht==''){kht=0;}
	if(kbl==''){kbl=0;}
	bor = parseFloat(bor);
	khl= parseFloat(khl);
	kht = parseFloat(kht);
	kbl = parseFloat(kbl);
	
	i = bor+khl+kht+kbl;
	document.getElementById('ttlhk').value=i;
	
}

function getsatbarang() {
	barang   = document.getElementById('barang').value;
	
	param = 'method=getsatbarang';
	param += '&barang=' + barang;
	
	
	tujuan = 'kebun_slave_rkhx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("###");
					document.getElementById('satbrg').value = trim(isdt[0]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function addbarang() {
	blok   = document.getElementById('blok').value;
	kegiatan   = document.getElementById('kegiatan').value;
	barang   = document.getElementById('barang').value;
	dosis    = document.getElementById('dosis').value;
	jlhbarang= document.getElementById('jlhbarang').value;
	
	param = 'method=addbarang';
	param += '&barang=' + barang;
	param += '&dosis=' + dosis;
	param += '&jlhbarang=' + jlhbarang;
	param += '&blok=' + blok;
	param += '&kegiatan=' + kegiatan;
	
	
	tujuan = 'kebun_slave_rkhx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contaddbarang').innerHTML = con.responseText;
					setValue2('barang',null);
					setValue('dosis','');
					setValue('jlhbarang','');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function delbrg(key) {
	barang   = document.getElementById('barang').value;
	dosis    = document.getElementById('dosis').value;
	jlhbarang= document.getElementById('jlhbarang').value;
	
	param = 'method=delbrg';
	param += '&key=' + key;
	param += '&barang=' + barang;
	param += '&dosis=' + dosis;
	param += '&jlhbarang=' + jlhbarang;
	
	
	tujuan = 'kebun_slave_rkhx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contaddbarang').innerHTML = con.responseText;
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
	param += '&divisi=' + getValue('divisis');
	param += '&tgl=' + getValue('tglh');
	param += '&notransaksi=' + getValue('notr');
	
	tujuan = 'kebun_slave_rkhx.php';
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
	document.getElementById('contpreview').style.display = 'none';
	cleardetail();
}
function cleardetail() {
	document.getElementById('notransaksi').disabled=true;
	document.getElementById('divisi').disabled=false;
	document.getElementById('asst').disabled=false;
	document.getElementById('mandor1').disabled=false;
	document.getElementById('tgl').disabled=false;
	
	document.getElementById('notransaksi').value='';
	document.getElementById('divisi').value='';
	document.getElementById('asst').value='';
	document.getElementById('mandor1').value='';
	document.getElementById('tgl').value='';
	document.getElementById('contview').innerHTML='';
	document.getElementById('contpreview').style.display = 'none';
	
	document.getElementById('method').value = 'insert';
}

function editht(notransaksi,divisi,asisten,mandor1,tgl) {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('method').value = 'update';
	
	setValue('notransaksi',notransaksi);
	setValue('divisi',divisi);
	setValue('asst',asisten);
	setValue('mandor1',mandor1);
	setValue('tgl',tgl);
	previewdata();
}


function edit(notransaksi,nourut) {
	param = 'method=edit' + '&notransaksi=' + notransaksi + '&nourut=' + nourut;
	tujuan = 'kebun_slave_rkhx.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("##");
					document.getElementById('methoddetail').value = 'update';
					document.getElementById('contaddbarang').innerHTML = data[0];
					
					setValue('nourut',nourut);
					setValue2('kegiatan',trim(data[2]));
					setTimeout(() => {
						getdetailkeg();
						setValue2('blok',trim(data[1]));
						setValue('pres',trim(data[7]));
					}, 800);
					setValue('luas',data[3]);
					setValue('pokok',data[4]);
					setValue('rotasi',trim(data[5]));
					setValue('sat',trim(data[6]));
					setValue('kbl',trim(data[8]));
					setValue('kht',trim(data[9]));
					setValue('khl',trim(data[10]));
					setValue('bor',trim(data[11]));
					ttlhk = parseFloat(data[8])+parseFloat(data[9])+parseFloat(data[10])+parseFloat(data[11]);
					setValue('ttlhk',ttlhk);
					setValue('jjg',trim(data[12]));
					setValue('kg',trim(data[13]));
					setValue('truk',trim(data[14]));
					setValue2('mandor',trim(data[15]));
					setValue('jlhbrg',trim(data[16]));
					
					setValue('dosis','');
					setValue('jlhbarang','');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function delht(notransaksi) {
	param = 'method=delete' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_rkhx.php';
	if(confirm(' Anda yakin ?')){
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
function deldetail(notransaksi,nourut) {
	param = 'method=deletedetail' + '&notransaksi=' + notransaksi + '&nourut=' + nourut;
	tujuan = 'kebun_slave_rkhx.php';
	if(confirm(' Anda yakin ?')){
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					canceldetail();
					loaddatadetail();
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
	alertify.alert(isipesan);
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
	document.getElementById('notr').value='';
	document.getElementById('divisis').value='';
	document.getElementById('tglh').value='';
	
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('contpreview').style.display = 'none';
	
	loaddata(0);
}



function previewdata(tipe) {
	notransaksi = document.getElementById('notransaksi').value;
	divisi   	= document.getElementById('divisi').value;
	asst  		= document.getElementById('asst').value;
	mandor1  	= document.getElementById('mandor1').value;
	tgl   		= document.getElementById('tgl').value;
	
	if (divisi == '') {
		notif('divisi','Kode Divisi wajib diisi.'); return;
	}
	if (asst == '' && mandor1 == '') {
		notif('asst','Assisten Atau Mandor 1 wajib diisi.'); return;
	}
	if (tgl == '') {
		notif('tgl','Tanggal wajib diisi.'); return;
	}

	document.getElementById('divisi').disabled=true;
	document.getElementById('asst').disabled=true;
	document.getElementById('mandor1').disabled=true;
	document.getElementById('tgl').disabled=true;
	
	param = 'method=previewdata';
	param += '&divisi=' + divisi;
	param += '&asst=' + asst;
	param += '&mandor1=' + mandor1;
	param += '&tgl=' + tgl;
	param += '&notransaksi=' + notransaksi;
	
	if(tipe!='excel'){
		tujuan = 'kebun_slave_rkhx.php';
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
					} else {
						data = con.responseText.split("####");
						document.getElementById('contview').innerHTML=trim(data[0]);
						document.getElementById('notransaksi').value=trim(data[1]);
						document.getElementById('contpreview').style.display = '';
						$(document).ready(function() {
							$('.select2').select2({
								dropdownAutoWidth:true
							});
						});
						$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
							$(this).closest(".select2-container").siblings('select:enabled').select2('open');
						});

						loaddatadetail();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}else{
		ev = "event";
		showDialog1('Preview', "<iframe frameborder=0 style='width:895px;height:400px'" +
			" src='kebun_slave_rkhx.php?" + param + "'></iframe>", '900', '400', ev);
		var dialog = document.getElementById('dynamic1');
		dialog.style.top = '50px';
		dialog.style.left = '15%';
	}
}

function savedetail(){
	param = "";
	param += "&notransaksi="+getValue('notransaksi');
	param += "&divisi="+getValue('divisi');
	param += "&asst="+getValue('asst');
	param += "&mandor1="+getValue('mandor1');
	param += "&tgl="+getValue('tgl');
	param += "&blok="+getValue('blok');
	param += "&kegiatan="+getValue('kegiatan');
	param += "&luas="+getValue('luas');
	param += "&pokok="+getValue('pokok');
	param += "&rotasi="+getValue('rotasi');
	param += "&sat="+getValue('sat');
	param += "&pres="+getValue('pres');
	param += "&kbl="+getValue('kbl');
	param += "&khl="+getValue('khl');
	param += "&kht="+getValue('kht');
	param += "&bor="+getValue('bor');
	param += "&ttlhk="+getValue('ttlhk');
	param += "&jjg="+getValue('jjg');
	param += "&kg="+getValue('kg');
	param += "&truk="+getValue('truk');
	param += "&mandor="+getValue('mandor');
	param += "&jlhbrg="+getValue('jlhbrg');
	param += "&nourut="+getValue('nourut');
	
	param += "&barang="+getValue('barang');
	param += "&dosis="+getValue('dosis');
	param += "&jlhbarang="+getValue('jlhbarang');
	method=document.getElementById('methoddetail').value;
	param += "&method="+method;
	
	if (getValue('notransaksi') == '') {
		notif('notransaksi','Notransaksi wajib diisi.'); return;
	}
	if (getValue('divisi') == '') {
		notif('divisi','Kode Divisi wajib diisi.'); return;
	}
	if (getValue('asst') == '' && getValue('mandor1') == '') {
		notif('asst','Assisten Atau Mandor 1 wajib diisi.'); return;
	}
	if (getValue('tgl') == '') {
		notif('tgl','Tanggal wajib diisi.'); return;
	}
	if (getValue('blok') == '') {
		notif('blok','Blok wajib diisi.'); return;
	}
	if (getValue('kegiatan') == '') {
		notif('kegiatan','Kegiatan wajib diisi.'); return;
	}
	if (getValue('pres') == '') {
		notif('pres','Prestasi kerja wajib diisi.'); return;
	}
	if (getValue('ttlhk') == '') {
		notif('ttlhk','Tenaga kerja wajib diisi.'); return;
	}
	if (getValue('mandor') == '') {
		notif('mandor','Mandor wajib diisi.'); return;
	}
	
	tujuan='kebun_slave_rkhx.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    unlockScreen();
                } else {
					alertify.alert("Done");
					canceldetail();
					loaddatadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}

function canceldetail(){
	document.getElementById('contaddbarang').innerHTML = "";
	setValue('nourut','');
	setValue2('blok','');
	setValue2('kegiatan','');
	setValue('luas','');
	setValue('pokok','');
	setValue('rotasi','');
	setValue('sat','');
	setValue('pres','');
	setValue('kbl','');
	setValue('khl','');
	setValue('kht','');
	setValue('bor','');
	setValue('ttlhk','');
	setValue('jjg','');
	setValue('kg','');
	setValue('truk','');
	setValue2('mandor','');
	setValue('jlhbrg','');
	setValue('dosis','');
	setValue('jlhbarang','');
	setValue('satbrg','');
	setValue2('barang','');
	document.getElementById('barang').disabled = false;
	setValue('methoddetail','insert');
}

function loaddatadetail() {
	param = 'method=loaddatadetail';
	param += '&notransaksi=' + getValue('notransaksi');
	
	tujuan = 'kebun_slave_rkhx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(notransaksi) {
	param = 'method=posting' + '&notransaksi=' + notransaksi;
	
	if(confirm(' Anda yakin ?')){
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert(trim(con.responseText));
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function unposting(notransaksi) {
	param = 'method=unposting' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_rkhx.php';
	
	if(confirm(' Anda yakin ?')){
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