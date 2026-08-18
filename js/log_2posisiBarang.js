
function batal()
{
	document.location.reload();
	
}



function cariNoPo(title,ev)
{
	content= "<div>";
	content+="<fieldset style=min-width:93%>No PO : <input type=text id=noPo class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariPo()>Go</button></fieldset>";
	content+="<fieldset><legend><i>Result</i></legend><div id=containercari style=\"max-height:300px;overflow:auto;\"></div></fieldset></div>";
	//display window
	title=title+' PO:';
	width='';
	height='';
	showDialog5(title,content,width,height,ev);	
}



function goCariPo()
{
	noPo=trim(document.getElementById('noPo').value);
	if(noPo.length<4)
	{   
		alert('Text too short');
		return;
	}
	else
	{   
		param='proses=goCariPo'+'&noPo='+noPo;
		tujuan = 'log_slave_2posisiBarang.php';
		post_response_text(tujuan, param, respog);			
	}
	//alert(param);
	function respog(){
			if (con.readyState == 4) {
					if (con.status == 200) {
							busy_off();
							if (!isSaveResponse(con.responseText)) {
									alert(con.responseText);
							}
							else {
									document.getElementById('containercari').innerHTML=con.responseText;
							}
					}
					else {
							busy_off();
							error_catch(con.status);
					}
			}
	}	
}



function goPickPO(nopo)
{
        document.getElementById('nopo').value=nopo;
		closeDialog5();
}


/*
function cari()
{
	nopo=trim(document.getElementById('nopo').value);
	param='nopo='+nopo+'&method=loadData';
	//param='txt='+txt+'&tgl='+tgl+'&method=loadData';
	tujuan='log_slave_2posisiBarang.php';
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
							}
						}
						else {
							busy_off();
							error_catch(con.status);
						}
				  }	
		 }
		 post_response_text(tujuan, param, respog);
}







function cariBast(num)
{
		txt=trim(document.getElementById('txt').value);
		
	
		
		param='method=loadData';
		param+='&page='+num+'&txt='+txt;
		
		//param='txt='+txt+'&tgl='+tgl+'&status='+status+'&method=loadData';
		
		
		tujuan = 'log_slave_2posisiBarang.php';
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



function loadData () 
{
	param='method=loadData';
	tujuan='log_slave_2posisiBarang.php';
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
*/


