function getsubunit() {
    unit = document.getElementById("unit").value;
  
    param = "method=getsubunit&unit=" + unit;
    tujuan = "kebun_slave_2newcurahhujan.php";
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

function getsubunit2() {
    unit2 = document.getElementById("unit2").value;
  
    param = "method=getsubunit2&unit2=" + unit2;
    tujuan = "kebun_slave_2newcurahhujan.php";
    post_response_text(tujuan, param, respog);
  
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            document.getElementById("subunit2").innerHTML = con.responseText;
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
      periode ;
    tujuan = "kebun_slave_2newcurahhujan.php";
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

  function preview2(tipeprint, ev) {
    unit2 = document.getElementById("unit2").value;
    subunit2 = document.getElementById("subunit2").value;
    periode2 = document.getElementById("periode2").value;
  
    param =
      "method=preview2&tipeprint=" +
      tipeprint +
      "&unit2=" +
      unit2 +
      "&subunit2=" +
      subunit2 +
      "&periode2=" +
      periode2 ;
    tujuan = "kebun_slave_2newcurahhujan.php";
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