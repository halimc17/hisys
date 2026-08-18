//JS 

function cariBast(num)
{
    param='method=loadData';
    param+='&page='+num;
    tujuan = 'pabrik_slave_5hargatbs.php';
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


function simpan()
{
	
	tgl=trim(document.getElementById('tgl').value);
	kdorg=trim(document.getElementById('kdorg').value);
	harga=trim(document.getElementById('harga').value);
	thntnm=trim(document.getElementById('thntnm').value);
	kodesupplier=trim(document.getElementById('kodesupplier').value);
	method=document.getElementById('method').value;
        
        if(kdorg=='' || tgl=='' || harga=='' || thntnm=='' || kodesupplier=='')
        {
            alert('Please complete the form');return;
        }
	
	param='tgl='+tgl+'&kdorg='+kdorg+'&harga='+harga+'&thntnm='+thntnm+'&kodesupplier='+kodesupplier+'&method='+method;
	tujuan='pabrik_slave_5hargatbs.php';
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
	document.getElementById('tgl').value='';
	document.getElementById('kdorg').value='';
	document.getElementById('harga').value='';	
	document.getElementById('kodesupplier').value='';	
	document.getElementById('thntnm').value='';
	document.getElementById('method').value='insert';
        document.getElementById('kdorg').disabled=false;
	document.getElementById('kodesupplier').disabled=false;
	document.getElementById('tgl').disabled=false;
	document.getElementById('thntnm').disabled=false;
	//method=document.getElementById('method').value;
}

function loadData () 
{
    
    //kdorgsort=document.getElementById('kdorgsort').value;
  //  periodesort=document.getElementById('periodesort').value;
  //  suppsort=document.getElementById('suppsort').value;
 
	param='method=loadData';
	tujuan='pabrik_slave_5hargatbs.php';
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
									getperiodesort();
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}

function fillField(kdorg,kodesupplier,tgl,thntnm,harga)
{
	document.getElementById('kdorg').value=kdorg;
	document.getElementById('kodesupplier').value=kodesupplier;
	document.getElementById('tgl').value=tgl;
	document.getElementById('thntnm').value=thntnm;
        
        document.getElementById('kdorg').disabled=true;
	document.getElementById('kodesupplier').disabled=true;
	document.getElementById('tgl').disabled=true;
	document.getElementById('thntnm').disabled=true;
        
	document.getElementById('harga').value=harga;
	document.getElementById('method').value='update';

	
}

function del(kdorg,kodesupplier,tgl,thntnm)
{
	param='method=delete'+'&kdorg='+kdorg+'&kodesupplier='+kodesupplier+'&tgl='+tgl+'&thntnm='+thntnm;
	//alert(param);
	tujuan='pabrik_slave_5hargatbs.php';
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

function getperiodesort()
{
	param='method=getperiodesort';	
	//alert(param);
	tujuan='pabrik_slave_5hargatbs.php';
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
						alert(con.responseText);
					}
					else 
					{
						//alert(con.responseText);
						document.getElementById('periodesort').innerHTML=con.responseText;
					  	getsuppsort();
					}//
				}
				else 
				{
					busy_off();
					error_catch(con.status);
				}
		  }	
	} 	
}


function getsuppsort()
{
	param='method=getsuppsort';	
	//alert(param);
	tujuan='pabrik_slave_5hargatbs.php';
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
						alert(con.responseText);
					}
					else 
					{
						//alert(con.responseText);
						document.getElementById('suppsort').innerHTML=con.responseText;
						getorgsort();
					}//
				}
				else 
				{
					busy_off();
					error_catch(con.status);
				}
		  }	
	} 	
}


function getorgsort()
{
	param='method=getorgsort';	
	//alert(param);
	tujuan='pabrik_slave_5hargatbs.php';
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
						alert(con.responseText);
					}
					else 
					{
						//alert(con.responseText);
						document.getElementById('kdorgsort').innerHTML=con.responseText;
					}//
				}
				else 
				{
					busy_off();
					error_catch(con.status);
				}
		  }	
	} 	
}


function ubah_list()
{
	periodesort=document.getElementById('periodesort').options[document.getElementById('periodesort').selectedIndex].value;
	suppsort=document.getElementById('suppsort').options[document.getElementById('suppsort').selectedIndex].value;
	kdorgsort=document.getElementById('kdorgsort').options[document.getElementById('kdorgsort').selectedIndex].value;
	//kodeblokHeader=document.getElementById('kodeblokHeader').options[document.getElementById('kodeblokHeader').selectedIndex].value;
	if(kdorgsort=='')
        {
            alert("Mill can't empty");return;
        }
        
        param='method=loadData'+'&periodesort='+periodesort+'&suppsort='+suppsort+'&kdorgsort='+kdorgsort;
	//alert (param);
	tujuan='pabrik_slave_5hargatbs.php';
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
}

