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
        document.getElementById('jenis').disabled=true;
        document.getElementById('tgl').disabled=true;
        document.getElementById('tombolHeader').style.display="none";
}
function unlockForm(){
        document.getElementById('tgl').disabled=false;
        document.getElementById('tombolHeader').style.display="block";
        clearForm();
}

function loadData(num){
    tgl=document.getElementById('tglCr').value;
    tgl2=document.getElementById('tglCr2').value;
    
    param ='proses=loadNewData&page=' + num;
    param+='&tgl='+tgl+'&tgl2='+tgl2;
    tujuan = 'pabrik_slave_mr_metp.php';
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
    tujuan = 'pabrik_slave_mr_metp.php';
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
    for(itung=0;itung<totDt;itung++){
        var nil=document.getElementById('nil_'+itung).value;
        var paramDt=document.getElementById('param_'+itung).value;
        datNil+="&nilai["+itung+"]="+nil;
        datNil+="&paramDt["+itung+"]="+paramDt;
    }
    tujuan = 'pabrik_slave_mr_metp.php';
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
                else {
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
function deletehead(notrans){
    param='tanggal='+notrans+'&proses=deletehead';
    tujuan='pabrik_slave_mr_metp.php';
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
                            //document.getElementById('tmbLheader').innerHTML='';
                            loadData(0);
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function detaildt(title,notransaksi){
    title=title+" "+notransaksi;
    width='450px';
    height='650px';
    formListPP(title,width,height);
        param='tgl='+notransaksi+'&proses=htmlDetail';
        tujuan='pabrik_slave_mr_metp.php';
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
function upDt(){
    tanggal=document.getElementById('tgl2').value;
    totDt=document.getElementById('totRow').value;
    var datNil='';
    for(itung=0;itung<totDt;itung++){
        var nil=document.getElementById('nil_'+itung).value;
        var paramDt=document.getElementById('param_'+itung).value;
        datNil+="&nilai["+itung+"]="+nil;
        datNil+="&paramDt["+itung+"]="+paramDt;
    }
    tujuan ='pabrik_slave_mr_metp.php';
    param='proses=update'+'&tanggal='+tanggal+'&totRow='+totDt;
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