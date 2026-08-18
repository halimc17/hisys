/**
 * @author repindra.ginting
 */
function simpangudang()
{
	afdeling=document.getElementById('afdeling').options[document.getElementById('afdeling').selectedIndex].value;
	kodegudang=document.getElementById('kodegudang').options[document.getElementById('kodegudang').selectedIndex].value;
	status=document.getElementById('status').options[document.getElementById('status').selectedIndex].value;
	met=document.getElementById('method').value;
	
		param='kodegudang='+kodegudang+'&afdeling='+afdeling+'&status='+status+'&method='+met;
		tujuan='kebun_slave_5gudangtransaksi.php';
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
							loadData();
							document.getElementById("kodegudang").selectedIndex = "0";
							document.getElementById("afdeling").selectedIndex = "0";
							document.getElementById("status").selectedIndex = "0";
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
	
		
}

function loadData(num) {
	param='method=loadData';
	param+='&page='+num;
	tujuan='kebun_slave_5gudangtransaksi.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;	
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	
}

function fillField(afdeling,kodegudang,status)
{
	Lafdeling=document.getElementById('afdeling');
    for(ard=0;ard<Lafdeling.length;ard++)
    {
        if(Lafdeling.options[ard].value==afdeling)
            {
                Lafdeling.options[ard].selected=true;
            }
    }
	document.getElementById('kodegudang').disabled=true;
    document.getElementById('afdeling').disabled=true;
    Lkodegudang=document.getElementById('kodegudang');
    for(ard=0;ard<Lkodegudang.length;ard++)
    {
        if(Lkodegudang.options[ard].value==kodegudang)
            {
                Lkodegudang.options[ard].selected=true;
            }
    }

    Lstatus=document.getElementById('status');
    for(ard=0;ard<Lstatus.length;ard++)
    {
        if(Lstatus.options[ard].value==status)
            {
                Lstatus.options[ard].selected=true;
            }
    }

	document.getElementById('method').value='update';
}

function cancelgudang()
{
	document.getElementById("kodegudang").selectedIndex = "0";
	document.getElementById("afdeling").selectedIndex = "0";
	document.getElementById("status").selectedIndex = "0";
	document.getElementById('afdeling').disabled=false;
	document.getElementById('kodegudang').disabled=false;
	document.getElementById('method').value='insert';		
}
