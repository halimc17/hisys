function loadData(x)
{
    var header = document.getElementById('header');
    var container = document.getElementById('container');
    var containerx = document.getElementById('containerx');
    var containery = document.getElementById('containerfoot');
    var detaildiv = document.getElementById('Detail');
    var listtabledetail = document.getElementById('listtabledetail');
    var param;
    if(x=='cari')
    {
        param='kodeorgcr='+document.getElementById('kodeorgcr').value;
    }
    post_response_text('lgl_slave_checklist.php?proses=loadData',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    containerx.style.display="block";
                    header.style.display='none';
                    detaildiv.style.display='none';
                    listtabledetail.style.display='none';
                    document.getElementById('kodeorgcr').value='';
                    document.getElementById('kodeorg').disabled=false;
                    document.getElementById('tanggalmulai').disabled=false;
                    document.getElementById('tanggalselesai').disabled=false;
                    document.getElementById('jenis').disabled=false;
                    document.getElementById('hbutton').style.display='';
                    arrcon = con.responseText.split('###');
                    container.innerHTML=arrcon[0];
                    containery.innerHTML=arrcon[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cariBast(cb,num,z,x)
{
   
    var param ='page='+num+'&kodeorgcr='+x;
    //alert(param);
    if(cb=='plus')
    {
        if(num > z)
        {

        }
        else
        {
            post_response_text('lgl_slave_checklist.php?proses=loadData',param,respon);
        }

    }
    else if(cb=='min')
    {
        if(num < z)
        {

        }
        else
        {
            post_response_text('lgl_slave_checklist.php?proses=loadData',param,respon);
        }
    }
    else
    {
    post_response_text('lgl_slave_checklist.php?proses=loadData',param,respon);
    }
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    arrcon = con.responseText.split('###');
                    document.getElementById('container').innerHTML=arrcon[0];
                    document.getElementById('containerfoot').innerHTML=arrcon[1];
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }   
}




function adddataform()
{
    var header = document.getElementById('header');
    var containerx = document.getElementById('containerx');
    var detaildiv = document.getElementById('Detail');
    var listtabledetail = document.getElementById('listtabledetail');
    containerx.style.display="none";
    detaildiv.style.display="none";
    listtabledetail.style.display="none";
    header.style.display='block';
                    document.getElementById('kodeorg').disabled=false;
                    document.getElementById('tanggalmulai').disabled=false;
                    document.getElementById('tanggalselesai').disabled=false;
                    document.getElementById('jenis').disabled=false;
}

function tambahInputDetail(btbw,btat)
{
    var kodeorg = document.getElementById('kodeorg').value;
    var periode = document.getElementById('periode').value;
    var pos = document.getElementById('pos').value;
    var minggu = document.getElementById('minggu').value;
    var karyawanid='';
    var shift='';
    var error_catch=0;

    num=1;
    while(document.getElementById('tr_'+num))
    {
        num++;
    }

    for(i=1;i<num;i++){
        if(i==1){
            karyawanid = document.getElementById('karyawanid_'+i).value;
        }
        else
        {
            karyawanid += '###'+document.getElementById('karyawanid_'+i).value;
        }
        for(j=btbw;j<=btat;j++){
            if(shift==''){
                shift = document.getElementById('shift_'+i+'_'+j).value;
            }
            else{
                shift += '###'+document.getElementById('shift_'+i+'_'+j).value;
            }
        }
    }
    //alert(shift);
    var param='kodeorg='+kodeorg+'&periode='+periode+'&pos='+pos+'&minggu='+minggu+'&nomer='+num;
    post_response_text('lgl_slave_checklist.php?proses=plusdetail',param,respon);

        function respon(){
             if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        //alert(con.responseText);
                        document.getElementById('formListdetailx').innerHTML+=con.responseText;
                        var kary = karyawanid.split('###');
                        var shi = shift.split('###');
                        noe=0;
                        for(i=1;i<num;i++){
                            document.getElementById('karyawanid_'+i).value=kary[(i-1)];
                            for(j=btbw;j<=btat;j++){
                                document.getElementById('shift_'+i+'_'+j).value=shi[noe];
                                noe++;
                            }
                        }
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
}

function html(notransaksi) {
    width = '';
    height = '';
    content = "<fieldset style=\"width:98%;\"><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog5(title, content, width, height, ev);
    param = 'notransaksi='+notransaksi;
    tujuan = 'lgl_slave_checklist.php?proses=loadfiles2';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contviewx').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function postingData(notransaksi)
{
    param='notransaksi='+notransaksi;
        
     if(confirm('Posting  will commited for good,  Are you sure..?')){   
        post_response_text('lgl_slave_checklist.php?proses=posting', param, respog); 
     }
        function respog()
        {
            if(con.readyState==4)
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
                        loadData();
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

function checkHeader()
{

    var detaildiv = document.getElementById('Detail');
    var detailform = document.getElementById('detailform');
    var header = document.getElementById('header');
    var headbuttoon = document.getElementById('hbutton');

    var kodeorg = document.getElementById('kodeorg').value;
    var tanggalmulai = document.getElementById('tanggalmulai').value;
    var tanggalselesai = document.getElementById('tanggalselesai').value;
    var jenis = document.getElementById('jenis').value;

     if(kodeorg=='' || tanggalmulai=='' || tanggalselesai=='' || jenis=='' )
    {
        alert("All field is are obligatory");
    }
    else
    {
        var param='notransaksi=&kodeorg='+kodeorg+'&tanggalmulai='+tanggalmulai+'&tanggalselesai='+tanggalselesai+'&jenis='+jenis;
        post_response_text('lgl_slave_checklist.php?proses=checkHeader', param, respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    header.style.display='';
                    document.getElementById('kodeorg').disabled=true;
                    document.getElementById('tanggalmulai').disabled=true;
                    document.getElementById('tanggalselesai').disabled=true;
                    document.getElementById('jenis').disabled=true;
                    headbuttoon.style.display='none';
                    detaildiv.style.display='';
                    document.getElementById('notransaksi').value=con.responseText;
                    addDetailForm(con.responseText,jenis,kodeorg);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function addDetailForm(notransaksi,jenis,kodeorg)
{
    var param='notransaksi='+notransaksi+'&jenis='+jenis+'&kodeorg='+kodeorg+'&method=saveData';
    post_response_text('lgl_slave_checklist.php?proses=getDetail', param, respon);
    console.log(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //console.log(con.responseText);
                    detailform.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function saveData(btbw,btat,jenis,notransaksi,method){
    var detaildiv = document.getElementById('Detail');
    var detailform = document.getElementById('detailform');
    var header = document.getElementById('header');
    var headbuttoon = document.getElementById('hbutton');
    var container = document.getElementById('container');
    var bodyList = document.getElementById('bodyList');

    var listtabledetail = document.getElementById('listtabledetail');
    if(jenis=='Permohonan Perpanjangan HGU' || jenis=='Permohonan HGU')
    {
         var pt = document.getElementById('pt').value;
         var berkedudukandi = document.getElementById('berkedudukandi').value;
         var letaktanah = document.getElementById('letaktanah').value;
         var desa = document.getElementById('desa').value;
         var kecamatan = document.getElementById('kecamatan').value;
         var kabupaten = document.getElementById('kabupaten').value;
         var luastanah = document.getElementById('luastanah').value;
    }
    var checklist='';

    for(i=parseInt(btat);i<=parseInt(btbw);i++)
    {
        if(document.getElementById('checklist_'+i))
        {
            if(document.getElementById('checklist_'+i).checked==true)
            {
                if(checklist=='')
                {
                    checklist='1';
                }
                else
                {
                    checklist+='###1';
                }
            }
            else
            {
                if(checklist=='')
                {
                    checklist='0';
                }
                else
                {
                    checklist+='###0';
                }
            }
        }
        if(document.getElementById('keterangan_'+i))
        {
            checklist+='/'+document.getElementById('keterangan_'+i).value;
            checklist+='/'+i;       
        }
    }

    if(jenis=='Permohonan Perpanjangan HGU' || jenis=='Permohonan HGU')
    {
        var param = 'pt='+pt+'&berkedudukandi='+berkedudukandi+'&letaktanah='+letaktanah+'&desa='+desa;
        param +='&kecamatan='+kecamatan+'&kabupaten='+kabupaten+'&notransaksi='+notransaksi;
        param +='&luastanah='+luastanah+'&checklist='+checklist+'&jenis='+jenis;
    }
    else
    {
        var param = 'checklist='+checklist+'&jenis='+jenis+'&notransaksi='+notransaksi;
    }
    
    console.log(param);
    post_response_text('lgl_slave_checklist.php?proses='+method,param,respon);
    
    //alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    /*header.style.display='none';
                    detaildiv.style.display='none';
                    container.style.display='';
                    listtabledetail.style.display='';
                    headbuttoon.style.display='';
                    loadData();*/
                    console.log(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function showformupload(ev){
    title="UPLOAD FILES";
    width='';
    height='';
    content="<fieldset style=width:100%><legend>Form</legend><div id=contUpload style='overflow:auto;width:100%px;height:auto;' ></div></fieldset>";
    showDialog2(title,content,width,height,ev); 
    
    pos = new Array();
    pos = getMouseP(ev);
    
    /*document.getElementById('dynamic2').style.top = pos[1]+'px';
    document.getElementById('dynamic2').style.left = (pos[0] - 500) +'px';
    document.getElementById('dynamic2').style.display='';*/
}



function showupload(ev,jenis,kodeorg,xxx,yyy){
    showformupload(ev);
    param = "kodeorg="+kodeorg;
    param += "&xxx="+xxx;       
    param += "&yyy="+yyy;   
    param += "&jenisupload="+jenis;     
    tujuan='lgl_slave_checklist.php?proses=showupload';
    post_response_text(tujuan, param, respog);
    console.log(param);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else {
                    
                    document.getElementById('contUpload').innerHTML=con.responseText;
                    loadfiles(jenis,kodeorg,xxx,yyy);
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }   
}

function submitfile(jenis){
    var file = document.getElementById("upload").files[0];
    var kodeorg = document.getElementById('ptupload').innerHTML;
    var xxx = document.getElementById('xxx').innerHTML;
    var yyy = document.getElementById('yyy').innerHTML;     
    var formdata = new FormData();
        formdata.append("xxx", xxx);
        formdata.append("yyy", yyy);
        formdata.append("file", file);
        formdata.append("fileupload", getValue('upload'));
        formdata.append("kodeorg", kodeorg);
        formdata.append("jenisupload", jenis);
    
    if(getValue('upload')==""){
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "lgl_slave_checklist.php?proses=submitfile", true);
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
                    loadfiles(jenis,kodeorg,xxx,yyy);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function viewlistfile(jenis,kodeorg,xxx,yyy){
    width = '';
    height = '';
    content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog2(title, content, width, height, ev);

    param='jenisupload='+jenis+'&kodeorg='+kodeorg+'&xxx='+xxx+'&yyy='+yyy;
    tujuan='lgl_slave_checklist.php?proses=viewlistfile';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else {
                    document.getElementById('contviewz').innerHTML=con.responseText;
                    loadfiles(jenis,kodeorg,xxx,yyy);
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function loadfiles(jenis,kodeorg,xxx,yyy){
    param='jenisupload='+jenis+'&kodeorg='+kodeorg+'&xxx='+xxx+'&yyy='+yyy;
    tujuan='lgl_slave_checklist.php?proses=loadfiles';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else {
                    if(document.getElementById('listfiles')!==null){
                        document.getElementById('listfiles').innerHTML=con.responseText;
                    }
                    if(document.getElementById('loadfilesdetail')!==null){
                        document.getElementById('loadfilesdetail').innerHTML=con.responseText;
                    }
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function deletefile(jenis,kodeorg,xxx,yyy,namafile){
    param = "jenisupload="+jenis;
    param += "&kodeorg="+kodeorg;
    param += "&xxx="+xxx;       
    param += "&yyy="+yyy;     
    param += "&namafile="+namafile;
    
    tujuan='lgl_slave_checklist.php?proses=deletefile';
    post_response_text(tujuan, param, respog);
    
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else {
                    loadfiles(jenis,kodeorg,xxx,yyy);
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function edit(num){
    var detaildiv = document.getElementById('Detail');
    var listtabledetail = document.getElementById('listtabledetail');
    var containerx = document.getElementById('containerx');
    var header = document.getElementById('header');
    var headbuttoon = document.getElementById('hbutton');

    var rownotransaksi = document.getElementById('notransaksi_'+num).innerHTML;
    var rowkodeorg = document.getElementById('kodeorg_'+num).innerHTML;
    var rowtanggalmulai = document.getElementById('tanggalmulai_'+num).innerHTML;
    var rowtanggalselesai = document.getElementById('tanggalselesai_'+num).innerHTML;
    var rowjenis = document.getElementById('jenis_'+num).innerHTML;

    var notransaksi = document.getElementById('notransaksi');
    var kodeorg = document.getElementById('kodeorg');
    var tanggalmulai = document.getElementById('tanggalmulai');
    var tanggalselesai = document.getElementById('tanggalselesai');
    var jenis = document.getElementById('jenis');

    header.style.display='';
    containerx.style.display='none';
    notransaksi.value = rownotransaksi;
    kodeorg.value = rowkodeorg;
    tanggalmulai.value = rowtanggalmulai;
    tanggalselesai.value = rowtanggalselesai;
    jenis.value = rowjenis;
    //minggu.value = rowminggu;

    notransaksi.disabled=true;
    kodeorg.disabled=true;
    tanggalmulai.disabled=true;
    tanggalselesai.disabled=true;
    jenis.disabled=true;

    headbuttoon.style.display='none';
    detaildiv.style.display='block';
    listtabledetail.style.display='block';
    
    editform(rownotransaksi,rowkodeorg,rowtanggalmulai,rowtanggalselesai,rowjenis);
    

}

function deletehd(num) {
    var rownotransaksi = document.getElementById('notransaksi_'+num).innerHTML;

    var param='notransaksi='+rownotransaksi;
    if(confirm('Are you sure..?')){
            post_response_text('lgl_slave_checklist.php?proses=deletehd', param, respon);
        }
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function editform(notransaksi,kodeorg,tanggalmulai,tanggalselesai,jenis){
    var detailform = document.getElementById('detailform');

    var param='notransaksi='+notransaksi+'&kodeorg='+kodeorg+'&tanggalmulai='+tanggalmulai;
    param+='&tanggalselesai='+tanggalselesai+'&jenis='+jenis+'&method=updateData';

    post_response_text('lgl_slave_checklist.php?proses=getDetail', param, respon);
    console.log(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    detailform.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function editDetail(num,tglbw,tglat){
   
    var rowkaryawanid = document.getElementById('karyawanid_'+num).innerHTML;
    var rownotransaksi = document.getElementById('id_'+num).innerHTML;

    var karyawanid = document.getElementById('karyawanid');
    var notransaksi = document.getElementById('notransaksi');
    var buttons = document.getElementById('updateDetailButton');
    //karyawanid.disabled = false;
    for(j=tglbw;j<=tglat;j++){
        document.getElementById('shift_'+j).value = document.getElementById('shift_'+num+'_'+j).innerHTML;
    }
    notransaksi.value = rownotransaksi;
    karyawanid.value = rowkaryawanid;

    buttons.removeAttribute('onclick');

    buttons.setAttribute('onclick','updateData('+tglbw+','+tglat+')');
}

function deleteDetail(num,tglbw,tglat){
   
    var kodeorg = document.getElementById('kodeorg').value;
    var periode = document.getElementById('periode').value;
    var minggu = document.getElementById('minggu').value;
    var pos=document.getElementById('pos').value;

    var rowkaryawanid = document.getElementById('karyawanid_'+num).innerHTML;
    var rownotransaksi = document.getElementById('id_'+num).innerHTML;

    var shift='';
    
    for(j=tglbw;j<=tglat;j++){
         if(shift=='')
         {
            shift = document.getElementById('shift_'+num+'_'+j).innerHTML
         }
         else
         {
            shift += '###'+document.getElementById('shift_'+num+'_'+j).innerHTML
         }
    }

    var param = 'notransaksi='+rownotransaksi+'&karyawanid='+rowkaryawanid+'&periode='+periode;
    param +='&batasbawah='+tglbw+'&batasatas='+tglat;
    param += '&shift='+shift;

if(confirm('Are you sure..?')){
    post_response_text('lgl_slave_checklist.php?proses=DelData', param, respon);
}
    

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   // alert(con.responseText);
                    loadDataDetail(kodeorg,periode,pos,minggu);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function saveDataDetail(tglbw,tglat){
    var kodeorg = document.getElementById('kodeorg').value;
    var periode = document.getElementById('periode').value;
    var pos = document.getElementById('pos').value;
    var minggu = document.getElementById('minggu').value;

    var karyawanid = document.getElementById('karyawanid');
    var notransaksi = kodeorg+'/'+periode+'/'+pos+'/'+minggu;
    var shift='';
    var error_catch=0;
    for(j=tglbw;j<=tglat;j++){
         if(document.getElementById('shift_'+j).value == '')
         {
            error_catch = 1;
         }
         if(shift=='')
         {
            
            shift = document.getElementById('shift_'+j).value
         }
         else
         {
            
            shift += '###'+document.getElementById('shift_'+j).value
         }
    }
    if(error_catch!=0)
    {
        alert('shift is obligatory');
    }
    else
    {
        var param ='karyawanid='+karyawanid.value;
        param +='&shift='+shift+'&pos='+pos+'&periode='+periode;
        param +='&batasbawah='+tglbw+'&batasatas='+tglat;
        param +='&notransaksi='+notransaksi;
       // alert(param);
        post_response_text('lgl_slave_checklist.php?proses=saveDataDetail', param, respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    
                    loadDataDetail(kodeorg,periode,pos,minggu);

                    buttons.removeAttribute('onclick');

                    buttons.setAttribute('onclick','saveDataDetail('+tglbw+','+tglat+')');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function updateData(tglbw,tglat){
    var kodeorg = document.getElementById('kodeorg').value;
    var periode = document.getElementById('periode').value;
    var pos = document.getElementById('pos').value;
    var minggu = document.getElementById('minggu').value;

    var buttons = document.getElementById('updateDetailButton');

    var karyawanid = document.getElementById('karyawanid');
    var notransaksi = document.getElementById('notransaksi');
    var shift='';
    var error_catch=0;
    for(j=tglbw;j<=tglat;j++){
         if(document.getElementById('shift_'+j).value == '' )
         {
            error_catch = 1;
         }
         if(shift=='')
         {
            shift = document.getElementById('shift_'+j).value
         }
         else
         {
            shift += '###'+document.getElementById('shift_'+j).value
         }
    }
    if(error_catch==1)
    {
        alert('shift is obligatory');
    }
    else
    {
        var param ='karyawanid='+karyawanid.value;
        param +='&shift='+shift+'&pos='+pos+'&periode='+periode;
        param +='&batasbawah='+tglbw+'&batasatas='+tglat;
        param +='&notransaksi='+notransaksi.value;
        //alert(param);
        post_response_text('lgl_slave_checklist.php?proses=updateData', param, respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    
                    loadDataDetail(kodeorg,periode,pos,minggu);

                    buttons.removeAttribute('onclick');

                    buttons.setAttribute('onclick','saveDataDetail('+tglbw+','+tglat+')');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function loadDataDetail(kodeorg,periode,pos,minggu){
    var tabledetail = document.getElementById('tabledetail');
    

    var param='kodeorg='+kodeorg+'&periode='+periode+'&pos='+pos+'&minggu='+minggu;
    //alert(param);
    post_response_text('lgl_slave_checklist.php?proses=loadDataDetail', param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    tabledetail.innerHTML=con.responseText;
                    getWeek(minggu);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}