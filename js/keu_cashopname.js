function numberFormat(number, digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	//Seperates the components of the number
	var components = (parseFloat(number).toFixed(digit)).split(".");
	//Comma-fies the first part
	components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	//Combines the two sections
	return components.join(".");
}


function displayList() {
	setValue2('notranssch',null);
	setValue2('unitsch',null);
	setValue2('periodesch',null);
	setValue2('noakunsch',null);
	setValue2('minggusch',null);

	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {
	notransaksi = document.getElementById('notranssch').value;
	unit 		= document.getElementById('unitsch').value;
	periode 	= document.getElementById('periodesch').value;
	noakun 		= document.getElementById('noakunsch').value;
	mingguke 	= document.getElementById('minggusch').value;


	param = 'method=loaddata&page=' + page;
	if (notransaksi != '') {
		param += '&notranssch=' + notransaksi;
	}
	if (unit != '') {
		param += '&unitsch=' + unit;
	}
	if (periode != '') {
		param += '&periodesch=' + periode;
	}
	if (noakun != '') {
		param += '&noakunsch=' + noakun;
	}
	if (mingguke != '') {
		param += '&minggusch=' + mingguke;
	}

	tujuan = 'keu_slave_cashopname.php';
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

function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cancel();
}

function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;

	document.getElementById('notrans').disabled = true;
	document.getElementById('notrans').value = '';

	document.getElementById('unit').disabled = false;
	document.getElementById('unit').value = '';

	document.getElementById('periode').disabled = false;
	document.getElementById('periode').value = '';

	document.getElementById('noakun').disabled = false;
	document.getElementById('noakun').value = '';

	document.getElementById('minggu').disabled = false;
	document.getElementById('minggu').value = '';
	
	setValue2('notrans',null);
	setValue2('unit',null);
	setValue2('periode',null);
	setValue2('noakun',null);
	setValue2('minggu',null);
}

function html(notransaksi, mingguke) {
	param = 'method=html' + '&notrans=' + notransaksi + '&minggu=' + mingguke;
	tujuan = 'keu_slave_cashopname.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

async function edit(notransaksi, unit, periode, mingguke, noakun, mode) {
	document.getElementById('notrans').value = notransaksi;
	document.getElementById('unit').value = unit;

	try {
        await getPeriode(); 
        document.getElementById('periode').value = periode; // Set nilai setelah HTML terisi
        document.getElementById('noakun').value = noakun;

        await getMinggu();
        document.getElementById('minggu').value = mingguke; // Set nilai setelah HTML terisi

        document.getElementById('listData').style.display = 'none';
        document.getElementById('header').style.display = 'block';
        
        detail(mode);
        document.getElementById('tomboldetail').disabled = true;
        document.getElementById('notrans').disabled = true;
        document.getElementById('unit').disabled = true;
        document.getElementById('periode').disabled = true;
        document.getElementById('noakun').disabled = true;
        document.getElementById('minggu').disabled = true;

	} catch (error) {
		console.error("Gagal memuat data edit: ", error);
	}
}

function deleteData(notransaksi, page) {
	param = 'method=deleteHeader' + '&notrans=' + notransaksi;
	tujuan = 'keu_slave_cashopname.php';

	alertify.confirm("Delete","Apakah Anda yakin ingin menghapus Data ???<br>Aksi ini hanya bisa dilakukan sekali.",
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
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(notransaksi, page) {
	param = 'method=posting' + '&notrans=' + notransaksi;
	tujuan = 'keu_slave_cashopname.php';
	// if (confirm('Apakah Anda yakin ingin posting Data ???\nAksi ini hanya bisa dilakukan sekali.')) {
	// 	post_response_text(tujuan, param, respog);
	// }

	alertify.confirm("Posting","Apakah Anda yakin ingin posting Data ???<br>Aksi ini hanya bisa dilakukan sekali.",
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
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveHeader() {
	// notransaksi = document.getElementById('notrans').value;
	unit 		= document.getElementById('unit').value;
	periode 	= document.getElementById('periode').value;
	noakun 		= document.getElementById('noakun').value;
	minggu 		= document.getElementById('minggu').value;
	method		= document.getElementById('method').value;

	if (unit == '' || periode == '' || noakun == '' || minggu == '') {
		alertify.alert('Lengkapi Pengisian');
		return;
	}

	param = '';
	// param += 'notrans=' + notransaksi;
	param += '&unit='	 + unit;
	param += '&periode=' + periode;
	param += '&noakun='  + noakun;
	param += '&minggu='  + minggu;
	param += '&method='	 + method;

	tujuan = 'keu_slave_cashopname.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('notrans').value = con.responseText;
					detail('baru');
					document.getElementById('tomboldetail').disabled = true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function detail(mode = 'baru') {
	notransaksi = document.getElementById('notrans').value;
	unit = document.getElementById('unit').value;
	periode = document.getElementById('periode').value;
	noakun = document.getElementById('noakun').value;
	minggu = document.getElementById('minggu').value;

	param = 'method=detail';
	param += '&notrans=' + notransaksi;
	param += '&unit='	 + unit;
	param += '&periode=' + periode;
	param += '&noakun='  + noakun;
	param += '&minggu='  + minggu;
	param += '&mode='	 + mode;

	tujuan = 'keu_slave_cashopname.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;

					loaddatadetail(notransaksi, mode);

					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetail(notransaksi, mode) {
	unit = document.getElementById('unit').value;
	periode = document.getElementById('periode').value;
	noakun = document.getElementById('noakun').value;
	minggu = document.getElementById('minggu').value;

	param = 'method=loaddatadetail';
	param += '&notrans=' + notransaksi;
	param += '&mode='+mode;
	param += '&unit='	 + unit;
	param += '&periode=' + periode;
	param += '&noakun='  + noakun;
	param += '&minggu='  + minggu;

	tujuan = 'keu_slave_cashopname.php';
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

function savedetail(mode) {
	notransaksi	= document.getElementById('notrans').value;
	unit 		= document.getElementById('unit').value;
	periode		= document.getElementById('periode').value;
	noakun		= document.getElementById('noakun').value;
	minggu		= document.getElementById('minggu').value;

	jmlfisik	= document.getElementById('jmlfisik').value;
	jumlah		= document.getElementById('jumlah').value;
	nominal		= document.getElementById('nominal').value;
	
	method		= document.getElementById('metdetail').value;

	param = 'notrans=' + notransaksi;
	param += '&unit=' + unit;
	param += '&periode=' + periode;
	param += '&noakun=' + noakun;
	param += '&minggu=' + minggu;
	
	param += '&jmlfisik=' + jmlfisik;
	param += '&jumlah=' + jumlah;
	param += '&nominal=' + nominal;

	param += '&mode=' + mode;
	param += '&method=' + method;
	tujuan = 'keu_slave_cashopname.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (mode == 'edit') {
						detail('edit');
					} else {
						detail('baru');
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedetail(notransaksi, jumlahfisik, jumlah, mode) {
	param = 'method=deletedetail';
	param += '&notrans=' +  notransaksi;
	param += '&jmlfisik=' + jumlahfisik;
	param += '&jumlah=' + jumlah;
	param += '&mode=' + mode;
	tujuan = 'keu_slave_cashopname.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					detail(mode);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPeriode() {
	return new Promise((resolve, reject) => {
		unit	= document.getElementById('unit').value;
		param = 'method=getPeriode';
		param += '&unit=' + unit;
	
		tujuan = 'keu_slave_cashopname.php';
		
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    let data = con.responseText.split('####');
                    document.getElementById('periode').innerHTML = data[0];
                    document.getElementById('noakun').innerHTML = data[1];
                    resolve();
                } else {
                    busy_off();
                    reject();
                }
            }
		}
		
	});
}


function getMinggu() {
	return new Promise((resolve, reject) => { 
		periode	= document.getElementById('periode').value;
	
		param = 'method=getMinggu';
		param += '&periode=' + periode;
	
		tujuan = 'keu_slave_cashopname.php';
		
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    document.getElementById('minggu').innerHTML = con.responseText;
                    resolve();
                } else {
                    busy_off();
                    reject();
                }
            }
		}
	});
}

function hitungNominal() {
	jumlahfisik = document.getElementById('jmlfisik').value;
	jumlah		= document.getElementById('jumlah').value;

	if (jumlahfisik == '') {
		jumlahfisik = 0;
	}

	jumlahfisik = parseFloat(jumlahfisik);
	jumlah 		= parseFloat(jumlah.replaceAll(',',''));

	nominal		= (jumlahfisik * jumlah);

	if (nominal <= 0) {
		nominal = 0;
	}

	document.getElementById('nominal').value = numberFormat(nominal,2)
}