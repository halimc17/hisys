function getkode(){
	method='getkode';
    jenis=document.getElementById('jenis').value;
    param='jenis='+jenis;
    param+='&method='+method;
    tujuan='pmn_5kapalponton_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    document.getElementById('kode').value=con.responseText;   
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
	}
}


function simpan(){
	

    method=document.getElementById('method').value;
    jenis=document.getElementById('jenis').value;
    kode=document.getElementById('kode').value;
    nama=document.getElementById('nama').value;
    keterangan=document.getElementById('keterangan').value;

    if(jenis=='' || kode=='' || nama==''){
        alert('Please complete the form');return;
    }
    param='jenis='+jenis+'&kode='+kode+'&nama='+nama+'&keterangan='+keterangan;
    param+='&method='+method;
    tujuan='pmn_5kapalponton_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    hapus();							
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }
}

function hapus(){
    document.getElementById('method').value='insert';
    document.getElementById('jenis').value='';
    document.getElementById('kode').value='';
    document.getElementById('nama').value='';
    document.getElementById('keterangan').value='';
    document.getElementById('jenis').disabled=false;
	document.getElementById('kode').disabled=false;
}

function loaddata(num) {

    kodesch = document.getElementById('kodesch').value;
    namasch = document.getElementById('namasch').value;

	param='method=loaddata';
	param+='&page='+num;
    
    if(kodesch != '') {
        param += "&kodesch=" + kodesch
    }

    if(namasch != '') {
        param += "&namasch=" + namasch
    }

	tujuan='pmn_5kapalponton_slave.php';
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

function fillField(jenis,kode,nama,keterangan){
    document.getElementById('method').value='update';	
	document.getElementById('jenis').value=jenis;
    document.getElementById('kode').value=kode;
    document.getElementById('nama').value=nama;
    document.getElementById('keterangan').value=keterangan;
    document.getElementById('jenis').disabled=true;
	document.getElementById('kode').disabled=true;
	
}

function del(kode){
    param='method=delete'+'&kode='+kode;
    tujuan='pmn_5kapalponton_slave.php';
    if(confirm("Delete data?")){
            post_response_text(tujuan, param, respog);	
    }
    function respog(){
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
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
