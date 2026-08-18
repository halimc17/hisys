function addData() { 
    var kode = document.getElementById('kode');
    var jenis = document.getElementById('jenis');
    var status = document.getElementById('status');
    var bodyList = document.getElementById('bodyList');
    var statusval='';
    if(status.checked==true)
    {
        statusval=1;
        status='Aktif';
    }
    else
    {
        statusval=0;
        status='Tidak Aktif';
    }

    var jenisval = jenis.options[jenis.selectedIndex].value;
    // Empty = Not Valid
    if(jenisval=='' ) {
        alert('jenis cheklist is obligatory');
      //  exit;
    }else{
        var param = "kode="+kode.value;
        param += "&jenis="+jenisval;
        param += "&status="+statusval;
        param += "&createdby=''";
        param += "&createdtime=''";
        post_response_text('lgl_slave_5checklist.php?proses=add', param, respon);
    } 

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    
                    // Tambah Row Header
                    var numRow = 0;
                    while(document.getElementById('tr_'+numRow)) {
                        numRow++;
                    }
                    kode=con.responseText;
                    // Prep row
                    var theRow = "<tr id='tr_"+numRow+"' class='rowcontent'>";
                    theRow += "<td  id='kode_"+numRow+"'>"+kode+"</td>";
                    theRow += "<td  id='jenis_"+numRow+"'>"+jenis.options[jenis.selectedIndex].text+"</td>";
                    theRow += "<td  id='status_"+numRow+"'>"+status+"</td>";
                    theRow += "<td id='edit_"+numRow+"'>";
                    theRow += "<img src='images/application/application_edit.png' ";
                    theRow += "class=resicon  title='Edit' onclick='edit("+numRow+")'></td>";   
                    theRow += "<td id='adddetail_"+numRow+"'>";
                    theRow += "<img src=images/plus.png ";
                    theRow += "class=resicon  title='Add Detail ' onclick='addDetail("+numRow+",event)'></td>";
                    theRow += "<td id='previewDetail_"+numRow+"'>";
                    theRow += "<img src=images/zoom.png ";
                    theRow += "class=resicon  title='Detail ' onclick='previewDetail("+numRow+",event)'></td>";
                    theRow += "</tr>";
                    
                    // Insert Row
                    bodyList.innerHTML += theRow;
                    clearData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function clearData()
{
     var kode = document.getElementById('kode');
    var  jenis = document.getElementById('jenis');
    var  status = document.getElementById('status');
    var saveBtn = document.getElementById('saveButton');
    // Remove attr
    saveBtn.removeAttribute('onclick');
                        
                        // Set Attr
    saveBtn.setAttribute('onclick','addData()');

    kode.value="0";
    jenis.value="";
    status.checked=true;
}

function edit(num)
{
    var rowkode = document.getElementById('kode_'+num);
    var rowjenis = document.getElementById('jenis_'+num);
    var rowstatus = document.getElementById('status_'+num);

    var kode = document.getElementById('kode');
    var  jenis = document.getElementById('jenis');
    var  status = document.getElementById('status');
    var saveBtn = document.getElementById('saveButton');
    //alert(num);
    // Pass Value
    kode.value = rowkode.innerHTML;
    jenis.value = rowjenis.innerHTML;
    if(rowstatus.innerHTML == 'Aktif')
    {
        status.checked=true;
    }
    else
    {
        status.checked=false;
    }
    

    
    // Disable attr
    //kodepajak.setAttribute('disabled','disabled');
    // Remove attr
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    saveBtn.setAttribute('onclick','editData('+num+')');
}

function editData(numRow) {
    var kode = document.getElementById('kode');
    var  jenis = document.getElementById('jenis');
    var  status = document.getElementById('status');
    var saveBtn = document.getElementById('saveButton');

    if(status.checked==true)
    {
        statusval=1;
        status='Aktif';
    }
    else
    {
        statusval=0;
        status='Tidak Aktif';
    }
    var jenisval = jenis.options[jenis.selectedIndex].value;
    // Empty = Not Valid
    if(jenisval=='' ) {
        alert('jenis cheklist is obligatory');
      //  exit;
    }else{
        var param = "kode="+kode.value;
        param += "&jenis="+jenisval;
        param += "&status="+statusval;
        param += "&updateby=''";
        param += "&updatetime=''";
        
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        eval("var res = "+con.responseText);

                        document.getElementById('kode_'+numRow).innerHTML = kode.value;
                        document.getElementById('jenis_'+numRow).innerHTML = res.jenis;
                        document.getElementById('status_'+numRow).innerHTML = status;
                        clearData();

                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        
        post_response_text('lgl_slave_5checklist.php?proses=edit', param, respon);
    } 
}

function showDetail(title,ev)
{
    width='';
    height='';
    content="<fieldset><div id='contDetail' style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
    showDialog1(title,content,width,height,ev);

    var dialog = document.getElementById('dynamic1');
    clientWidth = document.getElementById("dynamic1").clientWidth;
    clientHeight = document.getElementById("dynamic1").clientHeight;
    pos = new Array();
    pos = getMouseP(ev);
    
    if((pos[1] + clientWidth) >= 600){
        dialog.style.top = (pos[1]-(clientWidth+10)) + 'px';
    }else{
        dialog.style.top = pos[1] + 'px';
    }
    // if((pos[0] - clientHeight) < 0){
        // dialog.style.left = (pos[0]) +'px';
    // }else{
        // dialog.style.left = (pos[0] - (clientHeight+100)) +'px';
    // }
    
    dialog.style.top = pos[1]+'px';
    // dialog.style.left = (pos[0]-400)+'px';
}

function addDetail(num,ev)
{
    var rowkode = document.getElementById('kode_'+num);
    var rowjenis = document.getElementById('jenis_'+num);

    showDetail(rowjenis.innerHTML,ev);
    param='kodeheader='+rowkode.innerHTML;
    tujuan='lgl_slave_5checklist.php?proses=addDetail';
   // alert(param);
    post_response_text(tujuan, param, respog);
    
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
                    //alert(con.responseText);
                    document.getElementById('contDetail').innerHTML=con.responseText;
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

function addDetail2(num,ev,no,kodeval)
{
    var rowkodeheader = document.getElementById('kodeheader'+no+'_'+num);
    var rowdeskripsi = document.getElementById('deskripsi'+no+'_'+num);
    var rowdeskripsi = document.getElementById('tipe'+no+'_'+num);
    var param;


    showDetail(rowdeskripsi.innerHTML,ev);
    param='kode='+kodeval;
    param+='&kodeheader='+rowkodeheader.innerHTML;
    tujuan='lgl_slave_5checklist.php?proses=addDetail2';
    //alert(param);
    post_response_text(tujuan, param, respog);
    
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
                    //alert(con.responseText);
                    document.getElementById('contDetail').innerHTML=con.responseText;
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

function addDetail3(num,ev,no,kodeval)
{
    var rowkodeheader = document.getElementById('kodeheader'+no+'_'+num);
    var rowdeskripsi = document.getElementById('deskripsi'+no+'_'+num);
    var param;


    showDetail(rowdeskripsi.innerHTML,ev);
    param='kode='+kodeval;
    param+='&kodeheader='+rowkodeheader.innerHTML;
    tujuan='lgl_slave_5checklist.php?proses=addDetail3';
    //alert(param);
    post_response_text(tujuan, param, respog);
    
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
                    //alert(con.responseText);
                    document.getElementById('contDetail').innerHTML=con.responseText;
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

function addDataDetail(num) { 
    //alert(num);
    var kode;
    var nourut = document.getElementById('nourut'+num);
    var noinduk = document.getElementById('noinduk'+num);
    var kodeheader = document.getElementById('kodeheader'+num);
    var deskripsi = document.getElementById('deskripsi'+num);
    var tipe = document.getElementById('tipe'+num);
    var bodyList = document.getElementById('bodyListdetail'+num);
    var noe;
    noe=num;
    if(num == 0){noe=2;}
    if(num == 2){noe=3;}
    if(num == 3){noe=2;}
   
    // Empty = Not Valid
    if(deskripsi=='' ) {
        alert('deskripsi is obligatory');
      //  exit;
    }else{
        var param = "kode="+kode;
        param += "&nourut="+nourut.value;
        param += "&noinduk="+noinduk.value;
        param += "&kodeheader="+kodeheader.value;
        param += "&deskripsi="+deskripsi.value;
        if(tipe.checked==true)
        {
            tipe='Panduan';
            param += "&tipe=1";
        }
        else
        {
            tipe='Pertanyaan';
            param += "&tipe=0";
        }
        //alert(param);
        post_response_text('lgl_slave_5checklist.php?proses=addDataDetail', param, respon);
    } 

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    
                    // Tambah Row Header
                    var numRow = 0;
                    while(document.getElementById('tr_'+numRow)) {
                        numRow++;
                    }
                    console.log(con.responseText);
                    kode=con.responseText;
                    //Prep row
                    var theRow = "<tr id='tr_"+numRow+"' class='rowcontent'>";
                    theRow += "<td  id='kodeheader"+num+"_"+numRow+"'>"+kodeheader.value+"</td>";
                    theRow += "<td  id='noinduk"+num+"_"+numRow+"'>"+noinduk.value+"</td>";
                    theRow += "<td  id='nourut"+num+"_"+numRow+"'>"+nourut.value+"</td>";
                    theRow += "<td  id='deskripsi"+num+"_"+numRow+"'>"+deskripsi.value+"</td>";
                    theRow += "<td  id='tipe"+num+"_"+numRow+"'>"+tipe+"</td>";
                    theRow += "<td id='edit_"+numRow+"'>";
                    theRow += "<img src='images/application/application_edit.png' ";
                    theRow += "class=resicon  title='Edit' onclick='editdetail("+numRow+","+num+","+kode+")'></td>";   
                    theRow += "<td id='adddetail_"+numRow+"'>";
                    theRow += "<img src=images/plus.png ";
                    theRow += "class=resicon  title='Add Detail ' onclick='addDetail"+noe+"("+numRow+",event,"+num+","+kode+")'></td>";
                    theRow += "</tr>";
                    
                    // Insert Row
                    bodyList.innerHTML += theRow;
                    clearDataDetail(num);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function clearDataDetail(num)
{
    var nourut = document.getElementById('nourut'+num);
    var noinduk = document.getElementById('noinduk'+num);
    var kodeheader = document.getElementById('kodeheader'+num);
    var deskripsi = document.getElementById('deskripsi'+num);
    var tipe = document.getElementById('tipe'+num);
    var saveBtn = document.getElementById('saveButton'+num);
    // Remove attr
    saveBtn.removeAttribute('onclick');
                        
                        // Set Attr
    saveBtn.setAttribute('onclick','addDataDetail('+num+')');

    nourut.value="0";
    deskripsi.value="";
}

function editdetail(num,no,kode)
{
    var rownourut = document.getElementById('nourut'+no+'_'+num);
    var rownoinduk = document.getElementById('noinduk'+no+'_'+num);
    var rowkodeheader = document.getElementById('kodeheader'+no+'_'+num);
    var rowdeskripsi = document.getElementById('deskripsi'+no+'_'+num);
    var rowtipe = document.getElementById('tipe'+no+'_'+num);

    var nourut = document.getElementById('nourut'+no);
    var noinduk = document.getElementById('noinduk'+no);
    var kodeheader = document.getElementById('kodeheader'+no);
    var deskripsi = document.getElementById('deskripsi'+no);
    var tipe = document.getElementById('tipe'+no);
    var saveBtn = document.getElementById('saveButton'+no);
    //alert(num);
    // Pass Value
    nourut.value = rownourut.innerHTML;
    noinduk.value = rownoinduk.innerHTML;
    kodeheader.value = rowkodeheader.innerHTML;
    deskripsi.value = rowdeskripsi.innerHTML.replace("###","\n");
    if(rowtipe.innerHTML=='Panduan')
    {
        tipe.checked=true;
    }
    else
    {
        tipe.checked=false;
    }

    // Remove attr
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    saveBtn.setAttribute('onclick','editDataDetail('+num+','+no+','+kode+')');
    //alert(kode);
}

function editDataDetail(numRow,no,kode) {
    var nourut = document.getElementById('nourut'+no);
    var noinduk = document.getElementById('noinduk'+no);
    var kodeheader = document.getElementById('kodeheader'+no);
    var deskripsi = document.getElementById('deskripsi'+no);
    var tipe = document.getElementById('tipe'+no);
    var saveBtn = document.getElementById('saveButton'+no);

    
    // Empty = Not Valid
    if(deskripsi=='' ) {
        alert('deskripsi  is obligatory');
      //  exit;
    }else{
        var param ="kode="+kode;
        param += "&nourut="+nourut.value;
        param += "&noinduk="+noinduk.value;
        param += "&kodeheader="+kodeheader.value;
        param += "&deskripsi="+deskripsi.value;
        if(tipe.checked==true)
        {
            tipe='Panduan';
            param += "&tipe=1";
        }
        else
        {
            tipe='Pertanyaan';
            param += "&tipe=0";
        }
        
        //alert(param);
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        console.log(con.responseText);
                        document.getElementById('nourut'+no+'_'+numRow).innerHTML = nourut.value;
                        document.getElementById('noinduk'+no+'_'+numRow).innerHTML = noinduk.value;
                        document.getElementById('kodeheader'+no+'_'+numRow).innerHTML = kodeheader.value;
                        document.getElementById('deskripsi'+no+'_'+numRow).innerHTML = deskripsi.value;
                        document.getElementById('tipe'+no+'_'+numRow).innerHTML = tipe;
                        clearDataDetail(no);

                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        
        post_response_text('lgl_slave_5checklist.php?proses=editdetail', param, respon);
    } 
}

function previewDetail(num,ev)
{
    var rowkode = document.getElementById('kode_'+num);
    var rowjenis = document.getElementById('jenis_'+num);
    var param = "kodeheader="+rowkode.innerHTML;
    showDetail(rowjenis.innerHTML,ev);
    tujuan='lgl_slave_5checklist.php?proses=getdetail';
    post_response_text(tujuan, param, respog);
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
                   // alert(con.responseText);
                    document.getElementById('contDetail').innerHTML=con.responseText;
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