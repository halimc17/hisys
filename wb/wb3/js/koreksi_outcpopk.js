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
	getso('','',this.value);
});

$('#storage').on("select2:select", function(e) { 
	getkualitas();
});

$('#sambungso').on("select2:select", function(e) { 
	getsambungso();
});

function getso(product='',customer='',so='',transportir='',nokendaraan='',newFunc){
	if(product==''){
		if(document.getElementById('product1').checked==true){
			product='1';
		}else{
			product='2';
		}
	}
	if(customer==''){
		customer = getValue('customer');		
	}
	param='method=getso&customer='+customer+'&product='+product+'&so='+so+'&transportir='+transportir+'&nokendaraan='+nokendaraan;
    tujuan='koreksi_outcpopk_slave.php';
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
                    document.getElementById('transportir').innerHTML=arrlist['listtransportir'];
                    document.getElementById('nokendaraan').innerHTML=arrlist['listkendaraan'];
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

function getkendaraan(){
	transportir = getValue('transportir');
	param='method=getkendaraan&transportir='+transportir;
    tujuan='koreksi_outcpopk_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
					document.getElementById('nokendaraan').innerHTML=con.responseText;
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
    tujuan='koreksi_outcpopk_slave.php';
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
    tujuan='koreksi_outcpopk_slave.php';
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
	wbcond = getValue('wbcond');
	ticketno = getValue('ticketno');
	product='';
	if(document.getElementById('product1').checked==true){
		product='1';
	}
	if(document.getElementById('product2').checked==true){
		product='2';
	}
	customer = getValue('customer');
	so = getValue('so');
	sisaso = getValue('sisaso');
	nokontrak = getValue('nokontrak');
	transportir = getValue('transportir');
	nokendaraan = getValue('nokendaraan');
	supir = getValue('supir');
	nosim = getValue('nosim');
	qtysegel = getValue('qtysegel');
	segel = getValue('segel');
	keterangan = getValue('keterangan');
	tiketref = getValue('tiketref');
	
	storage=getValue('storage');
	ffa=getValue('ffa');
	moist=getValue('moist');
	dirt=getValue('dirt');
	dobi=getValue('dobi');
	sambungso=getValue('sambungso');
	
	netto = document.getElementById('netto').value;
	
	if(product==''){
		alert('Produk harus dipilih');
		document.getElementById('product1').focus();
		return false;
	}

	if (wbcond=='Return') {
		validate([
		        ["tiketref","WB Ref tidak boleh kosong."]
		    ]);
	}

	validate([
		["customer","Customer tidak boleh kosong"],
		["so","Sales Order tidak boleh kosong"],
		["nokendaraan","No. Kendaraan tidak boleh kosong"],
		["supir","Nama Driver tidak boleh kosong"],
		["nosim","No. SIM tidak boleh kosong"],
		["qtysegel","Jumlah Segel tidak boleh kosong"],
		["segel","No. Segel tidak boleh kosong"]
	]);
	

	param='ticketno='+ticketno+'&product='+product+'&customer='+customer+'&so='+so+'&sisaso='+sisaso+'&nokontrak='+nokontrak+'&transportir='+transportir+'&nokendaraan='+nokendaraan+'&supir='+supir;
	param+='&nosim='+nosim+'&qtysegel='+qtysegel+'&segel='+segel+'&keterangan='+keterangan;
	param+='&netto='+netto;
	param+='&storage='+storage+'&ffa='+ffa+'&moist='+moist+'&dirt='+dirt+'&dobi='+dobi+'&sambungso='+sambungso;
	param+='&wbcond='+wbcond;
	param+='&tiketref='+tiketref;
	param+='&method='+method;
	param2='ticketno='+ticketno;
	
	tujuan='koreksi_outcpopk_slave.php';
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
	document.getElementById('product1').checked=false;
	document.getElementById('product2').checked=false;
	setValue2('customer','');
	setValue2('transportir','');
	setValue2('nokendaraan','');
	setValue2('supir','');
	setValue2('nosim','');
	setValue2('keterangan','');
	setValue2('tiketref','');
	
	setValue2('storage','');
	setValue2('ffa','');
	setValue2('moist','');
	setValue2('dirt','');
	setValue2('dobi','');
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
	
	document.getElementById('wbcond').disabled=true;
	document.getElementById('product1').disabled=true;
	document.getElementById('product2').disabled=true;
	document.getElementById('customer').disabled=true;
	document.getElementById('so').disabled=true;
	document.getElementById('transportir').disabled=true;
	document.getElementById('nokendaraan').disabled=true;
	document.getElementById('supir').disabled=true;
	document.getElementById('nosim').disabled=true;
	document.getElementById('keterangan').disabled=true;
	
	document.getElementById('qtysegel').disabled=true;
	document.getElementById('segel').disabled=true;
	
	document.getElementById('showkualitas').style.display='none';
}

function fillfield() {
	ticketno = document.getElementById('ticketno').value;
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='koreksi_outcpopk_slave.php';
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
					
					if(arrlist['produk']=='1'){
						document.getElementById('product1').checked=true;
					}else{
						document.getElementById('product2').checked=true;
					}
					setValue2('customer',arrlist['customer']);
					getso(arrlist['produk'],arrlist['customer'],arrlist['kontrakjual'],arrlist['transportir'],arrlist['nokendaraan']);
					setValue2('wbcond',arrlist['wbcond']);
					setValue2('supir',arrlist['supir']);
					setValue2('nosim',arrlist['nosim']);
					setValue2('keterangan',arrlist['notekirim']);
					setValue2('tiketref',arrlist['tiketref']);

					setValue2('storage',arrlist['storage']);
					setValue2('ffa',arrlist['ffa']);
					setValue2('moist',arrlist['moist']);
					setValue2('dirt',arrlist['dirt']);
					setValue2('dobi',arrlist['dobi']);
					setValue2('qtysegel',arrlist['qtysegel']);
					setValue2('segel',arrlist['segel']);
					setValue2('netto',arrlist['netto']);

					if (arrlist['netto']=='0') {
						document.getElementById('so').disabled=false;
					}else{
						document.getElementById('so').disabled=true;
					}
					
					document.getElementById('nokendaraan').disabled=false;
					document.getElementById('supir').disabled=false;
					document.getElementById('nosim').disabled=false;
					document.getElementById('keterangan').disabled=false;
					
					document.getElementById('showkualitas').style.display='';
					document.getElementById('sambungso').innerHTML=arrlist['sambungso'];
					setValue2('sambungso',arrlist['kontrakjual2']);

					if (arrlist['wbcond']=='Return') {
						document.getElementById('tiketref').disabled=false;
					}else{
						document.getElementById('tiketref').disabled=true;
					}

					// if (arrlist['kontrakjual2']!='') {
						// document.getElementById('sambungso').disabled=false;
					// }else{
						document.getElementById('sambungso').disabled=true;
					// }
					
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

$('#kodeproduk').on("select2:selecting", function(e) { 
	getkontrak();
});

$('#transportir').on("select2:select", function(e) { 
	getkendaraan();
});

const btnsimpan = document.getElementById('simpan');
if (btnsimpan) {
	btnsimpan.addEventListener('click', function () {
	  simpan();
	});
}