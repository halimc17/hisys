function getsubunit() {
  unit = document.getElementById("unit").value;

  param = "method=getsubunit&unit=" + unit;
  tujuan = "log_slave_2operasionalbbm.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("subunit").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function preview(tipeprint, ev) {
  unit = document.getElementById("unit").value;
  subunit = document.getElementById("subunit").value;
  periode = document.getElementById("periode").value;

  param =
    "method=preview&tipeprint=" +
    tipeprint +
    "&unit=" +
    unit +
    "&subunit=" +
    subunit +
    "&periode=" +
    periode;
  tujuan = "log_slave_2operasionalbbm.php";
  if (tipeprint != "html") {
    judul = tipeprint;
    ev = "event";
    printFile(param, tujuan, judul, ev);
  }
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("printContainer").innerHTML = con.responseText;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "";
  height = "";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}

function detail(kodemesin, tanggal) {

  param = 'kodemesin=' + kodemesin + '&tanggal=' + tanggal;
  tujuan = 'log_slave_2operasionalbbm.php';
  function respog() {
      if (con.readyState == 4) {
          if (con.status == 200) {
              busy_off();
              if (!isSaveResponse(con.responseText)) {
                  alert(con.responseText);
              } else {
                  alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
              }
          } else {
              busy_off();
              error_catch(con.status);
          }
      }
  }
  post_response_text(tujuan + '?' + 'method=detail', param, respog);
}

// function downloadPdf() {
//   const tanggal = document.querySelector("#tgldetail").value;
//   const nik = document.querySelector("#nikdetail").value;
//   const karyawanid = document.querySelector("#karyawaniddetail").value;
//   let param = `method=downloadpdf&tanggal=${tanggal}&nik=${nik}&karyawanid=${karyawanid}`;
//   let tujuan = "log_slave_2operasionalbbm.php";
//   post_response_text(tujuan, param, respog);
//   function respog() {
//     if (con.readyState == 4) {
//       if (con.status == 200) {
//         busy_off();
//         if (!isSaveResponse(con.responseText)) {
//           alert(con.responseText);
//         } else {
//           document.querySelector("#containerdetail").innerHTML =
//             con.responseText;
//         }
//       } else {
//         busy_off();
//         error_catch(con.status);
//       }
//     }
//   }
// }
function downloadPdf(tipe,ev) {
  const tanggal = document.querySelector("#tgldetail").value;
  const nik = document.querySelector("#nikdetail").value;
  const karyawanid = document.querySelector("#karyawaniddetail").value;
  let param = `method=downloadpdf&tanggal=${tanggal}&nik=${nik}&karyawanid=${karyawanid}`;
  let tujuan = "log_slave_2operasionalbbm.php";
  // alertify.popuphist().destroy();
  // alertify.alert().close();
  alertify.closeAll()
  title='Laporan Fingerprint';
	width='1000';
	height='500';
	content="<iframe frameborder=0 width=100% height=100% style=z-index:9999; src='"+tujuan+"?"+param+"'></iframe>"

	showDialog1(title,content,width,height,ev);
}