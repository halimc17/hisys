const filePath = "pabrik_slave_2produksiBulanan.php";

const preview = (method) => {
  const periode = document.getElementById("periode").value;
  const kode_pabrik = document.getElementById("kode_pabrik").value;

  let param = `method=${method}&periode=${periode}&kode_pabrik=${kode_pabrik}`;

  post_response_text(filePath, param, respog);
    
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
};
