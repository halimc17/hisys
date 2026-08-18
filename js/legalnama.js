/**
 * @author repindra.ginting
 */
function simpanGolongan()
{
	//alert('masuk');
	kodegolongan=document.getElementById('kodegolongan').value;
	namagolongan=document.getElementById('namagolongan').value;
	kodekategori=document.getElementById('kodekategori').value;
	met=document.getElementById('method').value;
	if(trim(kodegolongan)=='')
	{
		alert('Code is empty');
		document.getElementById('kodegolongan').focus();
	}
	else
	{
		if(kodekategori=='')
		{
			alert('Category is empty');
			document.getElementById('kodekategori').focus();
		}
		else
		{
			kodegolongan=trim(kodegolongan);
			namagolongan=trim(namagolongan);
			param='kodegolongan='+kodegolongan+'&namagolongan='+namagolongan+'&kodekategori='+kodekategori+'&method='+met;
			tujuan='pad_slave_save_legalnama.php';
	        post_response_text(tujuan, param, respog);	
			
		}
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
	cancelGolongan();	
}

function fillField(kode,nama,kategori)
{
	document.getElementById('kodegolongan').value=kode;
    document.getElementById('kodegolongan').disabled=true;
	document.getElementById('namagolongan').value=nama;
	document.getElementById('kodekategori').value=kategori;
	document.getElementById('method').value='update';
}

function cancelGolongan()
{
    document.getElementById('kodegolongan').disabled=false;
	document.getElementById('kodegolongan').value='';
	document.getElementById('namagolongan').value='';
	document.getElementById('method').value='insert';		
}
