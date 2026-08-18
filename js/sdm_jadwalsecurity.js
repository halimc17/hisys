function loadData()
{
    var header = document.getElementById('header');
    var container = document.getElementById('container');
    var detaildiv = document.getElementById('Detail');
    var listtabledetail = document.getElementById('listtabledetail');
    var param;
   
    post_response_text('sdm_slave_jadwalsecurity.php?proses=loadData',param,respon);

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

function cariBast(num)
{
    var param ='page='+num;
    //alert(param);
    post_response_text('sdm_slave_jadwalsecurity.php?proses=loadData',param,respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('container').innerHTML=con.responseText;
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
   
    post_response_text('sdm_slave_jadwalsecurity.php?proses=loadCariData',param,respon);

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
                    document.getElementById('periode').disabled=false;
                    document.getElementById('pos').disabled=false;
                    document.getElementById('minggu').disabled=false;
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
    post_response_text('sdm_slave_jadwalsecurity.php?proses=plusdetail',param,respon);

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

function getWeek(num)
{
    var kodeorg = document.getElementById('kodeorg').value;
    var periode = document.getElementById('periode').value;
    var week = document.getElementById('minggu');
    var param;
    if(num==''){
         param = 'kodeorg='+kodeorg+'&periode='+periode;
    }
    else
    {
         param = 'kodeorg='+kodeorg+'&periode='+periode+'&ws='+num;   
    }
    //alert(param);
    post_response_text('sdm_slave_jadwalsecurity.php?proses=getWeek',param,respon);

        function respon(){
             if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        //alert(con.responseText);
                        week.innerHTML=con.responseText;
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
    var periode = document.getElementById('periode').value;
    var pos = document.getElementById('pos').value;
    var minggu = document.getElementById('minggu').value;

     if(kodeorg=='' || periode=='' || pos=='' || minggu=='' )
    {
        alert("All field is are obligatory");
    }
    else
    {
        var param='kodeorg='+kodeorg+'&periode='+periode+'&pos='+pos+'&minggu='+minggu;
        post_response_text('sdm_slave_jadwalsecurity.php?proses=checkHeader', param, respon);
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
                    document.getElementById('periode').disabled=true;
                    document.getElementById('pos').disabled=true;
                    document.getElementById('minggu').disabled=true;
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
function addDetailForm()
{

    var kodeorg = document.getElementById('kodeorg').value;
    var periode = document.getElementById('periode').value;
    var minggu = document.getElementById('minggu').value;

    var param='kodeorg='+kodeorg+'&periode='+periode+'&minggu='+minggu;
    post_response_text('sdm_slave_jadwalsecurity.php?proses=getDetail', param, respon);

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

function saveData(btbw,btat){
    var detaildiv = document.getElementById('Detail');
    var detailform = document.getElementById('detailform');
    var header = document.getElementById('header');
    var headbuttoon = document.getElementById('hbutton');
    var container = document.getElementById('container');
    var bodyList = document.getElementById('bodyList');

    var listtabledetail = document.getElementById('listtabledetail');
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
        //alert('karyawanid_'+i);
        if(i==1){
            karyawanid = document.getElementById('karyawanid_'+i).value;
            //alert(i);
        }
        else
        {
            karyawanid += '###'+document.getElementById('karyawanid_'+i).value;
        }
        for(j=btbw;j<=btat;j++){
            if(document.getElementById('shift_'+i+'_'+j).value=='')
            {
                error_catch=1;
            }
            if(shift==''){
                shift = document.getElementById('shift_'+i+'_'+j).value;
            }
            else{
                shift += '###'+document.getElementById('shift_'+i+'_'+j).value;
            }
                //alert(shift);
        }
    }
    if(error_catch!=0)
    {
        alert("shift is obligatory");
    }
    else{
        var param = 'kodeorg='+kodeorg+'&periode='+periode+'&pos='+pos+'&minggu='+minggu;
        param +='&karyawanid='+karyawanid+'&shift='+shift;
        param +='&batasbawah='+btbw+'&batasatas='+btat;

    post_response_text('sdm_slave_jadwalsecurity.php?proses=saveData',param,respon);
    }
    //alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

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

    var rowkodeorg = document.getElementById('kodeorg_'+num).innerHTML;
    var rowperiode = document.getElementById('periode_'+num).innerHTML;
    var rowpos = document.getElementById('pos_'+num).innerHTML;
    var rowminggu = document.getElementById('minggu_'+num).innerHTML;

    var kodeorg = document.getElementById('kodeorg');
    var periode = document.getElementById('periode');
    var pos = document.getElementById('pos');
    var minggu = document.getElementById('minggu');

    header.style.display='';
    container.style.display='none';
    kodeorg.value = rowkodeorg;
    //alert(kodeorg.value);
    periode.value = rowperiode;
    pos.value = rowpos;
    //minggu.value = rowminggu;

    kodeorg.disabled=true;
    periode.disabled=true;
    pos.disabled=true;
    minggu.disabled=true;
    headbuttoon.style.display='none';
    detaildiv.style.display='block';
    listtabledetail.style.display='block';

    editform(rowkodeorg,rowperiode,rowpos,rowminggu);
    

}

function deletehd(num) {
    var rowkodeorg = document.getElementById('kodeorg_'+num).innerHTML;
    var rowperiode = document.getElementById('periode_'+num).innerHTML;
    var rowpos = document.getElementById('pos_'+num).innerHTML;
    var rowminggu = document.getElementById('minggu_'+num).innerHTML;

    var param='kodeorg='+rowkodeorg+'&periode='+rowperiode+'&pos='+rowpos+'&minggu='+rowminggu;
    if(confirm('Are you sure..?')){
            post_response_text('sdm_slave_jadwalsecurity.php?proses=deletehd', param, respon);
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

function editform(kodeorg,periode,pos,minggu){
    var detailform = document.getElementById('detailform');

    var param='kodeorg='+kodeorg+'&periode='+periode+'&pos='+pos+'&minggu='+minggu;

    post_response_text('sdm_slave_jadwalsecurity.php?proses=getEditDetail', param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    detailform.innerHTML=con.responseText;
                    loadDataDetail(kodeorg,periode,pos,minggu);
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
    post_response_text('sdm_slave_jadwalsecurity.php?proses=DelData', param, respon);
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
        post_response_text('sdm_slave_jadwalsecurity.php?proses=saveDataDetail', param, respon);
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
        post_response_text('sdm_slave_jadwalsecurity.php?proses=updateData', param, respon);
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
    post_response_text('sdm_slave_jadwalsecurity.php?proses=loadDataDetail', param, respon);

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