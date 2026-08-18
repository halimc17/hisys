/**
 * @author repindra.ginting
 */
//=================================================sisi purchasing
function getKelompokSupplier(tipe) {
  param = "tipe=" + tipe;
  tujuan = "log_slave_get_klsupplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("kdkelompok").innerHTML = trim(
            con.responseText
          );
          document.getElementById("captiontipe").innerHTML = tipe;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getSupplierNumber(kdkelompok, namakelompok) {
  param = "kelompok=" + kdkelompok;
  tujuan = "log_slave_get_klsupplier_number.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("idsupplier").value = trim(con.responseText);
          document.getElementById("captionkelompok").innerHTML = namakelompok;
          getSupplierList(kdkelompok);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getSupplierList(kdkelompok) {
  param = "kelompok=" + kdkelompok;
  tujuan = "log_slave_save_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        //if (!isSaveResponse(con.responseText)) {
        //    alert(con.responseText);
        //}
        //alert(con.responseText);
        data = JSON.parse(con.responseText);
        htmlSupplierList(data);
        //document.getElementById('container').innerHTML=con.responseText;
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function htmlSupplierList(data) {
  if (data.error == "false") {
    var opt = "";
    var bg = "class=rowcontent";
    var html = "";
    for (i = 0; i < data.supplier.length; i++) {
      opt = "";
      bg = "class=rowcontent";
      if (data.supplier[i].status == 0) {
        opt +=
          "<input type=checkbox id=" +
          data.supplier[i].supplierid +
          " title='Click to activate' onclick=\"updateStatus('" +
          data.supplier[i].supplierid +
          "','" +
          data.supplier[i].status +
          "');\">";
        bg = "bgcolor=orange";
      } else {
        opt +=
          "<input type=checkbox id=" +
          data.supplier[i].supplierid +
          " checked  title='Click to deActivate' onclick=\"updateStatus('" +
          data.supplier[i].supplierid +
          "','" +
          data.supplier[i].status +
          "');\">";
      }
      html += "<tr " + bg + ">";
      //html += "<td>"+data.supplier[i].kelompok+"</td>";
      html += "<td>" + data.supplier[i].supplierid + "</td>";
      html += "<td>" + data.supplier[i].namasupplier + "</td>";
      html += "<td>" + data.supplier[i].alamat + "</td>";
      html += "<td>" + data.supplier[i].kontakperson || "" + "</td>";
      html += "<td>" + data.supplier[i].kota + "</td>";
      //html += "<td>"+data.supplier[i].telepon+"</td>";
      //html += "<td>"+data.supplier[i].fax+"</td>";
      //html += "<td>"+data.supplier[i].email+"</td>";
      //html += "<td>"+data.supplier[i].npwp+"</td>";
      html += "<td align='right'>" + data.supplier[i].formatplafon + "</td>";
      if (data.supplier[i].status == 0) {
        stat = "Not Active";
      } else {
        stat = "Active";
      }
      html += "<td align=center>" + opt + "&nbsp;" + stat + "</td>";
      html +=
        "<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editSupplier('" +
        data.supplier[i].supplierid +
        "','" +
        data.supplier[i].namasupplier +
        "','" +
        data.supplier[i].alamat +
        "','" +
        data.supplier[i].kontakperson +
        "','" +
        data.supplier[i].kota +
        "','" +
        data.supplier[i].telepon +
        "','" +
        data.supplier[i].fax +
        "','" +
        data.supplier[i].email +
        "','" +
        data.supplier[i].npwp +
        "','" +
        data.supplier[i].plafon +
        "','" +
        data.supplier[i].statusintext +
        "');\"></td>";
      html +=
        "<td><img src=images/skyblue/zoom.png class=resicon  title='Data Akun Bank Supplier' onclick=\"detaildt('Akun Bank','" +
        data.supplier[i].supplierid +
        "');\"></td>";
      //html += "<td><img src=images/skyblue/zoom.png class=resicon  title='Data Akun Bank Supplier' onclick=\"detailupload('List Upload','" + data.supplier[i].supplierid + "');\"></td>";
      html += "</tr>";
    }
    document.getElementById("container").innerHTML = html;
  } else {
    alert("ERROR TRANSACTION,\n" + data.error);
  }
  //document.getElementById('container').innerHTML=d;
}
function cancelSupplier() {
  document.getElementById("tipe").options[0].selected = true;
  document.getElementById("telp").value = "";
  document.getElementById("kdkelompok").options[0].selected = true;
  document.getElementById("statusintext").options[0].selected = true;
  document.getElementById("fax").value = "";
  document.getElementById("idsupplier").value = "";
  document.getElementById("email").value = "";
  document.getElementById("namasupplier").value = "";
  document.getElementById("npwp").value = "";
  document.getElementById("alamat").value = "";
  document.getElementById("cperson").value = "";
  document.getElementById("kota").value = "";
  document.getElementById("plafon").value = "0";
  document.getElementById("method").value = "insert";
  document.getElementById("tipe").disabled = false;
  document.getElementById("kdkelompok").disabled = false;
}
function editSupplier(
  idsupplier,
  namasupplier,
  alamat,
  kontakperson,
  kota,
  telp,
  fax,
  email,
  npwp,
  plafon,
  statusintext
) {
  objtipe = document.getElementById("tipe");
  tipe = idsupplier.substring(0, 1);
  if (tipe == "S") tipe = "SUPPLIER";
  else tipe = "KONTRAKTOR";
  for (x = 0; x < objtipe.length; x++) {
    if (objtipe.options[x].value == tipe) {
      objtipe.options[x].selected = true;
    }
  }
  objtipe.disabled = true;
  objkelompok = document.getElementById("kdkelompok");
  kel = idsupplier.substring(0, 4);
  for (x = 0; x < objkelompok.length; x++) {
    if (objkelompok.options[x].value == kel) {
      objkelompok.options[x].selected = true;
    }
  }
  objkelompok.disabled = true;
  objstatusintext = document.getElementById("statusintext");
  // kel=idsupplier.substring(0,4);
  for (x = 0; x < objstatusintext.length; x++) {
    if (objstatusintext.options[x].value == statusintext) {
      objstatusintext.options[x].selected = true;
    }
  }
  document.getElementById("telp").value = telp;
  document.getElementById("fax").value = fax;
  document.getElementById("email").value = email;
  document.getElementById("namasupplier").value = namasupplier;
  document.getElementById("npwp").value = npwp;
  document.getElementById("alamat").value = alamat;
  document.getElementById("cperson").value = kontakperson;
  document.getElementById("kota").value = kota;
  document.getElementById("plafon").value = plafon;
  document.getElementById("method").value = "update";
  document.getElementById("idsupplier").value = idsupplier;
  change_number(document.getElementById("plafon"));
}
function delSupplier(id, nama) {
  if (confirm("Deleting " + nama + ", Are you sure..?")) {
    param = "idsupplier=" + id + "&method=delete";
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//========================================================sisi akunting
function findSupplier() {
  txt = trim(document.getElementById("cari").value);
  if (txt == "") {
    alert("Please type supplier name");
  } else {
    param = "txt=" + txt;
    tujuan = "log_slave_save_akun_supplier.php";
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function editAkunSupplier(
  supplierid,
  namasupplier,
  noakun,
  nilaihutang,
  noseripajak,
  akunpajak,
  bank,
  rekening,
  an
) {
  document.getElementById("idsupplier").value = supplierid;
  document.getElementById("bank").value = bank;
  obj = document.getElementById("noakun");
  for (x = 0; x < obj.length; x++) {
    if (obj.options[x].value == noakun) obj.options[x].selected = true;
  }
  document.getElementById("rek").value = rekening;
  document.getElementById("namasupplier").value = namasupplier;
  document.getElementById("an").value = an;
  obj1 = document.getElementById("akunpajak");
  for (x = 0; x < obj1.length; x++) {
    if (obj1.options[x].value == akunpajak) obj1.options[x].selected = true;
  }
  document.getElementById("noseripajak").value = noseripajak;
  document.getElementById("nilaihutang").value = nilaihutang;
}

//===================== LOAD DATA REK BANK ======================
function loadDataAkun(idsupplier_detail) {
  // alert('masukk');
  param = "method=loadData4";
  param += "&id_supplier=" + idsupplier_detail;
  tujuan = "log_slave_akun_bank_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("containerAkun").innerHTML = con.responseText;
          // getPage();
          // detaildt();
          loadData(idsupplier_detail);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//===================== LOAD DATA SUP NPWP ======================
function loadData(idsupplier_detail) {
  // alert('masukk');
  param = "method=loadData";
  param += "&supplierid=" + idsupplier_detail;
  tujuan = "log_slave_save_5supnpwp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("container1").innerHTML = con.responseText;
          // getPage();
          // detaildt();
          loadDataAlamat(idsupplier_detail);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//===================== LOAD DATA SUP KELOMPOK ======================
// function loadData2(idsupplier_detail) {
// 	// alert('masukk');
// 	param = 'method=loadData2';
// 	param += '&supplier_id=' + idsupplier_detail;
// 	tujuan = 'log_slave_save_5supkelompok.php';
// 	post_response_text(tujuan, param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					// alert(con.responseText);
// 					document.getElementById('container2').innerHTML = con.responseText;
// 					// getPage();
// 					// detaildt();
// 					loadData3(idsupplier_detail)
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
//===================== LOAD DATA SUP KELOMPOK ======================
/*function loadData3(idsupplier_detail) {
	// alert('masukk');
	param = 'method=loadData3';
	param += '&supplierid1=' + idsupplier_detail;
	tujuan = 'log_slave_save_5suptimbangan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('container3').innerHTML = con.responseText;
					// getPage();
					// detaildt();
					loadDataAlamat(idsupplier_detail)
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}*/
//===================== LOAD DATA SUP KELOMPOK ======================
function loadDataAlamat(idsupplier_detail) {
  // alert('masukk');
  param = "method=loadData";
  param += "&supplierid2=" + idsupplier_detail;
  tujuan = "log_slave_save_5Alamat.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("containerAlamat").innerHTML =
            con.responseText;
          // getPage();
          // detaildt();
          loadDatapph(idsupplier_detail);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//===================== LOAD DATA PPH ======================
function loadDatapph(idsupplier_detail) {
  // alert(param);
  param = "method=loadDatapph";
  param += "&supp_id=" + idsupplier_detail;
  tujuan = "log_slave_5pph.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("containerpph").innerHTML = con.responseText;
          // getPage();
          // detaildt();
          // loadData(idsupplier_detail)
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//================= Load Data Awal ==========================
function loadData1(num, xxx) {
  param = "method=loadData";
  param += "&page=" + num;
  // param+='&page='+num;
  txtsearch = trim(document.getElementById("txtsearch").value);
  if (xxx === null || typeof xxx === "undefined") {
    txtNoakun = trim(document.getElementById("txtNoakun").value);
  } else {
    txtNoakun = xxx;
  }
  caristatusup = trim(document.getElementById("caristatusup").value);
  caribadan = trim(document.getElementById("caribadan").value);
  if (txtsearch != "") {
    param += "&txtsearch=" + txtsearch;
  }
  if (txtNoakun != "") {
    param += "&txtNoakun=" + txtNoakun;
  }
  if (caristatusup != "") {
    param += "&caristatusup=" + caristatusup;
  }
  if (caribadan != "") {
    param += "&caribadan=" + caribadan;
  }
  tujuan = "log_slave_save_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("container").innerHTML = con.responseText;
          loadDatacalon(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function loadDatacalon(num) {
  param = "method=loadDatacalon";
  param += "&page=" + num;
  // param+='&page='+num;
  txtsearchcalon = trim(document.getElementById("txtsearchcalon").value);
  txtNoakuncalon = trim(document.getElementById("txtNoakuncalon").value);
  if (txtsearchcalon != "") {
    param += "&txtsearchcalon=" + txtsearchcalon;
  }
  if (txtNoakuncalon != "") {
    param += "&txtNoakuncalon=" + txtNoakuncalon;
  }
  tujuan = "log_slave_save_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("caloncontainer").innerHTML =
            con.responseText;
          // getPage();
          // detaildt();
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
  loadData1(paged);
  // cariBast(pg-1);
}
//=========SIMPAN SUPPLIER===============//
function saveSupplier() {
  // telp=document.getElementById('telp').value;
  // kelompok=document.getElementById('kdkelompok').options[document.getElementById('kdkelompok').selectedIndex].value;
  // fax=document.getElementById('fax').value;
  //statusintext=document.getElementById('statusintext').options[document.getElementById('statusintext').selectedIndex].value;
  //email=document.getElementById('email').value;
  idsupplier = document.getElementById("idsupplier").value;
  namasupplier = document.getElementById("namasupplier").value.toUpperCase();
  // email=document.getElementById('email').value;
  badan = document.getElementById("badan").value;
  pemilik = document.getElementById("pemilik").value;
  direktur = document.getElementById("direktur").value;
  pj = document.getElementById("pj").value;
  jabatan = document.getElementById("jabatan").value;
  statusup = document.getElementById("statusup").value;
  email = document.getElementById("useremail").value;
  ju = document.getElementById("jenisusaha");
  jenisusaha = getSelectValues(ju);
  // if(statusup.checked==true)
  //    statusup=1;
  // else
  //    statusup=0;
  method = document.getElementById("methodSupplier").value;
  // npwp=document.getElementById('npwp').value;
  // alamat=document.getElementById('alamat').value;
  // cperson=document.getElementById('cperson').value;
  // kota=document.getElementById('kota').value;
  // plafon=remove_comma(document.getElementById('plafon'));
  if (namasupplier == "" || statusup == "" || jenisusaha == "") {
    alert("Field Was Empty");
    return;
  }
  // if(statusup=='3' && email=='')
  // {
  // 	alert('Email Must Not Empty For Register Supplier');
  // 	return;
  // }

  param =
    "idsupplier=" +
    idsupplier +
    "&useremail=" +
    email +
    "&namasupplier=" +
    namasupplier +
    "&badan=" +
    badan +
    "&pemilik=" +
    pemilik +
    "&direktur=" +
    direktur +
    "&pj=" +
    pj +
    "&jabatan=" +
    jabatan +
    "&statusup=" +
    statusup +
    "&jenisusaha=" +
    jenisusaha +
    "&method=" +
    method;
  // param+='&npwp='+npwp+'&cperson='+cperson+'&kota='+kota;
  // param+='&plafon='+plafon+'&method='+method+'&alamat='+alamat;
  // alert(param);

  trapproval = document.getElementById("trapproval").innerHTML;

  if (trapproval == "") {
    alert("Please contact administrator to setup Approval.");
    return;
  }

  var tbl = document.getElementById("trapproval");
  var row = parseFloat(tbl.rows.length) + 1;
  strUrl = "";
  for (i = 1; i < row; i++) {
    if (document.getElementById("persetujuan" + i).innerHTML == "") {
      alert("Please contact administrator to setup Approval.");
      return false;
    }
    persetujuan = document.getElementById("persetujuan" + i).options[
      document.getElementById("persetujuan" + i).selectedIndex
    ].value;
    if (persetujuan == "") {
      alert("Please compelete Approval");
      return;
    }
    strUrl += "&persetujuan[" + i + "]=" + persetujuan;
  }
  param += strUrl;

  tujuan = "log_slave_save_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          cancel();
          loadData1(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//============ SIMPAN AKUN BANK ============
function saveAkun(idsupplier_detail) {
  // alert('masukk');
  id_supplier = document.getElementById("id_supplier").value;
  rekening = document.getElementById("rekening").value;
  bank = document.getElementById("bank").value;
  atasnama = document.getElementById("atasnama").value;
  cabang = document.getElementById("cabang").value;
  kota = document.getElementById("kota").value;
  negara = document.getElementById("negara").value;
  matauang = document.getElementById("matauang").value;
  def = document.getElementById("def");
  if (def.checked == true) def = 1;
  else def = 0;
  statusbank = document.getElementById("statusbank");
  if (statusbank.checked == true) statusbank = 1;
  else statusbank = 0;
  method = document.getElementById("methodAkun").value;
  if (id_supplier == "" || rekening == "" || bank == "" || atasnama == "") {
    alert("Field Was Empty");
    return;
  }
  param =
    "id_supplier=" +
    id_supplier +
    "&rekening=" +
    rekening +
    "&bank=" +
    bank +
    "&atasnama=" +
    atasnama +
    "&cabang=" +
    cabang +
    "&kota=" +
    kota +
    "&negara=" +
    negara +
    "&matauang=" +
    matauang +
    "&method=" +
    method;
  param += "&def=" + def + "&statusbank=" + statusbank;

  trapproval = document.getElementById("trapproval").innerHTML;

  if (trapproval == "") {
    alert("Please contact administrator to setup Approval.");
    return;
  }

  var tbl = document.getElementById("trapproval");
  var row = parseFloat(tbl.rows.length) + 1;
  strUrl = "";
  for (i = 1; i < row; i++) {
    if (document.getElementById("persetujuan" + i).innerHTML == "") {
      alert("Please contact administrator to setup Approval.");
      return false;
    }
    persetujuan = document.getElementById("persetujuan" + i).options[
      document.getElementById("persetujuan" + i).selectedIndex
    ].value;
    if (persetujuan == "") {
      alert("Please compelete Approval");
      return;
    }
    strUrl += "&persetujuan[" + i + "]=" + persetujuan;
  }
  param += strUrl;

  // alert(param);
  tujuan = "log_slave_akun_bank_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadDataAkun(id_supplier);
          cancelAkun();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
// submit file npwp
function loadfiles(supplierid) {
  param = "method=loadfiles&supplierid=" + supplierid;
  tujuan = "log_slave_save_5supnpwp.php";
  // alert(param);
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("containerUpload").innerHTML =
            con.responseText;
          // getPage();
          // detaildt();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function submitfile() {
  var supplierid = document.getElementById("supplierid").value;
  var file = document.getElementById("upload").files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("upload"));
  formdata.append("supplierid", supplierid);
  if (getValue("upload") == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  var con = createXMLHttpRequest();
  con.open("POST", "log_slave_save_5supnpwp.php?method=submitfile", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //=== Success Response
          alert("Uploaded Success.");
          document.getElementById("upload").value = "";
          loadfiles(supplierid);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function downloadfile(path, filename) {
  param = "path=" + path + "&filename=" + filename;
  tujuan = "download.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function deletefile(id) {
  param = "method=deletefile&id=" + id;
  // alert(param);
  tujuan = "log_slave_save_5supnpwp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadfiles(supplierid);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//============ SIMPAN SUPPLIER NPWP ============
function simpan(idsupplier_detail) {
  supplierid = document.getElementById("supplierid").value;
  //supplierid=supplierid.options[supplierid.selectedIndex].value;
  npwp = document.getElementById("npwp").value;
  namanpwp = document.getElementById("namanpwp").value;
  jalan = document.getElementById("jalan").value;
  blok = document.getElementById("blok").value;
  nomor = document.getElementById("nomor").value;
  rt = document.getElementById("rt").value;
  rw = document.getElementById("rw").value;
  kelurahan = document.getElementById("kelurahan").value;
  kecamatan = document.getElementById("kecamatan").value;
  kabupaten = document.getElementById("kabupaten").value;
  propinsi = document.getElementById("propinsi").value;
  kodepos = document.getElementById("kodepos").value;
  telp_no = document.getElementById("telp_no").value;
  aktif = document.getElementById("aktif");
  if (aktif.checked == true) aktif = 1;
  else aktif = 0;
  method = document.getElementById("methoddt").value;
  if (supplierid == "" || npwp == "" || jalan == "") {
    alert("Field Was Empty");
    return;
  }
  param =
    "supplierid=" +
    supplierid +
    "&npwp=" +
    npwp +
    "&namanpwp=" +
    namanpwp +
    "&jalan=" +
    jalan +
    "&blok=" +
    blok +
    "&nomor=" +
    nomor +
    "&rt=" +
    rt +
    "&rw=" +
    rw +
    "&kelurahan=" +
    kelurahan +
    "&kecamatan=" +
    kecamatan +
    "&kabupaten=" +
    kabupaten +
    "&propinsi=" +
    propinsi +
    "&kodepos=" +
    kodepos +
    "&telp_no=" +
    telp_no +
    "&method=" +
    method;
  param += "&aktif=" + aktif;

  trapproval = document.getElementById("trapproval").innerHTML;

  if (trapproval == "") {
    alert("Please contact administrator to setup Approval.");
    return;
  }

  var tbl = document.getElementById("trapproval");
  var row = parseFloat(tbl.rows.length) + 1;
  strUrl = "";
  for (i = 1; i < row; i++) {
    if (document.getElementById("persetujuan" + i).innerHTML == "") {
      alert("Please contact administrator to setup Approval.");
      return false;
    }
    persetujuan = document.getElementById("persetujuan" + i).options[
      document.getElementById("persetujuan" + i).selectedIndex
    ].value;
    if (persetujuan == "") {
      alert("Please compelete Approval");
      return;
    }
    strUrl += "&persetujuan[" + i + "]=" + persetujuan;
  }
  param += strUrl;
  //alert(strUrl);
  // param+='&supplierid='+idsupplier_detail;
  tujuan = "log_slave_save_5supnpwp.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          // alert(param);
          loadData(supplierid);
          cancelTab();
          // loadDataA(idsupplier_detail);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//============ SIMPAN SUP KELOMPOK ============
function simpanKel(idsupplier_detail) {
  // alert ('masuk');
  supplier_id = document.getElementById("supplier_id").value;
  //supplierid=supplierid.options[supplierid.selectedIndex].value;
  noakun = document.getElementById("noakun").value;
  kode = document.getElementById("kode").value;
  // alamat=document.getElementById('alamat').value;
  statkel = document.getElementById("statkel");
  if (statkel.checked == true) {
    statkel = 1;
  } else {
    statkel = 0;
  }
  method = document.getElementById("methodKel").value;
  if (supplier_id == "" || noakun == "") {
    alert("Field Was Empty");
    return;
  }
  param =
    "supplier_id=" +
    supplier_id +
    "&noakun=" +
    noakun +
    "&kode=" +
    kode +
    "&method=" +
    method;
  param += "&statkel=" + statkel;
  // alert(param);
  tujuan = "log_slave_save_5supkelompok.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadData2(supplier_id);
          cancelKel();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//============ SIMPAN SUP Timbangan ============
function simpanSupTim(idsupplier_detail) {
  // alert ('masuk');
  supplierid1 = document.getElementById("supplierid1").value;
  //supplierid=supplierid.options[supplierid.selectedIndex].value;
  kodetimbangan = document.getElementById("kodetimbangan").value;
  // alamat=document.getElementById('alamat').value;
  status1 = document.getElementById("status1");
  if (status1.checked == true) status1 = 1;
  else status1 = 0;
  method = document.getElementById("methodTim").value;
  if (supplierid1 == "") {
    alert("Field Was Empty");
    return;
  }
  param =
    "supplierid1=" +
    supplierid1 +
    "&kodetimbangan=" +
    kodetimbangan +
    "&method=" +
    method;
  param += "&status1=" + status1;
  // alert(param);
  tujuan = "log_slave_save_5suptimbangan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadData3(supplierid1);
          cancelSupTim();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//============ SIMPAN Alamat Supplier ============
function simpanAlamat(idsupplier_detail) {
  // alert ('masuk');
  idalamat = document.getElementById("idalamat").value;
  supplierid2 = document.getElementById("supplierid2").value;
  //supplierid=supplierid.options[supplierid.selectedIndex].value;
  alamatsup = document.getElementById("alamatsup").value;
  kota1 = document.getElementById("kota1").value;
  telp = document.getElementById("telp").value;
  extensi = document.getElementById("extensi").value;
  nohp = document.getElementById("nohp").value;
  cperson = document.getElementById("cperson").value;
  jabatan1 = document.getElementById("jabatan1").value;
  fax = document.getElementById("fax").value;
  emailkor = document.getElementById("emailkor").value;
  emailkon = document.getElementById("emailkon").value;
  provinsi1 = document.getElementById("provinsi1").value;
  negara1 = document.getElementById("negara1").value;
  kodepos1 = document.getElementById("kodepos1").value;
  // email=document.getElementById('email').value;
  statusalamat = document.getElementById("statusalamat");
  if (statusalamat.checked == true) statusalamat = 1;
  else statusalamat = 0;
  method = document.getElementById("methodAlamat").value;
  if (
    supplierid2 == "" ||
    alamatsup == "" ||
    kota1 == "" ||
    telp == "" ||
    cperson == "" ||
    provinsi1 == "" ||
    negara1 == ""
  ) {
    alert(
      "Lengkapi form alamat, kota, telp, kontak person, provinsi dan negara"
    );
    return;
  }
  param =
    "idalamat=" +
    idalamat +
    "&supplierid2=" +
    supplierid2 +
    "&alamatsup=" +
    alamatsup +
    "&kota1=" +
    kota1 +
    "&telp=" +
    telp +
    "&extensi=" +
    extensi +
    "&nohp=" +
    nohp +
    "&cperson=" +
    cperson +
    "&jabatan1=" +
    jabatan1 +
    "&fax=" +
    fax +
    "&emailkor=" +
    emailkor +
    "&emailkon=" +
    emailkon +
    "&provinsi1=" +
    provinsi1 +
    "&negara1=" +
    negara1 +
    "&kodepos1=" +
    kodepos1 +
    "&method=" +
    method;
  param += "&statusalamat=" + statusalamat;

  trapproval = document.getElementById("trapproval").innerHTML;

  if (trapproval == "") {
    alert("Please contact administrator to setup Approval.");
    return;
  }

  var tbl = document.getElementById("trapproval");
  var row = parseFloat(tbl.rows.length) + 1;
  strUrl = "";
  for (i = 1; i < row; i++) {
    if (document.getElementById("persetujuan" + i).innerHTML == "") {
      alert("Please contact administrator to setup Approval.");
      return false;
    }
    persetujuan = document.getElementById("persetujuan" + i).options[
      document.getElementById("persetujuan" + i).selectedIndex
    ].value;
    if (persetujuan == "") {
      alert("Please compelete Approval");
      return;
    }
    strUrl += "&persetujuan[" + i + "]=" + persetujuan;
  }
  param += strUrl;

  // alert(param);
  tujuan = "log_slave_save_5Alamat.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadDataAlamat(supplierid2);
          cancelAlamat();
          // loadData1(supplierid2);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//============ SIMPAN PPh ============
function simpanpph(idsupplier_detail) {
  // alert ('masuk');
  supp_id = document.getElementById("idsupplier_detail").value;
  //supplierid=supplierid.options[supplierid.selectedIndex].value;
  pph = document.getElementById("pph").value;
  tarif = document.getElementById("tarif").value;

  statuspph = document.getElementById("statuspph");
  if (statuspph.checked == true) statuspph = 1;
  else statuspph = 0;

  method = document.getElementById("methodpph").value;
  if (supp_id == "" || pph == "" || tarif == "") {
    alert("Field Was Empty");
    return;
  }
  param =
    "supp_id=" +
    supp_id +
    "&pph=" +
    pph +
    "&tarif=" +
    tarif +
    "&method=" +
    method;
  param += "&status=" + statuspph;

  trapproval = document.getElementById("trapproval").innerHTML;

  if (trapproval == "") {
    alert("Please contact administrator to setup Approval.");
    return;
  }

  var tbl = document.getElementById("trapproval");
  var row = parseFloat(tbl.rows.length) + 1;
  strUrl = "";
  for (i = 1; i < row; i++) {
    if (document.getElementById("persetujuan" + i).innerHTML == "") {
      alert("Please contact administrator to setup Approval.");
      return false;
    }
    persetujuan = document.getElementById("persetujuan" + i).options[
      document.getElementById("persetujuan" + i).selectedIndex
    ].value;
    if (persetujuan == "") {
      alert("Please compelete Approval");
      return;
    }
    strUrl += "&persetujuan[" + i + "]=" + persetujuan;
  }
  param += strUrl;

  // alert(param);
  tujuan = "log_slave_5pph.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          loadDatapph(supp_id);
          cancelpph();
          // loadData1(supplierid2);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailsupp(jenis, suppid) {
  width = "";
  height = "";
  content =
    '<div id=containerdsup style="width:100%;height:100%;overflow:auto"></div>';
  ev = "event";
  title = "View Supplier";
  showDialog1(title, content, width, height, ev);
  param = "method=detailsupp&idsupplier=" + suppid + "&jenisview=" + jenis;
  tujuan = "log_slave_save_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerdsup").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//==========CANCEL / RESET FORM AWAL ==================//
function cancel() {
  document.getElementById("idsupplier").value = "";
  document.getElementById("namasupplier").value = "";
  document.getElementById("statusup").checked = false;
  // document.getElementById('email').value='';
  document.getElementById("badan").value = "";
  document.getElementById("pemilik").value = "";
  document.getElementById("direktur").value = "";
  document.getElementById("pj").value = "";
  document.getElementById("jabatan").value = "";
  document.getElementById("statusup").value = "";
  document.getElementById("useremail").value = "";
  document.getElementById("trpemilik").value = "";
  document.getElementById("jenisusaha").disabled = false;
  document.getElementById("chooserjenisusaha").disabled = false;
  document.getElementById("jenisusaharow").style.opacity = "1";
  document.getElementById("trdirektur").value = "";
  document.getElementById("trpj").value = "";
  document.getElementById("trjabatan").value = "";
  ClearChooser("jenisusaha");
  // document.getElementById('method').value='insert';
  // document.getElementById('supplierid').disabled=false;
}
//==========CANCEL / RESET FORM cari awal ==================//
function cancelsearch() {
  document.getElementById("txtNoakun").value = "";
  document.getElementById("txtsearch").value = "";
  // document.getElementById('statusup').checked=false;
  // document.getElementById('alamat').value='';
  // document.getElementById('aktif').checked=false;
  // document.getElementById('method').value='insert';
  // document.getElementById('supplierid').disabled=false;
  loadData1(0);
}
function cancelsearchcalon() {
  document.getElementById("txtNoakuncalon").value = "";
  document.getElementById("txtsearchcalon").value = "";
  loadDatacalon(0);
}
//==========CANCEL / RESET FORM REKENING BANK ==================//
function cancelAkun() {
  // document.getElementById('supplierid').value='';
  // document.getElementById('id_supplier').value='';
  document.getElementById("rekening").value = "";
  document.getElementById("rekening").disabled = false;
  document.getElementById("bank").value = "";
  document.getElementById("bank").disabled = false;
  document.getElementById("atasnama").value = "";
  document.getElementById("cabang").value = "";
  document.getElementById("kota").value = "";
  document.getElementById("negara").value = "";
  document.getElementById("matauang").value = "";
  if (document.getElementById("def").checked == true) {
    document.getElementById("def").checked = false;
  }
  if (document.getElementById("statusbank").checked == true) {
    document.getElementById("statusbank").checked = false;
  }
  document.getElementById("methodAkun").value = "insert";
  document.getElementById("id_supplier").disabled = true;
  // document.getElementById('supplierid').disabled=false;
  detaildt($frm[0]);
}
//==========CANCEL / RESET FORM SUP NPWP ==================//
function cancelTab() {
  document.getElementById("supplierid").value = "";
  document.getElementById("npwp").value = "";
  document.getElementById("namanpwp").value = "";
  document.getElementById("jalan").value = "";
  document.getElementById("blok").value = "";
  document.getElementById("nomor").value = "";
  document.getElementById("rt").value = "";
  document.getElementById("rw").value = "";
  document.getElementById("kelurahan").value = "";
  document.getElementById("kecamatan").value = "";
  document.getElementById("kabupaten").value = "";
  document.getElementById("propinsi").value = "";
  document.getElementById("kodepos").value = "";
  document.getElementById("telp_no").value = "";
  if (document.getElementById("aktif").checked == true) {
    document.getElementById("aktif").checked = false;
  }
  document.getElementById("method").value = "insert";
  document.getElementById("supplierid").disabled = false;
  detaildt($frm[1]);
}
//==========CANCEL / RESET FORM SUP KELOMPOK ==================//
function cancelKel() {
  // document.getElementById('supplier_id').value='';
  document.getElementById("noakun").value = "";
  document.getElementById("kode").value = "";
  document.getElementById("kode").disabled = false;
  document.getElementById("statkel").checked = false;
  document.getElementById("methodKel").value = "insert";
  document.getElementById("supplier_id").disabled = false;
  detaildt($frm[2]);
}
//==========CANCEL / RESET FORM SUP TIMBANGAN ==================//
function cancelSupTim() {
  document.getElementById("supplierid1").value = "";
  document.getElementById("kodetimbangan").value = "";
  document.getElementById("status1").checked = false;
  document.getElementById("methodTim").value = "insert";
  document.getElementById("supplierid1").disabled = false;
  detaildt($frm[3]);
}
//==========CANCEL / RESET FORM ALAMAT ==================//
function cancelAlamat() {
  document.getElementById("supplierid2").value = "";
  document.getElementById("alamatsup").value = "";
  document.getElementById("kota1").value = "";
  document.getElementById("cperson").value = "";
  document.getElementById("telp").value = "";
  document.getElementById("extensi").value = "";
  document.getElementById("nohp").value = "";
  document.getElementById("jabatan1").value = "";
  document.getElementById("fax").value = "";
  document.getElementById("emailkor").value = "";
  document.getElementById("emailkon").value = "";
  document.getElementById("provinsi1").value = "";
  document.getElementById("negara1").value = "";
  document.getElementById("kodepos1").value = "";
  document.getElementById("statusalamat").checked = false;
  document.getElementById("methodAlamat").value = "insert";
  document.getElementById("supplierid2").disabled = false;
  detaildt($frm[4]);
}
//==========CANCEL / RESET FORM PPH ==================//
function cancelpph() {
  document.getElementById("supp_id").value = "";
  document.getElementById("pph").value = "";
  document.getElementById("tarif").value = "";
  document.getElementById("pph").disabled = false;
  document.getElementById("methodpph").value = "insert";
  document.getElementById("supp_id").disabled = false;
  detaildt($frm[5]);
}
//==========EDIT FORM AWAL==================//
function editSupplier2(
  idsupplier,
  namasupplier,
  badan,
  pemilik,
  direktur,
  pj,
  jabatan,
  statusup,
  email,
  jenisusaha
) {
  cancel();
  document.getElementById("idsupplier").value = idsupplier;
  document.getElementById("idsupplier").disabled = true;
  document.getElementById("namasupplier").value = namasupplier;
  document.getElementById("badan").value = badan;
  document.getElementById("pemilik").value = pemilik;
  document.getElementById("direktur").value = direktur;
  document.getElementById("pj").value = pj;
  document.getElementById("jabatan").value = jabatan;
  document.getElementById("statusup").value = statusup;
  document.getElementById("useremail").value = email;
  // if(statusup=='1')
  //    document.getElementById('statusup').checked=true;
  // else
  //    document.getElementById('statusup').checked=false;
  document.getElementById("jenisusaharow").style.opacity = null;
  document.getElementById("jenisusaha").disabled = false;
  document.getElementById("chooserjenisusaha").disabled = false;
  document.getElementById("methodSupplier").value = "update";
  document.getElementById("trpemilik").style.display = null;
  document.getElementById("trdirektur").style.display = null;
  document.getElementById("trpj").style.display = null;
  document.getElementById("trjabatan").style.display = null;
  selectedValue(jenisusaha, "jenisusaha");
}
//==========EDIT FORM AKUN REKENING==================//
function editAkun(
  id_supplier,
  bank,
  rekening,
  atasnama,
  cabang,
  kota,
  negara,
  matauang,
  def,
  statusbank
) {
  document.getElementById("id_supplier").value = id_supplier;
  document.getElementById("id_supplier").disabled = true;
  document.getElementById("bank").value = bank;
  document.getElementById("bank").disabled = true;
  document.getElementById("rekening").value = rekening;
  document.getElementById("rekening").disabled = true;
  // document.getElementById('supplierid').disabled=true;
  document.getElementById("atasnama").value = atasnama;
  document.getElementById("cabang").value = cabang;
  document.getElementById("kota").value = kota;
  document.getElementById("negara").value = negara;
  document.getElementById("matauang").value = matauang;
  if (def == "1") document.getElementById("def").checked = true;
  else document.getElementById("def").checked = false;
  if (statusbank == "1") document.getElementById("statusbank").checked = true;
  else document.getElementById("statusbank").checked = false;
  document.getElementById("methodAkun").value = "update";
}
//==========EDIT FORM SUP NPWP==================//
function edit(
  supplierid,
  npwp,
  namanpwp,
  jalan,
  blok,
  nomor,
  rt,
  rw,
  kelurahan,
  kecamatan,
  kabupaten,
  propinsi,
  kodepos,
  telp_no,
  aktif
) {
  document.getElementById("supplierid").value = supplierid;
  document.getElementById("supplierid").disabled = true;
  document.getElementById("npwp").value = npwp;
  document.getElementById("npwp").disabled = true;
  document.getElementById("namanpwp").value = namanpwp;
  document.getElementById("jalan").value = jalan;
  document.getElementById("blok").value = blok;
  document.getElementById("nomor").value = nomor;
  document.getElementById("rt").value = rt;
  document.getElementById("rw").value = rw;
  document.getElementById("kelurahan").value = kelurahan;
  document.getElementById("kecamatan").value = kecamatan;
  document.getElementById("kabupaten").value = kabupaten;
  document.getElementById("propinsi").value = propinsi;
  document.getElementById("kodepos").value = kodepos;
  document.getElementById("telp_no").value = telp_no;
  if (aktif == "1") document.getElementById("aktif").checked = true;
  else document.getElementById("aktif").checked = false;
  document.getElementById("methoddt").value = "update";
}
//==========EDIT FORM SUP KELOMPOK==================//
function editSupKel(supplier_id, noakun, kode, statkel) {
  document.getElementById("supplier_id").value = supplier_id;
  document.getElementById("supplier_id").disabled = true;
  document.getElementById("noakun").value = noakun;
  document.getElementById("kode").value = kode;
  document.getElementById("kode").disabled = true;
  // document.getElementById('kode').disabled=true;
  if (statkel == "1") document.getElementById("statkel").checked = true;
  else document.getElementById("statkel").checked = false;
  document.getElementById("methodKel").value = "update";
}
//==========EDIT FORM SUP TIMBANGAN==================//
function editSupTim(supplierid1, kodetimbangan, status1) {
  document.getElementById("supplierid1").value = supplierid1;
  document.getElementById("supplierid1").disabled = true;
  document.getElementById("kodetimbangan").value = kodetimbangan;
  document.getElementById("kodetimbangan").disabled = true;
  // document.getElementById('kode').disabled=true;
  if (status1 == "1") document.getElementById("status1").checked = true;
  else document.getElementById("status1").checked = false;
  document.getElementById("methodTim").value = "update";
}
//==========EDIT FORM Alamat==================//
function editAlamat(
  supplierid2,
  alamatsup,
  kota1,
  telp,
  extensi,
  nohp,
  cperson,
  jabatan1,
  fax,
  emailkor,
  emailkon,
  provinsi1,
  negara1,
  kodepos1,
  statusalamat,
  idalamat
) {
  document.getElementById("idalamat").value = idalamat;
  // document.getElementById('idalamat').disabled=true;
  document.getElementById("supplierid2").value = supplierid2;
  document.getElementById("supplierid2").disabled = true;
  // document.getElementById('namasupplier').value=namasupplier;
  document.getElementById("alamatsup").value = alamatsup;
  document.getElementById("kota1").value = kota1;
  document.getElementById("telp").value = telp;
  document.getElementById("extensi").value = extensi;
  document.getElementById("nohp").value = nohp;
  document.getElementById("cperson").value = cperson;
  document.getElementById("jabatan1").value = jabatan1;
  document.getElementById("fax").value = fax;
  document.getElementById("emailkor").value = emailkor;
  document.getElementById("emailkon").value = emailkon;
  document.getElementById("provinsi1").value = provinsi1;
  document.getElementById("negara1").value = negara1;
  document.getElementById("kodepos1").value = kodepos1;
  if (statusalamat == "1")
    document.getElementById("statusalamat").checked = true;
  else document.getElementById("statusalamat").checked = false;
  document.getElementById("methodAlamat").value = "update";
}
//==========EDIT FORM SUP PPH==================//
function editpph(supp_id, pph, tarif) {
  document.getElementById("supp_id").value = supp_id;
  document.getElementById("supp_id").disabled = true;
  document.getElementById("pph").value = pph;
  document.getElementById("pph").disabled = true;
  document.getElementById("tarif").value = tarif;
  // document.getElementById('kode').disabled=true;
  //  if(status1=='1')
  //    document.getElementById('status1').checked=true;
  // else
  //    document.getElementById('status1').checked=false;
  document.getElementById("methodpph").value = "update";
}
function cancelAkunSupplier() {
  document.getElementById("idsupplier").value = "";
  document.getElementById("bank").value = "";
  obj = document.getElementById("noakun");
  obj.options[0].selected = true;
  document.getElementById("rek").value = "";
  document.getElementById("namasupplier").value = "";
  document.getElementById("an").value = "";
  obj1 = document.getElementById("akunpajak");
  obj1.options[0].selected = true;
  document.getElementById("noseripajak").value = "";
  document.getElementById("nilaihutang").value = "0";
}
function saveAkunSupplier() {
  obj1 = document.getElementById("akunpajak");
  akunpajak = obj1.options[obj1.selectedIndex].value;
  obj = document.getElementById("noakun");
  noakun = obj.options[obj.selectedIndex].value;
  idsupplier = trim(document.getElementById("idsupplier").value);
  bank = trim(document.getElementById("bank").value);
  rek = trim(document.getElementById("rek").value);
  namasupplier = trim(document.getElementById("namasupplier").value);
  an = trim(document.getElementById("an").value);
  noseripajak = trim(document.getElementById("noseripajak").value);
  nilaihutang = remove_comma(document.getElementById("nilaihutang"));
  param = "noakun=" + noakun + "&akunpajak=" + akunpajak;
  param += "&idsupplier=" + idsupplier + "&an=" + an + "&bank=" + bank;
  param += "&rek=" + rek + "&namasupplier=" + namasupplier;
  param += "&noseripajak=" + noseripajak + "&nilaihutang=" + nilaihutang;
  //method always update
  param += "&method=update";
  if (idsupplier == "") {
    alert("object id  undefined");
  } else {
    tujuan = "log_slave_save_akun_supplier.php";
    if (confirm("Saving Account for " + namasupplier + ", Are you sure..?"))
      post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("container").innerHTML = con.responseText;
          cancelAkunSupplier();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function updateStatus(notrans, stat) {
  param = "method=updStatus" + "&supplierid=" + notrans + "&status=" + stat;
  tujuan = "log_slave_save_supplier.php";
  //alert(param);
  if (stat == 1) {
    dert = "Are you sure deactive this supplier?";
  } else {
    dert = "Are you sure active this supplier?";
  }
  if (confirm(dert)) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        // if (!isSaveResponse(con.responseText)) {
        //         alert(con.responseText);
        //}
        // else {
        //alert(con.responseText);
        kdkelompok = notrans.substring(0, 4);
        getSupplierList(kdkelompok);
        //}
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function formListPP(title, wdth, heig) {
  //closeDialog();
  width = "";
  height = "";
  if (wdth != "") {
    width = wdth;
  }
  if (heig != "") {
    height = heig;
  }
  content = "<div id=containerData></div>";
  ev = "event";
  showDialog4(title, content, width, height, ev);
}
// Open Window - Author Atwal
function detaildt(title, idsupplier_detail, namasupplier) {
  title = title + " " + namasupplier;
  width = "1050px";
  height = "650px";
  formListPP(title, width, height);
  param = "idsupplier_detail=" + idsupplier_detail;

  tujuan = "log_akun_bank_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerData").innerHTML = con.responseText;
          loadDataAkun(idsupplier_detail); //panggil fungsi load data rek bank
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
// Open Window - Author Atwal
function detailupload(title, idsupplier_detail, namasupplier, badanusaha) {
  title = title + " " + namasupplier + ", " + badanusaha;
  width = "1050px";
  height = "650px";
  formListPP(title, width, height);
  param = "idsupplier_detail=" + idsupplier_detail;
  param += "&prosses=listupload";
  param += "&namasupplier=" + namasupplier;
  param += "&badanusaha=" + badanusaha;
  tujuan = "log_akun_bank_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        console.log(con.responseText);
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("containerData").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
// Insert/Update callback - Author Atwal
function tableakunSupplier(data) {
  var html = "";
  html += '<td for="rekening">' + data.rekening + "</td>";
  html += '<td for="bank">' + data.bank + "</td>";
  html += '<td for="an">' + data.an + "</td>";
  html +=
    '<td align="center"><a href="#" onclick="editakunSupplier(this);"><img src="images/application/application_edit.png" class="resicon" title="Edit" ></a>&nbsp;&nbsp;<a href="#" onclick="delete_cell(this);"><img src="images/close.png" class="resicon" title="delete"></a></td>';
  if (data.prosses == "insert") {
    var tr = document.createElement("tr");
    tr.setAttribute("id", data.supplierid + data.rekening);
    tr.setAttribute("class", "rowcontent");
    document.getElementById("containerData_table").appendChild(tr);
    document.getElementById(data.supplierid + data.rekening).innerHTML = html;
  } else if (data.prosses == "update") {
    var tr = document.getElementById(data.supplierid + data.rekening);
    tr.innerHTML = html;
  }
  removeObj("nodata");
}
// Edit button - Author Atwal
function editakunSupplier(e) {
  function ex(namecell, text) {
    var form = document.getElementById("akun_supplier");
    var cells = form.getElementsByTagName("input");
    for (var i = 0; i < cells.length; i++) {
      var name = cells[i].getAttribute("name");
      if (name == namecell) {
        cells[i].value = text;
      }
    }
  }
  var tr = e.parentNode.parentNode;
  var tr_cells = tr.getElementsByTagName("td");
  for (var i = 0; i < tr_cells.length; i++) {
    var name = tr_cells[i].getAttribute("for");
    ex(name, tr_cells[i].textContent);
  }
}
// Remove Object table tr - Author Atwal
function removeObj(id) {
  Element.prototype.remove = function () {
    this.parentElement.removeChild(this);
  };
  NodeList.prototype.remove = HTMLCollection.prototype.remove = function () {
    for (var i = this.length - 1; i >= 0; i--) {
      if (this[i] && this[i].parentElement) {
        this[i].parentElement.removeChild(this[i]);
      }
    }
  };
  document.getElementById(id).remove();
}
// Delete data Cell - Author Atwal
function delete_cell(e) {
  function ex(where) {
    var tujuan = "log_slave_akun_bank_supplier.php";
    var param = "prosses=delete";
    for (i = 0; i < where.length; i++) {
      param += "&" + where[i].name + "=" + where[i].value;
    }
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          var data = JSON.parse(con.responseText);
          id = data.supplierid + data.rekening;
          removeObj(id);
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  }
  var where = [];
  var idsupplier_detail = {};
  var id = document.getElementById("idsupplier_detail").value;
  idsupplier_detail["name"] = "idsupplier_detail";
  idsupplier_detail["value"] = id;
  where.push(idsupplier_detail);
  var tr = e.parentNode.parentNode;
  var tr_cells = tr.getElementsByTagName("td");
  for (var i = 0; i < tr_cells.length; i++) {
    var name = tr_cells[i].getAttribute("for");
    if (name == "rekening") {
      data["name"] = name;
      data["value"] = tr_cells[i].textContent;
      break;
    }
  }
  where.push(data);
  ex(where);
}
function clearinput() {
  var form = document.getElementById("akun_supplier");
  var cells = form.getElementsByTagName("input");
  for (var i = 0; i < cells.length; i++) {
    var name = cells[i].getAttribute("name");
    if (name != "idsupplier_detail" && name != "prosses") {
      cells[i].value = "";
    }
  }
}
// Save Button - Author Atwal
function saveAkunSupplier(e) {
  var cells = e.getElementsByTagName("input");
  var tujuan = "log_slave_akun_bank_supplier.php";
  var param = "";
  var dan = "";
  for (var i = 0; i < cells.length; i++) {
    var name = cells[i].getAttribute("name");
    if (i > 0) {
      dan = "&";
    }
    if (name == "idsupplier_detail" && cells[i].value == "") {
      alert("Id Supplier Tidak ditemukan!");
      return false;
    }
    param += dan + name + "=" + cells[i].value;
  }
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        var data = JSON.parse(con.responseText);
        tableakunSupplier(data);
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function sendUrl(idname) {
  var supplierid = idname;
  var tujuan = "log_slave_save_supplier.php";
  var param = "method=kirimemail&idsupplier=" + supplierid;
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        //var data = JSON.parse(con.responseText);
        console.log(con.responseText);
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//-- multiple select package
function cancelTarget(e, target) {
  var ele = e;
  var index = ele.getAttribute("index");
  var val = ele.getAttribute("val");
  var chooser = document.getElementById("chooser" + target);
  var jenisusaha = document.getElementById(target);
  var both = document.getElementById("chooserboth" + target);
  jenisusaha.options[index].selected = false;
  chooser.options[index].style.display = "";
  both.removeChild(e);
}
function ClearChooser(target) {
  var chooser = document.getElementById("chooser" + target);
  var jenisusaha = document.getElementById(target);
  var both = document.getElementById("chooserboth" + target);
  if (jenisusaha.options.length > 0) {
    opt = jenisusaha.options;
    for (i = 0; i < opt.length; i++) {
      if (jenisusaha.options[i].selected == true) {
        jenisusaha.options[i].selected = false;
        chooser.options[i].style.display = null;
      }
    }
  }
  both.innerHTML = "";
}
function setOption(selectElement, value) {
  var options = selectElement.options;
  for (var i = 0; i < options.length; i++) {
    if (options[i].value == value) {
      selectElement.options[i].selected = true;
      break;
    }
  }
  return false;
}
function create_multipleSelect(id) {
  var ele = document.getElementById(id);
  var att = ele.getAttribute("multiple");
  if (att != null) {
    var wi = ele.parentNode.offsetWidth;
    var div = document.createElement("div");
    div.setAttribute("id", "chooserboth" + id);
    div.setAttribute("class", "chooserboth");
    div.style =
      "width:" +
      (wi - 12) +
      "px;padding:0px 5px 5px 5px;margin-top:-2px;word-wrap: break-word;";
    var newElem = ele.cloneNode(true);
    newElem.setAttribute("id", "chooser" + id);
    newElem.setAttribute("class", "chooser");
    newElem.removeAttribute("multiple");
    newElem.setAttribute("onchange", "chooseTarget(this,'" + id + "')");
    newElem.style = "width:" + wi + "px";
    for (i = 0; i < ele.options.length; i++) {
      if (ele.options[i].selected == true) {
        newElem.options[i].style.display = "none";
      }
    }
    newElem.value = "";
    ele.parentNode.insertBefore(newElem, ele);
    ele.parentNode.insertBefore(div, ele);
    ele.setAttribute("multiple", "multiple");
    ele.style.display = "none";
    /**
		for(i=0; i<ele.options.length; i++){
		ele.options[i].selected = false;
		}**/
  }
}
function chooseTarget(e, target) {
  var select = e;
  var choosed = document.createElement("div");
  var both = document.getElementById("chooserboth" + target);
  var jenisusaha = document.getElementById(target);
  //alert(document.getElementById('chooserbothjenisusaha').childElementCount);
  if (document.getElementById("chooserbothjenisusaha").childElementCount > 10) {
    alert("Maximum 10 Jenis Usaha");
  } else {
    selectVal = select.options[select.selectedIndex].value;
    setOption(jenisusaha, selectVal);
    //jenisusaha.options[select.selectedIndex].selected = true;
    select.options[select.selectedIndex].style.display = "none";
    choosed.setAttribute("val", jenisusaha.options[select.selectedIndex].value);
    choosed.setAttribute("index", select.selectedIndex);
    choosed.setAttribute("class", "choosed");
    choosed.setAttribute("onclick", "cancelTarget(this,'" + target + "')");
    html = jenisusaha.options[select.selectedIndex].text;
    choosed.innerHTML = html;
    both.appendChild(choosed);
    select.value = "";
  }
}
function selectedValue(isi, k) {
  if (isi.trim() !== "") {
    dataSelect = isi.split(",");
    iSelect = document.getElementById("chooser" + k);
    for (ii = 0; ii < dataSelect.length; ii++) {
      setOption(iSelect, dataSelect[ii]);
      chooseTarget(iSelect, k);
    }
  }
}
function getSelectValues(select) {
  var result = [];
  var options = select && select.options;
  var opt;
  for (var i = 0, iLen = options.length; i < iLen; i++) {
    opt = options[i];
    if (opt.selected) {
      result.push(opt.value || opt.text);
    }
  }
  return result;
}
function getNoakunKl(kdnoakun) {
  obj1 = document.getElementById("kode");
  akunpajak = obj1.options[obj1.selectedIndex].value;
  param = "kode=" + akunpajak;
  if (kdnoakun != 0) {
    obj2 = document.getElementById("noakun");
    noakun = obj2.options[obj2.selectedIndex].value;
    param += "&noakun=" + noakun;
  }
  //method always update
  param += "&method=getNoakun";
  tujuan = "log_slave_save_akun_supplier.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          document.getElementById("noakun").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function upload_fileaftersign(
  fileid,
  supplierid,
  namasupplier,
  badanusaha,
  idlampiran,
  lokasifile
) {
  var file = document.getElementById(fileid).files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue(fileid));
  formdata.append("prosses", "uploadfile");
  formdata.append("idsupplier_detail", supplierid);
  formdata.append("idlampiran", idlampiran);
  formdata.append("lokasifile", lokasifile);
  if (getValue(fileid) == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  var conX = createXMLHttpRequest();
  conX.open("POST", "log_akun_bank_supplier.php", true);
  conX.onreadystatechange = eval(respon);
  conX.send(formdata);
  function respon() {
    if (this.readyState == 4) {
      if (this.status == 200) {
        busy_off();
        if (!isSaveResponse(this.responseText)) {
          alert(this.responseText);
        } else {
          //=== Success Response
          alert("Upload Success!");
          detailupload("List Upload", supplierid, namasupplier, badanusaha);
        }
      } else {
        busy_off();
        error_catch(this.status);
      }
    }
  }
  //alert(supplierid);
}
function delete_fileaftersign(
  idlampiran,
  supplierid,
  namasupplier,
  badanusaha
) {
  param = "prosses=deletefile";
  param += "&idlampiran=" + idlampiran;
  param += "&idsupplier_detail=" + supplierid;
  tujuan = "log_akun_bank_supplier.php";
  //alert(param);
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //alert(con.responseText);
          detailupload("List Upload", supplierid, namasupplier, badanusaha);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

/*Upload File*/
function portaltender(supplierid, namasupplier, ev) {
  showformemail(namasupplier, ev);
  param = "method=portaltender&supplierid=" + supplierid;
  tujuan = "log_slave_portaltender.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          closeDialog2();
          alert(con.responseText);
        } else {
          document.getElementById("contUpload").innerHTML = con.responseText;
          loadfilestender(supplierid);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadfilestender(supplierid) {
  param = "method=loadfilestender&supplierid=" + supplierid;
  tujuan = "log_slave_portaltender.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          if (document.getElementById("listfilestop") !== null) {
            document.getElementById("listfilestop").innerHTML =
              con.responseText;
          }
          if (document.getElementById("listfiles") !== null) {
            document.getElementById("listfiles").innerHTML = con.responseText;
          }
          if (document.getElementById("listfilesview") !== null) {
            document.getElementById("listfilesview").innerHTML =
              con.responseText;
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function submitfiletender(supplierid) {
  var subject = document.getElementById("subject").value;
  var deskripsi = document.getElementById("deskripsi").value;
  var notransaksi = document.getElementById("notransaksi").value;
  var supplierid = document.getElementById("supplierupload").value;
  var file = document.getElementById("upload").files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("fileupload", getValue("upload"));
  formdata.append("subject", subject);
  formdata.append("deskripsi", deskripsi);
  formdata.append("notransaksi", notransaksi);
  formdata.append("supplierid", supplierid);
  if (subject == "") {
    alert("warning : Subject has been empty.");
    return false;
  }

  if (deskripsi == "") {
    alert("warning : Uraian/Question file has been empty.");
    return false;
  }
  document.getElementsByClassName("mybutton").disabled = true;
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "log_slave_portaltender.php?method=submitfiletender", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          //=== Success Response
          document.getElementsByClassName("mybutton").disabled = false;
          document.getElementById("subject").value = "";
          document.getElementById("deskripsi").value = "";
          document.getElementById("upload").value = "";
          loadfilestender(supplierid);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showformemail(namasupplier, ev) {
  title = namasupplier;
  width = "";
  height = "";
  content =
    "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
  showDialog2(title, content, width, height, ev);
  pos = new Array();
  pos = getMouseP(ev);
  document.getElementById("dynamic2").style.top = pos[1] + "px";
  // document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
  document.getElementById("dynamic2").style.display = "";
}
/*End Upload File*/
