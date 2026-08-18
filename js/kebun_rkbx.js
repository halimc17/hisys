// function detailData(notransaksi,divisi,kodeorg,periode,numRow,ev,tipe){
	
	// param = "method=preview&tipe="+tipe+"&notransaksi="+notransaksi+"&divisi="+divisi+"&kodeorg="+kodeorg+"&periode="+periode;
	// title=tipe;
	
	// tujuan = 'kebun_slave_rkbx.php';
	// post_response_text(tujuan, param, respog);

	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// //document.getElementById('container').innerHTML = con.responseText;
					// alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }	
// }


function detailData(notransaksi,divisi,kodeorg,periode,numRow,ev,tipe){
		param = "method=preview&tipe="+tipe+"&notransaksi="+notransaksi+"&divisi="+divisi+"&kodeorg="+kodeorg+"&periode="+periode;
        title=tipe;
		
		alertify.popuppdf("Preview","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_rkbx.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false, 'maximizable':true,'startMaximized':true}).resizeTo('80%','70%');
		
        // showDialog1(title,"<iframe frameborder=0 style='width:100%;min-height:400px'"+
        // " src='kebun_slave_rkbx.php?"+param+"'></iframe>",'1300','400',ev);	
        // var dialog = document.getElementById('dynamic1');
        // dialog.style.top = '50px';
        // dialog.style.left = '15%';
}

// function detailDataRekap(notransaksi,divisi,kodeorg,periode,numRow,ev,tipe){
		// param = "method=detailDataRekap&tipe="+tipe+"&notransaksi="+notransaksi+"&divisi="+divisi+"&kodeorg="+kodeorg+"&periode="+periode;
        // title=tipe;
        // showDialog1(title,"<iframe frameborder=0 style='width:100%;min-height:400px'"+
        // " src='kebun_slave_rkbx.php?"+param+"'></iframe>",'1300','400',ev);	
        // var dialog = document.getElementById('dynamic1');
        // dialog.style.top = '50px';
        // dialog.style.left = '15%';
// }


function detailDataRekap(notransaksi,divisi,kodeorg,periode,numRow,ev,tipe){
	param = "method=detailDataRekap&tipe="+tipe+"&notransaksi="+notransaksi+"&divisi="+divisi+"&kodeorg="+kodeorg+"&periode="+periode;
	title=tipe;
	
	tujuan = 'kebun_slave_rkbx.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('container').innerHTML = con.responseText;
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function detailPDF(notransaksi,numRow,ev,tipe) {
    param = "proses=pdf&tipe="+tipe+"&notransaksi="+notransaksi;
    
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_operasional_print_detailx.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
    // showDialog1('Print PDF',"<iframe frameborder=0 style='width:995px;height:400px'"+
        // " src='kebun_slave_operasional_print_detailx.php?"+param+"'></iframe>",'1000','400',ev);
    // var dialog = document.getElementById('dynamic1');
    // dialog.style.top = '50px';
    // dialog.style.left = '15%';
}

function postingData(notransaksi,numRow) {
    var param = "notransaksi="+notransaksi;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    x=document.getElementById('tr_'+numRow);
                    x.cells[13].innerHTML='';
                    x.cells[14].innerHTML='';
                    x.cells[15].innerHTML="<img class=\"zImgOffBtn\" title=\"Posted\" src=\"images/skyblue/posted.png\">";
 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	//alert("Yang ini belum jadi postingnya, perlu hitung ulang"); return;
    if(confirm('Akan dilakukan posting untuk transaksi '+notransaksi+
        '\nData tidak dapat diubah setelah ini. Anda yakin?')) {
        post_response_text('kebun_slave_operasional_postingx.php', param, respon);
    }
}


function edithead(notransaksi,kodeorg,periode,statussetuju){
	// alert('masuk');
    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('kodeorg').value=kodeorg;
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('periode').disabled=true;
    document.getElementById('periode').value=periode;
    document.getElementById('mode').value='edit';
    document.getElementById('statussetuju').value=statussetuju;
    document.getElementById('listData').style.display='none';
    document.getElementById('header').style.display='block';
    //document.getElementById('detail').style.display='block';
	simpanheader();
	//addHeader(notransaksi);
}



// =============================================================== //

function form_ajukan(notransaksi, unit, numrow) {
	width = '300';
	height = '';
	content = "<fieldset style=width:280px><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog5(title, content, width, height, ev);
	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi + '&unit=' + unit + '&numrow=' + numrow;
	tujuan = 'kebun_slave_rkbx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containeraju').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function ajukan() {
	kepada = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	numrow = document.getElementById('numrow').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	if (kepada == '') {
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'kebun_slave_rkbx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					x = document.getElementById('tr_' + numrow);
					x.cells[6].innerHTML = '';
					x.cells[7].innerHTML = '';
					x.cells[8].innerHTML = '';
					alert('Sucses');
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function kembali(){
	document.getElementById('uploadpemel').style.display='none';
	document.getElementById('detail').style.display='block';
}

function kembalipemelmaterial(){
	document.getElementById('uploadpemelmaterial').style.display='none';
	document.getElementById('detail').style.display='block';
}


function simpanheader(){
    notransaksi= document.getElementById('notransaksi').value;
    kodeorg= document.getElementById('kodeorg').value;
    periode= document.getElementById('periode').value;
    mode=document.getElementById('mode').value;
	document.getElementById('uploadpemel').style.display='none';
	
	if(periode==''||kodeorg==''){
        alert('Periode dan atau Kode Organisasi harus di isi !');
        return;
    }
	if(mode=='baru'){
		document.getElementById('tomboldetail').disabled = true;
	}else{
		document.getElementById('tomboldetail').disabled = false;
	}
    param = 'method=simpanheader';
    param += '&periode=' + periode+'&kodeorg=' + kodeorg+'&mode='+mode+'&notransaksi='+notransaksi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else {
					if(mode=='baru'){
						document.getElementById('notransaksi').value = trim(con.responseText);
					}
                    addHeader();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function addHeader(){
    kodeorg= document.getElementById('kodeorg').value;
    periode=document.getElementById('periode').value;
    notransaksi=document.getElementById('notransaksi').value;
    mode=document.getElementById('mode').value;
    admin=document.getElementById('admin').value;
    statussetuju=document.getElementById('statussetuju').value;
	if(periode==''||kodeorg==''){
        alert('Periode dan atau Kode Organisasi harus di isi !');
        return;
    }
    param = 'method=detail';
    param += '&periode=' + periode+'&kodeorg=' + kodeorg+'&notransaksi='+notransaksi+'&mode='+mode;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else {
                    document.getElementById('detail').style.display = 'block';
                    document.getElementById('detail').innerHTML = con.responseText;
					if(mode=='edit' && admin=='1' && statussetuju=='1'){
						//document.getElementById('tabFRM1').style.display = 'none'; //tab panen
						//document.getElementById('tabFRM2').style.display = 'none'; //tab angkut
						document.getElementById('tabFRM3').style.display = 'none';
						document.getElementById('tabFRM4').style.display = 'none';
						
					}
					loaddatadetail(notransaksi);
					
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function inputdetail(notransaksi){
    kodeorg= document.getElementById('kodeorg').value;
    notransaksi= document.getElementById('notransaksi').value;
    periode= document.getElementById('periode').value;
		
	divisi= document.getElementById('divisi').value;
	if(divisi==''){
		alert("Divisi Masih Kosong !!!"); return;
	}
	
    param = 'method=inputdetail';
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&notransaksi=' + notransaksi+'&divisi=' + divisi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('inputdetail').innerHTML = con.responseText;
					getSelect2();
					loaddatadetail(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getluas(){
    blok= document.getElementById('blok').value;
    kegiatan= document.getElementById('kegiatan').value;
    
    param = 'method=getluas';
    param += '&blok=' + blok;
    param += '&kegiatan=' + kegiatan;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('luas').value = trim(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function gettotalhk(){
	kbl= document.getElementById('kbl').value;
    kht= document.getElementById('kht').value;
    khl= document.getElementById('khl').value;
    rpperhkkbl= document.getElementById('rpperhkkbl').value;
    rpperhkkht= document.getElementById('rpperhkkht').value;
    rpperhkkhl= document.getElementById('rpperhkkhl').value;
    luas= document.getElementById('luas').value;
	
	kbl= remove_comma_var(kbl);
    kht= remove_comma_var(kht);
    khl= remove_comma_var(khl);
    rpperhkkbl= remove_comma_var(rpperhkkbl);
    rpperhkkht= remove_comma_var(rpperhkkht);
    rpperhkkhl= remove_comma_var(rpperhkkhl);
    luas= remove_comma_var(luas);
	
    
	if(rpperhkkbl=='' || rpperhkkht=='' || rpperhkkhl==''){
		alert("Rupiah per HK belum ada !!!"); return;
	}
	if(luas=='' || luas==0){
		alert("Luas belum ada !!!"); return;
	}
	
	if(kbl==''){kbl=0;}
	if(kht==''){kht=0;}
	if(khl==''){khl=0;}
	
	// alert(kbl);
	totalhk = parseFloat(kbl)+parseFloat(kht)+parseFloat(khl);
	totalrphk = (parseFloat(kbl)*parseFloat(rpperhkkbl))+(parseFloat(kht)*parseFloat(rpperhkkht))+(parseFloat(khl)*parseFloat(rpperhkkhl));
	output = parseFloat(luas)/parseFloat(totalhk);
	
	document.getElementById('jhk').value=totalhk;
	document.getElementById('jrphk').value=totalrphk;
	document.getElementById('output').value=output;
}

function gettotalbor(){
	luasbor= document.getElementById('luasbor').value;
    rpperhabor= document.getElementById('rpperhabor').value;
	
	luasbor= remove_comma_var(luasbor);
    rpperhabor= remove_comma_var(rpperhabor);
	
	totalbor = parseFloat(luasbor)*parseFloat(rpperhabor);	
	document.getElementById('rupiahbor').value=totalbor;
	
}

function getsatmat(jenis){
	if(jenis=='umum'){
		kodebarang=document.getElementById('kodebarangumm').value;
	}else{
		kodebarang=document.getElementById('kodebarang').value;
	}
	kodeorg=document.getElementById('kodeorg').value;
	divisi=document.getElementById('divisi').value;
	periode=document.getElementById('periode').value;
    
    param = 'method=getsatmat';
    param += '&kodebarang=' + kodebarang;
    param += '&kodeorg=' + kodeorg;
    param += '&divisi=' + divisi;
    param += '&periode=' + periode;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					data=con.responseText.split("####");
					if(jenis=='umum'){
						document.getElementById('satmatumm').value = trim(data[0]);
					}else{
						document.getElementById('satmat').value = trim(data[0]);
						if(data[1]==''){
							alert("Harga Barang Belum Ada !!!");
						}
						document.getElementById('hargarata').value = trim(data[1]);
						document.getElementById('stok').value = trim(data[2]);
						document.getElementById('dosismat').value='';
						document.getElementById('jlhmat').value='';
						document.getElementById('rpmat').value='';						
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getrupiahmat(jenis){
	luas= document.getElementById('luas').value;
    luas= remove_comma_var(luas);
	if(jenis=='dosis'){
		dosismat= document.getElementById('dosismat').value;
		dosismat= remove_comma_var(dosismat);
		totalmat = parseFloat(dosismat)*parseFloat(luas);	
		document.getElementById('jlhmat').value=numberFormat(totalmat,2);		
		totalmat = totalmat;
	}else{
		jlhmat= document.getElementById('jlhmat').value;
		jlhmat= remove_comma_var(jlhmat);
		totalmat = parseFloat(jlhmat)/parseFloat(luas);	
		document.getElementById('dosismat').value=numberFormat(totalmat,2);
		totalmat = jlhmat;
	}
	
	hargarata= document.getElementById('hargarata').value;
	hargarata= remove_comma_var(hargarata);
	if(hargarata==''){alert("Harga Material Belum Ada !!!"); hargarata=0;}
	totalrpmat=totalmat*parseFloat(hargarata);
	if(isNaN(totalrpmat)==true){totalrpmat=0;}
	document.getElementById('rpmat').value=numberFormat(totalrpmat,2);
	// if(totalrpmat>0){
		// document.getElementById('tombolsimpanmaterial').style.display='';
	// }
	
}

function simpandetail(clear){
	notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksi').value;
    periode= document.getElementById('periode').value;	
	divisi= document.getElementById('divisi').value;
	
	kegiatan= document.getElementById('kegiatan').value;
	blok= document.getElementById('blok').value;
	luas= document.getElementById('luas').value;
	
	kodebarang= document.getElementById('kodebarang').value;
	dosismat= document.getElementById('dosismat').value;
	jlhmat= document.getElementById('jlhmat').value;
	rpmat= document.getElementById('rpmat').value;
	hargarata= document.getElementById('hargarata').value;

	pusingan= document.getElementById('pusingan').value;
	kbl= document.getElementById('kbl').value;
	kht= document.getElementById('kht').value;
	khl= document.getElementById('khl').value;
	output= document.getElementById('output').value;
	upah= document.getElementById('jrphk').value;
	premi= document.getElementById('premi').value;
	luasbor= document.getElementById('luasbor').value;
	rpperhabor= document.getElementById('rpperhabor').value;
	rupiahbor= document.getElementById('rupiahbor').value;
	stok= document.getElementById('stok').value;

    param = 'method=simpandetail';
    param += '&notransaksi=' + notransaksi;
    param += '&hargarata=' + hargarata;
    param += '&pusingan=' + pusingan;
    param += '&stok=' + stok;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    param += '&kegiatan=' + kegiatan+'&blok=' + blok+'&luas=' + luas;
    param += '&kodebarang=' + kodebarang+'&dosismat=' + dosismat+'&jlhmat=' + jlhmat+'&rpmat=' + rpmat;
    param += '&kbl=' + kbl+'&kht=' + kht+'&khl=' + khl+'&output=' + output+'&upah=' + upah+'&premi=' + premi;
    param += '&luasbor=' + luasbor+'&rpperhabor=' + rpperhabor+'&rupiahbor=' + rupiahbor;
	
	if((upah=='' || upah==0) && (premi=='' || premi==0)&& (rupiahbor=='' || rupiahbor==0)){
		alert("Upah atau Premi atau Rupiah Borongan masih kosong !!!"); return;
	}
	if(kodebarang!=''  && (dosismat==0 || dosismat=='' || jlhmat==0 || jlhmat=='' || rpmat==0 || rpmat=='')){
		alert("Dosis atau Jumlah atau Rupiah Material Masih Kosong !!!"); return;
	}
	if(divisi==''){
		alert("Divisi Wajib di Pilih !!!"); return;
	}
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					if(clear=='clear'){
						cleardetailall();
					}
                    daftarmaterial();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function daftarmaterial(){
	notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksi').value;
    periode= document.getElementById('periode').value;	
	divisi= document.getElementById('divisi').value;
	
	kegiatan= document.getElementById('kegiatan').value;
	blok= document.getElementById('blok').value;
	
    param = 'method=daftarmaterial';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    param += '&kegiatan=' + kegiatan+'&blok=' + blok;
    
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('listmaterial').innerHTML = con.responseText;
					loaddatadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loaddatadetail(){
    notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksi').value;
    periode= document.getElementById('periode').value;	
	divisi= document.getElementById('divisi').value;

    param = 'method=loaddatadetail';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('loaddatadetail').innerHTML = con.responseText;
					loaddatadetailpnn();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletedetail(notransaksi,tipetransaksi,periode,kodeorg,divisi,kegiatan,blok){
    param='method=deletedetail';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    param += '&kegiatan=' + kegiatan+'&blok=' + blok;
    
    tujuan='kebun_slave_rkbx.php';
	if(confirm('Anda yakin ???')){
		post_response_text(tujuan, param, respog);	
	}
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
				   loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}


function cleardetailall(){
	document.getElementById('kegiatan').value='';
	document.getElementById('blok').value='';
	setValue2('kegiatan',null);
	setValue2('blok',null);
	
	document.getElementById('luas').value='';
	
	document.getElementById('kodebarang').value='';
	document.getElementById('dosismat').value='';
	document.getElementById('jlhmat').value='';
	document.getElementById('rpmat').value='';
	document.getElementById('hargarata').value='';

	document.getElementById('pusingan').value='';
	document.getElementById('kbl').value='';
	document.getElementById('kht').value='';
	document.getElementById('khl').value='';
	document.getElementById('output').value='';
	document.getElementById('jrphk').value='';
	document.getElementById('premi').value='';
	document.getElementById('luasbor').value='';
	document.getElementById('rpperhabor').value='';
	document.getElementById('rupiahbor').value='';
	document.getElementById('jhk').value='';
	document.getElementById('satmat').value='';
	//document.getElementById('tombolsimpanmaterial').style.display='none';
	document.getElementById('listmaterial').innerHTML='';
}

function getblokandbarang(){
    kegiatan= document.getElementById('kegiatan').value;
    divisi= document.getElementById('divisi').value;

    param = 'method=getblok';
    param += '&kegiatan=' + kegiatan;
    param += '&divisi=' + divisi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					data = con.responseText.split("###");
                    document.getElementById('blok').innerHTML = data[0];
                    document.getElementById('kodebarang').innerHTML = data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function inputdetailpnn(){
    notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    periode= document.getElementById('periode').value;	
	
    tipetransaksi= document.getElementById('tipetransaksipnn').value;
	divisi= document.getElementById('divisipnn').value;
	
	rpperhkkbl=document.getElementById('rpperhkkblpnn').value;
	rpperhkkht=document.getElementById('rpperhkkhtpnn').value;
	rpperhkkhl=document.getElementById('rpperhkkhlpnn').value;	
	
	if(rpperhkkbl=='' || rpperhkkht=='' || rpperhkkhl==''){
		//alert("Rupiah per HK per Tipe Karyawan masih kosong / Blank !!!"); return;
	}
	if(divisi==''){
		alert("Divisi Masih Kosong !!!"); return;
	}
    param = 'method=inputdetailpnn';
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&notransaksi=' + notransaksi+'&divisi=' + divisi;
    param += '&tipetransaksi=' + tipetransaksi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('inputdetailpnn').innerHTML = con.responseText;
					loaddatadetailpnn();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
// function getkalkulasipnn(row,jenis){
// 	totaloutputpnn = 0;
// 	jlhbrs       = document.getElementById('jlhbrs').value;
// 	pokok        = document.getElementById('pkkpnn'+row).innerHTML;
// 	rotasi       = document.getElementById('rotpnn'+row).value;
// 	akp          = document.getElementById('akppnn'+row).value;
// 	bjr          = document.getElementById('bjrpnn'+row).value;
// 	output   = document.getElementById('outputpnn'+row).value;
// 	// if(document.getElementById('outputpnn'+row).value >0){
// 	// 	output   = document.getElementById('outputpnn'+row).value;
// 	// }
	
// 	jjgpnn       = document.getElementById('jjgpnn'+row).innerHTML;
// 	kgpnn        = document.getElementById('kgpnn'+row).innerHTML;
// 	bsspnn       = document.getElementById('bsspnn'+row).innerHTML;
// 	persenbsspnn = document.getElementById('persenbsspnn'+row).innerHTML;
// 	rppremilb1pnn= document.getElementById('rppremilb1pnn'+row).innerHTML;
// 	rppremilb2pnn= document.getElementById('rppremilb2pnn'+row).innerHTML;
// 	ttlrphkpnn   = document.getElementById('ttlrphkpnn'+row).innerHTML; 
// 	// copypremibrd = document.getElementById('copypremibrd').value; 
// 	rppremibrdpnn= document.getElementById('rppremibrdpnn'+row).innerHTML;
	
// 	totalkg      = document.getElementById('totalkg').innerHTML;
// 	ttlupahmdr   = document.getElementById('ttlupahmdr').value;
// 	persenmdr    = document.getElementById('persenmdr').value;
// 	ttlupahkrn   = document.getElementById('ttlupahkrn').value;
// 	persenkrn    = document.getElementById('persenkrn').value;
// 	ttlupahmdr1  = document.getElementById('ttlupahmdr1').value;
// 	jlhmdrmdr1   = document.getElementById('jlhmdrmdr1').value;
// 	persenmdr1   = document.getElementById('persenmdr1').value;
// 	boronganpnn  = document.getElementById('borpnn'+row).value;
	
// 	if(boronganpnn==''){boronganpnn='0'}
	
// 	totaloutputpnn += parseFloat(output);
// 	console.log("total output kg: "+totaloutputpnn);
// 	console.log("output kg per blok: "+output);
// 	pokok			= remove_comma_var(pokok);
// 	rotasi			= remove_comma_var(rotasi);
// 	akp				= remove_comma_var(akp);
// 	bjr				= remove_comma_var(bjr);
// 	output			= remove_comma_var(output);
// 	jjgpnn			= remove_comma_var(jjgpnn);
// 	kgpnn			= remove_comma_var(kgpnn);
// 	ttlrphkpnn		= remove_comma_var(ttlrphkpnn);
// 	boronganpnn		= remove_comma_var(boronganpnn);
	
// 	ttlupahmdr= remove_comma_var(ttlupahmdr);
// 	totalkg= remove_comma_var(totalkg);
// 	persenmdr= remove_comma_var(persenmdr);
// 	ttlupahkrn= remove_comma_var(ttlupahkrn);
// 	persenkrn= remove_comma_var(persenkrn);
// 	ttlupahmdr1= remove_comma_var(ttlupahmdr1);
// 	jlhmdrmdr1= remove_comma_var(jlhmdrmdr1);
// 	persenmdr1= remove_comma_var(persenmdr1);
// 	rppremilb1pnn= remove_comma_var(rppremilb1pnn);
	
	
// 	jjg = parseFloat(pokok)*parseFloat(rotasi)*(parseFloat(akp)/100);
// 	kg = parseFloat(bjr)*jjg;
// 	ttlhk = kg/parseFloat(output);
// 	ttlhk = numberFormat(ttlhk,2);
// 	//ttlhk = 0; //tidak pakai HK pakai premi semua
	
// 	kglb1 = ((bsspnn * persenbsspnn) /100);
// 	kglb2 = output - bsspnn - kglb1;
	
// 	/* if(output >= kglb1){
// 		rppremilb1 = ttlhk * kglb1 * rppremilb1pnn;
// 	}else{
// 		rppremilb1 = ttlhk * output * rppremilb1pnn;
// 	} 
	
// 	rppremilb2 = ttlhk * kglb2 * rppremilb2pnn;
// 	*/
	
// 	rppremilb1 = parseFloat(kgpnn) * parseFloat(rppremilb1pnn);
// 	rppremilb2 = 0;
	
// 	// rpbrd = kg * (copypremibrd/100) * rppremibrdpnn; 
	
// 	if(isNaN(jjg)==true){jjg=0;}
// 	if(isNaN(kg)==true){kg=0;}
// 	if(isNaN(ttlhk)==true){ttlhk=0;}
// 	if(isNaN(rppremilb1)==true){rppremilb1=0;}
// 	if(isNaN(rppremilb2)==true){rppremilb2=0;}
// 	if(rppremilb1<0){rppremilb1=0;}
// 	if(rppremilb2<0){rppremilb2=0;}
	
// 	rpbrd = 0;
// 	if(isNaN(rpbrd)==true){rpbrd=0;}
// 	if(isNaN(boronganpnn)==true){boronganpnn=0;}
	
// 	subttlpremi = parseFloat(rppremilb1) + parseFloat(rppremilb2) + parseFloat(rpbrd) + parseFloat(boronganpnn);
// 	ttlupahpre = parseFloat(subttlpremi) + parseFloat(ttlrphkpnn);
// 	if(isNaN(ttlupahpre)==true){ttlupahpre=0;}
		
// 	document.getElementById('jjgpnn'+row).innerHTML=numberFormat(jjg,0);	
// 	document.getElementById('kgpnn'+row).innerHTML=numberFormat(kg,0);	
// 	// document.getElementById('ttlhkpnn'+row).innerHTML=numberFormat(ttlhk,2);
// 	document.getElementById('premi1pnn'+row).innerHTML=numberFormat(rppremilb1,0);
// 	document.getElementById('premi2pnn'+row).innerHTML=numberFormat(rppremilb2,0);
// 	// document.getElementById('premibrdpnn'+row).innerHTML=numberFormat(rpbrd,0);
// 	document.getElementById('subttlpre'+row).innerHTML=numberFormat(subttlpremi,0);
// 	document.getElementById('totalupahpre'+row).innerHTML=numberFormat(ttlupahpre,0);
	
// 	// rpupahmdr = (parseFloat(kg) / parseFloat(totalkg)) * parseFloat(ttlupahmdr);
// 	rpupahmdr = (parseFloat(output) / parseFloat(totaloutputpnn)) * parseFloat(ttlupahmdr);
// 	// rppremimdr = subttlpremi / ttlhk * parseFloat(persenmdr);
// 	rppremimdr = (parseFloat(output) / parseFloat(totaloutputpnn)) * parseFloat(persenmdr);
// 	rpupahkrn = (parseFloat(kg) / parseFloat(totalkg)) * parseFloat(ttlupahkrn);
// 	rppremikrn = subttlpremi / ttlhk * parseFloat(persenkrn);
// 	rpupahmdr1 = (parseFloat(kg) / parseFloat(totalkg)) * parseFloat(ttlupahmdr1);
// 	rppremimdr1 = rppremimdr / jlhmdrmdr1 * parseFloat(persenmdr1);
	
	
// 	if(isNaN(rpupahmdr)==true){rpupahmdr=0;}
// 	if(isNaN(rppremimdr)==true){rppremimdr=0;}
// 	if(isNaN(rpupahkrn)==true){rpupahkrn=0;}
// 	if(isNaN(rppremikrn)==true){rppremikrn=0;}
// 	if(isNaN(rpupahmdr1)==true){rpupahmdr1=0;}
// 	if(isNaN(rppremimdr1)==true){rppremimdr1=0;}
	
	
	
// 	document.getElementById('upahmdr'+row).innerHTML=numberFormat(rpupahmdr,0);
// 	document.getElementById('premimdr'+row).innerHTML=numberFormat(rppremimdr,0);
// 	document.getElementById('upahkrn'+row).innerHTML=numberFormat(rpupahkrn,0);
// 	document.getElementById('premikrn'+row).innerHTML=numberFormat(rppremikrn,0);
// 	document.getElementById('upahmdrsatu'+row).innerHTML=numberFormat(rpupahmdr1,0);
// 	document.getElementById('premimdrsatu'+row).innerHTML=numberFormat(rppremimdr1,0);
	
	
// 	gttlupahmdr = rpupahmdr + rpupahkrn + rpupahmdr1;
// 	gttlpremimdr = rppremimdr + rppremikrn + rppremimdr1;
// 	document.getElementById('ttlupahmandor'+row).innerHTML=numberFormat(gttlupahmdr,0);
// 	document.getElementById('ttlpremimandor'+row).innerHTML=numberFormat(gttlpremimdr,0);
	
// 	gtbiaya = ttlupahpre + gttlupahmdr + gttlpremimdr;
// 	gtbiayaperkg = parseFloat(gtbiaya) / parseFloat(kg);
// 	if(isNaN(gtbiayaperkg)==true){gtbiayaperkg=0;}
// 	document.getElementById('gtbiaya'+row).innerHTML=numberFormat(gtbiaya,0);
// 	document.getElementById('rpperkg'+row).innerHTML=numberFormat(gtbiayaperkg,2);
	
	
// 	if(jenis!='skipttl'){
// 		gettotal();
// 	}
// }
function getkalkulasipnn(row,jenis){
	jlhbrs       = document.getElementById('jlhbrs').value;
	totaloutputpnn = 0;
	// console.log("Jumlah Baris: "+jlhbrs);
	for (i = 1; i <= jlhbrs; i++) {
		pokok        = document.getElementById('pkkpnn'+i).innerHTML;
		rotasi       = document.getElementById('rotpnn'+i).value;
		akp          = document.getElementById('akppnn'+i).value;
		bjr          = document.getElementById('bjrpnn'+i).value;
		output   	 = document.getElementById('outputpnn'+i).value;
		
		jjgpnn       = document.getElementById('jjgpnn'+i).innerHTML;
		kgpnn        = document.getElementById('kgpnn'+i).innerHTML;
		bsspnn       = document.getElementById('bsspnn'+i).innerHTML;
		premi1pnn  	 = document.getElementById('premi1pnn'+i).value;
		premibrdpnn  = document.getElementById('premibrdpnn'+i).value;
		persenbsspnn = document.getElementById('persenbsspnn'+i).innerHTML;
		rppremilb1pnn= document.getElementById('rppremilb1pnn'+i).innerHTML;
		rppremilb2pnn= document.getElementById('rppremilb2pnn'+i).innerHTML;
		ttlrphkpnn   = document.getElementById('ttlrphkpnn'+i).innerHTML; 
		// copypremibrd = document.getElementById('copypremibrd').value; 
		rppremibrdpnn= document.getElementById('rppremibrdpnn'+i).innerHTML;
		
		totalkg      = document.getElementById('totalkg').innerHTML;
		ttlupahmdr   = document.getElementById('ttlupahmdr').value;
		persenmdr    = document.getElementById('persenmdr').value;
		ttlupahkrn   = document.getElementById('ttlupahkrn').value;
		persenkrn    = document.getElementById('persenkrn').value;
		// ttlupahmdr1  = document.getElementById('ttlupahmdr1').value;
		// jlhmdrmdr1   = document.getElementById('jlhmdrmdr1').value;
		// persenmdr1   = document.getElementById('persenmdr1').value;
		// boronganpnn  = document.getElementById('borpnn'+i).value;
		
		// if(boronganpnn==''){boronganpnn='0'}
		for (x = 1; x <= jlhbrs; x++) {
			outputxz   	 = document.getElementById('outputpnn'+x).value;
			totaloutputpnn = totaloutputpnn + parseFloat(outputxz);
		}
		// console.log("total output kg: "+totaloutputpnn);
		// console.log("output kg per blok: "+output);
		
		pokok			= remove_comma_var(pokok);
		rotasi			= remove_comma_var(rotasi);
		akp				= remove_comma_var(akp);
		bjr				= remove_comma_var(bjr);
		output			= remove_comma_var(output);
		jjgpnn			= remove_comma_var(jjgpnn);
		kgpnn			= remove_comma_var(kgpnn);
		ttlrphkpnn		= remove_comma_var(ttlrphkpnn);
		// boronganpnn		= remove_comma_var(boronganpnn);
		
		ttlupahmdr= remove_comma_var(ttlupahmdr);
		totalkg= remove_comma_var(totalkg);
		persenmdr= remove_comma_var(persenmdr);
		ttlupahkrn= remove_comma_var(ttlupahkrn);
		persenkrn= remove_comma_var(persenkrn);
		rppremilb1pnn= remove_comma_var(rppremilb1pnn);
		premi1pnn= remove_comma_var(premi1pnn);
		premibrdpnn= remove_comma_var(premibrdpnn);
		// ttlupahmdr1= remove_comma_var(ttlupahmdr1);
		// jlhmdrmdr1= remove_comma_var(jlhmdrmdr1);
		// persenmdr1= remove_comma_var(persenmdr1);
		
		
		jjg = parseFloat(pokok)*parseFloat(rotasi)*(parseFloat(akp)/100);
		kg = parseFloat(bjr)*jjg;
		ttlhk = kg/parseFloat(output);
		ttlhk = numberFormat(ttlhk,2);
		//ttlhk = 0; //tidak pakai HK pakai premi semua
		
		kglb1 = ((bsspnn * persenbsspnn) /100);
		kglb2 = output - bsspnn - kglb1;
		
		// if(output >= kglb1){
		// 	rppremilb1 = ttlhk * kglb1 * rppremilb1pnn;
		// }else{
		// 	rppremilb1 = ttlhk * output * rppremilb1pnn;
		// } 
		
		// rppremilb2 = ttlhk * kglb2 * rppremilb2pnn;
		// rppremilb1 = parseFloat(kgpnn) * parseFloat(rppremilb1pnn);
		
		
		rppremilb1 = parseFloat(premi1pnn);
		rppremilb2 = 0;
		
		// rpbrd = kg * (copypremibrd/100) * rppremibrdpnn; 
		rpbrd = premibrdpnn; 
		
		if(isNaN(jjg)==true){jjg=0;}
		if(isNaN(kg)==true){kg=0;}
		if(isNaN(ttlhk)==true){ttlhk=0;}
		if(isNaN(rppremilb1)==true){rppremilb1=0;}
		if(isNaN(rppremilb2)==true){rppremilb2=0;}
		if(rppremilb1<0){rppremilb1=0;}
		// if(rppremilb2<0){rppremilb2=0;}
		
		// rpbrd = 0;
		if(isNaN(rpbrd)==true || rpbrd == ''){rpbrd=0;}
		// if(isNaN(boronganpnn)==true){boronganpnn=0;}
		
		// subttlpremi = parseFloat(rppremilb1) + parseFloat(rppremilb2) + parseFloat(rpbrd) + parseFloat(boronganpnn);
		subttlpremi = parseFloat(rppremilb1) + parseFloat(rpbrd);
		ttlupahpre = parseFloat(subttlpremi) + parseFloat(ttlrphkpnn);
		if(isNaN(ttlupahpre)==true){ttlupahpre=0;}
			
		document.getElementById('jjgpnn'+i).innerHTML=numberFormat(jjg,0);	
		document.getElementById('kgpnn'+i).innerHTML=numberFormat(kg,0);	
		// document.getElementById('ttlhkpnn'+i).innerHTML=numberFormat(ttlhk,2);
		// document.getElementById('premi1pnn'+i).innerHTML=numberFormat(rppremilb1,0);
		// document.getElementById('premi2pnn'+i).innerHTML=numberFormat(rppremilb2,0);
		// document.getElementById('premibrdpnn'+i).innerHTML=numberFormat(rpbrd,0);
		document.getElementById('subttlpre'+i).innerHTML=numberFormat(subttlpremi,0);
		document.getElementById('totalupahpre'+i).innerHTML=numberFormat(ttlupahpre,0);
		
		// rpupahmdr = (parseFloat(kg) / parseFloat(totalkg)) * parseFloat(ttlupahmdr);
		rpupahmdr = (parseFloat(output) / parseFloat(totaloutputpnn)) * parseFloat(ttlupahmdr);
		// rppremimdr = subttlpremi / ttlhk * parseFloat(persenmdr);
		rppremimdr = (parseFloat(output) / parseFloat(totaloutputpnn)) * parseFloat(persenmdr);
		rpupahkrn = (parseFloat(kg) / parseFloat(totalkg)) * parseFloat(ttlupahkrn);
		rppremikrn = subttlpremi / ttlhk * parseFloat(persenkrn);
		//   = (parseFloat(kg) / parseFloat(totalkg)) * parseFloat(ttlupahmdr1);
		// rppremimdr1 = rppremimdr / jlhmdrmdr1 * parseFloat(persenmdr1);
		
		// console.log("Upah Rupiah per blok: "+rpupahmdr);
		
		if(isNaN(rpupahmdr)==true){rpupahmdr=0;}
		if(isNaN(rppremimdr)==true){rppremimdr=0;}
		if(isNaN(rpupahkrn)==true){rpupahkrn=0;}
		if(isNaN(rppremikrn)==true){rppremikrn=0;}
		// if(isNaN(rpupahmdr1)==true){rpupahmdr1=0;}
		// if(isNaN(rppremimdr1)==true){rppremimdr1=0;}
		
		
		
		document.getElementById('upahmdr'+i).innerHTML=numberFormat(rpupahmdr,0);
		document.getElementById('premimdr'+i).innerHTML=numberFormat(rppremimdr,0);
		document.getElementById('upahkrn'+i).innerHTML=numberFormat(rpupahkrn,0);
		document.getElementById('premikrn'+i).innerHTML=numberFormat(rppremikrn,0);
		// document.getElementById('upahmdrsatu'+i).innerHTML=numberFormat(rpupahmdr1,0);
		// document.getElementById('premimdrsatu'+i).innerHTML=numberFormat(rppremimdr1,0);
		
		
		// gttlupahmdr = rpupahmdr + rpupahkrn + rpupahmdr1;
		// gttlpremimdr = rppremimdr + rppremikrn + rppremimdr1;

		gttlupahmdr = rpupahmdr + rpupahkrn;
		gttlpremimdr = rppremimdr + rppremikrn;
		document.getElementById('ttlupahmandor'+i).innerHTML=numberFormat(gttlupahmdr,0);
		document.getElementById('ttlpremimandor'+i).innerHTML=numberFormat(gttlpremimdr,0);
		
		gtbiaya = ttlupahpre + gttlupahmdr + gttlpremimdr;
		gtbiayaperkg = parseFloat(gtbiaya) / parseFloat(kg);
		if(isNaN(gtbiayaperkg)==true){gtbiayaperkg=0;}
		document.getElementById('gtbiaya'+i).innerHTML=numberFormat(gtbiaya,0);
		document.getElementById('rpperkg'+i).innerHTML=numberFormat(gtbiayaperkg,2);	
	}
	
	
	if(jenis!='skipttl'){
		gettotal();
	}
}

function gettotal(){
	jlhbrs=document.getElementById('jlhbrs').value;
	totalkg = 0;
	totaljjg = 0;
	totalhkpnn = 0;
	totalpremi2pnn=totalpremi1pnn = totalupahpnn = 0;
	for(i=1;i<=jlhbrs;i++){
		jjgpnn = document.getElementById('jjgpnn'+i).innerHTML;
		kgpnn = document.getElementById('kgpnn'+i).innerHTML;
		// ttlhkpnn = document.getElementById('ttlhkpnn'+i).innerHTML;
		ttlrphkpnn = document.getElementById('ttlrphkpnn'+i).innerHTML;
		// premi1pnn = document.getElementById('premi1pnn'+i).innerHTML;
		premi1pnn = document.getElementById('premi1pnn'+i).value;
		// premi2pnn = document.getElementById('premi2pnn'+i).innerHTML;
		
		if(kgpnn==''){kgpnn = 0;}else{kgpnn = remove_comma_var(kgpnn);}
		if(jjgpnn==''){jjgpnn = 0;}else{jjgpnn = remove_comma_var(jjgpnn);}
		// if(ttlhkpnn==''){ttlhkpnn = 0;}else{ttlhkpnn = remove_comma_var(ttlhkpnn);}
		if(ttlrphkpnn==''){ttlrphkpnn = 0;}else{ttlrphkpnn = remove_comma_var(ttlrphkpnn);}
		if(premi1pnn==''){premi1pnn = 0;}else{premi1pnn = remove_comma_var(premi1pnn);}
		// if(premi2pnn==''){premi2pnn = 0;}else{premi2pnn = remove_comma_var(premi2pnn);}
		if(isNaN(kgpnn)==true){kgpnn=0;}
		if(isNaN(jjgpnn)==true){jjgpnn=0;}
		// if(isNaN(ttlhkpnn)==true){ttlhkpnn=0;}
		if(isNaN(ttlrphkpnn)==true){ttlrphkpnn=0;}
		if(isNaN(premi1pnn)==true){premi1pnn=0;}
		// if(isNaN(premi2pnn)==true){premi2pnn=0;}
		
		totalkg = totalkg + parseFloat(kgpnn);
		totaljjg = totaljjg + parseFloat(jjgpnn);
		// totalhkpnn = totalhkpnn + parseFloat(ttlhkpnn);
		totalupahpnn = totalupahpnn + parseFloat(ttlrphkpnn);
		totalpremi1pnn = totalpremi1pnn + parseFloat(premi1pnn);
		// totalpremi2pnn = totalpremi2pnn + parseFloat(premi2pnn);
	}
	document.getElementById('totaljjg').innerHTML=numberFormat(totaljjg,0);
	document.getElementById('totalkg').innerHTML=numberFormat(totalkg,0);
	// document.getElementById('totalhk').innerHTML=numberFormat(totalhkpnn,0);
	document.getElementById('totalupahpnn').innerHTML=numberFormat(totalupahpnn,0);
	document.getElementById('totalpremi1pnn').innerHTML=numberFormat(totalpremi1pnn,0);
	// document.getElementById('totalpremi2pnn').innerHTML=numberFormat(totalpremi2pnn,0);
}


function getkalkulasihk(row){
	rpperhkkbl=document.getElementById('rpperhkkblpnn').value;
	rpperhkkht=document.getElementById('rpperhkkhtpnn').value;
	rpperhkkhl=document.getElementById('rpperhkkhlpnn').value;	
	// ttlhkpnn=document.getElementById('ttlhkpnn'+row).innerHTML;	
	// ttlhkpnn= remove_comma_var(ttlhkpnn);
	
	if(rpperhkkbl==''){rpperhkkbl=0;}
	if(rpperhkkht==''){rpperhkkht=0;}
	if(rpperhkkhl==''){rpperhkkhl=0;}
				
	kblpnn = document.getElementById('kblpnn'+row).value;
	khtpnn = document.getElementById('khtpnn'+row).value;
	khlpnn = document.getElementById('khlpnn'+row).value;
	kblpnn= remove_comma_var(kblpnn);
	khtpnn= remove_comma_var(khtpnn);
	khlpnn= remove_comma_var(khlpnn);

	if(kblpnn==''){kblpnn=0;}
	if(khtpnn==''){khtpnn=0;}
	if(khlpnn==''){khlpnn=0;}
	
	ttlhk = parseFloat(kblpnn)+parseFloat(khtpnn)+parseFloat(khlpnn);
	// if(ttlhk>ttlhkpnn){
	// 	alert("Jumlah HK melebihi total HK"); return;
	// }
	// console.log("HK KHL: "+khlpnn);
	// console.log("Rupiah KHL: "+rpperhkkhl);

	ttlrp = (parseFloat(kblpnn)*parseFloat(rpperhkkbl))+(parseFloat(khtpnn)*parseFloat(rpperhkkht))+(parseFloat(khlpnn)*parseFloat(rpperhkkhl));
	document.getElementById('ttlrphkpnn'+row).innerHTML=numberFormat(ttlrp);
}


function proporsihkpanen(){
	jlhbrs=document.getElementById('jlhbrs').value;
	// console.log("Jumlah Baris "+jlhbrs);
	if(document.getElementById('jlhbrs')!=undefined){		
		// persenkbl=document.getElementById('persenkbl').value;
		// persenkht=document.getElementById('persenkht').value;
		// persenkhl=document.getElementById('persenkhl').value;
		
		rpperhkkbl=document.getElementById('rpperhkkblpnn').value;
		rpperhkkht=document.getElementById('rpperhkkhtpnn').value;
		rpperhkkhl=document.getElementById('rpperhkkhlpnn').value;	

		if(jlhbrs==''){jlhbrs=0;}
		// if(persenkbl==''){persenkbl=0;}
		// if(persenkht==''){persenkht=0;}
		// if(persenkhl==''){persenkhl=0;}
		
		if(rpperhkkbl==''){rpperhkkbl=0;}
		if(rpperhkkht==''){rpperhkkht=0;}
		if(rpperhkkhl==''){rpperhkkhl=0;}
	
	
		// ttlpersen = parseFloat(persenkbl)+parseFloat(persenkht)+parseFloat(persenkhl);
		ttlpersen = 1;
		if(jlhbrs>0){
			for(i=1;i<=jlhbrs;i++){
				// ttlhkpnn = document.getElementById('ttlhkpnn'+i).innerHTML;	
				// ttlhkpnn= remove_comma_var(ttlhkpnn);
				// hkkbl = (parseFloat(persenkbl)/ttlpersen) * ttlhkpnn;
				// hkkht = (parseFloat(persenkht)/ttlpersen) * ttlhkpnn;
				// hkkhl = (parseFloat(persenkhl)/ttlpersen) * ttlhkpnn;
				
				// hkkbl = (parseFloat(persenkbl)/ttlpersen) * ttlhkpnn;
				// hkkht = (parseFloat(persenkht)/ttlpersen) * ttlhkpnn;
				// hkkhl = (parseFloat(persenkhl)/ttlpersen) * ttlhkpnn;
				
				// if(isNaN(hkkbl)==true){hkkbl=0;}
				// if(isNaN(hkkht)==true){hkkht=0;}
				// if(isNaN(hkkhl)==true){hkkhl=0;}

				hkkbl = document.getElementById('kblpnn'+i).value;
				hkkht = document.getElementById('khtpnn'+i).value;
				hkkhl = document.getElementById('khlpnn'+i).value;
				
				ttlrp = (hkkbl*parseFloat(rpperhkkbl))+(hkkht*parseFloat(rpperhkkht))+(hkkhl*parseFloat(rpperhkkhl));
				if(isNaN(ttlrp)==true){ttlrp=0;}
				document.getElementById('ttlrphkpnn'+i).innerHTML=numberFormat(ttlrp);
				getkalkulasipnn(i,'skipttl');

				// subttlpre = document.getElementById('subttlpre'+i).innerHTML;
				// subttlpre= remove_comma_var(subttlpre);
				// ttlupahpre = parseFloat(subttlpre) + ttlrp;
				// if(isNaN(ttlupahpre)==true){ttlupahpre=0;}
				// document.getElementById('totalupahpre'+i).innerHTML=numberFormat(ttlupahpre,0);
			}
			gettotal();
		}
	}
}

function proporsihk(){
	if(document.getElementById('jlhbrs')!=undefined){		
		jlhbrs=document.getElementById('jlhbrs').value;
		
		persenkbl=document.getElementById('persenkbl').value;
		persenkht=document.getElementById('persenkht').value;
		persenkhl=document.getElementById('persenkhl').value;
		
		rpperhkkbl=document.getElementById('rpperhkkblpnn').value;
		rpperhkkht=document.getElementById('rpperhkkhtpnn').value;
		rpperhkkhl=document.getElementById('rpperhkkhlpnn').value;	

		if(jlhbrs==''){jlhbrs=0;}
		if(persenkbl==''){persenkbl=0;}
		if(persenkht==''){persenkht=0;}
		if(persenkhl==''){persenkhl=0;}
		
		if(rpperhkkbl==''){rpperhkkbl=0;}
		if(rpperhkkht==''){rpperhkkht=0;}
		if(rpperhkkhl==''){rpperhkkhl=0;}
	
	
		ttlpersen = parseFloat(persenkbl)+parseFloat(persenkht)+parseFloat(persenkhl);
		if(jlhbrs>0){
			for(i=1;i<=jlhbrs;i++){
				ttlhkpnn = document.getElementById('ttlhkpnn'+i).innerHTML;	
				ttlhkpnn= remove_comma_var(ttlhkpnn);
				hkkbl = (parseFloat(persenkbl)/ttlpersen) * ttlhkpnn;
				hkkht = (parseFloat(persenkht)/ttlpersen) * ttlhkpnn;
				hkkhl = (parseFloat(persenkhl)/ttlpersen) * ttlhkpnn;
				
				if(isNaN(hkkbl)==true){hkkbl=0;}
				if(isNaN(hkkht)==true){hkkht=0;}
				if(isNaN(hkkhl)==true){hkkhl=0;}
				document.getElementById('kblpnn'+i).value=numberFormat(hkkbl,1);
				document.getElementById('khtpnn'+i).value=numberFormat(hkkht,1);
				document.getElementById('khlpnn'+i).value=numberFormat(hkkhl,1);
				
				
				ttlrp = (hkkbl*parseFloat(rpperhkkbl))+(hkkht*parseFloat(rpperhkkht))+(hkkhl*parseFloat(rpperhkkhl));
				if(isNaN(ttlrp)==true){ttlrp=0;}
				//ksp tidak pakei hk
				//document.getElementById('ttlrphkpnn'+i).innerHTML=numberFormat(ttlrp);
				getkalkulasipnn(i,'skipttl');

				subttlpre = document.getElementById('subttlpre'+i).innerHTML;
				subttlpre= remove_comma_var(subttlpre);
				ttlupahpre = parseFloat(subttlpre) + ttlrp;
				if(isNaN(ttlupahpre)==true){ttlupahpre=0;}
				document.getElementById('totalupahpre'+i).innerHTML=numberFormat(ttlupahpre,0);
			}
			gettotal();
		}
	}
}


function copy(jenis){
	jlhbrs=document.getElementById('jlhbrs').value;
	// copyrot=document.getElementById('copyrot').value;
	// copyakp=document.getElementById('copyakp').value;
	// copyoutput=document.getElementById('copyoutput').value;
	// copypremibrd=document.getElementById('copypremibrd').value;
	
	if(jenis=='rotasi'){
		document.getElementById('ttlupahmdr').value='0';
		document.getElementById('persenmdr').value='0';
		document.getElementById('ttlupahkrn').value='0';
		document.getElementById('persenkrn').value='0';
		// document.getElementById('ttlupahmdr1').value='0';
		// document.getElementById('jlhmdrmdr1').value='0';
		// document.getElementById('persenmdr1').value='0';
		if(jlhbrs>0){
			for(i=1;i<=jlhbrs;i++){
				document.getElementById('rotpnn'+i).value=copyrot;
				getkalkulasipnn(i,'skipttl');
			}
			gettotal();
		}
	}else if(jenis=='akp'){
		document.getElementById('ttlupahmdr').value='0';
		document.getElementById('persenmdr').value='0';
		document.getElementById('ttlupahkrn').value='0';
		document.getElementById('persenkrn').value='0';
		// document.getElementById('ttlupahmdr1').value='0';
		// document.getElementById('jlhmdrmdr1').value='0';
		// document.getElementById('persenmdr1').value='0';
		if(jlhbrs>0){
			for(i=1;i<=jlhbrs;i++){
				document.getElementById('akppnn'+i).value=copyakp;
				getkalkulasipnn(i,'skipttl');
				
			}
			gettotal();
		}
	}else if(jenis=='output'){
		if(jlhbrs>0){
			for(i=1;i<=jlhbrs;i++){
				document.getElementById('outputpnn'+i).value=copyoutput;
				getkalkulasipnn(i,'skipttl');
			}
		}
	}else if(jenis=='premibrd' || jenis=='mdr' || jenis=='prsnmdr' || jenis=='krn' || jenis=='prsnkrn' || jenis=='mdr1' || jenis=='jlhmdr1' || jenis=='prsnmdr1'){
		if(jlhbrs>0){
			for(i=1;i<=jlhbrs;i++){
				getkalkulasipnn(i,'skipttl');
			}
		}
	}
}

function info(jenis){
	// if(jenis=='mdr'){
		// alert("Isikan Total Rupiah Upah Mandor !!!");
		// document.getElementById('ttlupahmdr').focus(); return;
	// }else if(jenis=='prsnmdr'){
		// alert("Isikan Persen Premi Mandor !!! \n\nFormula : Rata2 premi kary dikali Persen");
	// }
	
	
}


max = 0
sekarang = 1;
function simpanallpnn(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Simpan semua ???")) {
		max = maxRow;
		savedetail(1, maxRow);
	}
}
function savedetail(currRow, maxRow) {
	notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    periode= document.getElementById('periode').value;	
	
    tipetransaksi= document.getElementById('tipetransaksipnn').value;
	divisi= document.getElementById('divisipnn').value;
	
	blok= document.getElementById('blokpnn'+currRow).innerHTML;
	rotasi= document.getElementById('rotpnn'+currRow).value;
	akp= document.getElementById('akppnn'+currRow).value;
	bjr= document.getElementById('bjrpnn'+currRow).value;
	jjg= document.getElementById('jjgpnn'+currRow).innerHTML;
	kg= document.getElementById('kgpnn'+currRow).innerHTML;
	output= document.getElementById('outputpnn'+currRow).value;
	kbl= document.getElementById('kblpnn'+currRow).value;
	kht= document.getElementById('khtpnn'+currRow).value;
	khl= document.getElementById('khlpnn'+currRow).value;
		
	// ttlhkpnn= document.getElementById('ttlhkpnn'+currRow).innerHTML;
	ttlupahmdr= document.getElementById('ttlupahmdr').value;
	persenmdr= document.getElementById('persenmdr').value;
	ttlupahkrn= document.getElementById('ttlupahkrn').value;
	persenkrn= document.getElementById('persenkrn').value;
	// ttlupahmdr1= document.getElementById('ttlupahmdr1').value;
	// persenmdr1= document.getElementById('persenmdr1').value;
	// copypremibrd= document.getElementById('copypremibrd').value;
	// jlhmdrmdr1= document.getElementById('jlhmdrmdr1').value;
			
	upah= document.getElementById('ttlrphkpnn'+currRow).innerHTML;
	premi1= document.getElementById('premi1pnn'+currRow).value;
	// premi2= document.getElementById('premi2pnn'+currRow).innerHTML;
	// brondol= document.getElementById('premibrdpnn'+currRow).innerHTML;
	brondol= document.getElementById('premibrdpnn'+currRow).value;
	// boronganpnn= document.getElementById('borpnn'+currRow).value;
	kgbrd= document.getElementById('kgbrd'+currRow).value;
	upahmdr= document.getElementById('upahmdr'+currRow).innerHTML;
	premimdr= document.getElementById('premimdr'+currRow).innerHTML;
	upahkrn= document.getElementById('upahkrn'+currRow).innerHTML;
	premikrn= document.getElementById('premikrn'+currRow).innerHTML;
	// upahmdrsatu= document.getElementById('upahmdrsatu'+currRow).innerHTML;
	// premimdrsatu= document.getElementById('premimdrsatu'+currRow).innerHTML;
	
	
	method = 'simpanallpnn';
	param = "";
	param += 'notransaksi=' + notransaksi;
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&tipetransaksi=' + tipetransaksi + '&divisi=' + divisi;
	param += "&blok=" + blok;
	param += "&rotasi=" + rotasi;
	param += "&akp=" + akp;
	param += "&bjr=" + bjr;
	param += "&jjg=" + jjg;
	param += "&kg=" +kg;
	param += "&output=" +output;
	param += "&kbl=" +kbl;
	param += "&kht=" +kht;
	param += "&khl=" +khl;
	param += "&upah=" +upah;
	param += "&premi1=" +premi1;
	param += "&brondol=" +brondol;
	param += "&upahmdr=" +upahmdr;
	param += "&premimdr=" +premimdr;
	param += "&upahkrn=" +upahkrn;
	param += "&premikrn=" +premikrn;
	// param += "&premi2=" +premi2;
	// param += "&upahmdrsatu=" +upahmdrsatu;
	// param += "&premimdrsatu=" +premimdrsatu;
	
	
	// param += "&ttlhkpnn=" + ttlhkpnn;
	param += '&ttlupahmdr=' + ttlupahmdr + '&persenmdr=' + persenmdr;
	param += '&ttlupahkrn=' + ttlupahkrn + '&persenkrn=' + persenkrn;
	param += "&kgbrd=" + kgbrd;
	// param += '&ttlupahmdr1=' + ttlupahmdr1 + '&jlhmdrmdr1=' + jlhmdrmdr1+ '&persenmdr1=' + persenmdr1;
	// param += "&copypremibrd=" + copypremibrd;
	// param += "&boronganpnn=" + boronganpnn;
	

	param += '&method=' + method;
	tujuan = 'kebun_slave_rkbx.php';
	post_response_text(tujuan, param, respog);
	
	document.getElementById('rowpnn'+currRow).style.backgroundColor='cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('rowpnn' + currRow).style.backgroundColor = 'red';
				} else {
						loaddatadetailpnn();
					if (currRow != undefined) {
						document.getElementById('rowpnn' + currRow).style.backgroundColor = '';
					}
					currRow += 1;
					sekarang = currRow;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						loaddatadetailpnn();
					} else {
						savedetail(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetailpnn(){
    notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksipnn').value;
    periode= document.getElementById('periode').value;	
	divisi= document.getElementById('divisipnn').value;

    param = 'method=loaddatadetailpnn';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('loaddatadetailpnn').innerHTML = con.responseText;
					loaddatadetailangkut();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getRandomColor() {
	var letters = '0123456789ABCDEF';
	var color = '#';
	for (var i = 0; i < 6; i++) {
	color += letters[Math.floor(Math.random() * 16)];
	}
	return color;
}

// === PENGANGKUTAN ===

function inputdetailangkut(){
    notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    periode= document.getElementById('periode').value;	
	
    tipetransaksi= document.getElementById('tipetransaksiangkut').value;
	divisi= document.getElementById('divisiangkut').value;
	
	rpperhkkbl=document.getElementById('rpperhkkblangkut').value;
	rpperhkkht=document.getElementById('rpperhkkblangkut').value;
	rpperhkkhl=document.getElementById('rpperhkkblangkut').value;	
	
	if(rpperhkkbl=='' || rpperhkkht=='' || rpperhkkhl==''){
		//alert("Rupiah per HK per Tipe Karyawan masih kosong / Blank !!!"); return;
	}
	if(divisi==''){
		alert("Divisi Masih Kosong !!!"); return;
	}
    param = 'method=inputdetailangkut';
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&notransaksi=' + notransaksi+'&divisi=' + divisi;
    param += '&tipetransaksi=' + tipetransaksi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('inputdetailangkut').innerHTML = con.responseText;
					loaddatadetailangkut();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getkalkulasiangkut(row,jenis){
	jlhbrs=document.getElementById('jlhbrsangkut').value;
	ttlprod=document.getElementById('ttlprodangkut'+row).innerHTML;
	jarakpks=document.getElementById('jarakpksangkut'+row).value;
	persenangksdr=document.getElementById('persensdrangkut'+row).value;
	kapangkut=document.getElementById('kapsdrangkut'+row).value;
	rpperkg=document.getElementById('rpkgangkut'+row).value;
	rpperkm=document.getElementById('rpkmangkut'+row).value;
	persenkontangkut = document.getElementById('persenkontangkut'+row).value;
	tonkontangkut 	 = document.getElementById('tonkontangkut'+row).value;
	rpkont=document.getElementById('rpkgkontangkut'+row).value;
	outputkg=document.getElementById('outputkghkangkut'+row).value;
	// basiskg=document.getElementById('basiskghkangkut'+row).value;
	persenkbl=document.getElementById('persenkblangkut').value;
	persenkht=document.getElementById('persenkhtangkut').value;
	persenkhl=document.getElementById('persenkhlangkut').value;
	rpperkgpremi=document.getElementById('rpkgpremiangkut'+row).value;
	ttlrphkangkut=document.getElementById('ttlrphkangkut'+row).innerHTML;

	kgpremi = document.getElementById('kgpremiangkut'+row).value;

	
	persenalong=document.getElementById('persenalong'+row).value;
	hargalong=document.getElementById('hargalong'+row).value;
	
	persenmekanis=document.getElementById('persenmekanis'+row).value;
	hargamekanis=document.getElementById('hargamekanis'+row).value;
	
	persenalong= remove_comma_var(persenalong);
	hargalong= remove_comma_var(hargalong);
	persenmekanis= remove_comma_var(persenmekanis);
	hargamekanis= remove_comma_var(hargamekanis);
	ttlprod= remove_comma_var(ttlprod);
	jarakpks= remove_comma_var(jarakpks);
	persenangksdr= remove_comma_var(persenangksdr);
	kapangkut= remove_comma_var(kapangkut);
	rpperkg= remove_comma_var(rpperkg);
	rpperkm= remove_comma_var(rpperkm);
	rpkont= remove_comma_var(rpkont);
	outputkg= remove_comma_var(outputkg);
	// basiskg= remove_comma_var(basiskg);
	rpperkgpremi= remove_comma_var(rpperkgpremi);
	kgpremi= remove_comma_var(kgpremi);
	if(ttlrphkangkut!=undefined){
		ttlrphkangkut= remove_comma_var(ttlrphkangkut);
	}
	
	
	
	kgsdr = parseFloat(ttlprod)*(parseFloat(persenangksdr)/100);
	if(isNaN(kgsdr)==true){kgsdr=0;}
	trippks = parseFloat(kgsdr)/parseFloat(kapangkut);
	if(isNaN(trippks)==true){trippks=0;}
	ttlkm = parseFloat(jarakpks)*parseFloat(trippks)*2;
	if(isNaN(ttlkm)==true){ttlkm=0;}
	
	if(jenis=='rpkg'){
		ttlrpangkut = parseFloat(rpperkg)*parseFloat(kgsdr);
		if(isNaN(ttlrpangkut)==true){ttlrpangkut=0;}
		rpperkm = ttlrpangkut / parseFloat(ttlkm);
		document.getElementById('rpkmangkut'+row).value=numberFormat(rpperkm,0);	
		document.getElementById('ttlrpsdrangkut'+row).innerHTML=numberFormat(ttlrpangkut,0);	
	}else if(jenis=='rpkm'){
		ttlrpangkut = parseFloat(rpperkm)*parseFloat(ttlkm);
		if(isNaN(ttlrpangkut)==true){ttlrpangkut=0;}
		rpperkg = ttlrpangkut / parseFloat(kgsdr);
		document.getElementById('rpkgangkut'+row).value=numberFormat(rpperkg,0);	
		document.getElementById('ttlrpsdrangkut'+row).innerHTML=numberFormat(ttlrpangkut,0);	
	}
	
	if(persenangksdr==''){persenangksdr=0;}
	persenkont = 100 - parseFloat(persenangksdr);
	if(isNaN(persenkont)==true){persenkont=0;}
	if(persenangksdr>100){
		alert("Persen tidak boleh lebih dari 100 !!!");
		document.getElementById('persensdrangkut'+row).value='100';
	}
	kgkont = parseFloat(ttlprod) - kgsdr;
	// rpttlkont = kgkont * parseFloat(rpkont);
	rpttlkont = tonkontangkut * parseFloat(rpkont);
	if(isNaN(rpttlkont)==true){rpttlkont=0;}
	ttlhk = parseFloat(ttlprod)/parseFloat(outputkg);
	if(isNaN(ttlhk)==true){ttlhk=0;}
	// ttlkgbasis = parseFloat(basiskg)*parseFloat(ttlhk);
	// if(isNaN(ttlkgbasis)==true){ttlkgbasis=0;}
	
	ttlpersen = parseFloat(persenkbl)+parseFloat(persenkht)+parseFloat(persenkhl);
	kbl = ttlhk * parseFloat(persenkbl)/ttlpersen;
	if(isNaN(kbl)==true){kbl=0;}
	kht = ttlhk * parseFloat(persenkht)/ttlpersen;
	if(isNaN(kht)==true){kht=0;}
	khl = ttlhk * parseFloat(persenkhl)/ttlpersen;
	if(isNaN(khl)==true){khl=0;}
	
	// kgpremi = parseFloat(ttlprod)-parseFloat(ttlkgbasis);
	// kgpremi = parseFloat(ttlprod);
	if(isNaN(kgpremi)==true){kgpremi=0;}
	ttlpremi = parseFloat(kgpremi)*parseFloat(rpperkgpremi);
	if(isNaN(ttlpremi)==true){ttlpremi=0;}
	
	ttlupahpremi = parseFloat(ttlpremi)+parseFloat(ttlrphkangkut);
	if(isNaN(ttlupahpremi)==true){ttlupahpremi=0;}
	rpkgupahpremiangkut=ttlupahpremi/parseFloat(kgsdr);
	if(isNaN(rpkgupahpremiangkut)==true){rpkgupahpremiangkut=0;}
	
	ttlrpangkut = document.getElementById('ttlrpsdrangkut'+row).innerHTML;	
	ttlrpangkut= remove_comma_var(ttlrpangkut);

	tonalong2 = (parseFloat(persenalong) * parseFloat(ttlprod))/100;
	if(isNaN(tonalong2)==true){tonalong2=0;}
	rpalong2 = parseFloat(tonalong2) * parseFloat(hargalong);
	if(isNaN(rpalong2)==true){rpalong2=0;}
	
	tonmekanis2 = (parseFloat(persenmekanis) * parseFloat(ttlprod))/100;
	if(isNaN(tonmekanis2)==true){tonmekanis2=0;}
	rpmekanis2 = parseFloat(tonmekanis2) * parseFloat(hargamekanis);
	if(isNaN(rpmekanis2)==true){rpmekanis2=0;}
	
	gtangkut = parseFloat(ttlupahpremi) + parseFloat(rpttlkont) + parseFloat(ttlrpangkut) + parseFloat(rpalong2) + parseFloat(rpmekanis2);
	if(isNaN(gtangkut)==true){gtangkut=0;}
	gtrpkgangkut = parseFloat(gtangkut)/parseFloat(ttlprod);
	if(isNaN(gtrpkgangkut)==true){gtrpkgangkut=0;}
	
	//Along Along
	document.getElementById('tonalong'+row).innerHTML=numberFormat(tonalong2,0);	
	document.getElementById('rpalong'+row).innerHTML=numberFormat(rpalong2,0);	
	
	//Along Along
	document.getElementById('tonmekanis'+row).innerHTML=numberFormat(tonmekanis2,0);	
	document.getElementById('rpmekanis'+row).innerHTML=numberFormat(rpmekanis2,0);	
	
	
	document.getElementById('kblangkut'+row).value=numberFormat(kbl,0);	
	document.getElementById('khtangkut'+row).value=numberFormat(kht,0);	
	document.getElementById('khlangkut'+row).value=numberFormat(khl,0);	
	
	document.getElementById('tonangkut'+row).innerHTML=numberFormat(kgsdr,0);	
	document.getElementById('trippksangkut'+row).innerHTML=numberFormat(trippks,0);	
	document.getElementById('kmangkut'+row).innerHTML=numberFormat(ttlkm,0);	
	// document.getElementById('persenkontangkut'+row).innerHTML=numberFormat(persenkont,0);	
	// document.getElementById('tonkontangkut'+row).innerHTML=numberFormat(kgkont,0);	
	document.getElementById('ttlrpkontangkut'+row).innerHTML=numberFormat(rpttlkont,0);	
	document.getElementById('ttlhkangkut'+row).innerHTML=numberFormat(ttlhk,0);	
	// document.getElementById('ttlkgbssangkut'+row).innerHTML=numberFormat(ttlkgbasis,0);	
	// document.getElementById('kgpremiangkut'+row).innerHTML=numberFormat(kgpremi,0);	
	// document.getElementById('ttlrppremiangkut'+row).innerHTML=numberFormat(ttlpremi,0);	
	
	document.getElementById('ttlupahpremiangkut'+row).innerHTML=numberFormat(ttlupahpremi,0);	
	document.getElementById('rpkgupahpremiangkut'+row).innerHTML=numberFormat(rpkgupahpremiangkut,0);	
	document.getElementById('gtangkut'+row).innerHTML=numberFormat(gtangkut,0);	
	document.getElementById('gtrpkgangkut'+row).innerHTML=numberFormat(gtrpkgangkut,0);	
	
}

function proporsihkangkut(){
	jlhbrs=document.getElementById('jlhbrsangkut').value;
	persenkbl=document.getElementById('persenkblangkut').value;
	persenkht=document.getElementById('persenkhtangkut').value;
	persenkhl=document.getElementById('persenkhlangkut').value;
	
	rpperhkkbl=document.getElementById('rpperhkkblangkut').value;
	rpperhkkht=document.getElementById('rpperhkkhtangkut').value;
	rpperhkkhl=document.getElementById('rpperhkkhlangkut').value;	

	if(jlhbrs==''){jlhbrs=0;}
	if(persenkbl==''){persenkbl=0;}
	if(persenkht==''){persenkht=0;}
	if(persenkhl==''){persenkhl=0;}
	if(rpperhkkbl==''){rpperhkkbl=0;}
	if(rpperhkkht==''){rpperhkkht=0;}
	if(rpperhkkhl==''){rpperhkkhl=0;}


	ttlpersen = parseFloat(persenkbl)+parseFloat(persenkht)+parseFloat(persenkhl);
	if(jlhbrs>0){
		for(i=1;i<=jlhbrs;i++){
			ttlhkangkut = document.getElementById('ttlhkangkut'+i).innerHTML;	
			// ttlkgbss = document.getElementById('ttlkgbssangkut'+i).innerHTML;	
			// ttlkgbss= remove_comma_var(ttlkgbss);
			ttlhkangkut= remove_comma_var(ttlhkangkut);
			hkkbl = (parseFloat(persenkbl)/ttlpersen) * ttlhkangkut;
			hkkht = (parseFloat(persenkht)/ttlpersen) * ttlhkangkut;
			hkkhl = (parseFloat(persenkhl)/ttlpersen) * ttlhkangkut;
			
			if(isNaN(hkkbl)==true){hkkbl=0;}
			if(isNaN(hkkht)==true){hkkht=0;}
			if(isNaN(hkkhl)==true){hkkhl=0;}
			document.getElementById('kblangkut'+i).value=numberFormat(hkkbl,1);
			document.getElementById('khtangkut'+i).value=numberFormat(hkkht,1);
			document.getElementById('khlangkut'+i).value=numberFormat(hkkhl,1);
			
			
			ttlrp = (hkkbl*parseFloat(rpperhkkbl))+(hkkht*parseFloat(rpperhkkht))+(hkkhl*parseFloat(rpperhkkhl));
			if(isNaN(ttlrp)==true){ttlrp=0;}
			document.getElementById('ttlrphkangkut'+i).innerHTML=numberFormat(ttlrp);
			
			// rpperkghk = ttlrp / parseFloat(ttlkgbss);
			rpperkghk = ttlrp;
			if(isNaN(rpperkghk)==true){rpperkghk=0;}
			document.getElementById('rpperhkhkangkut'+i).innerHTML=numberFormat(rpperkghk,2);
			
			getkalkulasiangkut(i);
		}
	}
}


max = 0
sekarang = 1;
function simpanallangkut(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Simpan semua ???")) {
		max = maxRow;
		savedetailangkut(1, maxRow);
	}
}
function savedetailangkut(currRow, maxRow) {
	notransaksi  = document.getElementById('notransaksi').value;
	kodeorg      = document.getElementById('kodeorg').value;
    periode      = document.getElementById('periode').value;	
	
    tipetransaksi= document.getElementById('tipetransaksiangkut').value;
	divisi       = document.getElementById('divisiangkut').value;

	tt           = document.getElementById('ttangkut'+currRow).innerHTML;
	kg           = document.getElementById('ttlprodangkut'+currRow).innerHTML;
	jarakpks     = document.getElementById('jarakpksangkut'+currRow).value;
	persensendiri= document.getElementById('persensdrangkut'+currRow).value;
	kapasitas    = document.getElementById('kapsdrangkut'+currRow).value;
	trippks      = document.getElementById('trippksangkut'+currRow).innerHTML;
	km           = document.getElementById('kmangkut'+currRow).innerHTML;
	kgsendiri    = document.getElementById('tonangkut'+currRow).innerHTML;
	
	ttlrpsendiri = document.getElementById('ttlrpsdrangkut'+currRow).innerHTML;
	kgkont       = document.getElementById('tonkontangkut'+currRow).value;
	ttlrpkont    = document.getElementById('ttlrpkontangkut'+currRow).innerHTML;
	outputkgperhk= document.getElementById('outputkghkangkut'+currRow).value;
	// norma        = document.getElementById('basiskghkangkut'+currRow).value;
	kbl          = document.getElementById('kblangkut'+currRow).value;
	kht          = document.getElementById('khtangkut'+currRow).value;
	khl          = document.getElementById('khlangkut'+currRow).value;
	// ttlkgbasis   = document.getElementById('ttlkgbssangkut'+currRow).innerHTML;
	upah         = document.getElementById('ttlrphkangkut'+currRow).innerHTML;
	kgpremi      = document.getElementById('kgpremiangkut'+currRow).value;
	rpperkgpremi =document.getElementById('rpkgpremiangkut'+currRow).value;
	premi        = document.getElementById('ttlrppremiangkut'+currRow).value;
	hargaborongan= document.getElementById('rpkgkontangkut'+currRow).value;
	
	persenkontangkut= document.getElementById('persenkontangkut'+currRow).value;
	tonkontangkut= document.getElementById('tonkontangkut'+currRow).value;
	hargakontangkut= document.getElementById('rpkgkontangkut'+currRow).value;
	rpkontangkut= document.getElementById('ttlrpkontangkut'+currRow).innerHTML;

	persenalong= document.getElementById('persenalong'+currRow).value;
	tonalong= document.getElementById('tonalong'+currRow).innerHTML;
	hargalong= document.getElementById('hargalong'+currRow).value;
	rpalong= document.getElementById('rpalong'+currRow).innerHTML;
	
	persenmekanis= document.getElementById('persenmekanis'+currRow).value;
	tonmekanis= document.getElementById('tonmekanis'+currRow).innerHTML;
	hargamekanis= document.getElementById('hargamekanis'+currRow).value;
	rpmekanis= document.getElementById('rpmekanis'+currRow).innerHTML;
	
	method = 'simpanallangkut';
	param = "";
	param += 'notransaksi=' + notransaksi;
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&tipetransaksi=' + tipetransaksi + '&divisi=' + divisi;
	param += "&tt=" + tt;
	param += "&kg=" + kg;
	param += '&jarakpks=' + jarakpks;
	param += '&persensendiri=' + persensendiri;
	param += '&kapasitas=' + kapasitas;
	param += '&trippks=' + trippks;
	param += '&km=' + km;
	param += '&kgsendiri=' + kgsendiri;
	param += '&ttlrpsendiri=' + ttlrpsendiri;
	param += '&kgkont=' + kgkont;
	param += '&ttlrpkont=' + ttlrpkont;
	param += '&outputkgperhk=' + outputkgperhk;
	// param += '&norma=' + norma;
	param += '&kbl=' + kbl;
	param += '&kht=' + kht;
	param += '&khl=' + khl;
	// param += '&ttlkgbasis=' + ttlkgbasis;
	param += '&upah=' + upah;
	param += '&kgpremi=' + kgpremi;
	param += '&rpkgpremi=' + rpperkgpremi;
	param += '&premi=' + premi;
	param += '&hargaborongan=' + hargaborongan;
	param += '&persenkontangkut=' + persenkontangkut;
	param += '&tonkontangkut=' + tonkontangkut;
	param += '&hargakontangkut=' + hargakontangkut;
	param += '&rpkontangkut=' + rpkontangkut;
	param += '&persenalong=' + persenalong;
	param += '&tonalong=' + tonalong;
	param += '&hargalong=' + hargalong;
	param += '&rpalong=' + rpalong;
	param += '&persenmekanis=' + persenmekanis;
	param += '&tonmekanis=' + tonmekanis;
	param += '&hargamekanis=' + hargamekanis;
	param += '&rpmekanis=' + rpmekanis;



	param += '&method=' + method;
	tujuan = 'kebun_slave_rkbx.php';
	post_response_text(tujuan, param, respog);
	
	document.getElementById('rowangkut'+currRow).style.backgroundColor='cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('rowangkut' + currRow).style.backgroundColor = 'red';
				} else {
						loaddatadetailangkut();
					if (currRow != undefined) {
						document.getElementById('rowangkut' + currRow).style.backgroundColor = '';
					}
					currRow += 1;
					sekarang = currRow;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						loaddatadetailangkut();
					} else {
						savedetailangkut(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loaddatadetailangkut(){
    notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksiangkut').value;
    periode= document.getElementById('periode').value;	
	divisi= document.getElementById('divisiangkut').value;

    param = 'method=loaddatadetailangkut';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('loaddatadetailangkut').innerHTML = con.responseText;
					loaddatadetailumm();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// === UMUM ===

function inputdetailumm(){
    notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    periode= document.getElementById('periode').value;	
	
    tipetransaksi= document.getElementById('tipetransaksiumm').value;
	divisi= document.getElementById('divisiumm').value;
	
	rpperhkkbl=document.getElementById('rpperhkkblumm').value;
	rpperhkkht=document.getElementById('rpperhkkblumm').value;
	rpperhkkhl=document.getElementById('rpperhkkblumm').value;	
	
	if(rpperhkkbl=='' || rpperhkkht=='' || rpperhkkhl==''){
		//alert("Rupiah per HK per Tipe Karyawan masih kosong / Blank !!!"); return;
	}
	if(divisi==''){
		alert("Divisi Masih Kosong !!!"); return;
	}
    param = 'method=inputdetailumm';
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&notransaksi=' + notransaksi+'&divisi=' + divisi;
    param += '&tipetransaksi=' + tipetransaksi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('inputdetailumm').innerHTML = con.responseText;
					getSelect2();
					loaddatadetailumm();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function gettotalhkumm(){
	kbl= document.getElementById('kblumm').value;
    kht= document.getElementById('khtumm').value;
    khl= document.getElementById('khlumm').value;
    rpperhkkbl= document.getElementById('rpperhkkblumm').value;
    rpperhkkht= document.getElementById('rpperhkkhtumm').value;
    rpperhkkhl= document.getElementById('rpperhkkhlumm').value;
    jam= document.getElementById('jamumm').value;
    rpjam= document.getElementById('rpjamumm').value;
    premi= document.getElementById('rplpremiumm').value;
	
	kbl= remove_comma_var(kbl);
    kht= remove_comma_var(kht);
    khl= remove_comma_var(khl);
    rpperhkkbl= remove_comma_var(rpperhkkbl);
    rpperhkkht= remove_comma_var(rpperhkkht);
    rpperhkkhl= remove_comma_var(rpperhkkhl);
    jam= remove_comma_var(jam);
    rpjam= remove_comma_var(rpjam);
    premi= remove_comma_var(premi);
	
    
	if(rpperhkkbl=='' || rpperhkkht=='' || rpperhkkhl==''){
		alert("Rupiah per HK belum ada !!!"); return;
	}
	
	if(kbl==''){kbl=0;}
	if(kht==''){kht=0;}
	if(khl==''){khl=0;}
	
	totalhk = parseFloat(kbl)+parseFloat(kht)+parseFloat(khl);
	totalrphk = (parseFloat(kbl)*parseFloat(rpperhkkbl))+(parseFloat(kht)*parseFloat(rpperhkkht))+(parseFloat(khl)*parseFloat(rpperhkkhl));
	lembur = parseFloat(jam)*parseFloat(rpjam);
	if(isNaN(lembur)==true){lembur=0;}
	
	if(totalrphk==''){totalrphk=0;}
	if(premi==''){premi=0;}
	
	gt = totalrphk+parseFloat(lembur)+parseFloat(premi);
	document.getElementById('ttlhkumm').value=totalhk;
	document.getElementById('upahumm').value=numberFormat(totalrphk);
	document.getElementById('rplbrumm').value=numberFormat(lembur);
	document.getElementById('ttlrpumm').innerHTML=numberFormat(gt);
}

function simpandetailumm(clear){
	notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksiumm').value;
    periode= document.getElementById('periode').value;	
	divisi= document.getElementById('divisiumm').value;
	
	kegiatan= document.getElementById('jabatanumm').value;
	kbl= document.getElementById('kblumm').value;
	kht= document.getElementById('khtumm').value;
	khl= document.getElementById('khlumm').value;
	upah= document.getElementById('upahumm').value;
	
	jam= document.getElementById('jamumm').value;
	lembur= document.getElementById('rplbrumm').value;
	premi= document.getElementById('rplpremiumm').value;
	
	kodebarang= document.getElementById('kodebarangumm').value;
	jlhmat= document.getElementById('jlhmatumm').value;

    param = 'method=simpandetailumm';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    param += '&kegiatan=' + kegiatan;
    param += '&kodebarang=' + kodebarang+'&jlhmat=' + jlhmat;
    param += '&kbl=' + kbl+'&kht=' + kht+'&khl=' + khl+'&upah=' + upah+'&premi=' + premi;
    param += '&lembur=' + lembur+'&jam=' + jam;
	
	if((upah=='' || upah==0) && (premi=='' || premi==0)&& (lembur=='' || lembur==0)){
		alert("Upah atau Premi atau Lembur masih kosong !!!"); return;
	}
	if(kodebarang!=''  && (jlhmat==0 || jlhmat=='')){
		alert("Jumlah Material Masih Kosong !!!"); return;
	}
	
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					if(clear=='clear'){
						cleardetailallumm();
					}
                    daftarmaterialumm();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function daftarmaterialumm(){
	notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksiumm').value;
    periode= document.getElementById('periode').value;	
	divisi= document.getElementById('divisiumm').value;
	
	kegiatan= document.getElementById('jabatanumm').value;
	
    param = 'method=daftarmaterialumm';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    param += '&kegiatan=' + kegiatan;
    
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('listmaterialumm').innerHTML = con.responseText;
					loaddatadetailumm();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loaddatadetailumm(){
    notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksiumm').value;
    periode= document.getElementById('periode').value;	
	divisi= document.getElementById('divisiumm').value;

    param = 'method=loaddatadetailumm';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('loaddatadetailumm').innerHTML = con.responseText;
					// loaddatadetailsupport();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function cleardetailallumm(){
	document.getElementById('jabatanumm').value='';
	document.getElementById('kblumm').value='';
	document.getElementById('khtumm').value='';
	document.getElementById('khlumm').value='';
	document.getElementById('ttlhkumm').value='';
	document.getElementById('upahumm').value='';
	document.getElementById('jamumm').value='';
	document.getElementById('rpjamumm').value='';
	document.getElementById('rplbrumm').value='';
	document.getElementById('rplpremiumm').value='';
	document.getElementById('kodebarangumm').value='';
	document.getElementById('satmatumm').value='';
	document.getElementById('jlhmatumm').value='';
	document.getElementById('ttlrpumm').innerHTML='';
	document.getElementById('listmaterialumm').innerHTML='';
}


function deletedetailumm(notransaksi,tipetransaksi,periode,kodeorg,divisi,kegiatan,blok){
    param='method=deletedetailumm';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    param += '&kegiatan=' + kegiatan+'&blok=' + blok;
    
    tujuan='kebun_slave_rkbx.php';
	if(confirm('Anda yakin ???')){
		post_response_text(tujuan, param, respog);	
	}
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
				   loaddatadetailumm();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}

// SUPPORT
function inputdetailsup(){
    notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    periode= document.getElementById('periode').value;	
	
    tipetransaksi= document.getElementById('tipetransaksisup').value;
	divisi= document.getElementById('divisisup').value;
	
	rpperhkkbl=document.getElementById('rpperhkkblsup').value;
	rpperhkkht=document.getElementById('rpperhkkblsup').value;
	rpperhkkhl=document.getElementById('rpperhkkblsup').value;	
	
	if(rpperhkkbl=='' || rpperhkkht=='' || rpperhkkhl==''){
		//alert("Rupiah per HK per Tipe Karyawan masih kosong / Blank !!!"); return;
	}
	if(divisi==''){
		alert("Divisi Masih Kosong!"); return;
	}
    param = 'method=inputdetailsup';
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&notransaksi=' + notransaksi+'&divisi=' + divisi;
    param += '&tipetransaksi=' + tipetransaksi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('inputdetailsup').innerHTML = con.responseText; getSelect2();
					loaddatadetailangkut();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getjabsup(){
    dept= document.getElementById('dept').value;
    kodeorg= document.getElementById('kodeorg').value;

    param = 'method=getjabsup';
    param += '&dept=' + dept;
    param += '&kodeorg=' + kodeorg;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('jabatansup').innerHTML = con.responseText;
					listsupport();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getttltkhk(){
    dept= document.getElementById('dept').value;
    kodeorg= document.getElementById('kodeorg').value;
    jabatan= document.getElementById('jabatansup').value;
    periode= document.getElementById('periode').value;
    compgaji= document.getElementById('compgaji').value;
	
	if(dept=='' || jabatan==''){
		alert("Silahkan pilih Departement dan Jabatan terlebih dahulu !!!"); 
		document.getElementById('compgaji').value='';
		return;
		
	}
    param = 'method=getttltkhk';
    param += '&dept=' + dept;
    param += '&kodeorg=' + kodeorg;
    param += '&jabatan=' + jabatan;
    param += '&periode=' + periode;
    param += '&compgaji=' + compgaji;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					data = con.responseText.split("####");
                    document.getElementById('tkkbl').value = numberFormat(data[0],0);
                    document.getElementById('tkkht').value = numberFormat(data[1],0);
                    document.getElementById('tkkhl').value = numberFormat(data[2],0);
                    document.getElementById('rpkbl').value = numberFormat(data[3],0);
                    document.getElementById('rpkht').value = numberFormat(data[4],0);
                    document.getElementById('rpkhl').value = numberFormat(data[5],0);
					if(compgaji==1){
						document.getElementById('hkkbl').value = numberFormat(data[0]*25,0);
						document.getElementById('hkkht').value = numberFormat(data[1]*25,0);
						document.getElementById('hkkhl').value = numberFormat(data[2]*25,0);						
					}else{
						document.getElementById('hkkbl').value =0;
						document.getElementById('hkkht').value =0;
						document.getElementById('hkkhl').value =0;						
					}
					gettotalsup();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function gettotalsup(){
	tkkbl = document.getElementById('tkkbl').value;
	tkkht = document.getElementById('tkkht').value;
	tkkhl = document.getElementById('tkkhl').value;
	rpkbl = document.getElementById('rpkbl').value;
	rpkht = document.getElementById('rpkht').value;
	rpkhl = document.getElementById('rpkhl').value;
	hkkbl = document.getElementById('hkkbl').value;
	hkkht = document.getElementById('hkkht').value;
	hkkhl = document.getElementById('hkkhl').value;
	
	tkkbl= remove_comma_var(tkkbl);
	tkkht= remove_comma_var(tkkht);
	tkkhl= remove_comma_var(tkkhl);
	rpkbl= remove_comma_var(rpkbl);
	rpkht= remove_comma_var(rpkht);
	rpkhl= remove_comma_var(rpkhl);
	hkkbl= remove_comma_var(hkkbl);
	hkkht= remove_comma_var(hkkht);
	hkkhl= remove_comma_var(hkkhl);
	
	ttltk = parseFloat(tkkbl)+parseFloat(tkkht)+parseFloat(tkkhl);
	ttlhk = parseFloat(hkkbl)+parseFloat(hkkht)+parseFloat(hkkhl);
	ttlrp = parseFloat(rpkbl)+parseFloat(rpkht)+parseFloat(rpkhl);
	document.getElementById('ttltksup').value=numberFormat(ttltk,0);
	document.getElementById('ttlhksup').value=numberFormat(ttlhk,0);
	document.getElementById('ttlrpsup').value=numberFormat(ttlrp,0);
}

function simpandetailsup(){
	notransaksi  = document.getElementById('notransaksi').value;
	kodeorg      = document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksisup').value;
    periode      = document.getElementById('periode').value;	
	divisi       = document.getElementById('divisisup').value;
	
	dept         = document.getElementById('dept').value;
	jabatan      = document.getElementById('jabatansup').value;
	compgaji     = document.getElementById('compgaji').value;
	
	tkkbl        = document.getElementById('tkkbl').value;
	tkkht        = document.getElementById('tkkht').value;
	tkkhl        = document.getElementById('tkkhl').value;
	rpkbl        = document.getElementById('rpkbl').value;
	rpkht        = document.getElementById('rpkht').value;
	rpkhl        = document.getElementById('rpkhl').value;
	kbl          = document.getElementById('hkkbl').value;
	kht          = document.getElementById('hkkht').value;
	khl          = document.getElementById('hkkhl').value;
	ket          = document.getElementById('keterangan').value;
	

    param = 'method=simpandetailsup';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    param += '&dept=' + dept;
    param += '&ket=' + ket;
    param += '&jabatan=' + jabatan+'&compgaji=' + compgaji;
    param += '&kbl=' + kbl+'&kht=' + kht+'&khl=' + khl;
    param += '&tkkbl=' + tkkbl+'&tkkht=' + tkkht+'&tkkhl=' + tkkhl;
    param += '&rpkbl=' + rpkbl+'&rpkht=' + rpkht+'&rpkhl=' + rpkhl;
	
	if((rpkbl=='' || rpkbl==0) && (rpkht=='' || rpkht==0)&& (rpkhl=='' || rpkhl==0)){
		alert("Nilai Rupiah masih kosong !!!"); return;
	}
	
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    listsupport();
					cleardetailallsup();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function listsupport(){
	notransaksi  = document.getElementById('notransaksi').value;
	kodeorg      = document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksisup').value;
    periode      = document.getElementById('periode').value;	
	divisi       = document.getElementById('divisisup').value;
	
	dept         = document.getElementById('dept').value;
	jabatan      = document.getElementById('jabatansup').value;
	compgaji     = document.getElementById('compgaji').value;
	
    param = 'method=listsupport';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    param += '&dept=' + dept;
    param += '&jabatan=' + jabatan+'&compgaji=' + compgaji;
    
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('listsupport').innerHTML = con.responseText;
					document.getElementById('compgaji').selectedIndex=0;
					loaddatadetailsupport();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletedetailsupport(notransaksi,tipetransaksi,periode,kodeorg,divisi,dept,jabatan,compgaji){
    param = 'method=deletedetailsupport';
    param += '&notransaksi=' + notransaksi;
    param += '&tipetransaksi=' + tipetransaksi;
    param += '&periode=' + periode;
    param += '&kodeorg=' + kodeorg;
    param += '&divisi=' + divisi;
    param += '&dept=' + dept;
    param += '&compgaji=' + compgaji;
    param += '&jabatan=' + jabatan;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					listsupport();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadetailsupport(){
    notransaksi= document.getElementById('notransaksi').value;
	kodeorg= document.getElementById('kodeorg').value;
    tipetransaksi= document.getElementById('tipetransaksisup').value;
    periode= document.getElementById('periode').value;	
	divisi= document.getElementById('divisisup').value;

    param = 'method=loaddatadetailsupport';
    param += '&notransaksi=' + notransaksi;
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi+'&divisi=' + divisi;
    
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('loaddatadetailsup').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function cleardetailallsup(){
	document.getElementById('tkkbl').value='0';
	document.getElementById('tkkht').value='0';
	document.getElementById('tkkhl').value='0';
	document.getElementById('rpkbl').value='0';
	document.getElementById('rpkht').value='0';
	document.getElementById('rpkhl').value='0';
	document.getElementById('hkkbl').value='0';
	document.getElementById('hkkht').value='0';
	document.getElementById('hkkhl').value='0';
	document.getElementById('ttltksup').value='0';
	document.getElementById('ttlhksup').value='0';
	document.getElementById('ttlrpsup').value='0';
	document.getElementById('keterangan').value='';
}

//==============================================================================//

function inputdetailmaterial(notransaksi){
	periode=document.getElementById('periode').value;
    notransaksi=document.getElementById('notransaksi').value;
    kodeorg=document.getElementById('kodeorg').value;
    
    param = 'method=inputdetailmaterial';
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('inputdetailmaterial').innerHTML = con.responseText;
					loaddatadetailmaterial(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function savematerial(currRow){
	notransaksi=document.getElementById('notransaksi').value;
	
	kegiatan=document.getElementById('kegiatanmat'+currRow).innerHTML;
	blok=document.getElementById('blokmat'+currRow).innerHTML;
	kodegudang=document.getElementById('kodegudang'+currRow).innerHTML;
	kodebarang=document.getElementById('kodemat'+currRow).value;
	qtymat=document.getElementById('qtymat'+currRow).value;
	prestasi=document.getElementById('pres'+currRow).innerHTML;

	param = 'method=insertmaterial';
	param += '&notransaksi='+notransaksi;
	param += '&kegiatan='+kegiatan;
	param += '&blok='+blok;
	param += '&kodebarang='+kodebarang;
	param += '&qtymat='+qtymat;
	param += '&kodegudang='+kodegudang;
	param += '&prestasi='+prestasi;
	
	tujuan='kebun_slave_rkbx.php';
	post_response_text(tujuan, param, respog);
    
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					document.getElementById('rowmat_' + currRow).style.backgroundColor = 'red';
                } else {
					document.getElementById('rowmat_' + currRow).style.backgroundColor='cyan';
					loaddatadetailmaterial(notransaksi);
					clearmaterial(currRow);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}

function clearmaterial(currRow){
	document.getElementById('kodemat'+currRow).value='';
	document.getElementById('namamat'+currRow).value='';
	document.getElementById('satmat'+currRow).value='';
	document.getElementById('qtymat'+currRow).value='';
}

function delmaterial(notransaksi,kegiatan,blok,kodebarang){

	param = 'method=delmaterial';
	param += '&notransaksi='+notransaksi;
	param += '&kegiatan='+kegiatan;
	param += '&blok='+blok;
	param += '&kodebarang='+kodebarang;
	
	tujuan='kebun_slave_rkbx.php';
	post_response_text(tujuan, param, respog);
    
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alert('Delete');
					loaddatadetailmaterial(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadetailmaterial(notransaksi){
	periode=document.getElementById('periode').value;
    notransaksi=document.getElementById('notransaksi').value;
    kodeorg=document.getElementById('kodeorg').value;
    
    param = 'method=loaddatadetailmaterial';
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('loaddatadetailmaterial').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function searchmat(baris,title,ev){
	kdgdg = document.getElementById('kodegudang'+baris).innerHTML;
	kgtn = document.getElementById('kegiatanmat'+baris).innerHTML;
	content= "<div style='width:100%;'>";
	content+="<fieldset style=width:95%>Search : <input type=text id=txtnamabarang onkeypress='key=getKey(event);if(key==13){goCariBarang()}' class=myinputtext size=25 maxlength=35><button class=mybutton onclick=goCariBarang()>Search</button> </div></fieldset>";
	content+="<input id=kodegudang value="+kdgdg+" style=display:none>";
	content+="<input id=kegiatansch value="+kgtn+" style=display:none>";
	content+="<input id=baris value="+baris+" style=display:none>";
	content+="<fieldset><legend><i>Result</i></legend><div id=containercari style='overflow:auto;max-height:317px;'></div></fieldset>";
    width='auto';
	height='auto';
	showDialog2(title,content,width,height,ev);
	
	var dialog = document.getElementById('dynamic2');
	clientWidth = document.getElementById("dynamic2").clientWidth;
	clientHeight = document.getElementById("dynamic2").clientHeight;
	pos = new Array();
	pos = getMouseP(ev);

	dialog.style.top = pos[1]+'px';
	dialog.style.left = (pos[0]-clientWidth)+'px';
}


function goCariBarang(){
	kodegudang = trim(document.getElementById('kodegudang').value);
	kegiatan = trim(document.getElementById('kegiatansch').value);
	txtcari = trim(document.getElementById('txtnamabarang').value);
	param = 'txtcari='+txtcari+'&method=caribarang&kodegudang='+kodegudang+'&kegiatan='+kegiatan;
	tujuan = 'kebun_slave_rkbx.php';
	post_response_text(tujuan, param, respog);
			
	function respog(){
		if (con.readyState == 4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}else {
					
					document.getElementById('containercari').innerHTML=con.responseText;
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadField(kode,nama,sat){
	baris = document.getElementById('baris').value;
	document.getElementById('kodemat'+baris).value=kode;
	document.getElementById('namamat'+baris).value=nama;
	document.getElementById('satmat'+baris).value=sat;
	closeDialog2();
}


function add_new_data(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    cancel();  
}

function del(notransaksi,numrow){
	pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
	
    param='method=delete'+'&notransaksi='+notransaksi;
    tujuan='kebun_slave_rkbx.php';
    if(confirm('Anda yakin ???')){
        post_response_text(tujuan, param, respog);	
    }
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
				  loaddata(paged);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}


function displayList(){
    document.getElementById('notransaksisch').value='';
    document.getElementById('divsch').value='';
    document.getElementById('postingsrc').value='';
    document.getElementById('periodesch').value='';
    document.getElementById('mode').value='baru';
    document.getElementById('listData').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('uploadpemel').style.display = 'none';
	
	document.getElementById('header_trans').style.display='block';
    //document.getElementById('hidebtn').style.display='block';
    //document.getElementById('unhidebtn').style.display='none';
    loaddata(0);
}


function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function loaddata(page){
    notransaksisch=document.getElementById('notransaksisch').value;
    admin=document.getElementById('admin').value;
    divsch=document.getElementById('divsch').value;
    postingsrc=document.getElementById('postingsrc').value;
    periodesch=document.getElementById('periodesch').value;
	param = 'method=loaddata&page=' + page;
    if (divsch != '') {
        param += '&divsch=' + divsch;
    }
    if (notransaksisch != '') {
        param += '&notransaksisch=' + notransaksisch;
    }
	if (postingsrc != '') {
        param += '&postingsrc=' + postingsrc;
    }
	if (periodesch != '') {
        param += '&periodesch=' + periodesch;
    }
 
	param += '&admin=' + admin;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function cancel(){
    document.getElementById('detail').style.display = 'none';
    document.getElementById('tomboldetail').disabled=false;
    document.getElementById('periode').disabled=false;
    document.getElementById('periode').value='';
	document.getElementById('kodeorg').disabled=false;
    document.getElementById('kodeorg').value='';
    document.getElementById('notransaksi').value='';
    document.getElementById('mode').value='baru';
}

function numberFormat(number,digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	var components = (parseFloat(number).toFixed(digit)).split(".");
	components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	return components.join(".");
}


function form(){
    width = '720';
    height = '';
    //nopp=document.getElementById('nopp_'+id).value;
    content = "<fieldset><div id=containerd align=center style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog5(title, content, width, height, ev); 
}

function html(notransaksi,kodeorg, periode){
    form();
    param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode+ '&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('containerd').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function excel(ev,tujuan){
    unitexp = document.getElementById('unitexp').value;
    perexp = document.getElementById('perexp').value;
	if(unitexp==''||perexp==''){
		alert('Lengkapi unit dan periode.');
		return;
	}
    judul='Report Ms.Excel';	
    param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
    printFile(param,tujuan,judul,ev);	
}


function deletehead(notransaksi){
	param = 'method=deletehead&notransaksi=' + notransaksi;
    
    tujuan = 'kebun_slave_rkbx.php';
	if (confirm("Anda Yakin ???")) {
		post_response_text(tujuan, param, respog);	
	}
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function uploadpemel(){
	document.getElementById('listData').style.display='none';
    document.getElementById('detail').style.display='none';
    document.getElementById('uploadpemel').style.display='block';
	
	periode=document.getElementById('periode').value;
    notransaksi=document.getElementById('notransaksi').value;
    kodeorg=document.getElementById('kodeorg').value;
    
    param = 'method=uploadpemel';
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					document.getElementById('uploadpemel').style.display = '';
                    document.getElementById('viewuploadpemel').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function uploadpemelmaterial(){
	document.getElementById('listData').style.display='none';
    document.getElementById('detail').style.display='none';
    document.getElementById('uploadpemelmaterial').style.display='block';
	
	periode=document.getElementById('periode').value;
    notransaksi=document.getElementById('notransaksi').value;
    kodeorg=document.getElementById('kodeorg').value;
    
    param = 'method=uploadpemelmaterial';
    param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_rkbx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
					document.getElementById('uploadpemelmaterial').style.display = '';
                    document.getElementById('viewuploadpemelmaterial').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function submitFile(){
    if(confirm('Are you sure..?')){
    	;
    }
}


function uploaddatapemel() {
	let file = document.getElementById('filex').files[0];
	method = document.getElementById('methodpemel').value;
	unit = document.getElementById('kodeorgupload').value;
	periode = document.getElementById('periodeupload').value;
	notransaksi = document.getElementById('notransaksiupload').value;
	
	let formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue('filex'));
    formdata.append("method", method);
    formdata.append("unit", unit);
    formdata.append("periode", periode);
    formdata.append("notransaksi", notransaksi);

    if(unit == ''){
		alert("Warning : Harap Kode Organisasi diisikan.");
		return false;
    }else if (getValue('filex') == "") {
		alert("Warning : Tidak ada data yang di upload !");
		return false;
	}

	busy_on();
    let con = createXMLHttpRequest();
    con.open("POST", "kebun_slave_rkbx.php?method="+method, true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);

    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    alert("Data berhasil di simpan.");
					kembali();
					loaddatadetail(notransaksi);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function uploaddatapemelmaterial() {
	let file = document.getElementById('filexm').files[0];
	method = document.getElementById('methodpemelmaterial').value;
	unit = document.getElementById('kodeorgupload').value;
	periode = document.getElementById('periodeupload').value;
	notransaksi = document.getElementById('notransaksiupload').value;
	
	let formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue('filexm'));
    formdata.append("method", method);
    formdata.append("unit", unit);
    formdata.append("periode", periode);
    formdata.append("notransaksi", notransaksi);

    if(unit == ''){
		alert("Warning : Harap Kode Organisasi diisikan.");
		return false;
    }else if (getValue('filexm') == "") {
		alert("Warning : Tidak ada data yang di upload !");
		return false;
	}

	busy_on();
    let con = createXMLHttpRequest();
    con.open("POST", "kebun_slave_rkbx.php?method="+method, true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);

    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					alert("Data berhasil di simpan.");
					kembalipemelmaterial();
					loaddatadetail(notransaksi);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// max = 0
// sekarang = 1;
// function uploaddataall(maxRow) {
// 	if (maxRow == '' || maxRow == 0) {
// 		alert('Data tidak ditemukan, proses dibatalkan !');
// 		return;
// 	}
// 	if (confirm("Simpan semua ???")) {
// 		max = maxRow;
// 		uploaddata(1, maxRow);
// 	}
// }
// function uploaddata(currRow, maxRow) {
//     tipetransaksi= 'PEMEL';
// 	notransaksi= document.getElementById('notransup').value;
// 	kodeorg= document.getElementById('kodeorgup').value;
//     periode= document.getElementById('periodeup').value;
	
// 	kegiatan= document.getElementById('tdkegiatan_'+currRow).innerHTML;
// 	blok= document.getElementById('tdblok_'+currRow).innerHTML;
// 	luas= document.getElementById('tdluas_'+currRow).innerHTML;
// 	kbl= document.getElementById('tdkbl_'+currRow).innerHTML;
// 	kht= document.getElementById('tdkht_'+currRow).innerHTML;
// 	khl= document.getElementById('tdkhl_'+currRow).innerHTML;
// 	upah= document.getElementById('tdttlrphk_'+currRow).innerHTML;
// 	premi= document.getElementById('tdpremi_'+currRow).innerHTML;
// 	luasbor= document.getElementById('tdluabor_'+currRow).innerHTML;
// 	rupiahbor= document.getElementById('tdrupiahbor_'+currRow).innerHTML;
	
// 	kodebarang1= document.getElementById('tdmati_'+currRow).innerHTML;
// 	jlhmat1= document.getElementById('tdjlhmati_'+currRow).innerHTML;
// 	rpmat1= document.getElementById('tdrpmati_'+currRow).innerHTML;
	
// 	kodebarang2= document.getElementById('tdmatii_'+currRow).innerHTML;
// 	jlhmat2= document.getElementById('tdjlhmatii_'+currRow).innerHTML;
// 	rpmat2= document.getElementById('tdrpmatii_'+currRow).innerHTML;
	
// 	kodebarang3= document.getElementById('tdmatiii_'+currRow).innerHTML;
// 	jlhmat3= document.getElementById('tdjlhmatiii_'+currRow).innerHTML;
// 	rpmat3= document.getElementById('tdrpmatiii_'+currRow).innerHTML;
	
// 	kodebarang4= document.getElementById('tdmativ_'+currRow).innerHTML;
// 	jlhmat4= document.getElementById('tdjlhmativ_'+currRow).innerHTML;
// 	rpmat4= document.getElementById('tdrpmativ_'+currRow).innerHTML;
// 	ket= document.getElementById('ket'+currRow).innerHTML;


//     param = 'method=uploaddata';
//     param += '&notransaksi=' + notransaksi;
//     param += '&ket=' + ket;
//     param += '&kodeorg=' + kodeorg+'&periode=' + periode+'&tipetransaksi=' + tipetransaksi;
//     param += '&kegiatan=' + kegiatan+'&blok=' + blok+'&luas=' + luas;
//     param += '&kodebarang1=' + kodebarang1+'&jlhmat1=' + jlhmat1+'&rpmat1=' + rpmat1;
//     param += '&kodebarang2=' + kodebarang2+'&jlhmat2=' + jlhmat2+'&rpmat2=' + rpmat2;
//     param += '&kodebarang3=' + kodebarang3+'&jlhmat3=' + jlhmat3+'&rpmat3=' + rpmat3;
//     param += '&kodebarang4=' + kodebarang4+'&jlhmat4=' + jlhmat4+'&rpmat4=' + rpmat4;
//     param += '&kbl=' + kbl+'&kht=' + kht+'&khl=' + khl+'&upah=' + upah+'&premi=' + premi;
//     param += '&luasbor=' + luasbor+'&rupiahbor=' + rupiahbor;
	
// 	tujuan = 'kebun_slave_rkbx.php';
// 	document.getElementById('trpemel_'+currRow).style.backgroundColor='cyan';
// 	document.getElementById('btnupload2').disabled=true;
	
// 	post_response_text(tujuan, param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 					document.getElementById('trpemel_' + currRow).style.backgroundColor = 'red';
// 				} else {
// 					if (currRow != undefined) {
// 						document.getElementById('trpemel_' + currRow).style.backgroundColor = '';
// 					}
// 					currRow += 1;
// 					sekarang = currRow;
// 					if ((currRow > maxRow) || (maxRow == undefined)) {
// 						alert("Done"); 
// 						document.getElementById('btnupload2').disabled=false;
// 					} else {
// 						uploaddata(currRow, maxRow);
// 					}
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }