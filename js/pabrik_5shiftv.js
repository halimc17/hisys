//JS 


function simpan(){
	pks=trim(document.getElementById('pks').value);
	shift=trim(document.getElementById('shift').value);
	jmmulai=document.getElementById('jmmulai').value;
    mnmulai=document.getElementById('mnmulai').value;
    jmselesai=document.getElementById('jmselesai').value;
    mnselesai=document.getElementById('mnselesai').value;
	method=document.getElementById('method').value;
        
	if(pks=='' || shift==''){
		alert('Please complete the form');return;
	}
	
	param='pks='+pks+'&shift='+shift+'&method='+method;
	param+='&jmmulai='+jmmulai+'&mnmulai='+mnmulai;
    param+='&jmselesai='+jmselesai+'&mnselesai='+mnselesai;
	tujuan='pabrik_slave_5shiftv.php';
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

function hapus()
{
	document.getElementById('pks').value='';
	document.getElementById('shift').value='';
	document.getElementById('jmmulai').value='00';
    document.getElementById('mnmulai').value='00';
    document.getElementById('jmselesai').value='00';
    document.getElementById('mnselesai').value='00';
	document.getElementById('method').value='insert';
	document.getElementById('pks').disabled=false;
	document.getElementById('shift').disabled=false;
}

function loadData () 
{
	
	param='method=loadData';
	tujuan='pabrik_slave_5shiftv.php';
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

function fillField(pks,shift,jmmulai,mnmulai,jmselesai,mnselesai){
	document.getElementById('pks').value=pks;
	document.getElementById('shift').value=shift;
	
	document.getElementById('pks').disabled=true;
	document.getElementById('shift').disabled=true;
	
	document.getElementById('method').value='update';

	document.getElementById('jmmulai').value=jmmulai;
    document.getElementById('mnmulai').value=mnmulai;
    document.getElementById('jmselesai').value=jmselesai;
    document.getElementById('mnselesai').value=mnselesai;
	
}

function del(pks,shift){
	param='method=delete'+'&pks='+pks+'&shift='+shift;
	tujuan='pabrik_slave_5shiftv.php';
	if(confirm("Delete data?")){
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
