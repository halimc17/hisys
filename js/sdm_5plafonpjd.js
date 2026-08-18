function cancel() {
    document.getElementById('regional').value = '';
    document.getElementById('levelkaryawan').value = '';
    document.getElementById('kodegolongan').value = '';
    document.getElementById('tipekaryawan').value = '';
    document.getElementById('jenis').value = '';
    document.getElementById('jabatan').value = '';
    document.getElementById('tujuan').value = '';
    document.getElementById('uangmakandriver').value = '';
    document.getElementById('rupiah').value = '0';
    document.getElementById('kode').value = '';
    document.getElementById('pt').value = '';
    document.getElementById('unit').value = '';
    document.getElementById('method').value = 'insert';
}

function getunit() {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
    param = 'pt=' + pt + '&method=getunit';
	tujuan = 'sdm_slave_5plafonpjd.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('unit').innerHTML = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadData(num) {
	regional    = getValue('regionalsch');
	levelkaryawan= getValue('levelkaryawansch');
	tipekaryawan= getValue('tipekaryawansch');
	kodegolongan= getValue('kodegolongansch');
	jenis       = getValue('jenissch');
	jabatan     = getValue('jabatansch');
	tuj         = getValue('tujuansch');
	umpremi     = getValue('umpremisch');
	pt          = getValue('ptsch');
	unit        = getValue('unitsch');
	
	param  = 'method=loadData&page=' + num;
	param += '&regional=' + regional;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&levelkaryawan=' + levelkaryawan;
	param += '&kodegolongan=' + kodegolongan;
	param += '&jenis=' + jenis;
	param += '&jabatan=' + jabatan;
	param += '&tujuan=' + tuj;
	param += '&umpremi=' + umpremi;
	param += '&pt=' + pt;
	param += '&unit=' + unit;
	
	tujuan      = 'sdm_slave_5plafonpjd';
    post_response_text(tujuan + '.php', param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
					leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getPage(){
	pg      = document.getElementById('pages');
	pg      = pg.options[pg.selectedIndex].value;
	paged   = parseFloat(pg) - 1;
	loadData(paged);
}

function fillfield(kode,pt,unit,regiontujuan,tipekaryawan,levelkaryawan,golongan,jenis,jumlah) {
	setValue('pt',pt);
	setValue('unit',unit);
	setValue('regional',regiontujuan);
	setValue('tipekaryawan',tipekaryawan);
	setValue('levelkaryawan',levelkaryawan);
	setValue('kodegolongan',golongan);
	setValue('jenis',jenis);
	setValue('kode',kode);
	setValue('rupiah',jumlah);
    document.getElementById('method').value = 'update';

    document.getElementById('pt').disabled=true;
	document.getElementById('unit').disabled=true;
	document.getElementById('regional').disabled=true;
	document.getElementById('jenis').disabled=true;
	document.getElementById('levelkaryawan').disabled=true;
	document.getElementById('kodegolongan').disabled=true;
	document.getElementById('tipekaryawan').disabled=true;
}

function deleteData(kode) {
    param = 'kode=' + kode + '&method=delete';
    tujuan = 'sdm_slave_5plafonpjd.php';
    if (confirm('Anda yakin hapus item ini?'))
        post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData(0);
                    cancel();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function save() {
	regional       = document.getElementById('regional').value;
	tipekaryawan   = document.getElementById('tipekaryawan').value;
	levelkaryawan   = document.getElementById('levelkaryawan').value;
	kodegolongan   = document.getElementById('kodegolongan').value;
	jenis          = getValue('jenis');
	jabatan        = getValue('jabatan');
	tujuan         = getValue('tujuan');
	kode           = getValue('kode');
	uangmakandriver= getValue('uangmakandriver');
	rupiah         = trim(document.getElementById('rupiah').value);
	method         = trim(document.getElementById('method').value);
	
	tuj            = document.getElementById('tujuan');
	umd            = document.getElementById('uangmakandriver');
	pt             = document.getElementById('pt').value;
	unit           = document.getElementById('unit').value;
	jlhopttuj      = (tuj.length)-1;
	jlhoptumd      = (umd.length)-1;
	
    param = 'jabatan=' + jabatan + '&regional=' + regional + '&levelkaryawan=' + levelkaryawan + '&kodegolongan=' + kodegolongan + '&jenis=' + jenis + '&tujuan=' + tujuan + '&uangmakandriver=' + uangmakandriver + '&rupiah=' + rupiah + '&method=' + method;
	param += '&jlhopttuj=' + jlhopttuj;
	param += '&jlhoptumd=' + jlhoptumd;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&kode=' + kode;
	param += '&pt=' + pt;
	param += '&unit=' + unit;
    tujuan = 'sdm_slave_5plafonpjd.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData(0);
                    cancel();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}