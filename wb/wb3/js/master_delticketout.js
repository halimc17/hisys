const tabhapus = document.getElementById('tabhapus');
if (tabhapus) {
	tabhapus.addEventListener('click', function () {
		hapus();
	});
}

const tabbatal = document.getElementById('tabbatal');
if (tabbatal) {
	tabbatal.addEventListener('click', function () {
		batal();
	});
}

function batal(){
	document.getElementById('notiket').value='';
	document.getElementById('catatan').value='';
}

function hapus(){
	notiket = document.getElementById('notiket').value;
	catatan = document.getElementById('catatan').value;
	param='ticketno='+notiket;
	param+='&catatan='+catatan;
	
	validate([
		["notiket","No. Tiket tidak boleh kosong"],
		["catatan","Catatan tidak boleh kosong"]
	]);
	
	tujuan='master_delticketout_slave.php';
	alertify.confirm("Konfirmasi","Apakah anda yakin hapus tiket keluar "+notiket+"??",
	function(){
		post_response_text(tujuan, param, respog);
	},
	function(){
		return;
	}).set('resizable',false).resizeTo(100,250);
    
	function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
					batal();
					alertify.set('notifier','position', 'top-right');
                    alertify.success('Berhasil, No. Tiket keluar '+notiket+' sudah dihapus dari sistem.');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}