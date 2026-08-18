function tampilkanCatu(){
    unitId=document.getElementById('unitId');
    unitId=unitId.options[unitId.selectedIndex].value;
    komoditi=document.getElementById('komoditi');
    komoditi=komoditi.options[komoditi.selectedIndex].value;
    tgl=document.getElementById('tgl');
    tgl=tgl.options[tgl.selectedIndex].value;
    param='pt='+unitId+'&komoditi='+komoditi+'&tgl='+tgl+'&proses=preview';
    tujuan='pabrik_slave_penerimaankomoditi.php';
    post_response_text(tujuan, param, respog);
    function respog(){
      if(con.readyState==4){
            if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else {                     
                          document.getElementById('showDt').style.display="block";
                          document.getElementById('container').innerHTML=con.responseText;
                          closeDialog4();
                      }
                    }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      } 
    }  
}

function saveData(rowdt){
    var isiDt='';
    var tempDt='';
    for(i=1;i<=rowdt;i++){
        notktKrm=document.getElementById('notktkrm_'+i).value;
        brtktKrm=document.getElementById('brtktkrm_'+i).value;    
        notktTrm=document.getElementById('notkttrm_'+i).value;    
        brtktTrm=document.getElementById('brtkttrm_'+i).value;    
        if(i==1){
            isiDt="&dataKirim["+i+"][noTiketKrm]="+notktKrm;
            isiDt+="&dataKirim["+i+"][beratKrm]="+brtktKrm;
            isiDt+="&dataKirim["+i+"][noTiketTrm]="+notktTrm;
            isiDt+="&dataKirim["+i+"][beratTrm]="+brtktTrm;
        }else{
            isiDt+="&dataKirim["+i+"][noTiketKrm]="+notktKrm;
            isiDt+="&dataKirim["+i+"][beratKrm]="+brtktKrm;
            isiDt+="&dataKirim["+i+"][noTiketTrm]="+notktTrm;
            isiDt+="&dataKirim["+i+"][beratTrm]="+brtktTrm;
        }
    }
    unitId=document.getElementById('unitId');
    unitId=unitId.options[unitId.selectedIndex].value;
    komoditi=document.getElementById('komoditi');
    komoditi=komoditi.options[komoditi.selectedIndex].value;
    tgl=document.getElementById('tgl').value;
    totTrm=document.getElementById('totTrm').value;
    totKrm=document.getElementById('totKrm').value;
    param ='proses=saveAll'+'&pt='+unitId+'&komoditi='+komoditi+'&tgl='+tgl;
    param+='&totKrm='+totKrm+'&totTrm='+totTrm;
    param+=isiDt;
    tujuan = 'pabrik_slave_penerimaankomoditi.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else {
                    alert("Data Sudah Tersimpan");
                    document.getElementById('container').innerHTML="";
                    document.getElementById('showDt').style.display="none";
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged,0);    
}

function loadData(num,modetgl){
    ptCari=document.getElementById('ptCr');
    ptCari=ptCari.options[ptCari.selectedIndex].value;
    periode=document.getElementById('periode');
    periode=periode.options[periode.selectedIndex].value;
    periode2=document.getElementById('periode2');
    periode2=periode2.options[periode2.selectedIndex].value;

    param = 'proses=loadData&page='+num;
    param+='&ptCr='+ptCari+'&periode='+periode+'&periode2='+periode2;
    tujuan = 'pabrik_slave_penerimaankomoditi.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else {
                    isdt = con.responseText.split("####");
                    document.getElementById('containerlist').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                    if(modetgl==1){
                        getTanggal2();    
                    }
                    
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function deletehead(pt,tgl,komoditi){
    pag=document.getElementById('pages');
    pag=pag.options[pag.selectedIndex].value;
    param='kodeorg='+pt+'&tgl='+tgl+'&komoditi='+komoditi+'&proses=deleteDt';
    tujuan = 'pabrik_slave_penerimaankomoditi.php';
    if(confirm(notif)){
        post_response_text(tujuan, param, respog);
        function respog()
        {
            if (con.readyState == 4)
            {
                if (con.status == 200)
                {
                    busy_off();
                    if (!isSaveResponse(con.responseText))
                    {
                        alert(con.responseText);
                    }
                    else {
                        pag=parseInt(pag)-1;
                        loadData(pag,0);
                    }
                }
                else
                {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
    }
}
function getTanggal2(){
    unitId=document.getElementById('unitId');
    unitId=unitId.options[unitId.selectedIndex].value;
    komoditi=document.getElementById('komoditi');
    komoditi=komoditi.options[komoditi.selectedIndex].value;
     
    param='unitId='+unitId+'&komoditi='+komoditi+'&proses=getTgl2';
    param+='&tgl=""';
     
    tujuan = 'pabrik_slave_penerimaankomoditi.php';
        post_response_text(tujuan, param, respog);
        function respog()
        {
            if (con.readyState == 4)
            {
                if (con.status == 200)
                {
                    busy_off();
                    if (!isSaveResponse(con.responseText))
                    {
                        alert(con.responseText);
                    }
                    else {
                            document.getElementById('tgl').innerHTML=con.responseText;    
                    }
                }
                else
                {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
    
}
function getTanggal(){
    unitId=document.getElementById('unitId');
    unitId=unitId.options[unitId.selectedIndex].value;
    komoditi=document.getElementById('komoditi');
    komoditi=komoditi.options[komoditi.selectedIndex].value;
    tgl=document.getElementById('tgl');
    tgl=tgl.options[tgl.selectedIndex].value;
    param='unitId='+unitId+'&komoditi='+komoditi+'&proses=getTgl';
    if(tgl!=''){
        param+='&tgl='+tgl;
    }
    tujuan = 'pabrik_slave_penerimaankomoditi.php';
        post_response_text(tujuan, param, respog);
        function respog()
        {
            if (con.readyState == 4)
            {
                if (con.status == 200)
                {
                    busy_off();
                    if (!isSaveResponse(con.responseText))
                    {
                        alert(con.responseText);
                    }
                    else {
                        if(tgl!=''){
                            dt=con.responseText.split("####");
                            document.getElementById('kgKirim').value=dt[1];
                            document.getElementById('kgTrima').value=dt[0];
                        }else{
                            document.getElementById('tgl').innerHTML=con.responseText;    
                        }
                        
                        
                    }
                }
                else
                {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
    
}
function formListPP(title,wdth,heig){
        //closeDialog();
        width='';
        height='';
        if(wdth!=''){
            width=wdth;
        }
        if(heig!=''){
            height=heig;
        }
        
        content="<div id=containerdata></div>";
        ev='event';
        showDialog4(title,content,width,height,ev);
}
function detaildt(title,kdorg,tipepot,periode){
    var width=580;
    var height=380;
    formListPP(title,width,height);
    param='proses=detailDt'+'&kdOrg='+kdorg+'&tipepotongan='+tipepot+'&periodegaji='+periode;
    tujuan = 'pabrik_slave_penerimaankomoditi.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('containerdata').innerHTML=con.responseText;
                }
            }
            else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='';
   height='';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev);  
}
function excel(ev,kodeorg,periodegaji,tipepotongan){
        param='proses=excel'+'&kodeorg='+kodeorg+'&periodegaji='+periodegaji+'&tipepotongan='+tipepotongan;
        //alert(param);
        tujuan='pabrik_slave_penerimaankomoditi.php';
        judul='Print Excel';        
        printFile(param,tujuan,judul,ev)    
}
function saveData(){
    unitId=document.getElementById('unitId');
    unitId=unitId.options[unitId.selectedIndex].value;
    komoditi=document.getElementById('komoditi');
    komoditi=komoditi.options[komoditi.selectedIndex].value;
    tgl=document.getElementById('tgl');
    tgl=tgl.options[tgl.selectedIndex].value;
    totTrm=document.getElementById('kgTrima').value;
    kgKirim=document.getElementById('kgKirim').value;
    param ='proses=saveData'+'&pt='+unitId+'&komoditi='+komoditi+'&tgl='+tgl;
    param+='&totKrm='+totTrm+'&kgKirim='+kgKirim;
    tujuan = 'pabrik_slave_penerimaankomoditi.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else {
                    alert("Data Sudah Tersimpan");
                    document.getElementById('kgTrima').value=0;
                    document.getElementById('kgKirim').value=0;
                    tgldt=1;
                    loadData(0,tgldt);
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}