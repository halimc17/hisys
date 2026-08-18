/**
 * @author repindra.ginting
 */
function loadData1() 
{
    // alert('masukk');
    param='method=loadData';
    // param+='&supplierid='+idsupplier_detail;
    tujuan='sdm_slave_5kategoti_inv.php';
    // alert(param);
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

function simpan()
{
	idjenis=document.getElementById('idjenis').value;
	jenis=document.getElementById('jenis').value;
	//jumlahhk=remove_comma(document.getElementById('jumlahhk'));
	method=document.getElementById('method').value;
	if(jenis=='')
	{
		alert('Each Field are obligatory');
		return;
	}
	param='idjenis='+idjenis+'&jenis='+jenis+'&method='+method;
	 tujuantujuan='sdm_slave_5kategoti_inv.php';
	 // alert(param);
	
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
                            loadData1(0);
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              } 
         }    
}

function edit(idjenis,jenis)
{
    document.getElementById('idjenis').value=idjenis;
    document.getElementById('idjenis').disabled=true;
    document.getElementById('jenis').value=jenis;
    
    // document.getElementById('email').value=namasupplier;
    // if(statusup=='1')
    //    document.getElementById('statusup').checked=true;
    // else
    //    document.getElementById('statusup').checked=false;
    document.getElementById('method').value='update';
}

function cancel()
{
    document.getElementById('idjenis').value='';
   document.getElementById('jenis').value='';
   // document.getElementById('namasupplier').value='';
	
	document.getElementById('method').value='insert';		
}

