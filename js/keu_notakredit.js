function gettgl(tipe){
    tipe=trim(document.getElementById('tipe').value);
    var tgl = new Date();
    var tahun=tgl.getFullYear();
    var tahun=tahun-1;
    if (tipe==1) {
        document.getElementById('tanggal').value='31-12-'+tahun;
        document.getElementById('tanggal').disabled=true;
        document.getElementById('revisi').disabled=false;
        document.getElementById('revisix').style.display='';
    }else{
        document.getElementById('tanggal').value='';
        document.getElementById('tanggal').disabled=false;
        document.getElementById('revisi').value=0;
        document.getElementById('revisi').disabled=true;
        document.getElementById('revisix').style.display='none';
    }
}

function getunit(unit)
{
    kodeorg=trim(document.getElementById('kodeorg').value);
    param = 'kodeorg='+kodeorg+'&unit='+unit+'&proses=getunit';
    post_response_text('keu_slave_notakredit.php', param, respon);
    
    function respon() 
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
                    // === Success Response
                    document.getElementById('unit').innerHTML = con.responseText;
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

function searchnodo(title,content,ev)
{
    kodeorg=trim(document.getElementById('kodeorg').value);
    unit=trim(document.getElementById('unit').value);

    if (kodeorg=='') {
        alert('PT tidak boleh kosong.');
        return;
    }
    if (unit=='') {
        alert('Unit tidak boleh kosong.');
        return;
    }

    width='auto';
    height='auto';
    showDialog1(title,content,width,height,ev);
    getformnodo();
}

function getformnodo(){
    unit=trim(document.getElementById('unit').value);
    jenis=trim(document.getElementById('jenis').value);
    param='proses=getformnodo'+'&jenis='+jenis+'&unit='+unit;
    tujuan='keu_slave_notakredit.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('formPencariandata').innerHTML=con.responseText;
                    findnodo();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function findnodo(){
    unit=trim(document.getElementById('unit').value);
    jenis=trim(document.getElementById('jenis').value);
    fnodo=trim(document.getElementById('fnodo').value);
    param='proses=getdatanodo'+'&unit='+unit+'&jenis='+jenis;
    param+='&nodo='+fnodo;
    
    tujuan='keu_slave_notakredit.php';
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
                    document.getElementById('container2').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setdata(nodo,nokontrak,jenis,customer,matauang,kurs) {
    document.getElementById('noinvoice').value=nodo;
    document.getElementById('nokontrak').value=nokontrak;
    document.getElementById('jenis').value=jenis;
    document.getElementById('customer').value=customer;
    document.getElementById('matauang').value=matauang;
    document.getElementById('kurs').value=kurs;
    closeDialog();
}

function saveData(){
    notakredit=trim(document.getElementById('notakredit').value);
    nokontrak=trim(document.getElementById('nokontrak').value);
    tipe=trim(document.getElementById('tipe').value);
    tanggal=trim(document.getElementById('tanggal').value);
    revisi=trim(document.getElementById('revisi').value);
    kodeorg=trim(document.getElementById('kodeorg').value);
    unit=trim(document.getElementById('unit').value);
    jenis=trim(document.getElementById('jenis').value);
    noinvoice=trim(document.getElementById('noinvoice').value);
    customer=trim(document.getElementById('customer').value);
    nilaiinvoice=trim(document.getElementById('nilaiinvoice').value);
    keterangan=trim(document.getElementById('keterangan').value);
    noakun=trim(document.getElementById('noakun').value);
    matauang=trim(document.getElementById('matauang').value);
    kurs=trim(document.getElementById('kurs').value);
    proses=trim(document.getElementById('proses').value);
    
    param='kodeorg='+kodeorg+'&notakredit='+notakredit+'&nokontrak='+nokontrak+'&tipe='+tipe+'&proses='+proses+'&tanggal='+tanggal+'&unit='+unit;
    param+='&revisi='+revisi+'&jenis='+jenis+'&noinvoice='+noinvoice;
    param+='&customer='+customer+'&nilaiinvoice='+nilaiinvoice+'&keterangan='+keterangan;
    param+='&matauang='+matauang+'&kurs='+kurs+'&noakun='+noakun;
    tujua='keu_slave_notakredit.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {          
                    document.getElementById('notakredit').value=con.responseText;
                    showdetail(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showdetail()
{
    unit=trim(document.getElementById('unit').value);
    jenis=trim(document.getElementById('jenis').value);
    notakredit=trim(document.getElementById('notakredit').value);
    param = 'unit='+unit+'&proses=showdetail'+'&jenis='+jenis+'&notakredit='+notakredit;
    post_response_text('keu_slave_notakredit.php', param, respon);
    
    function respon() 
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
                    // === Success Response
                    document.getElementById('formdetail').style.display = 'block';
                    document.getElementById('formdetail').innerHTML = con.responseText;
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

function deldt(notakredit)
{
    param='proses=deldt'+'&notakredit='+notakredit;
    tujuan='keu_slave_notakredit.php';
    if(confirm(' Anda yakin ingin menghapus data ini?'))
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
                }else{
                   displaylist();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function editdt(notakredit,nokontrak,tipe,tanggal,revisi,kodeorg,unit,jenis,noinvoice_referensi,kodecustomer,nilaiinvoice,keterangan,noakun,matauang,kurs){
    document.getElementById('kodeorg').value=kodeorg;
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
    document.getElementById('notakredit').value=notakredit;
    document.getElementById('nokontrak').value=nokontrak;
    document.getElementById('noakun').value=noakun;
    document.getElementById('tipe').value=tipe;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('revisi').value=revisi;
    document.getElementById('keterangan').value=keterangan;
    document.getElementById('jenis').value=jenis;
    document.getElementById('jenis').disabled=true;
    document.getElementById('noinvoice').value=noinvoice_referensi;
    document.getElementById('noinvoice').disabled=true;
    document.getElementById('customer').value=kodecustomer;
    document.getElementById('customer').disabled=true;
    document.getElementById('matauang').value=matauang;
    document.getElementById('matauang').disabled=true;
    document.getElementById('kurs').value=kurs;
    document.getElementById('kurs').disabled=true;
    document.getElementById('nilaiinvoice').value=nilaiinvoice;
    document.getElementById('proses').value='update';
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    showdetail(notakredit);
}

function clearData(){
    document.getElementById('kodeorg').value='';
    document.getElementById('kodeorg').disabled=false;
    document.getElementById('noinvoice').disabled=false;
    document.getElementById('tanggal').disabled=false;
    // document.getElementById('kurs').disabled=false;
    // document.getElementById('jenis').disabled=false;
    document.getElementById('unit').value='';
    document.getElementById('unit').disabled=false;
    document.getElementById('notakredit').value='';
    document.getElementById('nokontrak').value='';
    document.getElementById('tipe').value=0;
    document.getElementById('tanggal').value='';
    document.getElementById('revisi').value=0;
    document.getElementById('jenis').value='';
    document.getElementById('noinvoice').value='';
    document.getElementById('customer').value='';
    document.getElementById('nilaiinvoice').value=0;
    document.getElementById('keterangan').value='';
    document.getElementById('proses').value='insert';
}

function displayFormInput(){
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    document.getElementById('formdetail').innerHTML='';
    document.getElementById('formdetail').style.display='none';
    clearData();
}

function displaylist(){
    document.getElementById('crnotakredit').value='';
    document.getElementById('listData').style.display='block';
    document.getElementById('formInput').style.display='none';
    document.getElementById('formdetail').innerHTML='';
    document.getElementById('formdetail').style.display='none';
    clearData();
    loadData(0);
}

function loadData(num){
    crnotakredit=document.getElementById('crnotakredit').value;

    param='proses=loadData';
    param+='&page='+num;

    if (crnotakredit != '') {
        param += '&notakredit=' + crnotakredit;
    }

    tujuan='keu_slave_notakredit.php';
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
    pg=pg.okodeorgions[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);  
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

function viewdetail(notakredit)
{
    form();
    param = 'proses=viewdetail' + '&notakredit=' + notakredit;
    tujuan = 'keu_slave_notakredit.php';
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

function posting(notakredit)
{
    param='proses=posting'+'&notakredit='+notakredit;
    tujuan='keu_slave_notakredit.php';
    if(confirm('Anda yakin ingin memposting ini ??'))
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
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function saveDetail(){
    notakredit=trim(document.getElementById('notakredit').value);
    noakundt=trim(document.getElementById('noakundt').value);
    noakundtold=trim(document.getElementById('noakundtold').value);
    nilai=trim(document.getElementById('nilai').value);
    keterangan=trim(document.getElementById('keterangandt').value);
    proses=trim(document.getElementById('prosesdt').value);

    if (noakundt=='' || nilai=='') {
        alert('noakun dan nilai tidak boleh kosong.');
        return;
    }
    
    param='notakredit='+notakredit+'&proses='+proses+'&noakun='+noakundt+'&noakundtold='+noakundtold;
    param+='&nilai='+nilai+'&keterangan='+keterangan;

    tujuan='keu_slave_notakredit.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {          
                    cleardetail();
                    showdetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cleardetail(){ ;
    document.getElementById('noakundt').value='';
    document.getElementById('noakundtold').value='';
    document.getElementById('noakundt').disabled=false;
    document.getElementById('nilai').value='';
    document.getElementById('keterangandt').value='';
    document.getElementById('prosesdt').value='insertdt';
}

function editdetail(noakun,nilai,jenis,ket){
    document.getElementById('noakundt').value=noakun;
    document.getElementById('noakundtold').value=noakun;
    document.getElementById('nilai').value=nilai;
    document.getElementById('keterangandt').value=ket;
    document.getElementById('prosesdt').value='updatedt';
}



function deldetail(noakun,keterangan)
{
    notakredit=trim(document.getElementById('notakredit').value);
    jenis=trim(document.getElementById('jenis').value);
    param='proses=deldetail'+'&notakredit='+notakredit+'&jenis='+jenis+'&noakun='+noakun+'&keterangan='+keterangan;
    tujuan='keu_slave_notakredit.php';
    if(confirm(' Anda yakin ingin menghapus data ini?'))
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
                }else{
                   showdetail();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}