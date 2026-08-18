// inspectelementOff();
function getkontrak(so='',newFunc){
	supplier = getValue('supplier');
	param='method=getkontrak&supplier='+supplier+'&so='+so;
    tujuan='koreksi_inothers_slave.php';
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
	method = 'timbang2';
	ticketno = getValue('ticketno');
	qrcode = getValue('qrcode');
	produk = getValue('produk');
	supplier = getValue('supplier');
	so = getValue('so');
	transportir = getValue('transportir');
	pemilik = getValue('pemilik');
	nokendaraan = getValue('nokendaraan');
	supir = getValue('supir');
	nosim = getValue('nosim');
	qtysegel = getValue('qtysegel');
	segel = getValue('segel');
	keterangan = getValue('keterangan');
	
	netto = document.getElementById('netto').value;

	validate([
		["qrcode","No. Surat Pengiriman tidak boleh kosong"],
		["produk","Produk tidak boleh kosong"],
		["supplier","Supplier tidak boleh kosong"],
		["nokendaraan","No. Kendaraan tidak boleh kosong"],
		["supir","Nama Driver tidak boleh kosong"]
	]);

	param='ticketno='+ticketno+'&qrcode='+qrcode+'&produk='+produk+'&supplier='+supplier+'&so='+so+'&transportir='+transportir+'&pemilik='+pemilik+'&nokendaraan='+nokendaraan+'&supir='+supir;
	param+='&nosim='+nosim+'&qtysegel='+qtysegel+'&segel='+segel+'&keterangan='+keterangan;
	param+='&netto='+netto;
	param+='&method='+method;
	param2='ticketno='+ticketno;
	
	tujuan='koreksi_inothers_slave.php';
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
	setValue2('qrcode','');
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
  
	setValue2('netto','');
	
	document.getElementById('qrcode').disabled=true;
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
	document.getElementById('keterangan').disabled=true;
}

function fillfield() {
	ticketno = document.getElementById('ticketno').value;
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='koreksi_inothers_slave.php';
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
					
					setValue2('qrcode',arrlist['qr']);
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
					setValue2('netto',arrlist['netto']);
					
					document.getElementById('qrcode').disabled=false;
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
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

const btnbatal = document.getElementById('batal');
if (btnbatal) {
	btnbatal.addEventListener('click', function () {
	  batal();
	});
}

const ticketnoEl = document.getElementById('ticketno');
if (ticketnoEl) {
  ticketnoEl.addEventListener('blur', function () {
    fillfield();
  });
}

$('#kodeproduk').on("select2:selecting", function(e) { 
	getkontrak();
});

$('#supplier').on("select2:select", function(e) { 
	getkontrak();
});

const btnsimpan = document.getElementById('simpan');
if (btnsimpan) {
	btnsimpan.addEventListener('click', function () {
	  simpan();
	});
}