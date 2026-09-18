// JavaScript Document
function checktipe()
{
	if(document.getElementById('tipe').checked)
	{
		document.getElementById('kodekelompokc').style.display = "";
	}
	else
	{
		document.getElementById('kodekelompokc').style.display = "none";
	}
}

function copyData()
{
    kodePt1=document.getElementById('kodeorg1').options[document.getElementById('kodeorg1').selectedIndex].value;
    kodeUnit1=document.getElementById('kodeunit1').options[document.getElementById('kodeunit1').selectedIndex].value;
    periode1=document.getElementById('periode1').options[document.getElementById('periode1').selectedIndex].value;
    kodePt2=document.getElementById('kodeorg2').options[document.getElementById('kodeorg2').selectedIndex].value;
    kodeUnit2=document.getElementById('kodeunit2').options[document.getElementById('kodeunit2').selectedIndex].value;
    periode2=document.getElementById('periode2').options[document.getElementById('periode2').selectedIndex].value;
    tipe=document.getElementById('tipe').checked;
    kodekelompok=document.getElementById('kodekelompokc').value;

    if(kodePt1 == '' || kodeUnit1 == '' || periode1 == '')
    {
    	alertify.alert("Informasi","Kode Organisasi, Unit, dan Periode tujuan wajib dipilih");
    	return;
    }
    if(kodePt2 == '' || kodeUnit2 == '' || periode2 == '')
    {
    	alertify.alert("Informasi","Kode Organisasi, Unit, dan Periode 'Duplikat dari' wajib dipilih");
    	return;
    }
    if(kodePt1 == kodePt2 && kodeUnit1 == kodeUnit2 && periode1 == periode2)
    {
    	alertify.alert("Informasi","Organisasi, Unit, dan Periode tujuan tidak boleh sama persis dengan sumber");
    	return;
    }
    if(tipe && kodekelompok == '')
    {
    	alertify.alert("Informasi","Kode Kelompok belum dipilih");
    	return;
    }

    alertify.confirm("Warning","Proses ini akan menghapus data Kelompok Jurnal Unit <b>"+kodeUnit1+"</b> periode <b>"+periode1+"</b> yang sudah ada, lalu menggantinya dengan salinan dari Unit <b>"+kodeUnit2+"</b> periode <b>"+periode2+"</b>. Anda yakin?",
    	function(){
			param='proses=copyData'+'&kodePt1='+kodePt1+'&kodeUnit1='+kodeUnit1+'&periode1='+periode1+'&kodePt2='+kodePt2+'&kodeUnit2='+kodeUnit2+'&periode2='+periode2+'&tipe='+tipe+'&kodekelompok='+kodekelompok;
			tujuan='keu_slave_5kelompokjurnal_copy.php';
			post_response_text(tujuan, param, respog);
    	},
    	function(){
    		return;
    	}
    );

	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alertify.alert("Informasi",con.responseText);
						}
						else {
                                                if(con.responseText==1)
                                                    {
                                                        alertify.alert("Informasi","Copy Berhasil");
                                                    }
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }
	 }

}
