/**
 * @author repindra.ginting
 */
 function toggleBtn(id){
	var list = document.getElementById(id);
	if(list.style.display == "none"){
		list.style.display = "block";
	}else if(list.style.display == "block"){
		list.style.display = "none";
	}
}

function setSloc(x){
        gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
        tglstart=document.getElementById(gudang+'_start').value;
        tglend=document.getElementById(gudang+'_end').value;
        tglstart=tglstart.substr(6,2)+"-"+tglstart.substr(4,2)+"-"+tglstart.substr(0,4);
        tglend=tglend.substr(6,2)+"-"+tglend.substr(4,2)+"-"+tglend.substr(0,4);
        document.getElementById('displayperiod').innerHTML=tglstart+" - "+tglend;

        if (gudang != '') {
                if (x == 'simpan') {
                        document.getElementById('sloc').disabled = true;
                        document.getElementById('btnsloc').disabled = true;
                        document.getElementById('pemilikbarang').disabled = true;
                        tujuan = 'log_slave_getBastNumber.php';
                        param = 'gudang=' + gudang;
                        post_response_text(tujuan, param, respog);
                }
                else {
                        document.getElementById('nodok').value='';
                        document.getElementById('sloc').disabled = false;
                        document.getElementById('pemilikbarang').disabled = false;
                        document.getElementById('pemilikbarang').innerHTML = "<option value=''></option>";
                        document.getElementById('noSj').innerHTML = typeof ktrngPi!='undefined'? ktrngPi: '';
                        //document.getElementById('sloc').options[0].selected=true;
                        document.getElementById('btnsloc').disabled = false;
						
						setValue2('sloc',null);
						setValue2('kegudang',null);
                        kosongkan(); 
						refresh();
                }	

        }	
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                //alert(con.responseText);
                                                document.getElementById('nodok').value = trim(con.responseText);
                                                getMutasiList(gudang);
                                                getSJList(gudang);
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }
}
function getSJList(gudang)
{
	param='proses=getsuratjalan&gudangId='+gudang+'&kodept='+document.getElementById('pemilikbarang').value;
	tujuan = 'log_slave_mutasibarang_getSj.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}
				else {
					if(document.getElementById('noSj')){
						document.getElementById('noSj').innerHTML=con.responseText;
					}
					cariBast();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function getPT(gudang)
{
   param='gudang='+gudang;
        tujuan = 'log_slave_gudangGetPTOption.php';
        post_response_text(tujuan, param, respog);
        function respog(){
                if (con.readyState == 4) {
                        if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert(con.responseText);
                                }
                                else {
                                        document.getElementById('pemilikbarang').innerHTML=con.responseText;
                                        getNopo(gudang);
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
                }
        }	
}


function disableHeader()
{
        document.getElementById('tanggal').disabled=true;
        document.getElementById('kegudang').disabled=true;
        document.getElementById('catatan').disabled=true;	
}
function enableHeader()
{
        document.getElementById('tanggal').disabled=false;
        document.getElementById('kegudang').disabled=false;
        document.getElementById('catatan').disabled=false;
}

function showWindowBarang(title,ev)
{

          content= "<div style='width:100%;'>";
          content+="<fieldset>Search : <input type=text id=txtnamabarang class=myinputtext size=25 onkeypress=\"return enterEuy(event);\" maxlength=35><button class=mybutton onclick=goCariBarang()>Go</button> ";
          content+="<div id=containercari style='overflow:auto;max-height:200px;width:auto'></div></fieldset></div>";
     //display window
           width='auto';
           height='auto';
           showDialog1(title,content,width,height,ev);		
}

function enterEuy(evt)
{
        key=getKey(evt);
        if(key==13)
        {
                goCariBarang();
        }
        else
        {
                return tanpa_kutip(evt);
        }

}

function loadField(kode,nama,satuan,saldo)
{
        document.getElementById('kodebarang').value=kode;
        document.getElementById('namabarang').value=nama;
        document.getElementById('satuan').value=satuan;
        if(document.getElementById('saldoakhirqty') && typeof saldo !== 'undefined'){
            document.getElementById('saldoakhirqty').value=saldo;
        }
        closeDialog();		
}

function kosongkan()
{
	if(document.getElementById('kodebarang')){
		document.getElementById('kodebarang').value='';
	}
	if(document.getElementById('catatan')){
		document.getElementById('catatan').value='';
	}
	if(document.getElementById('satuan')){
		document.getElementById('satuan').value='';
	}
	if(document.getElementById('qty')){
		document.getElementById('qty').value=0;
	}
	enableHeader();	
}

function nextItem()
{
	if(document.getElementById('kodebarang')){
		document.getElementById('kodebarang').disabled=false;
	}
	if(document.getElementById('satuan')){
		document.getElementById('satuan').disabled=false;
	}
	if(document.getElementById('namabarang')){
		document.getElementById('namabarang').disabled=false;
	}
	if(document.getElementById('kodebarang')){	
		document.getElementById('kodebarang').value='';
	}
	if(document.getElementById('namabarang')){
		document.getElementById('namabarang').value='';
	}
	if(document.getElementById('satuan')){
		document.getElementById('satuan').value='';
	}
	if(document.getElementById('qty')){
		document.getElementById('qty').value=0;	
	}
	if(document.getElementById('nopo')){
		document.getElementById('nopo').value='';
	}


     document.getElementById('saveitemmutasi').style.display='';
     document.getElementById('edititemmutasi').style.display='none';
     
}
function refresh()
{
	if(document.getElementById('noSj')){
		document.getElementById('noSj').selectedIndex = 0;
	}
	if(document.getElementById('sloc')){
		var gudang = document.getElementById('sloc').value;
		getSJList(gudang);
	}
	if(document.getElementById('expeditor')){
		document.getElementById('expeditor').value="";	
	}
	if(document.getElementById('jeniskendaraan')){
		document.getElementById('jeniskendaraan').value="";	
	}
	if(document.getElementById('driver')){
		document.getElementById('driver').value="";	
	}
	if(document.getElementById('nopol')){
		document.getElementById('nopol').value="";	
	}
	if(document.getElementById('hpdriver')){
		document.getElementById('hpdriver').value="";	
	}
	getdata('container','defaulttemplatedetail');	
}
function bastBaru()
{
	nextItem();
	kosongkan();	
	setSloc('simpan');
	document.getElementById('bastcontainer').innerHTML='';
	if(document.getElementById('expeditor')){
		document.getElementById('expeditor').value="";	
	}
	if(document.getElementById('jeniskendaraan')){
		document.getElementById('jeniskendaraan').value="";	
	}
	if(document.getElementById('driver')){
		document.getElementById('driver').value="";	
	}
	if(document.getElementById('nopol')){
		document.getElementById('nopol').value="";	
	}
	if(document.getElementById('hpdriver')){
		document.getElementById('hpdriver').value="";	
	}


     document.getElementById('saveitemmutasi').style.display='';
     document.getElementById('edititemmutasi').style.display='none';
}

function goCariBarang() {
    gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    tanggal = document.getElementById('tanggal').value;

    nodok = document.getElementById('nodok').value;
    if (nodok == '') {
        alert('Document Number is Obligatory');
    } else {
        txtcari = trim(document.getElementById('txtnamabarang').value);
        pemilikbarang = document.getElementById('pemilikbarang');
        pemilikbarang = pemilikbarang.options[pemilikbarang.selectedIndex].value;

        if (document.getElementById('nodok') == '') {
            alert('Document number is obligatory');
        } else
            if (pemilikbarang.length < 3) {
                alert('Googs Owner(PT) is obligatory');
            } else {
                if (txtcari.length < 1) {
                    alert('material name min. 1 char');
                } else {
                    param = 'txtcari=' + txtcari + '&pemilikbarang=' + pemilikbarang;
                    param += '&gudang=' + gudang;
                    param += '&tanggal=' + tanggal;
                    tujuan = 'log_slave_cariBarang.php';
                    post_response_text(tujuan, param, respog);
                }
            }
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containercari').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getItemAndSave(dataparam,num,jml,ele,suratjalan_flag){
	var tujuan='log_slave_saveMutasi.php';
	var newTrans = num+1;
	if(typeof dataparam[num] !== 'undefined' && dataparam[num] !== ""){
		param = dataparam[num];
		bastCont = getInner('bastcontainer');
		bastCont = bastCont.trim();
		if(bastCont=='') param += '&isNewTrans='+newTrans;
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					var res = con.responseText;
					res = res.split('#####');
					//document.getElementById('qty').style.backgroundColor='#ffffff';
					//nextItem();
					document.getElementById('bastcontainer').innerHTML=res[0];
					
					
					if(suratjalan_flag == ""){
						allinput = ele[num].getElementsByTagName('input');
						for(i=0; i<allinput.length; i++){
							allinput[i].value="";
						}
						if(res.length>1) {
							setValue('nodok',res[1]);
						}
						getMutasiList(gudang);
					}else{
						if(ele[num]){
							console.log(ele[num]);
							ele[num].remove();
						}
					}
					
					if(num<jml){
						num++;
						getItemAndSave(dataparam,num,jml,ele,suratjalan_flag);
					}else{
						if(suratjalan_flag == ""){
							gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
						}else{
							var nodok = document.getElementById('nodok').value;
							splres = nodok.split('-');
							var tabFRM1 = document.getElementById('tabFRM1');
							tabFRM1.click();
							setValue('txtbabp',splres[0]);
							document.getElementById('bastcontainer').innerHTML = "";
							setSlocx();
						}
					}
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cariBastx(num)
{
	if(typeof num == 'undefined' || num == ""){
		num = 0;
	}
	tex=trim(document.getElementById('txtbabp').value);
	document.getElementById('txbnosj').value='';
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    if(gudang =='')
        {
                alert('Storage Location  is obligatory')
        }
        else
        {
                param='gudang='+gudang;
                param+='&page='+num;
                param+='&txbnosj=';
                if(tex!='')
                        param+='&tex='+tex;
                tujuan = 'log_slave_getMutasiList.php';
                post_response_text(tujuan, param, respog);			
        }
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                document.getElementById('containerlist').innerHTML=con.responseText;
												refreshx();
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	
}

function refreshx()
{
	if(document.getElementById('noSj')){
		document.getElementById('noSj').selectedIndex = 0;
	}
	if(document.getElementById('expeditor')){
		document.getElementById('expeditor').value="";	
	}
	if(document.getElementById('jeniskendaraan')){
		document.getElementById('jeniskendaraan').value="";	
	}
	if(document.getElementById('driver')){
		document.getElementById('driver').value="";	
	}
	if(document.getElementById('nopol')){
		document.getElementById('nopol').value="";	
	}
	if(document.getElementById('hpdriver')){
		document.getElementById('hpdriver').value="";	
	}
	
	var gudang = document.getElementById('sloc').value;
	getSJListx(gudang);
}

function getSJListx(gudang)
{
	param='proses=getsuratjalan&gudangId='+gudang+'&kodept='+document.getElementById('pemilikbarang').value;
	tujuan = 'log_slave_mutasibarang_getSj.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}
				else {
					if(document.getElementById('noSj')){
						document.getElementById('noSj').innerHTML=con.responseText;
					}
					getdata('container','defaulttemplatedetail');	
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function setSlocx(){
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	tglstart=document.getElementById(gudang+'_start').value;
	tglend=document.getElementById(gudang+'_end').value;
	tglstart=tglstart.substr(6,2)+"-"+tglstart.substr(4,2)+"-"+tglstart.substr(0,4);
	tglend=tglend.substr(6,2)+"-"+tglend.substr(4,2)+"-"+tglend.substr(0,4);
	document.getElementById('displayperiod').innerHTML=tglstart+" - "+tglend;
	
	if (gudang != '') {
		document.getElementById('sloc').disabled = true;
		document.getElementById('btnsloc').disabled = true;
		document.getElementById('pemilikbarang').disabled = true;
		tujuan = 'log_slave_getBastNumber.php';
		param = 'gudang=' + gudang;
		post_response_text(tujuan, param, respog);
	}	
    
	function respog(){
		if (con.readyState == 4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('nodok').value = trim(con.responseText);
                    cariBastx();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function saveItemBast(){
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	var noSj = "";
	if(document.getElementById('noSj')){
		noSj = document.getElementById('noSj').options[document.getElementById('noSj').selectedIndex].value;
	}
	var suratjalan_flag = "";
	if(document.getElementById('suratjalan_flag')){
		suratjalan_flag = document.getElementById('suratjalan_flag').value;
	}
	tanggal=document.getElementById('tanggal').value;
    x=tanggal;
    _start=document.getElementById(gudang+'_start').value;
    _end=document.getElementById(gudang+'_end').value;
    while (x.lastIndexOf("-") > -1) {
        x = x.replace("-", "");
    }
	while (x.lastIndexOf("-") > -1) {
		x=x.replace("/","");
	}
	
	curdateY=x.substr(4,4).toString();
	curdateM=x.substr(2,2).toString();
	curdateD=x.substr(0,2).toString();
	curdate=curdateY+curdateM+curdateD;	
	curdate=parseInt(curdate);
	if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
		alert('Date out of range');
		return false;
	} else {
		nodok		=trim(document.getElementById('nodok').value);
		tanggal		=trim(document.getElementById('tanggal').value);
		catatan		=trim(document.getElementById('catatan').value);
		if(noSj !== ""){
			noSj = "&nosj="+noSj;
		}
		/**INPUT*
		kodebarang	=trim(document.getElementById('kodebarang').value);
		satuan		=trim(document.getElementById('satuan').value);
		qty			=trim(document.getElementById('qty').value);
		//CHANGE ** TO **/
		var listbarang = document.getElementById('listbarang');
		var tr = listbarang.getElementsByTagName('tr'); 
		var data 	= {};
		var alldata	= [];
		if(tr.length > 0){
			for(ti=0; ti<tr.length; ti++){
				rowdata	= [];
				inputTxt = tr[ti].getElementsByTagName('input'); 
				if(inputTxt.length > 0){
					for(i=0; i<inputTxt.length; i++){
						data = {};
						if(inputTxt[i].name !== ""){
							data = {
								'name'	: inputTxt[i].name,
								'value'	: inputTxt[i].value
							}
							rowdata.push(data);
						}
					}
				}
				alldata.push(rowdata);
			}
		}
		
		/** END **/
		
        kegudang	=document.getElementById('kegudang');
        kegudang	=trim(kegudang.options[kegudang.selectedIndex].value);
        gudang 		=trim(document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value);
        pemilikbarang =trim(document.getElementById('pemilikbarang').options[document.getElementById('pemilikbarang').selectedIndex].value);
		

		driver=trim(document.getElementById('driver').value);
		nosuratjalan=trim(document.getElementById('noSj_find').value);
		hpdriver=trim(document.getElementById('hpdriver').value);
		nopol		=trim(document.getElementById('nopol').value);
		jeniskendaraan		=trim(document.getElementById('jeniskendaraan').value);
		expeditor		=trim(document.getElementById('expeditor').value);
		
		
		
		
        if(nodok=='') {
            alert('Document Number is obligatory');
        } else if(kegudang=='') {
            alert('Destination is obligatory');
        } else {
            if(confirm('Are you sure?')) {
				/**INPUT*
				param='nodok='+nodok+'&tanggal='+tanggal+'&kodebarang='+kodebarang;
				param+='&kegudang='+kegudang+'&satuan='+satuan+'&qty='+qty;
				param+='&gudang='+gudang+'&catatan='+catatan;
				param+='&pemilikbarang='+pemilikbarang;
				//CHANGE ** TO **/
				var dataparam = [];
				if(alldata.length>0){
					for(ti=0; ti<alldata.length; ti++){
						var paramItem = "";
						if(alldata[ti].length > 0){
							kodebarang = "";
							satuan = "";
							qty = 0;
							nopo = "";
							for(i=0; i<alldata[ti].length; i++){
								if(alldata[ti][i].name == "kodebarang"){
									kodebarang = alldata[ti][i].value;
									if(alldata[ti][i].value=='') {
										alert('Material, UOM and volume is obligatory - '+alldata[ti][i].name);
										return false;
									}
								}
								if(alldata[ti][i].name == "satuan"){
									satuan = alldata[ti][i].value;
									if(alldata[ti][i].value=='') {
										alert('Material, UOM and volume is obligatory - '+alldata[ti][i].name);
										return false;
									}
								}
								if(alldata[ti][i].name == "qty"){
									qty = alldata[ti][i].value;
									// if(parseFloat(qty)<0.001) {
									if(parseFloat(qty)<=0) {
										alert('Material, UOM and volume is obligatory - '+alldata[ti][i].name);
										return false;
									}
								}
								if(alldata[ti][i].name == "nopo"){
									nopo = alldata[ti][i].value;
									if(alldata[ti][i].value=='') {
										alert('Material, UOM and volume is obligatory - ' + alldata[ti][i].name);
										return false;
									}
								}
								paramItem += "&"+alldata[ti][i].name+"="+alldata[ti][i].value;
							}
						}
						param='nodok='+nodok+'&tanggal='+tanggal+noSj+paramItem;
						param+='&kegudang='+kegudang;
						param+='&gudang='+gudang+'&catatan='+catatan+'&nosuratjalan='+nosuratjalan;
						param+='&pemilikbarang='+pemilikbarang;
						param+='&driver='+driver+'&hpdriver='+hpdriver+'&nopol='+nopol+'&jeniskendaraan='+jeniskendaraan+'&expeditor='+expeditor;
						dataparam.push(param);
					}
					getItemAndSave(dataparam,0,alldata.length-1,tr,suratjalan_flag);
				}
				/** END **/
				disableHeader();
				//document.getElementById('qty').style.backgroundColor='red';
			}
		}
    }
}

function cekGudang(elemkegudang)
{
        kegudang=elemkegudang.options[elemkegudang.selectedIndex].value;
        src=document.getElementById('sloc');
        gudang 		=trim(src.options[src.selectedIndex].value);
        if(src.disabled)
        {
			if(gudang==kegudang)
			{
				alert('Storage Location is the same');
				elemkegudang.options[0].selected=true;
			}else{
				getdata('listsj','getsuratjalan');
				
			}
					
        }
        else
        {
                elemkegudang.options[0].selected=true;
                alert('Document Number is obligatory');
        }
		if(document.getElementById('noSj')){
			getSJList(gudang);
		}
}
function getdata(toid,proses){
	var ele = document.getElementById(toid);
	var sloc = document.getElementById('sloc');
	var pemilikbarang = document.getElementById('pemilikbarang');
	kodegudang = sloc.options[sloc.selectedIndex].value;
	kodeorg = pemilikbarang.options[pemilikbarang.selectedIndex].value;
	param='proses='+proses+'&kodeorg='+kodeorg+'&kodegudang='+kodegudang;
	tujuan = 'log_slave_mutasibarang_getSj.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}
				else {
					if(ele){
					ele.innerHTML=con.responseText;
					}
				}
			}else {
					busy_off();
					error_catch(con.status);
			}
		}
	}	
}
function getdatabarang(e){
	var val = e.options[e.selectedIndex].value;
	var ele = document.getElementById('container');
	var sloc = document.getElementById('sloc');
	var pemilikbarang = document.getElementById('pemilikbarang');
	param='proses=getbarang&notransaksi='+val+'&sloc='+sloc.value+'&pemilikbarang='+pemilikbarang.value;
	tujuan = 'log_slave_mutasibarang_getSj.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}
				else {
					try{
						dataArr = JSON.parse(con.responseText);
						ele.innerHTML=dataArr.data;	
						if(document.getElementById('expeditor')){
							document.getElementById('expeditor').value=dataArr.expeditor;	
						}
						if(document.getElementById('jeniskendaraan')){
							document.getElementById('jeniskendaraan').value=dataArr.jeniskend;	
						}
						if(document.getElementById('driver')){
							document.getElementById('driver').value=dataArr.driver;	
						}
						if(document.getElementById('nopol')){
							document.getElementById('nopol').value=dataArr.nopol;	
						}
						if(document.getElementById('hpdriver')){
							document.getElementById('hpdriver').value=dataArr.hpdriver;	
						}
					}catch(e){
						alert(con.responseText);
					}
				}
			}else {
					busy_off();
					error_catch(con.status);
			}
		}
	}
}
function getMutasiList(gudang)
{
	param='gudang='+gudang;
	tujuan = 'log_slave_getMutasiList.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
				if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
								alert(con.responseText);
						}
						else {
							document.getElementById('containerlist').innerHTML=con.responseText;
							getSJList(gudang);
							getSj();
						}
				}
				else {
						busy_off();
						error_catch(con.status);
				}
		}
	}	
}

function editMutasi(kodebarang,namabarang,satuan,jumlah,nopo)
{
     document.getElementById('kodebarang').value=kodebarang;
     document.getElementById('namabarang').value=namabarang;
     document.getElementById('satuan').value=satuan;
     document.getElementById('qty').value=jumlah;
    //  document.getElementById('nopo').value=nopo;
     document.getElementById('saveitemmutasi').style.display='none';
     document.getElementById('edititemmutasi').style.display='';
}

function EditItemBast() {
        		kegudang	=document.getElementById('kegudang');
                kegudang=trim(kegudang.options[kegudang.selectedIndex].value);
                pemilikbarang = document.getElementById('pemilikbarang');
                     pemilikbarang=trim(pemilikbarang.options[pemilikbarang.selectedIndex].value);
                notransaksi=document.getElementById('nodok').value;
                kodebarang=document.getElementById('kodebarang').value;

                param='nodok='+notransaksi+'&kodebarang='+kodebarang;
                // param+='&delete=true&pemilikbarang='+pemilikbarang;
                param+='&pemilikbarang='+pemilikbarang;
                param+='&kegudang='+kegudang;
                tujuan='log_slave_saveMutasi.php';
                
                post_response_text(tujuan, param, respog);	
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                              saveItemBast();
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	
}

function delMutasi(notransaksi,kodebarang)
{
        kegudang	=document.getElementById('kegudang');
                     kegudang=trim(kegudang.options[kegudang.selectedIndex].value);
                pemilikbarang = document.getElementById('pemilikbarang');
                     pemilikbarang=trim(pemilikbarang.options[pemilikbarang.selectedIndex].value);
                param='nodok='+notransaksi+'&kodebarang='+kodebarang;
                param+='&delete=true&pemilikbarang='+pemilikbarang;
                param+='&kegudang='+kegudang;
                tujuan='log_slave_saveMutasi.php';
                if(confirm('Deleting Document '+notransaksi+', are you sure..?'))
                  post_response_text(tujuan, param, respog);	
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                document.getElementById('bastcontainer').innerHTML=con.responseText;
												getSJList(gudang);
												getSj();
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	
}

function delXMutasi(nodok)
{
        if(confirm('Deleting Doc: '+nodok+', Are sure..?'))
        {
                param='notransaksi='+nodok;
                tujuan='log_slave_deleteBapb.php';//file ini berfungsi untuk penerimaan dan pengeluaran
           if(confirm('All data in this document will be removed. Continue ?'))
           {
                 post_response_text(tujuan, param, respog);
           }   
        }
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
											gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
											bastBaru();
											//setSloc('simpan');
											var contentFRM1 = document.getElementById('contentFRM1');
											if(contentFRM1.style.display !== 'none'){
												cariBast();
											}
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }		
}

function cariBast(num)
{
	if(typeof num == 'undefined' || num == ""){
		num = 0;
	}
	tex=trim(document.getElementById('txtbabp').value);
	txbnosj=trim(document.getElementById('txbnosj').value);
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    if(gudang =='')
        {
                alert('Storage Location  is obligatory')
        }
        else
        {
                param='gudang='+gudang;
                param+='&page='+num;
                param+='&txbnosj='+txbnosj;
                if(tex!='')
                        param+='&tex='+tex;
                tujuan = 'log_slave_getMutasiList.php';
                post_response_text(tujuan, param, respog);			
        }
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                document.getElementById('containerlist').innerHTML=con.responseText;
												// getSj();
												kegudang = getValue('kegudang');
												if(kegudang == ''){
													getGudangTujuan();
												}
											}
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	
}


function previewMutasi(notransaksi,ev){
	param='notransaksi='+notransaksi;
	tujuan = 'log_slave_print_mutasi_pdf.php?'+param;	
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_print_mutasi_pdf.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
 //display window
   title=notransaksi;
   width='800';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   //showDialog2(title,content,width,height,ev);

}

/**
 * Tambah Barang dari Surat Jalan
 */
 
function tambahSj() {
	var gudang=getValue('sloc'),
		notrans = getValue('nodok'),
		kegudang = getValue('kegudang'),
		param='notransaksi='+notrans+'&nosj='+getValue('noSj')+'&tanggal='+getValue('tanggal')
			+'&pemilikbarang='+getValue('pemilikbarang')+'&catatan='+getValue('catatan')
			+'&gudang='+gudang+'&kegudang='+kegudang+'&statInput='+status,
		bastCont = getInner('bastcontainer');
	bastCont = bastCont.trim();
	if(bastCont=='') param += '&isNewTrans=1';
	
	tanggal=document.getElementById('tanggal').value;
	x=tanggal;
    _start=document.getElementById(gudang+'_start').value;
    _end=document.getElementById(gudang+'_end').value;
    while (x.lastIndexOf("-") > -1) {
        x = x.replace("-", "");
    }
	while (x.lastIndexOf("-") > -1) {
		x=x.replace("/","");
	}
	
	curdateY=x.substr(4,4).toString();
	curdateM=x.substr(2,2).toString();
	curdateD=x.substr(0,2).toString();
	curdate=curdateY+curdateM+curdateD;	
	curdate=parseInt(curdate);
	if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
		alert('Date out of range');
		return;
	}
	
	if(getValue('noSj')==''){
		alert("No Surat Jalan Tidak Boleh Kosong");
		return;
	}
	tujuan = 'log_slave_mutasibarang_fromSj.php';
	if(notrans=='') {
		alert("Document number is obligatory");
		return;
	}
	if(kegudang=='') {
		alert("Gudang Tujuan harus ada");
		return;
	}
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					statusDrt=1;
					document.getElementById('qty').style.backgroundColor='#ffffff';
					if(getValue('catatan')=='') setValue('catatan',getValue('noSj'));
					setValue('isNewTrans',statusDrt);
					nextItem();
                    dtIsi=con.responseText.split("#####");
					document.getElementById('bastcontainer').innerHTML=dtIsi[0];
                    document.getElementById('nodok').value=dtIsi[1];
                    getMutasiList(gudang);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

/**
 * getSj
 */
function getGudangTujuan() {
	gdng=document.getElementById('sloc');
	gdng=gdng.options[gdng.selectedIndex].value;
	param = 'proses=gudangtujuan'+'&gudangId='+gdng;
	tujuan = "log_slave_gudangtujuan.php";
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('kegudang').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getSj() {
	/*
	gdng=document.getElementById('sloc');
	gdng=gdng.options[gdng.selectedIndex].value;
	param = 'proses=list'+'&gudangId='+gdng;
	tujuan = "log_slave_mutasibarang_getSj.php";
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('noSj').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}*/
}
function getNosj(title,nsj,ev){
    nd=document.getElementById('nodok').value;
    if(nd==''){
        return;
    }
	gdng=document.getElementById('sloc');
    gdng=gdng.options[gdng.selectedIndex].value;
        content= "<div style='width:100%;'>";
        content+="<fieldset>"+nsj+"<input type=text id=txtnamabarang class=myinputtext size=25 maxlength=35><button class=mybutton onclick=goCariNosj()>Go</button> </fieldset>";
        content+="<div id=containercari style='overflow:scroll;height:250px;width:235px'></div></div><input type=hidden id=gdngId value='"+gdng+"' />";                 
        width='250';
        height='300';
        showDialog1(title,content,width,height,ev);		
}
function goCariNosj(){
	txtcr=document.getElementById('txtnamabarang').value;
	gdnid=document.getElementById('gdngId').value;
	param = 'proses=crLst'+'&txtcrNosj='+txtcr;
	param+='&gudangId='+gdnid;
	tujuan = "log_slave_mutasibarang_getSj.php";
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('containercari').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setNosj(nosj,jnsdt){
    sj=document.getElementById('noSj');
    for(as=0;as<sj.length;as++){
        if(sj.options[as].value==nosj){
                sj.options[as].selected=true;
        }
    }
	document.getElementById('jns').value=jnsdt;
    closeDialog();
}

// Umar
function showupload(ev) {
	nodok = document.getElementById('nodok').value;
	kodebarang = document.getElementById('kodebarang').value;

	if (nodok == '') {
		alert('Pilih Gudang Terlebih Dahulu!');
		return;
	}

	if (kodebarang == '') {
		alert('Isi Kode Barang Terlebih Dahulu!');
		return;
	}

	showformupload(ev);
	param = 'method=showupload&notransaksi='+nodok+'&kodebarang='+kodebarang;
	tujuan = 'log_slave_penerimaanUpload.php';
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
					loadfilesx(nodok,kodebarang);
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

function loadfilesx(nodok,kodebarang) {
	param = 'method=loadfiles&notransaksi='+nodok+'&kodebarang='+kodebarang;
	tujuan = 'log_slave_penerimaanUpload.php';
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

function save_filex(){
	var file = document.getElementById("upload").files[0];
    var notransaksi = document.getElementById('nodok').value;
    var kodebarang = document.getElementById('kodebarangupload').innerHTML;
    var jenisupload = document.getElementById('kriteriaefil').value;
    var formdata = new FormData();
    formdata.append("notransaksi", notransaksi);
    formdata.append("kodebarang", kodebarang);
    formdata.append("jenisupload", jenisupload);
    formdata.append("file", file);
    formdata.append("fileupload", document.getElementById("upload").value);
    //alert(document.getElementById("filex").value);
    if (document.getElementById("upload").value == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "log_slave_penerimaanUpload.php?method=submitfilex", true);
    busy_on();
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
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfilesx(notransaksi,kodebarang);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function edit(notransaksi, tanggal, subunit, catatan, nosj, expeditor, jeniskendaraan, nopol, driver, hpdriver){
	document.getElementById('contentFRM0').style.display = '';
	document.getElementById('contentFRM1').style.display = 'none';

	document.getElementById("nodok").value    	 	= notransaksi;
	document.getElementById("tanggal").value  	 	= tanggal;
	// document.getElementById("kegudang").value 	 	= subunit;
	setValue2('kegudang',subunit);
	document.getElementById("catatan").value  	 	= catatan;
	document.getElementById("noSj_find").value 	    = nosj;
	document.getElementById("expeditor").value 	 	= expeditor;
	document.getElementById("jeniskendaraan").value = jeniskendaraan;
	document.getElementById("nopol").value 	        = nopol;
	document.getElementById("driver").value         = driver;
	document.getElementById("hpdriver").value       = hpdriver;

	loadMaterial(notransaksi);
}

function getNopo(gudang){
	let pt 		= document.getElementById('pemilikbarang').value;
	let tujuan 	= 'log_slave_mutasibarang_getSj.php';

	let param 	= 'proses=getNopo';
		param 	+= '&gudang=' + gudang;
		param 	+= '&pt=' + pt;

	function response(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                	// document.getElementById('nopo').disabled 	= false;
                	// document.getElementById('nopo').innerHTML 	= con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    post_response_text(tujuan, param, response);
}

function loadMaterial(notransaksi){
	let tujuan  = 'log_slave_mutasibarang_getSj.php';
	let param 	= 'proses=loadMaterial';
		param 	+= '&nodok=' + notransaksi;

	function response(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                	document.getElementById('bastcontainer').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    post_response_text(tujuan, param, response);
}
//End Umar