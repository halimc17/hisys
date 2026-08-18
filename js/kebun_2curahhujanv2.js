function prevHujan() {
  let param = `method=previewhujan`;
  let tujuan = "kebun_slave_2curahhujanv2.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function prevHujan2(tipe = "html") {
  let param = `method=previewhujan2&periode=${
    document.querySelector("#periode").value
  }&divisi=${document.querySelector("#divisi").value}&tipe=${tipe}`;
  let tujuan = "kebun_slave_2curahhujanv2.php";

  if (tipe == "excel") {
    printnopopup(tujuan + "?" + param);
  } else if (tipe == "pdf") {
    alertify
      .popuppdf(
        "title",
        "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_2curahhujanv2.php?" +
          param +
          "'></iframe>"
      )
      .set({ resizable: true, overflow: false })
      .resizeTo("90%", "80%");
  } else {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          document.getElementById("filtercurahhujan").innerHTML =
            con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function detailCh(divisi, tanggal) {
  let param = `method=getDetail&divisi=${divisi}&tanggal=${tanggal}`;
  let tujuan = "kebun_slave_2curahhujanv2.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
        } else {
          alertify
            .popup(
              `Detail Curah hujan: ${divisi} - ${tanggal}`,
              con.responseText
            )
            .set({ resizable: true, maximizable: true })
            .resizeTo("80%", "70%");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

document.addEventListener("DOMContentLoaded", function () {
  prevHujan();
  prevHujan2();
  setInterval(prevHujan, 60 * 1000); // 60 seconds
});
