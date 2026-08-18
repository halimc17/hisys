function savechecklistdokumen(noinvoice, maxrow) {
  strparam = "";
  param = "proses=savechecklistdokumen";
  param += "&noinvoice=" + noinvoice + "&maxrow=" + maxrow;
  for (i = 1; i <= maxrow; i++) {
    strparam +=
      "&kodedokumen[" +
      i +
      "]=" +
      trim(document.getElementById("kodedokumen" + i).innerHTML);
    nilaiflag = document.getElementById("flagdokumen" + i);
    if (nilaiflag.checked == true) {
      nilaiflag = 1;
    } else {
      nilaiflag = 0;
    }
    param += "&flagdokumen[" + i + "]=" + nilaiflag;
    // strparam += '&flagdokumen['+i+']='+trim(document.getElementById('flagdokumen'+i).innerHTML);
  }
  param += strparam;
  // alertify.alert("Informasi",param);return;
  tujuan = "keu_slave_tagihanv2.php";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // findap();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}

function getinfo() {
  content =
    '<div id=formgetinfo style="max-height:250px;overflow:auto;"></div>';
  title = "Info";
  height = "";
  width = "";
  ev = "event";
  showDialog1(title, content, width, height, ev);
  kodeorg = document.getElementById("kodeorg").value;
  tanggalinvoice = document.getElementById("tanggalinvoice").value;
  supplier = document.getElementById("supplier").value;
  noinvoice = document.getElementById("noinvoice").value;
  param = "proses=getinfo";
  param += "&kodeorg=" + kodeorg;
  param += "&tanggalinvoice=" + tanggalinvoice;
  param += "&supplier=" + supplier;
  param += "&noinvoice=" + noinvoice;

  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          document.getElementById("formgetinfo").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function displayFormInput() {
  clearData();
  document.getElementById("formInput").style.display = "block";
  document.getElementById("listData").style.display = "none";
  document.getElementById("detailField").innerHTML = "";
  document.getElementById("detailField").style.display = "none";
}

function displaylist() {
  clearData();
  document.getElementById("noinvoicesch").value = "";
  document.getElementById("noinvoicesuppliersch").value = "";
  document.getElementById("noposch").value = "";
  document.getElementById("tipeinvoicesch").value = "";
  document.getElementById("kodesuppliersch").value = "";
  document.getElementById("unitsch").value = "";
  document.getElementById("tanggalmulaisch").value = "";
  document.getElementById("tanggalselesaisch").value = "";
  document.getElementById("postingsch").value = "";
  loadData(0);
}

function clearData() {
  document.getElementById("tipeinvoice").disabled = false;
  document.getElementById("jenissupplier").value = "";
  document.getElementById("unit").value = "";
  document.getElementById("noinvoice").value = "";
  document.getElementById("unit").disabled = false;
  document.getElementById("noinvoicesupplier").value = "";
  document.getElementById("kodeorg").value = "";
  document.getElementById("kodeorg").disabled = false;
  document.getElementById("jatuhtempo").value = "";
  document.getElementById("tanggal").value = "";
  document.getElementById("reksupplier").value = "";
  document.getElementById("tipeinvoice").value = "";
  document.getElementById("nopo").value = "";
  document.getElementById("nopo").disabled = true;
  document.getElementById("supplier").disabled = false;
  document.getElementById("supplier").value = "";
  document.getElementById("noakun").value = "";
  document.getElementById("matauang").value = "IDR";
  document.getElementById("matauang").disabled = false;
  document.getElementById("kurs").value = "1";
  document.getElementById("nofp").value = "";
  document.getElementById("jenistransaksi").value = "";
  document.getElementById("nilaidpp").value = "";
  document.getElementById("nilaiinvoice").value = "";
  document.getElementById("tanggalinvoice").value = "";
  document.getElementById("keterangan").value = "";
  document.getElementById("npwp").value = "";
  document.getElementById("npwp").disabled = false;
  document.getElementById("npwppph").value = "";
  document.getElementById("npwppph").disabled = false;
  document.getElementById("tanggalnofp").value = "";
  document.getElementById("notransaksi_gr").value = "";
  document.getElementById("termin").value = "";
  document.getElementById("proses").value = "add";
  // document.getElementById('upload').value="";
  param = "proses=clearData";
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
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

function printPDF(ev) {
  // Prep Param
  param = "proses=pdf";
  showDialog1(
    "Print PDF",
    "<iframe frameborder=0 style='width:795px;height:400px'" +
      " src='keu_slave_tagihan_print.php?" +
      param +
      "'></iframe>",
    "800",
    "400",
    ev
  );
  var dialog = document.getElementById("dynamic1");
  dialog.style.top = "50px";
  dialog.style.left = "15%";
}

function getunit(kodeorg, kodeunit, npwp, npwppph) {
  kdpt = kodeorg.value;
  param = "kdpt=" + kdpt + "&proses=getunit";
  if (kodeunit != 0) {
    param += "&kodeunit=" + kodeunit;
  }
  if (npwp != 0) {
    param += "&npwp=" + npwp;
  }
  if (npwppph != 0) {
    param += "&npwppph=" + npwppph;
  }
  post_response_text("keu_slave_tagihanv2.php", param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // === Success Response
          data = con.responseText.split("####");
          document.getElementById("unit").innerHTML = data[0];
          document.getElementById("npwp").innerHTML = data[1];
          document.getElementById("npwppph").innerHTML = data[2];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getrek(supplier, reksupplier, jenissupplier) {
  supplier = supplier;
  tipeinvoice = document.getElementById("tipeinvoice").value;
  param =
    "supplier=" + supplier + "&proses=getrek" + "&tipeinvoice=" + tipeinvoice;
  if (reksupplier != 0) {
    param += "&reksupplier=" + reksupplier;
  }
  if (jenissupplier != 0) {
    param += "&jenissupplier=" + jenissupplier;
  }
  noinvoice = document.getElementById("noinvoice").value;
  // alertify.alert("Informasi",param);
  // alertify.alert("Informasi",keterangan2);
  post_response_text("keu_slave_tagihanv2.php", param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // === Success Response

          data = con.responseText.split("####");
          document.getElementById("reksupplier").innerHTML = data[0];
          document.getElementById("jenissupplier").innerHTML = data[1];
          if (noinvoice != "") {
            showDetail();
          } else {
            getdate30();
          }

          //
          //if(reksupplier!=0){

          //}
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getkurs() {
  matauang = document.getElementById("matauang").value;
  tanggal = document.getElementById("tanggal").value;
  param = "matauang=" + matauang + "&tanggal=" + tanggal + "&proses=getkurs";
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          document.getElementById("kurs").value = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getnoakunsup() {
  supplier = document.getElementById("supplier").value;
  jenissupplier = document.getElementById("jenissupplier").value;
  tipeinvoice = document.getElementById("tipeinvoice").value;
  param =
    "supplier=" +
    supplier +
    "&jenissupplier=" +
    jenissupplier +
    "&tipeinvoice=" +
    tipeinvoice +
    "&proses=getnoakunsup";
  // alertify.alert("Informasi",param);
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("noakun").value = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getdate30() {
  tanggal = document.getElementById("tanggal").value;
  nopo = document.getElementById("nopo").value;
  tipeinvoice = document.getElementById("tipeinvoice").value;

  // if (tanggal == "") {
  //   document.getElementById("tanggalinvoice").value = "";
  //   alertify.alert("Informasi", "Tanggal Invoice tidak boleh kosong");
  //   return;
  // }

  param = "tanggal=" + tanggal + "&proses=getdate30";
  if (nopo != 0 || nopo != "") {
    param += "&nopo=" + nopo;
  }
  if (tipeinvoice == "p" || tipeinvoice == "pon") {
    param += "&tipeinvoice=" + tipeinvoice;
  }
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('tanggalinvoice').value =tanggal;

          // if(con.responseText==1){
          // alertify.alert("Informasi",'Tanggal dipilih tidak boleh kurang dari tanggal hari ini');
          // document.getElementById('tanggal').value ='';
          // document.getElementById('jatuhtempo').value ='';
          // }else{
          document.getElementById("jatuhtempo").value = trim(con.responseText);
          // document.getElementById('tanggalinvoice').value = tanggal;
          getnoakunsup();
          // }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function gettglfp() {
  tanggalinvoice = document.getElementById("tanggalinvoice").value;
  document.getElementById("tanggalnofp").value = tanggalinvoice;
}

function getnilai() {
  pajak = document.getElementById("pajak").value;
  nilaidpp = document.getElementById("nilaidpp").value;
  nilaidpp = nilaidpp.replace(new RegExp(/,/i, "gm"), "");
  if (pajak != 0) {
    nilai = (pajak / 100) * nilaidpp;
  }
  document.getElementById("nilai").value = numberFormat(nilai);
}

function getpajak() {
  supplier = document.getElementById("supplier").value;
  noakun = document.getElementById("noakundt").value;
  nilaidpp = document.getElementById("nilaidpp").value;
  param =
    "supplier=" +
    supplier +
    "&noakun=" +
    noakun +
    "&nilaidpp=" +
    nilaidpp +
    "&proses=getpajak";
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          $data = con.responseText.split("####");
          document.getElementById("pajak").value = $data[0];
          document.getElementById("nilai").value = $data[1];
          getkegiatan();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getnoakun(noakun, keterangan) {
  noaruskas = document.getElementById("noaruskas").value;
  kodevhc = document.getElementById("kodevhc").value;
  param =
    "noaruskas=" +
    noaruskas +
    "&noakun=" +
    noakun +
    "&keterangan=" +
    keterangan +
    "&kodevhc=" +
    kodevhc +
    "&proses=getnoakun";
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          data = con.responseText.split("####");
          document.getElementById("noakundt").innerHTML = data[0];
          document.getElementById("keterangandt").innerHTML = data[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getnoaruskas(noaruskas) {
  kodevhc = document.getElementById("kodevhc").value;
  param =
    "kodevhc=" + kodevhc + "&noaruskas=" + noaruskas + "&proses=getnoaruskas";
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          document.getElementById("noaruskas").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getkegiatan() {
  noakun = document.getElementById("noakundt").value;
  param = "noakun=" + noakun + "&proses=getkegiatan";
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          document.getElementById("kegiatandt").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getblok() {
  kegiatan = document.getElementById("kegiatandt").value;
  unit = document.getElementById("unit").value;
  param = "kegiatan=" + kegiatan + "&proses=getblok";
  param += "&unit=" + unit;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          document.getElementById("kodeblokdt").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getblokbesar() {
  kegiatan = document.getElementById("kegiatandt").value;
  unit = document.getElementById("unit").value;
  param = "kegiatan=" + kegiatan + "&proses=getblokbesar";
  param += "&unit=" + unit;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          document.getElementById("kodeblokdt").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

/*
function disnopo(){
    jnsInvoice=document.getElementById('tipeinvoice');
    jnsInvoice=jnsInvoice.options[jnsInvoice.selectedIndex].value;
    param='tipeinvoice=' + jnsInvoice;
    tujuan='keu_slave_tagihanv2.php';
    post_response_text(tujuan + '?' + 'proses=disnopo', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    data=con.responseText.split('####');
                    document.getElementById('nopo').disabled=false;
                    if (data[0]==1){
                        document.getElementById('supplier').disabled=false;
                    }else{
                        document.getElementById('supplier').disabled=true;
                    }
                    // document.getElementById('supplier').innerHTML=data[1];
                }

                if (jnsInvoice=='um') {
                    document.getElementById('supplier').disabled=false;
                }

            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
*/

function showhidesearchnodok() {
  document.getElementById("nopo").disabled = true;
  document.getElementById("buttonsearchnodok").disabled = true;
  nopo = document.getElementById("nopo").value;
  tipeinvoice = document.getElementById("tipeinvoice").value;

  if (nopo != "") {
    document.getElementById("nopo").value = "";
  }

  if (tipeinvoice == "ot" || tipeinvoice == "um") {
    document.getElementById("nopo").disabled = false;
    document.getElementById("buttonsearchnodok").disabled = true;
    document.getElementById("supplier").disabled = false;
    // Hapus Attribute Onclick
    document.getElementById("nopo").removeAttribute("onclick");
  } else {
    document.getElementById("nopo").disabled = true;
    document.getElementById("buttonsearchnodok").disabled = false;
    document.getElementById("supplier").disabled = true;
  }

  if (
    tipeinvoice == "ot" ||
    tipeinvoice == "spks" ||
    tipeinvoice == "sip" ||
    tipeinvoice == "um"
  ) {
    document.getElementById("nilaidpp").disabled = false;
    document.getElementById("nilaiinvoice").disabled = false;
    document.getElementById("supplier").disabled = false;
  } else {
    document.getElementById("nilaidpp").disabled = true;
    document.getElementById("nilaiinvoice").disabled = true;
    document.getElementById("supplier").disabled = true;
  }
}

/*

function searchNopo(title,ev,langCari) {
    kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
    unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
    isi=document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].value;
    tipe=document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].text;
    tanggal=document.getElementById('tanggal').value;
	
    keterangan2=document.getElementById('keterangan2').value;
    supplier=document.getElementById('supplier').value;
    jenissupplier=document.getElementById('jenissupplier').value;
	
    if(kodeorg==''){
        alertify.alert("Informasi","PT Tidak Boleh Kosong");
        return;
    }
    if(unit==''){
        alertify.alert("Informasi","Unit Tidak Boleh Kosong");
        return;
    }
    if(isi==''){
        alertify.alert("Informasi","Jenis PO Tidak Boleh Kosong");
        return;
    }
    if (tanggal == '') {
        alertify.alert("Informasi",notiftagihtanggal);
        return;
    }
    cekDtPo(langCari, title, ev);
}


function searchNopo(title,ev,langCari) {
    kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
    unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
    isi=document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].value;
    tipe=document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].text;
    tanggal=document.getElementById('tanggal').value;
	
    keterangan2=document.getElementById('keterangan2').value;
    supplier=document.getElementById('supplier').value;
    jenissupplier=document.getElementById('jenissupplier').value;
	
    if(kodeorg==''){
        alertify.alert("Informasi","PT Tidak Boleh Kosong");
        return;
    }
    if(unit==''){
        alertify.alert("Informasi","Unit Tidak Boleh Kosong");
        return;
    }
    if(isi==''){
        alertify.alert("Informasi","Jenis PO Tidak Boleh Kosong");
        return;
    }
    if (tanggal == '') {
        alertify.alert("Informasi",notiftagihtanggal);
        return;
    }
    cekDtPo(langCari, title, ev);
}

function cekDtPo(langCari,title,ev) {
    jnsInvoice=document.getElementById('tipeinvoice').value;
    tanggal=document.getElementById('tanggal').value;
    param='jnsInvoice=' + jnsInvoice;
    tujuan='keu_slave_tagihanv2.php';
    post_response_text(tujuan + '?' + 'proses=cekData', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    if (parseInt(con.responseText) != 0) {
                        doc="No. ";
                        content="<fieldset><legend>" + langCari + " " + tipe + "</legend>";
                        // content="<fieldset><legend>" + langCari + " " + tipe + "</legend>" + langCari +
                            // " " + doc + "<input type=text class=myinputtext id=no_brg>";
                         
						content+="<table>";	
						content+="<tr>";	
						content+="<td>" + langCari + " "+ doc +"</td>";	
							content+="<td>:</td>";	
							content+="<td><input type=text class=myinputtext id=no_brg></td>";	
						content+="</tr>";
						
						content+="<tr>";	
							content+="<td>Tanggal</td>";	
							content+="<td>:</td>";	
							content+="<td><input type=text class=myinputtext id=tglcariinv1 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:75px; maxlength=10 />";	
							content+="s/d <input type=text class=myinputtext id=tglcariinv2 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:75px; maxlength=10 /></td>";							
						content+="</tr>";
						
						contentjenis='';
						if(jnsInvoice=='spks'){
							contentjenis+="<tr>";	
							contentjenis+="<td>SPK</td>";	
							contentjenis+="<td>:</td>";	
							contentjenis+="<td>";	
						
						}else{
							content+="</table>";
						}
						contentjenis+="<select id=jeniscari style='width:150px'>";
						contentjenis+="<option value='etc'>Lain-Lain</option>";
						contentjenis+="<option value='ipk'>Instruksi Pemuatan Kargo</option>";
						contentjenis+="<option value='ipd'>Instruksi Pemuatan Kargo Darat</option>";
						contentjenis+="<option value='sda'>Sampling dan Analisa</option>";
						contentjenis+="<option value='sp'>Surat Pemberitahuan Pengiriman Antar Pulau</option>";
						contentjenis+="<option value='spp'>Surat Permintaan Ponton</option>";
						contentjenis+="<option value='sub'>Surveyor Bongkar</option>";
						contentjenis+="<option value='sum'>Surveyor Pemuatan</option>";
						contentjenis+="<option value='tkbm'>Tenaga Kerja Bongkar Muat</option>";
						contentjenis+="<option value='sip'>Surat Instruksi Pengiriman</option></select>";
						contentjenis+="</td>";							
							content+="</tr>";
						if (jnsInvoice == 'spks') {
							 contentjenis+="</table>";
							content=content + contentjenis + "<button class=mybutton onclick=findNopo()>Find</button></fieldset><div id=container2></div>";
							
						} else {
							
                            content=content + "<button class=mybutton onclick=findNopo()>Find</button></fieldset><div id=container2></div>";
                        }
						
						
                        content=content + "<input type='hidden' id='jnsInvoice' value=" + isi + ">";
                        width='';
                        height='400';
                        showDialog1(title + tipe, content, width, height, ev);
                        findNopo();
                    } else {
                        // document.getElementById('nopo').value='';
                        // document.getElementById('nopo').disabled=true;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}




bersihin


*/

function searchNopo(title, ev, langCari) {
  kodeorg =
    document.getElementById("kodeorg").options[
      document.getElementById("kodeorg").selectedIndex
    ].value;
  unit =
    document.getElementById("unit").options[
      document.getElementById("unit").selectedIndex
    ].value;
  tipeinvoice =
    document.getElementById("tipeinvoice").options[
      document.getElementById("tipeinvoice").selectedIndex
    ].value;
  tipe =
    document.getElementById("tipeinvoice").options[
      document.getElementById("tipeinvoice").selectedIndex
    ].text;
  tanggal = document.getElementById("tanggal").value;

  keterangan = document.getElementById("keterangan").value;
  supplier = document.getElementById("supplier").value;
  jenissupplier = document.getElementById("jenissupplier").value;
  if (tipeinvoice != "ot") {
    if (kodeorg == "") {
      alertify.alert("Informasi", "PT Tidak Boleh Kosong");
      return;
    }
    if (unit == "") {
      alertify.alert("Informasi", "Unit Tidak Boleh Kosong");
      return;
    }
    if (tipeinvoice == "") {
      alertify.alert("Informasi", "Jenis Tagihan Tidak Boleh Kosong");
      return;
    }
    if (tanggal == "") {
      alertify.alert("Informasi", notiftagihtanggal);
      return;
    }
    doc = "No. ";
    content = "<fieldset><legend>" + langCari + " " + tipe + "</legend>";
    // content="<fieldset><legend>" + langCari + " " + tipe + "</legend>" + langCari +
    // " " + doc + "<input type=text class=myinputtext id=no_brg>";

    content += "<table>";
    content += "<tr>";
    content += "<td>" + langCari + " " + doc + "</td>";
    content += "<td>:</td>";
    content += "<td><input type=text class=myinputtext id=no_brg></td>";
    content += "</tr>";

    content += "<tr>";
    content += "<td>Tanggal</td>";
    content += "<td>:</td>";
    content +=
      "<td><input type=text class=myinputtext id=tglcariinv1 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:75px; maxlength=10 />";
    content +=
      "s/d <input type=text class=myinputtext id=tglcariinv2 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:75px; maxlength=10 /></td>";
    content += "</tr>";

    contentjenis = "";
    if (tipeinvoice == "spks") {
      contentjenis += "<tr>";
      contentjenis += "<td>SPK</td>";
      contentjenis += "<td>:</td>";
      contentjenis += "<td>";
    } else {
      content += "</table>";
    }
    contentjenis += "<select id=jeniscari style='width:150px'>";
    contentjenis += "<option value='etc'>Lain-Lain</option>";
    // contentjenis+="<option value='ipk'>Instruksi Pemuatan Kargo</option>";
    // contentjenis+="<option value='ipd'>Instruksi Pemuatan Kargo Darat</option>";
    // contentjenis+="<option value='sda'>Sampling dan Analisa</option>";
    // contentjenis+="<option value='sp'>Surat Pemberitahuan Pengiriman Antar Pulau</option>";
    // contentjenis+="<option value='spp'>Surat Permintaan Ponton</option>";
    contentjenis += "<option value='sub'>Surveyor Bongkar</option>";
    contentjenis += "<option value='sum'>Surveyor Pemuatan</option>";
    // contentjenis+="<option value='tkbm'>Tenaga Kerja Bongkar Muat</option>";
    // contentjenis+="<option value='sip'>Surat Instruksi Pengiriman</option></select>";
    contentjenis += "</td>";
    content += "</tr>";
    if (tipeinvoice == "spks") {
      contentjenis += "</table>";
      content =
        content +
        contentjenis +
        "<button class=mybutton onclick=findNopo()>Find</button></fieldset><div id=container2></div>";
    } else {
      content =
        content +
        "<button class=mybutton onclick=findNopo()>Find</button></fieldset>";
      content +=
        "<div id=container2  style=overflow:auto;max-width:800px;max-height:300px;></div><div id=containerdetail2></div>";
      content +=
        "<div id=container2detailtermin  style=overflow:auto;max-width:800px;max-height:300px;></div>";
    }
    content =
      content +
      "<input type='hidden' id='jnsInvoice' value=" +
      tipeinvoice +
      ">";
    width = "";
    height = "400";
    showDialog1(title + tipe, content, width, height, ev);
    findNopo();
  }
}

function detailtermin(nopo, xxx) {
  txt = trim(document.getElementById("no_brg").value);
  jnsInvoice = document.getElementById("tipeinvoice").value;
  tanggal = document.getElementById("tanggal").value;
  unit = document.getElementById("unit").value;
  kodeorg = document.getElementById("kodeorg").value;

  tglcariinv1 = document.getElementById("tglcariinv1").value;
  tglcariinv2 = document.getElementById("tglcariinv2").value;
  supplier = document.getElementById("supplier").value;

  param = "nopo=" + nopo + "&xxx=" + xxx;
  // param+='&tglcariinv1=' + tglcariinv1 + '&tglcariinv2=' + tglcariinv2 + '&supplier=' + supplier;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan + "?" + "proses=detailtermin", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("container2detailtermin").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findNopo() {
  txt = trim(document.getElementById("no_brg").value);
  jnsInvoice = document.getElementById("tipeinvoice").value;
  tanggal = document.getElementById("tanggal").value;
  unit = document.getElementById("unit").value;
  kodeorg = document.getElementById("kodeorg").value;

  tglcariinv1 = document.getElementById("tglcariinv1").value;
  tglcariinv2 = document.getElementById("tglcariinv2").value;
  supplier = document.getElementById("supplier").value;

  param =
    "txtfind=" +
    txt +
    "&jnsInvoice=" +
    jnsInvoice +
    "&tanggal=" +
    tanggal +
    "&unit=" +
    unit +
    "&kodeorg=" +
    kodeorg;
  param +=
    "&tglcariinv1=" +
    tglcariinv1 +
    "&tglcariinv2=" +
    tglcariinv2 +
    "&supplier=" +
    supplier;
  if (jnsInvoice == "spks") {
    jeniscari = document.getElementById("jeniscari");
    jeniscari = jeniscari.options[jeniscari.selectedIndex].value;
    param += "&jeniscari=" + jeniscari;
  }
  tujuan = "keu_slave_getpotagihan.php";
  post_response_text(tujuan + "?" + "proses=getPo", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("container2").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function numberFormat(number, digit) {
  number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
  //Seperates the components of the number
  var components = parseFloat(number).toFixed(digit).split(".");
  //Comma-fies the first part
  components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  //Combines the two sections
  return components.join(".");
}

function setdatanodok(
  nopo,
  supplier,
  nilaidpp,
  nilaiinvoice,
  matauang,
  kurs,
  reksupplier,
  jenissupplier,
  keterangan,
  tanggal,
  nosj
) {
  document.getElementById("nopo").value = nopo;
  document.getElementById("nilaiinvoice").value = nilaiinvoice;
  document.getElementById("nilaidpp").value = nilaidpp;
  document.getElementById("supplier").value = supplier;
  document.getElementById("matauang").value = matauang;
  document.getElementById("kurs").value = kurs;
  document.getElementById("keterangan").value = keterangan;
  document.getElementById("tanggal").value = tanggal;
  document.getElementById("nosj").value = nosj;
  alertify.popup().destroy();
  getrek(supplier, reksupplier, jenissupplier);
  // (np,nilai,jns,ppn,namasupplier,noakun,untdt,matauang,kurs,notransaksi_gr,termin,tanggalterima,keterangan2,nilaidpp){
}

/*
function setPo(np,nilai,jns,ppn,namasupplier,noakun,untdt,matauang,kurs,notransaksi_gr,termin,tanggalterima,keterangan2,nilaidpp){
    document.getElementById('keterangan2').value=keterangan2;
    document.getElementById('tanggalinvoice').value=tanggalterima;
    document.getElementById('nopo').value=np;
    document.getElementById('nilaiinvoice').value=(nilai);
    document.getElementById('nilaidpp').value=(nilaidpp);
    // document.getElementById('noakun').value=noakun;
    document.getElementById('notransaksi_gr').value=notransaksi_gr;
    document.getElementById('termin').value=termin;
    document.getElementById('tipeinvoice').disabled=false;
    jk=document.getElementById('supplier');
    for (x=0; x < jk.length; x++) {
        if (jk.options[x].value == namasupplier) {
            jk.options[x].selected=true;
        }
    }
    jkunit=document.getElementById('unit');
    for (x=0; x < jkunit.length; x++) {
        if (untdt != '') {
            if (jkunit.options[x].value == untdt) {
                jkunit.options[x].selected=true;
            }
        }
    }

    if (typeof matauang != 'undefined') {
        document.getElementById('matauang').value=matauang;
    }
    if (typeof kurs != 'undefined') {
        document.getElementById('kurs').value=kurs;
    }
    closeDialog();
    getrek(namasupplier,0,0);
}
*/

/*
function addDataTable(nomor) {
    
    // var file=document.getElementById("upload").files[0];
    var formdata=new FormData();
    // formdata.append("file", file);
    // formdata.append("fileupload", getValue('upload'));
    formdata.append("noinvoice", getValue('noinvoice'));
    formdata.append("noinvoicesupplier", getValue('noinvoicesupplier'));
    formdata.append("tanggal", getValue('tanggal'));
    formdata.append("tipeinvoice", getValue('tipeinvoice'));
    formdata.append("nopo", getValue('nopo'));
    formdata.append("jenistransaksi", getValue('jenistransaksi'));
    formdata.append("nilaiinvoice", getValue('nilaiinvoice'));
    formdata.append("nilaidpp", getValue('nilaidpp'));
    formdata.append("jatuhtempo", getValue('jatuhtempo'));
    formdata.append("nofp", getValue('nofp'));
    formdata.append("noakun", getValue('noakun'));
    formdata.append("uangmuka", getValue('uangmuka'));
    formdata.append("kodesupplier", getValue('supplier'));
    formdata.append("matauang", getValue('matauang'));
    formdata.append("kurs", getValue('kurs'));
    formdata.append("keterangan2", getValue('keterangan2'));
    formdata.append("bagian", getValue('bagian'));
    formdata.append("tanggalinvoice", getValue('tanggalinvoice'));
    formdata.append("tanggalnofp", getValue('tanggalnofp'));
    formdata.append("npwp", getValue('npwp'));
    formdata.append("npwppph", getValue('npwppph'));
    formdata.append("kodeorg", getValue('kodeorg'));
    formdata.append("unit", getValue('unit'));
    formdata.append("reksupplier", getValue('reksupplier'));
    formdata.append("notransaksi_gr", getValue('notransaksi_gr'));
    formdata.append("termin", getValue('termin'));
    formdata.append("jenissupplier", getValue('jenissupplier'));
    formdata.append("proses", getValue('proses'));
	
	tipeinvoice=document.getElementById('tipeinvoice').value;
	
	if(tanggal==''){
		alertify.alert("Informasi",'Tanggal invoice masih kosong');return;
	}
	
    var con=createXMLHttpRequest();
    con.open("POST", "keu_slave_tagihanv2.php?", true);
    con.onreadystatechange=eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('noinvoice').value=con.responseText;
                    alertify.alert("Informasi",'Added Data Header');
					
					// if(tipeinvoice=='ffb' || tipeinvoice=='ffbe' || tipeinvoice=='ffba'){
						// saveall(nomor);
					// }else{
						// // redirectefill(con.responseText);
						// showDetail();
					// }
					
					showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
*/

function redirectefill(noinvoice) {
  // var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value');
  // kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
  // noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
  // tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');

  param = "method=insertefill&noinvoice=" + noinvoice;
  tujuan = "log_slave_efill.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function editdt(
  noinvoice,
  noinvoicesupplier,
  tanggal,
  jatuhtempo,
  kodeorg,
  unit,
  tipeinvoice,
  nopo,
  kodesupplier,
  nofp,
  jenistransaksi,
  tanggalinvoice,
  tanggalnofp,
  nilaidpp,
  nilaiinvoice,
  keterangan,
  noakun,
  matauang,
  kurs,
  npwp,
  npwppph,
  reksupplier,
  jenissupplier,
  bagian,
  tipearuskasht
) {
  document.getElementById("kodeorg").value = kodeorg;
  document.getElementById("kodeorg").disabled = true;
  document.getElementById("unit").value = unit;
  document.getElementById("unit").disabled = true;
  document.getElementById("noinvoice").value = noinvoice;
  document.getElementById("noakun").value = noakun;
  document.getElementById("noinvoicesupplier").value = noinvoicesupplier;
  document.getElementById("tanggal").value = tanggal;
  document.getElementById("jatuhtempo").value = jatuhtempo;
  document.getElementById("keterangan").value = keterangan;
  document.getElementById("bagian").value = bagian;
  document.getElementById("tipeinvoice").value = tipeinvoice;
  document.getElementById("tipeinvoice").disabled = true;

  document.getElementById("tipearuskasht").value = tipearuskasht;
  document.getElementById("tipearuskashtold").value = tipearuskasht;

  if (tipeinvoice == "ffb") {
    document.getElementById("nopo").value = nopo;
    // document.getElementById('nopo').disabled=true;
  } else {
    document.getElementById("nopo").value = nopo;
    document.getElementById("nopo").disabled = true;
  }

  document.getElementById("supplier").value = kodesupplier;
  document.getElementById("supplier").disabled = true;
  document.getElementById("matauang").value = matauang;
  document.getElementById("matauang").disabled = true;
  document.getElementById("kurs").value = kurs;
  document.getElementById("kurs").disabled = true;
  document.getElementById("nofp").value = nofp;
  document.getElementById("jenistransaksi").value = jenistransaksi;
  document.getElementById("tanggalinvoice").value = tanggalinvoice;
  document.getElementById("tanggalnofp").value = tanggalnofp;
  document.getElementById("nilaiinvoice").value = nilaiinvoice;
  document.getElementById("nilaidpp").value = nilaidpp;
  setValue2("npwp", npwp);
  setValue2("npwppph", npwppph);
  document.getElementById("npwp").disabled = true;
  document.getElementById("npwppph").disabled = true;
  document.getElementById("proses").value = "edit";
  document.getElementById("formInput").style.display = "block";
  document.getElementById("listData").style.display = "none";
  getrek(kodesupplier, reksupplier, jenissupplier);
}

function deleteht(noinvoice) {
  param = "proses=delete" + "&noinvoice=" + noinvoice;
  tujuan = "keu_slave_tagihanv2.php";
  // if(confirm(' Anda yakin ingin menghapus data ini?'))
  // {
  //     post_response_text(tujuan, param, respog);
  // }
  alertify.confirm(
    "Informasi",
    "Anda yakin ingin menghapus data ini?",
    function () {
      post_response_text(tujuan, param, respog);
    },
    function () {
      return;
    }
  );
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          loadData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postingData___(noinvoice) {
  ev = "event";
  title = "Pencarian";
  content = '<div id=formbayar style="height:100%;width:100%;"></div>';
  width = "";
  height = "";
  // showDialog4(title, content, width, height, ev);
  param = "proses=showformposting&noinvoice=" + noinvoice;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('formpencariannodok').innerHTML=con.responseText;
          alertify
            .popup("Detail", con.responseText)
            .set({ resizable: true, maximizable: true })
            .resizeTo("40%", "40%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function postingData(noinvoice) {
  // minta tanggal dulu ke atas. KAGAK JADI: vienny + sabi 20211117
  // document.getElementById('tomblo').disabled=true;
  // tanggaljurnal=document.getElementById('tanggaljurnal').value;
  // param = 'noinvoice=' + noinvoice+'&tanggaljurnal='+tanggaljurnal;
  param = "noinvoice=" + noinvoice;
  tujuan = "keu_slave_tagihanPosting.php";
  if (confirm(notifpostingpenagihan))
    post_response_text(tujuan + "?" + "proses=getPo", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // redirectefill2(noinvoice);
          // alertify.alert("Informasi",'Done');
          pg = document.getElementById("pages");
          pg = pg.options[pg.selectedIndex].value;
          getPage(pg);
          // closeDialog();
          // closeDialog4();
          alertify.popup().destroy();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function redirectefill2(noinvoice) {
  // var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value');
  // kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
  // noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
  // tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');

  param = "method=insertefill&noinvoice=" + noinvoice;
  tujuan = "log_slave_efill.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          pg = document.getElementById("pages");
          pg = pg.options[pg.selectedIndex].value;
          getPage(pg);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function postingDatalaporan(noinvoice) {
  param = "noinvoice=" + noinvoice;
  tujuan = "keu_slave_tagihanPosting.php";
  if (confirm(notifpostingpenagihan))
    post_response_text(tujuan + "?" + "proses=getPo", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          pg = document.getElementById("pages");
          pg = pg.options[pg.selectedIndex].value;
          getPage(pg);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function detailPDF(noinvoice, ev) {
  param = "proses=pdf&noinvoice=" + noinvoice;
  // showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
  //     " src='keu_slave_tagihan_print_detail.php?" + param + "'></iframe>", '', '', ev);
  // var dialog = document.getElementById('dynamic1');
  // dialog.style.top = '50px';
  // dialog.style.left = '15%';

  alertify
    .popuppdf(
      "Print PDF",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_slave_tagihan_print_detail.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function showDetailData(noinvoice) {
  width = "500px";
  height = "450px";
  content = "<div id=containerData></div>";
  ev = "event";
  title = noinvoice;
  showDialog1(title, content, width, height, ev);
}

function pdf3(notransaksi) {
  param = "method=pdf3" + "&notransaksi=" + notransaksi;
  tujuan = "kebun_tbskud_slave.php";
  tujuan = tujuan + "?" + param;
  // // content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
  // // width = '820';
  // // height = '500';
  // // title = "List Petani "+ notransaksi;
  // showDialog5(title, content, width, height, 'event');

  alertify
    .popuppdf(
      "",
      "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" +
        tujuan +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

function viewDetailData2(noinvoice, ev) {
  // Prep Param
  param = "noinv=" + noinvoice + "&proses=getDetail";
  // showDetailData(noinvoice);
  tujuan = "keu_slave_2tagihan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          // document.getElementById('containerData').innerHTML = con.responseText;
          title = noinvoice;
          // alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='keu_slave_2tagihan.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
          // alertify.popup(title,con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
          alertify
            .popup(title, con.responseText)
            .set({ resizable: true, overflow: true })
            .resizeTo("80%", "80%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function fakturpajak(noinvoice, ev) {
  alertify.closeAll();
  content = "<div id=formpost ></div>";
  title = "Faktur Pajak";
  height = "auto";
  width = "auto";
  // showDialog2(title, content, width, height,ev);
  alertify
    .popup(title, content)
    .set({ resizable: false, maximizable: false, overflow: false })
    .resizeTo("200px", "10px");
  getformfp(noinvoice);
}

function getformfp(noinvoice) {
  var param = "noinvoice=" + noinvoice;
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("formpost").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(
    "keu_slave_tagihanv2.php?proses=showformfp",
    param,
    respon
  );
}

function savefp(noinvoice) {
  historynofp = document.getElementById("historynofp").value;
  historytanggalfp = document.getElementById("historytanggalfp").value;
  param =
    "noinvoice=" +
    noinvoice +
    "&historynofp=" +
    historynofp +
    "&historytanggalfp=" +
    historytanggalfp;

  if (historynofp == "") {
    alertify.alert("Informasi", "Factur Number must be filled");
    return;
  }
  if (historytanggalfp == "") {
    alertify.alert("Informasi", "Date must be filled");
    return;
  }
  post_response_text("keu_slave_tagihanv2.php?proses=savefp", param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //=== Success Response
          //alertify.alert("Informasi",'Posting Berhasil');
          closeDialog2();
          loadData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showDetail() {
  unit = trim(document.getElementById("unit").value);
  tipeinvoice = trim(document.getElementById("tipeinvoice").value);
  noinvoice = trim(document.getElementById("noinvoice").value);
  tipearuskasht = trim(document.getElementById("tipearuskasht").value);
  tipearuskashtold = trim(document.getElementById("tipearuskashtold").value);
  param =
    "unit=" +
    unit +
    "&proses=showDetail" +
    "&tipeinvoice=" +
    tipeinvoice +
    "&noinvoice=" +
    noinvoice;

  param +=
    "&tipearuskasht=" + tipearuskasht + "&tipearuskashtold=" + tipearuskashtold;
  post_response_text("keu_slave_tagihanv2.php", param, respon);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // === Success Response
          // alertify.alert("Informasi",con.responseText);
          document.getElementById("detailField").style.display = "block";
          document.getElementById("detailField").innerHTML = con.responseText;

          document.getElementById("tipearuskashtold").value = tipearuskasdt;
          // getdate30();
          loadfiles();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function saveDetail() {
  noinvoice = trim(document.getElementById("noinvoice").value);
  kodevhc = trim(document.getElementById("kodevhc").value);
  kodeasset = trim(document.getElementById("kodeasset").value);
  noakundt = trim(document.getElementById("noakundt").value);
  nilai = trim(document.getElementById("nilai").value);
  proses = trim(document.getElementById("prosesdt").value);
  tipeinvoice = trim(document.getElementById("tipeinvoice").value);
  noaruskas = trim(document.getElementById("noaruskas").value);
  keterangandt = document.getElementById("keterangandt").value;
  hisnoakun = document.getElementById("hisnoakun").value;
  hisnoaruskas = document.getElementById("hisnoaruskas").value;
  nourut = document.getElementById("nourut").value;
  pajak = document.getElementById("pajak").value;
  kodeblok = document.getElementById("kodeblokdt").value;
  kodeblokold = document.getElementById("kodeblokdtold").value;
  nopo = document.getElementById("nopo").value;
  kegiatan = document.getElementById("kegiatandt").value;
  tipearuskasdt = document.getElementById("tipearuskasdt").value;

  param =
    "kodevhc=" +
    kodevhc +
    "&noinvoice=" +
    noinvoice +
    "&kodeasset=" +
    kodeasset +
    "&proses=" +
    proses +
    "&noakun=" +
    noakundt;
  param +=
    "&nilai=" +
    nilai +
    "&tipeinvoice=" +
    tipeinvoice +
    "&noaruskas=" +
    noaruskas +
    "&keterangan=" +
    keterangandt;
  param +=
    "&hisnoakun=" +
    hisnoakun +
    "&hisnoaruskas=" +
    hisnoaruskas +
    "&nourut=" +
    nourut +
    "&pajak=" +
    pajak;
  param += "&kodeblok=" + kodeblok + "&nopo=" + nopo + "&kegiatan=" + kegiatan;
  param += "&kodeblokold=" + kodeblokold;
  param += "&tipearuskasdt=" + tipearuskasdt;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // nilai=parseFloat(con.responseText);
          // alertify.alert("Informasi",con.responseText);
          // alertify.alert("Informasi",nilai);
          nilai = parseFloat(con.responseText);
          // alertify.alert("Informasi",con.responseText);
          // alertify.alert("Informasi",nilai);
          if (nilai > 0) {
            document.getElementById("nilaiinvoice").value = nilai;
            // alertify.alert("Informasi",con.responseText);
          }

          cleardetail();
          showDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cleardetail() {
  document.getElementById("kodevhc").value = "";
  document.getElementById("kodeasset").value = "";
  document.getElementById("noakundt").value = "";
  document.getElementById("nilai").value = "";
  document.getElementById("keterangandt").value = "";
  document.getElementById("noaruskas").value = "";
  document.getElementById("hisnoakun").value = "";
  document.getElementById("hisnoaruskas").value = "";
  document.getElementById("pajak").value = "";
  document.getElementById("kodeblokdt").value = "";
  document.getElementById("kodeblokdtold").value = "";
  document.getElementById("prosesdt").value = "insertdt";
  showDetail();
}

function updatedt(
  kodevhc,
  kodeasset,
  noakun,
  nilai,
  noaruskas,
  keterangan,
  noinv_ref,
  nourut,
  pajak,
  kodeblok,
  kodekegiatan
) {
  document.getElementById("kodevhc").value = kodevhc;
  document.getElementById("kodeasset").value = kodeasset;
  document.getElementById("noakundt").value = noakun;
  document.getElementById("hisnoakun").value = noakun;
  document.getElementById("nilai").value = nilai;
  document.getElementById("noaruskas").value = noaruskas;
  document.getElementById("hisnoaruskas").value = noaruskas;
  document.getElementById("keterangandt").value = keterangan;
  document.getElementById("prosesdt").value = "updatedt";
  document.getElementById("noinv_ref").value = noinv_ref;
  document.getElementById("nourut").value = nourut;
  document.getElementById("pajak").value = pajak;
  document.getElementById("kodeblokdt").value = kodeblok;
  document.getElementById("kodeblokdtold").value = kodeblok;

  // Mengambil option select kegiatan
  param = "noakun=" + noakun + "&proses=getkegiatan";
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          document.getElementById("kegiatandt").innerHTML = con.responseText;
          document.getElementById("kegiatandt").value = kodekegiatan;

          // Mengambil option select blok
          // param2 = "kegiatan=" + kodekegiatan + "&proses=getblok";
          param2 = "kegiatan=" + kodekegiatan + "&proses=getblokbesar";
          tujuan2 = "keu_slave_tagihanv2.php";
          post_response_text(tujuan2, param2, respog2);
          function respog2() {
            if (con.readyState == 4) {
              if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                  alertify.alert("Informasi", con.responseText);
                } else {
                  //alertify.alert("Informasi",con.responseText);
                  document.getElementById("kodeblokdt").innerHTML =
                    con.responseText;
                  document.getElementById("kodeblokdt").value = kodeblok;

                  document.getElementById("kodeblokdtold").innerHTML =
                    con.responseText;
                  document.getElementById("kodeblokdtold").value = kodeblok;

                  getnoakun(noakun, keterangan);
                }
              } else {
                busy_off();
                error_catch(con.status);
              }
            }
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
// function deletedt(noinvoice,noakun,noaruskas,kodevhc,noinvoicesupplier,nourut)
function deletedt(noinvoice, nourut, notransaksi, nopo, noakun, indukblok) {
  param =
    "proses=deletedt" +
    "&noinvoice=" +
    noinvoice +
    "&nourut=" +
    nourut +
    "&notransaksi=" +
    notransaksi +
    "&nopo=" +
    nopo +
    "&noakun=" +
    noakun +
    "&indukblok=" +
    indukblok;

  tujuan = "keu_slave_tagihanv2.php";
  if (confirm(" Anda yakin ingin menghapus data ini?")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // nilai=con.responseText.split("####");
          // if (parseFloat(nilai[0])>=0) {

          //     document.getElementById('nilaidpp').value=nilai[0];
          // }
          // if (parseFloat(nilai[1])>=0) {

          //     document.getElementById('nilaiinvoice').value=nilai[1];
          // }
          showDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showhideinfo() {
  var row = document.getElementById("forminfo");
  if (row !== null) {
    if (row.style.display == "") {
      row.style.display = "none";
    } else {
      row.style.display = "";
    }
  }
}

function loadData(num) {
  // document.getElementById('formInput').style.display='none';
  // document.getElementById('listData').style.display='block';
  // document.getElementById('detailField').style.display='none';

  noinvoice = document.getElementById("noinvoicesch").value;
  noinvoicesupplier = document.getElementById("noinvoicesuppliersch").value;
  nopo = document.getElementById("noposch").value;
  tipeinvoice = document.getElementById("tipeinvoicesch").value;
  kodesupplier = document.getElementById("kodesuppliersch").value;
  unit = document.getElementById("unitsch").value;
  tanggalmulai = document.getElementById("tanggalmulaisch").value;
  tanggalselesai = document.getElementById("tanggalselesaisch").value;
  posting = document.getElementById("postingsch").value;
  param = "proses=loadData&page=" + num;
  param +=
    "&noinvoice=" +
    noinvoice +
    "&noinvoicesupplier=" +
    noinvoicesupplier +
    "&nopo=" +
    nopo;
  param +=
    "&tipeinvoice=" +
    tipeinvoice +
    "&kodesupplier=" +
    kodesupplier +
    "&unit=" +
    unit;
  param +=
    "&tanggalmulai=" +
    tanggalmulai +
    "&tanggalselesai=" +
    tanggalselesai +
    "&posting=" +
    posting;
  // alertify.alert("Informasi",param);

  // param='proses=loadData';
  // param+='&page='+num;

  // if (sJenis != '') {
  // param += '&sJenis=' + sJenis;
  // }
  // if (sNoTrans != '') {
  // param += '&sNoTrans=' + sNoTrans;
  // }
  // if (ssupplier != '') {
  // param += '&supplier=' + ssupplier;
  // }
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          leftFixedTable();
          document.getElementById("listData").style.display = "block";
          document.getElementById("formInput").style.display = "none";
          document.getElementById("detailField").style.display = "none";

          isdt = con.responseText.split("####");
          document.getElementById("continerlist").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function xloadDataawal(num) {
  sJenis = document.getElementById("sJenis").value;
  sNoTrans = document.getElementById("sNoTrans").value;
  ssupplier = document.getElementById("ssupplier").value;

  param = "proses=loadData";
  param += "&page=" + num;

  if (sJenis != "") {
    param += "&sJenis=" + sJenis;
  }
  if (sNoTrans != "") {
    param += "&sNoTrans=" + sNoTrans;
  }
  if (ssupplier != "") {
    param += "&supplier=" + ssupplier;
  }

  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          isdt = con.responseText.split("####");
          document.getElementById("continerlist").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function xloadData(num) {
  // clearData();

  // formInput = document.getElementById('formInput');
  // if(formInput!=''){
  document.getElementById("formInput").style.display = "none";
  // }

  document.getElementById("listData").style.display = "block";

  document.getElementById("detailField").style.display = "none";

  sJenis = document.getElementById("sJenis").value;
  sNoTrans = document.getElementById("sNoTrans").value;
  ssupplier = document.getElementById("ssupplier").value;

  param = "proses=loadData";
  param += "&page=" + num;

  if (sJenis != "") {
    param += "&sJenis=" + sJenis;
  }
  if (sNoTrans != "") {
    param += "&sNoTrans=" + sNoTrans;
  }
  if (ssupplier != "") {
    param += "&supplier=" + ssupplier;
  }

  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          isdt = con.responseText.split("####");
          document.getElementById("continerlist").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getPage(pg) {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loadData(paged);
  // cariBast(pg-1);
}

function addum(title, content, ev) {
  width = "auto";
  height = "auto";
  showDialog1(title, content, width, height, ev);
  getformum();
}

function getformum() {
  supplier = trim(document.getElementById("supplier").value);
  param = "proses=getformum" + "&supplier=" + supplier;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("formPencarianum").innerHTML =
            con.responseText;
          findum();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findum() {
  supplier = trim(document.getElementById("supplier").value);
  param = "proses=getdataum" + "&supplier=" + supplier;
  transum = trim(document.getElementById("transum").value);
  param += "&transum=" + transum;

  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("containerum").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getdatadt(noinvoiceum, noakunum, nilaium, noaruskasum, keteranganum) {
  noinvoice = trim(document.getElementById("noinvoice").value);
  param =
    "proses=saveum" +
    "&noinvoice=" +
    noinvoice +
    "&noinvoiceum=" +
    noinvoiceum +
    "&noakun=" +
    noakunum +
    "&nilai=" +
    nilaium +
    "&noaruskas=" +
    noaruskasum +
    "&keterangan=" +
    keteranganum;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showDetail();
          closeDialog();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function addToDetail(rowdt) {
  var supp = 0;
  var suppid = "";
  var totRpAll = 0;
  var allKirim = "";
  for (awal = 1; awal <= rowdt; awal++) {
    ckbox = document.getElementById("pph22_" + awal);
    if (ckbox.checked == true) {
      if (awal == 1) {
        allKirim +=
          "&suppId[]=" + document.getElementById("suppId_" + awal).value;
        allKirim +=
          "&noInv[]=" + document.getElementById("noinv_" + awal).value;
        allKirim +=
          "&nilaiRp[]=" + document.getElementById("nilaiId_" + awal).value;
      } else {
        allKirim +=
          "&suppId[]=" + document.getElementById("suppId_" + awal).value;
        allKirim +=
          "&noInv[]=" + document.getElementById("noinv_" + awal).value;
        allKirim +=
          "&nilaiRp[]=" + document.getElementById("nilaiId_" + awal).value;
      }
      totRpAll += parseFloat(document.getElementById("nilaiId_" + awal).value);
      supp += 1;
    }
  }
  if (supp == 0) {
    alertify.alert("Informasi", bahasa.datakosong);
    return;
  }
  param =
    "proses=saveHutang" +
    "&noinvoice=" +
    getValue("noinvoice") +
    "&noinvoicesupplier=" +
    getValue("noinvoicesupplier");
  param +=
    "&tanggal=" +
    getValue("tanggal") +
    "&tipeinvoice=" +
    getValue("tipeinvoice") +
    "&unit=" +
    getValue("unit") +
    "&kodeorg=" +
    getValue("kodeorg") +
    "&totRpAll=" +
    totRpAll;
  param +=
    "&jatuhtempo=" +
    getValue("jatuhtempo") +
    "&npwp=" +
    getValue("npwp") +
    "&npwppph=" +
    getValue("npwppph") +
    "&suppIdHtg=" +
    getValue("suppIdHtg") +
    "&noakundetail=" +
    getValue("noakundetail");
  param += allKirim;
  //alertify.alert("Informasi",param);
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //echo $tempSupp."####".$totNilRp."####".$rNoakun[0]['noakun']."####IDR####1";
          balikandt = con.responseText.split("####");
          suppId = document.getElementById("supplier");
          for (a = 0; a < suppId.length; a++) {
            if (suppId.options[a].value == balikandt[0]) {
              suppId.options[a].selected = true;
            }
          }
          document.getElementById("nilaiinvoice").value = balikandt[1];
          document.getElementById("noakun").value = balikandt[2];
          mtuang = document.getElementById("matauang");
          for (a = 0; a < mtuang.length; a++) {
            if (mtuang.options[a].value == balikandt[3]) {
              mtuang.options[a].selected = true;
            }
          }
          document.getElementById("kurs").value = balikandt[4];
          document.getElementById("noinvoice").value = balikandt[5];
          document.getElementById("nopo").value = balikandt[6];
          document.getElementById("nopo").disabled = true;
          document.getElementById("proses").value = "edit";
          alertify.alert("Informasi", "Added Data Header");
          showDetail();
          closeDialog();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function chkDtAll() {
  totrow = document.getElementById("totRowPPh").value;
  chkAlldt = document.getElementById("chkAll");
  for (itungAwal = 1; itungAwal <= totrow; itungAwal++) {
    if (chkAlldt.checked == false) {
      document.getElementById("pph22_" + itungAwal).checked = false;
    } else {
      document.getElementById("pph22_" + itungAwal).checked = true;
    }
  }
}
function ambilHtgPO() {
  prd = document.getElementById("periodeHtgId");
  prd = prd.options[prd.selectedIndex].value;
  prd2 = document.getElementById("periodeHtgId2");
  prd2 = prd2.options[prd2.selectedIndex].value;
  suppId = document.getElementById("suppIdHtg");
  suppId = suppId.options[suppId.selectedIndex].value;

  txt = trim(document.getElementById("no_brg").value);
  jnsInvoice = document.getElementById("tipeinvoice").value;
  tanggal = document.getElementById("tanggal").value;
  unit = document.getElementById("unit").value;
  kodeorg = document.getElementById("kodeorg").value;
  param =
    "txtfind=" +
    txt +
    "&jnsInvoice=" +
    jnsInvoice +
    "&tanggal=" +
    tanggal +
    "&unit=" +
    unit;
  param +=
    "&kodeorg=" +
    kodeorg +
    "&periodeHtgId=" +
    prd +
    "&periodeHtgId2=" +
    prd2 +
    "&suppIdHtg=" +
    suppId;
  tujuan = "keu_slave_getpotagihan.php";
  if (prd != "" && prd2 != "") {
    post_response_text(tujuan + "?" + "proses=getPo", param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("container2").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

/*
function showupload(ev) {
	showformupload(ev);
	noinvoice = document.getElementById('noinvoice').value;
	param = 'proses=showupload&noinvoice='+noinvoice;
	tujuan = 'keu_slave_tagihanv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					//alertify.alert("Informasi",con.responseText);
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(noinvoice);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
	document.getElementById('dynamic2').style.display = '';
}
*/

function loadfiles() {
  noinvoice = document.getElementById("noinvoice").value;
  param = "proses=loadfiles&noinvoice=" + trim(noinvoice);
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          if (document.getElementById("listfiles") !== null) {
            document.getElementById("listfiles").innerHTML = con.responseText;
          }
          // document.getElementById('listfiles').innerHTML=con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

/*
function loadfiles(noinvoice) {
	param = 'proses=loadfiles&noinvoice='+noinvoice;
	tujuan = 'keu_slave_tagihanv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					if (document.getElementById('listfilestop') !== null) {
						document.getElementById('listfilestop').innerHTML = con.responseText;
					}
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('listfilesview') !== null) {
						document.getElementById('listfilesview').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
*/

// fungsi untuk progress bar
function progressHandler(event) {
  document.getElementById("progressBar").style.display = "block";
  document.getElementById("loaded_n_total").innerHTML =
    "Uploaded " +
    numberFormat(Math.round(event.loaded / 1024)) +
    " KB of " +
    numberFormat(Math.round(event.total / 1024)) +
    " KB";
  var percent = (event.loaded / event.total) * 100;
  document.getElementById("progressBar").value = Math.round(percent);
  document.getElementById("statusbar").innerHTML =
    Math.round(percent) + "% uploaded... please wait";
}
function completeHandler(event) {
  document.getElementById("progressBar").style.display = "none";
  document.getElementById("statusbar").innerHTML = event.target.responseText;
  document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
}
function errorHandler(event) {
  document.getElementById("statusbar").innerHTML = "Upload Failed";
}
function abortHandler(event) {
  document.getElementById("statusbar").innerHTML = "Upload Aborted";
}

function submitfile() {
  var noinvoice = document.getElementById("noinvoice").value;
  var kriteriaefil = document.getElementById("kriteriaefil").value;
  var file = document.getElementById("upload").files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("upload"));
  formdata.append("noinvoice", noinvoice);
  formdata.append("kriteriaefil", kriteriaefil);
  if (getValue("upload") == "") {
    alertify.alert("Informasi", "warning : Upload file has been empty.");
    return false;
  }
  document.getElementsByClassName("mybutton").disabled = true;
  var con = createXMLHttpRequest();
  //tambahan progress bar
  con.upload.addEventListener("progress", progressHandler, false);
  con.addEventListener("load", completeHandler, false);
  con.addEventListener("error", errorHandler, false);
  con.addEventListener("abort", abortHandler, false);
  //tambahan progress bar -end-
  con.open("POST", "keu_slave_tagihanv2.php?proses=submitfile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //=== Success Response
          document.getElementsByClassName("mybutton").disabled = false;
          alertify.alert("Informasi", "Uploaded Success.");
          document.getElementById("upload").value = "";
          loadfiles(noinvoice);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deletefile(noinvoice, namafile) {
  param = "proses=deletefile&noinvoice=" + noinvoice + "&namafile=" + namafile;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          loadfiles(noinvoice);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function viewefill(noinvoice, ev) {
  content = '<div id=formviewefill  style="height:100%;"></div>';
  title = "View Efilling System";
  height = "";
  width = "";
  showDialog5(title, content, width, height, "event");
  showefil(noinvoice);

  var dialog = document.getElementById("dynamic5");
  clientWidth = document.getElementById("dynamic5").clientWidth;
  clientHeight = document.getElementById("dynamic5").clientHeight;
  pos = new Array();
  pos = getMouseP(ev);

  dialog.style.top = pos[1] + "px";
  dialog.style.left = pos[0] - clientWidth - 500 + "px";
}

function showefil(noinvoice) {
  param = "method=viewefill&noinvoice=" + noinvoice;
  tujuan = "log_slave_efill.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("formviewefill").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function addfiledata(noinvoice, criteria) {
  uploadfile = document.getElementById("upload_" + criteria);
  var file = uploadfile.files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", uploadfile.value);
  formdata.append("noinvoice", noinvoice);
  formdata.append("criteria", criteria);
  if (uploadfile.value == "") {
    alertify.alert("Informasi", "warning : Upload file has been empty.");
    return false;
  }

  document.getElementsByClassName("mybutton").disabled = true;
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "log_slave_efill.php?method=uploadfile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);

  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //=== Success Response
          document.getElementsByClassName("mybutton").disabled = false;
          alertify.alert("Informasi", "Uploaded Success.");
          document.getElementById("upload_" + criteria).value = "";
          document.getElementById("bodyefil").innerHTML = "";
          document.getElementById("bodyefil").innerHTML = con.responseText;
          // loadfiles(nopp);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function deleteefil(noinvoice, namafile) {
  param = "method=deleteefil&namafile=" + namafile + "&noinvoice=" + noinvoice;
  tujuan = "log_slave_efill.php";

  if (confirm("Anda yakin hapus item/file ini : " + namafile + " ?")) {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          alertify.alert("Informasi", "Success");
          document.getElementById("bodyefil").innerHTML = "";
          document.getElementById("bodyefil").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getnodok() {
  alertify.popup().destroy();

  tanggalinvoice = trim(document.getElementById("tanggalinvoice").value);
  tipeinvoice = trim(document.getElementById("tipeinvoice").value);
  kodeorg = trim(document.getElementById("kodeorg").value);
  unit = trim(document.getElementById("unit").value);

  if (tanggalinvoice == "") {
    alert("Tanggal Invoice Tidak boleh kosong");
    return;
  }

  if (tipeinvoice == "") {
    alert("Tipe Invoice Tidak boleh kosong");
    return;
  }

  if (kodeorg == "") {
    alert("PT Tidak boleh kosong");
    return;
  }

  if (unit == "") {
    alert("Unit Tidak boleh kosong");
    return;
  }

  ev = "event";
  title = "Pencarian";
  content = "<div id=formpencariannodok></div>";
  // content+='<div id=formpencariannodokdetail></div>';
  // content='';
  width = "";
  height = "";
  // showDialog6(title,content,width,height,ev);

  param = "proses=getnodok&tipeinvoice=" + tipeinvoice;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respon);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // document.getElementById('formpencariannodok').innerHTML=con.responseText;
          //alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('90%','90%');
          alertify
            .popup()
            .set({
              resizable: true,
              maximizable: true,
              startMaximized: true,
              message: con.responseText,
            })
            .resizeTo("70%", "70%")
            .show();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findnodok() {
  nodok = trim(document.getElementById("nodokcr").value);
  kodeorg = trim(document.getElementById("kodeorg").value);
  unit = trim(document.getElementById("unit").value);
  tipeinvoice = trim(document.getElementById("tipeinvoice").value);
  jenis = trim(document.getElementById("jeniscr").value);
  param = "proses=findnodok";
  param += "&nodok=" + nodok + "&kodeorg=" + kodeorg;
  param += "&unit=" + unit + "&tipeinvoice=" + tipeinvoice + "&jenis=" + jenis;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("formpencariannodoktampil").innerHTML =
            con.responseText;
          document.getElementById("formpencariannodoktampildetail").innerHTML =
            "";
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function findnodokdetail(nodok, tipeinvoice) {
  // unit = trim(document.getElementById('unit').value);
  tanggalinvoice = trim(document.getElementById("tanggalinvoice").value);
  param = "proses=findnodokdetail";
  param +=
    "&nodok=" +
    nodok +
    "&tipeinvoice=" +
    tipeinvoice +
    "&tanggalinvoice=" +
    tanggalinvoice;
  tujuan = "keu_slave_tagihanv2.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("formpencariannodoktampildetail").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prosesnodok(tipeinvoice, nodokdt, maxrow, dtsisadppum, dtsisappnum) {
  param = "proses=prosesnodok";
  param +=
    "&nodokdt=" +
    nodokdt +
    "&maxrow=" +
    maxrow +
    "&tipeinvoice=" +
    tipeinvoice +
    "&dtsisappnum=" +
    dtsisappnum;
  // alert(param);
  strparam = "";
  countcheck = 0;
  for (i = 1; i <= maxrow; i++) {
    checklist = document.getElementById("checkboxdt" + i);
    // alertify.alert("Informasi",checklist);
    if (checklist.checked == true) {
      countcheck++;
      // strparam += '&noaruskas['+i+']='+trim(document.getElementById('noaruskas'+i).innerHTML);
      // strparam += '&jumlahdt['+i+']='+remove_comma_var(trim(document.getElementById('sisadetail'+i).innerHTML));
      strparam +=
        "&notransaksidt[" +
        i +
        "]=" +
        document.getElementById("notransaksidt" + i).innerHTML;
      strparam +=
        "&tanggaldt[" +
        i +
        "]=" +
        document.getElementById("tanggaldt" + i).innerHTML;
      strparam +=
        "&nilaidt[" +
        i +
        "]=" +
        document.getElementById("nilaidt" + i).innerHTML;
      strparam +=
        "&nilaiuangmukadt[" +
        i +
        "]=" +
        document.getElementById("nilaiuangmukadt" + i).value;
      strparam +=
        "&noakunuangmukadt[" +
        i +
        "]=" +
        document.getElementById("noakunuangmukadt" + i).innerHTML;
      strparam +=
        "&kelompokbarangdt[" +
        i +
        "]=" +
        document.getElementById("kelompokbarangdt" + i).innerHTML;
      strparam +=
        "&kodekegiatandt[" +
        i +
        "]=" +
        document.getElementById("kodekegiatandt" + i).innerHTML;
      strparam +=
        "&noaruskasdt[" +
        i +
        "]=" +
        document.getElementById("noaruskasdt" + i).innerHTML;
      strparam +=
        "&noakundt[" +
        i +
        "]=" +
        document.getElementById("noakundt" + i).innerHTML;
      strparam +=
        "&kodeassetdt[" +
        i +
        "]=" +
        document.getElementById("kodeassetdt" + i).innerHTML;
      strparam +=
        "&keterangandatadt[" +
        i +
        "]=" +
        document.getElementById("keterangandatadt" + i).innerHTML;
      strparam +=
        "&termindt[" +
        i +
        "]=" +
        document.getElementById("termindt" + i).innerHTML;

      strparam +=
        "&nilailaindt[" +
        i +
        "]=" +
        document.getElementById("nilailaindt" + i).value;
      strparam +=
        "&noakunlaindt[" +
        i +
        "]=" +
        document.getElementById("noakunlaindt" + i).value;
      strparam +=
        "&reksupplierdt[" +
        i +
        "]=" +
        document.getElementById("reksupplierdt" + i).innerHTML;

      // untuk validasi
      tnilaium = remove_comma_var(
        document.getElementById("nilaiuangmukadt" + i).value
      );
    }
  }

  if (countcheck < 1) {
    alertify.alert("Informasi", "Tidak ada transaksi detail yang dicheck");
    return;
  }

  if (parseFloat(tnilaium) > parseFloat(dtsisadppum)) {
    alertify.alert(
      "Informasi",
      "Nilai uang muka yang di-input melebihi dari sisa uang muka"
    );
    return;
  }

  param += strparam;

  tujuan = "keu_slave_tagihanv2.php";
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("tipeinvoice").disabled = true;
          alertify.popup().destroy();
          arr = con.responseText.split("###");
          setdatanodok(
            arr[0],
            arr[1],
            arr[2],
            arr[3],
            arr[4],
            arr[5],
            arr[6],
            arr[7],
            arr[8],
            arr[9],
            arr[10]
          );
          // setdatanodok(nopodt,supplier,nilaidpp,nilaiinvoice,matauang,kurs,reksupplier,jenissupplier){);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}

function saveht(parameter) {
  proses = "saveht";
  tujuan = "keu_slave_tagihanv2.php";
  var passP = parameter.split("###");
  var param = "";
  for (i = 1; i < passP.length; i++) {
    var tmp = document.getElementById(passP[i]);
    if (i == 1) {
      //jumlah ditaro di awal agar di removecomma
      param += passP[i] + "=" + getValue(passP[i]);
    } else {
      param += "&" + passP[i] + "=" + getValue(passP[i]);
    }
  }
  param += "&proses=" + proses;
  console.log(param);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("noinvoice").value = con.responseText;
          showDetail();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
  post_response_text(tujuan, param, respon);
}

function getnilaitotaldt(i) {
  nilaitransaksidt = remove_comma_var(
    document.getElementById("nilaitransaksidt" + i).innerHTML
  );
  nilaiuangmukadt = remove_comma_var(
    document.getElementById("nilaiuangmukadt" + i).value
  );
  nilaireturdt = remove_comma_var(
    document.getElementById("nilaireturdt" + i).innerHTML
  );
  nilaidt =
    parseFloat(nilaitransaksidt) -
    parseFloat(nilaiuangmukadt) -
    parseFloat(nilaireturdt);
  if (nilaidt < 0) {
    document.getElementById("nilaiuangmukadt" + i).value = 0;
    alertify.alert("Informasi", "Nilai uang muka lebih besar dari transaksi");
    return;
  }
  document.getElementById("nilaidt" + i).innerHTML = numberFormat(nilaidt);
}

function detailBlokKecilDt(noht) {
  // Pilih semua elemen <tr> yang memiliki ID 'detailBlok'
  var rows = document.querySelectorAll("tr#detailBlok" + noht);

  // Loop melalui setiap elemen yang dipilih
  rows.forEach(function (row) {
    // Periksa apakah atribut 'hidden' ada pada elemen
    if (row.hasAttribute("hidden")) {
      // Jika atribut 'hidden' ada, hapus atribut 'hidden' dari elemen
      row.removeAttribute("hidden");
    } else {
      // Jika atribut 'hidden' tidak ada, tambahkan atribut 'hidden' ke elemen
      row.setAttribute("hidden", "");
    }
  });
}
