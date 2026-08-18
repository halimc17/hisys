/**
 * @author repindra.ginting
 */
function ambilBuktiKas(tanggal)
{
        document.getElementById('space').innerHTML='';
        param='tanggal='+tanggal+'&aksi=ambilnokas';        
        tujuan = 'keu_slave_alokasiIDC.php';
        if(tanggal!='')	
        post_response_text(tujuan, param, respog);
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alertify.alert(con.responseText);
                            }
                            else {
                                    document.getElementById('nokas').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }             
}



function getAfd()
{
    
    val=document.getElementById('nokas').options[document.getElementById('nokas').selectedIndex].value;
    vals=val.split("#");
    kodeorg=vals[2];
    jumlah=vals[1];
    nokas=vals[0];

    alokasi=document.getElementById('alokasi').value;
    stblok=document.getElementById('stblok').value;
    param='alokasi='+alokasi+'&aksi=getAfd';    
    param+='&stblok='+stblok;

    if(val != '') {
        param += '&kodeorg='+kodeorg;
    }

    tujuan='keu_slave_alokasiIDC.php';
	if(alokasi=='')
	{
		document.getElementById('listafd').innerHTML='';
	}
	else
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
                            alertify.alert(con.responseText);
                            
                    }
                    else {
                        //alertify.alert(con.responseText);
                        document.getElementById('listafd').innerHTML=con.responseText; 
                        // document.getElementById('afdeling').innerHTML=con.responseText; 
                        // ambilBlok();
                    }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                    
                }
      }	
     } 
}

function ambilAlokasi()
{
    val=document.getElementById('nokas').options[document.getElementById('nokas').selectedIndex].value;
    stblok=document.getElementById('stblok').value;
	document.getElementById('listafd').innerHTML="";
    document.getElementById('space').innerHTML='';
    tujuan = 'keu_slave_alokasiIDC.php';    
    vals=val.split("#");
    kodeorg=vals[2];
    jumlah=vals[1];
    nokas=vals[0];
    if(val!='')
    param='kodeorg='+kodeorg+'&aksi=ambilAlokasi';
    param+='&stblok='+stblok;
    post_response_text(tujuan, param, respog);
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alertify.alert(con.responseText);
                            }
                            else {
								document.getElementById('alokasi').innerHTML=con.responseText;
								if(val=='')
								{
									document.getElementById('alokasi').innerHTML='';
								}
                                    
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }       
}

function ambilTipeAlokasi()
{
    val=document.getElementById('nokas').options[document.getElementById('nokas').selectedIndex].value;
    stblok=document.getElementById('stblok').value;
	document.getElementById('listafd').innerHTML="";
    document.getElementById('space').innerHTML='';
    tujuan = 'keu_slave_alokasiIDC.php';    
    vals=val.split("#");
    kodeorg=vals[2];
    jumlah=vals[1];
    nokas=vals[0];
    if(val!='')
    param='kodeorg='+kodeorg+'&aksi=ambilTipeAlokasi';
    param+='&stblok='+stblok;
    post_response_text(tujuan, param, respog);
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alertify.alert(con.responseText);
                            }
                            else {
								document.getElementById('stblok').innerHTML=con.responseText;
								if(val=='')
								{
									document.getElementById('stblok').innerHTML='';
								}
                                    
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }       
}

function ambilBlok(kebun)
{
	tanggal=document.getElementById('tanggal').value;
	stblok=document.getElementById('stblok').value;
	if(tanggal=='')
	{
		alertify.alert("Tanggal harus diisi");
		document.getElementById('space').innerHTML='';
		return;
	}
	val=document.getElementById('nokas').options[document.getElementById('nokas').selectedIndex].value;
	
	if(document.getElementById('alokasi').value=='')
	{
		alertify.alert("Alokasi biaya belum dipilih.");
		document.getElementById('space').innerHTML='';
		return;
	}
	else
	{
		alokasi=document.getElementById('alokasi').options[document.getElementById('alokasi').selectedIndex].value;
	}
    afdeling=document.getElementById('afdeling');
	listafd=document.getElementById('listafd').innerHTML;
	
	if(listafd=='')
	{
		alertify.alert("Alokasi biaya belum dipilih.");
		document.getElementById('space').innerHTML='';
		return;
	}
	
	chkAfd=document.getElementsByName('chkAfd[]');
	var vals = "";
	var countAfd=0;
	for (var i=0;i<chkAfd.length;i++) {
		if (chkAfd[i].checked) 
		{
			if(countAfd==0)
			{
				vals += chkAfd[i].value;
			}
			else
			{
				vals += "####"+chkAfd[i].value;
			}
			countAfd=countAfd+1;
		}
	}
	if(countAfd<=0)
	{
		alertify.alert("Alokasi biaya belum dipilih.");
		document.getElementById('space').innerHTML='';
		return;
	}
	afdeling=vals;
	
    tujuan = 'keu_slave_alokasiIDC.php';    
    val=val.split("#");
    nojurnal=val[0];    
    jumlah=val[1];
	param='kodeorg='+alokasi+'&jumlah='+jumlah+'&tanggal='+tanggal+'&nojurnal='+nojurnal+'&aksi=ambilBlok&afdeling='+afdeling;
    param+='&stblok='+stblok;
    // if(afdeling) 
    // {
        // param+='&afdeling='+afdeling.value;
    // }
    
    
    post_response_text(tujuan, param, respog);
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alertify.alert(con.responseText);
                            }
                            else {
                                    document.getElementById('space').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }    
}

function saveDistribusi(kebun)
{
val=document.getElementById('nokas').options[document.getElementById('nokas').selectedIndex].value;
debet=document.getElementById('debet').options[document.getElementById('debet').selectedIndex].value;
kredit=document.getElementById('kredit').options[document.getElementById('kredit').selectedIndex].value;
tanggal=document.getElementById('tanggal').value;
afdeling=document.getElementById('afdeling');
tujuan = 'keu_slave_alokasiIDC.php';
    val=val.split("#");
    jumlah=val[1];    
    nokas=val[0];
    
    chkAfd=document.getElementsByName('chkAfd[]');
	var vals = "";
	var countAfd=0;
	for (var i=0;i<chkAfd.length;i++) {
		if (chkAfd[i].checked) 
		{
			if(countAfd==0)
			{
				vals += chkAfd[i].value;
			}
			else
			{
				vals += "####"+chkAfd[i].value;
			}
			countAfd=countAfd+1;
		}
	}
	if(countAfd<=0)
	{
		alertify.alert("Alokasi biaya belum dipilih.");
		return;
	}
	afdeling=vals;
    
    param='kodeorg='+kebun+'&jumlah='+jumlah+'&debet='+debet+'&kredit='+kredit+'&nokas='+nokas+'&tanggal='+tanggal+'&aksi=simpanIDC&afdeling='+afdeling;
	
    //alertify.alert(param);
    if(confirm('Anda yakin sudah benar..?')){
        post_response_text(tujuan, param, respog);
    }
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alertify.alert(con.responseText);
                            }
                            else {
                                    document.getElementById('space').innerHTML='';
                                    alertify.alert('Done');
                                    window.location.reload();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }        
}
function hapusIni(nojurnal,tanggal,kodeorg)
{  
    param='nojurnal='+nojurnal+'&tanggal='+tanggal+'&kodeorg='+kodeorg+'&aksi=hapusJurnal';
    tujuan = 'keu_slave_alokasiIDC.php';
    if(confirm('Anda yakin mau menghapus: '+nojurnal+' ?'))
        post_response_text(tujuan, param, respog);
    
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alertify.alert(con.responseText);
                            }
                            else {
                                   alertify.alert('Done');
                                    window.location.reload();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }     
}

function selectall()
{
	chkAfd=document.getElementsByName('chkAfd[]');
	for (i = 0; i < chkAfd.length; i++)
	{
		chkAfd[i].checked = true ;
	}
}

function unselectall()
{
	chkAfd=document.getElementsByName('chkAfd[]');
	for (i = 0; i < chkAfd.length; i++)
	{
		chkAfd[i].checked = false ;
	}
}