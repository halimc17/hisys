// JavaScript Document
function batal(){
	document.getElementById('method').value='insert';
	document.getElementById("kode").value='';
	document.getElementById('deskripsi').value='';
	document.getElementById('kode').disabled=false;
}

function loadData(){
	param='method=loadData';
	tujuan='kebun_slave_5kodedendapanen.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
						}
						else {
							document.getElementById('container').innerHTML=con.responseText;
							batal();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function fillfield(kode,deskripsi){
	document.getElementById('kode').disabled=true;
	document.getElementById('kode').value=kode;
	document.getElementById('deskripsi').value=deskripsi;
	document.getElementById('method').value='edit';
}

function simpan(){
	kode=trim(document.getElementById('kode').value);
	deskripsi=trim(document.getElementById('deskripsi').value);
	method=trim(document.getElementById('method').value);
	
	param='kode='+kode+'&deskripsi='+deskripsi+'&method='+method;
	tujuan='kebun_slave_5kodedendapanen.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
						}
						else {
							document.getElementById('container').innerHTML=con.responseText;
							batal();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function deletefield(kode){
	param='kode='+kode+'&method=delete';
	tujuan='kebun_slave_5kodedendapanen.php';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}