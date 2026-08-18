function simpan()
{ 
	id=document.getElementById('id').value;
	id_kec=document.getElementById('id_kec').value;
	kecamatan=document.getElementById('kecamatan').value;
	desa=document.getElementById('desa').value;
	met=document.getElementById('method').value;
	
	{	
		id=id;
		id_kec=id_kec;
		kecamatan=kecamatan;
		desa=desa;

		param='id='+id+'&id_kec='+id_kec+'&kecamatan='+kecamatan+'&desa='+desa+'&method='+met;
		tujuan='set_slave_5desa.php';
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

function deletefield(id,id_kec){ 
	param='id='+id+'&kecamatan='+id_kec+'&method=deletefield';
	tujuan='set_slave_5desa.php';
	
	if(confirm('Apakah Anda yakin hapus item ini?')){
		post_response_text(tujuan, param, respog);		
	}
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
					document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}


function fillField(id_kec,id,id_kec,desa)
{
	document.getElementById('id').value=id;
	document.getElementById('id_kec').value=id_kec;
    document.getElementById('kecamatan').value=id_kec;
    document.getElementById('desa').value=desa;
	document.getElementById('method').value='update';	
}

function batal()
{
	document.getElementById('id').value='';
	document.getElementById('id_kec').value='';
    document.getElementById('kecamatan').value='';
    document.getElementById('desa').value='';	
}