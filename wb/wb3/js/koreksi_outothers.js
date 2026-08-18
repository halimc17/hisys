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
    tujuan='koreksi_outothers_slave.php';
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
					// document.getElementById('divisi').disabled=arrlist['attrdis'];						
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
    tujuan='koreksi_outothers_slave.php';
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
    tujuan='koreksi_outothers_slave.php';
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
	method = 'timbang2';
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
	
	netto = document.getElementById('netto').value;
	
	validate([
		["produk","Produk tidak boleh kosong"],
		["customer","Customer tidak boleh kosong"],
		["nokendaraan","No. Kendaraan tidak boleh kosong"],
		["supir","Nama Driver tidak boleh kosong"]
	]);

	param='ticketno='+ticketno+'&product='+product+'&customer='+customer+'&divisi='+divisi+'&so='+so+'&nokontrak='+nokontrak+'&transportir='+transportir+'&nokendaraan='+nokendaraan+'&supir='+supir;
	param+='&nosim='+nosim+'&qtysegel='+qtysegel+'&segel='+segel+'&keterangan='+keterangan;
	param+='&netto='+netto;
	param+='&sambungso='+sambungso;
	param+='&method='+method;
	param2='ticketno='+ticketno;
	
	tujuan='koreksi_outothers_slave.php';
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
						printticket(tujuan2);
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
	setValue2('divisi','');
	
	setValue2('qtysegel','');
	setValue2('segel','');
	setValue2('sambungso','');
	document.getElementById('detailsambungso').innerHTML='';
  
	setValue2('netto','');
	
	document.getElementById('produk').disabled=true;
	document.getElementById('customer').disabled=true;
	document.getElementById('so').disabled=true;
	document.getElementById('transportir').disabled=true;
	document.getElementById('nokendaraan').disabled=true;
	document.getElementById('supir').disabled=true;
	document.getElementById('nosim').disabled=true;
	document.getElementById('keterangan').disabled=true;
	document.getElementById('divisi').disabled=true;
	
	document.getElementById('qtysegel').disabled=true;
	document.getElementById('segel').disabled=true;
	
}

function fillfield() {
	ticketno = document.getElementById('ticketno').value;
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='koreksi_outothers_slave.php';
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
					
					setValue2('produk',arrlist['kodebarang']);
					setValue2('customer',arrlist['customer']);
					getso(arrlist['kodebarang'],arrlist['customer'],arrlist['kontrakjual'],arrlist['divcode']);
					setValue2('transportir',arrlist['transportir']);
					setValue2('nokendaraan',arrlist['nokendaraan']);
					setValue2('supir',arrlist['supir']);
					setValue2('nosim',arrlist['nosim']);
					setValue2('keterangan',arrlist['keterangan']);
					
					setValue2('netto',arrlist['netto']);	
					
					document.getElementById('produk').disabled=false;
					document.getElementById('customer').disabled=false;
					document.getElementById('divisi').disabled=false;
					document.getElementById('transportir').disabled=false;
					document.getElementById('nokendaraan').disabled=false;
					document.getElementById('supir').disabled=false;
					document.getElementById('nosim').disabled=false;
					
					document.getElementById('keterangan').disabled=false;
					
					document.getElementById('sambungso').innerHTML=arrlist['sambungso'];

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

const ticketnoEl = document.getElementById('ticketno');
if (ticketnoEl) {
  ticketnoEl.addEventListener('blur', function () {
    fillfield();
  });
}

const btnbatal = document.getElementById('batal');
if (btnbatal) {
	btnbatal.addEventListener('click', function () {
	  batal();
	});
}

$('#kodeproduk').on("select2:selecting", function(e) { 
	getkontrak();
});


const btnsimpan = document.getElementById('simpan');
if (btnsimpan) {
	btnsimpan.addEventListener('click', function () {
	  simpan();
	});
}