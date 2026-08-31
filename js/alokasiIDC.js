/**
 * @author sendi
 */
function initSelect2()
{
    // pakai jQuery(...) bukan $(...): js/drag.js mendefinisikan ulang $() jadi document.getElementById, menimpa $ milik jQuery
    jQuery('.select2').select2({
        dropdownAutoWidth:true
    });
}

function newdata()
{
    document.getElementById('tanggal').value='';
    document.getElementById('nokas').innerHTML="<option value=''>Pilih....</option>";
    document.getElementById('detailjurnal').innerHTML='';
    document.getElementById('stblok').innerHTML='';
    document.getElementById('thntnm').innerHTML='';
    document.getElementById('alokasi').innerHTML='';
    document.getElementById('listafd').innerHTML='';
    document.getElementById('space').innerHTML='';
    initSelect2();

    document.getElementById('header').style.display='block';
    document.getElementById('listdata').style.display='none';
}

function displaylist()
{
    document.getElementById('header').style.display='none';
    document.getElementById('listdata').style.display='block';
    loaddataIDC();
}

function downloadExcel(data, filename)
{
    var blob = new Blob(['﻿', data], { type: 'application/vnd.ms-excel' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

function exportListExcel()
{
    var tbl = document.getElementById('tblListIDC');
    if(!tbl){ return; }
    var clone = tbl.cloneNode(true);
    var rows = clone.querySelectorAll('tr');
    for(var i=0;i<rows.length;i++){
        var cells = rows[i].querySelectorAll('td,th');
        if(cells.length>0){
            cells[cells.length-1].parentNode.removeChild(cells[cells.length-1]);
        }
    }
    downloadExcel(clone.outerHTML, 'Daftar_Alokasi_IDC.xls');
}

function loaddataIDC()
{
    param='notransaksisch='+document.getElementById('notransaksisch').value;
    param+='&tglmulaisch='+document.getElementById('tglmulaisch').value;
    param+='&tglselesaisch='+document.getElementById('tglselesaisch').value;
    param+='&unitsch='+document.getElementById('unitsch').value;
    param+='&aksi=listIDC';
    tujuan = 'keu_slave_alokasiIDC.php';
    post_response_text(tujuan, param, respog);
        function respog(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alertify.alert(con.responseText);
                        }
                        else {
                                document.getElementById('containIDC').innerHTML=con.responseText;
                        }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
            }
        }
}

function previewIDC(nojurnal, ev)
{
    param='nojurnal='+nojurnal+'&aksi=detailIDC';
    tujuan = 'keu_slave_alokasiIDC.php';
    post_response_text(tujuan, param, respog);
        function respog(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alertify.alert(con.responseText);
                        }
                        else {
                                showDialog1('Detail Jurnal '+nojurnal, "<div style='overflow:auto;max-width:100%;max-height:100%'>"+con.responseText+"</div>", 750, 300, ev);
                        }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
            }
        }
}

function excelIDC(nojurnal)
{
    param='nojurnal='+nojurnal+'&aksi=detailIDC&mode=excel';
    tujuan = 'keu_slave_alokasiIDC.php';
    post_response_text(tujuan, param, respog);
        function respog(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alertify.alert(con.responseText);
                        }
                        else {
                                downloadExcel(con.responseText, 'Detail_'+nojurnal.replace(/\//g,'_')+'.xls');
                        }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
            }
        }
}

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
                                    initSelect2();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }
}

function tampilkanJurnal()
{
    val=document.getElementById('nokas').options[document.getElementById('nokas').selectedIndex].value;
    vals=val.split("#");
    nojurnal=vals[0];
    if(val=='')
    {
        document.getElementById('detailjurnal').innerHTML='';
        return;
    }
    param='nojurnal='+nojurnal+'&aksi=detailJurnal';
    tujuan = 'keu_slave_alokasiIDC.php';
    post_response_text(tujuan, param, respog);
        function respog(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alertify.alert(con.responseText);
                        }
                        else {
                                document.getElementById('detailjurnal').innerHTML=con.responseText;
                        }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
            }
        }
}

function ambilTahunTanam(onDone)
{
    val=document.getElementById('nokas').options[document.getElementById('nokas').selectedIndex].value;
    vals=val.split("#");
    kodeorg=vals[2];
    stblok=document.getElementById('stblok').value;
    if(val=='')
    {
        document.getElementById('thntnm').innerHTML='';
        if(onDone) onDone();
        return;
    }
    param='kodeorg='+kodeorg+'&stblok='+stblok+'&aksi=ambilTahunTanam';
    tujuan = 'keu_slave_alokasiIDC.php';
    post_response_text(tujuan, param, respog);
        function respog(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alertify.alert(con.responseText);
                        }
                        else {
                                document.getElementById('thntnm').innerHTML=con.responseText;
                                initSelect2();
                        }
                        if(onDone) onDone();
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
    thntnm=document.getElementById('thntnm').value;
    param='alokasi='+alokasi+'&aksi=getAfd';
    param+='&stblok='+stblok;
    param+='&thntnm='+thntnm;

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
								initSelect2();
								ambilTahunTanam();
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
								initSelect2();
								ambilTahunTanam(tampilkanJurnal);
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }       
}

function ambilBlok(kebun, mode)
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
    param+='&thntnm='+document.getElementById('thntnm').value;
    param+='&mode='+(mode?mode:'');
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
                                    if(mode=='excel'){
                                        downloadExcel(con.responseText, 'Preview_Alokasi_IDC.xls');
                                    }else{
                                        document.getElementById('space').innerHTML=con.responseText;
                                        initSelect2();
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

    if(debet=='' || kredit=='')
    {
        alertify.alert("Akun debet dan kredit harus dipilih.");
        return;
    }
    if(debet==kredit)
    {
        alertify.alert("Akun debet dan kredit tidak boleh sama.");
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
		return;
	}
	afdeling=vals;
    
    param='kodeorg='+kebun+'&jumlah='+jumlah+'&debet='+debet+'&kredit='+kredit+'&nokas='+nokas+'&tanggal='+tanggal+'&aksi=simpanIDC&afdeling='+afdeling;
    param+='&stblok='+document.getElementById('stblok').value;
    param+='&thntnm='+document.getElementById('thntnm').value;

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

jQuery(document).ready(function(){
	initSelect2();
});