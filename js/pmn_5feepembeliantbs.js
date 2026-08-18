//JS
function simpan() {
  pt            = document.getElementById("pt").value;
  alokasi       = document.getElementById("alokasi").value;
  tipetbs       = document.getElementById("tipetbs").value;
  supplier      = document.getElementById("supplier").value;
  tanggaldari   = document.getElementById("tanggaldari").value;
  tanggalsampai = document.getElementById("tanggalsampai").value;
  batasbawah    = document.getElementById("batasbawah").value;
  batasatas     = document.getElementById("batasatas").value;
  rpkg          = document.getElementById("rpkg").value;
  rpkg          = remove_comma_var(rpkg);
  batasbawah    = remove_comma_var(batasbawah);
  batasatas     = remove_comma_var(batasatas);
  rekening      = document.getElementById("rekening").value;
  kredit        = document.getElementById("kredit").value;
  debet         = document.getElementById("debet").value;
  method        = document.getElementById("method").value;
  notransaksi        = document.getElementById("notransaksi").value;
  persenppn        = document.getElementById("persenppn").value;

  if (
    pt            == "" ||
    alokasi       == "" ||
    tipetbs       == "" ||
    supplier      == "" ||
    tanggaldari   == "" ||
    batasbawah    == "" ||
    batasatas     == "" ||
    rpkg          == "" ||
    rekening      == "" ||
    kredit        == "" ||
    debet         == "" 
  ) {
    alertify.alert("Informasi", "Semua kolom harus terisi");
    return;
  }
 

  param = "pt=" + pt;
  param += "&alokasi=" + alokasi;
  param += "&tipetbs=" + tipetbs;
  param += "&supplier=" + supplier;
  param += "&tanggaldari=" + tanggaldari;
  param += "&tanggalsampai=" + tanggalsampai;
  param += "&batasbawah=" + batasbawah;
  param += "&batasatas=" + batasatas;
  param += "&rpkg=" + rpkg;
  param += "&rekening=" + rekening;
  param += "&kredit=" + kredit;
  param += "&debet=" + debet;
  param += "&method=" + method;
  param += "&notransaksi=" + notransaksi;
  param += "&persenppn=" + persenppn;
  // alert(param);
  // return;
  tujuan = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          cancel();
          loadData(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function simpancopy() {
  pt            = document.getElementById("ptcopy").value;
  alokasi       = document.getElementById("alokasicopy").value;
  tipetbs       = document.getElementById("tipetbscopy").value;
  supplier      = document.getElementById("suppliercopy").value;
  tanggaldari   = document.getElementById("tanggaldaricopy").value;
  periodecopy   = document.getElementById("periodecopy").value;
  method        = "insertcopy";
  
  if (
    pt            == "" ||
    alokasi       == "" ||
    tipetbs       == "" ||
    supplier      == "" ||
    tanggaldari   == "" ||
    periodecopy   == "" 
  ) {
    alertify.alert("Informasi", "There is an empty field");
    return;
  }
 

  param = "pt=" + pt;
  param += "&alokasi=" + alokasi;
  param += "&tipetbs=" + tipetbs;
  param += "&supplier=" + supplier;
  param += "&tanggaldari=" + tanggaldari;
  param += "&periodecopy=" + periodecopy;
  param += "&method=" + method;
  // alert(param);
  // return;
  tujuan = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          cancelcopy();
          loadData(0);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cancel() {
  document.getElementById("pt").value = "";
  document.getElementById("pt").disabled = false;
  document.getElementById("alokasi").value = "";
  document.getElementById("supplier").disabled = false;
  document.getElementById("rekening").disabled = false;
  document.getElementById("supplier").value = "";
  document.getElementById("tanggaldari").value = "";
  document.getElementById("tanggalsampai").value = "";
  document.getElementById("batasbawah").value = "";
  document.getElementById("batasatas").value = "";
  document.getElementById("rpkg").value = "";
  document.getElementById("tipetbs").value = "";
  document.getElementById("rekening").value = "";
  document.getElementById("persenppn").value = "";
  document.getElementById("kredit").value = "";
  document.getElementById("debet").value = "";
  document.getElementById("method").value = "insert";
  document.getElementById("notransaksi").value = "";
}

function cancelcopy() {
  document.getElementById("ptcopy").value = "";
  document.getElementById("alokasicopy").value = "";
  document.getElementById("tipetbscopy").value = "";
  document.getElementById("suppliercopy").value = "";
  document.getElementById("tanggaldaricopy").value = "";
  document.getElementById("periodecopy").value = "";
}

function loadData(page) {
  pt = document.getElementById('cariunit').value;
  tipetbs = document.getElementById('caritipe').value;
  supplier = document.getElementById('carisupp').value;
  status = document.getElementById('caristatus').value;

  param   = "method=loadData";
  param   += '&page=' + page;
  param   += "&pt="+pt+"&tipetbs="+tipetbs+"&supplier="+supplier+"&status="+status;
  tujuan  = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("container").innerHTML = con.responseText;
		  leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getTipe() {
  pt      = document.getElementById("pt").value;
  param   = "method=getTipe&pt="+pt;
  tujuan  = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("tipetbs").innerHTML = con.responseText;
          getAlokasi();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getTipecopy() {
  pt      = document.getElementById("ptcopy").value;
  param   = "method=getTipe&pt="+pt;
  tujuan  = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("tipetbscopy").innerHTML = con.responseText;
          getAlokasicopy();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getAlokasi() {
  pt      = document.getElementById("pt").value;
  param   = "method=getAlokasi&pt="+pt;
  tujuan  = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("alokasi").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getAlokasicopy() {
  pt      = document.getElementById("ptcopy").value;
  param   = "method=getAlokasi&pt="+pt;
  tujuan  = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("alokasicopy").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getSup() {
  pt      = document.getElementById("pt").value;
  tipetbs = document.getElementById("tipetbs").value;
  param   = "method=getSup&pt="+pt+"&tipetbs="+tipetbs;
  tujuan  = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("supplier").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getSupcopy() {
  pt      = document.getElementById("ptcopy").value;
  tipetbs = document.getElementById("tipetbscopy").value;
  param   = "method=getSup&pt="+pt+"&tipetbs="+tipetbs;
  tujuan  = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("suppliercopy").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getRek() {
  supplier      = document.getElementById("supplier").value;
  param   = "method=getRek&supplier="+supplier;
  tujuan  = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          // alert(con.responseText);
          document.getElementById("rekening").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function edit(pt, supplier,rekening,tipetbs, notransaksi) {
  param = "method=edit" + "&pt=" + pt + "&supplier=" + supplier+ "&rekening=" + rekening+ "&tipetbs=" + tipetbs+ "&notransaksi=" + notransaksi;
  tujuan = "pmn_5feepembeliantbs_slave.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          ar = con.responseText.split("###");
          document.getElementById("pt").disabled = true;
          document.getElementById("alokasi").disabled = true;
          document.getElementById("supplier").disabled = true;
          document.getElementById("rekening").disabled = true;
          document.getElementById("tipetbs").disabled = true;
        
          document.getElementById("pt").innerHTML = ar[0];
          document.getElementById("tipetbs").innerHTML = ar[1];
          document.getElementById("supplier").innerHTML = ar[2];
          document.getElementById("tanggaldari").value = ar[3];
          document.getElementById("tanggalsampai").value = ar[4];
          document.getElementById("batasbawah").value = ar[5];
          document.getElementById("batasatas").value = ar[6];
          document.getElementById("rpkg").value = ar[7];
          document.getElementById("rekening").innerHTML = ar[8];
          document.getElementById("alokasi").innerHTML = ar[9];
          document.getElementById("debet").value = ar[10];
          document.getElementById("kredit").value = ar[11];
          document.getElementById("method").value = ar[12];
          document.getElementById("persenppn").value = ar[13];
          document.getElementById("notransaksi").value = notransaksi;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function editFill(pt,tipetbs,supplier,tanggaldari,tanggalsampai,batasbawah,batasatas,rpkg,rekening) {
  document.getElementById("pt").disabled = true;
  document.getElementById("supplier").disabled = true;
  document.getElementById("rekening").disabled = true;
  document.getElementById("pt").value = pt;
  document.getElementById("supplier").innerHTML = supplier;
  document.getElementById("tanggaldari").value = tanggaldari;
  document.getElementById("tanggalsampai").value = tanggalsampai;
  document.getElementById("batasbawah").value = batasbawah;
  document.getElementById("batasatas").value = batasatas;
  document.getElementById("rpkg").value = rpkg;
  document.getElementById("tipetbs").innerHTML = tipetbs;
  document.getElementById("rekening").value = rekening;
  document.getElementById("method").value = "update";
}

function del(pt, supplier,rekening, notransaksi) {
  param = "method=delete" + "&pt=" + pt + "&supplier=" + supplier+ "&rekening=" + rekening+ "&notransaksi=" + notransaksi;
  tujuan = "pmn_5feepembeliantbs_slave.php";
   alertify.confirm("Informasi","Anda yakin ingin menghapus data ini?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
  
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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

function formajukan(pt,notransaksi,page){
  param = 'pt='+pt+'&notransaksi='+notransaksi+'&page='+page+'&method=formajukan';
  tujuan = 'pmn_5feepembeliantbs_slave.php';

  content = "<div id=formajukan style=\"height:100%;width:100%;\"></div>";
  title = 'Posting';
  height = '';
  width = 300;
  // showDialog4(title, content, width, height, 'event');

  post_response_text(tujuan, param, respon);
  function respon() {
      if (con.readyState == 4) {
          if (con.status == 200) {
              busy_off();
              if (!isSaveResponse(con.responseText)) {
                  alertify.alert("Informasi",con.responseText);
              } else {
                  // document.getElementById('formajukan').innerHTML = con.responseText;
				  
				  alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('400px','300px'); 
              }
          } else {
              busy_off();
              error_catch(con.status);
          }
      }
  }
} 

function posting(notransaksi,maxaproval,page) {
  param = '';
  method = 'posting';
  tanggalpengajuan = document.getElementById('tanggalpengajuan').value; 

  strper = '';
  for(i=1;i<=maxaproval;i++){
   strper += '&persetujuan['+i+']='+trim(document.getElementById('persetujuan'+i).value)
  }
  param += 'notransaksi=' + notransaksi + '&tanggalpengajuan=' + tanggalpengajuan;
  param += '&maxaproval=' + maxaproval;
  param += '&method=' + method;
  param += strper;  
  // alert(strper);return;
  tujuan = 'pmn_5feepembeliantbs_slave.php';

  alertify.confirm("Informasi","Yakin ingin memposting ???",
    function(){
      post_response_text(tujuan, param, respon);
    },
    function(){
      return;
    }
  );  
  
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert('Informasi',con.responseText);
        } else {
          // closeDialog4();
		  alertify.popup().destroy();
          loadData(page);
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }  
} 

function detailpost(notransaksi){
  param = 'notransaksi='+notransaksi+'&method=detailpost';
  tujuan = 'pmn_5feepembeliantbs_slave.php';

  content = "<div id=detailpost style=\"height:100%;width:100%;\"></div>";
  title = 'Posting';
  height = '';
  width = 500;
  // showDialog4(title, content, width, height, 'event');

  post_response_text(tujuan, param, respon);
  function respon() {
      if (con.readyState == 4) {
          if (con.status == 200) {
              busy_off();
              if (!isSaveResponse(con.responseText)) {
                  alertify.alert("Informasi",con.responseText);
              } else {
                  // document.getElementById('detailpost').innerHTML = con.responseText;
				  alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','50%'); 
              }
          } else {
              busy_off();
              error_catch(con.status);
          }
      }
  }
} 