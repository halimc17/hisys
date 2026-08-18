function simpan()
{ 
	id=document.getElementById('id').value;
	id_prov=document.getElementById('id_prov').value;
	kabupaten=document.getElementById('kabupaten').value;
	provinsi=document.getElementById('provinsi').value;
	met=document.getElementById('method').value;
	
	{	
		id=id;
		id_prov=id_prov;
		provinsi=provinsi;
		kabupaten=kabupaten;

		param='id='+id+'&kabupaten='+kabupaten+'&provinsi='+provinsi+'&id_prov='+id_prov+'&method='+met;
		tujuan='set_slave_5kabupaten.php';
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


function fillField(id,id_prov,kabupaten,provinsi)
{
	document.getElementById('id').value=id;
	document.getElementById('id_prov').value=id_prov;
    document.getElementById('kabupaten').value=kabupaten;
    document.getElementById('provinsi').value=provinsi;
	document.getElementById('method').value='update';	
}

function batal()
{
    document.getElementById('id').value='';
	document.getElementById('id_prov').value='';		
	document.getElementById('provinsi').value='';
	document.getElementById('kabupaten').value='';		
}