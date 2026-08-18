function dataKeExcel(ev,tujuan,notransaksi){
    judul='Report Ms.Excel';    
    param='notransaksi='+notransaksi+'&proses=excel';
    printFile(param,tujuan,judul,ev)    
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   showDialog1(title,content,width,height,ev);  
}

function caridata()
{
    var container = document.getElementById('container');
    var containerForm = document.getElementById('containerForm');
    var kodeorgcr = document.getElementById('kodeorgcr');
    var param='kodeorg='+kodeorgcr.value;
    //alert(param);
    post_response_text('pad_slave_survey.php?proses=loadDataCari',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    container.style.display="block";
                    containerForm.style.display="none";
                    container.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData()
{
    var container = document.getElementById('container');
    var containerForm = document.getElementById('containerForm');
    var param;
   
    post_response_text('pad_slave_survey.php?proses=loadData',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    container.style.display="block";
                    containerForm.style.display="none";
                    container.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletehd(num)
{
    var rownotransaksi = document.getElementById('notransaksi_'+num).innerHTML;
    var param = 'notransaksi='+rownotransaksi;
    if(confirm('Are you sure..?')){
    post_response_text('pad_slave_survey.php?proses=deletehd',param,respon);
    }
    function respon()
    {
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

function edit(num)
{
    var containerForm = document.getElementById('containerForm');
    var container = document.getElementById('container');
    var surveyforms = document.getElementById('surveyforms');
    var rownotransaksi = document.getElementById('notransaksi_'+num).innerHTML;
    var param = 'notransaksi='+rownotransaksi;

    post_response_text('pad_slave_survey.php?proses=loadDataEdit',param,respon);

    function respon()
    {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    container.style.display="none";
                    containerForm.style.display='block';
                    surveyforms.innerHTML=con.responseText;

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
    post_response_text('pad_slave_survey.php?proses=loadData',param,respon);
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

function tambahRincian()
{
    var nom = 1;
    while(document.getElementById('headlist2_'+nom)) {
    nom++;
    }

    var bodyList = document.getElementById('bodyList1');
    var numRow = 1;
    while(document.getElementById('rincian1_'+numRow)) {
    numRow++;
    }

    var theRow = "<tr id='rincian1_"+numRow+"'><td>&nbsp;&nbsp;"+numRow+".<input type='hidden' id='id_"+numRow+"'/><input type='text' class='optjawaban' id='rincianval_"+numRow+"' /></td>";
        if(nom==1)
        {
            theRow +="<td>&nbsp;&nbsp;Ket.<input type='text' class='optjawaban' id='ketval_"+numRow+"' /></td>";
        }
        else
        {
            theRow +="<td>&nbsp;&nbsp;Ket.<input type='text' class='optjawaban' id='ketval_"+numRow+"' disabled/></td>";
        }
        theRow +="<td id='delcon_"+numRow+"'><a id='delete_"+numRow+"'><img src=images/delete.png ";
            theRow +="class=resicon  title='Delete ' onclick='deleteRincian("+numRow+")'></a></td></tr>";

    bodyList.innerHTML += theRow;
    if(numRow >1)
    {
        var thedel = document.getElementById('delete_'+(numRow-1));
        thedel.parentNode.removeChild(thedel);
    }
}

function deleteRincian(num)
{
    if(document.getElementById('id_'+num))
    {
        var id = document.getElementById('id_'+num);
        deletedatarincian(id.value);
    }
    var theRow = document.getElementById('rincian1_'+num);
    theRow.parentNode.removeChild(theRow);
    if(num>1){
        document.getElementById('delcon_'+(num-1)).innerHTML ="<a id='delete_"+(num-1)+"'><img src=images/delete.png class=resicon  title='Delete ' onclick='deleteRincian("+(num-1)+")'></a>";
    }
    
}


function deletedatarincian(id)
{
   
    var param='id='+id;
   // alert(param);
    post_response_text('pad_slave_survey.php?proses=deletedatarincian',param,respon);

    function respon()
    {
         if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                       //alert('delete success');
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }
}

function tambahRincian1()
{
    var nom = 1;
    while(document.getElementById('ketval_'+nom)) {
    document.getElementById('ketval_'+nom).value='';
    document.getElementById('ketval_'+nom).disabled=true;
    nom++;
    }

    var submenu2 = document.getElementById('bodyList2');
    var numRow = 1;
    while(document.getElementById('headlist2_'+numRow)) {
    numRow++;
    }
    //alert(numRow);
    var theRow= "<tr id='headlist2_"+numRow+"'><td>&nbsp;&nbsp;"+numRow+".<input type='text' class='optjawaban' id='subval_"+numRow+"' /></td>";
        theRow+= "<td><div id='rinciansub_"+numRow+"'>Rincian&nbsp;:<img src=images/plus.png ";
        theRow+= "class=resicon  title='Add Detail ' onclick='tambahSubRincian("+numRow+")'></div></td><td>";

    submenu2.innerHTML += theRow;
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

function showupload(ev, notransaksi) {
    showformupload(ev);
    param = "";
    param += "notransaksi=" + notransaksi;
    param += '&proses=showupload';
    tujuan = 'pad_slave_survey.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadfiles(notransaksi) {
    param = 'proses=loadfiles&notransaksi=' + notransaksi;
    tujuan = 'pad_slave_survey.php';
    post_response_text(tujuan, param, respog);
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
    param = "proses=deletefile";
    param += "&notransaksi=" + notransaksi;
    param += "&namafile=" + namafile;
    tujuan = 'pad_slave_survey.php';
    post_response_text(tujuan, param, respog);
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
    formdata.append("fileupload", getValue('upload'));
    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "pad_slave_survey.php?proses=submitfile", true);
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


function tambahSubRincian(num)
{
    var rinciansub = document.getElementById('rinciansub_'+num);
    var numRow = 1;
    while(document.getElementById('rincian_'+numRow+'_subval_'+num)) {
    numRow++;
    }

    var theRow = "<table id='rincian_"+numRow+"_subval_"+num+"'><tr><td>&nbsp;&nbsp;"+numRow+".<input type='hidden' id='id_"+numRow+"_subval_"+num+"'/><input type='text' class='optjawaban' id='rincianval_"+numRow+"_subval_"+num+"' /></td>";
        theRow +="<td>&nbsp;&nbsp;Ket.<input type='text' class='optjawaban' id='ketval_"+numRow+"_subval_"+num+"' /></td>";
        theRow +="<td id='delcon_"+numRow+"_subval_"+num+"'><a id='delete_"+numRow+"_subval_"+num+"'><img src=images/delete.png ";
        theRow +="class=resicon  title='Delete ' onclick='deleteSubRincian("+numRow+","+num+")'></a></td></tr></table>";

    rinciansub.innerHTML += theRow;
    if(numRow > 1)
    {
        var thedel = document.getElementById('delete_'+(numRow-1)+'_subval_'+num);
        thedel.parentNode.removeChild(thedel);
    }
}

function deleteSubRincian(numRow,num)
{
    
    if(numRow==1)
    {
        var noc = 1;
        while(document.getElementById('headlist2_'+noc)) {
        noc++;
        }
        if(num==(noc-1))
        {
            if(document.getElementById('id_'+numRow+'_subval_'+num))
            {
                var id = document.getElementById('id_'+numRow+'_subval_'+num);
                deletedatasubrincian(id.value);
            }
            var thead = document.getElementById('headlist2_'+num);
            thead.parentNode.removeChild(thead);
            
        }
    }
    
    if(numRow>1)
    {
        if(document.getElementById('id_'+numRow+'_subval_'+num))
        {
            var id = document.getElementById('id_'+numRow+'_subval_'+num);
            deletedatasubrincian(id.value);
        }
        var theRow = document.getElementById('rincian_'+numRow+'_subval_'+num);
        theRow.parentNode.removeChild(theRow);
        document.getElementById('delcon_'+(numRow-1)+'_subval_'+num).innerHTML ="<a id='delete_"+(numRow-1)+"_subval_"+num+"'><img src=images/delete.png class=resicon  title='Delete ' onclick='deleteSubRincian("+(numRow-1)+","+num+")'></a>";
        
    }
}

function deletedatasubrincian(id)
{
   
    var param='id='+id;
    //alert(param);
    post_response_text('pad_slave_survey.php?proses=deletedatasubrincian',param,respon);

    function respon()
    {
         if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                       //alert('delete success');
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
    var containerForm = document.getElementById('containerForm');
    var container = document.getElementById('container');
    var surveyforms = document.getElementById('surveyforms');
    var param;
    //alert(param);
    post_response_text('pad_slave_survey.php?proses=adddataform',param,respon);

    function respon()
    {
         if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                        container.style.display="none";
                        containerForm.style.display='block';
                        surveyforms.innerHTML = con.responseText;
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }
}

function addData()
{
    var notransaksi= document.getElementById('notransaksi');
    var kodeorg= document.getElementById('kodeorg');

    var param='notransaksi='+notransaksi.value+'&kodeorg='+kodeorg.value;
    //alert(param);
    post_response_text('pad_slave_survey.php?proses=simpanData',param,respon);

    function respon()
    {
         if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    addDataDetail1(con.responseText);
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }
}

function addDataDetail1(induk)
{
    var tipe= document.getElementById('tipe');
    var jenissurvey= document.getElementById('jenissurvey');

    var param='id=&induk='+induk+'&tipe='+tipe.value+'&jenis='+jenissurvey.value;
    //alert(param);
    post_response_text('pad_slave_survey.php?proses=simpanDataDetail1',param,respon);

    function respon()
    {
         if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    addDataDetail2(con.responseText);
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }
}

function addDataDetail2(induk)
{
    var sub1='';
    var sub2='';

    var numSub1 = 1;
    while(document.getElementById('rincian1_'+numSub1)) {
        if(numSub1==1)
        {
            sub1=document.getElementById('rincianval_'+numSub1).value;
            sub1+='/'+document.getElementById('ketval_'+numSub1).value;
        }
        else
        {
            sub1+='###'+document.getElementById('rincianval_'+numSub1).value;
            sub1+='/'+document.getElementById('ketval_'+numSub1).value;
        }
    numSub1++;
    }

    var numSub2 = 1;
    while(document.getElementById('headlist2_'+numSub2)) {
        if(numSub2==1)
        {
            var numSub21 = 1;
            while(document.getElementById('rincian_'+numSub21+'_subval_'+numSub2)) {
                if(numSub21==1)
                {   
                    sub2=document.getElementById('subval_'+numSub2).value;
                    sub2+='/'+document.getElementById('rincianval_'+numSub21+'_subval_'+numSub2).value;
                    sub2+='/'+document.getElementById('ketval_'+numSub21+'_subval_'+numSub2).value;
                }
                else
                {
                    sub2+='###'+document.getElementById('subval_'+numSub2).value;
                    sub2+='/'+document.getElementById('rincianval_'+numSub21+'_subval_'+numSub2).value;
                    sub2+='/'+document.getElementById('ketval_'+numSub21+'_subval_'+numSub2).value;
                }
                numSub21++;
            }
        }
        else
        {
            var numSub21 = 1;
            while(document.getElementById('rincian_'+numSub21+'_subval_'+numSub2)) {
                sub2+='###'+document.getElementById('subval_'+numSub2).value;
                sub2+='/'+document.getElementById('rincianval_'+numSub21+'_subval_'+numSub2).value;
                sub2+='/'+document.getElementById('ketval_'+numSub21+'_subval_'+numSub2).value;
                numSub21++;
            }
        }
    numSub2++;
    }


    var param='id=&induk='+induk+'&sub1='+sub1+'&sub2='+sub2;
    //alert(param);
    post_response_text('pad_slave_survey.php?proses=simpanDataDetail2',param,respon);

    function respon()
    {
         if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
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

function updateDataDetail1(induk)
{
    var tipe= document.getElementById('tipe');
    var jenissurvey= document.getElementById('jenissurvey');
    var id= document.getElementById('iddetail1');
    var param='id='+id.value+'&induk='+induk+'&tipe='+tipe.value+'&jenis='+jenissurvey.value;
   // alert(param);
    post_response_text('pad_slave_survey.php?proses=updateDataDetail1',param,respon);

    function respon()
    {
         if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                   // alert(con.responseText);
                    updateDataDetail2(con.responseText);
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }
}

function updateDataDetail2(induk)
{
    var sub1='';
    var sub2='';

    var numSub1 = 1;
    while(document.getElementById('rincian1_'+numSub1)) {
        if(numSub1==1)
        {
            sub1=document.getElementById('id_'+numSub1).value;
            sub1+='/'+document.getElementById('rincianval_'+numSub1).value;
            sub1+='/'+document.getElementById('ketval_'+numSub1).value;
        }
        else
        { 
            sub1+='###'+document.getElementById('id_'+numSub1).value;
            sub1+='/'+document.getElementById('rincianval_'+numSub1).value;
            sub1+='/'+document.getElementById('ketval_'+numSub1).value;
        }
    numSub1++;
    }

    var numSub2 = 1;
    while(document.getElementById('headlist2_'+numSub2)) {
        if(numSub2==1)
        {
            var numSub21 = 1;
            while(document.getElementById('rincian_'+numSub21+'_subval_'+numSub2)) {
                if(numSub21==1)
                {   
                    sub2=document.getElementById('id_'+numSub21+'_subval_'+numSub2).value;
                    sub2+='/'+document.getElementById('subval_'+numSub2).value;
                    sub2+='/'+document.getElementById('rincianval_'+numSub21+'_subval_'+numSub2).value;
                    sub2+='/'+document.getElementById('ketval_'+numSub21+'_subval_'+numSub2).value;
                }
                else
                {
                    sub2+='###'+document.getElementById('id_'+numSub21+'_subval_'+numSub2).value;
                    sub2+='/'+document.getElementById('subval_'+numSub2).value;
                    sub2+='/'+document.getElementById('rincianval_'+numSub21+'_subval_'+numSub2).value;
                    sub2+='/'+document.getElementById('ketval_'+numSub21+'_subval_'+numSub2).value;
                }
                numSub21++;
            }
        }
        else
        {
            var numSub21 = 1;
            while(document.getElementById('rincian_'+numSub21+'_subval_'+numSub2)) {
                sub2+='###'+document.getElementById('id_'+numSub21+'_subval_'+numSub2).value;
                sub2+='/'+document.getElementById('subval_'+numSub2).value;
                sub2+='/'+document.getElementById('rincianval_'+numSub21+'_subval_'+numSub2).value;
                sub2+='/'+document.getElementById('ketval_'+numSub21+'_subval_'+numSub2).value;
                numSub21++;
            }
        }
    numSub2++;
    }


    var param='induk='+induk+'&sub1='+sub1+'&sub2='+sub2;
   // alert(param);
    post_response_text('pad_slave_survey.php?proses=updateDataDetail2',param,respon);

    function respon()
    {
         if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                   // alert(con.responseText);
                    //loadData();
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }
}