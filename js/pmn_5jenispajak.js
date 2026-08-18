function addData() { 
    var id = '';
    var kodepajak = document.getElementById('kodepajak');
    var namapajak = document.getElementById('namapajak');
    var bodyList = document.getElementById('bodyList');
    var kodepajakval = kodepajak.options[kodepajak.selectedIndex].value;
    // Empty = Not Valid
    if(kodepajak=='' || namapajak=='') {
        alert('kode pajak and nama nama pajak is obligatory');
      //  exit;
    }else{
        var param = "id="+id.value;
        param += "&kodepajak="+kodepajakval;
        param += "&namapajak="+namapajak.value;
        post_response_text('pmn_slave_5jenispajak.php?proses=add', param, respon);
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
                    id=con.responseText;
                    // Prep row
                    var theRow = "<tr id='tr_"+numRow+"' class='rowcontent'>";
                    theRow += "<td  id='kodepajak_"+numRow+"'>"+kodepajak.options[kodepajak.selectedIndex].text+"</td>";
                    theRow += "<td  id='namapajak_"+numRow+"'>"+namapajak.value+"</td>";
                    theRow += "<td id='edit_"+numRow+"'><img src='images/application/application_edit.png' ";
                    theRow += "class=resicon  caption='Edit' onclick='edit("+numRow+","+id+","+kodepajakval+")'></td>";
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
     var kodepajak = document.getElementById('kodepajak');
    var  namapajak = document.getElementById('namapajak');
    var saveBtn = document.getElementById('saveButton');
    // Remove attr
    saveBtn.removeAttribute('onclick');
                        
                        // Set Attr
    saveBtn.setAttribute('onclick','addData()');

    kodepajak.value="";
    namapajak.value="";
}

function edit(num,id,kodepajakval)
{
    var rowkodepajak = document.getElementById('kodepajak_'+num);
    var rownamapajak = document.getElementById('namapajak_'+num);

    var kodepajak = document.getElementById('kodepajak');
    var namapajak = document.getElementById('namapajak');
    var saveBtn = document.getElementById('saveButton');
    //alert(num);
    // Pass Value
    kodepajak.value = kodepajakval;
    namapajak.value = rownamapajak.innerHTML;
    

    
    // Disable attr
    //kodepajak.setAttribute('disabled','disabled');
    // Remove attr
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    saveBtn.setAttribute('onclick','editData('+num+','+id+')');
}

function editData(numRow,id) {
    var kodepajak = document.getElementById('kodepajak');
    var namapajak = document.getElementById('namapajak');
    var saveBtn = document.getElementById('saveButton');
    // Empty = Not Valid
    if(kodepajak=='' || namapajak==''  ) {
        alert('kodepajak and namapajak is obligatory');
      //  exit;
    }else{
        var param = "id="+id; 
        param += "&kodepajak="+kodepajak.options[kodepajak.selectedIndex].value;
        param += "&namapajak="+namapajak.value;
        
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        eval("var res = "+con.responseText);
                        document.getElementById('kodepajak_'+numRow).innerHTML = kodepajak.options[kodepajak.selectedIndex].text
                        document.getElementById('namapajak_'+numRow).innerHTML = namapajak.value;
                        document.getElementById('edit_'+numRow).innerHTML = "<img src='images/application/application_edit.png' class=resicon  caption='Edit' onclick='passEditHeader("+numRow+","+id+","+getOptionsValue(kodepajak)+")'>";
                        clearData();

                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        
        post_response_text('pmn_slave_5jenispajak.php?proses=edit', param, respon);
    } 
}