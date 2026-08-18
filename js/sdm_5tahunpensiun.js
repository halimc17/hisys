//JS 

function simpan()
{
    tahun=document.getElementById('tahun').value;
    usiapensiun=document.getElementById('usiapensiun').value;
    method=document.getElementById('method').value;

    if(tahun=='' || usiapensiun=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='tahun='+tahun+'&usiapensiun='+usiapensiun+'&method='+method;
    tujuan='sdm_slave_5tahunpensiun.php';
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
   
    document.getElementById('tahun').value='';
    document.getElementById('usiapensiun').value='';
    document.getElementById('method').value='insert';
    document.getElementById('tahun').disabled=false;
}

function loadData(num){
	param='method=loadData';
    param+='&page='+num;

    tujuan='sdm_slave_5tahunpensiun.php';
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

function edit(tahun,usiapensiun)
{
    document.getElementById('tahun').value=tahun;
    document.getElementById('tahun').disabled=true;
     document.getElementById('usiapensiun').value=usiapensiun;
    document.getElementById('method').value='update';
}



function del(tahun)
{
	param='method=delete'+'&tahun='+tahun;
	tujuan='sdm_slave_5tahunpensiun.php';
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




