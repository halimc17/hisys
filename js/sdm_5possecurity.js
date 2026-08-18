function addData() {
    var nopos = document.getElementById('nopos');
    var namapos = document.getElementById('namapos');
    var unit = document.getElementById('unit');
    var status = document.getElementById('status');
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
    
    var bodyList = document.getElementById('bodyList');
    
    // Empty = Not Valid
    if(namapos=='' || unit=='') {
        alert('No. Urut and Jenis is obligatory');
      //  exit;
    }else{
        var param = "nopos="+nopos.value;
        param += "&namapos="+namapos.value;
        param += "&unit="+unit.value;
        param += "&status="+statusval;
        param += "&createdby=''&createdtime=''&updateby=''&updatetime=''";
        post_response_text('sdm_slave_5possecurity.php?proses=add', param, respon);
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
                    nopos = con.responseText;
                    // Prep row
                    
                    var theRow = "<tr id='tr_"+numRow+"' class='rowcontent'>";
                    theRow += "<td  id='nopos_"+numRow+"'>"+nopos+"</td>";
                    theRow += "<td  id='namapos_"+numRow+"'>"+namapos.value+"</td>";
                    theRow += "<td hidden id='unit_id_"+numRow+"'>"+unit.options[unit.selectedIndex].value+"</td>";
                    theRow += "<td  id='unit_"+numRow+"'>"+unit.options[unit.selectedIndex].text+"</td>";
                    theRow += "<td  id='status_"+numRow+"'>"+status+"</td>";
                    theRow += "<td id='edit_"+numRow+"'><img src='images/application/application_edit.png' ";
                    theRow += "class=resicon  caption='Edit' onclick='edit("+numRow+")'></td>";
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
     
    var nopos = document.getElementById('nopos');
    var namapos = document.getElementById('namapos');
    var unit = document.getElementById('unit');
    var status = document.getElementById('status');

    nopos.value="0";
    namapos.value="";
    unit.value="";
    status.checked=true;
}

function edit(num)
{
    var rownourut = document.getElementById('nopos_'+num);
    var rownamapos = document.getElementById('namapos_'+num);
    var rowunit = document.getElementById('unit_id_'+num);
    var rowstatus = document.getElementById('status_'+num);

    var nopos = document.getElementById('nopos');
    var namapos = document.getElementById('namapos');
    var unit = document.getElementById('unit');
    var status = document.getElementById('status');
    var saveBtn = document.getElementById('saveButton');
    //alert(rowunit);
    // Pass Value
    nopos.value=rownourut.innerHTML;
    namapos.value=rownamapos.innerHTML;
    unit.value=rowunit.innerHTML;
    if(rowstatus.innerHTML == "Aktif")
    {
        status.checked=true;
    }
    else
    {
        status.checked=false;
    }

    // Disable attr
    // Remove attr
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    saveBtn.setAttribute('onclick','editData('+num+')');
}

function editData(numRow) {
    var nopos = document.getElementById('nopos');
    var namapos = document.getElementById('namapos');
    var unit = document.getElementById('unit');
    var status = document.getElementById('status');
    var saveBtn = document.getElementById('saveButton');
    // Empty = Not Valid
     if(namapos=='' || unit=='') {
        alert('No. Urut and Jenis is obligatory');
      //  exit;
    }else{
        var param = "nopos="+nopos.value;
        param += "&namapos="+namapos.value;
        param += "&unit="+unit.value;
        if(status.checked==true)
        {
            status = 1;
        }
        else
        {
            status =0;
        }
        param += "&status="+status;
        
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        eval("var res = "+con.responseText);
                        document.getElementById('nopos_'+numRow).innerHTML = res.nopos;
                        document.getElementById('namapos_'+numRow).innerHTML = res.namapos;
                        document.getElementById('unit_id_'+numRow).innerHTML = unit.options[unit.selectedIndex].value;
                        document.getElementById('unit_'+numRow).innerHTML = unit.options[unit.selectedIndex].text;
                        if(res.status == 0)
                        {
                            document.getElementById('status_'+numRow).innerHTML = 'Tidak Aktif';
                        }
                        else
                        {
                            document.getElementById('status_'+numRow).innerHTML = 'Aktif';
                        }
                        clearData();

                        // Remove attr
                        saveBtn.removeAttribute('onclick');
                        
                        // Set Attr
                        saveBtn.setAttribute('onclick','addData()');

                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        
        post_response_text('sdm_slave_5possecurity.php?proses=edit', param, respon);
    } 
}