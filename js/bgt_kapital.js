function getaruskas(idsumber,idtujuan,akun){
	kodebgt = document.getElementById(idsumber).value;
    param = 'kodebgt=' + kodebgt;
    param += '&akun=' + akun;
    param += '&proses=getaruskas';
    tujuan = 'bgt_slave_kapital.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					data=con.responseText.split("###");
                    document.getElementById(idtujuan).innerHTML = data[0];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getlokasi() {
	kodeorg = document.getElementById('kodeorg').value;
	
	param = 'proses=getlokasi';
	param += '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_kapital.php';
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
function rekap() {
	tahun = document.getElementById('thnBudgetTutup').value;
	
	param = 'proses=rekap';
	param += '&tahun=' + tahun;
	tujuan = 'bgt_slave_kapital.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('rekapkapital').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cariBast(num) {
	param = 'proses=loadData&page=' + num;
	tujuan = 'bgt_slave_kapital.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadData() {
	param = 'proses=loadData';
	tujuan = 'bgt_slave_kapital.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// alertify.alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					//hapus();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveHeader() {
	kaliKan();
	tahunbudget = getValue('tahunbudget');
	// thnBdget=document.getElementById('thnBudgetKap').value;
	kodeorg = getValue('kodeorg');
	//kodeOrg=document.getElementById('kodeOrg').options[document.getElementById('kodeOrg').selectedIndex].value;
	jeniskapital = getValue('jeniskapital');
	//jns=document.getElementById('jnsKapital').options[document.getElementById('jnsKapital').selectedIndex].value;
	keterangan = getValue('keterangan');
	//ket=document.getElementById('ket').value;
	jumlah = getValue('jumlah');
	//jmlhKap=document.getElementById('jmlhKap').value;
	harga = getValue('harga');
	//hrgSatuan=document.getElementById('hrgSatuanKap').value;
	total = getValue('totalrp');
	lokasi = getValue('lokasi');
	aruskas = getValue('aruskas');
	id = getValue('idbgt');
	method=document.getElementById('method').value;
	harga=remove_comma_var(harga);
	total=remove_comma_var(total);
	jumlah=remove_comma_var(jumlah);
	
	
	param = '';
	param += '&tahunbudget=' + tahunbudget + '&kodeorg=' + kodeorg + '&jeniskapital=' + jeniskapital + '&keterangan=' + keterangan;
	param += '&jumlah=' + jumlah + '&harga=' + harga + '&total=' + total + '&lokasi=' + lokasi;
	param += '&aruskas=' + aruskas;
	param += '&id=' + id;
	param += '&proses=' + method;

	tujuan = 'bgt_slave_kapital.php';
	if (tahunbudget == '' || kodeorg == '' || jeniskapital == '' || keterangan == '' || jumlah == '' || jumlah == 0 || harga == 0 || harga == '' || total == 0)
		alertify.alert('Data harus lengkap');
	else
		post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(method=='edit' && con.responseText!=''){
						alertify.alert("Silahkan lakukan sebaran ulang dikarenakan nilai total dengan nilai sebaran tidak sama, terdapat selisih sebesar : "+con.responseText+"");
					}
					document.getElementById('keterangan').value = '';
					document.getElementById('jumlah').value = ''
					document.getElementById('harga').value = ''
					document.getElementById('totalrp').value = ''
					document.getElementById('method').value = 'simpanHeader'
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function deleteData(kunci) {
	param = 'proses=delete&kunci=' + kunci;
	tujuan = 'bgt_slave_kapital.php';
	if (confirm('Anda yakin menghapus..?')) {
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('container1').innerHTML = con.responseText;
					loadData();
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
	jumlah = getValue('jumlah');
	
	harga=remove_comma_var(harga);
	jumlah=remove_comma_var(jumlah);
	
	document.getElementById('totalrp').value = numberFormat(harga * jumlah);
}

function sebaran(kunci, ev) {
	param = 'proses=sebaran&kunci=' + kunci;
	tujuan = 'bgt_slave_kapital.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					tabAction(document.getElementById('tabFRM1'), 1, 'FRM', 2);
					document.getElementById('detailDataSebaran').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clearForm() {
	if (confirm("Anda yakin ingin mengkosongkan form??")) {
		for (sr = 1; sr < 13; sr++) {
			document.getElementById('k' + sr).value = '';
			document.getElementById('persen' + sr).value = '';
		}
	} else {
		return;
	}
}
function simpanSebaran(total, kunci) {
	param = 'proses=updatesebaran&kunci=' + kunci + '&total=' + total;
	k01 = document.getElementById('k1').value;
	k02 = document.getElementById('k2').value;
	k03 = document.getElementById('k3').value;
	k04 = document.getElementById('k4').value;
	k05 = document.getElementById('k5').value;
	k06 = document.getElementById('k6').value;
	k07 = document.getElementById('k7').value;
	k08 = document.getElementById('k8').value;
	k09 = document.getElementById('k9').value;
	k10 = document.getElementById('k10').value;
	k11 = document.getElementById('k11').value;
	k12 = document.getElementById('k12').value;
	param += '&k01=' + k01;
	param += '&k02=' + k02;
	param += '&k03=' + k03;
	param += '&k04=' + k04;
	param += '&k05=' + k05;
	param += '&k06=' + k06;
	param += '&k07=' + k07;
	param += '&k08=' + k08;
	param += '&k09=' + k09;
	param += '&k10=' + k10;
	param += '&k11=' + k11;
	param += '&k12=' + k12;
	param += '&total=' + total;
	tujuan = 'bgt_slave_kapital.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					tabAction(document.getElementById('tabFRM0'), 0, 'FRM', 1);
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function closeBudget() {
	thn = getValue('thnBudgetTutup');
	param = 'proses=tutup&tahun=' + thn;
	if (confirm('Anda yakin mau tutup budget ' + thn + '..?\nSetelah tutup data tidak dapat diubah'));{
		tujuan = 'bgt_slave_kapital.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					window.location.reload();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ubahNilai(total) {
	tot = 0;
	for (x = 1; x < 13; x++) {
		if (document.getElementById('persen' + x).value == '')
			document.getElementById('persen' + x).value = 0;
		tot += parseFloat(document.getElementById('persen' + x).value);
	}
	if (tot > 0) {
		for (x = 1; x < 13; x++) {
			document.getElementById('k' + x).value = 0;
		}
	}
	for (x = 1; x < 13; x++) {
		if (document.getElementById('persen' + x).value != '' || document.getElementById('persen' + x).value != 0) {
			z = parseFloat(document.getElementById('persen' + x).value);
			if (tot > 0)
				document.getElementById('k' + x).value = ((z / tot) * total).toFixed(2);
		}
	}
}

function fillfield(id){
	param = 'proses=fillfield' + '&id=' + id;
	tujuan = 'bgt_slave_kapital.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("##");
					document.getElementById('tahunbudget').value=data[0];
					document.getElementById('kodeorg').value=data[1];
					document.getElementById('jeniskapital').value=data[2];
					document.getElementById('lokasi').value=data[7];
					document.getElementById('aruskas').innerHTML="<option value='"+ data[3] +"'>"+ data[4] +"</option>";
					document.getElementById('jumlah').value=data[5];
					document.getElementById('harga').value=data[6];
					document.getElementById('totalrp').value=data[7];
					document.getElementById('lokasi').value=data[8];
					document.getElementById('keterangan').value=data[9];
					
					document.getElementById('idbgt').value=id;
					document.getElementById('method').value='edit';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}