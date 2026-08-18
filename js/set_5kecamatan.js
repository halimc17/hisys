function simpan()
{ 
	id=document.getElementById('id').value;
	id_kab=document.getElementById('kabupaten').value;
	kabupaten=document.getElementById('kabupaten').value;
	kecamatan=document.getElementById('kecamatan').value;
	met=document.getElementById('method').value;
	
	{	
		id=id;
		id_kab=id_kab;
		kabupaten=kabupaten;
		kecamatan=kecamatan;

		param='id='+id+'&kabupaten='+kabupaten+'&kecamatan='+kecamatan+'&id_kab='+id_kab+'&method='+met;
		tujuan='set_slave_5kecamatan.php';
        post_response_text(tujuan, param, respog);
		//alert(param);
	}
	
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
							//alert(con.responseText);
							document.getElementById('container').innerHTML=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
	//cancelGolongan();	
}


function fillField(id_kab,id,kabupaten,kecamatan)
{
	document.getElementById('id').value=id;
	document.getElementById('id_kab').value=id_kab;
    document.getElementById('kabupaten').value=kabupaten;
    document.getElementById('kecamatan').value=kecamatan;
	document.getElementById('method').value='update';	
}

function batal(){
	document.getElementById('id').value='';
	document.getElementById('id_kab').value='';
    document.getElementById('kabupaten').value='';
    document.getElementById('kecamatan').value='';	
}