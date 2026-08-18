// JavaScript Document
function clearForm(){
    document.getElementById('jenis').value='';
    document.getElementById('tgl').value='';
    document.getElementById('station').value='';
    document.getElementById('dataIsian').innerHTML='';
    //form cari
    //document.getElementById('jnsCr').value='';
}
function displayList(){
    document.getElementById('listData').style.display='block';
    document.getElementById('headher').style.display='none';
    document.getElementById('tglCr').value='';
    document.getElementById('tglCr2').value='';
    alertify.popup().destroy();
    loadData(0);
}
function lockForm(){
    document.getElementById('jenis').disabled=true;
    document.getElementById('tgl').disabled=true;
    document.getElementById('station').disabled=true;
    document.getElementById('tombolHeader').style.display="none";
}
function unlockForm(){
    document.getElementById('jenis').disabled=false;
    document.getElementById('tgl').disabled=false;
    document.getElementById('station').disabled=false;
    document.getElementById('tombolHeader').style.display="block";
    clearForm();
}

function loadData(num){
    // pilJns=document.getElementById('jnsCr');
    // pilJns=pilJns.options[pilJns.selectedIndex].value;
    tgl=document.getElementById('tglCr').value;
    tgl2=document.getElementById('tglCr2').value;
    
    param ='proses=loadNewData&page=' + num;
    param+='&tgl='+tgl+'&tgl2='+tgl2;
    tujuan = 'pabrik_slave_mr_roa.php';
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
                    document.getElementById('contain').innerHTML = con.responseText;
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
    jns=document.getElementById('jenis');
    jns=jns.options[jns.selectedIndex].value;
    tujuan = 'pabrik_slave_mr_roa.php';
    param='proses=getTable'+'&jenis='+jns;
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
    station=document.getElementById('station').value;
    jns=document.getElementById('jenis');
    jns=jns.options[jns.selectedIndex].value;
    totDt=document.getElementById('totRow').value;
    var datNil='';
    for(itung=0;itung<totDt;itung++){
        var nil=document.getElementById('nil_'+itung).value;
        var paramDt=document.getElementById('param_'+itung).value;
        datNil+="&nilai["+itung+"]="+nil;
        datNil+="&paramDt["+itung+"]="+paramDt;
    }
    tujuan = 'pabrik_slave_mr_roa.php';
    param='proses=saveDt'+'&jenis='+jns+'&tanggal='+tanggal+'&totRow='+totDt+'&station='+station;
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
                   //displayList();
                   document.getElementById('dataIsian').innerHTML='';
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
function upDt(){
    tanggal=document.getElementById('tgl2').value;
    station=document.getElementById('station2').value;
    totDt=document.getElementById('totRow2').value;
    var datNil='';
    for(itung=0;itung<totDt;itung++){
        var nil=document.getElementById('nil_'+itung).value;
        var paramDt=document.getElementById('param_'+itung).value;
        datNil+="&nilai["+itung+"]="+nil;
        datNil+="&paramDt["+itung+"]="+paramDt;
    }
    tujuan = 'pabrik_slave_mr_roa.php';
    param='proses=update'+'&tanggal='+tanggal+'&station='+station+'&totRow='+totDt;
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
                    alertify.popup().destroy();
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
function deletehead(tgl,station){
    param='tanggal='+tgl+'&station='+station+'&proses=deletehead';
    tujuan='pabrik_slave_mr_roa.php';
    if(confirm("Anda Yakin ingin Menghapus?")){
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
function detaildt(title,tgl,station){
    title=title+" "+tgl;
    width='450px';
    height='650px';
    // formListPP(title,width,height);
        param='tgl='+tgl+'&station='+station+'&proses=htmlDetail';
        tujuan='pabrik_slave_mr_roa.php';
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
                        //    document.getElementById('containerData').innerHTML=con.responseText;
					    alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','');
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