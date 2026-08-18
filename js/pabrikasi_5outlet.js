//JS 

function cariBast(num)
{
    param='method=loaddata';
    param+='&page='+num;
    tujuan = 'pabrikasi_slave_5outlet.php';
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
                                    //loaddata();
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
	kode=trim(document.getElementById('kode').value);
	nama=trim(document.getElementById('nama').value);
	method=document.getElementById('method').value;
        
	if(kode=='' || nama==''){
		alert('Please complete the form');return;
	}
	
	param='kode='+kode+'&nama='+nama+'&method='+method;
	tujuan='pabrikasi_slave_5outlet.php';
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

function hapus(){
	document.getElementById('kode').value='';
	document.getElementById('nama').value='';
	document.getElementById('method').value='insert';
	document.getElementById('kode').disabled=false;
	document.getElementById('nama').disabled=false;
}

function loaddata () {
	param='method=loaddata';
	tujuan='pabrikasi_slave_5outlet.php';
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

function fillField(kode,nama)
{
	document.getElementById('kode').value=kode;
	document.getElementById('nama').value=nama;
	document.getElementById('kode').disabled=true;
	document.getElementById('nama').disabled=false;
	document.getElementById('method').value='update';	
}

function del(kode,nama)
{
	param='method=delete'+'&kode='+kode+'&nama='+nama;
	tujuan='pabrikasi_slave_5outlet.php';
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
						loaddata();	
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


