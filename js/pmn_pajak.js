//JS 

function cariBast(num)
{
	cariPt=document.getElementById('cariPt').value;
	
    param='method=loadData&cariPt='+cariPt;
    param+='&page='+num;
    tujuan = 'pmn_slave_pajak.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
            if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert(con.responseText);
                            }
                            else {
                                    //displayList();

                                    document.getElementById('container').innerHTML=con.responseText;
                                    //loadData();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }
    }	
}





function getFaktur()
{
    pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
    //pt=document.getElementById('pt').value; 
    
    
    param='method=getFaktur'+'&pt='+pt;
    //alert(param);
    tujuan='pmn_slave_pajak.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('faktur').innerHTML=con.responseText;
                  
                    //.value=trim(con.responseText);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}



function hapus()
{
    document.getElementById('invoice').value='';
    document.getElementById('pt').value='';
    document.getElementById('kurs').value='1';
    document.getElementById('faktur').value='';
}



function simpan()
{
	
	invoice=trim(document.getElementById('invoice').value);
	faktur=document.getElementById('faktur').options[document.getElementById('faktur').selectedIndex].value;
	kurs=trim(document.getElementById('kurs').value);
	jenis=trim(document.getElementById('jenis').value);
	method=document.getElementById('method').value;
        
        if(invoice=='' || faktur=='' || kurs=='')
        {
            alert('Please complete the form');return;
        }
	
	param='invoice='+invoice+'&faktur='+faktur+'&kurs='+kurs+
		'&jenis='+jenis+'&method='+method+'&pt='+getValue('pt');
	tujuan='pmn_slave_pajak.php';
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
							hapus();							
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

/*function hapus()
{
	document.getElementById('invoice').value='';
	document.getElementById('faktur').options[0].selected=true;
	document.getElementById('kurs').value='';	
	document.getElementById('jenis').value='Jumlah Harga Jual';
	//method=document.getElementById('method').value;
	
	param='method=getFaktur';
    tujuan='pmn_slave_pajak.php';
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
                    document.getElementById('faktur').innerHTML=con.responseText;	
                   
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }	
     } 
}*/

function loadData () 
{
	param='method=loadData';
    tujuan='pmn_slave_pajak.php';
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
					hapus();
                   
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }	
     }  
}



function del(faktur)
{
	param='method=delete'+'&faktur='+faktur;
	//alert(param);
	tujuan='pmn_slave_pajak.php';
	//if(confirm("Delete data for "+kdorg+" period "+kodesupplier+" ?"))
	if(confirm("Delete data?"))
	
        {
		post_response_text(tujuan, param, respog);	
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
					else 
					{
						 document.getElementById('container').innerHTML=con.responseText;
						loadData();	
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}
	//alert("Data telah terhapus !!!");	
}
