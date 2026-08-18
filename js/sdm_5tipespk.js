function addData() {
    var nourut = document.getElementById('nourut');
    var tipe = document.getElementById('tipe');
    var bodyList = document.getElementById('bodyList');
    
    // Empty = Not Valid
    if(nourut=='' || tipe=='') {
        alert('No. Urut and Tipe is obligatory');
      //  exit;
    }else{
        var param = "nourut="+nourut.value;
        param += "&tipe="+tipe.value;
        post_response_text('sdm_slave_5tipespk.php?proses=add', param, respon);
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
                    theRow += "<td  id='tipe_"+numRow+"'>"+tipe.value+"</td>";
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
    var tipe = document.getElementById('tipe');

    nourut.value="0";
    tipe.value="";
}

function edit(num)
{
    var rownourut = document.getElementById('nourut_'+num);
    var rowtipe = document.getElementById('tipe_'+num);

    var nourut = document.getElementById('nourut');
    var tipe = document.getElementById('tipe');
    var saveBtn = document.getElementById('saveButton');
    
    // Pass Value
    nourut.value = rownourut.innerHTML;
    tipe.value = rowtipe.innerHTML;

    // Disable attr
    // Remove attr
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    saveBtn.setAttribute('onclick','editData('+num+')');
}

function editData(numRow) {
    var nourut = document.getElementById('nourut');
    var tipe = document.getElementById('tipe');
    var saveBtn = document.getElementById('saveButton');
    // Empty = Not Valid
    if(nourut=='' || tipe==''  ) {
        alert('nourut and tipe is obligatory');
      //  exit;
    }else{
        var param = "nourut="+nourut.value;
        param += "&tipe="+tipe.value;
        
        
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
                        document.getElementById('tipe_'+numRow).innerHTML = res.tipe;
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
        
        post_response_text('sdm_slave_5tipespk.php?proses=edit', param, respon);
    } 
}