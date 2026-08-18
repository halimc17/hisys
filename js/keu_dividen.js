function displayFormInput(){
    cancel();
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
}

function displaylist(){
    cancel();
    document.getElementById('notranscr').value='';
    document.getElementById('listData').style.display='block';
    document.getElementById('formInput').style.display='none';
    loadData(0);
}

function getakun(norek){
    unit1=trim(document.getElementById('unit1').value);

    param='unit1='+unit1+'&method=getakun'+'&norek='+norek;
    tujuan='keu_slave_dividen.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('norek').innerHTML=con.responseText;
                    getmatauang();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getmatauang(){
    norek=trim(document.getElementById('norek').value);

    param='method=getmatauang'+'&norek='+norek;
    tujuan='keu_slave_dividen.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('matauang').value=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getunit2(unit2){
    unit1=trim(document.getElementById('unit1').value);
    transaksi=trim(document.getElementById('transaksi').value);

    param='unit1='+unit1+'&method=getunit2'+'&unit2='+unit2+'&transaksi='+transaksi;
    tujuan='keu_slave_dividen.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('unit2').innerHTML=con.responseText;
                    disakun();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function disakun(){

    status=trim(document.getElementById('status').value);
    transaksi=trim(document.getElementById('transaksi').value);

    if (status=='Receiver' && transaksi=='Eksternal') {
        document.getElementById('akunpiutangeks').disabled=false;
    }else{
        document.getElementById('akunpiutangeks').disabled=true;
    }

}

function simpan(){
    notransaksi=document.getElementById('notransaksi').value;
    unit1=trim(document.getElementById('unit1').value);
    tanggal=document.getElementById('tanggal').value;
    norek=document.getElementById('norek').value;
    tipetransaksi=document.getElementById('tipetransaksi').value;
    transaksi=document.getElementById('transaksi').value;
    unit2=trim(document.getElementById('unit2').value);
    nilai=document.getElementById('nilai').value;
    status=document.getElementById('status').value;
    akunpiutangeks=document.getElementById('akunpiutangeks').value;
    method=document.getElementById('method').value;

    if(unit1=='' || tanggal=='' || norek=='' || tipetransaksi=='' || transaksi=='' || unit2=='' || nilai=='' || status=='' )
    {
        alert('Field Was Empty');
        return;
    }

    if (unit1==unit2) {
        alert('Unit 1 dan Unit 2 tidak boleh sama.');
        return;
    }

    if (status=='Receiver' && transaksi=='Eksternal') {
        if ( akunpiutangeks=='' ) {
            alert('Akun piutang eksternal harus terisi.');
            return;
        }
    }

    param='notransaksi='+notransaksi+'&unit1='+unit1+'&unit2='+unit2+'&norek='+norek+'&method='+method;
    param+='&status='+status+'&tanggal='+tanggal+'&nilai='+nilai+'&tipetransaksi='+tipetransaksi+'&transaksi='+transaksi;
    param+='&akunpiutangeks='+akunpiutangeks;
    tujuan='keu_slave_dividen.php';
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
                    cancel();
                    displaylist();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}
                    
function cancel(){

    document.getElementById('notransaksi').value='';
    document.getElementById('unit1').value='';
    document.getElementById('unit1').disabled=false;
    document.getElementById('unit2').value='';
    document.getElementById('matauang').value='';
    document.getElementById('norek').value='';
    document.getElementById('tipetransaksi').value='';
    document.getElementById('tipetransaksi').disabled=false;
    document.getElementById('transaksi').value='';
    document.getElementById('tanggal').value='';
    document.getElementById('nilai').value='';
    document.getElementById('status').value='';
    document.getElementById('akunpiutangeks').value='';
    document.getElementById('akunpiutangeks').disabled=false;
    document.getElementById('method').value='insert';
}

function loadData(num){
    notranscr=document.getElementById('notranscr').value;

    param='method=loadData';
    param+='&page='+num;

    if (notranscr != '') {
        param += '&notransaksi=' + notranscr;
    }

    tujuan='keu_slave_dividen.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    isdt = con.responseText.split("####");
                    document.getElementById('continerlist').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }else{
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

function edit(notransaksi,unit1,norek,unit2,status,transaksi,tipetransaksi,tanggal,nilai,akunpiutangeks)
{
    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('unit1').value=unit1;
    document.getElementById('unit1').disabled=true;
    document.getElementById('norek').value=norek;
    document.getElementById('unit2').value=unit2;
    document.getElementById('status').value=status;
    document.getElementById('transaksi').value=transaksi;
    document.getElementById('tipetransaksi').value=tipetransaksi;
    document.getElementById('tipetransaksi').disabled=true;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('nilai').value=nilai;
    document.getElementById('akunpiutangeks').value=akunpiutangeks;

    if (status=='Receiver' && transaksi=='Eksternal') {
        document.getElementById('akunpiutangeks').disabled=false;
    }else{
        document.getElementById('akunpiutangeks').disabled=true;
    }

    document.getElementById('method').value='update';
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    getakun(norek);
}

function del(notransaksi)
{
    param='method=delete'+'&notransaksi='+notransaksi;
    tujuan='keu_slave_dividen.php';
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
                else 
                {
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

function form()
{
    width = '';
    height = '';
    content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}

function formposting(notransaksi)
{
    form();
    param = 'method=formposting'+'&notransaksi='+notransaksi;
    tujuan = 'keu_slave_dividen.php';
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
                else
                {
                    document.getElementById('containerd').innerHTML = con.responseText;
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

function posting(notransaksi)
{
    tglposting=document.getElementById('tglposting').value;
    param='method=posting'+'&notransaksi='+notransaksi+'&tglposting='+tglposting;
    tujuan='keu_slave_dividen.php';
    if(confirm('Anda yakin ingin memposting transaksi ini ??'))
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
                    displaylist();
                    closeDialog();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function viewdetail(notransaksi,tipetransaksikasbank)
{
    form();
    param = 'method=viewdetail'+'&notransaksi='+notransaksi+'&tipetransaksikasbank='+tipetransaksikasbank;
    tujuan = 'keu_slave_dividen.php';
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
                else
                {
                    document.getElementById('containerd').innerHTML = con.responseText;
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



