function gettgl(){
	prd    = getValue('prd');
	
	param  = 'proses=gettgl';
	param += '&prd=' + prd;
	
	tujuan = 'vhc_slave_3mandortraksi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data=con.responseText.split("####");
					document.getElementById('tglmulai').value=data[0];
					document.getElementById('tglakhir').value=data[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdivisi() {
	unit= document.getElementById('unit').value;
	param  = 'proses=getdivisi' + '&unit=' + unit;
	tujuan = 'vhc_slave_3mandortraksi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('afd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function previewdetail(prd,mandor,jabatan,unit,tglawal,tglakhir,tahap,kontanan) {
	//form();
	param = 'proses=previewdetail' + '&mandor=' + mandor + '&prd=' + prd+ '&jabatan=' + jabatan+ '&unit=' + unit;
	param += '&tglmulai=' + tglawal;
	param += '&tglakhir=' + tglakhir;
	param += '&tahap=' + tahap;
	param += '&kontanan=' + kontanan;
	tujuan = 'vhc_slave_3mandortraksi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					// zPreview('kebun_slave_premimandoranlistx','##prdlist##unitlist##jabatanlist##afdlist','printContainerlist');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function form() {
	width = '820';
	height = '';
	content = "<fieldset><div id=containerd style=\"width:800px;max-height:350px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog1(title, content, width, height, ev);
}

function getdetail(tgl,mandor,jabatan) {
	form();
	param = 'proses=getdetail' + '&mandor=' + mandor + '&tgl=' + tgl+ '&jabatan=' + jabatan;
	tujuan = 'vhc_slave_3mandortraksi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function add_new_data(){
	document.getElementById('detail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
}

function displayList() {
	document.getElementById('listData').style.display = 'block';
	document.getElementById('detail').style.display = 'none';
	batallist();
	loaddata(0);
}

function edit(kodeorg,divisi,periode,tahap,tglawal,tglakhir,jabatan){
	document.getElementById('detail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	
	setValue2('unit',kodeorg);
	setValue2('afd',divisi);
	setValue2('prd',periode);
	setValue2('tglmulai',tglawal);
	setValue2('tglakhir',tglakhir);
	zPreview('vhc_slave_3mandortraksi','##prd##unit##afd##tglmulai##tglakhir','printContainer');
}

function loaddata(page) {
	prdlist     = document.getElementById('prdlist').value;
	unitlist    = document.getElementById('unitlist').value;
	afdlist     = document.getElementById('afdlist').value;
	
	
	param = 'proses=loaddata&page=' + page;
	param += '&prdlist=' + prdlist;
	param += '&unitlist=' + unitlist;
	param += '&afdlist=' + afdlist;
	
	
	tujuan = 'vhc_slave_3mandortraksi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('printContainerlist').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function gettglmdr(jenis,id){
	if(jenis=='KONTAN'){
		document.getElementById(id).style.display='';
	}else{
		document.getElementById(id).style.display='none';
	}
}
function batallist(){

	document.getElementById('prdlist').value = '';
	document.getElementById('unitlist').value = '';
	document.getElementById('afdlist').value = '';
}

function batal(){
	// document.location.reload();
	document.getElementById('prd').value='';	
    document.getElementById('unit').value='';
    document.getElementById('afd').value='';
    document.getElementById('tglmulai').value='';
    document.getElementById('tglakhir').value='';
	
	setValue2('prd',null);
	setValue2('unit',null);
	setValue2('afd',null);
	setValue2('tglmulai',null);
	setValue2('tglakhir',null);
	
    document.getElementById('printContainer').innerHTML='';	
}

function gettotal(row,idpremi,iddenda,idhasil){
	totalbaris =document.getElementById('totalbaris'+row).value;
	denda      =document.getElementById(iddenda).value;
	premi      =document.getElementById(idpremi).innerHTML;
	ttlprebruto=document.getElementById('ttlprebruto'+row).innerHTML;
	denda      =remove_comma_var(denda);
	premi      =remove_comma_var(premi);
	ttlprebruto=remove_comma_var(ttlprebruto);
	if(denda==''){denda=0;}
	
	premitotal=parseFloat(premi)-parseFloat(denda);
	document.getElementById(idhasil).innerHTML=numberFormat(premitotal);
	
	totaldenda=0;
	for(i=1;i<=totalbaris;i++){
		n = document.getElementById('denda_'+row+'_'+i).value;
		n = remove_comma_var(n);
		if(n==''){n=0;}
		
		totaldenda = parseFloat(totaldenda)+parseFloat(n);
	}
	document.getElementById('ttldenda'+row).innerHTML=numberFormat(totaldenda);
	grandtotal = parseFloat(ttlprebruto)-parseFloat(totaldenda);
	document.getElementById('ttlprenetto'+row).innerHTML=numberFormat(grandtotal);
}


function gettotalpertama(row,idrupiah,idpembagi,idpremibrutorata,idpengali,idpremibruto,idttlblmdenda,iddenda,idpremiinput){

	totalbaris =document.getElementById('totalbaris'+row).value;

	rupiah      =document.getElementById(idrupiah).innerHTML;
	pembagi     =document.getElementById(idpembagi).value;
	brutorata   =document.getElementById(idpremibrutorata).innerHTML;
	pengali     =document.getElementById(idpengali).innerHTML;
	blmdenda    =document.getElementById(idttlblmdenda).innerHTML;
	premibruto  =document.getElementById(idpremibruto).innerHTML;
	denda    	=document.getElementById(iddenda).value;
	premiinput  =document.getElementById(idpremiinput).innerHTML;
	ttlprebruto	=document.getElementById('ttlprebruto'+row).innerHTML;

	if(pembagi == 0){
		alert('Pembagi tidak boleh 0');
		document.getElementById(idpembagi).value = 1;
		pembagi     =document.getElementById(idpembagi).value;
	}

	rupiah_value        =remove_comma_var(rupiah);
	pembagi_value       =remove_comma_var(pembagi);
	brutorata_value     =remove_comma_var(brutorata);
	pengali_value       =remove_comma_var(pengali);
	blmdenda_value      =remove_comma_var(blmdenda);
	premibruto_value    =remove_comma_var(premibruto);
	denda_value    	    =remove_comma_var(denda);
	premiinput_value    =remove_comma_var(premiinput);
	ttlprebruto         =remove_comma_var(ttlprebruto);



	rataratapremi = parseFloat(rupiah_value)/parseFloat(pembagi_value);
	document.getElementById(idpremibrutorata).innerHTML=numberFormat(rataratapremi);
	
	hasil_premibruto = parseFloat(rataratapremi) * parseFloat(pengali_value);
	document.getElementById(idpremibruto).innerHTML=numberFormat(hasil_premibruto);
	document.getElementById(idttlblmdenda).innerHTML=numberFormat(hasil_premibruto);

	if(denda==''){
		denda=0;
	}
	
	document.getElementById(idpremiinput).innerHTML=numberFormat(hasil_premibruto - denda_value);

	totalpembagi=0;
	for(i=1;i<=totalbaris;i++){
		n = document.getElementById('pembagi_'+row+'_'+i).value;
		n = remove_comma_var(n);
		if(n==''){n=0;}
		
		totalpembagi = parseFloat(totalpembagi)+parseFloat(n);
	}

	document.getElementById('ttlpembagi'+row).innerHTML=numberFormat(totalpembagi);

	totalprembrutorata=0;
	for(i=1;i<=totalbaris;i++){
		n = document.getElementById('premibrutorata_'+row+'_'+i).innerHTML;
		n = remove_comma_var(n);
		if(n==''){n=0;}
		
		totalprembrutorata = parseFloat(totalprembrutorata)+parseFloat(n);
	}

	document.getElementById('ttlprebrutorata'+row).innerHTML=numberFormat(totalprembrutorata);
	
	totalprembruto=0;
	for(i=1;i<=totalbaris;i++){
		n = document.getElementById('premibruto_'+row+'_'+i).innerHTML;
		n = remove_comma_var(n);
		if(n==''){n=0;}
		
		totalprembruto = parseFloat(totalprembruto)+parseFloat(n);
	}
	
	document.getElementById('ttlprebruto'+row).innerHTML=numberFormat(totalprembruto);
	
	
	totaldenda=0;
	for(i=1;i<=totalbaris;i++){
		n = document.getElementById('denda_'+row+'_'+i).value;
		n = remove_comma_var(n);
		if(n==''){n=0;}
		
		totaldenda = parseFloat(totaldenda)+parseFloat(n);
	}
	
	document.getElementById('ttldenda'+row).innerHTML=numberFormat(totaldenda);
	
	totalpreminput=0;
	for(i=1;i<=totalbaris;i++){
		n = document.getElementById('premiinput_'+row+'_'+i).innerHTML;
		n = remove_comma_var(n);
		if(n==''){n=0;}
		
		totalpreminput = parseFloat(totalpreminput)+parseFloat(n);
	}
	document.getElementById('ttlprenetto'+row).innerHTML=numberFormat(totalpreminput);

}

function gettotalderes(row,cell){
	denda      =document.getElementById('denda_'+row+'_'+cell).value;
	ttlprebruto=document.getElementById('ttlprebruto_'+row+'_'+cell).innerHTML;
	denda      =remove_comma_var(denda);
	ttlprebruto=remove_comma_var(ttlprebruto);
	if(denda==''){denda=0;}
	if(ttlprebruto==''){ttlprebruto=0;}
	
	grandtotal = parseFloat(ttlprebruto)-parseFloat(denda);
	document.getElementById('ttlprenetto_'+row+'_'+cell).innerHTML=numberFormat(grandtotal);
}

function gettotaltrk(no){
	denda=document.getElementById('dendatrk'+no).value;
	premi=document.getElementById('premitrk'+no).innerHTML;
	denda=remove_comma_var(denda);
	premi=remove_comma_var(premi);
	if(denda==''){
		denda=0;
	}
	premitotal=parseFloat(premi)-parseFloat(denda);
	document.getElementById('premitotaltrk'+no).innerHTML=numberFormat(premitotal);
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



// function batal(){
//     document.getElementById('printContainer').innerHTML='';	
// }

function saveAll(currRowMdr,currRowDet,maxrowmdr,maxrowdet){
	currRowMdr= parseFloat(currRowMdr);
	currRowDet= parseFloat(currRowDet);
	maxrowmdr = parseFloat(maxrowmdr);
	maxrowdet = parseFloat(maxrowdet);
	
	unit      =document.getElementById('unit').value;
	afd       =document.getElementById('afd').value;
	prd       =document.getElementById('prd').value;
	tglmulai  =document.getElementById('tglmulai').value;
	tglakhir  =document.getElementById('tglakhir').value;

	mandor              =document.getElementById('mandor'+currRowMdr).innerHTML;
    tgl                 =document.getElementById('tgl_'+currRowMdr+'_'+currRowDet).innerHTML;
    hasilkerja          =document.getElementById('hasilkerja_'+currRowMdr+'_'+currRowDet).innerHTML;
    harga               =document.getElementById('harga_'+currRowMdr+'_'+currRowDet).innerHTML;
    premi               =document.getElementById('premi_'+currRowMdr+'_'+currRowDet).innerHTML;
	
    if(unit==''){
        alert("Kode organisasi wajib diisi.");return;
    }
	if(afd==''){
        alert("Kode Traksi wajib diisi.");return;
    }
	if(prd==''){
        alert("Periode wajib diisi.");return;
    }

	param='prd='+prd+'&unit='+unit+'&mandor='+mandor+'&hasilkerja='+hasilkerja+'&harga='+harga+'&premi='+premi+'&maxrowmdr='+maxrowmdr+'&maxrowdet='+maxrowdet;
	param+='&tglmulai='+tglmulai;
	param+='&tglakhir='+tglakhir;
	param+='&tgl='+tgl;
	param+='&afd='+afd;
	param+='&hasilkerja='+hasilkerja;
	param+='&harga='+harga;
	param+='&premi='+premi;
	param+='&baris='+currRowDet;
	param+='&mdrbaris='+currRowMdr;

	param+="&proses=savedata";
	tujuan = 'vhc_slave_3mandortraksi.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					document.getElementById('baris'+currRowMdr+'_'+currRowDet).style.backgroundColor = 'red';
					unlockScreen();
                } else {
					document.getElementById('baris'+currRowMdr+'_'+currRowDet).style.backgroundColor = 'cyan';
                    
					currRowDet += 1;
					if(currRowDet>maxrowdet){	
						currRowMdr += 1;
						currRowDet = 1;
					}
					
                    if(currRowMdr>maxrowmdr || currRowDet>maxrowdet){
						alert('Done');
						document.getElementById('printContainer').innerHTML='';
						loaddata(0);
					} else {
						saveAll(currRowMdr,currRowDet,maxrowmdr,maxrowdet);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}


function del(periode,karyid,unit){
   	param='proses=delete'+'&prd='+periode+'&karyid='+karyid+'&unit='+unit;
    tujuan='vhc_slave_3mandortraksi.php';
    if(confirm(' Anda yakin ???')){
        post_response_text(tujuan, param, respog);	
    }
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}else{
					getPage();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}

function posting(periode,karyid,unit){
   	param='proses=posting'+'&prd='+periode+'&karyid='+karyid+'&unit='+unit;
    tujuan='vhc_slave_3mandortraksi.php';
    if(confirm(' Anda yakin ???')){
        post_response_text(tujuan, param, respog);	
    }
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}else{
					getPage();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}

function unposting(periode,karyid,unit){
   	param='proses=unposting'+'&prd='+periode+'&karyid='+karyid+'&unit='+unit;
    tujuan='vhc_slave_3mandortraksi.php';
    if(confirm(' Anda yakin ???')){
        post_response_text(tujuan, param, respog);	
    }
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}else{
					getPage();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}
