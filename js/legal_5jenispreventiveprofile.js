function addData() {
    var nourut = document.getElementById('nourut');
    var jenis = document.getElementById('jenis');
    var bodyList = document.getElementById('bodyList');
    
    // Empty = Not Valid
    if(nourut=='' || jenis=='') {
        alert('No. Urut and Jenis is obligatory');
      //  exit;
    }else{
        var param = "nourut="+nourut.value;
        param += "&jenis="+jenis.value;
        post_response_text('legal_slave_5jenispreventiveprofile.php?proses=add', param, respon);
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
                    nourut = con.responseText;
                    // Prep row
                    var theRow = "<tr id='tr_"+numRow+"' class='rowcontent'>";
                    theRow += "<td  id='nourut_"+numRow+"'>"+nourut+"</td>";
                    theRow += "<td  id='jenis_"+numRow+"'>"+jenis.value+"</td>";
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
     var nourut = document.getElementById('nourut');
    var jenis = document.getElementById('jenis');

    nourut.value="0";
    jenis.value="";
}

function edit(num)
{
    var rownourut = document.getElementById('nourut_'+num);
    var rowjenis = document.getElementById('jenis_'+num);

    var nourut = document.getElementById('nourut');
    var jenis = document.getElementById('jenis');
    var saveBtn = document.getElementById('saveButton');
    
    // Pass Value
    nourut.value = rownourut.innerHTML;
    jenis.value = rowjenis.innerHTML;

    // Disable attr
    // Remove attr
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    saveBtn.setAttribute('onclick','editData('+num+')');
}

function editData(numRow) {
    var nourut = document.getElementById('nourut');
    var jenis = document.getElementById('jenis');
    var saveBtn = document.getElementById('saveButton');
    // Empty = Not Valid
    if(nourut=='' || jenis==''  ) {
        alert('nourut and jenis is obligatory');
      //  exit;
    }else{
        var param = "nourut="+nourut.value;
        param += "&jenis="+jenis.value;
        
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        eval("var res = "+con.responseText);
                        document.getElementById('nourut_'+numRow).innerHTML = res.nourut;
                        document.getElementById('jenis_'+numRow).innerHTML = res.jenis;
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
        
        post_response_text('legal_slave_5jenispreventiveprofile.php?proses=edit', param, respon);
    } 
}