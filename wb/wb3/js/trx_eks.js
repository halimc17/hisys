$('#so').on("select2:select", function(e) { 
	getso();
});

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

function getdivisi(divisi) {
	unit = getValue('unit');
	divisi = divisi;
    param='method=getdivisi&unit='+unit+'&divisi='+divisi;
    tujuan='trx_eks_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('divisi').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function simpan(){
	// paramgp='';
	// var gp = document.getElementsByName('persen');
	// for (var i = 0; i < gp.length; i++){
		// if(gp[i].value > 0 && gp[i].value != ''){
			// paramgp+="&"+gp[i].id+"="+gp[i].value;
		// }
	// }
	// alert(paramdt);
	// return false;
	
	method = document.getElementById('method').value;
	ticketno = document.getElementById('ticketno').value;
	unit = getValue('unit');
	divisi = getValue('divisi');
	nospb = document.getElementById('nospb').value;
	transportir = document.getElementById('transportir').value;
	nokendaraan = document.getElementById('nokendaraan').value;
	supir = document.getElementById('supir').value;
	jjg = document.getElementById('jjg').value;
	brondol = document.getElementById('brondol').value;
	keterangan = document.getElementById('keterangan').value;
	
	wei1st = document.getElementById('wei1st').value;
	wei2nd = document.getElementById('wei2nd').value;
	datein = document.getElementById('datein').value;
	dateout = document.getElementById('dateout').value;
	bruto = document.getElementById('bruto').value;
	kgpotongan = document.getElementById('kgpotongan').value;
	netto = document.getElementById('netto').value;

	rowsortasi = document.getElementById('rowsortasi').value;
	rowkualitas = document.getElementById('rowkualitas').value;

	if(method=='timbang1'){
		validate([
			["nospb","No. SPB tidak boleh kosong"],
			["wei1st","Berat timbang 1 tidak boleh kosong"]
		]);
	}

	paramgp='';
	if(method=='timbang2'){
		validate([
			["nospb","No. SPB tidak boleh kosong"],
			["wei1st","Berat timbang 1 tidak boleh kosong"],
			["wei2nd","Berat timbang 2 tidak boleh kosong"]
		]);
		
		var gp = document.getElementsByName('jjg');
		for (var i = 0; i < gp.length; i++){
			if(gp[i].value > 0 && gp[i].value != ''){
				paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
			}
		}
		var gp = document.getElementsByName('persen');
		for (var i = 0; i < gp.length; i++){
			if(gp[i].value > 0 && gp[i].value != ''){
				paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
			}
		}
		var gp = document.getElementsByName('kg');
		for (var i = 0; i < gp.length; i++){
			if(gp[i].value > 0 && gp[i].value != ''){
				paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
			}
		}
	}

	param='ticketno='+ticketno+'&unit='+unit+'&divisi='+divisi+'&nospb='+nospb+'&transportir='+transportir+'&nokendaraan='+nokendaraan+'&supir='+supir+'&keterangan='+keterangan;
	param+='&wei1st='+wei1st+'&wei2nd='+wei2nd+'&datein='+datein+'&dateout='+dateout+'&kgpotongan='+kgpotongan+'&bruto='+bruto+'&netto='+netto;
	param+='&method='+method;
	param+=paramgp;
	
	tujuan='trx_eks_slave.php';
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
                        // cetaktiket(ticketno);
                    }

                    // batal();
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function fillfield(ticketno) {
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='trx_eks_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
					document.getElementById('tdgrading').style.display='block';
					showontop();
					var arrlist = new Array();
					arrlist = JSON.parse(con.responseText);
					
					setValue2('ticketno',arrlist[0]['notiket']);
					setValue2('unit',arrlist[0]['unit']);
					getdivisi(arrlist[0]['divisi']);
					setValue2('nospb',arrlist[0]['nospb']);
					setValue2('transportir',arrlist[0]['transportir']);
					setValue2('nokendaraan',arrlist[0]['nokendaraan']);
					setValue2('supir',arrlist[0]['supir']);
					setValue2('jjg',arrlist[0]['jjg']);
					setValue2('brondol',arrlist[0]['brondolan']);
					setValue2('keterangan',arrlist[0]['keterangan']);
					
					setValue2('datein',arrlist[0]['waktumasuk']);
					setValue2('wei1st',arrlist[0]['timbang1']);
					
					
					setValue2('dateout','');
					setValue2('wei2nd','');
					setValue2('bruto','');
					setValue2('netto','');
					setValue2('kgpotongan','');
					document.getElementById('getweight1').disabled=true;
                    document.getElementById('getweight2').disabled=false;
					setValue2('method','timbang2');	

					//GRADING
					var gp = document.getElementsByName('jjg');
					for (var i = 0; i < gp.length; i++){
						gp[i].value='';
					}
					var gp = document.getElementsByName('persen');
					for (var i = 0; i < gp.length; i++){
						gp[i].value='';
					}
					var gp = document.getElementsByName('kg');
					for (var i = 0; i < gp.length; i++){
						gp[i].value='';
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function hitungsortasi(namaele,idele){
	
	// netto=document.getElementById('weight').value;
	// ist=document.getElementById(idelemen).value;
	
	// alert(namaele);
    // rowsortasi = document.getElementById('rowsortasi').value;
    // if (idelemen.length==1) {
        // id = idelemen.substr(13,1);
    // }else{
        // id = idelemen.substr(13,2);
    // }
    // persen = document.getElementById(idelemen).value;
    // bruto = document.getElementById('bruto').value;

    // berat = ((persen*bruto)/100).toFixed(0);

    // document.getElementById('beratsortasi'+id).value=berat;
    // hitungberatpotongan(rowsortasi);
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

function generatenotiket() {
    param='method=generatenotiket';
    tujuan='trx_eks_slave.php';
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
    tujuan='trx_eks_slave.php';
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
    param='method=loadData';
	tujuan='trx_eks_slave.php';
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
                    // formsortasi();
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


