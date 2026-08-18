//JS 

function cariBast(num){
    tglSch=document.getElementById('tglSch').value;
    param='method=loadData'+'&tglSch='+tglSch;
    param+='&page='+num;
    tujuan = 'pabrik_slave_analisaproduksi.php';
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


function simpan(){
	
    kodeorg 	=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	tanggal  	=document.getElementById('tanggal').value;
	
	dirt		=document.getElementById('dirtcpo').value;
	kadarair	=document.getElementById('kadaraircpo').value;
	ffa			=document.getElementById('ffacpo').value;
	usbcpo		=document.getElementById('usbcpo').value;
	dirtpk		=document.getElementById('dirtpk').value;
	kadarairpk	=document.getElementById('kadarairpk').value;
	ffapk		=document.getElementById('ffapk').value;
	
	
    method=document.getElementById('method').value;
        
    
	param='kodeorg='+kodeorg+'&tanggal='+tanggal;
	param+='&dirt='+dirt+'&kadarair='+kadarair+'&ffa='+ffa+'&usbcpo='+usbcpo;
	param+='&dirtpk='+dirtpk+'&kadarairpk='+kadarairpk+'&ffapk='+ffapk;
    param+='&method='+method;
    tujuan='pabrik_slave_analisaproduksi.php';
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
	batalcari();
    document.getElementById('usbcpo').value='0';
    document.getElementById('dirtcpo').value='0';
	document.getElementById('kadaraircpo').value='0';
	document.getElementById('ffacpo').value='0';
	document.getElementById('dirtpk').value='0';
	document.getElementById('kadarairpk').value='0';
	document.getElementById('ffapk').value='0';
	document.getElementById('formhead').style.display='none';
}

function loadData(){
	param='method=loadData';
	tujuan='pabrik_slave_analisaproduksi.php';
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

function fillField(kodeorg,tanggal,kadarkotoran,kadarair,ffa,dobi,kadarkotoranpk,kadarairpk,ffapk){  
	document.getElementById('formhead').style.display='block';
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('dirtcpo').value=kadarkotoran;
	document.getElementById('kadaraircpo').value=kadarair;
	document.getElementById('ffacpo').value=ffa;
	document.getElementById('usbcpo').value=dobi;
	document.getElementById('dirtpk').value=kadarkotoranpk;
	document.getElementById('kadarairpk').value=kadarairpk;
	document.getElementById('ffapk').value=ffapk;
    document.getElementById('method').value='update';	
}



function batalcari(){
		document.getElementById('tglSch').value='';	
		loadData();
}



function cari(){
    tglSch=document.getElementById('tglSch').value;
    param='method=loadData'+'&tglSch='+tglSch;
    //alert (param);
    tujuan='pabrik_slave_analisaproduksi.php';
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

