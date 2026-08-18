//koreksi tbs internal
function fillfield(ticketno) {
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='master_koreksi_slave.php';
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
					setValue2('unit',arrlist['unitcode']);
					getdivisi(arrlist['divcode'],arrlist['nopo'],arrlist['kontrakbeli'],arrlist['tipeunit']);
					setValue2('transportir',arrlist['transportir']);
					setValue2('nokendaraan',arrlist['nokendaraan']);
					setValue2('supir',arrlist['supir']);
					setValue2('qtysegel',arrlist['qtysegel']);
					setValue2('segel',arrlist['segel']);
					
					setValue2('bruto',arrlist['bruto']);
					setValue2('kgpotongan',arrlist['potongan']);
					setValue2('netto',arrlist['netto']);
					setValue2('jjg',arrlist['janjang']);
					
					document.getElementById('qrcode').disabled=false;
					document.getElementById('unit').disabled=false;
					document.getElementById('divisi').disabled=false;
					document.getElementById('productionorder').disabled=false;
					document.getElementById('so').disabled=false;
					document.getElementById('transportir').disabled=false;
					document.getElementById('nokendaraan').disabled=false;
					document.getElementById('supir').disabled=false;
					document.getElementById('qtysegel').disabled=false;
					document.getElementById('segel').disabled=false;
					
					document.getElementById('jjg').disabled=false;
					document.getElementById('brondol').disabled=false;
					document.getElementById('keterangan').disabled=false;
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
	setValue2('ticketno','');
	setValue2('qrcode','');
	setValue2('unit','');
	setValue2('divisi','');
	setValue2('so','');
	setValue2('productionorder','');
	setValue2('transportir','');
	setValue2('nokendaraan','');
	setValue2('supir','');
	setValue2('qtysegel','');
	setValue2('segel','');
	setValue2('jjg','');
	setValue2('brondol','');
	setValue2('keterangan','');
	setValue2('bruto','');
	setValue2('kgpotongan','');
	setValue2('netto','');
	
	document.getElementById('qrcode').disabled=true;
	document.getElementById('unit').disabled=true;
	document.getElementById('divisi').disabled=true;
	document.getElementById('so').disabled=true;
	document.getElementById('productionorder').disabled=true;
	document.getElementById('transportir').disabled=true;
	document.getElementById('nokendaraan').disabled=true;
	document.getElementById('supir').disabled=true;
	document.getElementById('qtysegel').disabled=true;
	document.getElementById('segel').disabled=true;
	document.getElementById('jjg').disabled=true;
	document.getElementById('brondol').disabled=true;
	document.getElementById('keterangan').disabled=true;
	
	document.getElementById('showgrading').innerHTML='';
	document.getElementById('showsortasi').innerHTML='';
}

function showsortasi(){
	ticketno = getValue('ticketno');
	param='method=showsortasi&ticketno='+ticketno;
    tujuan='master_koreksi_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('showsortasi').innerHTML=con.responseText;
                    document.getElementById('showgrading').innerHTML='';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    } 
}

function showgrading(){
	ticketno = getValue('ticketno');
	param='method=showgrading&ticketno='+ticketno;
    tujuan='master_koreksi_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('showgrading').innerHTML=con.responseText;
                    document.getElementById('showsortasi').innerHTML='';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    } 
}

function getdivisi(divisi='',productionorder='',so='',tipeunit='',newFunc) {
	unit = getValue('unit');
	divisi = divisi;
    param='method=getdivisi&unit='+unit+'&divisi='+divisi;
    tujuan='master_koreksi_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('divisi').innerHTML=con.responseText;
                    getproductionorder(productionorder,so,tipeunit,newFunc);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function getkontrak(so='',tipeunit='',newFunc){
	unit = getValue('unit');
	param='method=getkontrak&unit='+unit+'&so='+so;
    tujuan='master_koreksi_slave.php';
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
					if(tipeunit=='PLASMA'){
						showsortasi();
					}else{
						showgrading();
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function getproductionorder(productionorder='',so='',tipeunit='',newFunc){
	param='method=getproductionorder&productionorder='+productionorder;
    tujuan='master_koreksi_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('productionorder').innerHTML=con.responseText;
                    getkontrak(so,tipeunit,newFunc);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function simpanInternal(){
	method = 'timbangInternal';
	ticketno = getValue('ticketno');
	qrcode = getValue('qrcode');
	unit = getValue('unit');
	divisi = getValue('divisi');
	so = getValue('so');
	productionorder = getValue('productionorder');
	transportir = getValue('transportir');
	nokendaraan = getValue('nokendaraan');
	supir = getValue('supir');
	qtysegel = getValue('qtysegel');
	segel = getValue('segel');
	jjg = getValue('jjg');
	brondol = getValue('brondol');
	keterangan = getValue('keterangan');
	
	bruto = document.getElementById('bruto').value;
	kgpotongan = document.getElementById('kgpotongan').value;
	netto = document.getElementById('netto').value;

	validate([
		["qrcode","QR Code / SPB tidak boleh kosong"],
		["unit","Unit tidak boleh kosong"],
		["divisi","Divisi tidak boleh kosong"],
		["nokendaraan","No. Kendaraan tidak boleh kosong"],
		["supir","Nama Driver tidak boleh kosong"]
	]);

	paramgp='';
		
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

	param='ticketno='+ticketno+'&qrcode='+qrcode+'&unit='+unit+'&divisi='+divisi+'&so='+so+'&transportir='+transportir+'&nokendaraan='+nokendaraan+'&supir='+supir;
	param+='&jjg='+jjg+'&brondol='+brondol+'&qtysegel='+qtysegel+'&segel='+segel+'&keterangan='+keterangan;
	param+='&kgpotongan='+kgpotongan+'&bruto='+bruto+'&netto='+netto;
	param+='&method='+method;
	param+='&productionorder='+productionorder;
	param+=paramgp;
	param2='ticketno='+ticketno;
	
	tujuan='master_koreksi_slave.php';
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
		}else{
			if(typeof kg[i]!='undefined'){
				if(pr[i].id==id){
					kg[i].value = 0;					
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
	
	const ttlgrdjjgEl = document.getElementById('ttlgrdjjg');
	if (ttlgrdjjgEl) {
		document.getElementById('ttlgrdjjg').value=Math.round(ttljg);
	}

	const ttlgrdpersenEl = document.getElementById('ttlgrdpersen');
	if (ttlgrdpersenEl) {
		document.getElementById('ttlgrdpersen').value=Math.round(ttlpr * 100) / 100;
	}

	const ttlgrdkgEl = document.getElementById('ttlgrdkg');
	if (ttlgrdkgEl) {
		document.getElementById('ttlgrdkg').value=Math.round(ttlkg);
	}

	const ttlsorpersenEl = document.getElementById('ttlsorpersen');
	if (ttlsorpersenEl) {
		document.getElementById('ttlsorpersen').value=Math.round(ttlpr * 100) / 100;
	}

	const ttlsorkgEl = document.getElementById('ttlsorkg');
	if (ttlsorkgEl) {
		document.getElementById('ttlsorkg').value=Math.round(ttlkg);
	}
	
	if (document.getElementById('showsortasi').style.display==''){
		document.getElementById('kgpotongan').value=Math.round(ttlkg);
	}else{
		document.getElementById('kgpotongan').value=0;
	}
	
	netto = document.getElementById('bruto').value-document.getElementById('kgpotongan').value;
    document.getElementById('netto').value=netto;
}

function hitungres(){
	bruto=document.getElementById('bruto').value;
	ttljg=0;
	ttlpr=0;
	ttlkg=0;
	
	if(bruto==''){bruto=0;}
	
	if(bruto > 0){
		var jg = document.getElementsByName('jjg');
		var pr = document.getElementsByName('persen');
		var kg = document.getElementsByName('kg');
		for (var i = 0; i < kg.length; i++){
			hsl=0;
			if(bruto > 0){
				if(pr[i].value!=''){
					hsl = parseFloat(bruto) * parseFloat(pr[i].value) / 100;
					if(typeof kg[i]!='undefined'){
						kg[i].value = hsl;					
					}
				}
				
				if(kg[i].value!=''){
					hsl = parseFloat(kg[i].value) / parseFloat(bruto) * 100;				
					if(typeof pr[i]!='undefined'){
						pr[i].value = Math.round(parseFloat(hsl) * 100) / 100;
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
}


const ticketEl = document.getElementById('ticketno');
if (ticketEl) {
  ticketEl.addEventListener('blur', function () {
    fillfield(ticketEl.value);
  });
}

const unitEl = document.getElementById('unit');
if (unitEl) {
  unitEl.addEventListener('change', function () {
    getdivisi();
  });
}

const btnsimpan = document.getElementById('simpan');
if (btnsimpan) {
	btnsimpan.addEventListener('click', function () {
	  simpanInternal();
	});
}

const btnbatal = document.getElementById('batal');
if (btnbatal) {
	btnbatal.addEventListener('click', function () {
	  batal();
	});
}
//koreksi tbs internal

//koreksi tbs eksternal
function batal1(){
	showontop();
	setValue2('ticketno1','');
	setValue2('qrcode1','');
	setValue2('so1','');
	setValue2('supplier1','');
	setValue2('transportir1','');
	setValue2('nokendaraan1','');
	setValue2('supir1','');
	setValue2('qtysegel1','');
	setValue2('segel1','');
	setValue2('jjg1','');
	setValue2('brondol1','');
	setValue2('keterangan1','');
	setValue2('bruto1','');
	setValue2('kgpotongan1','');
	setValue2('netto1','');
	
	document.getElementById('qrcode1').disabled=true;
	document.getElementById('supplier1').disabled=true;
	document.getElementById('so1').disabled=true;
	document.getElementById('transportir1').disabled=true;
	document.getElementById('nokendaraan1').disabled=true;
	document.getElementById('supir1').disabled=true;
	document.getElementById('qtysegel1').disabled=true;
	document.getElementById('segel1').disabled=true;
	document.getElementById('jjg1').disabled=true;
	document.getElementById('brondol1').disabled=true;
	document.getElementById('keterangan1').disabled=true;
	
	document.getElementById('showsortasi1').innerHTML='';
}

function fillfield1(ticketno1) {
    param='method=showedit1';
    param+='&ticketno1='+ticketno1;
    tujuan='master_koreksi_slave.php';
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
					
					setValue2('qrcode1',arrlist['qr']);
					setValue2('supplier1',arrlist['supplier']);
					getkontrak1(arrlist['kontrakbeli']);
					setValue2('transportir1',arrlist['transportir']);
					setValue2('nokendaraan1',arrlist['nokendaraan']);
					setValue2('supir1',arrlist['supir']);
					setValue2('qtysegel1',arrlist['qtysegel']);
					setValue2('segel1',arrlist['segel']);

					setValue2('netto1',arrlist['netto']);
					setValue2('bruto1',arrlist['bruto']);
					setValue2('kgpotongan1',arrlist['potongan']);
					
					document.getElementById('qrcode1').disabled=false;
					document.getElementById('supplier1').disabled=false;
					document.getElementById('so1').disabled=false;
					document.getElementById('transportir1').disabled=false;
					document.getElementById('nokendaraan1').disabled=false;
					document.getElementById('supir1').disabled=false;
					document.getElementById('qtysegel1').disabled=false;
					document.getElementById('segel1').disabled=false;
					
					document.getElementById('jjg1').disabled=false;
					document.getElementById('brondol1').disabled=false;
					document.getElementById('keterangan1').disabled=false;
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function getkontrak1(so1='',newFunc){
	supplier1 = getValue('supplier1');
	param='method=getkontrak1&supplier1='+supplier1+'&so1='+so1;
    tujuan='master_koreksi_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('so1').innerHTML=con.responseText;
					if(typeof newFunc !== 'undefined' && typeof newFunc == 'function'){
						eval(newFunc());
					}
					showsortasi1();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function showsortasi1(){
	ticketno1 = getValue('ticketno1');
	param='method=showsortasi&ticketno='+ticketno1;
    tujuan='master_koreksi_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('showsortasi1').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    } 
}

function simpanEksternal(){
	method = 'timbangEksternal';
	ticketno = getValue('ticketno1');
	qrcode = getValue('qrcode1');
	supplier = getValue('supplier1');
	so = getValue('so1');
	transportir = getValue('transportir1');
	nokendaraan = getValue('nokendaraan1');
	supir = getValue('supir1');
	qtysegel = getValue('qtysegel1');
	segel = getValue('segel1');
	jjg = getValue('jjg1');
	brondol = getValue('brondol1');
	keterangan = getValue('keterangan1');

	bruto = document.getElementById('bruto1').value;
	kgpotongan = document.getElementById('kgpotongan1').value;
	netto = document.getElementById('netto1').value;

	validate([
		["qrcode","No. SPB tidak boleh kosong"],
		["supplier","Supplier tidak boleh kosong"],
		["so","Kontrak tidak boleh kosong"],
		["nokendaraan","No. Kendaraan tidak boleh kosong"],
		["supir","Nama Driver tidak boleh kosong"]
	]);

	paramgp='';
		
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

	param='ticketno='+ticketno+'&qrcode='+qrcode+'&supplier='+supplier+'&so='+so+'&transportir='+transportir+'&nokendaraan='+nokendaraan+'&supir='+supir;
	param+='&jjg='+jjg+'&brondol='+brondol+'&qtysegel='+qtysegel+'&segel='+segel+'&keterangan='+keterangan;
	param+='&kgpotongan='+kgpotongan+'&bruto='+bruto+'&netto='+netto;
	param+='&method='+method;
	param+=paramgp;
	param2='ticketno='+ticketno;
	
	tujuan='master_koreksi_slave.php';
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

function hitungkgEksternal(id=''){
	bruto=document.getElementById('bruto1').value;
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
		}else{
			if(typeof kg[i]!='undefined'){
				if(pr[i].id==id){
					kg[i].value = 0;					
				}
			}
		}
	}	
	hitungpr(id);
}

function hitungprEksternal(id=''){
	bruto=document.getElementById('bruto1').value;
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
	
	const ttlgrdjjgEl = document.getElementById('ttlgrdjjg');
	if (ttlgrdjjgEl) {
		document.getElementById('ttlgrdjjg').value=Math.round(ttljg);
	}

	const ttlgrdpersenEl = document.getElementById('ttlgrdpersen');
	if (ttlgrdpersenEl) {
		document.getElementById('ttlgrdpersen').value=Math.round(ttlpr * 100) / 100;
	}

	const ttlgrdkgEl = document.getElementById('ttlgrdkg');
	if (ttlgrdkgEl) {
		document.getElementById('ttlgrdkg').value=Math.round(ttlkg);
	}

	const ttlsorpersenEl = document.getElementById('ttlsorpersen');
	if (ttlsorpersenEl) {
		document.getElementById('ttlsorpersen').value=Math.round(ttlpr * 100) / 100;
	}

	const ttlsorkgEl = document.getElementById('ttlsorkg');
	if (ttlsorkgEl) {
		document.getElementById('ttlsorkg').value=Math.round(ttlkg);
	}
	
	if (document.getElementById('showsortasi').style.display==''){
		document.getElementById('kgpotongan').value=Math.round(ttlkg);
	}else{
		document.getElementById('kgpotongan').value=0;
	}
	
	netto = document.getElementById('bruto').value-document.getElementById('kgpotongan').value;
    document.getElementById('netto').value=netto;
}

const ticket1El = document.getElementById('ticketno1');
if (ticket1El) {
  ticket1El.addEventListener('blur', function () {
    fillfield1(ticket1El.value);
  });
}

const btnsimpan1 = document.getElementById('simpan1');
if (btnsimpan1) {
	btnsimpan1.addEventListener('click', function () {
	  simpanEksternal();
	});
}

const btnbatal1 = document.getElementById('batal1');
if (btnbatal1) {
	btnbatal1.addEventListener('click', function () {
	  batal1();
	});
}
//koreksi tbs eksternal


//koreksi cpo / pk
function getso(product='',customer='',so='',newFunc){
	if(product==''){
		if(document.getElementById('product12').checked==true){
			product='1';
		}else{
			product='2';
		}
	}
	if(customer==''){
		customer = getValue('customer2');		
	}
	param='method=getso&customer='+customer+'&product='+product+'&so='+so;
    tujuan='master_koreksi_slave.php';
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
                    document.getElementById('so2').innerHTML=arrlist['listso'];
                    document.getElementById('sisaso2').value=arrlist['sisaso'];
                    document.getElementById('nokontrak2').value=arrlist['nokontrak'];
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

function fillfield2(ticketno2) {
    param='method=showedit2';
    param+='&ticketno2='+ticketno2;
    tujuan='master_koreksi_slave.php';
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
						document.getElementById('product12').checked=true;
					}else{
						document.getElementById('product22').checked=true;
					}
					setValue2('customer2',arrlist['customer']);
					getso(arrlist['produk'],arrlist['customer'],arrlist['kontrakjual']);
					setValue2('wbcond2',arrlist['wbcond']);
					setValue2('transportir2',arrlist['transportir']);
					setValue2('nokendaraan2',arrlist['nokendaraan']);
					setValue2('supir2',arrlist['supir']);
					setValue2('nosim2',arrlist['nosim']);
					setValue2('keterangan2',arrlist['keterangan']);
					setValue2('tiketref2',arrlist['tiketref']);
					setValue2('storage2',arrlist['storage']);
					setValue2('ffa2',arrlist['ffa']);
					setValue2('moist2',arrlist['moist']);
					setValue2('dirt2',arrlist['dirt']);
					setValue2('dobi2',arrlist['dobi']);
					setValue2('qtysegel2',arrlist['qtysegel']);
					setValue2('segel2',arrlist['segel']);
					setValue2('sambungso2',arrlist['kontrakjual2']);
					setValue2('netto2',arrlist['netto']);

					setValue2('tanggalmasuk2',arrlist['waktumasuk'].substr(0,10));
					setValue2('tanggalkeluar2',arrlist['waktukeluar'].substr(0,10));

					setValue2('jammasuk2',arrlist['waktumasuk'].substr(11,2));
					setValue2('mntmasuk2',arrlist['waktumasuk'].substr(14,2));
					setValue2('jamkeluar2',arrlist['waktukeluar'].substr(11,2));
					setValue2('mntkeluar2',arrlist['waktukeluar'].substr(14,2));
					
					document.getElementById('customer2').disabled=false;
					document.getElementById('so2').disabled=false;
					document.getElementById('transportir2').disabled=false;
					document.getElementById('nokendaraan2').disabled=false;
					document.getElementById('supir2').disabled=false;
					document.getElementById('nosim2').disabled=false;
					
					document.getElementById('keterangan2').disabled=false;
					
					document.getElementById('showkualitas').style.display='';
					document.getElementById('storage2').innerHTML=arrlist['storage'];
					document.getElementById('sambungso2').innerHTML=arrlist['sambungso'];
				
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function batal2(){
	showontop();
	document.getElementById('product12').checked=false;
	document.getElementById('product22').checked=false;
	setValue2('ticketno2','');
	setValue2('customer2','');
	setValue2('transportir2','');
	setValue2('nokendaraan2','');
	setValue2('supir2','');
	setValue2('nosim2','');
	setValue2('keterangan2','');
	setValue2('tiketref2','');
	
	setValue2('storage2','');
	setValue2('ffa2','');
	setValue2('moist2','');
	setValue2('dirt2','');
	setValue2('dobi2','');
	setValue2('qtysegel2','');
	setValue2('segel2','');
	setValue2('sambungso2','');
	document.getElementById('detailsambungso2').innerHTML='';
  
	setValue2('netto2','');
	
	document.getElementById('wbcond2').disabled=false;
	document.getElementById('product12').disabled=false;
	document.getElementById('product22').disabled=false;
	document.getElementById('customer2').disabled=false;
	document.getElementById('so2').disabled=false;
	document.getElementById('transportir2').disabled=false;
	document.getElementById('nokendaraan2').disabled=false;
	document.getElementById('supir2').disabled=false;
	document.getElementById('nosim2').disabled=false;
	document.getElementById('keterangan2').disabled=false;
	
	document.getElementById('qtysegel2').disabled=false;
	document.getElementById('segel2').disabled=false;
}

const btnbatal2 = document.getElementById('batal2');
if (btnbatal2) {
	btnbatal2.addEventListener('click', function () {
	  batal2();
	});
}

const product12 = document.getElementById('product12');
if (product12) {
	product12.addEventListener('click', function () {
		getso();
	});
}

const product22 = document.getElementById('product22');
if (product22) {
	product22.addEventListener('click', function () {
		getso();
	});
}

$('#customer2').on("select2:select", function(e) { 
	getso();
});

$('#so2').on("select2:select", function(e) { 
	getso('','',this.value);
});

const ticket2El = document.getElementById('ticketno2');
if (ticket2El) {
  ticket2El.addEventListener('blur', function () {
    fillfield2(ticket2El.value);
  });
}
//koreksi cpo / pk