/**
 * @author repindra.ginting
 */
function simpanPlafon()
{
	regional=document.getElementById('regional').options[document.getElementById('regional').selectedIndex].value;
	kodegolongan=document.getElementById('kodegolongan').options[document.getElementById('kodegolongan').selectedIndex].value;
	rupiah=document.getElementById('rupiah').value;
	satuan=document.getElementById('satuan').options[document.getElementById('satuan').selectedIndex].value;
	met=document.getElementById('method').value;
	jenisbiaya=document.getElementById('jenisbiaya').options[document.getElementById('jenisbiaya').selectedIndex].value;
	if(trim(kodegolongan)=='')
	{
		alert('Code is empty');
		document.getElementById('kodegolongan').focus();
	}
	else
	{
		param='regional='+regional+'&kodegolongan='+kodegolongan+'&rupiah='+rupiah+'&method='+met+'&jenisbiaya='+jenisbiaya+'&satuan='+satuan;
		tujuan='sdm_slave_save_setup_plafond.php';
        post_response_text(tujuan, param, respog);		
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
							cancelPlafon();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
		
}

function fillField(regional,kode,rupiah,jenisbiaya,satuan)
{
	x=document.getElementById('regional');
	for(z=0;z<x.length;z++)
	{
		if(x.options[z].value==regional)
		x.options[z].selected=true;
	}
	document.getElementById('regional').disabled=true;
	x=document.getElementById('kodegolongan');
	for(z=0;z<x.length;z++)
	{
		if(x.options[z].value==kode)
		x.options[z].selected=true;
	}
    document.getElementById('kodegolongan').disabled=true;
	x=document.getElementById('jenisbiaya');
	for(z=0;z<x.length;z++)
	{
		if(x.options[z].value==jenisbiaya)
		x.options[z].selected=true;
	}
	x=document.getElementById('satuan');
	for(z=0;z<x.length;z++)
	{
		if(x.options[z].value==satuan)
		x.options[z].selected=true;
	}
    document.getElementById('jenisbiaya').disabled=true;	
	document.getElementById('rupiah').value=rupiah;
	document.getElementById('method').value='update';
}

function cancelPlafon()
{
    document.getElementById('regional').disabled=false;
    document.getElementById('regional').selectedIndex=0;
    document.getElementById('kodegolongan').disabled=false;
	document.getElementById('kodegolongan').selectedIndex=0;
    document.getElementById('jenisbiaya').disabled=false;
	document.getElementById('jenisbiaya').selectedIndex=0;	
	document.getElementById('rupiah').value='0';
	document.getElementById('satuan').selectedIndex=0;
	document.getElementById('method').value='insert';		
}
