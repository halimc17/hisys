// JavaScript Document
function clearForm(){
    document.getElementById('tgl').value='';
    document.getElementById('dataIsian').innerHTML='';
    //form cari
//    document.getElementById('jnsCr').value='';
    document.getElementById('tglCr').value='';
    document.getElementById('tglCr2').value='';
}
function displayList(){
        document.getElementById('listData').style.display='block';
        document.getElementById('headher').style.display='none';
        clearForm();
        loadData(0);
        closeDialog4();
}
function lockForm(){
        document.getElementById('tgl').disabled=true;
}
function unlockForm(){
        document.getElementById('tgl').disabled=false;
        clearForm();
}
function loadData(num){
    tgl=document.getElementById('tglCr').value;
    tgl2=document.getElementById('tglCr2').value;
    param ='proses=loadNewData&page=' + num;
    param+='&tgl='+tgl+'&tgl2='+tgl2;
    tujuan = 'pabrik_slave_logmesin.php';
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
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
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
    loadData(paged);    
}

function getTable(){
    tgl=document.getElementById('tgl').value;
    tujuan = 'pabrik_slave_logmesin.php';
    param='proses=getTable'+'&tgl='+tgl;
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
                    document.getElementById('dataIsian').innerHTML = con.responseText;
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
function saveDt(){
    tanggal=document.getElementById('tgl').value;
    totDt=document.getElementById('totRow').value;
    var datNil='';
    var stationId='';
    for(itung=0;itung<totDt;itung++){
        stationId=document.getElementById('station_'+itung).value;
        //HEATING UP
        jmStrtHU=document.getElementById('HU_JAMSTRT_'+itung);
        jmStrtHU=jmStrtHU.options[jmStrtHU.selectedIndex].value;
        mntStrtHU=document.getElementById('HU_MNTSTRT_'+itung);
        mntStrtHU=mntStrtHU.options[mntStrtHU.selectedIndex].value;
        jmStpHU=document.getElementById('HU_JAMSTP_'+itung);
        jmStpHU=jmStpHU.options[jmStpHU.selectedIndex].value;
        mntStpHU=document.getElementById('HU_MNTSTP_'+itung);
        mntStpHU=mntStpHU.options[mntStpHU.selectedIndex].value;
        totalJamHu=getselisih(jmStpHU,jmStrtHU,mntStpHU,mntStrtHU);

        //PROSES
        jmStrtPR=document.getElementById('PR_JAMSTRT_'+itung);
        jmStrtPR=jmStrtPR.options[jmStrtPR.selectedIndex].value;
        mntStrtPR=document.getElementById('PR_MNTSTRT_'+itung);
        mntStrtPR=mntStrtPR.options[mntStrtPR.selectedIndex].value;
        jmStpPR=document.getElementById('PR_JAMSTP_'+itung);
        jmStpPR=jmStpPR.options[jmStpPR.selectedIndex].value;
        mntStpPR=document.getElementById('PR_MNTSTP_'+itung);
        mntStpPR=mntStpPR.options[mntStpPR.selectedIndex].value;
        totalJamPR=getselisih(jmStpPR,jmStrtPR,mntStpPR,mntStrtPR);

        //COOLING DOWN
        jmStrtCN=document.getElementById('CN_JAMSTRT_'+itung);
        jmStrtCN=jmStrtCN.options[jmStrtCN.selectedIndex].value;
        mntStrtCN=document.getElementById('CN_MNTSTRT_'+itung);
        mntStrtCN=mntStrtCN.options[mntStrtCN.selectedIndex].value;
        jmStpCN=document.getElementById('CN_JAMSTP_'+itung);
        jmStpCN=jmStpCN.options[jmStpCN.selectedIndex].value;
        mntStpCN=document.getElementById('CN_MNTSTP_'+itung);
        mntStpCN=mntStpCN.options[mntStpCN.selectedIndex].value;
        totalJamCN=getselisih(jmStpCN,jmStrtCN,mntStpCN,mntStrtCN);

        //BREAKDOWN
        jmStrtBN=document.getElementById('BN_JAMSTRT_'+itung);
        jmStrtBN=jmStrtBN.options[jmStrtBN.selectedIndex].value;
        mntStrtBN=document.getElementById('BN_MNTSTRT_'+itung);
        mntStrtBN=mntStrtBN.options[mntStrtBN.selectedIndex].value;
        jmStpBN=document.getElementById('BN_JAMSTP_'+itung);
        jmStpBN=jmStpBN.options[jmStpBN.selectedIndex].value;
        mntStpBN=document.getElementById('BN_MNTSTP_'+itung);
        mntStpBN=mntStpBN.options[mntStpBN.selectedIndex].value;
        totalJamBN=getselisih(jmStpBN,jmStrtBN,mntStpBN,mntStrtBN);
        
        datNil+="&stationId["+itung+"]="+stationId;
        //HEATING UP
        datNil+="&jamStrtHU["+itung+"]="+jmStrtHU+":"+mntStrtHU;
        datNil+="&jamStpHU["+itung+"]="+jmStpHU+":"+mntStpHU;
        datNil+="&totalJamHU["+itung+"]="+totalJamHu;
        datNil+="&klafikasiHU["+itung+""+stationId+"]=HU";
        //PROSES
        datNil+="&jamStrtPR["+itung+"]="+jmStrtPR+":"+mntStrtPR;
        datNil+="&jamStpPR["+itung+"]="+jmStpPR+":"+mntStpPR;
        datNil+="&totalJamPR["+itung+"]="+totalJamPR;
        datNil+="&klafikasiPR["+itung+""+stationId+"]=PR";
        //COOLING DOWN
        datNil+="&jamStrtCN["+itung+"]="+jmStrtCN+":"+mntStrtCN;
        datNil+="&jamStpCN["+itung+"]="+jmStpCN+":"+mntStpCN;
        datNil+="&totalJamCN["+itung+"]="+totalJamCN;
        datNil+="&klafikasiCN["+itung+""+stationId+"]=CN";
        //BREAKDOWN
        datNil+="&jamStrtBN["+itung+"]="+jmStrtBN+":"+mntStrtBN;
        datNil+="&jamStpBN["+itung+"]="+jmStpBN+":"+mntStpBN;
        datNil+="&totalJamBN["+itung+"]="+totalJamBN;
        datNil+="&klafikasiBN["+itung+""+stationId+"]=BN";
    }
    tujuan = 'pabrik_slave_logmesin.php';
    param='proses=saveDt'+'&tanggal='+tanggal+'&totRow='+totDt;
    param+=datNil;
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else{
                   displayList();
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
function getselisih(jmstp,jmstrt,mntstp,mntstrt){
    a=jmstp;//document.getElementById('jmstop').value;
    b=jmstrt;//document.getElementById('jmstart').value;
    c=mntstp;//document.getElementById('mnstop').value;
    d=mntstrt;//document.getElementById('mnstart').value;
    var y=0;
    var x=0;
    if(a!=b){   
        if(c!=d){   
            if(a>b){
                if(c>d){//a>b c>d
                    x=a-b;
                    y=c-d;
                } else { //a>b c<d
                    x=a-b-1;
                    y=(c-d)+60;
                }
            } else {
                if(c>d){
                    x=(a-b)+24;
                    y=c-d;  
                } else {
                    x=(a-b)+23;
                    y=(c-d)+60;
                }
            }
        } else { //c==d
            //y=0;
            if(a>b){
                x=a-b;
                y=0;
            } else { //a<b
                x=(a-b)+24;
                y=0;
            }
        }
    } else { //a==b
        if(c!=d) {  
            if(c>d) {//a>b c>d
                x=0;
                y=c-d;
            } else { //a>b d>c
                x=0;
                y=(c-d)+60;
            }       
        } else {  //c==d
        
            //alert('waktu mulai dan selsai masih sama harap periksa kembali !!');return;
            //document.getElementById('jam').value=0;
        }
    }
    
    //convert menit ke decimal /100
    //contoh 7.30 harusnya 7.50 -> 30/60=0.5
    //z=x+"."+y;
    m=parseFloat(y)/60;
    z=parseFloat(x)+parseFloat(m);
    //z=x+"."+m;
    return z;
}
function deletehead(notrans,nourut){
    param='tanggal='+notrans+'&nourut='+nourut+'&proses=deletehead';
    tujuan='pabrik_slave_logmesin.php';
    if(confirm("Anda Yakin Menghapus?")){
        post_response_text(tujuan, param, respog);    
    }
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            getPage();
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function detaildt(title,notransaksi,klasifikasi,nourut){
    title=title+" "+notransaksi;
    width='650px';
    height='650px';
    formListPP(title,width,height);
        param='tgl='+notransaksi+'&proses=htmlDetail'+'&klasifikasi='+klasifikasi+'&nourut='+nourut;
        // alert(param);
        // return;
        tujuan='pabrik_slave_logmesin.php';
        post_response_text(tujuan, param, respog);
        function respog(){
              if(con.readyState==4)
              {
                if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert(con.responseText);
                                }
                                else {
                                       document.getElementById('containerData').innerHTML=con.responseText;
                                }
                        }
                        else {
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
        
        content="<div id=containerData></div>";
        ev='event';
        showDialog4(title,content,width,height,ev);
}
function upDt(klsafikasi){
    tanggal=document.getElementById('tgl2').value;
    totDt=document.getElementById('totRow').value;
    norut=document.getElementById('nourut').value;
    var datNil='';
    for(itung=0;itung<totDt;itung++){
        stationId=document.getElementById('station_'+itung).value;
        //HEATING UP
        jmStrtHU=document.getElementById(klsafikasi+'_JAMSTRT_'+itung);
        jmStrtHU=jmStrtHU.options[jmStrtHU.selectedIndex].value;
        mntStrtHU=document.getElementById(klsafikasi+'_MNTSTRT_'+itung);
        mntStrtHU=mntStrtHU.options[mntStrtHU.selectedIndex].value;
        jmStpHU=document.getElementById(klsafikasi+'_JAMSTP_'+itung);
        jmStpHU=jmStpHU.options[jmStpHU.selectedIndex].value;
        mntStpHU=document.getElementById(klsafikasi+'_MNTSTP_'+itung);
        mntStpHU=mntStpHU.options[mntStpHU.selectedIndex].value;
        totalJamHu=getselisih(jmStpHU,jmStrtHU,mntStpHU,mntStrtHU);
        datNil+="&stationId["+itung+"]="+stationId;
        //HEATING UP
        datNil+="&jamStrtHU["+itung+"]="+jmStrtHU+":"+mntStrtHU;
        datNil+="&jamStpHU["+itung+"]="+jmStpHU+":"+mntStpHU;
        datNil+="&totalJamHU["+itung+"]="+totalJamHu;
    }
    tujuan ='pabrik_slave_logmesin.php';
    param='proses=update'+'&tanggal='+tanggal+'&totRow='+totDt+'&klasifikasi='+klsafikasi+'&nourut='+norut;
    param+=datNil;
    post_response_text(tujuan, param, respog);
    function respog(){
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
                    closeDialog4();
                    getPage();
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