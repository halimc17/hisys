//JS 

function simpan()
{
    jenisijin=document.getElementById('jenisijin').value;
    hakcuti=document.getElementById('hakcuti').value;
    method=document.getElementById('method').value;

    if(jenisijin=='' || hakcuti=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='jenisijin='+jenisijin+'&hakcuti='+hakcuti+'&method='+method;
    // alert(param);
    tujuan='sdm_slave_5hakcutijns.php';
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
                            loadData();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}
					


function cancel()
{
   
    document.getElementById('jenisijin').value='';
    document.getElementById('hakcuti').value='';
    document.getElementById('method').value='insert';
    document.getElementById('jenisijin').disabled=false;
}

function loadData(num){
	param='method=loadData';
    param+='&page='+num;

    tujuan='sdm_slave_5hakcutijns.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    isdt = con.responseText.split("####");
                    document.getElementById('continerlist').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);  
}

function edit(jenisijin,hakcuti)
{
    document.getElementById('jenisijin').value=jenisijin;
    document.getElementById('jenisijin').disabled=true;
     document.getElementById('hakcuti').value=hakcuti;
    document.getElementById('method').value='update';
}



function del(jenisijin)
{
	param='method=delete'+'&jenisijin='+jenisijin;
	tujuan='sdm_slave_5hakcutijns.php';
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
						loadData();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}




