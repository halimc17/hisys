function getsubunit() {
    unit = document.getElementById("unit").value;
  
    param = "method=getsubunit&unit=" + unit;
    tujuan = "sdm_slave_2upahpremi_panenrawat.php";
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
    tipekaryawan = document.getElementById("tipekaryawan").value;
  
    param =
      "method=preview&tipeprint=" +
      tipeprint +
      "&unit=" +
      unit +
      "&subunit=" +
      subunit +
      "&periode=" +
      periode +
      "&tipekaryawan=" +
      tipekaryawan;
    tujuan = "sdm_slave_2upahpremi_panenrawat.php";
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


  function detailrawat(karyawanid, tgl) {

    param = 'karyawanid=' + karyawanid + '&tanggal=' + tgl;
    tujuan = 'sdm_slave_2upahpremi_panenrawat.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup("Detail Rawat",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan + '?' + 'method=detailrawat', param, respog);
  }

  function detailpanen(karyawanid, tgl) {

    param = 'karyawanid=' + karyawanid + '&tanggal=' + tgl;
    tujuan = 'sdm_slave_2upahpremi_panenrawat.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup("Detail Panen",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan + '?' + 'method=detailpanen', param, respog);
  }
