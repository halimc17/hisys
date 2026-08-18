
/* Function addModeForm
 * Fungsi untuk mengubah form header menjadi mode tambah
 * O : form header mode tambah
 */
function addModeForm(theme) {
    
    var kodejenis = document.getElementById('kodejenis');
    var tipejenis=document.getElementById('tipejenis');
    var namajenis = document.getElementById('namajenis');
    var sumberjenis = document.getElementById('sumberjenis');
    var status = document.getElementById('status');
    var saveBtn = document.getElementById('saveButton');
    var fieldForm = document.getElementById('fieldFormHeader');
        // Remove Disabled
    kodejenis.removeAttribute('disabled');
    namajenis.removeAttribute('disabled');
    sumberjenis.removeAttribute('disabled');
    saveBtn.removeAttribute('disabled');
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    saveBtn.setAttribute('onclick',"addDataHeader('"+theme+"')");
    fieldForm.firstChild.firstChild.innerHTML = 'Form Header : Add New Data';
}

/* Function editModeForm
 * Fungsi untuk mengubah form header menjadi mode edit
 * I : Nomor Row pada tabel header
 * O : form header mode edit
 */
function editModeForm(num) {
    var rowkodejenis = document.getElementById('kodejenis_'+num);
    var rowtipejenis = document.getElementById('tipejenis_'+num);
    var rownamajenis = document.getElementById('namajenis_'+num);
    var rowsumberjenis = document.getElementById('sumberjenis_'+num);
    var rowstatus = document.getElementById('status_'+num);
    
    var kodejenis = document.getElementById('kodejenis');
    var tipejenis = document.getElementById('tipejenis');
    var namajenis = document.getElementById('namajenis');
    var sumberjenis = document.getElementById('sumberjenis');
    var status = document.getElementById('status');
    var saveBtn = document.getElementById('saveButton');
    var fieldForm = document.getElementById('fieldFormHeader');
    
    // Pass Value
    kodejenis.value = rowkodejenis.innerHTML;
    tipejenis.value = rowtipejenis.innerHTML;
    namajenis.value = rownamajenis.innerHTML;
    sumberjenis.value = rowsumberjenis.innerHTML;
    if(rowstatus.innerHTML == 'Aktif'){
        stat=1;
    }
    else{
        stat=0;
    }
    status.value = stat;
    
    // Disabled
    kodejenis.setAttribute('disabled','disabled');
    tipejenis.setAttribute('disabled','disabled');
    namajenis.setAttribute('disabled','disabled');
    sumberjenis.setAttribute('disabled','disabled');
    
    // Remove Disabled
    //tanggal.removeAttribute('disabled');
    //noreferensi.removeAttribute('disabled');
    //matauang.removeAttribute('disabled');
    saveBtn.removeAttribute('disabled');
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    //tanggal.setAttribute('onmousemove','setCalendar(this.id)');
    saveBtn.setAttribute('onclick','editDataHeader('+num+')');
    fieldForm.firstChild.firstChild.innerHTML = 'Form Header : Edit Data';
    
    showDetail();
}

/* Function addDataHeader
 * Fungsi untuk menambah data header
 * O : form header mode tambah
 */
function addDataHeader(theme) {
    var kodejenis = document.getElementById('kodejenis');
    var tipejenis = document.getElementById('tipejenis');
    var namajenis = document.getElementById('namajenis');
    var sumberjenis = document.getElementById('sumberjenis');
    var status = document.getElementById('status');
    var fieldForm = document.getElementById('fieldFormHeader'),
        saveBtn = document.getElementById('saveButton');
    
    // Empty = Not Valid
    if(kodejenis=='') {
        alert('kodejenis is obligatory');
      //  exit;
    }else{
        var param = "kodejenis="+kodejenis.value;
        param += "&tipejenis="+getOptionsValue(tipejenis);
        param += "&namajenis="+namajenis.value;
        param += "&sumberjenis="+sumberjenis.value;
        param += "&status="+getOptionsValue(status);
        post_response_text('setup_slave_notification_header.php?proses=add', param, respon);
    } 

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    // Pass Journal No
                    
                    // Change Form to Edit Mode
                    fieldForm.firstChild.firstChild.innerHTML = 'Form Header : Edit Data';
                    kodejenis.setAttribute('disabled','disabled');
                    tipejenis.setAttribute('disabled','disabled');
                    namajenis.setAttribute('disabled','disabled');
                    sumberjenis.setAttribute('disabled','disabled');
                    saveBtn.setAttribute('disabled','disabled');
                    
                    // Tambah Row Header
                    addHeaderRow(theme);
                    
                    // Show Detail
                    showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/* Function addHeaderRow
 * Fungsi untuk menambah row baru hasil penambahan header
 * O : Row baru pada table header
 */
function addHeaderRow(theme) {
    var bodyHeader = document.getElementById('bodyListHeader');
    var kodejenis = document.getElementById('kodejenis');
    var tipejenis = document.getElementById('tipejenis');
    var namajenis = document.getElementById('namajenis');
    var sumberjenis = document.getElementById('sumberjenis');
    var status = document.getElementById('status');
    
    // Search Available numRow
    var numRow = 0;
    while(document.getElementById('tr_'+numRow)) {
        numRow++;
    }
    
    // Prep row
    var tipejenisval = tipejenis.options[tipejenis.selectedIndex].value;
    var statusval = status.options[status.selectedIndex].text;
    var theRow = "<tr id='tr_"+numRow+"' class='rowcontent'>";
    theRow += "<td id='delHead_"+numRow+"'><img src='images/"+theme+"/delete.png' ";
    theRow += "class='zImgBtn' onclick='delHead("+numRow+")'></td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='kodejenis_"+numRow+"'>"+kodejenis.value+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='tipejenis_"+numRow+"'>"+tipejenisval+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='namajenis_"+numRow+"'>"+namajenis.value+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='sumberjenis_"+numRow+"'>"+sumberjenis.value+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='status_"+numRow+"'>"+statusval+"</td>";
    theRow += "</tr>";
    
    // Insert Row
    bodyHeader.innerHTML += theRow;
}

/* Function editDataHeader
 * Fungsi untuk mengubah data header
 * O : form header mode edit
 */
function editDataHeader(numRow) {
    var kodejenis = document.getElementById('kodejenis');
    var tipejenis = document.getElementById('tipejenis');
    var namajenis = document.getElementById('namajenis');
    var sumberjenis = document.getElementById('sumberjenis');
    var status = document.getElementById('status');
    var fieldForm = document.getElementById('fieldFormHeader');
    
    // Empty = Not Valid
    if(kodejenis.value=='') {
        alert('Date is obligatory');
        //exit;
    }else {
        var param = "kodejenis="+kodejenis.value;
        param += "&tipejenis="+getOptionsValue(tipejenis);
        param += "&namajenis="+namajenis.value;
        param += "&sumberjenis="+sumberjenis.value;
        param += "&status="+getOptionsValue(status);
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        eval("var res = "+con.responseText);
                        
                        document.getElementById('kodejenis_'+numRow).innerHTML = res.kodejenis;
                        document.getElementById('tipejenis_'+numRow).innerHTML = res.tipejenis;
                        document.getElementById('namajenis_'+numRow).innerHTML = res.namajenis;
                        document.getElementById('sumberjenis_'+numRow).innerHTML = res.sumberjenis;
                        document.getElementById('status_'+numRow).innerHTML = res.status;
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        
        post_response_text('setup_slave_notification_header.php?proses=edit', param, respon);
    } 
}

/* Function showDetail
 * Fungsi untuk menambah row baru hasil penambahan header
 * O : Row baru pada table header
 */
function showDetail() {
    var kodejenis = document.getElementById('kodejenis');
    var status = document.getElementById('status');
    var fieldForm = document.getElementById('fieldFormHeader');
    
    var param = "kodejenis="+kodejenis.value+"&status="+getOptionsValue(status);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    var divDet = document.getElementById('divDetail');
                    if(divDet) {
                        var res = con.responseText;
                        res = res.split('<script>');
                    //alert (res);
                        divDet.innerHTML = res[0];
                        if(res.length>1) {
                            res[1] = res[1].replace('</script>','');
                            eval(res[1]);
                        }
                        
                        loadHeader();
                    } else {
                        alert('DOM Definition Error : divDetail');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_notification_detail.php?proses=show', param, respon);
}

function delHead(num) {
    var kodejenis = document.getElementById('kodejenis_'+num).innerHTML;
    var theRow = document.getElementById('tr_'+num);
    
    var param = "kodejenis="+kodejenis;
    
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
    
    if(confirm("Removing Notification header \nAre you sure?")) {
        post_response_text('setup_slave_notification_header.php?proses=delete', param, respon);
    }
}

/* Function passEditHeader
 * Fungsi untuk mengubah form header menjadi mode edit dan lihat detailnya
 * O : Form header mode edit, dan tampilkan detail
 */
function passEditHeader(num) {
    editModeForm(num);
    showDetail();
}



/**

 * loadHeader
 * Load Journal Header Content
 */
function loadHeader() {
	var param;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
					document.getElementById('bodyListHeader').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_notification_header.php?proses=loadHeader', param, respon);
}