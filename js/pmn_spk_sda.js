function delet(nospk,jenis) {
	param='method=delete';
	param+='&nospk='+nospk+'&jenis='+jenis;
    tujuan='pmn_spk_sda_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				} else {
					loaddata();
				}
			} else {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}


function save() {
	param = "";

	nokontrak               = document.getElementById('nokontrak').value;
	kodept                  = document.getElementById('kodept').value;
	tanggalkontrak          = document.getElementById('tanggalkontrak').value;
	kodecustomer            = document.getElementById('kodecustomer').value;
	kodebarang              = document.getElementById('kodebarang').value;

	nospk                   = document.getElementById('nospk').value;
	jenis                   = document.getElementById('jenis').value;
	tanggal                 = document.getElementById('tanggal').value;
	kuantitas               = document.getElementById('kuantitas').value;

	tanggalpelaksanaan       = document.getElementById('tanggalpelaksanaan').value;
	surveyor                    = document.getElementById('surveyor').value;
	kota                    = document.getElementById('kota').value;
	parameter                    = document.getElementById('parameter').value;
	rupiah                  = document.getElementById('rupiah').value;

	tandatangan            = document.getElementById('tandatangan').value;
	tandatangan2            = document.getElementById('tandatangan2').value;
	sample          = document.getElementById('sample').value;
	tempatpelaksanaan          = document.getElementById('tempatpelaksanaan').value;
	pelabuhantujuan          = document.getElementById('pelabuhantujuan').value;
	pekerjaan          = document.getElementById('pekerjaan').value;
	namaponton          = document.getElementById('namaponton').value;
	namakapal          = document.getElementById('namakapal').value;
	transportir          = document.getElementById('transportir').value;
	nosip               = document.getElementById('nosip').value;
	kuantitas=remove_comma_var(kuantitas);

	method            = document.getElementById('method').value;
	if (tanggal == '' || kuantitas == '') {
		alert('Field Was Empty');
		return false;
	}

	param += 'nokontrak=' + nokontrak + '&kodept=' + kodept + '&tanggalkontrak=' + tanggalkontrak + '&kodecustomer=' + kodecustomer + '&kodebarang=' + kodebarang;
	param += '&nospk=' + nospk + '&jenis=' + jenis + '&tanggal=' + tanggal+ '&kuantitas=' + kuantitas;
	param += '&tanggalpelaksanaan=' + tanggalpelaksanaan+ '&surveyor=' + surveyor+ '&parameter=' + parameter;
	param += '&kota=' + kota  + '&rupiah=' + rupiah + '&method=' + method+ '&nosip=' + nosip;
	param += '&pekerjaan=' + pekerjaan  + '&pelabuhantujuan=' + pelabuhantujuan + '&tempatpelaksanaan=' + tempatpelaksanaan+ '&namaponton=' + namaponton;
	param += '&tandatangan=' + tandatangan  + '&tandatangan2=' + tandatangan2 + '&sample=' + sample;
	param += '&transportir=' + transportir  + '&namakapal=' + namakapal;

	tujuan = 'pmn_spk_sda_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cancel();
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddata() {
	nokontrak = document.getElementById('nokontrak').value;
	jenis = document.getElementById('jenis').value;
	param = 'method=loaddata';
	param += '&nokontrak=' + nokontrak + '&jenis=' + jenis;
	tujuan = 'pmn_spk_sda_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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

function fillField(nokontrak,jenis) {
	nokontrak = nokontrak;
	param = 'method=getEditData' + '&nokontrak=' + nokontrak+ '&jenis=' + jenis;
	tujuan = 'pmn_spk_sda_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('method').value = 'update';
					ar = con.responseText.split("###");
					document.getElementById('nospk').value = ar[4];
					document.getElementById('tanggal').value = ar[6];
					document.getElementById('surveyor').value = ar[7];
					document.getElementById('kuantitas').value = ar[8];
					document.getElementById('tanggalpelaksanaan').value = ar[9];
					document.getElementById('kota').value = ar[10];
					document.getElementById('tandatangan').value = ar[11];
					document.getElementById('tandatangan2').value=ar[12];
					document.getElementById('rupiah').value=ar[13];
					document.getElementById('sample').value=ar[14];
					document.getElementById('parameter').value=ar[15];
					
					document.getElementById('tempatpelaksanaan').value=ar[16];
					document.getElementById('pelabuhantujuan').value=ar[17];
					document.getElementById('pekerjaan').value=ar[18];
					document.getElementById('namaponton').value=ar[19];
					document.getElementById('nosip').value=ar[20];
					document.getElementById('transportir').value=ar[21];
					document.getElementById('namakapal').value=ar[22];
					getkapalponton(ar[22],ar[19]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function cancel() {
	document.getElementById('method').value = 'insert';
	document.getElementById('nospk').value = '';
	document.getElementById('tanggal').value = '';
	document.getElementById('kuantitas').value = '';
	document.getElementById('parameter').value = '';
	document.getElementById('surveyor').value = '';

	document.getElementById('tanggalpelaksanaan').value = '';
	document.getElementById('kota').value='';

	document.getElementById('tandatangan2').value='';
	document.getElementById('sample').value='';
	
	document.getElementById('tandatangan').value = '';
	document.getElementById('rupiah').value = '';
	
	document.getElementById('tempatpelaksanaan').value = '';
	document.getElementById('pelabuhantujuan').value = '';
	document.getElementById('pekerjaan').value = '';
	document.getElementById('namaponton').value = '';
	document.getElementById('namakapal').value = '';
	document.getElementById('transportir').value = '';
	document.getElementById('nosip').value = '';
}