// JavaScript Document
function loadData(){
	param='method=loaddata';
	tujuan='kebun_slave_5hpt.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else {
					document.getElementById('container').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function simpan()
{
	kegiatan = document.getElementById('kegiatan').options[document.getElementById('kegiatan').selectedIndex].value;
	tipes = document.getElementsByName('tipe');
	for (var i = 0, length = tipes.length; i < length; i++) {
		if (tipes[i].checked) {
			tipe = tipes[i].value;
			break;
		}
	}
	
	param='kegiatan='+kegiatan+'&tipe='+tipe+'&method=simpan';
	tujuan='kebun_slave_5hpt.php';
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
					alert("Done.");
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function delData(kegiatan,tipe){
	param='kegiatan='+kegiatan+'&tipe='+tipe+'&method=delete';
	tujuan='kebun_slave_5hpt.php';
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
					alert("Done.");
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}