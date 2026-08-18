// JavaScript Document
function btlalasanrencanasisip()
{
	document.getElementById('kd_alasan').value='';
	document.getElementById('kd_alasan').disabled=false;
	document.getElementById('deskripsi').value='';
	document.getElementById('method').value='insert';
}

function loadData(){
	param='method=loaddata';
	tujuan='kebun_slave_5alasanrencanasisip.php';
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
							btlalasanrencanasisip();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function fillfield(kd_alasan,deskripsi){
	document.getElementById('kd_alasan').disabled=true;
	document.getElementById('kd_alasan').value=kd_alasan;
	document.getElementById('deskripsi').value=deskripsi;
	document.getElementById('method').value='edit';
}

function simpanalasanrencanasisip()
{
	kd_alasan=trim(document.getElementById('kd_alasan').value);
	deskripsi=trim(document.getElementById('deskripsi').value);
	method=trim(document.getElementById('method').value);
	
	param='kd_alasan='+kd_alasan+'&deskripsi='+deskripsi+'&method='+method;
	tujuan='kebun_slave_5alasanrencanasisip.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
					btlalasanrencanasisip();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function deletefield(kd_alasan){
	param='kd_alasan='+kd_alasan+'&method=delete';
	tujuan='kebun_slave_5alasanrencanasisip.php';
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