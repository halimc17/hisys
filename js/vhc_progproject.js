function loadData(){
    var header = document.getElementById('header');
    var container = document.getElementById('container');
    var detaildiv = document.getElementById('Detail');
    var listtabledetail = document.getElementById('listtabledetail');
    var loksrc = document.getElementById('loksrc').value;
    var peksrc = document.getElementById('peksrc').value;
    var tglawal = document.getElementById('tglawal').value;
    var tglakhir = document.getElementById('tglakhir').value;
    var mgsrc = document.getElementById('mgsrc').value;
    
	param = 'loksrc='+loksrc+'&peksrc='+peksrc;
	param += '&tglawal='+tglawal+'&tglakhir='+tglakhir;
	param += '&mgsrc='+mgsrc;
   
    post_response_text('vhc_slave_progproject.php?proses=loadData',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    container.style.display="block";
                    header.style.display='none';
                    detaildiv.style.display='none';
                    listtabledetail.style.display='none';
                    document.getElementById('kodeorg').disabled=false;
                    document.getElementById('kodeproject').disabled=false;
                    document.getElementById('minggu').disabled=false;
                    document.getElementById('tanggalawal').disabled=false;
                    document.getElementById('tanggalakhir').disabled=false;
                    document.getElementById('hbutton').style.display='';

                    arrres= con.responseText.split('###');
                    document.getElementById('bodyList').innerHTML=arrres[0];
                    document.getElementById('tfootlist').innerHTML=arrres[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cariBast(num)
{
    var param ='page='+num;
    //alert(param);
    post_response_text('vhc_slave_progproject.php?proses=loadData',param,respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    arrres= con.responseText.split('###');
                    document.getElementById('bodyList').innerHTML=arrres[0];
                    document.getElementById('tfootlist').innerHTML=arrres[1];
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }   
}

function caridata()
{
    var header = document.getElementById('header');
    var container = document.getElementById('container');
    var detaildiv = document.getElementById('Detail');
    var listtabledetail = document.getElementById('listtabledetail');

    var kodeorg = document.getElementById('kodeorgcr').value;
    var periode = document.getElementById('periodecr').value;

    var param = 'kodeorg='+kodeorg+'&periode='+periode;
   
    post_response_text('vhc_slave_progproject.php?proses=loadCariData',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    container.style.display="block";
                    header.style.display='none';
                    detaildiv.style.display='none';
                    listtabledetail.style.display='none';
                    document.getElementById('kodeorg').disabled=false;
                    document.getElementById('periode').disabled=false;
                    document.getElementById('pos').disabled=false;
                    document.getElementById('minggu').disabled=false;
                    document.getElementById('hbutton').style.display='';

                    container.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function adddataform()
{
    var header = document.getElementById('header');
    var container = document.getElementById('container');
    var detaildiv = document.getElementById('Detail');
    var listtabledetail = document.getElementById('listtabledetail');
    container.style.display="none";
    detaildiv.style.display="none";
    listtabledetail.style.display="none";
    header.style.display='block';
    document.getElementById('kodeorg').disabled=false;
                    document.getElementById('kodeproject').disabled=false;
                    document.getElementById('minggu').disabled=false;
                    document.getElementById('tanggalawal').disabled=false;
                    document.getElementById('tanggalakhir').disabled=false;
                    document.getElementById('hbutton').style.display='';
}
function kurangInputDetail()
{
    num=1;
    while(document.getElementById('tr_'+num))
    {
        num++;
    }
    if(num>2)
    {
    var thead = document.getElementById('tr_'+(num-1));
            thead.parentNode.removeChild(thead);
    }
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
    post_response_text('vhc_slave_progproject.php?proses=plusdetail',param,respon);

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

function getproject(kodeproject)
{
    var kodeorg = document.getElementById('kodeorg').value;
    var param;
    param = 'kodeorg='+kodeorg;   
    
    //alert(param);
    post_response_text('vhc_slave_progproject.php?proses=getproject',param,respon);

        function respon(){
             if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        //alert(con.responseText);
                        document.getElementById('kodeproject').innerHTML=con.responseText;
                        if(kodeproject!='')
                        {
                            document.getElementById('kodeproject').value=kodeproject;
                        }
                    }
                } else {
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
    var kodeproject = document.getElementById('kodeproject').value;
    var minggu = document.getElementById('minggu').value;
    var tanggalawal = document.getElementById('tanggalawal').value;
    var tanggalakhir = document.getElementById('tanggalakhir').value;

     if(kodeorg=='' || kodeproject=='' || minggu=='' || tanggalawal=='' || tanggalakhir=='' )
    {
        alert("All field is are obligatory");
    }
    else
    {
        var param='kodeorg='+kodeorg+'&kodeproject='+kodeproject+'&minggu='+minggu+'&tanggalawal='+tanggalawal+'&tanggalakhir='+tanggalakhir;
        //console.log(param);
        post_response_text('vhc_slave_progproject.php?proses=checkHeader', param, respon);
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
                    document.getElementById('kodeproject').disabled=true;
                    document.getElementById('minggu').disabled=true;
                    document.getElementById('tanggalawal').disabled=true;
                    document.getElementById('tanggalakhir').disabled=true;
                    headbuttoon.style.display='none';
                    detaildiv.style.display='';
                    
                    addDetailForm();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function getbobot2()
{
    volumetotal=parseFloat(document.getElementById('volumetotal_x_y').innerHTML);
    volumebjln=parseFloat(document.getElementById('volumebjln_x_y').innerHTML);
    volumex=parseFloat(document.getElementById('volume_x_y').value);
    volumelalu=parseFloat(document.getElementById('volumelalu_x_y').innerHTML);

    bobottotal=parseFloat(document.getElementById('bobottotal_x_y').innerHTML);
    bobotbjln=parseFloat(document.getElementById('bobotbjln_x_y').innerHTML);
    bobotx=parseFloat(document.getElementById('bobot_x_y').value);
    bobotlalu=parseFloat(document.getElementById('bobotlalu_x_y').innerHTML);

    volumebjlnnow=volumebjln-volumelalu;
    bobotbjlnnow=bobotbjln-bobotlalu;

    totvol=volumebjlnnow+volumex;
    sisavol=volumetotal-volumebjlnnow;

    bobotx=(volumex/volumetotal)*bobottotal;

    if(totvol>volumetotal)
    {
        alert("sisa volume hanya : "+sisavol);
        document.getElementById('volume_x_y').value=0;
        document.getElementById('bobot_x_y').value=0;
    }
    else if(volumex=='')
    {
        document.getElementById('volume_x_y').value=0;
        document.getElementById('bobot_x_y').value=0;
    }
    else
    {
        document.getElementById('bobot_x_y').value=bobotx;
    }

}

function getbobot(kegiatan,num,volume,svolume,bobot,sbobot)
{
    volumex=parseFloat(document.getElementById('volume_'+kegiatan+'_'+num).value);
    //alert(volumex);
    volume=parseFloat(volume);
    svolume=parseFloat(svolume);
    bobot=parseFloat(bobot);
    sbobot=parseFloat(sbobot);
    totvol=svolume+volumex;
    sisavol=volume-svolume;
    bobotx=(volumex/volume)*bobot;
    totbobot=sbobot+bobotx;

    if(totvol>volume)
    {
        alert("sisa volume hanya : "+sisavol);
        document.getElementById('volume_'+kegiatan+'_'+num).value=0;
        document.getElementById('bobot_'+kegiatan+'_'+num).value=0;
    }
    else if(volumex=='')
    {
        document.getElementById('volume_'+kegiatan+'_'+num).value=0;
        document.getElementById('bobot_'+kegiatan+'_'+num).value=0;
    }
    else
    {
        document.getElementById('bobot_'+kegiatan+'_'+num).value=bobotx;
    }

}
function addDetailForm()
{
    kodeproject=document.getElementById('kodeproject').value;
    var param='kodeproject='+kodeproject;
    post_response_text('vhc_slave_progproject.php?proses=getDetail', param, respon);

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

function saveData(arrdat){
    var detaildiv = document.getElementById('Detail');
    var detailform = document.getElementById('detailform');
    var header = document.getElementById('header');
    var headbuttoon = document.getElementById('hbutton');
    var container = document.getElementById('container');
    var bodyList = document.getElementById('bodyList');

    var kodeorg = document.getElementById('kodeorg').value;
    var kodeproject = document.getElementById('kodeproject').value;
    var minggu = document.getElementById('minggu').value;
    var tanggalawal = document.getElementById('tanggalawal').value;
    var tanggalakhir = document.getElementById('tanggalakhir').value;

    var listtabledetail = document.getElementById('listtabledetail');
    
    var dataray='';

    var uparay='';

    arrx=arrdat.split('###');
    jlharx=arrx.length;

    /*console.log(jlharx);
    console.log(arrx);*/

    for (i=1;i<jlharx;i++)
    {
        arry=arrx[i].split('/');
        /*console.log(i);
        console.log(arry);*/

        dataray+='###'+parseInt(document.getElementById('induk_'+arry[0]+'_'+arry[1]).innerHTML);
        dataray+='/'+parseInt(arry[0]);
        dataray+='/'+parseFloat(document.getElementById('volume_'+arry[0]+'_'+arry[1]).value);
        dataray+='/'+parseFloat(document.getElementById('bobot_'+arry[0]+'_'+arry[1]).value);

        uparay+='###'+parseInt(arry[0]);
        uparay+='/'+document.getElementById('kodeproject').value;
        uparay+='/'+(parseFloat(document.getElementById('volume_'+arry[0]+'_'+arry[1]).value)+parseFloat(arry[2]));
        uparay+='/'+(parseFloat(document.getElementById('bobot_'+arry[0]+'_'+arry[1]).value)+parseFloat(arry[3]));
    }

    var param = 'dataray='+dataray+'&uparay='+uparay+'&kodeorg='+kodeorg+'&kodeproject='+kodeproject+'&minggu='+minggu+'&tanggalawal='+tanggalawal+'&tanggalakhir='+tanggalakhir;
    //console.log(param);
    post_response_text('vhc_slave_progproject.php?proses=saveData',param,respon);
    
    //alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    console.log(con.responseText);
                    header.style.display='none';
                    detaildiv.style.display='none';
                    container.style.display='';
                    listtabledetail.style.display='';
                    headbuttoon.style.display='';
                    //alert(con.responseText);
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function edit(num){
    var detaildiv = document.getElementById('Detail');
    var listtabledetail = document.getElementById('listtabledetail');
    var container = document.getElementById('container');
    var header = document.getElementById('header');
    var headbuttoon = document.getElementById('hbutton');

    var rowkode = document.getElementById('kode_'+num).innerHTML;
    var rowkodeorg = document.getElementById('kodeorg_'+num).innerHTML;
    var rowkodeproject = document.getElementById('kodeproject_'+num).innerHTML;
    var rowminggu = document.getElementById('mingguke_'+num).innerHTML;
    var rowtanggalawal = document.getElementById('tanggalawal_'+num).innerHTML;
    var rowtanggalakhir = document.getElementById('tanggalakhir_'+num).innerHTML;

    var kode = document.getElementById('kode');
    var kodeorg = document.getElementById('kodeorg');
    var kodeproject = document.getElementById('kodeproject');
    var minggu = document.getElementById('minggu');
    var tanggalawal = document.getElementById('tanggalawal');
    var tanggalakhir = document.getElementById('tanggalakhir');

    header.style.display='';
    container.style.display='none';
    
    kode.value = rowkode;
    kodeorg.value = rowkodeorg;
    //kodeproject.value = rowkodeproject;
    minggu.value = rowminggu;
    tanggalawal.value = rowtanggalawal;
    tanggalakhir.value = rowtanggalakhir;

    kode.disabled=true;
    kodeorg.disabled=true;
    kodeproject.disabled=true;
    minggu.disabled=true;
    tanggalawal.disabled=true;
    tanggalakhir.disabled=true;

    headbuttoon.style.display='none';
    detaildiv.style.display='block';
    listtabledetail.style.display='block';

    editform(rowkode,rowkodeorg,rowkodeproject,rowminggu,rowtanggalawal,rowtanggalakhir);
    

}

function deletehd(num) {
    var rowkode = document.getElementById('kode_'+num).innerHTML;
    var rowkodeproject = document.getElementById('kodeproject_'+num).innerHTML;
    
    var param='kode='+rowkode+'&kodeproject='+rowkodeproject;
    if(confirm('Are you sure..?')){
            post_response_text('vhc_slave_progproject.php?proses=deletehd', param, respon);
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

function editform(kode,kodeorg,kodeproject,minggu,tanggalawal,tanggalakhir){
    var detailform = document.getElementById('detailform');

    var param='kode='+kode+'&kodeorg='+kodeorg+'&kodeproject='+kodeproject+'&minggu='+minggu+'&tanggalawal='+tanggalawal+'&tanggalakhir='+tanggalakhir;

    post_response_text('vhc_slave_progproject.php?proses=getEditDetail', param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    detailform.innerHTML=con.responseText;
                    loadDataDetail(kode,kodeorg,kodeproject,minggu,tanggalawal,tanggalakhir);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function editDetail(arrdat){
   
    dataray = arrdat.split('###');
    buttons=document.getElementById('updateDetailButton');
    rowid= document.getElementById('id_'+dataray[0]+'_'+dataray[1]).innerHTML;
    rowinduk= document.getElementById('induk_'+dataray[0]+'_'+dataray[1]).innerHTML;
    rowkegiatan= document.getElementById('kegiatan_'+dataray[0]+'_'+dataray[1]).innerHTML;
    rownmkegiatan= document.getElementById('nmkegiatan_'+dataray[0]+'_'+dataray[1]).innerHTML;
    rowsatuan= document.getElementById('satuan_'+dataray[0]+'_'+dataray[1]).innerHTML;
    rowvolume= document.getElementById('volume_'+dataray[0]+'_'+dataray[1]).innerHTML;
    rowbobot= document.getElementById('bobot_'+dataray[0]+'_'+dataray[1]).innerHTML;
    rowvolumebbjln=parseFloat(dataray[2]);
    rowbobotbjln=parseFloat(dataray[3]);
    rowvolumetot=parseFloat(dataray[4]);
    rowbobottot=parseFloat(dataray[5]);

    document.getElementById('id_x_y').innerHTML=rowid;
    document.getElementById('induk_x_y').innerHTML=rowinduk;
    document.getElementById('kegiatan_x_y').innerHTML=rowkegiatan;
    document.getElementById('nmkegiatan_x_y').innerHTML=rownmkegiatan;
    document.getElementById('satuan_x_y').innerHTML=rowsatuan;
    document.getElementById('volume_x_y').value=rowvolume;
    document.getElementById('bobot_x_y').value=rowbobot;
    document.getElementById('volumebjln_x_y').innerHTML=rowvolumebbjln;
    document.getElementById('bobotbjln_x_y').innerHTML=rowbobotbjln;
    document.getElementById('volumetotal_x_y').innerHTML=rowvolumetot;
    document.getElementById('bobottotal_x_y').innerHTML=rowbobottot;
    document.getElementById('volumelalu_x_y').innerHTML=rowvolume;
    document.getElementById('bobotlalu_x_y').innerHTML=rowbobot;

    buttons.removeAttribute('onclick');

    buttons.setAttribute('onclick','updateData()');
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
    post_response_text('vhc_slave_progproject.php?proses=DelData', param, respon);
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
        post_response_text('vhc_slave_progproject.php?proses=saveDataDetail', param, respon);
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

function dataKeExcel(ev,tujuan,coloumn){
    judul='Report Ms.Excel';    
    param='coloumn='+coloumn+'&proses=excel';
    //alert(param);
    printFile(param,tujuan,judul,ev)    
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   showDialog1(title,content,width,height,ev);  
}

function postIni(num)
{
    var rowkode = document.getElementById('kode_'+num).innerHTML;
    var rowkodeorg = document.getElementById('kodeorg_'+num).innerHTML;
    var rowkodeproject = document.getElementById('kodeproject_'+num).innerHTML;
    var rowminggu = document.getElementById('mingguke_'+num).innerHTML;
    var rowtanggalawal = document.getElementById('tanggalawal_'+num).innerHTML;
    var rowtanggalakhir = document.getElementById('tanggalakhir_'+num).innerHTML;

    param='kode='+rowkode+'&kodeorg='+rowkodeorg+'&kodeproject='+rowkodeproject;

    if(confirm('Apakah anda yakin akan posting data ini..?')){
            post_response_text('vhc_slave_progproject.php?proses=posting', param, respon);
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
function clearHeader()
{
    document.getElementById('kodeorg').value='';
    document.getElementById('kodeproject').innerHTML='';
    document.getElementById('minggu').value='';
    document.getElementById('tanggalawal').value='';
    document.getElementById('tanggalakhir').value='';
}

function updateData(){
    var buttons = document.getElementById('updateDetailButton');

    var id = document.getElementById('id_x_y').innerHTML;
    var induk = document.getElementById('induk_x_y').innerHTML;
    var kodeproject = document.getElementById('kodeproject').value;
    var kegiatan = document.getElementById('kegiatan_x_y').innerHTML;
    var volumetotal = parseFloat(document.getElementById('volumetotal_x_y').innerHTML);
    var bobottotal = parseFloat(document.getElementById('bobottotal_x_y').innerHTML);
    var volumesebelum = parseFloat(document.getElementById('volumelalu_x_y').innerHTML);
    var bobotsebeleum = parseFloat(document.getElementById('bobotlalu_x_y').innerHTML);
    var volumebjln = parseFloat(document.getElementById('volumebjln_x_y').innerHTML);
    var bobotbjln = parseFloat(document.getElementById('bobotbjln_x_y').innerHTML);
    var volumex = parseFloat(document.getElementById('volume_x_y').value);
    var bobotx = parseFloat(document.getElementById('bobot_x_y').value);

    volumebjlnnow=volumebjln-volumesebelum;
    bobotbjlnnow=bobotbjln-bobotsebeleum;

    totvol=volumebjlnnow+volumex;
    sisavol=volumetotal-volumebjlnnow;

    bobotx=(volumex/volumetotal)*bobottotal;
    totbobot=bobotbjlnnow+bobotx;

    var param ='id='+id;
    param +='&induk='+induk+'&volumex='+volumex+'&bobotx='+bobotx;
    param +='&kegiatan='+kegiatan+'&kodeproject='+kodeproject;
    param +='&bobotbjlnnow='+totbobot+'&volumebjlnnow='+totvol;
        //alert(param);
    post_response_text('vhc_slave_progproject.php?proses=updateData', param, respon);
   

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    loadDataDetail(induk,'',kodeproject,'','','');

                    buttons.removeAttribute('onclick');

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function loadDataDetail(kode,kodeorg,kodeproject,minggu,tanggalawal,tanggalakhir){
    var tabledetail = document.getElementById('tabledetail');
    

    var param='kode='+kode+'&kodeorg='+kodeorg+'&kodeproject='+kodeproject+'&minggu='+minggu+'&tanggalawal='+tanggalawal+'&tanggalakhir='+tanggalakhir;
    //alert(param);
    post_response_text('vhc_slave_progproject.php?proses=loadDataDetail', param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    tabledetail.innerHTML=con.responseText;
                    getproject(kodeproject);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}