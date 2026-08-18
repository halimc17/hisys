/**
 * @author repindra.ginting
 */
function saveMD()
{
	namajenis=document.getElementById('namajenis').value;
	IsStatus=document.getElementById('status').checked;
	kodeid=document.getElementById('kodeid').value;
	proses=document.getElementById('proses').value;
	if(IsStatus == true)status=1;
	else status=0;

	if(trim(namajenis)=='')
	{
		alert('Nama Jenis is empty');
		document.getElementById('namajenis').focus();
	}
	else
	{
		namajenis=trim(namajenis);
		param='kodeid='+kodeid+'&namajenis='+namajenis+'&status='+status+'&proses='+proses;
		//alert(param);
		tujuan='legal_slave_5masterdraftspk.php';
        post_response_text(tujuan, param, response);	
		
	}
	
	function response()
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
	cancelMD();	
}

function fillField(kodeid,namajenis,status)
{
	//alert(kodeid);
	document.getElementById('kodeid').value=kodeid;
    document.getElementById('namajenis').disabled=true;
	document.getElementById('namajenis').value=namajenis;
	document.getElementById('proses').value='update';
	if(status == 1)document.getElementById('status').checked=true;
	else document.getElementById('status')=false;
}

function cancelMD()
{
    document.getElementById('namajenis').disabled=false;
	document.getElementById('namajenis').value='';
	document.getElementById('status').checked=false;
	document.getElementById('proses').value='insert';		
}
