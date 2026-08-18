function delet(nospk,jenis) {
	param='method=delete';
	param+='&nospk='+nospk+'&jenis='+jenis;
    tujuan='pmn_spk_spp_slave.php';
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
	nokontrak = document.getElementById('nokontrak').value;
	kodept = document.getElementById('kodept').value;
	tanggalkontrak = document.getElementById('tanggalkontrak').value;
	kodecustomer = document.getElementById('kodecustomer').value;
	kodebarang = document.getElementById('kodebarang').value;

	nospk = document.getElementById('nospk').value;
	jenis = document.getElementById('jenis').value;
	tanggal = document.getElementById('tanggal').value;
	transportir = document.getElementById('transportir').value;
	kuantitas = document.getElementById('kuantitas').value;
	kuantitas=remove_comma_var(kuantitas);

	asalkargo = document.getElementById('asalkargo').value;
	namakapal = document.getElementById('namakapal').value;
	namaponton = document.getElementById('namaponton').value;
	tanggalkedatangan1 = document.getElementById('tanggalkedatangan1').value;
	tanggalkedatangan2 = document.getElementById('tanggalkedatangan2').value;

	tandatangan = document.getElementById('tandatangan').value;

	rupiah = document.getElementById('rupiah').value;

	method = document.getElementById('method').value;
	bongkarmuat = document.getElementById('bongkarmuat').value;
	kota = document.getElementById('kota').value;
	if (tanggal == '' || transportir == '' || kuantitas == '' || namakapal == '') {
		alert('Field Was Empty');
		return false;
	}
	
	if(namakapal.substr(0,3)=='TRK' && namaponton!=''){
			alert('Jenis Angkutan yang Anda pilih adalah Truck, Nama ponton tidak perlu dilengkapi / The type of transportation you choose is a truck, barge name is required');
			return false;
		}

	param += 'nokontrak=' + nokontrak + '&kodept=' + kodept + '&tanggalkontrak=' + tanggalkontrak + '&kodecustomer=' + kodecustomer + '&kodebarang=' + kodebarang;
	param += '&nospk=' + nospk + '&jenis=' + jenis + '&tanggal=' + tanggal + '&transportir=' + transportir + '&kuantitas=' + kuantitas;
	param += '&asalkargo=' + asalkargo + '&tanggalkedatangan1=' + tanggalkedatangan1+ '&tanggalkedatangan2=' + tanggalkedatangan2 + '&namaponton=' + namaponton+ '&namakapal=' + namakapal;
	param += '&tandatangan=' + tandatangan  + '&rupiah=' + rupiah + '&method=' + method + '&bongkarmuat=' + bongkarmuat+ '&kota=' + kota;

	tujuan = 'pmn_spk_tkbm_slave.php';
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
	tujuan = 'pmn_spk_tkbm_slave.php';
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

function edit(nokontrak, kodept, tanggalkontrak, kodecustomer, kodebarang,
	nospk, jenis, tanggal, transportir, kuantitas,
	asalkargo, namakapal,tanggalkedatangan1,tanggalkedatangan2, tandatangan, rupiah,bongkarmuat,kota,namaponton) {
	document.getElementById('nospk').value = nospk;
	document.getElementById('tanggal').value = tanggal;
	document.getElementById('transportir').value = transportir;
	document.getElementById('kuantitas').value = kuantitas;
	document.getElementById('asalkargo').value = asalkargo;
	document.getElementById('tanggalkedatangan1').value = tanggalkedatangan1;
	document.getElementById('tanggalkedatangan2').value = tanggalkedatangan2;
	document.getElementById('namakapal').value = namakapal;
	document.getElementById('tandatangan').value = tandatangan;
	document.getElementById('rupiah').value = rupiah;
	document.getElementById('bongkarmuat').value = bongkarmuat;
	document.getElementById('kota').value = kota;
	document.getElementById('namaponton').value = namaponton;
	document.getElementById('method').value = 'update';
}

function cancel() {
	document.getElementById('method').value = 'insert';
	document.getElementById('nospk').value = '';
	document.getElementById('tanggal').value = '';
	document.getElementById('transportir').value = '';
	document.getElementById('kuantitas').value = '';
	document.getElementById('asalkargo').value= '';
	document.getElementById('tanggalkedatangan1').value= '';
	document.getElementById('tanggalkedatangan2').value= '';
	
	document.getElementById('kota').value = '';
	document.getElementById('namaponton').value = '';
	document.getElementById('namakapal').value = '';
	document.getElementById('tandatangan').value = '';
	document.getElementById('rupiah').value = '';
	document.getElementById('bongkarmuat').value = '';
}