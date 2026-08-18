function simpan()
{ 
	id=document.getElementById('id').value;
	provinsi=document.getElementById('provinsi').value;
	met=document.getElementById('method').value;
	
	{	
		id=id;
		provinsi=provinsi;

		param='id='+id+'&provinsi='+provinsi+'&method='+met;
		tujuan='set_slave_5propinsi.php';
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


function fillField(id,provinsi)
{
	document.getElementById('id').value=id;
	document.getElementById('provinsi').value=provinsi;
	document.getElementById('method').value='update';	
}

function batal()
{
    document.getElementById('id').value='';
	document.getElementById('provinsi').value='';		
}
