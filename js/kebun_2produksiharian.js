function getorg() {
    tipe        = document.getElementById("tipe").value;

    param =
    "method=getorg&tipe=" + tipe ;
        
    tujuan = "kebun_slave_2produksiharian.php";
    post_response_text(tujuan, param, respog);
  
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            document.getElementById("unit").innerHTML = con.responseText;
            document.getElementById("divisi").value = '';
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  }


  function getdivisi() {
    unit        = document.getElementById("unit").value;

    param =
    "method=getdivisi&unit=" + unit ;
        
    tujuan = "kebun_slave_2produksiharian.php";
    post_response_text(tujuan, param, respog);
  
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            document.getElementById("divisi").innerHTML = con.responseText;
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  }


function preview(tipeprint, ev) {
    tipe        = document.getElementById("tipe").value;
    unit        = document.getElementById("unit").value;
    divisi      = document.getElementById("divisi").value;
    periode     = document.getElementById("periode").value;
    periode2    = document.getElementById("periode2").value;

    param =
      "method=preview&tipeprint=" +
      tipeprint +
      "&tipe=" +
      tipe +
      "&unit=" +
      unit +
      "&divisi=" +
      divisi +
      "&periode=" +
      periode +
      "&periode2=" +
      periode2;

    tujuan = "kebun_slave_2produksiharian.php";
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