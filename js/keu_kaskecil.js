function deleteht(notransaksi){
    param = 'method=deleteht&notransaksi=' + notransaksi;
    tujuan = 'keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    loaddata(0);
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function deleteDt(notransaksi,nourut,novoucher){
    param = 'method=deleteDt&notransaksi='+notransaksi+'&nourut='+nourut+'&novoucher='+novoucher;
    tujuan = 'keu_slave_kaskecil.php';
    if(confirm(novoucher+","+bahasa.notifdeleteingdata)){
        post_response_text(tujuan, param, respog);    
    }
    
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    loaddatadetail();
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function setketerangan(noakun){
    param = 'method=setketerangan&noakun=' + noakun;
    tujuan = 'keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    // document.getElementById('keterangan').disabled=true;
                    document.getElementById('keterangan').value=con.responseText;
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function disableditem(){
    if(document.getElementById('jenis').value=='1'){
        document.getElementById('noakun').value='';
        document.getElementById('noaruskas').value='';
        document.getElementById('keterangan').value='';
        document.getElementById('noaruskas').disabled=true;
        document.getElementById('noakun').disabled=true;
        //document.getElementById('noreferensi').disabled=true;
        document.getElementById('noreferensi').value='';
        document.getElementById('keterangan').disabled=false;
    }
    else if(document.getElementById('jenis').value=='2'){
        document.getElementById('noaruskas').disabled=false;
        document.getElementById('noakun').disabled=false;
        document.getElementById('noreferensi').disabled=false;
        //document.getElementById('noreferensi').readonly=true;
        document.getElementById('noreferensi').value='';
        document.getElementById('keterangan').disabled=false;
    }
    else{
        document.getElementById('noaruskas').disabled=false;
        document.getElementById('noreferensi').disabled=true;
        document.getElementById('noakun').disabled=false;
        document.getElementById('noreferensi').value='';
        document.getElementById('keterangan').disabled=false;
    }
}

function showformupload(ev) {
    title = "UPLOAD FILES";
    width = '';
    height = '';
    content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
    showDialog2(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic2').style.top = pos[1] + 'px';
    document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
    document.getElementById('dynamic2').style.display = '';
}

function showupload(ev, notransaksi,tipe,jenis,posting) {
    showformupload(ev);
    param = "";
    param += "notransaksi=" + notransaksi+"&tipe=" + tipe+"&jenis=" + jenis+"&posting=" + posting;
    //alert(param);
    post_response_text('keu_slave_kaskecil.php?method=showupload', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfiles(notransaksi,tipe,jenis,posting);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadfiles(notransaksi,tipe,jenis,posting) {
    param = "notransaksi=" + notransaksi+"&tipe=" + tipe+"&jenis=" + jenis+"&posting=" + posting;
    post_response_text('keu_slave_kaskecil.php?method=loadfiles', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                    }
                    if (document.getElementById('loadfilesdetail') !== null) {
                        document.getElementById('loadfilesdetail').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefile(notransaksi, namafile) {
    param = "notransaksi=" + notransaksi;
    param += "&namafile=" + namafile;
    post_response_text('keu_slave_kaskecil.php?method=deletefile', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile() {
    var file = document.getElementById("upload").files[0];
    var notransaksi = document.getElementById('noupload').innerHTML;
    var formdata = new FormData();
    formdata.append("notransaksi", notransaksi);
    formdata.append("file", file);
    formdata.append("fileupload", document.getElementById("upload").value);
    alert(document.getElementById("upload").value);
    if (document.getElementById("upload").value == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "keu_slave_kaskecil.php?method=submitfile", true);
    busy_on();
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function detaildata(unit,periode){
    file='excel';
    judul='Report Ms.Excel';    
    param = 'method=detaildata&unit=' + unit+'&periode='+periode+'&file='+file;
    tujuan='keu_slave_kaskecil.php';
    ev='event';
    printFile(param,tujuan,judul,ev)    
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='20';
   height='20';
   content="<iframe frameborder=0 width=10 height=10 src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev);  
}


function posting (notransaksi,tipe,jenis){
    if(tipe=='M'){
        param = 'method=postingmasuk&notransaksi=' + notransaksi+'&jenis='+jenis;
    }else{
        param = 'method=postingkeluar&notransaksi=' + notransaksi+'&jenis='+jenis;
    }
    tujuan = 'keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    alert(con.responseText);
                    loaddata(0);
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getopening(notransaksi){
    unit=document.getElementById('unit').value;
    notransaksi=document.getElementById('notransaksi').value;
    tanggal=document.getElementById('tanggal').value;
    
    param = 'method=getopening&unit='+unit+'&notransaksi='+notransaksi+'&tanggal='+tanggal;
    //alert(param);
    tujuan = 'keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    isi = con.responseText.split("#####");
                    document.getElementById('opening').value = isi[0];
                    document.getElementById('advance').value = isi[1];
                    document.getElementById('closing').value = isi[2];
                    document.getElementById('expense').value = isi[3];
                    document.getElementById('sawal').value = isi[4];
                    document.getElementById('noaruskas').innerHTML = isi[5];
                    document.getElementById('penerima').innerHTML = isi[6];
                    
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function searchNoRef(title,content,ev){
    width='';
    height='';
    if(document.getElementById('unit').value=='')
    {
        alert('pilih unit terlebih dahulu');
    }
    else
    {
        showDialog4(title,content,width,height,ev);
        getformNoRef();
    }
    
}

function getformNoRef(){
    notrans=document.getElementById('notransaksi').value;    
    param = "notransaksi="+notrans;
    tujuan='keu_slave_kaskecil.php';
    //console.log('masuk');
    post_response_text(tujuan+'?'+'method=getformNoRef', param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //console.log(con.responseText);
                    document.getElementById('formPencariandata').innerHTML=con.responseText;
                    findref();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
} 

function findref(){
    param='unit='+document.getElementById('unit').value;
    novoucher=trim(document.getElementById('novouchercr').value);
    notrans=document.getElementById('notransaksiData').value;
    param+='&novoucher='+novoucher+'&notransaksi='+notrans;
    //alert(param);
    tujuan='keu_slave_kaskecil.php';
    post_response_text(tujuan+'?'+'method=getdatanoref', param, respog);
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
                    //alert(con.responseText);
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

function getnoref(notrans,noakun,keterangaan,novoucher){
    unit=document.getElementById('unit');
    unit=unit.options[unit.selectedIndex].value;
    param='notransaksi='+notrans+'&unit='+unit;
    tujuan='keu_slave_kaskecil.php';
    post_response_text(tujuan+'?'+'method=getnoref', param, respog);
    console.log(param);
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
                    data=con.responseText.split('###');
                    console.log(data);
                    document.getElementById('noreferensi').value=data[0];
                    document.getElementById('noreferensival').value=data[1];
                    getnoakun(noakun,keterangaan,novoucher);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdok(notransaksi,novoucher,jumlah) {
    setValue('noreferensival',notransaksi);
    setValue('noreferensi',novoucher);
    setValue('jumlah',jumlah);
    closeDialog4();
}

/*function gethitung(){
    jumlahditerima=document.getElementById('jumlahditerima').value;
    jumlahdipakai=document.getElementById('jumlahdipakai').value;
    jumlah=parseFloat(jumlahditerima)-parseFloat(jumlahdipakai);
    document.getElementById('jumlah').value=jumlah;
    // getsaldoberjalan();
}*/

function gethitung(){
    jumlahditerima=document.getElementById('jumlahditerima').value;
    jumlahdipakai=document.getElementById('jumlahdipakai').value;
    jumlah=parseFloat(jumlahditerima)-parseFloat(jumlahdipakai);
    document.getElementById('jumlah').value=jumlah;
    // getsaldoberjalan();
}






function buatcash(){

    unitcash=document.getElementById('unitcash').value;
    unitcashdata='';
    notransaksicashdata='';
    novouchercashdata='';
    noaruskascashdata='';
    noakuncashdata='';
    keterangancashdata='';
    plafoncashdata='';
    tanggalcashdata=document.getElementById('tanggalcashdata').value;
    penerimacashdata=document.getElementById('penerimacashdata').value;
    totjumlah=remove_comma_var(document.getElementById('totjumlah').innerHTML);
    selisihTopup=remove_comma_var(document.getElementById('selisihTopup').innerHTML);
    totPlafon=document.getElementById('plafondTopUpa').value;
    no=1;
    while(document.getElementById('tr_'+no))
    {
        if(no==1)
        {
            unitcashdata=document.getElementById('unitcashdata_'+no).innerHTML;
            notransaksicashdata=document.getElementById('notransaksicashdata_'+no).innerHTML;
            novouchercashdata=document.getElementById('novouchercashdata_'+no).innerHTML;
            noaruskascashdata=document.getElementById('noaruskascashdata_'+no).innerHTML;
            noakuncashdata=document.getElementById('noakuncashdata_'+no).innerHTML;
            keterangancashdata=document.getElementById('keterangancashdata_'+no).innerHTML;
            plafoncashdata=remove_comma_var(document.getElementById('plafoncashdata_'+no).innerHTML);
        }
        else
        {
            unitcashdata+='###'+document.getElementById('unitcashdata_'+no).innerHTML;
            notransaksicashdata+='###'+document.getElementById('notransaksicashdata_'+no).innerHTML;
            novouchercashdata+='###'+document.getElementById('novouchercashdata_'+no).innerHTML;
            noaruskascashdata+='###'+document.getElementById('noaruskascashdata_'+no).innerHTML;
            noakuncashdata+='###'+document.getElementById('noakuncashdata_'+no).innerHTML;
            keterangancashdata+='###'+document.getElementById('keterangancashdata_'+no).innerHTML;
            plafoncashdata+='###'+remove_comma_var(document.getElementById('plafoncashdata_'+no).innerHTML);
        }
        no++;
    }

    prd=document.getElementById('periodecash');
    prd=prd.options[prd.selectedIndex].value;

    
    
    param = 'method=buatcash&unitcashdata=' + unitcashdata+'&notransaksicashdata='+notransaksicashdata+'&periodecash='+prd;
    param+='&novouchercashdata='+novouchercashdata+'&noaruskascashdata='+noaruskascashdata+'&penerimacashdata='+penerimacashdata;
    param+='&noakuncashdata='+noakuncashdata+'&keterangancashdata='+keterangancashdata+'&unit='+unitcash+'&totPlafon='+totPlafon;
    param+='&plafoncashdata='+plafoncashdata+'&totjumlah='+totjumlah+'&tanggalcashdata='+tanggalcashdata+'&selisihTopup='+selisihTopup;
    //alert(param);
    tujuan = 'keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    //alert(con.responseText);
                    console.log(con.responseText);
                    // prosescash();
                    document.getElementById('datadetailcash').innerHTML='';
                    document.getElementById('unitcash').value='';
                    document.getElementById('periodecash').value='';
                    displayList();
                    //document.getElementById('datadetailcash').innerHTML=con.responseText;

                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}






function buatcashawal(){
    
    unitcash=document.getElementById('unitcash').value;
    periodecash=document.getElementById('periodecash').value;
    unitcashdata=document.getElementById('unitcashdata').innerHTML;
    plafoncashdata=document.getElementById('plafoncashdata').value;
    notransaksicashdata=document.getElementById('notransaksicashdata').innerHTML;
    tanggalcashdata=document.getElementById('tanggalcashdata').value;
    novouchercashdata=document.getElementById('novouchercashdata').value;
    noakuncashdata=document.getElementById('noakuncashdata').value;
    keterangancashdata=document.getElementById('keterangancashdata').value;
    penerimacashdata=document.getElementById('penerimacashdata').value;
    noaruskascashdata=document.getElementById('noaruskascashdata').value;
    saldoawal=document.getElementById('saldoawal').value;

    plafoncashdata=remove_comma_var(plafoncashdata);
    
    param = 'method=buatcashawal&unitcashdata=' + unitcashdata+'&plafoncashdata='+plafoncashdata+'&unit='+unitcash;
    param+='&novouchercashdata='+novouchercashdata+'&noakuncashdata='+noakuncashdata+'&saldoawal='+saldoawal;
    param+='&tanggalcashdata='+tanggalcashdata+'&notransaksicashdata='+notransaksicashdata+'&periodecash='+periodecash;
    param+='&keterangancashdata='+keterangancashdata+'&penerimacashdata='+penerimacashdata+'&noaruskascashdata='+noaruskascashdata;
    tujuan = 'keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    console.log(con.responseText);
                    // prosescash();
                    document.getElementById('datadetailcash').innerHTML='';
                    document.getElementById('unitcash').value='';
                    document.getElementById('periodecash').value='';
                    displayList();
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function prosescash(){
    unit=document.getElementById('unitcash').value;
    periode=document.getElementById('periodecash').value;
    plafondTopUpa=document.getElementById('plafondTopUpa').value;
    tipe=document.getElementById('tipeDis');
    tipe=tipe.options[tipe.selectedIndex].value;
    tglcash=document.getElementById('tanggalcashdata');

    param = 'method=prosescash&unit=' + unit+'&plafondTopUpa='+plafondTopUpa;
    param += '&periode=' + periode+'&tipeDis='+tipe;
    if(tglcash!=null){
        param+='&tanggalcashdata='+tglcash.value;   
    }
    tujuan = 'keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    if(tipe==1){
                        // logout();
                        add_new_data();
                    }else{
                        document.getElementById('datadetailcash').innerHTML = con.responseText;    
                    }
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}




// function buatcash (notransaksi){
    // param = 'method=buatcash&notransaksi=' + notransaksi;
    // tujuan = 'keu_slave_kaskecil.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if (con.readyState == 4){
            // if (con.status == 200){
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // }  else {
                    // prosescash();
                // }
            // }  else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }


function editht(notransaksi,unit,tanggal){
    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('tanggal').disabled=true;    
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    loaddatadetail();

}

function editdt(nourut,jenis,noref,noaruskas,noakun,keterangan,penerima,jumlahditerima,jumlahdipakai,jumlah,saldoberjalan,novoucher){
    document.getElementById('nourut').value=nourut;
    document.getElementById('nourut').disabled=true;
    document.getElementById('jenis').value=jenis;
    document.getElementById('noaruskas').value=noaruskas;
    document.getElementById('noakun').value=noakun;
    document.getElementById('penerima').value=penerima;
    document.getElementById('jumlah').value=jumlah;
    document.getElementById('keterangan').value=keterangan;
    document.getElementById('method').value='update';
    document.getElementById('novoucher').value=novoucher;
   
    if(jenis==2)
    {
    // console.log('masuk');
        document.getElementById('noreferensi').disabled=false;
        document.getElementById('noreferensi').value=noref;
        getnoref(noref,noakun,keterangan,novoucher);
    }
    else{
        document.getElementById('noreferensi').disabled=true;
        getnoakun(noakun,keterangan,novoucher);
    }
    // document.getElementById('header').style.display = 'block';
    // document.getElementById('listData').style.display = 'none';
    // loaddatadetail();
}

function canceldt(){
    document.getElementById('method').value='insert';
    document.getElementById('nourut').value='';
    document.getElementById('jenis').value='';
    document.getElementById('noakun').value='';
    document.getElementById('noaruskas').value='';
//document.getElementById('unit').value='';
//document.getElementById('tanggal').value='';
    document.getElementById('penerima').value='';
    document.getElementById('novoucher').value='';
    document.getElementById('noreferensi').value='';
    document.getElementById('keterangan').value='';
    document.getElementById('keterangan2').value='';
    document.getElementById('jumlahditerima').value='0';
    document.getElementById('jumlahdipakai').value='0';
    document.getElementById('jumlah').value='0';
//document.getElementById('unit').disabled=false;
//document.getElementById('tanggal').disabled=false;

    
}
function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    hal=parseInt(pg)-1;
    loaddata(hal);
}

function loaddata(page){
    notransaksisch=document.getElementById('notransaksisch').value;
    tanggalsch=document.getElementById('tanggalsch').value;
    param = 'method=loaddata&page=' + page;
    if (notransaksisch != '') {
        param += '&notransaksisch=' + notransaksisch;
    }
    if (tanggalsch != '') {
        param += '&tanggalsch=' + tanggalsch;
    }
    tujuan = 'keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadetail(){
    notransaksi=document.getElementById('notransaksi').value; 
    param='method=loaddatadetail'+'&notransaksi='+notransaksi;
    tujuan = 'keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    isdt = con.responseText.split("####");
                    document.getElementById('datadetail').innerHTML = isdt[0];
                    document.getElementById('closing').value = isdt[1];
                    getopening(notransaksi);
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan(){
    notransaksi=document.getElementById('notransaksi').value; 
    unit=document.getElementById('unit').value; 
    tanggal=document.getElementById('tanggal').value; 
    noreferensi=document.getElementById('noreferensi').value; 
    noaruskas=document.getElementById('noaruskas').value; 
    noakun=document.getElementById('noakun').value; 
    keterangan=document.getElementById('keterangan').value; 
    keterangan2=document.getElementById('keterangan2').value; 
    penerima=document.getElementById('penerima').value; 
    jumlahditerima=document.getElementById('jumlahditerima').value; 
    jumlahdipakai=document.getElementById('jumlahdipakai').value; 
    jumlah=document.getElementById('jumlah').value; 
    saldoberjalan=document.getElementById('saldoberjalan').value; 
    method=document.getElementById('method').value; 
    nourut=document.getElementById('nourut').value; 
    novoucher=document.getElementById('novoucher').value; 
    opening=document.getElementById('opening').value; 
    advance=document.getElementById('advance').value; 
    closing=document.getElementById('closing').value; 
    jenis=document.getElementById('jenis').value; //diganti stat karna status sudah di pakai untuk fungsi javascript
   
    
    param='notransaksi='+notransaksi+'&unit='+unit+'&tanggal='+tanggal+'&noaruskas='+noaruskas+'&noakun='+noakun;
    param+='&keterangan='+keterangan+'&keterangan2='+keterangan2+'&penerima='+penerima+'&novoucher='+novoucher;
    param+='&jumlahditerima='+jumlahditerima+'&jumlahdipakai='+jumlahdipakai+'&jumlah='+jumlah+'&saldoberjalan='+saldoberjalan+'&method='+method;
    param+='&nourut='+nourut+'&jenis='+jenis+'&noreferensi='+noreferensi+'&opening='+opening+'&advance='+advance+'&closing='+closing;
    tujuan='keu_slave_kaskecil.php';
     if(jenis != '1')
    {
        if(jenis == '2')
        {
            if(noaruskas=='' || noakun=='' || noreferensi==''){
             alert('lengkapi pengisian');
             return;
            }
            else
            {
              post_response_text(tujuan, param, respon);
            }
        }
        else
        {
            if(noaruskas=='' || noakun==''){
             alert('lengkapi pengisian');
             return;
            }
            else
            {
              post_response_text(tujuan, param, respon);
            }
        }
        
    }
    else
    {
        post_response_text(tujuan, param, respon);
    }
        
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // if(con.responseText=="input tanggal sebelum tanggal top up")
                    // {
                    //    alert(con.responseText); 
                    //    return;
                    // }
                    
                    document.getElementById('notransaksi').value=con.responseText;
                    document.getElementById('unit').value=unit;
                    document.getElementById('unit').disabled=true;
                    document.getElementById('tanggal').value=tanggal;
                    document.getElementById('tanggal').disabled=true;
                    //document.getElementById('jenis').disabled=true;
                    document.getElementById('nourut').value='';
                    canceldt();
                    loaddatadetail();
                }
            } else  {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function form(data,nodialog){
    width = '720';
    height = '';
    //nopp=document.getElementById('nopp_'+id).value;
    content = "<fieldset><legend>Data</legend><div id="+data+" align=left style=\"width:700px;max-height:300px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    if(nodialog==1){
        showDialog1(title, content, width, height, ev); 
    }
    else if (nodialog==2){
        showDialog2(title, content, width, height, ev); 
    }
    else if (nodialog==3){
        showDialog2(title, content, width, height, ev); 
    }
    
}


function popupdetail(data,nodialog,notransaksi,unit,periode){
    form(data,nodialog);
    param = 'method='+data;
    param+='&notransaksi='+notransaksi;
    param+='&unit='+unit;
    param+='&periode='+periode;
    tujuan = 'keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    document.getElementById(data).innerHTML = con.responseText;
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getnoakun(noakun,keterangan,novoucher){
    noaruskas=document.getElementById('noaruskas').value; 
    // noaruskas=noaruskas.options[noaruskas.selectedIndex].value;
    unit=document.getElementById('unit').value; 
    // unit=unit.options[unit.selectedIndex].value;
    novcher=document.getElementById('novoucher').value;
    param='method=getnoakun'+'&noaruskas='+noaruskas+'&noakun='+noakun+'&unit='+unit;
    if(keterangan!=null){
        param+="&keterangan="+keterangan;
    }
    if(novoucher!=null){
        param+="&novoucher="+novoucher;
    }
    console.log(param);
    //alert(novoucher);
    tujuan='keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    data=con.responseText.split('####');
                    document.getElementById('noakun').innerHTML=data[0];
                    document.getElementById('keterangan').innerHTML=data[1];
                    //.value=trim(con.responseText);
                    if(novoucher!=null){
                        document.getElementById('keterangan2').value=data[2];
                    }
                    
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
     }      
}

function getnoakuncash(){
    noaruskascash=document.getElementById('noaruskascash').value; 
    param='method=getnoakuncash'+'&noaruskascash='+noaruskascash;
    tujuan='keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('noakuncash').innerHTML=con.responseText;
                    //.value=trim(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
     }      
}



function displayList(){
    document.getElementById('notransaksisch').value='';
    document.getElementById('tanggalsch').value='';
    document.getElementById('listData').style.display = 'block';
    document.getElementById('header').style.display = 'none';
   // document.getElementById('detail').style.display = 'none';
    loaddata(0);
}


function add_new_data(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    document.getElementById('unitcash').value='';
    cancel();  
}



function cancel(){
    //alert("masuuukkk");
    // document.getElementById('detail').style.display = 'none';
    document.getElementById('datadetailcash').innerHTML="";
    document.getElementById('plafondTopUpa').value=0;
    document.getElementById('datadetail').innerHTML ='';
    document.getElementById('notransaksi').value='';
    document.getElementById('unit').value='';
    document.getElementById('unit').disabled=false;
    document.getElementById('jenis').value='';
    document.getElementById('jenis').disabled=false;
    document.getElementById('tanggal').value='';
    document.getElementById('tanggal').disabled=false;
    document.getElementById('notransaksi').value=''; 
    document.getElementById('noreferensi').value=''; 
    document.getElementById('unit').value=''; 
    document.getElementById('tanggal').value=''; 
    document.getElementById('noaruskas').value=''; 
    document.getElementById('noakun').value=''; 
    document.getElementById('keterangan').value=''; 
    document.getElementById('penerima').value=''; 
    document.getElementById('jumlahditerima').value='0'; 
    document.getElementById('jumlahdipakai').value='0'; 
    document.getElementById('jumlah').value='0'; 
    document.getElementById('saldoberjalan').value='0'; 
    document.getElementById('opening').value='0'; 
    document.getElementById('closing').value='0'; 
    document.getElementById('advance').value='0'; 
    document.getElementById('expense').value=0;
    document.getElementById('sawal').value=0;
    document.getElementById('periodecash').value='';

}
function getPeriodeKas(){
    unit=document.getElementById('unitcash');
    unit=unit.options[unit.selectedIndex].value;
    periodecash=document.getElementById('periodecash');
    periodecash=periodecash.options[periodecash.selectedIndex].value;
    param='method=getPeriodeKas'+'&unit='+unit+'&periodecash='+periodecash;
    tujuan='keu_slave_kaskecil.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    dt=con.responseText.split("####");
                    document.getElementById('periodecash').innerHTML=dt[0];
                    document.getElementById('plafondTopUpa').value=dt[1];
                    //.value=trim(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
     }  
}
function getNilaiData(tipe){
    salPlafon=document.getElementById('plafoncashdata').value;
    saldoawal=document.getElementById('saldoawal').value;
    plafondTopUpa=document.getElementById('plafondTopUpa').value;
    if(tipe==1){
        if(saldoawal!=0){
            hasil=parseFloat(plafondTopUpa)-parseFloat(saldoawal);    
        }else{
            hasil=plafondTopUpa;
        }
        document.getElementById('plafoncashdata').value=hasil;
    }
    if(tipe==2){
        if(salPlafon!=0){
            hasil=parseFloat(plafondTopUpa)-parseFloat(salPlafon);    
        }else{
            hasil=saldoawal;
        }
        document.getElementById('saldoawal').value=hasil;
    }
}



function detailPDF(notransaksi,kodeorg,tipetransaksi,noakun,ev) {
    // Prep Param
    param = "proses=pdf&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kaskecil_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

