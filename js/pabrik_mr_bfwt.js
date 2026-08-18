//JS 

function simpan(){
    tgl=trim(document.getElementById('tgl').value);
    tipe=trim(document.getElementById('tipe').value);
    kode=trim(document.getElementById('kode').value);
    nilai=trim(document.getElementById('nilai').value);
    ket=trim(document.getElementById('ket').value);
    method=document.getElementById('method').value;
    if(tgl=='' || tipe=='') {
        alert('Please complete the form');return;
    }

    param='tgl='+tgl+'&kode='+kode+'&nilai='+nilai+'&ket='+ket+'&tipe='+tipe;
    param+='&method='+method;
    tujuan='pabrik_slave_mr_bfwt.php';
    post_response_text(tujuan, param, respog);	
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
					hapus();							
                    loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}


function getkode(kode){
	tipe=trim(document.getElementById('tipe').value);
    param='method=getkode'+'&tipe='+tipe+'&kode='+kode;
    tujuan='pabrik_slave_mr_bfwt.php';
    post_response_text(tujuan, param, respog);	
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
					document.getElementById('kode').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}


function edit(kode,tgl,nilai,ket,tipe){
	document.getElementById('kode').value=kode;
	document.getElementById('kode').disabled=true;
	document.getElementById('tipe').disabled=true;
	document.getElementById('tgl').value=tgl;
	document.getElementById('tgl').disabled=true;
	document.getElementById('nilai').value=nilai;
	document.getElementById('ket').value=ket;
	document.getElementById('tipe').value=tipe;
	document.getElementById('method').value='update';
	getkode(kode);
}

function hapus(){
    document.getElementById('method').value='insert';
    document.getElementById('tgl').value='';
    document.getElementById('kode').value='';
    document.getElementById('ket').value='';
    document.getElementById('tipe').value='';
    document.getElementById('nilai').value='0';
	document.getElementById('kode').disabled=false;
	document.getElementById('tipe').disabled=false;
	document.getElementById('tgl').disabled=false;
}

function loaddata (page) {
	tipesch=document.getElementById('tipesch').options[document.getElementById('tipesch').selectedIndex].value;
    tglsch=document.getElementById('tglsch').value;
    param='method=loaddata'+'&tglsch='+tglsch+'&tipesch='+tipesch;
	param+='&page='+page;
	tujuan='pabrik_slave_mr_bfwt.php';

    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function batalcari(){
	document.getElementById('tipesch').value='';
	document.getElementById('tglsch').value='';	
}


function  del (tgl,kode,tipe){
    param='method=delete'+'&kode='+kode+'&tgl='+tgl+'&tipe='+tipe;
    tujuan='pabrik_slave_mr_bfwt.php';
    if(confirm("Delete data?")){
		post_response_text(tujuan, param, respog);	
    }
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
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