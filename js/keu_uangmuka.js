maxf = 0
sekarang = 1;
function saveall(maxRow) {
	maxf = maxRow;
	loopsave(1, maxRow);
}
function loopsave(currRow, maxRow) {
	
	noinvoice = document.getElementById('noinvoice').value;
	notransaksi= document.getElementById('notransaksi' + currRow).innerHTML;
	nilai = document.getElementById('nilai' + currRow).innerHTML;
	nilai = remove_comma_var(nilai);
	if (document.getElementById('cekdata' + currRow).checked == true) {
		cekdata = 1;
	} else {
		cekdata = 0;
	}
	param = 'proses=saveall' + '&noinvoice=' + noinvoice + '&notransaksi=' + notransaksi;
	param += '&nilai=' + nilai + '&cekdata=' + cekdata;
	tujuan = 'keu_slave_tagihanv2.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
					
					//update nilai ht
					document.getElementById('nilaiinvoice').value=con.responseText;
					
                    if(currRow>maxRow) {
						closeDialog();
						alert('Done');     
						redirectefill(noinvoice);
                    } else {
						loopsave(currRow,maxRow);
                    }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cekall() {
	drt = document.getElementById('cekall');
	if (drt.checked == true) {
		chk = true;
	} else {
		chk = false;
	}
	var tbl = document.getElementById("contentdetail");
	var row = tbl.rows.length;
	row = row - 1;
	for (i = 1; i <= row; i++) {
		document.getElementById('cek' + i).checked = chk;
	}
}





function getinfo(){
	content= "<div id=formgetinfo style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    title='Info';
    height='';
    width='';
    ev='event';
    showDialog1(title,content,width,height,ev);	
    kodeorg=document.getElementById('kodeorg').value;
    tanggalinvoice=document.getElementById('tanggalinvoice').value;
    supplier=document.getElementById('supplier').value;
    noinvoice=document.getElementById('noinvoice').value;
    param='proses=getinfo';
    param+='&kodeorg='+kodeorg;
    param+='&tanggalinvoice='+tanggalinvoice;
    param+='&supplier='+supplier;
    param+='&noinvoice='+noinvoice;
   
    tujuan = 'keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);      
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formgetinfo').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}






function displayFormInput(){
    //clearData();
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    document.getElementById('detailField').innerHTML='';
    document.getElementById('detailField').style.display='none';

    document.getElementById('kodeorg').disabled=false;
    document.getElementById('unit').disabled=false;
    document.getElementById('penerima').disabled=false;


}

function displaylist(){
    clearData();
    document.getElementById('sJenis').value='noinvoice';
    document.getElementById('sNoTrans').value='';
    document.getElementById('ssupplier').value='';
    document.getElementById('listData').style.display='block';
    document.getElementById('formInput').style.display='none';
    document.getElementById('detailField').innerHTML='';
    document.getElementById('detailField').style.display='none';
    loadData(0);
}

function clearData(){
    document.getElementById('tipeinvoice').disabled=false;
    document.getElementById('unit').value='';
    document.getElementById('noinvoice').value='';
    document.getElementById('unit').disabled=false;
    document.getElementById('noinvoicesupplier').value='';
    document.getElementById('kodeorg').value='';
    document.getElementById('kodeorg').disabled=false;
    document.getElementById('jatuhtempo').value='';
    document.getElementById('tanggal').value='';
    document.getElementById('reksupplier').value='';
    document.getElementById('tipeinvoice').value='';
    document.getElementById('nopo').value='';
    document.getElementById('nopo').disabled=false;
    document.getElementById('supplier').disabled=false;
    document.getElementById('supplier').value='';
    document.getElementById('noakun').value='';
    document.getElementById('matauang').value='IDR';
    document.getElementById('matauang').disabled=false;
    document.getElementById('kurs').value='1';
    document.getElementById('nofp').value='';
    document.getElementById('jenistransaksi').value='';
    document.getElementById('nilaiinvoice').value='';
    document.getElementById('tanggalinvoice').value='';
    document.getElementById('keterangan2').value='';
    document.getElementById('npwp').value='';
    document.getElementById('npwp').disabled=false;
    document.getElementById('tanggalnofp').value='';
    document.getElementById('notransaksi_gr').value='';
    document.getElementById('termin').value='';
    document.getElementById('proses').value='add';
    // document.getElementById('upload').value="";
	param='proses=clearData';
	tujuan='keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					loadfiles();
				}
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function printPDF(ev) {
    // Prep Param
    param = "proses=pdf";
    showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='keu_slave_tagihan_print.php?" + param + "'></iframe>", '800', '400', ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function getunit(kodeorg,kodeunit,npwp) {
    kdpt=kodeorg.value;
    param='kdpt=' + kdpt + '&proses=getunit';
    if(kodeunit!=0){
        param+='&kodeunit='+kodeunit;
    }
    if(npwp!=0){
        param+='&npwp='+npwp;
    }
    post_response_text('keu_slave_uangmuka.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // === Success Response
                    data=con.responseText.split("####");
                    document.getElementById('unit').innerHTML=data[0];
                   // document.getElementById('npwp').innerHTML=data[1];
                 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getrek(supplier,reksupplier,jenissupplier) {

    supplier=supplier;
    tipeinvoice=document.getElementById('tipeinvoice').value;
    param='supplier=' + supplier + '&proses=getrek'+'&tipeinvoice='+tipeinvoice;
    if(reksupplier!=0){
        param+='&reksupplier='+reksupplier;
    }
    if(jenissupplier!=0){
        param+='&jenissupplier='+jenissupplier;
    }
    post_response_text('keu_slave_tagihanv2.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // === Success Response

                    data=con.responseText.split('####');
                    document.getElementById('reksupplier').innerHTML=data[0];
                    document.getElementById('jenissupplier').innerHTML=data[1];

                    showDetail();
                    //if(reksupplier!=0){
                        
                    //}
                    
                 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getkurs() {
    matauang = document.getElementById('matauang').value;
    tanggal = document.getElementById('tanggal').value;
    param = 'matauang='+matauang+'&tanggal='+tanggal+'&proses=getkurs';
    tujuan = 'keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('kurs').value=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getnoakunsup() {
    supplier = document.getElementById('supplier').value;
    jenissupplier = document.getElementById('jenissupplier').value;
    param = 'supplier='+supplier+'&jenissupplier='+jenissupplier+'&proses=getnoakunsup';
    tujuan = 'keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('noakun').value=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdate30() {
    tanggal = document.getElementById('tanggal').value;
    param = 'tanggal=' + tanggal + '&proses=getdate30';
    tujuan = 'keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('tanggalinvoice').value =tanggal;
					
					
					if(con.responseText==1){
						alert('Tanggal dipilih tidak boleh kurang dari tanggal hari ini');
						document.getElementById('tanggal').value ='';
						document.getElementById('jatuhtempo').value ='';
					}else{
						document.getElementById('jatuhtempo').value = con.responseText;
					}
                    
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function gettglfp(){
    tanggalinvoice = document.getElementById('tanggalinvoice').value;
    document.getElementById('tanggalnofp').value=tanggalinvoice;
}

function getnilai() {
    pajak = document.getElementById('pajak').value;
    nilaiinvoice = document.getElementById('nilaiinvoice').value;
    nilaiinvoice=nilaiinvoice.replace(new RegExp(/,/i, "gm"),'');
    if (pajak!=0){
        nilai=(pajak/100)*nilaiinvoice;
    }
    document.getElementById('nilai').value=numberFormat(nilai);

}

function getpajak() {
    supplier = document.getElementById('supplier').value;
    noakun = document.getElementById('noakundt').value;
    nilaiinvoice = document.getElementById('nilaiinvoice').value;
    param = 'supplier='+supplier+'&noakun='+noakun+'&nilaiinvoice='+nilaiinvoice+'&proses=getpajak';
    tujuan = 'keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    $data=con.responseText.split("####");
                    document.getElementById('pajak').value = $data[0];
                    document.getElementById('nilai').value = $data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getnoakun(noakun,keterangan) {
    noaruskas = document.getElementById('noaruskas').value;
    kodevhc = document.getElementById('kodevhc').value;
    param = 'noaruskas='+noaruskas+'&noakun='+noakun+'&keterangan='+keterangan+'&kodevhc='+kodevhc+'&proses=getnoakun';
    tujuan = 'keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    data=con.responseText.split("####");
                    document.getElementById('noakundt').innerHTML=data[0];
                    document.getElementById('keterangandt').innerHTML=data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getnoaruskas(noaruskas) {
    kodevhc = document.getElementById('kodevhc').value;
    param = 'kodevhc='+kodevhc+'&noaruskas='+noaruskas+'&proses=getnoaruskas';
    tujuan = 'keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('noaruskas').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function disnopo(){
    jnsInvoice=document.getElementById('tipeinvoice');
    jnsInvoice=jnsInvoice.options[jnsInvoice.selectedIndex].value;
    param='tipeinvoice=' + jnsInvoice;
    tujuan='keu_slave_tagihanv2.php';
    post_response_text(tujuan + '?' + 'proses=disnopo', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    data=con.responseText.split('####');
                    document.getElementById('nopo').disabled=false;
                    if (data[0]==1){
                        document.getElementById('supplier').disabled=false;
                    }else{
                        document.getElementById('supplier').disabled=true;
                    }
                    // document.getElementById('supplier').innerHTML=data[1];
                }

                if (jnsInvoice=='um') {
                    document.getElementById('supplier').disabled=false;
                }

            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function searchNopo(title,ev,langCari) {
    kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
    unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
    isi=document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].value;
    tipe=document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].text;
    tanggal=document.getElementById('tanggal').value;
    if(kodeorg==''){
        alert("PT Tidak Boleh Kosong");
        return;
    }
    if(unit==''){
        alert("Unit Tidak Boleh Kosong");
        return;
    }
    if(isi==''){
        alert("Jenis PO Tidak Boleh Kosong");
        return;
    }
    if (tanggal == '') {
        alert(notiftagihtanggal);
        return;
    }
    cekDtPo(langCari, title, ev);
}

function cekDtPo(langCari,title,ev) {
    jnsInvoice=document.getElementById('tipeinvoice').value;
    tanggal=document.getElementById('tanggal').value;
    param='jnsInvoice=' + jnsInvoice;
    tujuan='keu_slave_tagihanv2.php';
    post_response_text(tujuan + '?' + 'proses=cekData', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (parseInt(con.responseText) != 0) {
                        doc="No. ";
                        content="<fieldset><legend>" + langCari + " " + tipe + "</legend>";
                        // content="<fieldset><legend>" + langCari + " " + tipe + "</legend>" + langCari +
                            // " " + doc + "<input type=text class=myinputtext id=no_brg>";
                         
						content+="<table>";	
						content+="<tr>";	
						content+="<td>" + langCari + " "+ doc +"</td>";	
							content+="<td>:</td>";	
							content+="<td><input type=text class=myinputtext id=no_brg></td>";	
						content+="</tr>";
						
						content+="<tr>";	
							content+="<td>Tanggal</td>";	
							content+="<td>:</td>";	
							content+="<td><input type=text class=myinputtext id=tglcariinv1 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:75px; maxlength=10 />";	
							content+="s/d <input type=text class=myinputtext id=tglcariinv2 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:75px; maxlength=10 /></td>";							
						content+="</tr>";
						content+="</table>";
		
						//>				
                        contentjenis="<select id=jeniscari style='width:150px'><option value='k'>Contractor</option><option value='p'>PO</option></select>";
                        if (jnsInvoice == 'um') {
                            content=content + contentjenis + "<button class=mybutton onclick=findNopo()>Find</button></fieldset><div id=container2></div>";
                        } else {
                            content=content + "<button class=mybutton onclick=findNopo()>Find</button></fieldset><div id=container2></div>";
                        }
                        content=content + "<input type='hidden' id='jnsInvoice' value=" + isi + ">";
                        width='';
                        height='400';
                        showDialog1(title + tipe, content, width, height, ev);
                        findNopo();
                    } else {
                        document.getElementById('nopo').value='';
                        document.getElementById('nopo').disabled=true;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function findNopo(){
    txt=trim(document.getElementById('no_brg').value);
    jnsInvoice=document.getElementById('tipeinvoice').value;
    tanggal=document.getElementById('tanggal').value;
    unit=document.getElementById('unit').value;
    kodeorg=document.getElementById('kodeorg').value;
	
	tglcariinv1=document.getElementById('tglcariinv1').value;
	tglcariinv2=document.getElementById('tglcariinv2').value;
	supplier=document.getElementById('supplier').value;
	
    param='txtfind=' + txt + '&jnsInvoice=' + jnsInvoice + '&tanggal=' + tanggal + '&unit=' + unit + '&kodeorg=' + kodeorg;
    param+='&tglcariinv1=' + tglcariinv1 + '&tglcariinv2=' + tglcariinv2 + '&supplier=' + supplier;
    if (jnsInvoice == 'um') {
        jeniscari=document.getElementById('jeniscari');
        jeniscari=jeniscari.options[jeniscari.selectedIndex].value;
        param += '&jeniscari=' + jeniscari;
    }
    tujuan='keu_slave_getpotagihan.php';
    post_response_text(tujuan + '?' + 'proses=getPo', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container2').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function numberFormat(number,digit) {
    number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
    //Seperates the components of the number
    var components = (parseFloat(number).toFixed(digit)).split(".");
    //Comma-fies the first part
    components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    //Combines the two sections
    return components.join(".");
}

function setPo(np,nilai,jns,ppn,namasupplier,noakun,untdt,matauang,kurs,notransaksi_gr,termin){
    document.getElementById('nopo').value=np;
    document.getElementById('nilaiinvoice').value=(nilai);
    // document.getElementById('noakun').value=noakun;
    document.getElementById('notransaksi_gr').value=notransaksi_gr;
    document.getElementById('termin').value=termin;
    document.getElementById('tipeinvoice').disabled=false;
    jk=document.getElementById('supplier');
    for (x=0; x < jk.length; x++) {
        if (jk.options[x].value == namasupplier) {
            jk.options[x].selected=true;
        }
    }
    jkunit=document.getElementById('unit');
    for (x=0; x < jkunit.length; x++) {
        if (untdt != '') {
            if (jkunit.options[x].value == untdt) {
                jkunit.options[x].selected=true;
            }
        }
    }

    if (typeof matauang != 'undefined') {
        document.getElementById('matauang').value=matauang;
    }
    if (typeof kurs != 'undefined') {
        document.getElementById('kurs').value=kurs;
    }
    closeDialog();
    getrek(namasupplier,0,0);
}

function batal(){
    document.getElementById('notransaksi').value='';
    document.getElementById('kodeorg').value='';
    document.getElementById('unit').value='';
    document.getElementById('tanggal').value='';
    document.getElementById('jenis').value='';
    document.getElementById('noakun').value='';
    document.getElementById('notransaksireferensi').value='';
    document.getElementById('penerima').value='';
    document.getElementById('keterangan').value='';
    document.getElementById('nilai').value='';


}

function insert(){
    notransaksi             = getValue('notransaksi');
    pt                      = getValue('kodeorg');
    unit                    = getValue('unit');
    tanggal                 = getValue('tanggal');
    jenis                   = getValue('jenis');
    noakun                  = getValue('noakun');
    notransaksireferensi    = getValue('notransaksireferensi');
    penerima                = getValue('penerima');
    keterangan              = getValue('keterangan');
    nilai                   = getValue('nilai');

    param = "proses=insert&notransaksi="+notransaksi+"&unit="+unit+"&tanggal="+tanggal+"&jenis="+jenis+"&noakun="+noakun+"&notransaksireferensi="+notransaksireferensi+
            "&penerima="+penerima+"&keterangan="+keterangan+"&nilai="+nilai;
    tujuan = "keu_slave_uangmuka.php";
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   // document.getElementById('container2').innerHTML=con.responseText;
                   document.getElementById('formInput').style.display='none';
                   loadData(0);
                   batal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function edit(notransaksi,pt,unit,tanggal,kode,noakun,notransaksireferensi,penerima,nilai,keterangan){
  

    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('notransaksi').disabled=true;

    document.getElementById('kodeorg').value=pt;
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;

    document.getElementById('tanggal').value=tanggal;
    document.getElementById('jenis').value=kode;
    
    document.getElementById('noakun').innerHTML="<option value="+noakun+">"+noakun+"<option>";
    document.getElementById('notransaksireferensi').innerHTML="<option value="+notransaksireferensi+">"+notransaksireferensi+"<option>";

    document.getElementById('penerima').innerHTML="<option value="+notransaksireferensi+">"+notransaksireferensi+"<option>";
 

    fillPenerima();
    document.getElementById('keterangan').value=keterangan;
    document.getElementById('nilai').value=nilai;
    // fillNoRef();

    document.getElementById('nilai').value=nilai;

    tombol = document.getElementById('aksi');
    tombol.setAttribute('onclick','saveEdit("'+notransaksi+'")');
    tombol.innerHTML='Update';

    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';


}

function saveEdit(notransaksi){
    unit                    = getValue('unit');
    tanggal                 = getValue('tanggal');
    jenis                   = getValue('jenis');
    noakun                  = getValue('noakun');
    notransaksireferensi    = getValue('notransaksireferensi');
    penerima                = getValue('penerima');
    keterangan              = getValue('keterangan');
    nilai                   = getValue('nilai');

    param = "proses=update&notransaksi="+notransaksi+"&unit="+unit+"&tanggal="+tanggal+"&jenis="+jenis+"&noakun="+noakun+"&notransaksireferensi="+notransaksireferensi+
            "&penerima="+penerima+"&keterangan="+keterangan+"&nilai="+nilai;
    tujuan = "keu_slave_uangmuka.php";
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   // document.getElementById('container2').innerHTML=con.responseText;
                   document.getElementById('formInput').style.display='none';
                   loadData(0);
                   batal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    

}


function redirectefill(noinvoice){
	// var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value');
	// kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
	// noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
	// tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
	
	param='method=insertefill&noinvoice='+noinvoice;
    tujuan='log_slave_efill.php';
	
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					showDetail();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}


function Delete(notransaksi)
{
    param='proses=delete'+'&notransaksi='+notransaksi;
    tujuan='keu_slave_uangmuka.php';
    if(confirm(' Anda yakin ingin menghapus data ini?'))
    {
        post_response_text(tujuan, param, respog);  
    }
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                   loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function postingData(notransaksi) {
    param = 'proses=postingData&notransaksi=' + notransaksi;
    tujuan = 'keu_slave_uangmuka.php';
    if (confirm(notifpostingpenagihan))
        post_response_text(tujuan,param,respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alert('Transaksi '+notransaksi+' berhasil diposting!');
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function redirectefill2(noinvoice){
	// var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value');
	// kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
	// noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
	// tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
	
	param='method=insertefill&noinvoice='+noinvoice;
    tujuan='log_slave_efill.php';
	
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					pg=document.getElementById('pages');
                    pg=pg.options[pg.selectedIndex].value;
                    getPage(pg);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}
function postingDatalaporan(noinvoice) {
    param = 'noinvoice=' + noinvoice;
    tujuan = 'keu_slave_tagihanPosting.php';
    if (confirm(notifpostingpenagihan))
        post_response_text(tujuan + '?' + 'proses=getPo', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    pg=document.getElementById('pages');
                    pg=pg.options[pg.selectedIndex].value;
                    getPage(pg);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function detailPDF(notransaksi, ev) {
    param = "proses=pdf&notransaksi=" + notransaksi;
    showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='keu_slave_uangmuka_print_detail.php?" + param + "'></iframe>", '', '', ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function showDetailData(notransaksi) {
    width = '500px';
    height = '450px';
    content = "<div id=containerData></div>";
    ev = 'event';
    title = notransaksi;
    showDialog1(title, content, width, height, ev);
}

function viewDetailData2(notransaksi, ev) {
    // Prep Param
    param = 'notransaksi=' + notransaksi + '&proses=getDetail';
    showDetailData(notransaksi);
    tujuan = 'keu_slave_uangmuka.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('containerData').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fakturpajak(noinvoice,ev) {
    content = "<div id=formpost ></div>";
    title = 'Faktur Pajak';
    height = 'auto';
    width = 'auto';
    showDialog2(title, content, width, height,ev);
    getformfp(noinvoice);
}

function getformfp(noinvoice) {
    var param = "noinvoice=" + noinvoice;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('formpost').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_slave_tagihanv2.php?proses=showformfp', param, respon);
}

function savefp(noinvoice) {
    historynofp = document.getElementById('historynofp').value;
    historytanggalfp = document.getElementById('historytanggalfp').value;
    param = "noinvoice=" + noinvoice + "&historynofp=" + historynofp + "&historytanggalfp=" + historytanggalfp;
    
    if (historynofp == '') {
        alert('Factur Number must be filled');
        return;
    }
    if (historytanggalfp == '') {
        alert('Date must be filled');
        return;
    }
    post_response_text('keu_slave_tagihanv2.php?proses=savefp', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    //alert('Posting Berhasil');
                    closeDialog2();
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showDetail()
{
    unit=trim(document.getElementById('unit').value);
    tipeinvoice=trim(document.getElementById('tipeinvoice').value);
    noinvoice=trim(document.getElementById('noinvoice').value);
    param = 'unit='+unit+'&proses=showDetail'+'&tipeinvoice='+tipeinvoice+'&noinvoice='+noinvoice;
    post_response_text('keu_slave_tagihanv2.php', param, respon);
    
    function respon() 
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else
                {
                    // === Success Response
                    document.getElementById('detailField').style.display = 'block';
                    document.getElementById('detailField').innerHTML = con.responseText;
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function saveDetail(){

    noinvoice=trim(document.getElementById('noinvoice').value);
    kodevhc=trim(document.getElementById('kodevhc').value);
    kodeasset=trim(document.getElementById('kodeasset').value);
    noakundt=trim(document.getElementById('noakundt').value);
    nilai=trim(document.getElementById('nilai').value);
    proses=trim(document.getElementById('prosesdt').value);
    tipeinvoice=trim(document.getElementById('tipeinvoice').value);
    noaruskas=trim(document.getElementById('noaruskas').value);
    keterangandt=document.getElementById('keterangandt').value;
    hisnoakun=document.getElementById('hisnoakun').value;
    hisnoaruskas=document.getElementById('hisnoaruskas').value;
    nourut=document.getElementById('nourut').value;
    pajak=document.getElementById('pajak').value;
    
    param='kodevhc='+kodevhc+'&noinvoice='+noinvoice+'&kodeasset='+kodeasset+'&proses='+proses+'&noakun='+noakundt;
    param+='&nilai='+nilai+'&tipeinvoice='+tipeinvoice+'&noaruskas='+noaruskas+'&keterangan='+keterangandt;
    param+='&hisnoakun='+hisnoakun+'&hisnoaruskas='+hisnoaruskas+'&nourut='+nourut+'&pajak='+pajak;

    tujuan='keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {          
                    cleardetail();
                    showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cleardetail(){ 
    document.getElementById('kodevhc').value='';
    document.getElementById('kodeasset').value='';
    document.getElementById('noakundt').value='';
    document.getElementById('nilai').value='';
    document.getElementById('keterangandt').value='';
    document.getElementById('noaruskas').value='';
    document.getElementById('hisnoakun').value='';
    document.getElementById('hisnoaruskas').value='';
    document.getElementById('pajak').value='';
    document.getElementById('prosesdt').value='insertdt';
    showDetail();
}

function updatedt(kodevhc,kodeasset,noakun,nilai,noaruskas,keterangan,noinv_ref,nourut,pajak){
    document.getElementById('kodevhc').value=kodevhc;
    document.getElementById('kodeasset').value=kodeasset;
    document.getElementById('noakundt').value=noakun;
    document.getElementById('hisnoakun').value=noakun;
    document.getElementById('nilai').value=nilai;
    document.getElementById('noaruskas').value=noaruskas;
    document.getElementById('hisnoaruskas').value=noaruskas;
    document.getElementById('keterangandt').value=keterangan;
    document.getElementById('prosesdt').value='updatedt';
    document.getElementById('noinv_ref').value=noinv_ref;
    document.getElementById('nourut').value=nourut;
    document.getElementById('pajak').value=pajak;
    getnoakun(noakun,keterangan);
}

function deletedt(noinvoice,noakun,noaruskas,kodevhc,noinvoicesupplier,nourut)
{
    param='proses=deletedt'+'&noinvoice='+noinvoice+'&noakun='+noakun+'&noaruskas='+noaruskas+'&kodevhc='+kodevhc+'&noinvoicesupplier='+noinvoicesupplier+'&nourut='+nourut;
    tujuan='keu_slave_tagihanv2.php';
    if(confirm(' Anda yakin ingin menghapus data ini?'))
    {
        post_response_text(tujuan, param, respog);  
    }
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                   showDetail();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function loadData(num){

    param='proses=loadData';
    if (num==="search"){
        sJenis=document.getElementById('sJenis').value;
        sNoTrans=document.getElementById('sNoTrans').value;
        penerima=document.getElementById('Spenerima').value;



        if (sJenis != '') {
            param += '&sJenis=' + sJenis;
        }
        if (sNoTrans != '') {
            param += '&sNoTrans=' + sNoTrans;
        }
        if (penerima != '') {
            param += '&penerima=' + penerima;
        }
    
        param+='&page=0';
      //  alert(param);

    }else{
    param+='&page='+num;
    }    
   // alert(param);

   document.getElementById('listData').style.display='block';

    tujuan='keu_slave_uangmuka.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    isdt=con.responseText.split("####");
                    document.getElementById('continerlist').innerHTML=isdt[0];
                    document.getElementById('footData').innerHTML=isdt[1];
                    document.getElementById('formInput').style.display='none';
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}
function getPage(pg) {
    pg = document.getElementById('pages');
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;
    loadData(paged);
    // cariBast(pg-1);
}

function addum(title,content,ev)
{
    width='auto';
    height='auto';
    showDialog1(title,content,width,height,ev);
    getformum();
}

function getformum(){
    supplier=trim(document.getElementById('supplier').value);
    param='proses=getformum'+'&supplier='+supplier;
    tujuan='keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('formPencarianum').innerHTML=con.responseText;
                    findum();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function findum(){
    supplier=trim(document.getElementById('supplier').value);
    param='proses=getdataum'+'&supplier='+supplier;
    transum=trim(document.getElementById('transum').value);
    param+='&transum='+transum;
    
    tujuan='keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('containerum').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdatadt(noinvoiceum,noakunum,nilaium,noaruskasum,keteranganum) {
    
    noinvoice=trim(document.getElementById('noinvoice').value);
    param='proses=saveum'+'&noinvoice='+noinvoice+'&noinvoiceum='+noinvoiceum+'&noakun='+noakunum+'&nilai='+nilaium+'&noaruskas='+noaruskasum+'&keterangan='+keteranganum;
    tujuan='keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);  

    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                   showDetail();
                   closeDialog();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}
function addToDetail(rowdt){    
    var supp=0;
    var suppid="";
    var totRpAll=0;
    var allKirim="";
    for(awal=1;awal<=rowdt;awal++){
         ckbox=document.getElementById('pph22_'+awal);
         if(ckbox.checked==true){
            if(awal==1){
                allKirim+="&suppId[]="+document.getElementById('suppId_'+awal).value;
                allKirim+="&noInv[]="+document.getElementById('noinv_'+awal).value;
                allKirim+="&nilaiRp[]="+document.getElementById('nilaiId_'+awal).value;  
            }else{
                allKirim+="&suppId[]="+document.getElementById('suppId_'+awal).value;
                allKirim+="&noInv[]="+document.getElementById('noinv_'+awal).value;
                allKirim+="&nilaiRp[]="+document.getElementById('nilaiId_'+awal).value;      
            }
            totRpAll+=parseFloat(document.getElementById('nilaiId_'+awal).value);
            supp+=1;
         }
    }
    if(supp==0){
        alert(bahasa.datakosong);
        return;
    }
    param='proses=saveHutang'+'&noinvoice='+getValue('noinvoice')+'&noinvoicesupplier='+getValue('noinvoicesupplier')
    param+='&tanggal='+getValue('tanggal')+'&tipeinvoice='+getValue('tipeinvoice')+'&unit='+getValue('unit')+'&kodeorg='+getValue('kodeorg')+'&totRpAll='+totRpAll;
    param+='&jatuhtempo='+getValue('jatuhtempo')+'&npwp='+getValue('npwp')+'&suppIdHtg='+getValue('suppIdHtg')+"&noakundetail="+getValue('noakundetail');
    param+=allKirim;
    //alert(param);
    tujuan='keu_slave_tagihanv2.php';
    post_response_text(tujuan, param, respog);  
    function respog(){
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                    //echo $tempSupp."####".$totNilRp."####".$rNoakun[0]['noakun']."####IDR####1";
                   balikandt=con.responseText.split("####");
                   suppId=document.getElementById('supplier');
                   for(a=0;a<suppId.length;a++){
                        if(suppId.options[a].value==balikandt[0]){
                            suppId.options[a].selected=true;
                        }
                    }
                    document.getElementById('nilaiinvoice').value=balikandt[1];
                    document.getElementById('noakun').value=balikandt[2];
                    mtuang=document.getElementById('matauang');
                    for(a=0;a<mtuang.length;a++){
                        if(mtuang.options[a].value==balikandt[3]){
                            mtuang.options[a].selected=true;
                        }
                    }
                    document.getElementById('kurs').value=balikandt[4];
                    document.getElementById('noinvoice').value=balikandt[5];
                    document.getElementById('nopo').value=balikandt[6];
                    document.getElementById('nopo').disabled=true;
                    document.getElementById('proses').value='edit';
                    alert('Added Data Header');
                    showDetail();
                    closeDialog();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}
function chkDtAll(){
    totrow=document.getElementById('totRowPPh').value;
    chkAlldt=document.getElementById('chkAll');
    for(itungAwal=1;itungAwal<=totrow;itungAwal++){
        if(chkAlldt.checked==false){
            document.getElementById('pph22_'+itungAwal).checked=false;
        }else{
            document.getElementById('pph22_'+itungAwal).checked=true;
        }
    }
}
function ambilHtgPO(){
    prd=document.getElementById('periodeHtgId');
    prd=prd.options[prd.selectedIndex].value;
    prd2=document.getElementById('periodeHtgId2');
    prd2=prd2.options[prd2.selectedIndex].value;
    suppId=document.getElementById('suppIdHtg');
    suppId=suppId.options[suppId.selectedIndex].value;

    txt=trim(document.getElementById('no_brg').value);
    jnsInvoice=document.getElementById('tipeinvoice').value;
    tanggal=document.getElementById('tanggal').value;
    unit=document.getElementById('unit').value;
    kodeorg=document.getElementById('kodeorg').value;
    param='txtfind=' + txt + '&jnsInvoice=' + jnsInvoice + '&tanggal=' + tanggal + '&unit=' + unit;
    param+='&kodeorg=' + kodeorg+'&periodeHtgId='+prd+'&periodeHtgId2='+prd2+'&suppIdHtg='+suppId;
    tujuan='keu_slave_getpotagihan.php';
    if((prd!='')&&(prd2!='')){
        post_response_text(tujuan + '?' + 'proses=getPo', param, respog);    
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container2').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showupload(ev) {
	showformupload(ev);
	noinvoice = document.getElementById('noinvoice').value;
	param = 'proses=showupload&noinvoice='+noinvoice;
	tujuan = 'keu_slave_tagihanv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(noinvoice);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function loadfiles(noinvoice) {
	param = 'proses=loadfiles&noinvoice='+noinvoice;
	tujuan = 'keu_slave_tagihanv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfilestop') !== null) {
						document.getElementById('listfilestop').innerHTML = con.responseText;
					}
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('listfilesview') !== null) {
						document.getElementById('listfilesview').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var noinvoice = document.getElementById("noinvoice").value;
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("noinvoice", noinvoice);
	formdata.append("kriteriaefil", kriteriaefil);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "keu_slave_tagihanv2.php?proses=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					document.getElementsByClassName("mybutton").disabled=false;
					alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(noinvoice);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(noinvoice, namafile) {
	param = 'proses=deletefile&noinvoice=' + noinvoice + '&namafile=' + namafile;
	tujuan = 'keu_slave_tagihanv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(noinvoice);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewefill(noinvoice,ev){
	content= "<div id=formviewefill  style=\"height:100%;\"></div>";
	title='View Efilling System';
	height='';
	width='';
	showDialog5(title,content,width,height,'event');
	showefil(noinvoice);
	
	var dialog = document.getElementById('dynamic5');
	clientWidth = document.getElementById("dynamic5").clientWidth;
	clientHeight = document.getElementById("dynamic5").clientHeight;
	pos = new Array();
	pos = getMouseP(ev);

	dialog.style.top = pos[1]+'px';
	dialog.style.left = (pos[0]-clientWidth-500)+'px';
}

function showefil(noinvoice){
	param='method=viewefill&noinvoice='+noinvoice;
    tujuan='log_slave_efill.php';
	
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('formviewefill').innerHTML = con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function addfiledata(noinvoice,criteria){
	uploadfile = document.getElementById("upload_"+criteria);
	var file = uploadfile.files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", uploadfile.value);
	formdata.append("noinvoice", noinvoice);
	formdata.append("criteria", criteria);
	if (uploadfile.value == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "log_slave_efill.php?method=uploadfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					document.getElementsByClassName("mybutton").disabled=false;
					alert('Uploaded Success.');
					document.getElementById("upload_"+criteria).value = "";
					document.getElementById("bodyefil").innerHTML = "";
					document.getElementById("bodyefil").innerHTML = con.responseText;
					// loadfiles(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deleteefil(noinvoice,namafile)
{
    param='method=deleteefil&namafile='+namafile+'&noinvoice='+noinvoice;
    tujuan='log_slave_efill.php';
	
	if (confirm('Anda yakin hapus item/file ini : '+namafile+' ?')) {
		post_response_text(tujuan, param, respog);
	}      
    
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Success");
					document.getElementById("bodyefil").innerHTML = "";
					document.getElementById("bodyefil").innerHTML = con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		} 
	}
}

function fillNoAkun(){
    jenis = document.getElementById('jenis');
    jenis=jenis.options[jenis.selectedIndex].value;


    //alert(jenis);

    param = "proses=fillNoAkun&jenis="+jenis;
    tujuan = 'keu_slave_uangmuka.php';

    post_response_text(tujuan, param, respog);
   
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                    // data = con.responseText.split("####");
                    // document.getElementById("favorite").value = mylist.options[mylist.selectedIndex].text; 
                   noakun = document.getElementById('noakun');
                   noakun.innerHTML = con.responseText;
                   fillNoRef();
                  

				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		} 
	}
}

function fillNoRek(){
    penerima = document.getElementById('penerima');
    penerima=penerima.options[penerima.selectedIndex].value;

    jenis = document.getElementById('jenis');
    jenis=jenis.options[jenis.selectedIndex].value;

    cgttu = document.getElementById('cgttu');
    cgttu=cgttu.options[cgttu.selectedIndex].value;

    param = "proses=fillNoRek&jenis="+jenis+"&penerima="+penerima;

    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                   norek = document.getElementById('norek');
                   norek.innerHTML = con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		} 
	}



}

function fillNoRef(){
    jenis = document.getElementById('jenis');
    jenis=jenis.options[jenis.selectedIndex].value;

    kodeorg = document.getElementById('kodeorg');
    kodeorg=kodeorg.options[kodeorg.selectedIndex].value;

    unit = document.getElementById('unit');
    unit=unit.options[unit.selectedIndex].value;

    param = "proses=fillNoRef&jenis="+jenis+'&kodeorg='+kodeorg+'&unit='+unit;
    tujuan = 'keu_slave_uangmuka.php';

    post_response_text(tujuan, param, respog);

    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                 
                   noakun = document.getElementById('notransaksireferensi');
                   noakun.innerHTML = con.responseText;
                  //alert(con.responseText);
                  

				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		} 
	}

}

function fillPenerima(){

    noref = document.getElementById('notransaksireferensi');
    noref=noref.options[noref.selectedIndex].value;

    jenis = document.getElementById('jenis');
    jenis=jenis.options[jenis.selectedIndex].value;

    param = "proses=fillPenerima&jenis="+jenis+"&noref="+noref;
    tujuan = 'keu_slave_uangmuka.php';

    post_response_text(tujuan, param, respog);

    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                   penerima = document.getElementById('penerima');
                   penerima.innerHTML = con.responseText;
                  //alert(con.responseText);
                  }
			}else{
				busy_off();
                error_catch(con.status);
			}
		} 
	}

}

function generateNoTran(tanggal){
    unit = document.getElementById('unit');
    unit=unit.options[unit.selectedIndex].value;
    notran=tanggal+unit;

   // alert(tanggal);
    param = "proses=generateNoTran&notran="+notran+"&unit="+unit;
    tujuan = 'keu_slave_uangmuka.php';

    post_response_text(tujuan, param, respog);

    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                   notransaksi = document.getElementById('notransaksi');
                   notransaksi.value = notran+"/"+con.responseText;
                  //alert(con.responseText);
                    notransaksi = document.getElementById('jenis');
                    notransaksi.value = "";
                  }
			}else{
				busy_off();
                error_catch(con.status);
			}
		} 
	}
}