function delet(nospk,jenis) {
	param='method=delete';
	param+='&nospk='+nospk+'&jenis='+jenis;
    tujuan='pmn_spk_sub_slave.php';
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

function getalamattibamuatan(){
	pelabuhantujuan             = document.getElementById('pelabuhantujuan').value;
	document.getElementById('alamattibamuatan').value=pelabuhantujuan;
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
	kuantitas                 = document.getElementById('kuantitas').value;
	
	kota                    = document.getElementById('kota').value;
	rupiah                  = document.getElementById('rupiah').value;
	tandatangan             = document.getElementById('tandatangan').value;
	tandatangan2             = document.getElementById('tandatangan2').value;
	jabatan2             = document.getElementById('jabatan2').value;
	
	ruanglingkup             = document.getElementById('ruanglingkup').value;
	tarif             = document.getElementById('tarif').value;
	pembayaran             = document.getElementById('pembayaran').value;
	tanggungjawab             = document.getElementById('tanggungjawab').value;
	
	pengalihan             = document.getElementById('pengalihan').value;
	transportir             = document.getElementById('transportir').value;
	namaponton             = document.getElementById('namaponton').value;
	namakapal             = document.getElementById('namakapal').value;
	pelabuhantujuan             = document.getElementById('pelabuhantujuan').value;
	nobl             = document.getElementById('nobl').value;
	jadwaltibakapal             = document.getElementById('jadwaltibakapal').value;
	alamattibamuatan             = document.getElementById('alamattibamuatan').value;
	surveyor             = document.getElementById('surveyor').value;
	kuantitas=remove_comma_var(kuantitas);
	method            = document.getElementById('method').value;
	if (tanggal == '' || kuantitas == '' || nobl == '') {
		alert('Field Was Empty');
		return false;
	}
	
	if(namakapal.substr(0,3)=='TRK' && namaponton!=''){
			alert('Jenis Angkutan yang Anda pilih adalah Truck, Nama ponton tidak perlu dilengkapi / The type of transportation you choose is a truck, barge name is required');
			return false;
		}

	param += 'nokontrak=' + nokontrak + '&kodept=' + kodept + '&tanggalkontrak=' + tanggalkontrak + '&kodecustomer=' + kodecustomer + '&kodebarang=' + kodebarang;
	param += '&nospk=' + nospk + '&jenis=' + jenis + '&tanggal=' + tanggal + '&kuantitas=' + kuantitas;
	param += '&ruanglingkup=' + ruanglingkup + '&tarif=' + tarif + '&pembayaran=' + pembayaran+ '&tanggungjawab=' + tanggungjawab;
	param += '&pengalihan=' + pengalihan + '&namakapal=' + namakapal + '&pelabuhantujuan=' + pelabuhantujuan+ '&nobl=' + nobl;
	param += '&jadwaltibakapal=' + jadwaltibakapal + '&alamattibamuatan=' + alamattibamuatan+ '&surveyor=' + surveyor;
	param += '&kota=' + kota + '&rupiah=' + rupiah + '&tandatangan=' + tandatangan + '&method=' + method;
	param += '&tandatangan2=' + tandatangan2 + '&jabatan2=' + jabatan2;
	param += '&transportir=' + transportir + '&namaponton=' + namaponton;
	tujuan = 'pmn_spk_sub_slave.php';
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
	tujuan = 'pmn_spk_sub_slave.php';
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
	tujuan = 'pmn_spk_sub_slave.php';
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
					
					document.getElementById('kuantitas').value = ar[7];
					document.getElementById('ruanglingkup').value = ar[8];
					document.getElementById('tarif').value = ar[9];
					
					
					document.getElementById('pembayaran').value = ar[10];
					document.getElementById('tanggungjawab').value = ar[11];
					document.getElementById('pengalihan').value = ar[12];
					document.getElementById('namakapal').value = ar[13];
					document.getElementById('pelabuhantujuan').value = ar[14];
					document.getElementById('nobl').value = ar[15];
					document.getElementById('jadwaltibakapal').value = ar[16];
					document.getElementById('alamattibamuatan').value = ar[17];
					document.getElementById('tandatangan').value = ar[18];
					document.getElementById('rupiah').value = ar[19];
					document.getElementById('kota').value = ar[20];
					document.getElementById('surveyor').value=ar[21];
					
					document.getElementById('tandatangan2').value=ar[22];
					
					document.getElementById('jabatan2').value=ar[23];
					
					
					document.getElementById('transportir').value=ar[24];
					document.getElementById('namaponton').value=ar[25];
				
					// getkapalponton(ar[13],ar[25]);
					// alert(ar[24]);

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
	document.getElementById('surveyor').value = '';
	document.getElementById('alamattibamuatan').value='';
	document.getElementById('jadwaltibakapal').value='';
	document.getElementById('namakapal').value='';
	document.getElementById('transportir').value='';
	document.getElementById('ruanglingkup').value='';
	document.getElementById('pelabuhantujuan').value='';
	document.getElementById('namaponton').value='';
	document.getElementById('kota').value='';
	document.getElementById('pengalihan').value='';
	document.getElementById('nobl').value='';
	document.getElementById('tarif').value='';
	document.getElementById('pembayaran').value='';
	document.getElementById('tanggungjawab').value='';
	document.getElementById('tandatangan').value = '';
	document.getElementById('rupiah').value = '';
	document.getElementById('tandatangan2').value = '';
	document.getElementById('jabatan2').value = '';
}