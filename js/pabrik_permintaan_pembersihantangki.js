//JS 
//


function getTangki()
{
    pabrik=document.getElementById('pabrik').value; 
    param='method=getTangki'+'&pabrik='+pabrik;
    tujuan='pabrik_slave_permintaan_pembersihantangki.php';
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
                    document.getElementById('tangki').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}

function getBarang(){
    tangki=document.getElementById('tangki').value;
    pabrik=document.getElementById('pabrik').value; 
    param='method=getBarang'+'&pabrik='+pabrik+'&tangki='+tangki;
    tujuan='pabrik_slave_permintaan_pembersihantangki.php';
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
                    document.getElementById('barang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}

function cariBast(num){
    brgSch=document.getElementById('brgSch').options[document.getElementById('brgSch').selectedIndex].value;
    tglSch=document.getElementById('tglSch').value;
    param='method=loadData'+'&tglSch='+tglSch+'&brgSch='+brgSch;
    param+='&page='+num;
    tujuan = 'pabrik_slave_pembersihantangki.php';
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
    pabrik=trim(document.getElementById('pabrik').value);
    tangki=trim(document.getElementById('tangki').value);
    barang=trim(document.getElementById('barang').value);
    tipe=trim(document.getElementById('tipe').value);
    tgl=trim(document.getElementById('tgl').value);
    jm=trim(document.getElementById('jm').value);
    mn=trim(document.getElementById('mn').value);
    noba=trim(document.getElementById('noba').value);
    sawal=trim(document.getElementById('sawal').value);
    jmlRey=trim(document.getElementById('jmlRey').value);
    jmlWaste=trim(document.getElementById('jmlWaste').value);
    ket=trim(document.getElementById('ket').value);

    persetujuan1=document.getElementById('persetujuan1').options[document.getElementById('persetujuan1').selectedIndex].value;
    persetujuan2=document.getElementById('persetujuan2').options[document.getElementById('persetujuan2').selectedIndex].value;
    persetujuan3=document.getElementById('persetujuan3').options[document.getElementById('persetujuan3').selectedIndex].value;

    
   
    if(pabrik=='' || tangki=='' || barang=='' || tgl=='' || jm=='' || mn=='')
    {
        alert('Please complete the form');return;
    }

    param='pabrik='+pabrik+'&tangki='+tangki+'&barang='+barang+'&tipe='+tipe+'&tgl='+tgl+'&jm='+jm;
    param+='&mn='+mn+'&jumlah='+jmlWaste+'&ket='+ket;
    param+='&persetujuan2='+persetujuan2+'&persetujuan3='+persetujuan3+'&persetujuan1='+persetujuan1;
    param+='&method='+method+'&noba='+noba+'&sawal='+sawal+'&jmlRey='+jmlRey;
    
    tujuan='pabrik_slave_permintaan_pembersihantangki.php';
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
    document.getElementById('pabrik').value='';
    document.getElementById('tangki').value='';
    document.getElementById('barang').value='';
    document.getElementById('tipe').value='';
    document.getElementById('tgl').value='';
    document.getElementById('jm').value='00';
    document.getElementById('mn').value='00';
    
    document.getElementById('pabrik').disabled=false;
    document.getElementById('tangki').disabled=false;
    document.getElementById('barang').disabled=false;
    document.getElementById('tgl').disabled=false;
    document.getElementById('jm').disabled=false;
    document.getElementById('mn').disabled=false;
    
    
    document.getElementById('jmlWaste').value='0';
    document.getElementById('sawal').value='0';
    document.getElementById('jmlRey').value='0';
    document.getElementById('ket').value='';
    document.getElementById('noba').value='';
	//method=document.getElementById('method').value;
}

function loadData () 
{
	param='method=loadData';
	tujuan='pabrik_slave_pembersihantangki.php';
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

function fillField(pabrik,tangki,nmtangki,kdbarang,nmbarang,tipe,tgl,jm,mn,jumlah,ket,nba,swl,reyjmlh)
{
    
    
    document.getElementById('pabrik').value=pabrik;
    document.getElementById('tangki').innerHTML="<option value='"+ tangki +"'>"+ nmtangki +"</option>";
    document.getElementById('barang').innerHTML="<option value='"+ kdbarang +"'>"+ nmbarang +"</option>";
    document.getElementById('tipe').value=tipe;
    document.getElementById('tgl').value=tgl;
    document.getElementById('jm').value=jm;
    document.getElementById('mn').value=mn;
    document.getElementById('jmlWaste').value=jumlah;
    document.getElementById('ket').value=ket; 
    document.getElementById('sawal').value=swl; 
    document.getElementById('jmlRey').value=reyjmlh; 
    document.getElementById('noba').value=nba; 
    
    document.getElementById('pabrik').disabled=true;
    document.getElementById('tangki').disabled=true;
    document.getElementById('barang').disabled=true;
    document.getElementById('tgl').disabled=true;
    document.getElementById('jm').disabled=true;
    document.getElementById('mn').disabled=true;
    
    document.getElementById('method').value='update';   
    tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
}

function batalRep()
	{
            document.getElementById('pabrikRep').value='';
            document.getElementById('brgRep').value='';
            document.getElementById('tgl2Rep').value='';	
            document.getElementById('tgl1Rep').value='';
            document.getElementById('printContainer').innerHTML='';	
	}

function batalcari()
	{
            document.getElementById('brgSch').value='';
            document.getElementById('tglSch').value='';	
	}


function del(pabrik,tangki,barang,tgl,jm,mn)
{
    param='method=delete'+'&pabrik='+pabrik+'&tangki='+tangki+'&barang='+barang;
    param+='&tgl='+tgl+'&jm='+jm+'&mn='+mn;
    tujuan='pabrik_slave_pembersihantangki.php';
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
	tujuan='pabrik_slave_pembersihantangki.php';
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
	tujuan='pabrik_slave_pembersihantangki.php';
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
	tujuan='pabrik_slave_pembersihantangki.php';
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
    tujuan='pabrik_slave_pembersihantangki.php';
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
    tujuan='pabrik_slave_pembersihantangki.php';
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