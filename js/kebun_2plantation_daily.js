function getperiode(){
	kebun=document.getElementById('kebun');
	kebun=kebun.options[kebun.selectedIndex].value;
	param='kebun='+kebun+'&method=getperiode';
	tujuan='kebun_slave_2plantation_daily.php';
	post_response_text(tujuan, param, respog);  
    	
	function respog()
	{
		      if(con.readyState==4)
				{	
			        if (con.status == 200) 
					{
						busy_off();
						if (!isSaveResponse(con.responseText)) 
						{
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}
						else {
							//alert(con.responseText);
							dtd=con.responseText.split("####");
							document.getElementById('divisi').innerHTML=dtd[0];
							document.getElementById('periode').innerHTML=dtd[1];
						}
					}
						else 
						{
							busy_off();
							error_catch(con.status);
						}
				}				
	}
}

function preview(){   
    kebun=document.getElementById('kebun');
	kebun=kebun.options[kebun.selectedIndex].value;
	divisi=document.getElementById('divisi');
	divisi=divisi.options[divisi.selectedIndex].value;
	periode=document.getElementById('periode');
	periode=periode.options[periode.selectedIndex].value;
	tipe=document.getElementById('tipe');
	tipe=tipe.options[tipe.selectedIndex].value;
	groupId=document.getElementById('groupId');
	groupId=groupId.options[groupId.selectedIndex].value;
    tglSmp=document.getElementById('tglSmp').value;
    param='kebun='+kebun+'&divisi='+divisi+'&periode='+periode+'&tglSmp='+tglSmp;
    param+='&method=preview'+'&tipe='+tipe+'&display=preview'+'&groupId='+groupId;
    tujuan='kebun_slave_2plantation_daily.php';
    post_response_text(tujuan, param, callback);  
        
    function callback(){
              if(con.readyState==4)
              {
                    if (con.status == 200) 
					{
                        busy_off();
                        if (!isSaveResponse(con.responseText)) 
						{
                            alert('ERROR TRANSACTION,\n' + con.responseText);
                        }
                        else 
						{
                            document.getElementById('container').innerHTML=con.responseText;
                        }
                    }
                    else 
					{
                        busy_off();
                        error_catch(con.status);
                    }
              } 
     }
}

function excel(ev,tujuan) {
	kebun=document.getElementById('kebun');
	kebun=kebun.options[kebun.selectedIndex].value;
	divisi=document.getElementById('divisi');
	divisi=divisi.options[divisi.selectedIndex].value;
	periode=document.getElementById('periode');
	periode=periode.options[periode.selectedIndex].value;
	tipe=document.getElementById('tipe');
	tipe=tipe.options[tipe.selectedIndex].value;
	groupId=document.getElementById('groupId');
	groupId=groupId.options[groupId.selectedIndex].value;
    tglSmp=document.getElementById('tglSmp').value;
    param='kebun='+kebun+'&divisi='+divisi+'&periode='+periode+'&tglSmp='+tglSmp;
    param+='&method=preview'+'&tipe='+tipe+'&display=excel'+'&groupId='+groupId;
    
    
	judul = 'Report Ms.Excel';
    printFile(param,tujuan,judul,ev);	
}

function printFile(param,tujuan,title,ev)
{ 
   tujuan=tujuan+"?"+param;  
    width='600';
    height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev);  
}

function cancel()
{
    document.getElementById('kebun').value='';
    document.getElementById('divisi').value='';
    document.getElementById('tanggal').value='';
}
function gantiObjectnya(){
	tipe=document.getElementById('tipe');
	tipe=tipe.options[tipe.selectedIndex].value;
	if(tipe==0){
		document.getElementById('isiPeriode1').style.display="block";
		document.getElementById('isiPeriode2').style.display="none";
	}else{
		document.getElementById('isiPeriode1').style.display="none";
		document.getElementById('isiPeriode2').style.display="block";
	}
}