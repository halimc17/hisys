// inspectelementOff();
function getdivisi(divisi='',productionorder='',so='',tipeunit='',newFunc) {
	unit = getValue('unit');
	divisi = divisi;
    param='method=getdivisi&unit='+unit+'&divisi='+divisi;
    tujuan='koreksi_intbsint_slave.php';
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
    tujuan='koreksi_intbsint_slave.php';
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

					// if(tipeunit=='PLASMA'){
						// showsortasi();
					// }else{
						showgrading();
					// }
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
    tujuan='koreksi_intbsint_slave.php';
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
    tujuan='koreksi_intbsint_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                	document.getElementById('showgrading').style.display='block';
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

function simpan(){
	method = 'timbang2';
	ticketno = document.getElementById('ticketno').value;
	qrcode = document.getElementById('qrcode').value;
	unit = document.getElementById('unit').value;
	divisi = document.getElementById('divisi').value;
	so = document.getElementById('so').value;
	transportir = document.getElementById('transportir').value;
	nokendaraan = document.getElementById('nokendaraan').value;
	supir = document.getElementById('supir').value;
	qtysegel = document.getElementById('qtysegel').value;
	segel = document.getElementById('segel').value;
	jjg = document.getElementById('jjg').value;
	brondol = document.getElementById('brondol').value;
	keterangan = document.getElementById('keterangan').value;
	
	productionorder = document.getElementById('productionorder').value;
	bruto = document.getElementById('bruto').value;
	kgpotongan = document.getElementById('kgpotongan').value;
	netto = document.getElementById('netto').value;

	validate([
		["qrcode","QR Code / SPB tidak boleh kosong"],
		["unit","Unit tidak boleh kosong"],
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
	
	tujuan='koreksi_intbsint_slave.php';
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
	setValue2('unit','');
	setValue2('divisi','');
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
	document.getElementById('productionorder').disabled=true;
	document.getElementById('so').disabled=true;
	document.getElementById('transportir').disabled=true;
	document.getElementById('nokendaraan').disabled=true;
	document.getElementById('supir').disabled=true;
	document.getElementById('qtysegel').disabled=true;
	document.getElementById('segel').disabled=true;
	document.getElementById('jjg').disabled=true;
	document.getElementById('brondol').disabled=true;
	document.getElementById('keterangan').disabled=true;
	document.getElementById('keterangan').disabled=true;

	document.getElementById('showgrading').style.display='none';
	
}

function fillfield() {
	ticketno = document.getElementById('ticketno').value;

    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='koreksi_intbsint_slave.php';
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
					setValue2('jjg',arrlist['janjang']);
					setValue2('brondol',arrlist['brondolan']);
					setValue2('keterangan',arrlist['keterangan']);
					
					setValue2('datein',arrlist['waktumasuk']);
					setValue2('wei1st',arrlist['beratmasuk']);
					setValue2('dateout','');
					setValue2('wei2nd','');
					setValue2('bruto',arrlist['netto']);
					setValue2('kgpotongan',arrlist['potongan']);
					setValue2('netto',arrlist['netto']);
					
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
	janjang=document.getElementById('jjg').value;
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

		//hitung persen janjang berdasarkan inputan total janjang
		if(typeof jg[i]!='undefined'){
			if(jg[i].value != ''){
				if (janjang!='') {
					pr[i].value = Math.round((parseFloat(jg[i].value)/parseFloat(janjang))*100);
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
			
		document.getElementById('ttlgrdjjg').value=Math.round(ttljg);
		document.getElementById('ttlgrdpersen').value=Math.round(ttlpr * 100) / 100;
		document.getElementById('ttlgrdkg').value=Math.round(ttlkg);
		
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
	document.getElementById('ttlgrdjjg').value='';
	document.getElementById('ttlgrdpersen').value='';
	document.getElementById('ttlgrdkg').value='';
}

function getkendaraan(){
	transportir = getValue('transportir');
	param='method=getkendaraan&transportir='+transportir;
    tujuan='koreksi_intbsint_slave.php';
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

function getproductionorder(productionorder='',so='',tipeunit='',newFunc){
	unit = getValue('unit');
	ticketno = getValue('ticketno');
	param='method=getproductionorder&unit='+unit+'&productionorder='+productionorder+'&ticketno='+ticketno;
    tujuan='koreksi_intbsint_slave.php';
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


$('#transportir').on("select2:select", function(e) { 
	getkendaraan();
});


$('#unit').on("select2:select", function(e) { 
	getdivisi();
});


const btnsimpan = document.getElementById('simpan');
if (btnsimpan) {
	btnsimpan.addEventListener('click', function () {
	  simpan();
	});
}

function calcgrad(idx=''){
	bruto=document.getElementById('bruto').value;
	janjang=document.getElementById('jjg').value;
	ttljg=0;
	ttlpr=0;
	ttlkg=0;
	
	if(bruto==''){bruto=0;}
	var jg = document.getElementsByName('jjg');
	var pr = document.getElementsByName('persen');
	var kg = document.getElementsByName('kg');
	countgrading = 0;
	
	//Check max count grading
	if(jg.length > pr.length){countgrading = jg.length;}else{countgrading = pr.length;}
	if(kg.length > countgrading){countgrading = kg.length;}
	
	for (var i = 0; i < countgrading; i++){
		if(idx!=''){
			//#########
			//## JJG ##
			//#########
			if(typeof jg[i]!='undefined'){
				if(jg[i].id==idx){
					if(jg[i].value != ''){
						setpr = parseFloat(jg[i].value) / parseFloat(janjang) * 100;						
						if(bruto > 0){
							if(typeof pr[i]!='undefined'){
								if(janjang > 0){
									pr[i].value = Math.round(parseFloat(setpr) * 100) / 100;							
								}
							}
							if(typeof kg[i]!='undefined'){
								kg[i].value = parseFloat(bruto) * parseFloat(pr[i].value) / 100;
							}
						}else{
							if(typeof pr[i]!='undefined'){
								if(janjang > 0){
									pr[i].value = Math.round(parseFloat(setpr) * 100) / 100;							
								}
							}
						}
					}
				}
			}
			
			//############
			//## PERSEN ##
			//############
			if(typeof pr[i]!='undefined'){
				if(pr[i].id==idx){
					if(pr[i].value > 0 && pr[i].value != ''){
						if(bruto > 0){
							if(typeof kg[i]!='undefined'){
								kg[i].value = parseFloat(bruto) * parseFloat(pr[i].value) / 100;							
							}
						}
					}
				}
			}
			
			//########
			//## KG ##
			//########
			if(typeof kg[i]!='undefined'){
				if(kg[i].id==idx){
					if(kg[i].value > 0 && kg[i].value != ''){
						if(bruto > 0){
							setpr = parseFloat(kg[i].value) / parseFloat(bruto) * 100;
							if(typeof kg[i]!='undefined'){
								pr[i].value = Math.round(parseFloat(setpr) * 100) / 100;							
							}
						}
					}
				}
			}
		}else{
			if(janjang > 0){
				if(typeof jg[i]!='undefined'){
					if(jg[i].value != ''){
						setpr = parseFloat(jg[i].value) / parseFloat(janjang) * 100;						
						if(bruto > 0){
							if(typeof pr[i]!='undefined'){
								if(janjang > 0){
									pr[i].value = Math.round(parseFloat(setpr) * 100) / 100;							
								}
							}
							if(typeof kg[i]!='undefined'){
								kg[i].value = parseFloat(bruto) * parseFloat(pr[i].value) / 100;
							}
						}else{
							if(typeof pr[i]!='undefined'){
								if(janjang > 0){
									pr[i].value = Math.round(parseFloat(setpr) * 100) / 100;							
								}
							}
						}
					}
				}
				
				if(typeof pr[i]!='undefined'){
					if(pr[i].value > 0 && pr[i].value != ''){
						if(bruto > 0){
							if(typeof kg[i]!='undefined'){
								kg[i].value = parseFloat(bruto) * parseFloat(pr[i].value) / 100;							
							}
						}
					}
				}
			}else{
				if(typeof jg[i]!='undefined'){
					jg[i].value = '';
				}
				if(typeof kg[i]!='undefined'){
					pr[i].value = '';
				}
				if(typeof kg[i]!='undefined'){
					kg[i].value = '';
				}
			}
		}
		
		// TOTAL GRADING
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
	
	if(document.getElementById('showsortasi').style.display==''){
		document.getElementById('kgpotongan').value=Math.round(ttlkg);
	}else{
		document.getElementById('kgpotongan').value=0;
	}
	
	netto = document.getElementById('bruto').value-document.getElementById('kgpotongan').value;
	document.getElementById('netto').value=netto;
}