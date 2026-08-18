function getpenghasilan(jenispphval,val) {
    var value = "";
    if(typeof val !== 'undefined'){
        value = val;
    }
    var jenispph = jenispphval;
    var param = "jenispph="+jenispph+"&value="+value;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
            
        
                
                    document.getElementById('jenispenghasilan').innerHTML=con.responseText;
            
                    
            
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    
    post_response_text('pmn_slave_5pajak.php?proses=getpenghasilan', param, respon);
    
} 

function addData() {
    var jenispph = document.getElementById('jenispph');
    var carapembayaran = document.getElementById('carapembayaran');
    var jenispenghasilan = document.getElementById('jenispenghasilan');
    var bodyList = document.getElementById('bodyList');
    var saveBtn = document.getElementById('saveButton');
    var id='';

    // Empty = Not Valid
    if(jenispph=='' || carapembayaran=='' || jenispenghasilan=='' ) {
        alert('jenis pph , cara pembayaran and jenis penghasilan is obligatory');
      //  exit;
    }else{
        var param = "id="+id;
        param += "&jenispph="+getOptionsValue(jenispph);
        param += "&carapembayaran="+getOptionsValue(carapembayaran);
        param += "&jenispenghasilan="+getOptionsValue(jenispenghasilan);
        //alert(param);
        post_response_text('pmn_slave_5pajak.php?proses=add', param, respon);
    } 

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    
                    var numRow = 0;
                    while(document.getElementById('tr_'+numRow)) {
                        numRow++;
                    }
                    
                    id=con.responseText;
                    // Prep row
                    var jenispphval = jenispph.options[jenispph.selectedIndex].text;
                    var carapembayaranval = carapembayaran.options[carapembayaran.selectedIndex].text;
                    var jenispenghasilanval = jenispenghasilan.options[jenispenghasilan.selectedIndex].text;
                    var theRow = "<tr id='tr_"+numRow+"' class='rowcontent'>";
                    theRow += "<td  id='jenispph_"+numRow+"'>"+jenispphval+"</td>";
                    theRow += "<td  id='carapembayaran_"+numRow+"'>"+carapembayaranval+"</td>";
                    theRow += "<td  id='jenispenghasilan_"+numRow+"'>"+jenispenghasilanval+"</td>";
                    theRow += "<td id='edit_"+numRow+"'><img src='images/application/application_edit.png' ";
                                    theRow += "class=resicon  caption='Edit' onclick='passEditHeader("+numRow+","+id+","+getOptionsValue(jenispph)+","+getOptionsValue(carapembayaran)+","+getOptionsValue(jenispenghasilan)+")'></td>";
                    theRow += "</tr>";
                    
                    // Insert Row
                    bodyList.innerHTML += theRow;
                    
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
}

function passEditHeader(num,idv,jenispphval,carapembayaranval,jenispenghasilanval) {
    var id = document.getElementById('id');
    var jenispph = document.getElementById('jenispph');
    var carapembayaran = document.getElementById('carapembayaran');
    var jenispenghasilan = document.getElementById('jenispenghasilan');
    var saveBtn = document.getElementById('saveButton');
    var fieldForm = document.getElementById('fieldFormHeader');
    
    // Pass Value
    //alert(idv);
    id.value = idv;
    jenispph.value = jenispphval;
    getpenghasilan(jenispphval,jenispenghasilanval);
    //jenispenghasilan.value = jenispenghasilanval;
    carapembayaran.value = carapembayaranval;
    
    // Disabled
    
    
    // Remove Disabled

    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    //tanggal.setAttribute('onmousemove','setCalendar(this.id)');
    saveBtn.setAttribute('onclick','editDataHeader('+num+','+idv+')');
}

function editDataHeader(numRow,id) {
    var id = id;
    var jenispph = document.getElementById('jenispph');
    var carapembayaran = document.getElementById('carapembayaran');
    var jenispenghasilan = document.getElementById('jenispenghasilan');

    
    // Empty = Not Valid
    if(jenispph=='' || carapembayaran=='' || jenispenghasilan=='' ) {
        alert('jenis pph , cara pembayaran and jenis penghasilan is obligatory');
      //  exit;
    }else{
        var param = "id="+id;
        param += "&jenispph="+getOptionsValue(jenispph);
        param += "&carapembayaran="+getOptionsValue(carapembayaran);
        param += "&jenispenghasilan="+getOptionsValue(jenispenghasilan);
        
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        eval("var res = "+con.responseText);
                        document.getElementById('jenispph_'+numRow).innerHTML = jenispph.options[jenispph.selectedIndex].text;
                        document.getElementById('carapembayaran_'+numRow).innerHTML = carapembayaran.options[carapembayaran.selectedIndex].text;
                        document.getElementById('jenispenghasilan_'+numRow).innerHTML = jenispenghasilan.options[jenispenghasilan.selectedIndex].text;
                        document.getElementById('edit_'+numRow).innerHTML = "<img src='images/application/application_edit.png' class=resicon  caption='Edit' onclick='passEditHeader("+numRow+","+id+","+getOptionsValue(jenispph)+","+getOptionsValue(carapembayaran)+","+getOptionsValue(jenispenghasilan)+")'>";
                        clearData();

                        

                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        
        post_response_text('pmn_slave_5pajak.php?proses=edit', param, respon);
    } 
}

function clearData()
{
     var jenispph = document.getElementById('jenispph');
    var  carapembayaran = document.getElementById('carapembayaran');
    var  jenispenghasilan = document.getElementById('jenispenghasilan');
    var saveBtn = document.getElementById('saveButton');
    // Remove attr
    saveBtn.removeAttribute('onclick');
                        
                        // Set Attr
    saveBtn.setAttribute('onclick','addData()');

    jenispph.value="";
    carapembayaran.value="";
    jenispenghasilan.value="";
}
/*function delHead(num,id) {
    var id = id;
    var theRow = document.getElementById('tr_'+num);
    
    var param = "id="+id;
    //alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    theRow.parentNode.removeChild(theRow);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    if(confirm("Removing data \nAre you sure?")) {
        post_response_text('pmn_slave_5pajak.php?proses=delete', param, respon);
    }
}*/

/*function loadData() {
    var param;
    alert('masuk');
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    alert(con.responseText);
                    document.getElementById('bodyListHeader').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pmn_slave_5pajak.php?proses=loadData', param, respon);
}*/