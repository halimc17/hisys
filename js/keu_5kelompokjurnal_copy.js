// JavaScript Document
function checktipe()
{
	if(document.getElementById('tipe').checked)
	{
		document.getElementById('kodekelompokc').style.display = "";
	}
	else
	{
		document.getElementById('kodekelompokc').style.display = "none";
	}
}

function copyData()
{
    //alert("masuk");
    kodePt1=document.getElementById('kodeorg1').options[document.getElementById('kodeorg1').selectedIndex].value;
    kodePt2=document.getElementById('kodeorg2').options[document.getElementById('kodeorg2').selectedIndex].value;
    tipe=document.getElementById('tipe').checked;
    kodekelompok=document.getElementById('kodekelompokc').value;
    if(kodePt1 == kodePt2)
    {
    	alertify.alert("Informasi","Organisasi tidak boleh sama")
    }
    else
    {
		param='proses=copyData'+'&kodePt1='+kodePt1+'&kodePt2='+kodePt2+'&tipe='+tipe+'&kodekelompok='+kodekelompok;
		//alert(param);
		tujuan='keu_slave_5kelompokjurnal_copy.php';
		post_response_text(tujuan, param, respog);
	}
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alertify.alert("Informasi",con.responseText);
						}
						else {
						//	alert(con.responseText);
						//eval(con.responseText);
                                                if(con.responseText==1)
                                                    {
                                                        alertify.alert("Informasi","Copy Berhasil");
                                                    }
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
	 	
}
