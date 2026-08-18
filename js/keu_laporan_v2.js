function ambilAnakBB(pt) // list untuk buku besar, lihat tipe lokasi tugas
{ 
    param = 'pt=' + pt + '&tipe=bb';
    tujuan = 'keu_slave_getUnit.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('gudang').innerHTML = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function cekTanggal1(tanggal1)
{
    tanggal2 = document.getElementById('tgl2').value;
    param = 'pam=2&tanggal1=' + tanggal1 + '&tanggal2=' + tanggal2;
    tujuan = 'keu_slave_getAkun2.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById('tgl1').value = "";
                }
                else {
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cekTanggal2(tanggal2)
{
    tanggal1 = document.getElementById('tgl1').value;
    param = 'pam=3&tanggal1=' + tanggal1 + '&tanggal2=' + tanggal2;
    tujuan = 'keu_slave_getAkun2.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById('tgl2').value = "";
                }
                else {
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ambilAkun2(akun)
{
    param = 'pam=1&akun=' + akun;
    tujuan = 'keu_slave_getAkun2.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('akunsampai').innerHTML = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function getLaporanBukuBesarv1()
{
    pt = document.getElementById('pt');
    gudang = document.getElementById('gudang');
    tanggal1 = document.getElementById('tgl1');
    tanggal2 = document.getElementById('tgl2');
    akundari = document.getElementById('akundari');
    akunsampai = document.getElementById('akunsampai');
    tipe = document.getElementById('tipe');
    
    ptV = pt.options[pt.selectedIndex].value;
    gudangV = gudang.options[gudang.selectedIndex].value;
    tanggal1V = tanggal1.value;
    tanggal2V = tanggal2.value;
    akundariV = akundari.options[akundari.selectedIndex].value;
    akunsampaiV = akunsampai.options[akunsampai.selectedIndex].value;
    tipeV = tipe.options[tipe.selectedIndex].value;

    param = 'pt=' + ptV + '&gudang=' + gudangV + '&tanggal1=' + tanggal1V + '&tanggal2=' + tanggal2V + '&akundari=' + akundariV + '&akunsampai=' + akunsampaiV + '&tipe='+tipeV;
    tujuan = 'keu_slave_2bukubesarv2.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showById('container');
                    document.getElementById('container').innerHTML = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function jurnalv2KeExcel(ev, tujuan)
{
    pt = document.getElementById('pt');
    gudang = document.getElementById('gudang');
    tanggal1 = document.getElementById('tgl1');
    tanggal2 = document.getElementById('tgl2');
    akundari = document.getElementById('akundari');
    tipe = document.getElementById('tipe');
    try {
        akunsampai = document.getElementById('akunsampai');
        akunsampaiV = akunsampai.options[akunsampai.selectedIndex].value;
    }
    catch (err) {
        akunsampaiV = '';
    }

    ptV = pt.options[pt.selectedIndex].value;
    gudangV = gudang.options[gudang.selectedIndex].value;
    tanggal1V = tanggal1.value;
    tanggal2V = tanggal2.value;
    akundariV = akundari.options[akundari.selectedIndex].value;
    tipeV = tipe.options[tipe.selectedIndex].value;

    param = 'pt=' + ptV + '&gudang=' + gudangV + '&tanggal1=' + tanggal1V + '&tanggal2=' + tanggal2V + '&akundari=' + akundariV + '&akunsampai=' + akunsampaiV + '&tipe=' + tipeV;

    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev);
}

function jurnalv2KePDF(ev, tujuan)
{
    pt = document.getElementById('pt');
    gudang = document.getElementById('gudang');
    tanggal1 = document.getElementById('tgl1');
    tanggal2 = document.getElementById('tgl2');
    akundari = document.getElementById('akundari');
    akunsampai = document.getElementById('akunsampai');
    tipe = document.getElementById('tipe');
    
    ptV = pt.options[pt.selectedIndex].value;
    gudangV = gudang.options[gudang.selectedIndex].value;
    tanggal1V = tanggal1.value;
    tanggal2V = tanggal2.value;
    akundariV = akundari.options[akundari.selectedIndex].value;
    akunsampaiV = akunsampai.options[akunsampai.selectedIndex].value;
    tipeV = tipe.options[tipe.selectedIndex].value;

    param = 'pt=' + ptV + '&gudang=' + gudangV + '&tanggal1=' + tanggal1V + '&tanggal2=' + tanggal2V + '&akundari=' + akundariV + '&akunsampai=' + akunsampaiV + '&tipe=' + tipeV;

    judul = 'Report PDF';
    printFile(param, tujuan, judul, ev)
}

function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
function getLaporanNeracav2()
{
        pt  =document.getElementById('pt');
        unit    =document.getElementById('gudang');
        periode =document.getElementById('periode');
        periode1=document.getElementById('periode1');       
        tplData1=document.getElementById('tplData');   
        pt  =pt.options[pt.selectedIndex].value;
        unit    =unit.options[unit.selectedIndex].value;
        periode =periode.options[periode.selectedIndex].value;
        periode1    =periode1.options[periode1.selectedIndex].value;
          tplData1 =tplData1.options[tplData1.selectedIndex].value;

            revisi =document.getElementById('revisi');
            revisi=revisi.options[revisi.selectedIndex].value;        
        param='pt='+pt+'&unit='+unit+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
        param+='&tplData='+tplData1;
        tujuan='keu_slave_2neracav2.php';
        post_response_text(tujuan, param, respog);
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                showById('printPanel');
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

function getLaporanKeuanganArusKasTidakLangsung()
{
//    pt=piti;
//    unit=yunit;
//    periode=period;

    pt	=document.getElementById('pt');
    unit    =document.getElementById('gudang');
    periode =document.getElementById('periode');
    pt	=pt.options[pt.selectedIndex].value;
    unit	=unit.options[unit.selectedIndex].value;
    periode	=periode.options[periode.selectedIndex].value;

    param='pt='+pt+'&unit='+unit+'&periode='+periode;
    tujuan='keu_slave_2laporankeuanganArusKasTidakLangsung.php';
    post_response_text(tujuan, param, respog);
    
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showById('printPanel');
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

function getLaporanKeuanganIkhtisarKeuangan()
{
//    pt=piti;
//    unit=yunit;
//    periode=period;

    pt	=document.getElementById('pt');
    unit    =document.getElementById('gudang');
    periode =document.getElementById('periode');
    pt	=pt.options[pt.selectedIndex].value;
    unit	=unit.options[unit.selectedIndex].value;
    periode	=periode.options[periode.selectedIndex].value;

    param='pt='+pt+'&unit='+unit+'&periode='+periode;
    tujuan='keu_slave_2laporankeuanganIkhtisarKeuangan.php';
    post_response_text(tujuan, param, respog);
    
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showById('printPanel');
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

function sebelumaruskasjalaninlabarugi(){
    pt=document.getElementById('pt');
    pt=pt.options[pt.selectedIndex].value;
    unit=document.getElementById('gudang');
    unit=unit.options[unit.selectedIndex].value;
    periode=document.getElementById('periode');
    periode=periode.options[periode.selectedIndex].value;
    
    periode1='akhir';
    revisi='0';
    tplData1='0';
    tplData2='0';

    param='pt='+pt+'&unit='+unit+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
    param+='&tplData='+tplData1+'&tplData2='+tplData2;
    tujuan='keu_slave_2labarugiv2.php';

    if(pt==''){
        alert('Please fill Company');
    }else{
        post_response_text(tujuan, param, respog);            
    }        
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    sebelumaruskasjalaninneraca();
//                    showById('printPanel');
//                    document.getElementById('container').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function sebelumaruskasjalaninneraca()
{
//    pt=piti;
//    unit=yunit;
//    periode=period;

    pt=document.getElementById('pt');
    pt=pt.options[pt.selectedIndex].value;
    unit=document.getElementById('gudang');
    unit=unit.options[unit.selectedIndex].value;
    periode=document.getElementById('periode');
    periode=periode.options[periode.selectedIndex].value;
    
    periode1='akhir';
    revisi='0';
    tplData1='0';
    
    param='pt='+pt+'&unit='+unit+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
    param+='&tplData='+tplData1;
    tujuan='keu_slave_2neracav2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    sebelumaruskasjalaninneracadestahunlalu();
//                    showById('printPanel');
//                    document.getElementById('container').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function sebelumaruskasjalaninneracadestahunlalu()
{
//    pt=piti;
//    unit=yunit;
//    periode=period;

    pt=document.getElementById('pt');
    pt=pt.options[pt.selectedIndex].value;
    unit=document.getElementById('gudang');
    unit=unit.options[unit.selectedIndex].value;
    periode=document.getElementById('periode');
    periode=periode.options[periode.selectedIndex].value;
    
    periodedes=periode.substring(0,4);
    periodedes=periodedes-1;
    periodedes=periodedes+'-12';
    
    periode1='akhir';
    revisi='0';
    tplData1='0';
    
    param='pt='+pt+'&unit='+unit+'&periode='+periodedes+'&periode1='+periode1+'&revisi='+revisi;

    param+='&tplData='+tplData1;
    tujuan='keu_slave_2neracav2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    sebelumaruskasjalaninneracanovtahunlalu();
//                    showById('printPanel');
//                    document.getElementById('container').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function sebelumaruskasjalaninneracanovtahunlalu()
{
//    pt=piti;
//    unit=yunit;
//    periode=period;

    pt=document.getElementById('pt');
    pt=pt.options[pt.selectedIndex].value;
    unit=document.getElementById('gudang');
    unit=unit.options[unit.selectedIndex].value;
    periode=document.getElementById('periode');
    periode=periode.options[periode.selectedIndex].value;
    
    periodedes=periode.substring(0,4);
    periodedes=periodedes-1;
    periodedes=periodedes+'-11';
    
    periode1='akhir';
    revisi='0';
    tplData1='0';
    
    param='pt='+pt+'&unit='+unit+'&periode='+periodedes+'&periode1='+periode1+'&revisi='+revisi;

    param+='&tplData='+tplData1;
    tujuan='keu_slave_2neracav2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    sebelumaruskasjalaninlabarugidestahunlalu();
//                    showById('printPanel');
//                    document.getElementById('container').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function sebelumaruskasjalaninlabarugidestahunlalu(){
    pt=document.getElementById('pt');
    pt=pt.options[pt.selectedIndex].value;
    unit=document.getElementById('gudang');
    unit=unit.options[unit.selectedIndex].value;
    periode=document.getElementById('periode');
    periode=periode.options[periode.selectedIndex].value;
    
    periodedes=periode.substring(0,4);
    periodedes=periodedes-1;
    periodedes=periodedes+'-12';
    
    periode1='akhir';
    revisi='0';
    tplData1='0';
    tplData2='0';

    param='pt='+pt+'&unit='+unit+'&periode='+periodedes+'&periode1='+periode1+'&revisi='+revisi;
    param+='&tplData='+tplData1+'&tplData2='+tplData2;
    tujuan='keu_slave_2labarugiv2.php';

    if(pt==''){
        alert('Please fill Company');
    }else{
        post_response_text(tujuan, param, respog);            
    }        
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    sebelumaruskasjalaninlabaruginovtahunlalu();
//                    showById('printPanel');
//                    document.getElementById('container').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function sebelumaruskasjalaninlabaruginovtahunlalu(){
    pt=document.getElementById('pt');
    pt=pt.options[pt.selectedIndex].value;
    unit=document.getElementById('gudang');
    unit=unit.options[unit.selectedIndex].value;
    periode=document.getElementById('periode');
    periode=periode.options[periode.selectedIndex].value;
    
    periodedes=periode.substring(0,4);
    periodedes=periodedes-1;
    periodedes=periodedes+'-11';
    
    periode1='akhir';
    revisi='0';
    tplData1='0';
    tplData2='0';

    param='pt='+pt+'&unit='+unit+'&periode='+periodedes+'&periode1='+periode1+'&revisi='+revisi;
    param+='&tplData='+tplData1+'&tplData2='+tplData2;
    tujuan='keu_slave_2labarugiv2.php';

    if(pt==''){
        alert('Please fill Company');
    }else{
        post_response_text(tujuan, param, respog);            
    }        
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    getLaporanKeuanganArusKasTidakLangsung();
//                    showById('printPanel');
//                    document.getElementById('container').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function lihatDetailNeraca(nourut,jmlhrow,lvl){
    isiurut=nourut.split("###");
    if(isiurut[1].substr(0,4)=='1261'){
        var adr=0;
    }else{
        var adr=1;
    }
    for(adr;adr<=jmlhrow;adr++){  
        var nourutdt;
        if(isiurut[1]==''){
            nourutdt=isiurut[0]+"_"+lvl+"_"+adr;    
        }else{
                if(isiurut[1]=='126'){
                    if(adr<10){
                        tmbhan="0"+adr;
                    }else{
                        if(adr!=jmlhrow){
                            tmbhan=adr;    
                        }else{
                            tmbhan="99";
                        }
                    }
                    if(isiurut[1].length==5){
                        nourutdt=isiurut[0]+"_"+isiurut[1]+dert+"_"+lvl+"_"+adr;    
                    }else{
                        nourutdt=isiurut[0]+"_"+isiurut[1]+tmbhan+"_"+lvl+"_"+adr;    
                    }
                }else{
                    if((isiurut[1].substr(0,4)=='1261')||(isiurut[1]=='1270')){
                        if(isiurut[1].substr(0,4)=='1261'){
                            dert=adr+1;
                            if(isiurut[1].length==5){
                                nourutdt=isiurut[0]+"_"+isiurut[1]+"_"+lvl+"_"+dert;    
                            }else{
                                nourutdt=isiurut[0]+"_"+isiurut[1]+adr+"_"+lvl+"_"+dert;    
                            }
                        }else{
                            nourutdt=isiurut[0]+"_"+isiurut[1]+adr+"_"+lvl+"_"+adr;
                        }
                    }else{
                        nourutdt=isiurut[0]+"_"+isiurut[1]+"_"+lvl+"_"+adr;    
                    }
                }
        }
        var row = document.getElementById(nourutdt);
        if (row.style.display == '') {
            row.style.display = 'none';
        }
        else {
            row.style.display = '';
        }
        
    }
}
function getLaporanNeracav3(){
            pt  =document.getElementById('pt');
            unit    =document.getElementById('gudang');
            periode =document.getElementById('periode');
            periode1=document.getElementById('periode1');       
            tplData1=document.getElementById('tplData');   
            tplData2=document.getElementById('tplData2');   

            pt  =pt.options[pt.selectedIndex].value;
            unit    =unit.options[unit.selectedIndex].value;
            periode =periode.options[periode.selectedIndex].value;
            periode1    =periode1.options[periode1.selectedIndex].value;
            tplData1 =tplData1.options[tplData1.selectedIndex].value;
            tplData2 =tplData2.options[tplData2.selectedIndex].value;

            revisi =document.getElementById('revisi');
            revisi=revisi.options[revisi.selectedIndex].value;              
        
        param='pt='+pt+'&unit='+unit+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
        param+='&tplData='+tplData1+'&tplData2='+tplData2;
        tujuan='keu_slave_2labarugiv2.php';

        if(pt==''){
            alert('Please fill Company');
        }else{
            post_response_text(tujuan, param, respog);            
        }        
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                showById('printPanel');
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

// function lihatDetailNeraca(nourut,jmlhrow,lvl){
//     isiurut=nourut.split("###");
    
//     for(adr;adr<=jmlhrow;adr++){  
//         var nourutdt;
//         if(isiurut[1]==''){
//             nourutdt=isiurut[0]+"_"+lvl+"_"+adr;    
//         }else{
//             if((isiurut[1]=='126')||(isiurut[1].substr(0,4)=='1261')||(isiurut[1]=='1270')){
//                 if(isiurut[1].substr(0,3)=='126'){
//                     if(adr<10){
//                         tmbhan="0"+adr;
//                     }else{
//                         if(adr!=jmlhrow){
//                             tmbhan=adr;    
//                         }else{
//                             tmbhan="99";
//                         }
//                     }
//                     if(isiurut[1].substr(0,4)=='1261'){
//                         var adr=0;
//                     }else{
//                         var adr=1;
//                     }
//                     if(isiurut[1].length==5){
//                         nourutdt=isiurut[0]+"_"+isiurut[1]+dert+"_"+lvl+"_"+adr;    
//                     }else{
//                         nourutdt=isiurut[0]+"_"+isiurut[1]+tmbhan+"_"+lvl+"_"+adr;    
//                     }
//                 }else{
//                     nourutdt=isiurut[0]+"_"+isiurut[1]+adr+"_"+lvl+"_"+adr;
//                 }
//             }else{
//                 nourutdt=isiurut[0]+"_"+isiurut[1]+"_"+lvl+"_"+adr;    
//             }
//         }
//         alert(nourutdt);
//         var row = document.getElementById(nourutdt);
//         if (row.style.display == '') {
//             row.style.display = 'none';
//         }
//         else {
//             row.style.display = '';
//         }
        
//     }
// }
function fisikKePDF(ev,tujuan)
{
        pt      =document.getElementById('pt');
        gudang  =document.getElementById('gudang');
        periode =document.getElementById('periode');
        tplData1=document.getElementById('tplData');   
        revisi='';
        try{
        periode1 =document.getElementById('periode1').options[document.getElementById('periode1').selectedIndex].value;
                revisi =document.getElementById('revisi');
        revisi=revisi.options[revisi.selectedIndex].value;  
        }
        catch(err){
          periode1='';  
        }
                pt      =pt.options[pt.selectedIndex].value;
                gudang  =gudang.options[gudang.selectedIndex].value;
                periode =periode.options[periode.selectedIndex].value;
                tplData1 =tplData1.options[tplData1.selectedIndex].value;
        judul='Report PDF'; 
        param='pt='+pt+'&gudang='+gudang+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
        param+='&tplData='+tplData1;
        printFile(param,tujuan,judul,ev);    
}
function fisikKeExcel(ev,tujuan){
        pt  =document.getElementById('pt');
        gudang  =document.getElementById('gudang');
        periode =document.getElementById('periode');
        tplData1=document.getElementById('tplData');   
        revisi='';
        try{
        periode1 =document.getElementById('periode1').options[document.getElementById('periode1').selectedIndex].value;
        revisi =document.getElementById('revisi');
        revisi=revisi.options[revisi.selectedIndex].value;  
        }
        catch(err){
          periode1='';  
        }   
                pt  =pt.options[pt.selectedIndex].value;
                gudang  =gudang.options[gudang.selectedIndex].value;
                periode =periode.options[periode.selectedIndex].value;
                tplData1 =tplData1.options[tplData1.selectedIndex].value;

        judul='Report Ms.Excel';    
        param='pt='+pt+'&gudang='+gudang+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
        param+='&tplData='+tplData1;
        printFile(param,tujuan,judul,ev);    
}

function fisikKeExcelAKTL(ev,tujuan){
        pt  =document.getElementById('pt');
        gudang  =document.getElementById('gudang');
        periode =document.getElementById('periode');
                pt  =pt.options[pt.selectedIndex].value;
                gudang  =gudang.options[gudang.selectedIndex].value;
                periode =periode.options[periode.selectedIndex].value;

        judul='Report Ms.Excel';    
        param='pt='+pt+'&gudang='+gudang+'&periode='+periode;
        printFile(param,tujuan,judul,ev);    
}

function getLaporanRasio(ev,pros){
        pt  =document.getElementById('pt');
        periode =document.getElementById('periode');
        periode1=document.getElementById('periode1');       
        tplData1=document.getElementById('tplData');   
        pt  =pt.options[pt.selectedIndex].value;
        periode =periode.options[periode.selectedIndex].value;
        periode1    =periode1.options[periode1.selectedIndex].value;
        tplData1 =tplData1.options[tplData1.selectedIndex].value;
        param='pt='+pt+'&periode='+periode+'&periode1='+periode1;
        param+='&tplData='+tplData1+'&proses='+pros;
        tujuan='keu_slave_2rasio.php';
        
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                showById('printPanel');
                                                document.getElementById('container').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }   
        
        if(pros=='preview'){
                post_response_text(tujuan, param, respog);
            } else if(pros=='excel'){
                judul='Report Ms.Excel';    
                printFile(param,tujuan,judul,ev) 
            }else if(pros=='pdf'){
                  judul='Report PDF'; 
                  tujuan='keu_slave_2rasio_pdf.php';
                  printFile(param,tujuan,judul,ev);  
            }
}