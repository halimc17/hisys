/**
 * @author repindra.ginting
 */


function simpanJ()
{
	kode=document.getElementById('kode').value;	
	keterangan=document.getElementById('keterangan').value;	
	met=document.getElementById('method').value;
	param='kode='+kode+'&method='+met;;
	param+='&keterangan='+keterangan;
	
	tujuan='sdm_slave_save_5tipenatura.php';
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
						//alert(con.responseText);
						document.getElementById('container').innerHTML=con.responseText;
						cancelJ();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	 }
		
}

function fillField(kode,keterangan){
	document.getElementById('kode').value=kode;
	document.getElementById('kode').disabled=true;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('method').value='update';
}

function cancelJ(){
	document.getElementById('kode').disabled=false;
	document.getElementById('keterangan').value='';
	document.getElementById('kode').value='';
	document.getElementById('method').value='insert';		
}
