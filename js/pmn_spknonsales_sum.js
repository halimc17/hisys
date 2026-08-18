function delet(nospk,jenis) {
	param='method=delete';
	param+='&nospk='+nospk+'&jenis='+jenis;
    tujuan='pmn_spknonsales_sum_slave.php';
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
	
	kodept                  = document.getElementById('kodept').value;
	kodebarang              = document.getElementById('kodebarang').value;

	nospk                   = document.getElementById('nospk').value;
	jenis                   = document.getElementById('jenis').value;
	tanggal                 = document.getElementById('tanggal').value;
	transportir             = document.getElementById('transportir').value;
	kuantitas               = document.getElementById('kuantitas').value;
	kuantitas=remove_comma_var(kuantitas);

	namakapal               = document.getElementById('namakapal').value;
	namaponton               = document.getElementById('namaponton').value;
	pelabuhanmuat           = document.getElementById('pelabuhanmuat').value;
	pelabuhantujuan         = document.getElementById('pelabuhantujuan').value;
	tanggalsurvey1          = document.getElementById('tanggalsurvey1').value;
	tanggalsurvey2          = document.getElementById('tanggalsurvey2').value;
	tanggalkedatangan       = document.getElementById('tanggalkedatangan').value;
	kota                    = document.getElementById('kota').value;
	rupiah                  = document.getElementById('rupiah').value;

	tandatangan1            = document.getElementById('tandatangan1').value;
	tandatangan2            = document.getElementById('tandatangan2').value;
	jenispekerjaan          = document.getElementById('jenispekerjaan').value;
	fee                     = document.getElementById('fee').value;
	note                    = document.getElementById('note').value;
	surveyor                    = document.getElementById('surveyor').value;



	method            = document.getElementById('method').value;
	if (tanggal == '' || transportir == '' || kuantitas == '' || namakapal == '') {
		alert('Field Was Empty');
		return false;
	}

	param += 'kodept=' + kodept + '&kodebarang=' + kodebarang;
	param += '&nospk=' + nospk + '&jenis=' + jenis + '&tanggal=' + tanggal + '&transportir=' + transportir + '&kuantitas=' + kuantitas;
	param += '&namakapal=' + namakapal + '&pelabuhanmuat=' + pelabuhanmuat + '&pelabuhantujuan=' + pelabuhantujuan;
	param += '&tanggalsurvey1=' + tanggalsurvey1 + '&tanggalsurvey2=' + tanggalsurvey2 + '&tanggalkedatangan=' + tanggalkedatangan;
	param += '&kota=' + kota  + '&rupiah=' + rupiah + '&method=' + method+ '&surveyor=' + surveyor;
	param += '&tandatangan1=' + tandatangan1  + '&tandatangan2=' + tandatangan2 + '&jenispekerjaan=' + jenispekerjaan;
	param += '&fee=' + fee  + '&note=' + note+ '&namaponton=' + namaponton;

	tujuan = 'pmn_spknonsales_sum_slave.php';
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

function getpage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function loaddata(page) {
	nospksch=document.getElementById('nospksch').value;
	kodeptsch=document.getElementById('kodeptsch').value;
	jenis=document.getElementById('jenis').value;
	param='method=loaddata';
	param+='&nospksch='+nospksch+'&kodeptsch='+kodeptsch+'&jenis='+jenis+'&page='+page;
    tujuan='pmn_spknonsales_sum_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				} else {
					// document.getElementById('container').innerHTML=con.responseText;
					
					isdt=con.responseText.split("####");
					document.getElementById('container').innerHTML=isdt[0];
					document.getElementById('footdata').innerHTML=isdt[1];
				}
			} else {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}

function cancelsch(){
document.getElementById('nospksch').value='';
document.getElementById('kodeptsch').value='';
loaddata(0);
}



function fillField(nospk,jenis) {
	nospk = nospk;
	param = 'method=getEditData' + '&nospk=' + nospk+ '&jenis=' + jenis;
	tujuan = 'pmn_spknonsales_sum_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('method').value = 'update';
					ar = con.responseText.split("###");
					document.getElementById('kodept').value = ar[0];
					document.getElementById('kodebarang').value = ar[3];
					document.getElementById('nospk').value = ar[4];
					document.getElementById('tanggal').value = ar[6];
					document.getElementById('transportir').value = ar[7];
					document.getElementById('kuantitas').value = ar[8];
					
					document.getElementById('namakapal').value=ar[9];
					document.getElementById('pelabuhanmuat').value=ar[10];
					document.getElementById('pelabuhantujuan').value=ar[11];
					document.getElementById('tanggalsurvey1').value=ar[12];
					document.getElementById('tanggalsurvey2').value=ar[13];
					document.getElementById('tanggalkedatangan').value = ar[14];
					document.getElementById('kota').value=ar[15];

					document.getElementById('tandatangan1').value = ar[16];
					document.getElementById('tandatangan2').value=ar[17];
					document.getElementById('rupiah').value = ar[18];
					document.getElementById('jenispekerjaan').value=ar[19];
					document.getElementById('fee').value=ar[20];
					document.getElementById('note').value=ar[21];
					document.getElementById('surveyor').value=ar[22];
					
					document.getElementById('namaponton').value=ar[23];
					// getkapalponton(ar[9],ar[23]);
					

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
	document.getElementById('transportir').value = '';
	document.getElementById('kuantitas').value = '';
	document.getElementById('surveyor').value = '';
	
	
	document.getElementById('namakapal').value='';
	document.getElementById('namaponton').value='';
	document.getElementById('pelabuhanmuat').value='';
	document.getElementById('pelabuhantujuan').value='';
	document.getElementById('tanggalsurvey1').value='';
	document.getElementById('tanggalsurvey2').value='';
	document.getElementById('tanggalkedatangan').value = '';
	document.getElementById('kota').value='';

	document.getElementById('tandatangan2').value='';
	document.getElementById('jenispekerjaan').value='';
	document.getElementById('fee').value='';
	document.getElementById('note').value='';
	
	document.getElementById('tandatangan1').value = '';
	document.getElementById('kodept').value = '';
	document.getElementById('kodebarang').value = '';
	document.getElementById('rupiah').value = '';
}