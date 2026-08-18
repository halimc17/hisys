
function saveht(parameter) {
	method='saveht';
	tujuan='keu_adjustmentlaporankeuangan_slave.php';
    var passP = parameter.split('###');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
		param += "&"+passP[i]+"="+getValue(passP[i]);
    }
	param += '&method=' + method;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
                } else {
					loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}



function cancelht(){
	document.getElementById('kodeunit').value='';
	document.getElementById('periode').value='';
	document.getElementById('jenis').value='';
	document.getElementById('code').value='';
	document.getElementById('jumlah').value='';
	document.getElementById('keterangan').value='';
    document.getElementById('notransaksi').value='';
}

function editht(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'keu_adjustmentlaporankeuangan_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					ar = con.responseText.split("###");
					document.getElementById('listdata').style.display='none';
					document.getElementById('header').style.display='block';
					document.getElementById('kodeunit').value = ar[0];
					document.getElementById('periode').value = ar[1];
					document.getElementById('jenis').value = ar[2];
					document.getElementById('code').value=ar[3];
					document.getElementById('jumlah').value=ar[4];
					document.getElementById('keterangan').value=ar[5];
					document.getElementById('notransaksi').value=notransaksi;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}


function displaylist() {
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('kodeunitsch').value='';
	document.getElementById('periodesch').value='';
	document.getElementById('jenissch').value='';
	document.getElementById('codesch').value='';
	document.getElementById('jumlahsch').value='';
    document.getElementById('keterangansch').value='';
	loaddata(0);
}

function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}



function loaddata(num) {
	if (document.getElementById('listdata') !== null) {
		document.getElementById('listdata').style.display = 'block';
	}
	if (document.getElementById('header') !== null) {
		document.getElementById('header').style.display = 'none';
	}
	kodeunit=document.getElementById('kodeunitsch').value;
	periode=document.getElementById('periodesch').value;
	jenis=document.getElementById('jenissch').value;
	code=document.getElementById('codesch').value;
	jumlah=document.getElementById('jumlahsch').value;
	keterangan=document.getElementById('keterangansch').value;
	param = 'method=loaddata&page=' + num;
	param += '&kodeunit=' + kodeunit+'&periode=' + periode+'&jenis=' + jenis+'&code=' + code+'&jumlah=' + jumlah+'&keterangan=' + keterangan;
	tujuan = 'keu_adjustmentlaporankeuangan_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function posting(notransaksi) {
	param='method=posting'+'&notransaksi='+notransaksi;
	tujuan = 'keu_adjustmentlaporankeuangan_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alertify.alert('Informasi',con.responseText);
				} else {
					closeDialog2();
					loaddata(0);
				}
			} else {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}


function deleteht(notransaksi){
	param = 'method=deleteht';
	param+='&notransaksi='+notransaksi;
	tujuan = 'keu_adjustmentlaporankeuangan_slave.php';
	alertify.confirm("Informasi","Hapus transaksi  ???",
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
					alertify.alert('Informasi',con.responseText);
				} else {
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function newdata(){
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	cancelht();
	// document.getElementById('detailhead').style.display='none';
}


