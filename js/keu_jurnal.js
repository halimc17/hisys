/* Function addModeForm
 * Fungsi untuk mengubah form header menjadi mode tambah
 * O : form header mode tambah
 */

function getnoakun(){
	nojurnal=trim(document.getElementById('nojurnal').value);
	noaruskas=trim(document.getElementById('noaruskas').value);
	vsplit = nojurnal.split('/');
	kodeorg = vsplit[1];
	
	noakun="";
	param='noaruskas='+noaruskas+'&kodeorg='+kodeorg+'&noakun='+noakun;
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=getnoakun', param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
                    document.getElementById('noakun').innerHTML = isdt[0];
                    // document.getElementById('keterangan2temp').innerHTML = isdt[1];
					updFieldAktif();
				
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function updFieldAktif() {
	var id='ftJurnalDt_';
    var noakun = document.getElementById('noakun').value;

    var kodekegiatan = document.getElementById(id+'kodekegiatan').childNodes;
    var kodeasset = document.getElementById(id+'kodeasset').childNodes;
    var kodevhc = document.getElementById(id+'kodevhc').childNodes;
    var orgalokasi = document.getElementById(id+'kodeblok').childNodes;
	
    var param = "noakun="+noakun;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					//=== Success Response
                    var res = con.responseText;
                    // Kegiatan
                    if(res[0]==0) {
                        kodekegiatan[0].setAttribute('disabled','disabled');
                        kodekegiatan[0].selectedIndex=0;
                    } else {
                        kodekegiatan[0].removeAttribute('disabled');
                    }
                    
                    // Asset
                    if(res[1]==0) {
                        kodeasset[0].setAttribute('disabled','disabled');
                        kodeasset[0].selectedIndex=0;
                    } else {
                        kodeasset[0].removeAttribute('disabled');
                    }
                    
                    // Kendaraan
                    if(res[5]==0) {
                        kodevhc[0].setAttribute('disabled','disabled');
                        kodevhc[0].selectedIndex=0;
                    } else {
                        kodevhc[0].removeAttribute('disabled');
                    }
                    // blok
                    if(res[6]==0) {
                        orgalokasi[0].setAttribute('disabled','disabled');
                        orgalokasi[0].selectedIndex=0;
                    } else {
                        orgalokasi[0].removeAttribute('disabled');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank_detail.php?proses=updField', param, respon);
}
 
/*
function getkeg() {
    var kodeasset = document.getElementById('kodeasset').value;
    var param = "kodeasset="+kodeasset;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
			
		
				
					document.getElementById('kodekegiatan').innerHTML=con.responseText;
			
					
			
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    
	post_response_text('keu_slave_jurnal_header.php?proses=getkeg', param, respon);
    
} 
 */
 
 

function getkeg() {
    var noakun = document.getElementById('noakun').value;
    var param = "noakun="+noakun;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('kodekegiatan').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	post_response_text('keu_slave_jurnal_header.php?proses=getkeg', param, respon);
    
} 

 
 
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
    
    var vPeriod = false;
    
   // if(jsCurr<=jsEnd && jsCurr>=jsStart) {
   //     vPeriod = true;
   // }
   if(jsCurr>jsEnd)
       {
           if(confirm('The date you enter is greater than the current period, continue..?'))
               {
                 vPeriod = true;  
               }
       }
   else if(jsCurr>=jsStart)
       vPeriod = true;
    
    return vPeriod;
}

/* Function addModeForm
 * Fungsi untuk mengubah form header menjadi mode tambah
 * O : form header mode tambah
 */
function addModeForm(theme) {
    var kodejurnal = document.getElementById('kodejurnal');
    var kodeunit = document.getElementById('kodeunit');
    var nojurnal = document.getElementById('nojurnal');
    var tanggal = document.getElementById('tanggal');
    var noreferensi = document.getElementById('noreferensi');
    var matauang = document.getElementById('matauang');
    // var persetujuan1 = document.getElementById('persetujuan1');
    // var persetujuan2 = document.getElementById('persetujuan2');
    var saveBtn = document.getElementById('saveButton');
    var fieldForm = document.getElementById('fieldFormHeader');
    var upload = document.getElementById('upload');
    var addfile = document.getElementById('addfile');
    
    // Remove Disabled
    kodejurnal.removeAttribute('disabled');
    kodeunit.removeAttribute('disabled');
    nojurnal.removeAttribute('disabled');
    tanggal.removeAttribute('disabled');
    noreferensi.removeAttribute('disabled');
    matauang.removeAttribute('disabled');
    // persetujuan1.removeAttribute('disabled');
    // persetujuan2.removeAttribute('disabled');
    upload.removeAttribute('disabled');
    saveBtn.removeAttribute('disabled');
    saveBtn.removeAttribute('onclick');
    addfile.setAttribute('onclick',"addfileupload()");
    
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
    
    var kodejurnal = document.getElementById('kodejurnal');
    var nojurnal = document.getElementById('nojurnal');
    var kodeunit = document.getElementById('kodeunit');
    var tanggal = document.getElementById('tanggal');
    var noreferensi = document.getElementById('noreferensi');
    var matauang = document.getElementById('matauang');
    var saveBtn = document.getElementById('saveButton');
    var fieldForm = document.getElementById('fieldFormHeader');
	var upload = document.getElementById('upload');
    var addfile = document.getElementById('addfile');
    
	vsplit = (rowNojurnal.innerHTML).split('/');
    // Pass Value
    kodejurnal.value = rowKodejurnal.innerHTML;
    kodeunit.value = vsplit[1];
    nojurnal.value = rowNojurnal.innerHTML;
    tanggal.value = rowTanggal.innerHTML;
    matauang.value = rowMatauang.innerHTML;
    noreferensi.value = rowNoreferensi.innerHTML;
    
    // Disabled
    kodejurnal.setAttribute('disabled','disabled');
    kodeunit.setAttribute('disabled','disabled');
    nojurnal.setAttribute('disabled','disabled');
    tanggal.setAttribute('disabled','disabled');
	
    // Remove Disabled
    //tanggal.removeAttribute('disabled');
    noreferensi.removeAttribute('disabled');
    //matauang.removeAttribute('disabled');
    saveBtn.removeAttribute('disabled');
    saveBtn.removeAttribute('onclick');
	
	upload.removeAttribute('disabled');
	addfile.setAttribute('onclick',"addfileupload()");
    
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
    var kodeunit = document.getElementById('kodeunit');
    var tanggal = document.getElementById('tanggal');
    var noref = document.getElementById('noreferensi');
    var matauang = document.getElementById('matauang');
    // var persetujuan1 = document.getElementById('persetujuan1');
    // var persetujuan2 = document.getElementById('persetujuan2');
    var fieldForm = document.getElementById('fieldFormHeader'),
		saveBtn = document.getElementById('saveButton');
    
    // Empty = Not Valid
	/*
    if(tanggal.value=='') {
        alert('Date is obligatory');
      //  exit;
    }else if(validPeriod('tanggal')) {
        var param = "kodejurnal="+getOptionsValue(kodejurnal);
        param += "&tanggal="+tanggal.value;
        param += "&kodeunit="+kodeunit.value;
        param += "&noreferensi="+noref.value;
        param += "&matauang="+getOptionsValue(matauang);
        // param += "&persetujuan1="+getOptionsValue(persetujuan1);
        // param += "&persetujuan2="+getOptionsValue(persetujuan2);
        post_response_text('keu_slave_jurnal_header.php?proses=add', param, respon);
    } else {
        alert("Date beyond active periode");
    }
	*/
	
	if(tanggal.value=='') {
        alert('Date is obligatory');
	}else{
		 var param = "kodejurnal="+getOptionsValue(kodejurnal);
        param += "&tanggal="+tanggal.value;
        param += "&kodeunit="+kodeunit.value;
        param += "&noreferensi="+noref.value;
        param += "&matauang="+getOptionsValue(matauang);
        // param += "&persetujuan1="+getOptionsValue(persetujuan1);
        // param += "&persetujuan2="+getOptionsValue(persetujuan2);
        post_response_text('keu_slave_jurnal_header.php?proses=add', param, respon);
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
					nojurnal.value = con.responseText;
					
					// Change Form to Edit Mode
					fieldForm.firstChild.firstChild.innerHTML = 'Form Header : Edit Data';
					nojurnal.setAttribute('disabled','disabled');
					matauang.setAttribute('disabled','disabled');
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

function addfileupload() {
    var addfile = document.getElementById('addfile');
    var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", document.getElementById('upload').value);
	
	if (document.getElementById('upload').value == "") {
		alert("Gagal, File upload masih kosong.");
		return false;
	}
	
	addfile.removeAttribute('onclick');
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "keu_slave_jurnal_header.php?proses=addfileupload", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					addfile.setAttribute('onclick',"addfileupload()");
					alert(con.responseText);
				} else {
					//=== Success Response
					addfile.setAttribute('onclick',"addfileupload()");
					document.getElementById("upload").value = "";
					loadfiles();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles() {
	param = '';
	post_response_text('keu_slave_jurnal_header.php?proses=loadfiles', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerupload').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(namafile) {
	param = 'namafile='+namafile;
	post_response_text('keu_slave_jurnal_header.php?proses=deletefile', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles();
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
    var nojurnal = document.getElementById('nojurnal');
    var kodejurnal = document.getElementById('kodejurnal');
    var tanggal = document.getElementById('tanggal');
    var noreferensi = document.getElementById('noreferensi');
    var matauang = document.getElementById('matauang');
    
    // Search Available numRow
    var numRow = 0;
    while(document.getElementById('tr_'+numRow)) {
        numRow++;
    }
    
    // Prep row
    var kodeVal = kodejurnal.options[kodejurnal.selectedIndex].value;
    var theRow = "<tr id='tr_"+numRow+"' class='rowcontent'>";
    theRow += "<td id='pdf_"+numRow+"'><img src='images/"+theme+"/pdf.jpg' ";
    theRow += "class='zImgBtn' onclick='detailPDF("+numRow+",event)'></td>";
    theRow += "<td id='delHead_"+numRow+"'><img src='images/"+theme+"/delete.png' ";
    theRow += "class='zImgBtn' onclick='delHead("+numRow+")'></td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='kodejurnal_"+numRow+"'>"+kodeVal+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='nojurnal_"+numRow+"'>"+nojurnal.value+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='tanggal_"+numRow+"'>"+tanggal.value+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='noreferensi_"+numRow+"'>"+noreferensi.value+"</td>";
    theRow += "<td onclick='passEditHeader("+numRow+")' id='matauang_"+numRow+"'>"+matauang.value+"</td>";
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
    // var persetujuan1 = document.getElementById('persetujuan1');
    // var persetujuan2 = document.getElementById('persetujuan2');
    var fieldForm = document.getElementById('fieldFormHeader');
    
    // Empty = Not Valid
    if(tanggal.value=='') {
        alert('Date is obligatory');
        //exit;
    }else {
        var param = "nojurnal="+nojurnal.value;
        param += "&kodejurnal="+getOptionsValue(kodejurnal);
        param += "&tanggal="+tanggal.value;
        param += "&noreferensi="+noref.value;
        param += "&matauang="+getOptionsValue(matauang);
        // param += "&persetujuan1="+getOptionsValue(persetujuan1);
        // param += "&persetujuan2="+getOptionsValue(persetujuan2);
        
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
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        post_response_text('keu_slave_jurnal_header.php?proses=edit', param, respon);
    }
	
	
	/*
	
    // Empty = Not Valid
    if(tanggal.value=='') {
        alert('Date is obligatory');
        //exit;
    }else if(validPeriod('tanggal')) {
        var param = "nojurnal="+nojurnal.value;
        param += "&kodejurnal="+getOptionsValue(kodejurnal);
        param += "&tanggal="+tanggal.value;
        param += "&noreferensi="+noref.value;
        param += "&matauang="+getOptionsValue(matauang);
        // param += "&persetujuan1="+getOptionsValue(persetujuan1);
        // param += "&persetujuan2="+getOptionsValue(persetujuan2);
        
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
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        
        post_response_text('keu_slave_jurnal_header.php?proses=edit', param, respon);
    } else {
        alert("Date beyond active periode");
    }
	
	*/
	
	
	
	
	
	
	
	
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
    var fieldForm = document.getElementById('fieldFormHeader');
    
    var param = "nojurnal="+nojurnal.value;
    param += "&kodejurnal="+getOptionsValue(kodejurnal);
    param += "&tanggal="+tanggal.value;
    param += "&noreferensi="+noref.value;
    param += "&matauang="+getOptionsValue(matauang);
    
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
    
    post_response_text('keu_slave_jurnal_detail.php?proses=show', param, respon);
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
    
    if(confirm("Removing Journal header \nAre you sure?")) {
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

/**
 * loadHeader
 * Load Journal Header Content
 */
function loadHeader() {
	// var param;
	var unitcr = document.getElementById('unitcr');
    var param = "unitcr="+unitcr.value;
	// alert(param);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
					document.getElementById('bodyListHeader').innerHTML = con.responseText;
					loadfiles();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_jurnal_header.php?proses=loadHeader', param, respon);
}