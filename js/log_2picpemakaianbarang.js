function preview(tipeprint, ev) {
    unit=document.getElementById('unit').value;
	periode =document.getElementById('periode').value;
    periode2 =document.getElementById('periode2').value;
    kodebarang =document.getElementById('kodebarang').value;

	param =
	"proses=preview&tipeprint=" +
	tipeprint +
	"&unit=" +
	unit +
	"&kodebarang=" +
	kodebarang +
	"&periode=" +
	periode +
	"&periode2=" +
	periode2;
	tujuan='log_slaveLaporanpicpemakaianbarang.php';
  
	if (tipeprint != "html") {
		// judul = tipeprint;
		// ev = "event";
		// printFile(param, tujuan, judul, ev);
    printnopopup(tujuan+'?'+param)
	  }else{
      post_response_text(tujuan, param, respog);
    }
  
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
