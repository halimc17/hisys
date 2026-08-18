// inspectelementOff();
let code = '';
let reading = false;
document.addEventListener('keypress', e=>{
    if (e.keyCode===13){
        if (code) {     
            document.getElementById('nospb').value=code;
        }
  }else{
       code+=e.key;
  }
   
  if(!reading){
         reading=true;
         setTimeout(()=>{
          code='';
          reading=false;
      }, 2000);
  }
});

function hitungsortasi(idelemen){
    rowsortasi = document.getElementById('rowsortasi').value;
    if (idelemen.length==1) {
        id = idelemen.substr(13,1);
    }else{
        id = idelemen.substr(13,2);
    }
    persen = document.getElementById(idelemen).value;
    bruto = document.getElementById('bruto').value;

    berat = ((persen*bruto)/100).toFixed(0);

    document.getElementById('beratsortasi'+id).value=berat;
    hitungberatpotongan(rowsortasi);
}

function hitungberatpotongan(row){
    var totalberatpotongan = 0;
    var berat = 0;
    for(var i = 1; i <= row; i++) {
        berat = document.getElementById('beratsortasi'+i).value;
        if (berat !== '') {
            totalberatpotongan += parseInt(berat);
        }
    }

    document.getElementById('kgpotongan').value=totalberatpotongan;
    netto = document.getElementById('bruto').value-document.getElementById('kgpotongan').value;
    document.getElementById('netto').value=netto;
}

function batal(){
  document.getElementById('nospb').value='';
  document.getElementById('nokontrak').value='';
  document.getElementById('noproductionorder').value='';
  document.getElementById('transportir').value='';
  document.getElementById('nokendaraan').value='';
  document.getElementById('supir').value='';
  document.getElementById('deliverynote').value='';
  document.getElementById('bruto').value='';
  document.getElementById('kgpotongan').value='';
  document.getElementById('netto').value='';
  document.getElementById('datein').value='';
  document.getElementById('dateout').value='';
  document.getElementById('wei1st').value='';
  document.getElementById('wei2nd').value='';
  document.getElementById('getweight1').disabled=false;
  document.getElementById('getweight2').disabled=true;
  document.getElementById('method').value='timbang1';
}

function fillfield(ticketno) {
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='trx_in_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    data=con.responseText.split("#");
                    document.getElementById('ticketno').value=data[0];
                    document.getElementById('kodeproduk').value=data[1];
                    document.getElementById('nospb').value=data[2];
                    document.getElementById('nokontrak').value=data[3];
                    document.getElementById('noproductionorder').value=data[4];
                    document.getElementById('transportir').value=data[5];
                    document.getElementById('nokendaraan').value=data[6];
                    document.getElementById('supir').value=data[7];
                    document.getElementById('deliverynote').value=data[8];
                    document.getElementById('datein').value=data[9];
                    document.getElementById('wei1st').value=data[10];
                    document.getElementById('getweight1').disabled=true;
                    document.getElementById('getweight2').disabled=false;
                    document.getElementById('method').value='timbang2';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function generatenotiket() {
    param='method=generatenotiket';
    tujuan='trx_in_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('ticketno').value=con.responseText;    
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function getkontrak() {
	kodeproduk = document.getElementById('kodeproduk').value;
    param='method=getkontrak'+'&kodeproduk='+kodeproduk;
    tujuan='trx_in_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('nokontrak').innerHTML=con.responseText;
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function loadData() {
    kodeproduk = document.getElementById('kodeproduk').value;
	param='method=loadData'+'&kodeproduk='+kodeproduk;
	tujuan='trx_in_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info",con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
                    generatenotiket();
                    formsortasi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function formsortasi() {
    kodeproduk = document.getElementById('kodeproduk').value;

    if (kodeproduk=='40000003') {
        document.getElementById('tdsortasi').style.display="block";
        document.getElementById('tdkualitas').style.display="none";
    }else if (kodeproduk=='40000001' || kodeproduk=='40000002'){
        document.getElementById('tdkualitas').style.display="block";
        document.getElementById('tdsortasi').style.display="none";
    }else{
        document.getElementById('tdsortasi').style.display="none";
        document.getElementById('tdkualitas').style.display="none";
    }
}

function simpan(){
  method = document.getElementById('method').value;
  ticketno = document.getElementById('ticketno').value;
  kodeproduk = document.getElementById('kodeproduk').value;
  nospb = document.getElementById('nospb').value;
  nokontrak = document.getElementById('nokontrak').value;
  noproductionorder = document.getElementById('noproductionorder').value;
  transportir = document.getElementById('transportir').value;
  nokendaraan = document.getElementById('nokendaraan').value;
  supir = document.getElementById('supir').value;
  deliverynote = document.getElementById('deliverynote').value;

  wei1st = document.getElementById('wei1st').value;
  wei2nd = document.getElementById('wei2nd').value;
  datein = document.getElementById('datein').value;
  dateout = document.getElementById('dateout').value;
  bruto = document.getElementById('bruto').value;
  kgpotongan = document.getElementById('kgpotongan').value;
  netto = document.getElementById('netto').value;

  rowsortasi = document.getElementById('rowsortasi').value;
  rowkualitas = document.getElementById('rowkualitas').value;

  validate([
    ["ticketno","No Ticket tidak boleh kosong"],
    ["kodeproduk","Kode produk tidak boleh kosong"],
    ["wei1st","Berat timbang 1 tidak boleh kosong"]
    ]);

  param='ticketno='+ticketno+'&kodeproduk='+kodeproduk+'&nospb='+nospb+'&nokontrak='+nokontrak+'&noproductionorder='+noproductionorder+'&transportir='+transportir+'&nokendaraan='+nokendaraan+'&supir='+supir+'&deliverynote='+deliverynote;
  param+='&wei1st='+wei1st+'&wei2nd='+wei2nd+'&datein='+datein+'&dateout='+dateout+'&netto='+netto+'&kgpotongan='+kgpotongan+'&bruto='+bruto;
  param+='&method='+method;
  param+='&rowsortasi='+rowsortasi;
  param+='&rowkualitas='+rowkualitas;

    for(var i = 1; i <= rowsortasi; i++) {
        persensortasi = document.getElementById('persensortasi'+i).value;
        beratsortasi = document.getElementById('beratsortasi'+i).value;
        
        param+='&persensortasi'+i+'='+persensortasi;
        param+='&beratsortasi'+i+'='+beratsortasi;
    }

    for(var i = 1; i <= rowkualitas; i++) {
        nilaikualitas = document.getElementById('nilaikualitas'+i).value;
        
        param+='&nilaikualitas'+i+'='+nilaikualitas;
    }

  tujuan='trx_in_slave.php';
  post_response_text(tujuan, param, respog);

    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    alertify.set('notifier','position', 'top-right');
                    alertify.success('Success');
                    
                    if (method=='timbang2') {
                        cetaktiket(ticketno);
                    }

                    batal();
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}


document.addEventListener('DOMContentLoaded', function () {
	loadData();
});

$('#kodeproduk').on("select2:selecting", function(e) { 
	getkontrak();
});


const btngetweight1 = document.getElementById('getweight1');
if (btngetweight1) {
  btngetweight1.addEventListener('click', function () {
    ambil_tanggal('datein','wei1st','penerimaan');
  });
}

const btngetweight2 = document.getElementById('getweight2');
if (btngetweight2) {
  btngetweight2.addEventListener('click', function () {
    ambil_tanggal('dateout','wei2nd','penerimaan');
  });
}

const btnsimpan = document.getElementById('simpan');
if (btnsimpan) {
	btnsimpan.addEventListener('click', function () {
	  simpan();
	});
}


