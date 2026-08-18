const product1 = document.getElementById('product1');
if (product1) {
	product1.addEventListener('click', function () {
		getso();
	});
}

const product2 = document.getElementById('product2');
if (product2) {
	product2.addEventListener('click', function () {
		getso();
	});
}

$('#customer').on("select2:select", function(e) { 
	getso();
});

$('#so').on("select2:select", function(e) { 
	getso('','',this.value,'');
});

$('#storage').on("select2:select", function(e) { 
	getkualitas();
});

$('#sambungso').on("select2:select", function(e) { 
	getsambungso();
});

function getso(product='',customer='',so='',divisi='',newFunc){
	if(product==''){
		product = getValue('produk');		
	}
	if(customer==''){
		customer = getValue('customer');		
	}
	param='method=getso&customer='+customer+'&product='+product+'&so='+so+'&divisi='+divisi;
    tujuan='trx_outothers_slave.php';
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
                    document.getElementById('so').innerHTML=arrlist['listso'];
                    document.getElementById('sisaso').value=arrlist['sisaso'];
                    document.getElementById('nokontrak').value=arrlist['nokontrak'];
					document.getElementById('divisi').disabled=arrlist['attrdis'];						
					document.getElementById('divisi').innerHTML=arrlist['divisi'];
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

function getsambungso(){
	sambungso = getValue('sambungso');
	param='method=getsambungso&sambungso='+sambungso;
    tujuan='trx_outothers_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
					document.getElementById('detailsambungso').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function getkualitas(){
	product='';
	if(document.getElementById('product1').checked==true){
		product='1';
	}
	if(document.getElementById('product2').checked==true){
		product='2';
	}
	storage = getValue('storage');
	param='method=getkualitas&storage='+storage+'&product='+product;
    tujuan='trx_outothers_slave.php';
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
                    document.getElementById('ffa').value=arrlist['ffa'];
                    document.getElementById('moist').value=arrlist['moist'];
                    document.getElementById('dirt').value=arrlist['dirt'];
                    document.getElementById('dobi').value=arrlist['dobi'];
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
	product = getValue('produk');
	customer = getValue('customer');
	divisi = getValue('divisi');
	so = getValue('so');
	nokontrak = getValue('nokontrak');
	transportir = getValue('transportir');
	nokendaraan = getValue('nokendaraan');
	supir = getValue('supir');
	nosim = getValue('nosim');
	qtysegel = getValue('qtysegel');
	segel = getValue('segel');
	keterangan = getValue('keterangan');
	
	sambungso=getValue('sambungso');
	
	wei1st = document.getElementById('wei1st').value;
	wei2nd = document.getElementById('wei2nd').value;
	datein = document.getElementById('datein').value;
	dateout = document.getElementById('dateout').value;
	bruto = document.getElementById('bruto').value;
	kgpotongan = document.getElementById('kgpotongan').value;
	netto = document.getElementById('netto').value;
	attr = document.getElementById('divisi').disabled;
	
	if(method=='timbang1'){
		validate([
			["produk","Produk tidak boleh kosong"],
			["customer","Customer tidak boleh kosong"],
			// ["so","Sales Order tidak boleh kosong"],
			["nokendaraan","No. Kendaraan tidak boleh kosong"],
			["supir","Nama Driver tidak boleh kosong"],
			// ["nosim","No. SIM tidak boleh kosong"],
			["wei1st","Berat timbang 1 tidak boleh kosong"]
		]);
		if(attr==false){
			validate([
			["divisi","Divisi tidak boleh kosong"]
			]);
		}
	}

	if(method=='timbang2'){
		validate([
			// ["qtysegel","Jumlah Segel tidak boleh kosong"],
			// ["segel","No. Segel tidak boleh kosong"],
			["wei1st","Berat timbang 1 tidak boleh kosong"],
			["wei2nd","Berat timbang 2 tidak boleh kosong"]
		]);
	}

	param='ticketno='+ticketno+'&product='+product+'&customer='+customer+'&divisi='+divisi+'&so='+so+'&nokontrak='+nokontrak+'&transportir='+transportir+'&nokendaraan='+nokendaraan+'&supir='+supir;
	param+='&nosim='+nosim+'&qtysegel='+qtysegel+'&segel='+segel+'&keterangan='+keterangan;
	param+='&wei1st='+wei1st+'&wei2nd='+wei2nd+'&datein='+datein+'&dateout='+dateout+'&kgpotongan='+kgpotongan+'&bruto='+bruto+'&netto='+netto;
	param+='&sambungso='+sambungso;
	param+='&method='+method;
	param2='ticketno='+ticketno;
	
	tujuan='trx_outothers_slave.php';
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
						showtobottom();
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
	setValue2('produk','');
	setValue2('customer','');
	setValue2('transportir','');
	setValue2('nokendaraan','');
	setValue2('supir','');
	setValue2('nosim','');
	setValue2('keterangan','');
	
	setValue2('qtysegel','');
	setValue2('segel','');
	setValue2('sambungso','');
	document.getElementById('detailsambungso').innerHTML='';
  
	setValue2('bruto','');
	setValue2('kgpotongan','');
	setValue2('netto','');
	setValue2('datein','');
	setValue2('datein','');
	setValue2('dateout','');
	setValue2('wei1st','');
	setValue2('wei2nd','');
	
	document.getElementById('produk').disabled=false;
	document.getElementById('customer').disabled=false;
	document.getElementById('so').disabled=false;
	document.getElementById('transportir').disabled=false;
	document.getElementById('nokendaraan').disabled=false;
	document.getElementById('supir').disabled=false;
	document.getElementById('nosim').disabled=false;
	document.getElementById('keterangan').disabled=false;
	
	document.getElementById('qtysegel').disabled=false;
	document.getElementById('segel').disabled=false;
	
	document.getElementById('getweight1').disabled=false;
	document.getElementById('getweight2').disabled=true;
	
	document.getElementById('method').value='timbang1';
	
	document.getElementById('showkualitas').style.display='none';
	getso('','','','',loadData);
}

function fillfield(ticketno) {
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='trx_outothers_slave.php';
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
					setValue2('produk',arrlist['kodebarang']);
					setValue2('customer',arrlist['customer']);
					getso(arrlist['kodebarang'],arrlist['customer'],arrlist['kontrakjual'],arrlist['divcode']);
					setValue2('transportir',arrlist['transportir']);
					setValue2('nokendaraan',arrlist['nokendaraan']);
					setValue2('supir',arrlist['supir']);
					setValue2('nosim',arrlist['nosim']);
					setValue2('keterangan',arrlist['keterangan']);
					
					setValue2('datein',arrlist['waktumasuk']);
					setValue2('wei1st',arrlist['beratmasuk']);
					setValue2('dateout','');
					setValue2('wei2nd','');
					setValue2('bruto','');
					setValue2('netto','');
					setValue2('kgpotongan','');
					setValue2('method','timbang2');	
					
					document.getElementById('produk').disabled=true;
					document.getElementById('customer').disabled=true;
					document.getElementById('so').disabled=true;
					document.getElementById('transportir').disabled=true;
					document.getElementById('nokendaraan').disabled=true;
					document.getElementById('supir').disabled=true;
					document.getElementById('nosim').disabled=true;
					
					document.getElementById('getweight1').disabled=true;
                    document.getElementById('getweight2').disabled=false;
					document.getElementById('keterangan').disabled=false;
					
					document.getElementById('showkualitas').style.display='';
					document.getElementById('sambungso').innerHTML=arrlist['sambungso'];
					document.getElementById('qtysegel').focus();
					
					cleargradsor();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function cleargradsor(){
	setValue2('qtysegel','');
	setValue2('segel','');
	setValue2('sambungso','');
	document.getElementById('detailsambungso').innerHTML='';
}

function generatenotiket() {
    produk = getValue('produk');
    param='method=generatenotiket'+'&produk='+produk;
    tujuan='trx_outothers_slave.php';
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
	tujuan='trx_outothers_slave.php';
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
    ambil_tanggal('datein','wei1st','pengiriman');
  });
}

const btngetweight2 = document.getElementById('getweight2');
if (btngetweight2) {
  btngetweight2.addEventListener('click', function () {
    ambil_tanggal('dateout','wei2nd','pengiriman');
  });
}

const btnsimpan = document.getElementById('simpan');
if (btnsimpan) {
	btnsimpan.addEventListener('click', function () {
	  simpan();
	});
}