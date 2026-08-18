function simpandetail(id,row,dept,col){
	param = '';
	if(row!=''){
		//percolom
		noakun = document.getElementById('noakun_'+row).innerHTML;
		param += '&noakun[0]=' + noakun;
	}else{
		//all diatas
		noakun = document.getElementsByName('noakun[]');
		for (i = 0; i < noakun.length; i++) {
			param += '&noakun['+i+']=' + noakun[i].innerHTML;
		}
	}
	if(id.checked==true){
		param += '&check=1';
	}else{
		param += '&check=0';
	}
	tipeorg = document.getElementById('tipeorg').value;
	
	param += '&dept=' + dept + '&method=simpandetail';
	param += '&tipeorg=' + tipeorg;
	tujuan = 'bgt_slave_5mappingakun.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					if(row==''){
						loaddata();
						setTimeout(function(){
							if(id.checked==true){
								document.getElementById('judul_'+col).checked=true;
							}else{
								document.getElementsByName(dept).checked=false;
							}
						}, 1000);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpanDep() {
    kode = document.getElementById('kode').value;
    nama = document.getElementById('nama').value;
    aktif = document.getElementById('aktif').value;
    met = document.getElementById('method').value;
    if (trim(kode) == '') {
        alert('Code is empty');
        document.getElementById('kode').focus();
    } else {
        kode = trim(kode);
        nama = trim(nama);
        param = 'kode=' + kode + '&nama=' + nama + '&method=' + met + '&aktif=' + aktif;
        tujuan = 'bgt_slave_5mappingakun.php';
        post_response_text(tujuan, param, respog);
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    loaddata();
                    cancelDep();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}


function loaddata() {    
    tipeorg = document.getElementById('tipeorg').value;
	document.getElementById('tipeorg').disabled = true;
	
	param  = 'method=loaddata';
	param += '&tipeorg='+tipeorg;
	tujuan = 'bgt_slave_5mappingakun.php';
	post_response_text(tujuan, param, respog);
    

    function respog() {
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

function fillField(kode, nama, aktif) {
    document.getElementById('kode').value = kode;
    document.getElementById('kode').disabled = true;
    document.getElementById('nama').value = nama;
    document.getElementById('aktif').value = aktif;
    document.getElementById('method').value = 'update';
}

function cancelDep() {
    document.getElementById('tipeorg').disabled = false;
    document.getElementById('container').innerHTML = '';
}