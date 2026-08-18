function updtjam(){
    
    jamawal=document.getElementById('jamawal').value;
    
    param ="jamawal="+jamawal;
    //alert(param);
    post_response_text('sdm_slave_5shiftsecurity.php?proses=updtjam', param, respog);
    
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
                                        document.getElementById('jamakhir').value=con.responseText;
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
        }   
    }   
}
function addData() {
    var kodeshift = document.getElementById('kodeshift');
    var namashift = document.getElementById('namashift');
    var jamawal = document.getElementById('jamawal');
    var jamakhir = document.getElementById('jamakhir');   
    
    var bodyList = document.getElementById('bodyList');
    
    // Empty = Not Valid
    if(namashift=='' || jamawal==''|| jamakhir=='') {
        alert('Every Field is obligatory');
      //  exit;
    }else{
        var param = "kodeshift="+kodeshift.value;
        param += "&namashift="+namashift.options[namashift.selectedIndex].value;
        param += "&jamawal="+jamawal.value;
        param += "&jamakhir="+jamakhir.value;
        param += "&createdby=''&createdtime=''";
        post_response_text('sdm_slave_5shiftsecurity.php?proses=add', param, respon);
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
                    kodeshift = con.responseText;
                    // Prep row
                    
                    var theRow = "<tr id='tr_"+numRow+"' class='rowcontent'>";
                    theRow += "<td  id='kodeshift_"+numRow+"'>"+kodeshift+"</td>";
                    theRow += "<td  id='namashift_"+numRow+"'>"+namashift.options[namashift.selectedIndex].text+"</td>";
                    theRow += "<td  id='jamawal_"+numRow+"'>"+jamawal.value+"</td>";
                    theRow += "<td  id='jamakhir_"+numRow+"'>"+jamakhir.value+"</td>";
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
     
    var kodeshift = document.getElementById('kodeshift');
    var namashift = document.getElementById('namashift');
    var jamawal = document.getElementById('jamawal');
    var jamakhir = document.getElementById('jamakhir');

    kodeshift.value="";
    namashift.value="";
    jamawal.value="00:00";
    jamakhir.value="00:00";
}


function edit(num)
{
    var rowkodeshift = document.getElementById('kodeshift_'+num);
    var rownamashift = document.getElementById('namashift_'+num);
    var rowjamawal = document.getElementById('jamawal_'+num);
    var rowjamakhir = document.getElementById('jamakhir_'+num);

    var kodeshift = document.getElementById('kodeshift');
    var namashift = document.getElementById('namashift');
    var jamawal = document.getElementById('jamawal');
    var jamakhir = document.getElementById('jamakhir');
    var saveBtn = document.getElementById('saveButton');
    
    // Pass Value
    kodeshift.value=rowkodeshift.innerHTML;
    namashift.value=rownamashift.innerHTML.substr(0,1);
    jamawal.value=rowjamawal.innerHTML;
    jamakhir.value=rowjamakhir.innerHTML;

    // Disable attr
    // Remove attr
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    saveBtn.setAttribute('onclick','editData('+num+')');
}

function editData(numRow) {
   var kodeshift = document.getElementById('kodeshift');
    var namashift = document.getElementById('namashift');
    var jamawal = document.getElementById('jamawal');
    var jamakhir = document.getElementById('jamakhir');
    var saveBtn = document.getElementById('saveButton');
    // Empty = Not Valid
   if(namashift=='' || jamawal==''|| jamakhir=='') {
        alert('Every Field is obligatory');
      //  exit;
    }else{
        var param = "kodeshift="+kodeshift.value;
        param += "&namashift="+namashift.value;
        param += "&jamawal="+jamawal.value;
        param += "&jamakhir="+jamakhir.value;
        param += "&updateby=''&updatetime=''";
        
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        eval("var res = "+con.responseText);
                        document.getElementById('kodeshift_'+numRow).innerHTML = res.kodeshift;
                        document.getElementById('namashift_'+numRow).innerHTML = res.namashift;
                        document.getElementById('jamawal_'+numRow).innerHTML = res.jamawal;
                        document.getElementById('jamakhir_'+numRow).innerHTML = res.jamakhir;
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
        
        post_response_text('sdm_slave_5shiftsecurity.php?proses=edit', param, respon);
    } 
}