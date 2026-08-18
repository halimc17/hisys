function saveTph() {
	kodeorg = document.getElementById('kodeorg');
	kodeorg = kodeorg.options[kodeorg.selectedIndex].value;
	notph = document.getElementById('notph').value;
	notphbesar = document.getElementById('notphbesar').value;
	keterangan = document.getElementById('keterangan').value;
	lat = document.getElementById('lat').value;
	lon = document.getElementById('long').value;
	sts = document.getElementById('status').value;
	luas = document.getElementById('luas').value;
	
	
	
	tomb = document.getElementById('tombol');
	aksi = tomb.getAttribute('state', 2);
	param = 'kodeorg=' + kodeorg + '&notph=' + notph + '&keterangan=' + keterangan + '&aksi=' + aksi;
	param += '&lat=' + lat + '&lon=' + lon + '&sts=' + sts + '&luas=' + luas+ '&notphbesar=' + notphbesar;
	if (kodeorg == '') {
		alert('Kodeorganisasi masih kosong');
	} else if (notph == '') {
		alert('No.Tph Pasih Kosong');
	} else {
		tujuan = 'kebun_slave_5tph.php';
		post_response_text(tujuan, param, respon);
	}

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
					cancelTph();
					getList(0,kodeorg);
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
	getList(paged);
}

function getList(page,kodeorg) {
	if (kodeorg == undefined || kodeorg == '') {
		kodeorgsrc = document.getElementById('kodeorgsrc').value;
	}
	notphsrc = document.getElementById('notphsrc').value;
	
	param = 'aksi=list'; 
	if (kodeorg == undefined || kodeorg == '') {
		param += '&kodeorgsrc=' + kodeorgsrc;
	} else {
		param += '&kodeorg=' + kodeorg; 
	}
	param += '&notphsrc=' + notphsrc;
	param += '&page=' + page;
	tujuan = 'kebun_slave_5tph.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					if(kodeorg!='' || kodeorg!=undefined){
						// document.getElementById('kodeorgsrc').value = '';
						// document.getElementById('notphsrc').value = '';
						document.getElementById('keterangan').value = '';
						if(data[1].length>2){
							document.getElementById('notph').value = data[1];
							document.getElementById('keterangan').value = data[1];
							document.getElementById('notphbesar').innerHTML = data[2];
						}
					}
					if(kodeorg==''){
						document.getElementById('notph').value = '';
						document.getElementById('keterangan').value = '';
						document.getElementById('notphbesar').innerHTML = '';						
					}
					document.getElementById('contain').innerHTML = data[0];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editData(kodeorg, notph,notphbesar, keterangan,lat,lon,luas,sts) {
	//document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('notph').value = notph;
	document.getElementById('notphbesar').innerHTML = notphbesar;
	document.getElementById('keterangan').value = keterangan;
	document.getElementById('lat').value = lat;
	document.getElementById('long').value = lon;
	document.getElementById('status').value = sts;
	document.getElementById('luas').value = luas;
	opt = document.getElementById('kodeorg');
	for (x = 0; x < opt.length; x++) {
		if (opt.options[x].value == kodeorg) {
			opt.options[x].selected = true;
		}
	}
	
	setValue2('kodeorg',kodeorg);
	tomb = document.getElementById('tombol');
	tomb.removeAttribute('state');
	tomb.setAttribute('state', 'edit');
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('notph').disabled = true;
	document.getElementById('notphbesar').disabled = true;
}

function deleteData(kodeorg, notph) {

	param = 'kodeorg=' + kodeorg + '&notph=' + notph + '&aksi=del';
	if (confirm('Delete, are you sure ?')) {
		tujuan = 'kebun_slave_5tph.php';
		post_response_text(tujuan, param, respon);
	}

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					tomb = document.getElementById('tombol');
					tomb.removeAttribute('state');
					tomb.setAttribute('state', 'save');
					document.getElementById('contain').innerHTML = con.responseText;
					getList(0,kodeorg);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancelTph() {
	document.getElementById('keterangan').value = '';
	document.getElementById('notph').value = '';
	document.getElementById('notphbesar').innerHTML = '';
	document.getElementById('lat').value='';
	document.getElementById('long').value='';
	document.getElementById('status').value='A';
	document.getElementById('luas').value='';
	
	tomb = document.getElementById('tombol');
	tomb.removeAttribute('state');
	tomb.setAttribute('state', 'save');
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('notph').disabled = false;
	document.getElementById('notphbesar').disabled = false;
}