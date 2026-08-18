//JS 

function getstation(){
    unit=document.getElementById('unit').value;
    param='method=getstation'+'&unit='+unit;
    tujuan='pabrik_slave_5criticalparts.php';
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
							document.getElementById('station').innerHTML=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}

function simpan(){
    unit=document.getElementById('unit').value;
    station=document.getElementById('station').value;
    kodebarang=document.getElementById('kodebarang').value;
    method=document.getElementById('method').value;
    if(unit=='' || station=='' || kodebarang=='') {
		alert('Field Was Empty');
		return;
    }
    param='unit='+unit+'&station='+station+'&method='+method+'&kodebarang='+kodebarang;
    tujuan='pabrik_slave_5criticalparts.php';
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
							cancel();
							loaddata();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}
					


function cancel(){
    document.getElementById('unit').value='';
    document.getElementById('kodebarang').value='';
    document.getElementById('kodebarang').disabled=false;
    document.getElementById('station').value='';
    document.getElementById('station').disabled=false;
    document.getElementById('method').value='insert';
    document.getElementById('unit').disabled=false;
}




function loaddata () {
	param='method=loaddata';
	tujuan='pabrik_slave_5criticalparts.php';
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
                                   // alert(con.responseText);
                                    document.getElementById('container').innerHTML=con.responseText;
									
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}

function edit(unit,station,kodebarang)
{
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
     document.getElementById('station').value=station;
    document.getElementById('method').value='update';
}



function del(unit,station,kodebarang)
{
	param='method=delete'+'&unit='+unit+'&station='+station+'&kodebarang='+kodebarang;
	tujuan='pabrik_slave_5criticalparts.php';
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
					else 
					{
						loaddata();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}




