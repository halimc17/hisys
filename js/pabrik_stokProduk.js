//JS 

function cariBast(num)
{
    kdBrgSch=document.getElementById('kdBrgSch').options[document.getElementById('kdBrgSch').selectedIndex].value;
    tglSch=document.getElementById('tglSch').value;
    param='method=loadData'+'&tglSch='+tglSch+'&kdBrgSch='+kdBrgSch;
    param+='&page='+num;
    tujuan = 'pabrik_slave_stokProduk.php';
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
    kdOrg=trim(document.getElementById('kdOrg').value);
    kdBrg=trim(document.getElementById('kdBrg').value);
    sawal=trim(document.getElementById('sawal').value);
    prod=trim(document.getElementById('prod').value);
    pakai=trim(document.getElementById('pakai').value);
    jual=trim(document.getElementById('jual').value);
    sisa=trim(document.getElementById('sisa').value);
    cangkang=trim(document.getElementById('cangkang').value);
    ket=trim(document.getElementById('ket').value);
    method=document.getElementById('method').value;
        
    if(kdOrg=='' || tgl=='' || kdBrg=='')
    {
        alert('Please complete the form');return;
    }

    param='tgl='+tgl+'&kdOrg='+kdOrg+'&kdBrg='+kdBrg+'&sawal='+sawal+'&prod='+prod;
    param+='&pakai='+pakai+'&jual='+jual+'&sisa='+sisa+'&cangkang='+cangkang+'&ket='+ket;
    param+='&method='+method;
    tujuan='pabrik_slave_stokProduk.php';
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
	
    document.getElementById('method').value='insert';
    document.getElementById('tgl').value='';
    document.getElementById('kdOrg').value='';
    document.getElementById('kdBrg').value='';
    document.getElementById('sawal').value='';
    document.getElementById('prod').value='';
    document.getElementById('pakai').value='';
    document.getElementById('jual').value='';
    document.getElementById('sisa').value='';
    document.getElementById('cangkang').value='';
    document.getElementById('ket').value='';   
	//method=document.getElementById('method').value;
}

function loadData () 
{
 
	param='method=loadData';
	tujuan='pabrik_slave_stokProduk.php';
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
									//getperiodesort();
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}

function fillField(kdOrg,tgl,kdBrg,sawal,prod,pakai,jual,sisa,cangkang,ket)
{   
    document.getElementById('kdOrg').value=kdOrg;
    document.getElementById('tgl').value=tgl;
    document.getElementById('kdBrg').value=kdBrg;
    document.getElementById('sawal').value=sawal;
    document.getElementById('prod').value=prod;
    document.getElementById('pakai').value=pakai;
    document.getElementById('jual').value=jual;
    document.getElementById('sisa').value=sisa;
    document.getElementById('cangkang').value=cangkang;
    document.getElementById('ket').value=ket; 
    document.getElementById('method').value='update';	
}

function batal()
	{
            document.getElementById('kdOrgRep').value='';
            document.getElementById('kdBrgRep').value='';
            document.getElementById('tgl2Rep').value='';	
            document.getElementById('tgl1Rep').value='';
            document.getElementById('printContainer').innerHTML='';	
	}

function batalcari()
	{
            document.getElementById('kdBrgSch').value='';
            document.getElementById('tglSch').value='';	
	}


function del(kdOrg,tgl,kdBrg)
{
    param='method=delete'+'&kdOrg='+kdOrg+'&tgl='+tgl+'&kdBrg='+kdBrg;
    tujuan='pabrik_slave_stokProduk.php';
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
	tujuan='pabrik_slave_stokProduk.php';
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
	tujuan='pabrik_slave_stokProduk.php';
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
	tujuan='pabrik_slave_stokProduk.php';
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


function cari()
{
    kdBrgSch=document.getElementById('kdBrgSch').options[document.getElementById('kdBrgSch').selectedIndex].value;
    tglSch=document.getElementById('tglSch').value;
    param='method=loadData'+'&tglSch='+tglSch+'&kdBrgSch='+kdBrgSch;
    //alert (param);
    tujuan='pabrik_slave_stokProduk.php';
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

