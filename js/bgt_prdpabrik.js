function calcTotalMonth(month) {
  const totalKgTbs = document.getElementById("kgtbs").value;

  let sourceElement = document.getElementById("calc-" + month);
  let targetElement = document.getElementById("kg" + month);

  let totalKgMonth = (sourceElement.value * totalKgTbs) / 100;

  let totalPercentage = calculateTotal();
  if (totalPercentage && totalKgMonth && targetElement) {
    targetElement.value = numberFormat(totalKgMonth);
  }
}

function calculateTotal() {
  let total = 0;

  for (let i = 1; i <= 12; i++) {
    const monthInput = document.getElementById("calc-" + i);
    const value = parseFloat(monthInput.value) || 0;
    total += value;
  }
  
  total = Math.round(total * 100) / 100;
  
  if (total > 100) {
    alert("Total percentage cannot exceed 100%");
    return false;
  }

  return true;
}

function showbutton() {
  document.getElementById("formuploaddt").style.display = "block";
}

function del(kunci) {
  param = "method=del";
  param += "&kunci=" + kunci;
  tujuan = "bgt_slave_prdpabrik.php";
  if (confirm("Anda yakin ??")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          getPage();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function unposting(tahunbudget, kodeorg) {
  param = "method=unposting";
  param += "&tahun=" + tahunbudget;
  param += "&kodeorg=" + kodeorg;
  tujuan = "bgt_slave_prdpabrik.php";
  if (confirm("Anda yakin ??")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          showposting();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function posting(tahunbudget, kodeorg, sebaran, varkg) {
  if (sebaran == "x" && varkg != 0) {
    alert(
      "Kg TBS Total tidak sama dengan Kg TBS Sebaran,\nTerdapat selisih : " +
        varkg +
        " Kg."
    );
    return;
  }
  param = "method=posting";
  param += "&tahun=" + tahunbudget;
  param += "&kodeorg=" + kodeorg;
  tujuan = "bgt_slave_prdpabrik.php";
  if (confirm("Anda yakin ??")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          showposting();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function bataladd() {
  document.getElementById("tahun").disabled = false;
  document.getElementById("kodeorg").disabled = false;
  //document.getElementById('continputdata').innerHTML='';
  //getblok();
}
function hapuspersen() {
  for (i = 1; i <= 12; i++) {
    document.getElementById("persen_" + i).value = 0;
  }
  hitungsebaran("", "1");
}

function saveall(maxrow) {
  if (maxrow == "" || maxrow == 0) {
    alert("Data tidak ditemukan, proses dibatalkan !");
    return;
  }
  row = document.getElementById("awalbaris").value;
  if (
    confirm(
      "Data akan disimpan semua mulai dari baris ke " + row + ", Anda yakin ???"
    )
  ) {
    simpan(row, maxrow);
  }
}

function simpan() {
  param = "";
  tahun = document.getElementById("tahun").value;
  kodeorg = document.getElementById("kodeorg").value;
  jenis = document.getElementById("jenis").value;
  kodeunit = document.getElementById("kodeunit").value;
  ttlkg = document.getElementById("kgtbs").value;
  oerpersen = document.getElementById("oerpersen").value;
  kerpersen = document.getElementById("kerpersen").value;

  for (i = 1; i <= 12; i++) {
    kg = document.getElementById("kg" + i).value;
    kg = remove_comma_var(kg);
    param += "&kg[" + i + "]=" + kg;
  }

  param += "&tahun=" + tahun;
  param += "&kodeorg=" + kodeorg;
  param += "&jenis=" + jenis;
  param += "&kodeunit=" + kodeunit;
  param += "&ttlkg=" + ttlkg;
  param += "&oerpersen=" + oerpersen;
  param += "&kerpersen=" + kerpersen;
  param += "&method=simpan";

  tujuan = "bgt_slave_prdpabrik.php";
  if (confirm("Anda yakin ???")) {
    post_response_text(tujuan, param, respog);
  }
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          alertify.alert("Done").set({
            onclose: function () {
              batalsimpan();
              loaddata(0);
            },
          });
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function batalsimpan() {
  document.getElementById("kgtbs").value = "";
  document.getElementById("oerpersen").value = "";
  document.getElementById("kerpersen").value = "";
  for (i = 1; i <= 12; i++) {
    document.getElementById("calc-" + i).value = "";
    document.getElementById("kg" + i).value = "";
  }
}

function numberFormat(number, digit) {
  number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
  var components = parseFloat(number).toFixed(digit).split(".");
  components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return components.join(".");
}

function hitungsebaran(row, bln) {
  if (bln != "") {
    //sumber isi persen sebaran
    e = document.getElementById("persen_" + bln).value;
    if (e == "") {
      document.getElementById("persen_" + bln).value = 0;
    }

    ttlrow = document.getElementById("ttlbaris").value;
    t = 0;
    for (i = 1; i <= 12; i++) {
      e = document.getElementById("persen_" + i).value;
      if (e == "") {
        e = 0;
      }
      t = t + parseFloat(e);
    }

    for (row = 1; row <= ttlrow; row++) {
      tjjg = 0;
      jjgsebar = 0;
      tkg = 0;
      kgsebar = 0;
      jjg = document.getElementById("jjg_" + row).value;
      kg = document.getElementById("kg_" + row).value;
      jjg = remove_comma_var(jjg);
      kg = remove_comma_var(kg);
      for (i = 1; i <= 12; i++) {
        n = document.getElementById("persen_" + i).value;
        if (i < 12) {
          j = Math.round(parseFloat(jjg) * (parseFloat(n) / parseFloat(t)));
          k = Math.round(parseFloat(kg) * (parseFloat(n) / parseFloat(t)));
          tjjg = tjjg + parseFloat(j);
          tkg = tkg + parseFloat(k);
        } else {
          j = jjg - tjjg;
          k = kg - tkg;
        }
        if (isNaN(j)) {
          j = 0;
        }
        if (j < 0) {
          j = 0;
        }
        if (isNaN(k)) {
          k = 0;
        }
        if (k < 0) {
          k = 0;
        }
        document.getElementById("jjg_" + row + "_" + i).value = j;
        document.getElementById("kg_" + row + "_" + i).value = k;

        jjgsebar = jjgsebar + parseFloat(j);
        kgsebar = kgsebar + parseFloat(k);
      }
      if (jjg - jjgsebar != 0 || kg - kgsebar != 0) {
        document.getElementById("btnsave_" + row).style.backgroundColor = "red";
      }
    }
  } else {
    //sumber isi ttljjg atau ttlkg
    t = 0;
    for (i = 1; i <= 12; i++) {
      e = document.getElementById("persen_" + i).value;
      if (e == "") {
        e = 0;
      }
      t = t + parseFloat(e);
    }
    tjjg = 0;
    jjgsebar = 0;
    tkg = 0;
    kgsebar = 0;
    jjg = document.getElementById("jjg_" + row).value;
    kg = document.getElementById("kg_" + row).value;
    jjg = remove_comma_var(jjg);
    kg = remove_comma_var(kg);
    for (i = 1; i <= 12; i++) {
      n = document.getElementById("persen_" + i).value;
      if (i < 12) {
        j = Math.round(parseFloat(jjg) * (parseFloat(n) / parseFloat(t)));
        k = Math.round(parseFloat(kg) * (parseFloat(n) / parseFloat(t)));
        tjjg = tjjg + parseFloat(j);
        tkg = tkg + parseFloat(k);
      } else {
        j = jjg - tjjg;
        k = kg - tkg;
      }
      if (isNaN(j)) {
        j = 0;
      }
      if (j < 0) {
        j = 0;
      }
      if (isNaN(k)) {
        k = 0;
      }
      if (k < 0) {
        k = 0;
      }

      b = parseFloat(kg) / parseFloat(jjg);
      if (isNaN(b)) {
        b = 0;
      }

      document.getElementById("jjg_" + row + "_" + i).value = j;
      document.getElementById("kg_" + row + "_" + i).value = k;
      document.getElementById("bjr_" + row).innerHTML = numberFormat(b, 2);

      jjgsebar = jjgsebar + parseFloat(j);
      kgsebar = kgsebar + parseFloat(k);
    }
    //alert("jjgttl : "+jjg+" jjgsebar : "+jjgsebar+" ttlkg : "+kg+" kgsebar "+kgsebar);
    if (jjg - jjgsebar != 0 || kg - kgsebar != 0) {
      document.getElementById("btnsave_" + row).style.backgroundColor = "red";
    }
  }
}

function addZero(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}

function editdetail(
  tahunbudget,
  millcode,
  jnsedit,
  kodeunit,
  kgolah,
  oerbunch,
  oerkernel,
  ...olahValues
) {
  document.getElementById("inputdata").style.display = "block";
  //document.getElementById('contdetail').style.display = 'block';
  document.getElementById("listData").style.display = "block";
  document.getElementById("formcari").style.display = "none";

  document.getElementById("tahun").value = tahunbudget;
  document.getElementById("kodeorg").value = millcode;
  document.getElementById("jenis").value = jnsedit;
  document.getElementById("kodeunit").value = kodeunit;
  document.getElementById("kgtbs").value = kgolah;
  document.getElementById("oerpersen").value = oerbunch;
  document.getElementById("kerpersen").value = oerkernel;

  for (let i = 1; i <= 12; i++) {
    const olahValue = olahValues[i - 1] || 0;
    document.getElementById("kg" + i).value = olahValue;

    const percentage = kgolah > 0 ? (olahValue / kgolah) * 100 : 0;
    document.getElementById("calc-" + i).value = numberFormat(percentage, 2);
  }

  document.getElementById("tahun").disabled = true;
  document.getElementById("kodeorg").disabled = true;

  setValue2("jenis", jnsedit);
  setValue2("kodeunit", kodeunit);
  adddata();
}

function add_new_data() {
  document.getElementById("inputdata").style.display = "block";
  //document.getElementById('contdetail').style.display = 'block';
  document.getElementById("listData").style.display = "none";
  document.getElementById("contposting").style.display = "none";
  document.getElementById("formcari").style.display = "none";
  document.getElementById("formcariposting").style.display = "none";
  bataladd();
}

function add_sebaran() {
  document.getElementById("inputdata").style.display = "none";
  document.getElementById("contdetail").style.display = "none";
  document.getElementById("formcari").style.display = "none";
  document.getElementById("contposting").style.display = "block";
  document.getElementById("formcariposting").style.display = "block";
  document.getElementById("listData").style.display = "none";
  showposting();
}

function displayList() {
  document.getElementById("formcari").style.display = "block";
  document.getElementById("listData").style.display = "block";
  document.getElementById("contdetail").style.display = "none";
  document.getElementById("inputdata").style.display = "none";
  document.getElementById("contposting").style.display = "none";
  document.getElementById("formcariposting").style.display = "none";
  loaddata(0);
}

function showposting() {
  tahun = document.getElementById("tahunpostsch").value;
  kodeorg = document.getElementById("kodeorgpostsch").value;

  param = "method=showposting";
  param += "&tahun=" + tahun + "&kodeorg=" + kodeorg;
  tujuan = "bgt_slave_prdpabrik.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contpostingdata").innerHTML =
            con.responseText;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function formupload() {
  tahun = document.getElementById("tahun").value;
  kodeorg = document.getElementById("kodeorg").value;
  divisi = document.getElementById("divisi").value;
  tt = document.getElementById("tt").value;

  param = "method=formupload";
  param += "&tahun=" + tahun + "&kodeorg=" + kodeorg;
  param += "&divisi=" + divisi + "&tt=" + tt;
  tujuan = "bgt_slave_prdpabrik.php";
  judul = "excel";
  ev = "event";
  printFile(param, tujuan, judul, ev);
}

function adddata() {
  tahun = document.getElementById("tahun").value;
  kodeorg = document.getElementById("kodeorg").value;

  param = "method=adddata";
  param += "&tahun=" + tahun + "&kodeorg=" + kodeorg;
  tujuan = "bgt_slave_prdpabrik.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("contdetail").style.display = "block";
          document.getElementById("listData").style.display = "block";
          //document.getElementById('continputdata').innerHTML = con.responseText;
          document.getElementById("tahunsch").innerHTML =
            "<option value='" + tahun + "'>" + tahun + "</option>";
          document.getElementById("kodeorgsch").value = kodeorg;
          loaddata(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getmark(no) {
  namacol = document.getElementsByName("baris[]");
  for (var r = 0; r < namacol.length; r++) {
    namacol[r].style.backgroundColor = "";
  }

  dis = document.getElementById("notran" + no).style.backgroundColor;
  if (dis != "") {
    document.getElementById("notran" + no).style.backgroundColor = "";
  } else {
    document.getElementById("notran" + no).style.backgroundColor = "cyan";
  }
}

function form() {
  width = "720";
  height = "";
  content =
    '<fieldset><div id=containerd align=center style="width:700px;overflow:auto;"></div></fieldset>';
  ev = "event";
  title = "";
  showDialog1(title, content, width, height, ev);
}
function html(tahun, kodeorg) {
  form();
  param = "method=html" + "&tahun=" + tahun + "&kodeorg=" + kodeorg;
  tujuan = "bgt_slave_prdpabrik.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("containerd").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getPage() {
  pg = document.getElementById("pages");
  pg = pg.options[pg.selectedIndex].value;
  paged = parseFloat(pg) - 1;
  loaddata(paged);
}
function loaddata(page) {
  tahun = document.getElementById("tahunsch").value;
  kodeorg = document.getElementById("kodeorgsch").value;
  sebaran = document.getElementById("sebaransch").value;

  param = "method=loaddata&page=" + page;
  param += "&tahun=" + tahun + "&kodeorg=" + kodeorg;
  param += "&sebaran=" + sebaran;
  tujuan = "bgt_slave_prdpabrik.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          isdt = con.responseText.split("####");
          document.getElementById("contain").innerHTML = isdt[0];
          document.getElementById("footData").innerHTML = isdt[1];
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function loadexcel(page) {
  tahun = document.getElementById("tahunsch").value;
  kodeorg = document.getElementById("kodeorgsch").value;
  sebaran = document.getElementById("sebaransch").value;

  param = "method=loaddata&page=" + page;
  param += "&tahun=" + tahun + "&kodeorg=" + kodeorg;
  param += "&sebaran=" + sebaran;
  param += "&jenis=excel";

  tujuan = "bgt_slave_prdpabrik.php";
  judul = "excel";
  ev = "event";
  printFile(param, tujuan, judul, ev);
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "300";
  height = "100";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}

function batalcari() {
  document.getElementById("kodeorgsch").value = "";
  document.getElementById("divisisch").value = "";
  document.getElementById("ttsch").value = "";
  document.getElementById("sebaransch").value = "";
  document.getElementById("ipsch").value = "";
  loaddata(0);
}

function getunit(sumber) {
  tahun = document.getElementById("tahun").value;
  kodeorg = document.getElementById("kodeorg").value;
  jenis = document.getElementById("jenis").value;

  param = "method=getunit";
  param += "&kodeorg=" + kodeorg;
  param += "&jenis=" + jenis;
  param += "&tahun=" + tahun;
  tujuan = "bgt_slave_prdpabrik.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          document.getElementById("kodeunit").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function gettbskebun(sumber) {
  tahun = document.getElementById("tahun").value;
  kodeorg = document.getElementById("kodeorg").value;
  jenis = document.getElementById("jenis").value;
  kodeunit = document.getElementById("kodeunit").value;

  param = "method=gettbskebun";
  param += "&kodeorg=" + kodeorg;
  param += "&jenis=" + jenis;
  param += "&tahun=" + tahun;
  param += "&kodeunit=" + kodeunit;
  tujuan = "bgt_slave_prdpabrik.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
          batalsimpan();
        } else {
          if (jenis != 0) {
            data = con.responseText.split("##");
            document.getElementById("kgtbs").value = trim(data[0]);
            for (i = 1; i <= 12; i++) {
              document.getElementById("kg" + i).value = trim(data[i]);
            }
            document.getElementById("oerpersen").value = trim(data[13]);
            document.getElementById("kerpersen").value = trim(data[14]);
          }
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
