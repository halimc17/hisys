// inspectelementOff();
function getkontrak(so='',newFunc){
	supplier = getValue('supplier');
	param='method=getkontrak&supplier='+supplier+'&so='+so;
    tujuan='koreksi_intbseks_slave.php';
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

					document.getElementById('showsortasi').style.display='block';
					showsortasi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function showsortasi(){
	ticketno = getValue('ticketno');
	param='method=showsortasi&ticketno='+ticketno;
    tujuan='koreksi_intbseks_slave.php';
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

function getpotonganwajib(){
	so = getValue('so');
	param='method=getpotonganwajib&so='+so;
    tujuan='koreksi_intbseks_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('potonganwajib').value=con.responseText;
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
	supplier = getValue('supplier');
	so = getValue('so');
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
	kgpotonganwajib = document.getElementById('kgpotonganwajib').value;
	netto = document.getElementById('netto').value;

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
	param+='&kgpotonganwajib='+kgpotonganwajib;
	param+=paramgp;
	param2='ticketno='+ticketno;
	
	tujuan='koreksi_intbseks_slave.php';
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
	setValue2('supplier','');
	setValue2('so','');
	setValue2('transportir','');
	setValue2('nokendaraan','');
	setValue2('supir','');
	setValue2('qtysegel','');
	setValue2('segel','');
	setValue2('jjg','');
	setValue2('brondol','');
	setValue2('keterangan','');
	setValue2('potonganwajib','');
  
	setValue2('bruto','');
	setValue2('kgpotongan','');
	setValue2('kgpotonganwajib','');
	setValue2('netto','');
	setValue2('datein','');
	setValue2('datein','');
	setValue2('dateout','');
	setValue2('wei1st','');
	setValue2('wei2nd','');
	
	document.getElementById('qrcode').disabled=true;
	document.getElementById('supplier').disabled=true;
	document.getElementById('so').disabled=true;
	document.getElementById('transportir').disabled=true;
	document.getElementById('nokendaraan').disabled=true;
	document.getElementById('supir').disabled=true;
	document.getElementById('qtysegel').disabled=true;
	document.getElementById('segel').disabled=true;
	document.getElementById('jjg').disabled=true;
	document.getElementById('brondol').disabled=true;
	document.getElementById('keterangan').disabled=true;
	
	document.getElementById('showsortasi').style.display='none';
	
}

function fillfield() {
	ticketno = document.getElementById('ticketno').value;
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='koreksi_intbseks_slave.php';
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
					setValue2('supplier',arrlist['supplier']);
					getkontrak(arrlist['kontrakbeli']);
					setValue2('transportir',arrlist['transportir']);
					setValue2('nokendaraan',arrlist['nokendaraan']);
					setValue2('supir',arrlist['supir']);
					setValue2('qtysegel',arrlist['qtysegel']);
					setValue2('segel',arrlist['segel']);
					setValue2('potonganwajib',arrlist['persenpotonganwajib']);
					setValue2('kgpotonganwajib',arrlist['potonganwajib']);
					setValue2('jjg',arrlist['janjang']);
					setValue2('brondol',arrlist['brondolan']);
					setValue2('keterangan',arrlist['keterangan']);

					bruto = parseFloat(arrlist['netto'])+parseFloat(arrlist['potongan'])+parseFloat(arrlist['potonganwajib']);
					
					setValue2('bruto',bruto);
					setValue2('netto',arrlist['netto']);
					setValue2('kgpotongan',arrlist['potongan']);
					
					document.getElementById('qrcode').disabled=false;
					document.getElementById('supplier').disabled=false;
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
	
	document.getElementById('ttlsorpersen').value=Math.round(ttlpr * 100) / 100;
	document.getElementById('ttlsorkg').value=Math.round(ttlkg);

	persenpotonganwajib = document.getElementById('potonganwajib').value;
		
	kgpotonganwajib = (bruto * Math.abs(persenpotonganwajib))/100;

	document.getElementById('kgpotongan').value=Math.round(ttlkg);
	document.getElementById('kgpotonganwajib').value=Math.round(kgpotonganwajib);

	
	netto = document.getElementById('bruto').value-document.getElementById('kgpotongan').value-document.getElementById('kgpotonganwajib').value;
    if(netto <= 0){
		document.getElementById('netto').value=0;		
	}else{
		document.getElementById('netto').value=netto;		
	}
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
					if(pr[i].value > 0 && kg[i].value <= 0){
						hsl = parseFloat(bruto) * parseFloat(pr[i].value) / 100;
						if(typeof kg[i]!='undefined'){
							kg[i].value = hsl;					
						}
					}
				}
				
				if(kg[i].value!=''){
					if(kg[i].value > 0){
						hsl = parseFloat(kg[i].value) / parseFloat(bruto) * 100;				
						if(typeof pr[i]!='undefined'){
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
		}
			
		
		document.getElementById('ttlsorpersen').value=Math.round(ttlpr * 100) / 100;
		document.getElementById('ttlsorkg').value=Math.round(ttlkg);

		persenpotonganwajib = document.getElementById('potonganwajib').value;
		
		kgpotonganwajib = (bruto * Math.abs(persenpotonganwajib))/100;
		
		document.getElementById('kgpotongan').value=Math.round(ttlkg);
		document.getElementById('kgpotonganwajib').value=Math.round(kgpotonganwajib);
		
		netto = document.getElementById('bruto').value-document.getElementById('kgpotongan').value-document.getElementById('kgpotonganwajib').value;
		document.getElementById('netto').value=netto;
	}
}

function cleargradsor(){
	var jg = document.getElementsByName('jjg');
	var pr = document.getElementsByName('persen');
	var kg = document.getElementsByName('kg');
	for (var i = 0; i < kg.length; i++){
		if(typeof jg[i]!='undefined'){
			jg[i].value='';
		}
		if(typeof pr[i]!='undefined'){
			pr[i].value='';
		}
		if(typeof kg[i]!='undefined'){
			kg[i].value='';
		}
	}
	
	document.getElementById('ttlsorpersen').value='';
	document.getElementById('ttlsorkg').value='';
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

$('#supplier').on("select2:select", function(e) { 
	getkontrak();
});

$('#so').on("select2:select", function(e) { 
	getpotonganwajib();
});


$('#kodeproduk').on("select2:selecting", function(e) { 
	getkontrak();
});


const btnsimpan = document.getElementById('simpan');
if (btnsimpan) {
	btnsimpan.addEventListener('click', function () {
	  simpan();
	});
}