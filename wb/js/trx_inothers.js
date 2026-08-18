$('#supplier').on("select2:select", function(e) { 
	getkontrak();
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

function getkontrak(so='',newFunc){
	supplier = getValue('supplier');
	param='method=getkontrak&supplier='+supplier+'&so='+so;
    tujuan='trx_inothers_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('so').innerHTML=con.responseText;
					if(typeof newFunc !== 'undefined' && typeof newFunc == 'function'){
						eval(newFunc());
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function simpan(){
	method = getValue('method');
	ticketno = getValue('ticketno');
	qrcode = getValue('qrcode');
	produk = getValue('produk');
	supplier = getValue('supplier');
	so = getValue('so');
	transportir = getValue('transportir');
	pemilik = getValue('pemilik');
	nokendaraan = getValue('nokendaraan');
	supir = getValue('supir');
	nopo = getValue('nopo');
	nosim = getValue('nosim');
	qtysegel = getValue('qtysegel');
	segel = getValue('segel');
	keterangan = getValue('keterangan');
	
	wei1st = document.getElementById('wei1st').value;
	wei2nd = document.getElementById('wei2nd').value;
	datein = document.getElementById('datein').value;
	dateout = document.getElementById('dateout').value;
	bruto = document.getElementById('bruto').value;
	kgpotongan = document.getElementById('kgpotongan').value;
	netto = document.getElementById('netto').value;

	if(method=='timbang1'){
		validate([
			["qrcode","No. Surat Pengiriman tidak boleh kosong"],
			["produk","Produk tidak boleh kosong"],
			["supplier","Supplier tidak boleh kosong"],
			["nokendaraan","No. Kendaraan tidak boleh kosong"],
			["supir","Nama Driver tidak boleh kosong"],
			["wei1st","Berat timbang 1 tidak boleh kosong"]
		]);
	}

	paramgp='';
	if(method=='timbang2'){
		validate([
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

	param='ticketno='+ticketno+'&qrcode='+qrcode+'&produk='+produk+'&supplier='+supplier+'&so='+so+'&transportir='+transportir+'&pemilik='+pemilik+'&nokendaraan='+nokendaraan+'&supir='+supir;
	param+='&nosim='+nosim+'&qtysegel='+qtysegel+'&segel='+segel+'&keterangan='+keterangan+'&nopo='+nopo;
	param+='&wei1st='+wei1st+'&wei2nd='+wei2nd+'&datein='+datein+'&dateout='+dateout+'&kgpotongan='+kgpotongan+'&bruto='+bruto+'&netto='+netto;
	param+='&method='+method;
	param+=paramgp;
	param2='ticketno='+ticketno;
	
	tujuan='trx_inothers_slave.php';
	tujuan2="printticket.php?"+param2+'&method=printticket';
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
					if (method=='timbang1') {
						if(con.responseText == 'terdaftar'){
							printnopopupx(tujuan2);
						}else{
							showtobottom();
						}
					}
                    if (method=='timbang2') {
						//printticket(tujuan2);
						printnopopupx(tujuan2);
                    }
					batal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function batal(){
	showontop();
	setValue2('qrcode','');
	setValue2('nopo','');
	setValue2('produk','');
	setValue2('supplier','');
	setValue2('transportir','');
	setValue2('pemilik','');
	setValue2('nokendaraan','');
	setValue2('nosim','');
	setValue2('supir','');
	setValue2('qtysegel','');
	setValue2('segel','');
	setValue2('keterangan','');
  
	setValue2('bruto','');
	setValue2('kgpotongan','');
	setValue2('netto','');
	setValue2('datein','');
	setValue2('datein','');
	setValue2('dateout','');
	setValue2('wei1st','');
	setValue2('wei2nd','');
	
	document.getElementById('qrcode').disabled=false;
	document.getElementById('nopo').disabled=false;
	document.getElementById('produk').disabled=false;
	document.getElementById('supplier').disabled=false;
	document.getElementById('so').disabled=false;
	document.getElementById('transportir').disabled=false;
	document.getElementById('pemilik').disabled=false;
	document.getElementById('nokendaraan').disabled=false;
	document.getElementById('supir').disabled=false;
	document.getElementById('nosim').disabled=false;
	document.getElementById('qtysegel').disabled=false;
	document.getElementById('segel').disabled=false;
	document.getElementById('keterangan').disabled=false;
	
	document.getElementById('getweight1').disabled=false;
	document.getElementById('getweight2').disabled=true;
	
	document.getElementById('method').value='timbang1';
	getkontrak('',loadData);
}

function fillfield(ticketno) {
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='trx_inothers_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
					showontop();
					var arrlist = new Array();
					arrlist = JSON.parse(con.responseText);
					
					setValue2('ticketno',arrlist['notransaksi']);
					setValue2('qrcode',arrlist['qr']);
					setValue2('nopo',arrlist['nopo']);
					setValue2('produk',arrlist['kodebarang']);
					setValue2('supplier',arrlist['supplier']);
					getkontrak(arrlist['kontrakbeli']);
					setValue2('transportir',arrlist['transportir']);
					setValue2('pemilik',arrlist['pemilik']);
					setValue2('nokendaraan',arrlist['nokendaraan']);
					setValue2('supir',arrlist['supir']);
					setValue2('nosim',arrlist['nosim']);
					setValue2('qtysegel',arrlist['qtysegel']);
					setValue2('segel',arrlist['segel']);
					setValue2('keterangan',arrlist['keterangan']);
					
					setValue2('datein',arrlist['waktumasuk']);
					setValue2('wei1st',arrlist['beratmasuk']);
					setValue2('dateout','');
					setValue2('wei2nd','');
					setValue2('bruto','');
					setValue2('netto','');
					setValue2('kgpotongan','');
					setValue2('method','timbang2');	
					
					document.getElementById('qrcode').disabled=true;
					document.getElementById('nopo').disabled=true;
					document.getElementById('produk').disabled=true;
					document.getElementById('supplier').disabled=true;
					document.getElementById('so').disabled=true;
					document.getElementById('transportir').disabled=true;
					document.getElementById('pemilik').disabled=true;
					document.getElementById('nokendaraan').disabled=true;
					document.getElementById('supir').disabled=true;
					document.getElementById('nosim').disabled=true;
					document.getElementById('qtysegel').disabled=true;
					document.getElementById('segel').disabled=true;
					
					document.getElementById('getweight1').disabled=true;
                    document.getElementById('getweight2').disabled=false;
					document.getElementById('keterangan').disabled=false;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function hitungkg(id=''){
	bruto=document.getElementById('bruto').value;
	if(bruto==''){bruto=0;}
	
	var pr = document.getElementsByName('persen');
	var kg = document.getElementsByName('kg');
	for (var i = 0; i < pr.length; i++){
		if(pr[i].value > 0 && pr[i].value != ''){
			hsl = parseFloat(bruto) * parseFloat(pr[i].value) / 100;
			if(typeof kg[i]!='undefined'){
				if(pr[i].id==id){
					kg[i].value = hsl;					
				}
			}
		}
	}	
	hitungpr(id);
}

function hitungpr(id=''){
	bruto=document.getElementById('bruto').value;
	ttljg=0;
	ttlpr=0;
	ttlkg=0;
	
	if(bruto==''){bruto=0;}
	
	var jg = document.getElementsByName('jjg');
	var pr = document.getElementsByName('persen');
	var kg = document.getElementsByName('kg');
	for (var i = 0; i < kg.length; i++){
		if(kg[i].value > 0 && kg[i].value != ''){
			hsl=0;
			if(bruto > 0){
				hsl = parseFloat(kg[i].value) / parseFloat(bruto) * 100;				
			}
			if(typeof pr[i]!='undefined'){
				if(kg[i].id==id){
					pr[i].value = Math.round(parseFloat(hsl) * 100) / 100;					
				}
			}
		}
		if(typeof jg[i]!='undefined'){
			if(jg[i].value != ''){
				ttljg=parseFloat(ttljg) + parseFloat(jg[i].value);					
			}
		}
		if(typeof pr[i]!='undefined'){
			if(pr[i].value != ''){
				ttlpr=parseFloat(ttlpr) + parseFloat(pr[i].value);					
			}
		}
		if(typeof kg[i]!='undefined'){
			if(kg[i].value != ''){
				ttlkg=parseFloat(ttlkg) + parseFloat(kg[i].value);
			}
		}
	}
		
	document.getElementById('ttlgrdjjg').value=Math.round(ttljg);
	document.getElementById('ttlgrdpersen').value=Math.round(ttlpr * 100) / 100;
	document.getElementById('ttlgrdkg').value=Math.round(ttlkg);
	
	document.getElementById('ttlsorpersen').value=Math.round(ttlpr * 100) / 100;
	document.getElementById('ttlsorkg').value=Math.round(ttlkg);
	
	
	if(document.getElementById('showsortasi').style.display==''){
		document.getElementById('kgpotongan').value=Math.round(ttlkg);
	}else{
		document.getElementById('kgpotongan').value=0;
	}
	
	netto = document.getElementById('bruto').value-document.getElementById('kgpotongan').value;
    document.getElementById('netto').value=netto;
}

function generatenotiket() {
	produk = getValue('produk');
    param='method=generatenotiket'+'&produk='+produk;
    tujuan='trx_inothers_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    var arrlist = new Array();
					arrlist = JSON.parse(con.responseText);
					
                    document.getElementById('ticketno').value=arrlist['tiket'];
                    document.getElementById('jlhkendaraan0').innerHTML=arrlist['masuk'];
                    document.getElementById('jlhkendaraan1').innerHTML=arrlist['keluar'];
					document.getElementById('qrcode').focus();
					getkontrak();
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
	tujuan='trx_inothers_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info",con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
                    // generatenotiket();
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



$('#produk').on("select2:select", function(e) { 
	// getkontrak();
	generatenotiket();
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