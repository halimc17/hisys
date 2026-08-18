function posting(kodeorg,periodegaji){
	param='kodeorg='+kodeorg+'&periodegaji='+periodegaji+'&proses=posting';
    tujuan = 'sdm_slave_3gajikecil.php';
    if(confirm("Anda Yakin Posting Data")){
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
                   loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }   
}

function tampilkanCatu(){
    unitId=document.getElementById('unitId');
    unitId=unitId.options[unitId.selectedIndex].value;
    periode=document.getElementById('periode');
    periode=periode.options[periode.selectedIndex].value;
    tpKary=document.getElementById('tpKary');
    tpKary=tpKary.options[tpKary.selectedIndex].value;
    param='unitId='+unitId+'&periode='+periode+'&tpKary='+tpKary+'&proses=preview';
    tujuan='sdm_slave_3gajikecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
      if(con.readyState==4){
            if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else {                     
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
function cekSma(){
    var rowDt=document.getElementById('rowIsiData').value;
    var ckRw=document.getElementById('dtKbwAll');
    for(i=1;i<=parseInt(rowDt);i++){
        var totData=document.getElementById('rowIsiData_'+i).value;
        for(a=1;a<=totData;a++){
            if(ckRw.checked==true){
                document.getElementById('dtKbw_'+i+'_'+a).checked=true;
            }else{
                document.getElementById('dtKbw_'+i+'_'+a).checked=false;
            }
        }
    }
}
function saveAll(rowdt){
    instData(1,rowdt);   
}
function instData(cur,maxRow){
    var isiDt='';
    var tempDt='';
    prd=document.getElementById('periode');
    prd=prd.options[prd.selectedIndex].value;
    tpKary=document.getElementById('tpKary');
    tpKary=tpKary.options[tpKary.selectedIndex].value;
    unitId=document.getElementById('unitId');
    unitId=unitId.options[unitId.selectedIndex].value;
    param = 'proses=saveAll'+'&periode='+prd+'&tpKary='+tpKary+'&unitId='+unitId;
    var totData=document.getElementById('rowIsiData_'+cur).value;
    for(a=1;a<=totData;a++){
        ckdt=document.getElementById('dtKbw_'+cur+'_'+a);
        kdOrg=document.getElementById('kdorg_'+cur+'_'+a).innerHTML;
        sbBag=document.getElementById('sb_'+cur+'_'+a).innerHTML;
        krId=document.getElementById('karyId_'+cur+'_'+a).value;
        rpGj=document.getElementById('rpDt_'+cur+'_'+a).value;
        hkDt=document.getElementById('hkDt_'+cur+'_'+a).value;
        
        indexDt=sbBag;
        if(rpGj==0){
            rpGj=0;
        }
        if(ckdt.checked==true){
            if(tempDt!=indexDt){
                tempDt=indexDt;
                isiDt="&dtKirim[]="+indexDt;
                isiDt+="&dtKary[]="+krId;
                isiDt+="&dtRup[]="+rpGj;
                isiDt+="&dthkDt[]="+hkDt;
                var rwbaris=0;
            }else{
                isiDt+="&dtKary[]="+krId;
                isiDt+="&dtRup[]="+rpGj;
                isiDt+="&dthkDt[]="+hkDt;
                rwbaris+=1;
            }
        }
        isiDt+="&rowDt["+indexDt+"]="+rwbaris;
    }
    param+=isiDt;
    tujuan = 'sdm_slave_3gajikecil.php';
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
                    if(cur==maxRow){
                        alert("Data Sudah Tersimpan");
                        document.getElementById('container').innerHTML=con.responseText;
                        loaddata(0);    
                    }else{
                         cur+=1;
                         instData(cur,maxRow);   
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


function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);    
}

function loaddata(num){
    param = 'proses=loaddata&page='+num;
    tujuan = 'sdm_slave_3gajikecil.php';
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
                    closeDialog4();
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
function deletehead(kdorg,tipepot,periode){
    param='kodeorg='+kdorg+'&periodegaji='+periode+'&tipepotongan='+tipepot+'&proses=deleteDt';
    tujuan = 'sdm_slave_3gajikecil.php';
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
                        loaddata(0);
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
    tujuan = 'sdm_slave_3gajikecil.php';
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
function excel(ev,kodeorg,periodegaji,tipepotongan,mode){
        param='proses=excel'+'&kodeorg='+kodeorg+'&periodegaji='+periodegaji+'&tipepotongan='+tipepotongan;
        param+='&mode='+mode;
        //alert(param);
        tujuan='sdm_slave_3gajikecil.php';
        judul='Print Excel';        
        printFile(param,tujuan,judul,ev)    
}
