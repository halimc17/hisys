function simpan(){
    kodeorg=document.getElementById('kodeorg').value;
    kodetangki=document.getElementById('kodetangki').value;
    komoditi=document.getElementById('komoditi').value;
    kapasitas=document.getElementById('kapasitas').value;
    keterangan=document.getElementById('keterangan').value;
    cycling=document.getElementById('cycling').value;
    method=document.getElementById('method').value;

    validate([
            ["kodeorg","Kode organisasi tidak boleh kosong."],
            ["kodetangki","Kode tangki tidak boleh kosong"],
            ["komoditi","Komoditi tidak boleh kosong"],
            ["kapasitas","Kapasitas tidak boleh kosong"],
            ["keterangan","Keterangan tidak boleh kosong"]
        ]);

    param='kodeorg='+kodeorg+'&kodetangki='+kodetangki+'&keterangan='+keterangan+'&komoditi='+komoditi+'&kapasitas='+kapasitas+'&cycling='+cycling+'&method='+method;
    tujuan='pabrik_slave_5tangkiv2.php';
    post_response_text(tujuan, param, respog);		
	
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
						}
						else {
							cancel();
							loadData();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}
					


function cancel() {
	document.getElementById('kodeorg').disabled=false;
    document.getElementById('kodetangki').disabled=false;
    document.getElementById('komoditi').disabled=false;
    document.getElementById('kodeorg').value='';
    document.getElementById('kodetangki').value='';
    document.getElementById('komoditi').value='';
    document.getElementById('kapasitas').value='';
    document.getElementById('keterangan').value='';
    document.getElementById('cycling').value='';
    document.getElementById('method').value='insert';
}


function loadData () {
	param='method=loadData';
	tujuan='pabrik_slave_5tangkiv2.php';
    post_response_text(tujuan, param, respog);
	function respog()
	{
              if(con.readyState==4)
              {
                    if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert(con.responseText);
                                }
                                else {
                                    document.getElementById('container').innerHTML=con.responseText;
									
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}

function edit(kodeorg,kodetangki,komoditi,kapasitas,keterangan,cycling) {
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('kodetangki').disabled=true;
    document.getElementById('komoditi').disabled=true;
    document.getElementById('kodeorg').value=kodeorg;
    document.getElementById('kodetangki').value=kodetangki;
    document.getElementById('komoditi').value=komoditi;
    document.getElementById('kapasitas').value=kapasitas;
    document.getElementById('keterangan').value=keterangan;
    document.getElementById('cycling').value=cycling;
    document.getElementById('method').value='update';
}



function del(kodeorg,kodetangki)
{
	param='method=delete'+'&kodeorg='+kodeorg+'&kodetangki='+kodetangki;
	tujuan='pabrik_slave_5tangkiv2.php';
	alertify.confirm("Warning","Apakah yakin hapus data?",
      function(){
        post_response_text(tujuan, param, respog);  
      },
      function(){
        return;
      }).set('resizable',false).resizeTo(100,250);	
	function respog()
	{
		  if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else 
					{
						loadData();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}




