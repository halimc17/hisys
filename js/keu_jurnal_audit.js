/* Function addModeForm
 * Fungsi untuk mengubah form header menjadi mode tambah
 * O : form header mode tambah
 */
function validPeriod(id) {
    var startPeriod = document.getElementById('startPeriod').value;
    var endPeriod = document.getElementById('endPeriod').value;
    var currDate = document.getElementById(id).value;
    var tmpCurr = currDate.split('-');
    
    // Get Tgl, Bln, Tahun
    var tglStart = startPeriod.substring(6,8);
    var blnStart = startPeriod.substring(4,6);
    var thnStart = startPeriod.substring(0,4);
    
    var tglEnd = endPeriod.substring(6,8);
    var blnEnd = endPeriod.substring(4,6);
    var thnEnd = endPeriod.substring(0,4);
    
    var tglCurr = tmpCurr[0];
    var blnCurr = tmpCurr[1];
    var thnCurr = tmpCurr[2];
    
    // Make to JS Date
    var jsCurr = new Date(thnCurr,blnCurr,tglCurr);
    var jsStart = new Date(thnStart,blnStart,tglStart);
    var jsEnd = new Date(thnEnd,blnEnd,tglEnd);
    
    var vPeriod = true;
    
   // if(jsCurr<=jsEnd && jsCurr>=jsStart) {
   //     vPeriod = true;
   // }
   if(jsCurr>jsEnd)
       {
           if(confirm(notifperiodeadjusment))
               {
                 vPeriod = true;  
               }
       }
    
    return vPeriod;
}

/* Function addModeForm
 * Fungsi untuk mengubah form header menjadi mode tambah
 * O : form header mode tambah
 */
function addModeForm(theme) {
    var kodejurnal = document.getElementById('kodejurnal');
    var nojurnal = document.getElementById('nojurnal');
    var tanggal = document.getElementById('tanggal');
    var noreferensi = document.getElementById('noreferensi');
    var matauang = document.getElementById('matauang');
    var revisi = document.getElementById('revisi');
    var saveBtn = document.getElementById('saveButton');
    var fieldForm = document.getElementById('fieldFormHeader');
    
    // Remove Disabled
    kodejurnal.removeAttribute('disabled');
    nojurnal.removeAttribute('disabled');
    tanggal.removeAttribute('disabled');
    noreferensi.removeAttribute('disabled');
    matauang.removeAttribute('disabled');
    revisi.removeAttribute('disabled');
    saveBtn.removeAttribute('disabled');
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    tanggal.setAttribute('onmousemove','setCalendar(this.id)');
    saveBtn.setAttribute('onclick',"addDataHeader('"+theme+"')");
    fieldForm.firstChild.firstChild.innerHTML = 'Form Header : Add New Data';
}

/* Function editModeForm
 * Fungsi untuk mengubah form header menjadi mode edit
 * I : Nomor Row pada tabel header
 * O : form header mode edit
 */
function editModeForm(num) {
    var rowKodejurnal = document.getElementById('kodejurnal_'+num);
    var rowNojurnal = document.getElementById('nojurnal_'+num);
    var rowTanggal = document.getElementById('tanggal_'+num);
    var rowNoreferensi = document.getElementById('noreferensi_'+num);
    var rowMatauang = document.getElementById('matauang_'+num);
    var rowRevisi = document.getElementById('revisi_'+num);
    
    var kodejurnal = document.getElementById('kodejurnal');
    var nojurnal = document.getElementById('nojurnal');
    var tanggal = document.getElementById('tanggal');
    var noreferensi = document.getElementById('noreferensi');
    var matauang = document.getElementById('matauang');
    var revisi = document.getElementById('revisi');
    
    var saveBtn = document.getElementById('saveButton');
    var fieldForm = document.getElementById('fieldFormHeader');
    
    // Pass Value
    kodejurnal.value = rowKodejurnal.innerHTML;
    nojurnal.value = rowNojurnal.innerHTML;
    tanggal.value = rowTanggal.innerHTML;
    matauang.value = rowMatauang.innerHTML;
    noreferensi.value = rowNoreferensi.innerHTML;
    revisi.value = rowRevisi.innerHTML;
    
    // Disabled
    kodejurnal.setAttribute('disabled','disabled');
    nojurnal.setAttribute('disabled','disabled');
    
    // Remove Disabled
    tanggal.removeAttribute('disabled');
    noreferensi.removeAttribute('disabled');
    matauang.removeAttribute('disabled');
    revisi.removeAttribute('disabled');
    saveBtn.removeAttribute('disabled');
    saveBtn.removeAttribute('onclick');
    
    // Set Attr
    tanggal.setAttribute('onmousemove','setCalendar(this.id)');
    saveBtn.setAttribute('onclick','editDataHeader('+num+')');
    fieldForm.firstChild.firstChild.innerHTML = 'Form Header : Edit Data';
    
    showDetail();
}

/* Function addDataHeader
 * Fungsi untuk menambah data header
 * O : form header mode tambah
 */
function addDataHeader(theme) {
    var nojurnal = document.getElementById('nojurnal');
    var kodejurnal = document.getElementById('kodejurnal');
    var tanggal = document.getElementById('tanggal');
    var noref = document.getElementById('noreferensi');
    var matauang = document.getElementById('matauang');
    var revisi = document.getElementById('revisi');
    var fieldForm = document.getElementById('fieldFormHeader');
    
        // Only allow last date of the year
    var qwe=tanggal.value.substr(0,5);
    
    
    // Empty = Not Valid
    if(tanggal.value=='') {
        alert('Date is obligatory');
     //   exit;
    }else if(qwe!='31-12') {
        alert('Date format: 31-12-XXXX');
       // exit();
    }else if(validPeriod('tanggal')) {
        var param = "kodejurnal="+getOptionsValue(kodejurnal);
        param += "&tanggal="+tanggal.value;
        param += "&noreferensi="+noref.value;
        param += "&matauang="+getOptionsValue(matauang);
        param += "&revisi="+getOptionsValue(revisi);
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        // Pass Journal No
                        nojurnal.value = con.responseText;
                        
                        alert(nojurnal.value);
                        
                        // Change Form to Edit Mode
                        fieldForm.firstChild.firstChild.innerHTML = 'Form Header : Edit Data';
                        nojurnal.setAttribute('disabled','disabled');
                        
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
        
        post_response_text('keu_slave_jurnal_audit_header.php?proses=add', param, respon);
    } else {
        alert(notiftanggalakutansi);
    }
    
}

/* Function addHeaderRow
 * Fungsi untuk menambah row baru hasil penambahan header
 * O : Row baru pada table header
 */
function addHeaderRow(theme) {
    var bodyHeader = document.getElementById('bodyListHeader');
    var nojurnal = document.getElementById('nojurnal');
    var kodejurnal = document.getElementById('kodejurnal');
    var tanggal = document.getElementById('tanggal');
    var noreferensi = document.getElementById('noreferensi');
    var matauang = document.getElementById('matauang');
    var revisi = document.getElementById('revisi');
    
    // Search Available numRow
    var numRow = 0;
    while(document.getElementById('tr_'+numRow)) {
        numRow++;
    }
    
    // Prep row
    var kodeVal = kodejurnal.options[kodejurnal.selectedIndex].value;
    var theRow = "<tr id='tr_"+numRow+"' class='rowtitle'>";
    theRow += "<td id='pdf_"+numRow+"'><img src='images/"+theme+"/pdf.jpg' ";
    theRow += "class='zImgBtn' onclick='detailPDF("+numRow+",event)'></td>";
    theRow += "<td id='delHead_"+numRow+"'><img src='images/"+theme+"/delete.png' ";
    theRow += "class='zImgBtn' onclick='delHead("+numRow+")'></td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='kodejurnal_"+numRow+"'>"+kodeVal+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='nojurnal_"+numRow+"'>"+nojurnal.value+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='tanggal_"+numRow+"'>"+tanggal.value+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='noreferensi_"+numRow+"'>"+noreferensi.value+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='matauang_"+numRow+"'>"+matauang.value+"</td>";
    theRow += "<td align=right onclick='passEditHeader("+numRow+")' id='debet_"+numRow+"'>0</td>";
    theRow += "<td align=right onclick='passEditHeader("+numRow+")' id='kredit_"+numRow+"'>0</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='revisi_"+numRow+"'>"+revisi.value+"</td>";
    theRow += "</tr>";
    
    // Insert Row
    bodyHeader.innerHTML += theRow;
}

/* Function editDataHeader
 * Fungsi untuk mengubah data header
 * O : form header mode edit
 */
function editDataHeader(numRow) {
    var nojurnal = document.getElementById('nojurnal');
    var kodejurnal = document.getElementById('kodejurnal');
    var tanggal = document.getElementById('tanggal');
    var noref = document.getElementById('noreferensi');
    var matauang = document.getElementById('matauang');
    var revisi = document.getElementById('revisi');
    var fieldForm = document.getElementById('fieldFormHeader');
    
    // Empty = Not Valid
    if(tanggal.value=='') {
        alert(notiftanggal);
       // exit;
    }else if(validPeriod('tanggal')) {
        var param = "nojurnal="+nojurnal.value;
        param += "&kodejurnal="+getOptionsValue(kodejurnal);
        param += "&tanggal="+tanggal.value;
        param += "&noreferensi="+noref.value;
        param += "&matauang="+getOptionsValue(matauang);
        param += "&revisi="+getOptionsValue(revisi);
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        // Success Response
                        eval("var res = "+con.responseText);
                        
                        document.getElementById('kodejurnal_'+numRow).innerHTML = res.kodejurnal;
                        document.getElementById('tanggal_'+numRow).innerHTML = res.tanggal;
                        document.getElementById('noreferensi_'+numRow).innerHTML = res.noreferensi;
                        document.getElementById('matauang_'+numRow).innerHTML = res.matauang;
                        document.getElementById('revisi_'+numRow).innerHTML = res.revisi;
                        
                        showDetail();
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        
        post_response_text('keu_slave_jurnal_audit_header.php?proses=edit', param, respon);
    } else {
        alert(notiftanggalakutansi);
    }
}

/* Function showDetail
 * Fungsi untuk menambah row baru hasil penambahan header
 * O : Row baru pada table header
 */
function showDetail() {
    var nojurnal = document.getElementById('nojurnal');
    var kodejurnal = document.getElementById('kodejurnal');
    var tanggal = document.getElementById('tanggal');
    var noref = document.getElementById('noreferensi');
    var matauang = document.getElementById('matauang');
    var revisi = document.getElementById('revisi');
    var fieldForm = document.getElementById('fieldFormHeader');
    
    var param = "nojurnal="+nojurnal.value;
    param += "&kodejurnal="+getOptionsValue(kodejurnal);
    param += "&tanggal="+tanggal.value;
    param += "&noreferensi="+noref.value;
    param += "&matauang="+getOptionsValue(matauang);
    param += "&revisi="+revisi.value;
    
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
                        divDet.innerHTML = con.responseText;
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
    
    post_response_text('keu_slave_jurnal_audit_detail.php?proses=show', param, respon);
}

function delHead(num) {
    var nojurnal = document.getElementById('nojurnal_'+num).innerHTML;
    var theRow = document.getElementById('tr_'+num);
    
    var param = "nojurnal="+nojurnal;
    
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
    
    if(confirm(notifremovejurnalheader+" \n"+notifandayakin)) {
        post_response_text('keu_slave_jurnal_header.php?proses=delete', param, respon);
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

function detailPDF(numRow,ev) {
    formPrint('pdf','1','##nojurnal_'+numRow,'','keu_slave_jurnal_print',ev,true);
}