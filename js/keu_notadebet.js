


function getnilai(){
	kodevhc=document.getElementById('kodevhc').value;
	kmhm=document.getElementById('kmhm').value;
	tanggal=document.getElementById('tanggal').value;
	unit=document.getElementById('unit').value;
    param = 'kodevhc='+kodevhc+'&kmhm='+kmhm+'&unit='+unit+'&tanggal='+tanggal+'&proses=getnilai';
    post_response_text('keu_slave_notadebet.php', param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('nilai').value = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function gettipesupplier(tipesupplier,noakun,notadebet){
	supplier=document.getElementById('supplier').value;
	notadebet=document.getElementById('notadebet').value;
    param = 'supplier='+supplier+'&tipesupplier='+tipesupplier+'&proses=gettipesupplier';
    post_response_text('keu_slave_notadebet.php', param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('tipesupplier').innerHTML = con.responseText;
					getakunht(tipesupplier,noakun,notadebet);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function getakunht(tipesupplier,noakun,notadebet){
	tipesupplier=document.getElementById('tipesupplier').value;
	notadebet=document.getElementById('notadebet').value;
    param = 'tipesupplier='+tipesupplier+'&noakun='+noakun+'&proses=getakunht';
    post_response_text('keu_slave_notadebet.php', param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('noakun').innerHTML = con.responseText;
					if(notadebet!=''){
						showdetail(notadebet);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}










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
    post_response_text('keu_slave_notadebet.php', param, respon);
    
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
    tipeinvoice=trim(document.getElementById('tipeinvoice').value);
    param='proses=getformnodo'+'&tipeinvoice='+tipeinvoice+'&unit='+unit;
    tujuan='keu_slave_notadebet.php';
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
    tipeinvoice=trim(document.getElementById('tipeinvoice').value);
    fnodo=trim(document.getElementById('fnodo').value);
    param='proses=getdatanodo'+'&unit='+unit+'&tipeinvoice='+tipeinvoice;
    param+='&nodo='+fnodo;
    
    tujuan='keu_slave_notadebet.php';
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

function setdata(nodo,tipeinvoice,supplier,noakun,matauang,kurs,tipesupplier) {
    document.getElementById('notahutang').value=nodo;
    document.getElementById('tipeinvoice').value=tipeinvoice;
    document.getElementById('supplier').value=supplier;
	document.getElementById('tipesupplier').value=tipesupplier;
    document.getElementById('noakun').value=noakun;
    document.getElementById('matauang').value=matauang;
    document.getElementById('kurs').value=kurs;
	gettipesupplier(tipesupplier,noakun);
	
	document.getElementById('supplier').value=supplier;
	
	
    closeDialog();
}

function saveData(){
    tipesupplier=trim(document.getElementById('tipesupplier').value);
    notadebet=trim(document.getElementById('notadebet').value);
    tipe=trim(document.getElementById('tipe').value);
    tanggal=trim(document.getElementById('tanggal').value);
    revisi=trim(document.getElementById('revisi').value);
    kodeorg=trim(document.getElementById('kodeorg').value);
    unit=trim(document.getElementById('unit').value);
    tipeinvoice=trim(document.getElementById('tipeinvoice').value);
    notahutang=trim(document.getElementById('notahutang').value);
    supplier=trim(document.getElementById('supplier').value);
    nilaiinvoice=trim(document.getElementById('nilaiinvoice').value);
    keterangan=trim(document.getElementById('keterangan').value);
    noakun=trim(document.getElementById('noakun').value);
    matauang=trim(document.getElementById('matauang').value);
    kurs=trim(document.getElementById('kurs').value);
    proses=trim(document.getElementById('proses').value);
    
    param='kodeorg='+kodeorg+'&notadebet='+notadebet+'&tipe='+tipe+'&proses='+proses+'&tanggal='+tanggal+'&unit='+unit;
    param+='&revisi='+revisi+'&tipeinvoice='+tipeinvoice+'&notahutang='+notahutang;
    param+='&supplier='+supplier+'&nilaiinvoice='+nilaiinvoice+'&keterangan='+keterangan;
    param+='&matauang='+matauang+'&kurs='+kurs+'&noakun='+noakun+'&tipesupplier='+tipesupplier;
    tujua='keu_slave_notadebet.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {          
                    document.getElementById('notadebet').value=con.responseText;
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
    tipeinvoice=trim(document.getElementById('tipeinvoice').value);
    notadebet=trim(document.getElementById('notadebet').value);
    param = 'unit='+unit+'&proses=showdetail'+'&tipeinvoice='+tipeinvoice+'&notadebet='+notadebet;
    post_response_text('keu_slave_notadebet.php', param, respon);
    
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

function getakun(noakun)
{
    supplierdt=trim(document.getElementById('supplierdt').value);
    kodevhc=trim(document.getElementById('kodevhc').value);
    tipeinvoice=trim(document.getElementById('tipeinvoice').value);
    param = 'kodevhc='+kodevhc+'&supplier='+supplierdt+'&proses=getakun'+'&tipeinvoice='+tipeinvoice+'&noakun='+noakun;
    post_response_text('keu_slave_notadebet.php', param, respon);
    
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
                    supplierdt=trim(document.getElementById('supplierdt').value);
                    // if (supplierdt!='') {
                    //     document.getElementById('kodevhc').value='';
                    //     // document.getElementById('kodevhc').disabled=true;
                    // }
                    document.getElementById('noakundt').innerHTML = con.responseText;
                    
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

function deldt(notadebet)
{
    param='proses=deldt'+'&notadebet='+notadebet;
    tujuan='keu_slave_notadebet.php';
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

function editdt(notadebet,tipe,tanggal,revisi,kodeorg,unit,tipeinvoice,noinvoice_referensi,kodesupplier,nilaiinvoice,keterangan,noakun,matauang,kurs,tipesupplier){
    // alert(tipesupplier);
	document.getElementById('tipesupplier').value=tipesupplier;
    document.getElementById('kodeorg').value=kodeorg;
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
    document.getElementById('notadebet').value=notadebet;
    document.getElementById('noakun').value=noakun;
    document.getElementById('tipe').value=tipe;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('revisi').value=revisi;
    document.getElementById('keterangan').value=keterangan;
    document.getElementById('tipeinvoice').value=tipeinvoice;
    document.getElementById('tipeinvoice').disabled=true;
    document.getElementById('notahutang').value=noinvoice_referensi;
    document.getElementById('notahutang').disabled=true;
    document.getElementById('supplier').value=kodesupplier;
    document.getElementById('supplier').disabled=true;
    document.getElementById('matauang').value=matauang;
    document.getElementById('matauang').disabled=true;
    document.getElementById('kurs').value=kurs;
    document.getElementById('kurs').disabled=true;
    document.getElementById('nilaiinvoice').value=nilaiinvoice;
    document.getElementById('proses').value='update';
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    
	// showdetail(notadebet);
	gettipesupplier(tipesupplier,noakun,notadebet);
}

function clearData(){
    document.getElementById('kodeorg').value='';
    document.getElementById('kodeorg').disabled=false;
    document.getElementById('notahutang').disabled=false;
    document.getElementById('tanggal').disabled=false;
    // document.getElementById('kurs').disabled=false;
    document.getElementById('tipeinvoice').disabled=false;
    document.getElementById('tipesupplier').disabled=false;
    document.getElementById('tipesupplier').value='';
    document.getElementById('noakun').value='';
    document.getElementById('supplier').disabled=false;
    document.getElementById('unit').value='';
    document.getElementById('unit').disabled=false;
    document.getElementById('notadebet').value='';
    document.getElementById('tipe').value=0;
    document.getElementById('tanggal').value='';
    document.getElementById('revisi').value=0;
    document.getElementById('tipeinvoice').value='';
    document.getElementById('notahutang').value='';
    document.getElementById('supplier').value='';
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
    document.getElementById('crnotadebet').value='';
    document.getElementById('listData').style.display='block';
    document.getElementById('formInput').style.display='none';
    document.getElementById('formdetail').innerHTML='';
    document.getElementById('formdetail').style.display='none';
    clearData();
    loadData(0);
}

function loadData(num){
    crnotadebet=document.getElementById('crnotadebet').value;

    param='proses=loadData';
    param+='&page='+num;

    if (crnotadebet != '') {
        param += '&notadebet=' + crnotadebet;
    }

    tujuan='keu_slave_notadebet.php';
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

function viewdetail(notadebet)
{
    form();
    param = 'proses=viewdetail' + '&notadebet=' + notadebet;
    tujuan = 'keu_slave_notadebet.php';
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

function posting(notadebet)
{
    param='proses=posting'+'&notadebet='+notadebet;
    tujuan='keu_slave_notadebet.php';
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
    kmhm=trim(document.getElementById('kmhm').value);
    notadebet=trim(document.getElementById('notadebet').value);
    kodevhc=trim(document.getElementById('kodevhc').value);
    supplierdt=trim(document.getElementById('supplierdt').value);
    kodeasset=trim(document.getElementById('kodeasset').value);
    noakundt=trim(document.getElementById('noakundt').value);
    noakundtold=trim(document.getElementById('noakundtold').value);
    nilai=trim(document.getElementById('nilai').value);
    proses=trim(document.getElementById('prosesdt').value);
    tipeinvoice=trim(document.getElementById('tipeinvoice').value);

    if (noakundt=='' || nilai=='') {
        alert('noakun dan nilai tidak boleh kosong.');
        return;
    }
    
    param='kodevhc='+kodevhc+'&notadebet='+notadebet+'&supplier='+supplierdt+'&kodeasset='+kodeasset+'&proses='+proses+'&noakun='+noakundt+'&noakundtold='+noakundtold;
    param+='&nilai='+nilai+'&tipeinvoice='+tipeinvoice+'&kmhm='+kmhm;

    // if (tipeinvoice=='k') {
    //     noreferensi_transaksi=document.getElementById('noreferensi_transaksi').value;
    //     param+='&noreferensi_transaksi='+noreferensi_transaksi;
    // }

    tujuan='keu_slave_notadebet.php';
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

function searchkodeasset(title,content,ev)
{
    width='auto';
    height='auto';
    showDialog1(title,content,width,height,ev);
    getformkodeasset();
}

function getformkodeasset(){
    unit=trim(document.getElementById('unit').value);
    param='proses=getformkodeasset'+'&unit='+unit;
    tujuan='keu_slave_notadebet.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('formPencarianasset').innerHTML=con.responseText;
                    findkodeasset();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function findkodeasset(){
    unit=trim(document.getElementById('unit').value);
    param='proses=getdatakodeasset'+'&unit='+unit;
    fkodeasset=trim(document.getElementById('fkodeasset').value);
    param+='&kodeasset='+fkodeasset;
    
    tujuan='keu_slave_notadebet.php';
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
                    document.getElementById('containerasset').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setdataasset(kodeasset) {
    document.getElementById('kodeasset').value=kodeasset;
    closeDialog();
}

function cleardetail(){ 
    document.getElementById('kodevhc').disabled=false;
    document.getElementById('kodevhc').value='';
    document.getElementById('tipesupplier').value='';
    document.getElementById('noakun').value='';
    document.getElementById('kodeasset').value='';
    document.getElementById('noakundt').value='';
    document.getElementById('noakundtold').value='';
    document.getElementById('supplierdt').value='';
    document.getElementById('noakundt').disabled=false;
    document.getElementById('nilai').value='';
    document.getElementById('prosesdt').value='insertdt';
    getakun();
}

function searchgudang(title,content,ev)
{
    width='auto';
    height='auto';
    // showDialog1(title,content,width,height,ev);
    alertify.popup(title,content,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
    getformgudang();
}

function getformgudang(){
    unit=trim(document.getElementById('unit').value);
    param='proses=getformgudang'+'&unit='+unit;
    tujuan='keu_slave_notadebet.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('formPencariangudang').innerHTML=con.responseText;
                    findgudang();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function findgudang(){
    unit=trim(document.getElementById('unit').value);
    supplier=trim(document.getElementById('supplier').value);
    param='proses=getdatagudang'+'&unit='+unit+'&supplier='+supplier;
    transgudang=trim(document.getElementById('transgudang').value);
    param+='&transgudang='+transgudang;
    
    tujuan='keu_slave_notadebet.php';
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
                    document.getElementById('containergudang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function adddetail() {
    totrow=trim(document.getElementById('totrow').value);
    notadebet=trim(document.getElementById('notadebet').value);
    noakunkeg=trim(document.getElementById('noakunkeg').value);
    
    var allData='';
    var cekpil=0;
    for(dwc=0;dwc<totrow;dwc++){
        if (document.getElementById('no_'+dwc).checked==true) {
            allData+="&nogudang[]="+document.getElementById('nogudang_'+dwc).innerHTML;
            allData+="&hartot[]="+document.getElementById('hartot_'+dwc).innerHTML;
            cekpil+=1;
        }
    }

    if(cekpil==0){
        alert('Data belum terpilih.');
        return;
    }

    param='totrow='+cekpil+'&notadebet='+notadebet+'&noakun='+noakunkeg+'&proses=adddetail';
    param+=allData;
    
    tujuan='keu_slave_notadebet.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showdetail();
                    closeDialog();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function checkAll()
{
    totrow = document.getElementById('totrow').value;
    btn = document.getElementById('btnall');
    if (btn.checked == true){
        chk = true;
    } else {
        chk = false;
    }

    for (i = 0; i < totrow; i++)
    {
        document.getElementById('no_' + i).checked = chk;
    }
}



function editdetail(kodevhc,kodeasset,noakun,nilai,supplierdt,kmhm){
    document.getElementById('supplierdt').value=supplierdt;
    document.getElementById('kodevhc').value=kodevhc;
    document.getElementById('kodeasset').value=kodeasset;
    document.getElementById('noakundt').value=noakun;
    document.getElementById('noakundtold').value=noakun;
    document.getElementById('nilai').value=nilai;
    document.getElementById('kmhm').value=kmhm;
    document.getElementById('prosesdt').value='updatedt';
    getakun(noakun);
}



function deldetail(noakun,noreferensi_transaksi)
{
    notadebet=trim(document.getElementById('notadebet').value);
    tipeinvoice=trim(document.getElementById('tipeinvoice').value);
    param='proses=deldetail'+'&notadebet='+notadebet+'&tipeinvoice='+tipeinvoice+'&noakun='+noakun+'&noreferensi_transaksi='+noreferensi_transaksi;
    tujuan='keu_slave_notadebet.php';
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

function detailPDF(notadebet, ev) {
    param = "notadebet="+notadebet;
    showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='keu_slave_notadebetpdf.php?" + param + "'></iframe>", '', '', ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}