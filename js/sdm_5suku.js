// JavaScript Document
function batal()
{
	document.getElementById('method').value = 'insert';
	document.getElementById("idsuku").value = "";
	document.getElementById('namasuku').value = "";
	document.getElementById('aktif').value = "1";
}

function loadData(){
	param='method=loaddata';
	tujuan='sdm_slave_5suku.php';
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
				else 
				{
					document.getElementById('container').innerHTML=con.responseText;
                    leftFixedTable();
					batal();
				}
			}
			else 
			{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function fillfield(idsuku,namasuku,aktif){
	document.getElementById('idsuku').value=idsuku;
	document.getElementById('namasuku').value=namasuku;
	document.getElementById('aktif').value=aktif;
	document.getElementById('method').value='edit';
}

function simpan()
{
	idsuku=trim(document.getElementById('idsuku').value);
	namasuku=trim(document.getElementById('namasuku').value);
	aktif=trim(document.getElementById('aktif').value);
	method=trim(document.getElementById('method').value);
	
	param='idsuku='+idsuku+'&namasuku='+namasuku+'&aktif='+aktif+'&method='+method;
	tujuan='sdm_slave_5suku.php';
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
			else 
			{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function deletefield(kode){
	param='idsuku='+kode+'&method=delete';
	tujuan='sdm_slave_5suku.php';
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