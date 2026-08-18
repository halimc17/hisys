function addData() {
    var kodekategori = document.getElementById('kodekategori');
    var namakategori = document.getElementById('namakategori');
    var bodyList = document.getElementById('bodyList');
    
    // Empty = Not Valid
    if(kodekategori=='' || namakategori=='') {
        alert('kode kategori and nama kategori is obligatory');
      //  exit;
    }else{
        var param = "kodekategori="+kodekategori.value;
        param += "&namakategori="+namakategori.value;
        post_response_text('legal_slave_5kategoriijin.php?proses=add', param, respon);
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
                    
                    // Prep row
                    var theRow = "<tr id='tr_"+numRow+"' class='rowcontent'>";
                    theRow += "<td  id='kodekategori_"+numRow+"'>"+kodekategori.value+"</td>";
                    theRow += "<td  id='namakategori_"+numRow+"'>"+namakategori.value+"</td>";
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
     var kodekategori = document.getElementById('kodekategori');
    var namakategori = document.getElementById('namakategori');

    kodekategori.value="";
    namakategori.value="";
}

function edit(num)
{
    var rowkodekategori = document.getElementById('kodekategori_'+num);
    var rownamakategori = document.getElementById('namakategori_'+num);

    var kodekategori = document.getElementById('kodekategori');
    var namakategori = document.getElementById('namakategori');
    var saveBtn = document.getElementById('saveButton');
    
    // Pass Value
    kodekategori.value = rowkodekategori.innerHTML;
    namakategori.value = rownamakategori.innerHTML;

    // Disable attr
    kodekategori.setAttribute('disabled','disabled');
    // Remove attr
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    saveBtn.setAttribute('onclick','editData('+num+')');
}

function editData(numRow) {
    var kodekategori = document.getElementById('kodekategori');
    var namakategori = document.getElementById('namakategori');
    var saveBtn = document.getElementById('saveButton');
    // Empty = Not Valid
    if(kodekategori=='' || namakategori==''  ) {
        alert('kodekategori and namakategori is obligatory');
      //  exit;
    }else{
        var param = "kodekategori="+kodekategori.value;
        param += "&namakategori="+namakategori.value;
        
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        eval("var res = "+con.responseText);
                        document.getElementById('kodekategori_'+numRow).innerHTML = kodekategori.value;
                        document.getElementById('namakategori_'+numRow).innerHTML = namakategori.value;
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
        
        post_response_text('legal_slave_5kategoriijin.php?proses=edit', param, respon);
    } 
}