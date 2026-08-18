  function preview(tipeprint, ev) {
    kodeorg = document.getElementById("kodeorg").value;
    periodegaji = document.getElementById("periodegaji").value;
  
    param =
      "method=preview&tipeprint=" +
      tipeprint +
      "&kodeorg=" +
      kodeorg +
      "&periodegaji=" +
      periodegaji;
    tujuan = "sdm_slave_2estimasigaji.php";
    
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
