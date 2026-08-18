//JS 

function getstation(){
    unit=document.getElementById('unit').value;
    param='method=getstation'+'&unit='+unit;
    tujuan='sdm_slave_5list_inventory.php';
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
    kategori=document.getElementById('kategori').value;
    kodebarang=document.getElementById('kodebarang').value;
    method=document.getElementById('method').value;
    if(unit=='' || kategori=='' || kodebarang=='') {
		alert('Field Was Empty');
		return;
    }
    param='unit='+unit+'&kategori='+kategori+'&method='+method+'&kodebarang='+kodebarang;
    tujuan='sdm_slave_5list_inventory.php';
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
    document.getElementById('kategori').value='';
    document.getElementById('kategori').disabled=false;
    document.getElementById('method').value='insert';
    document.getElementById('unit').disabled=false;
}




function loaddata () {
	param='method=loaddata';
	tujuan='sdm_slave_5list_inventory.php';
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
     document.getElementById('kategori').value=kategori;
    document.getElementById('method').value='update';
}



function del(unit,station,kodebarang)
{
	param='method=delete'+'&unit='+unit+'&kategori='+kategori+'&kodebarang='+kodebarang;
	tujuan='sdm_slave_5list_inventory.php';
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





