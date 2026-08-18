function cariBast(num)
{
    kdBrgSch=document.getElementById('kdBrgSch').options[document.getElementById('kdBrgSch').selectedIndex].value;
    tglSch=document.getElementById('tglSch').value;
    param='method=loadData'+'&tglSch='+tglSch+'&kdBrgSch='+kdBrgSch;
    param+='&page='+num;
    tujuan = 'vhc_slave_vlkpakai.php';
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
    method=document.getElementById('method').value;
	notran=trim(document.getElementById('notran').value);
    tipe=document.getElementById('tipe').value;
	kdvhc=document.getElementById('kdvhc').value;
	kdbrg=document.getElementById('kdbrg').value;
	tgl=document.getElementById('tgl').value;
	
	kmhm=document.getElementById('kmhm').value;
	tekangin=document.getElementById('tekangin').value;
    posroda=trim(document.getElementById('posroda').value);
	ket=trim(document.getElementById('ket').value);
	
    if(kdvhc=='' || kdbrg=='' || kmhm=='' || tgl=='' || posroda=='' || tipe==''){
        alert('Please complete the form');return;
    }
    param='notran='+notran+'&kdvhc='+kdvhc+'&kdbrg='+kdbrg+'&tgl='+tgl+'&kmhm='+kmhm;
    param+='&method='+method+'&tekangin='+tekangin+'&posroda='+posroda+'&ket='+ket+'&tipe='+tipe;
    tujuan='vhc_slave_vlkpakai.php';
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

function hapus(){
    document.getElementById('method').value='insert';
    document.getElementById('notran').value='';
    document.getElementById('kdvhc').value='';
    document.getElementById('kdbrg').value='';
    document.getElementById('tgl').value='';
    document.getElementById('kmhm').value='0';
    document.getElementById('tekangin').value='0';
    document.getElementById('posroda').value='';
    document.getElementById('ket').value='';
	document.getElementById('tipe').value='';
}

function loadData(num) {
	notransch=trim(document.getElementById('notransch').value);
    tipesch=document.getElementById('tipesch').value;
	kdvhcsch=document.getElementById('kdvhcsch').value;
	kdbrgsch=document.getElementById('kdbrgsch').value;
	tglsch=document.getElementById('tglsch').value;
	
	//param='method=loadData';
	param='method=loadData'+'&notransch='+notransch+'&tipesch='+tipesch+'&kdvhcsch='+kdvhcsch+'&kdbrgsch='+kdbrgsch+'&tglsch='+tglsch;
	param+='&page='+num;
	tujuan='vhc_slave_vlkpakai.php';
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

function fillField(notran,kdvhc,kdbrg,posroda,tgl,kmhm,tekangin,ket,tipe){
    document.getElementById('notran').value=notran;
	document.getElementById('kdvhc').value=kdvhc;
	document.getElementById('kdbrg').value=kdbrg;
	document.getElementById('posroda').value=posroda;
    document.getElementById('tgl').value=tgl;
    document.getElementById('kmhm').value=kmhm;
    document.getElementById('tekangin').value=tekangin;
    document.getElementById('ket').value=ket; 
    document.getElementById('tipe').value=tipe;  
    document.getElementById('method').value='update';	
}

function batalRep()
	{
            document.getElementById('pabrikRep').value='';
            document.getElementById('brgRep').value='';
            document.getElementById('tgl2Rep').value='';	
            document.getElementById('tgl1Rep').value='';
            document.getElementById('printContainer').innerHTML='';	
	}

function batalcari(){
	document.getElementById('notransch').value='';
	document.getElementById('tipesch').value='';
	document.getElementById('kdvhcsch').value='';
	document.getElementById('kdbrgsch').value='';
	document.getElementById('tglsch').value='';
	loadData(0);
}


function del(notran)
{
    param='method=delete'+'&notran='+notran;
    tujuan='vhc_slave_vlkpakai.php';
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

function getperiodesort()
{
	param='method=getperiodesort';	
	//alert(param);
	tujuan='vhc_slave_vlkpakai.php';
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
	tujuan='vhc_slave_vlkpakai.php';
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
	tujuan='vhc_slave_vlkpakai.php';
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
    brgSch=document.getElementById('brgSch').options[document.getElementById('brgSch').selectedIndex].value;
    tglSch=document.getElementById('tglSch').value;
    param='method=loadData'+'&tglSch='+tglSch+'&brgSch='+brgSch;
    //alert (param);
    tujuan='vhc_slave_vlkpakai.php';
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

function getSounding(){
    pbrik=document.getElementById('pabrik');
    pbrik=pbrik.options[pbrik.selectedIndex].value;
    tngki=document.getElementById('tangki');
    tngki=tngki.options[tngki.selectedIndex].value;
    tanggal=document.getElementById('tgl').value;
    param='method=getSounding'+'&tgl='+tanggal+'&pabrik='+pbrik;
    param+='&tangki='+tngki;
    //alert (param);
    tujuan='vhc_slave_vlkpakai.php';
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
                        document.getElementById('sawal').value=con.responseText;
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }   
     } 
}
function itungSisa(){
    var hsl=0;
    sw=document.getElementById('sawal').value;
    if(sw!=0){
        rey=document.getElementById('jmlRey').value;
        if(parseFloat(sw)<parseFloat(rey)){
            alert("Jumlah Daur Ulang Lebih Besar Dari Saldo Awal");
            return;
        }
        hsl=parseFloat(sw)-parseFloat(rey);    
    }
    document.getElementById('jmlWaste').value=hsl;
}