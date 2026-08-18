function getlain(no){
	jenis=document.getElementById('jenis_'+no).value;
	param='jenis='+jenis;
	post_response_text('tax_slave_buktipotongpajak.php?proses=getlain',param,respon);
	function respon() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}
				else {
					if(con.responseText==1){
						document.getElementById('jenisdetail_'+no).disabled=false;
					} else {
						document.getElementById('jenisdetail_'+no).disabled=true;
					}
				   
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
	
}






function posting(periode,kodeorg,noakun,supplier_kpp){
    num=document.getElementById('pages');
    num=num.options[num.selectedIndex].value;
	param='&periode='+periode+'&noakun='+noakun+'&kodeorg='+kodeorg+'&supplier_kpp='+supplier_kpp;
    if(confirm(bahasa.notifandayakin)){
        post_response_text('tax_slave_buktipotongpajak.php?proses=posting',param,respon);    
    }
	
	function respon()
	{
              if(con.readyState==4)
              {
                            if (con.status == 200) {
                                    busy_off();
                                    if (!isSaveResponse(con.responseText)) {
                                            alert(con.responseText);
                                    }
                                    else {
                                       getPage(num);
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
              }	
    }
}


function adddataform(){
    var forms = document.getElementById('forms');
    var kodeorg = document.getElementById('kodeorg');
    var periode = document.getElementById('periode');
    var npwp = document.getElementById('npwp');
    var noakun = document.getElementById('noakun');
    var supplier = document.getElementById('supplier');
    var listtable = document.getElementById('listtable');
    var forms = document.getElementById('forms');
    var dbutton = document.getElementById('hbutton');
    var container = document.getElementById('container');
    forms.style.display='block';
    kodeorg.value='';
    periode.innerHTML="<option value=''>"+bahasa.pilihdata+"</option>";
    noakun.innerHTML="<option value=''>"+bahasa.pilihdata+"</option>";
    npwp.innerHTML="<option value=''>"+bahasa.pilihdata+"</option>";
    supplier.value='';
    noakun.innerHTML="<option value=''>"+bahasa.pilihdata+"</option>";
    
    listtable.style.display='none';
    container.style.display='none';
    hbutton.style.display='';
}
function clearDisplay(){
    document.getElementById('kodeorgcr').value='';
    document.getElementById('periodecr').value='';
    document.getElementById('noakuncr').value='';
    loadData(0);
}
function loadData(num){
    
    var container = document.getElementById('container');
    var forms = document.getElementById('forms');
    var listtable = document.getElementById('listtable');
    var param;
    var kodeorg = document.getElementById('kodeorgcr').value;
    var periode = document.getElementById('periodecr').value;
    var noakuncr = document.getElementById('noakuncr').value;
    var nokasCr = document.getElementById('nokasCr').value;
    var noinvCr = document.getElementById('noinvCr').value;
    var supplierIdKppcr = document.getElementById('supplierIdKppcr').value;

    
    var param ='page='+num;
    param += '&kodeorg='+kodeorg+'&periode='+periode+'&noakuncr='+noakuncr+'&noinvCr='+noinvCr+'&nokasCr='+nokasCr+'&supplierIdKppcr='+supplierIdKppcr;
    post_response_text('tax_slave_buktipotongpajak.php?proses=loadData',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    container.style.display="block";
                    container.innerHTML=con.responseText;
                    forms.style.display="none";
                    listtable.style.display="none";
                }
            } else {
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
    cariBast(paged);
    // cariBast(pg-1);
}

function cariBast(num){
    var container = document.getElementById('container');
    var forms = document.getElementById('forms');
    var listtable = document.getElementById('listtable');
    var kodeorg = document.getElementById('kodeorgcr').value;
    var periode = document.getElementById('periodecr').value;
    var noakuncr = document.getElementById('noakuncr').value;
    var nokasCr = document.getElementById('nokasCr').value;
    var noinvCr = document.getElementById('noinvCr').value;
    var supplierIdKppcr = document.getElementById('supplierIdKppcr').value;
    
    var param ='page='+num;
    param += '&kodeorg='+kodeorg+'&periode='+periode+'&noakuncr='+noakuncr+'&noinvCr='+noinvCr+'&nokasCr='+nokasCr+'&supplierIdKppcr='+supplierIdKppcr;
    //alert(param);
    post_response_text('tax_slave_buktipotongpajak.php?proses=loadData',param,respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    container.style.display="block";
                    forms.style.display="none";
                    listtable.style.display="none";
                    document.getElementById('container').innerHTML=con.responseText;
                    document.getElementById('pages').value=num;
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }   
}

function caridata(){
    var container = document.getElementById('container');
    var forms = document.getElementById('forms');
    var listtable = document.getElementById('listtable');

    var kodeorg = document.getElementById('kodeorgcr').value;
    var periode = document.getElementById('periodecr').value;
    var noakuncr = document.getElementById('noakuncr').value;
    var nokasCr = document.getElementById('nokasCr').value;
    var noinvCr = document.getElementById('noinvCr').value;
    var supplierIdKppcr = document.getElementById('supplierIdKppcr').value;

    var param = 'kodeorg='+kodeorg+'&periode='+periode+'&noakuncr='+noakuncr+'&noinvCr='+noinvCr+'&nokasCr='+nokasCr+'&supplierIdKppcr='+supplierIdKppcr;
   
    post_response_text('tax_slave_buktipotongpajak.php?proses=loadData',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    container.style.display="block";
                    forms.style.display="none";
                    listtable.style.display="none";
                    container.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadDataDetail()
{
    var tables = document.getElementById('tables');
    var param;
   
    post_response_text('tax_slave_buktipotongpajak.php?proses=loadDataDetail',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {


                    tables.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function viewList()
{
    var kodeorg = document.getElementById('kodeorg');
    var periode = document.getElementById('periode');
    var noakun = document.getElementById('noakun');
    var supplier = document.getElementById('supplier');
    var listtable = document.getElementById('listtable');
    var dbutton = document.getElementById('hbutton');
    var tables = document.getElementById('tables');
    var npwp=document.getElementById('npwp');

    if(kodeorg.value==''|| periode.value==''|| noakun.value=='')
    {
        alert('All field is obligatory');
    }
    else
    {
        var param='kodeorg='+kodeorg.value+'&periode='+periode.value+'&noakun='+noakun.value+'&supplier='+supplier.value+'&npwp='+npwp.value+'&tipeView=preview';
        post_response_text('tax_slave_buktipotongpajak.php?proses=loadData2',param,respon);
    }
    //alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //kodeorg.disabled=true;
                    //periode.disabled=true;
                    //noakun.disabled=true;
                    //supplier.disabled=true;
                    listtable.style.display='';
                    //hbutton.style.display='none';
                    tables.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }


}
function viewListExcel(ev){
    tujuan="tax_slave_buktipotongpajak.php";
    var kodeorg = document.getElementById('kodeorg');
    var periode = document.getElementById('periode');
    var noakun = document.getElementById('noakun');
    var supplier = document.getElementById('supplier');
    var listtable = document.getElementById('listtable');
    var dbutton = document.getElementById('hbutton');
    var tables = document.getElementById('tables');
    var npwp=document.getElementById('npwp');
    param='proses=loadData2'+'&kodeorg='+kodeorg.value+'&periode='+periode.value+'&noakun='+noakun.value+'&supplier='+supplier.value+'&npwp='+npwp.value+'&tipeView=excel';
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev);
}

function savedata(){
    var listtable = document.getElementById('listtable');
    var forms = document.getElementById('forms');

    var periode = document.getElementById('periode').value;
    var kodeorg = document.getElementById('kodeorg').value;
    var npwp = document.getElementById('npwp').value;
    var supplier_kpp = document.getElementById('supplier_kpp').value;
    var notransaksi='';
    var noakun='';
    var nilai='';
    var jenis='';
    var kompensasi='';
    var supplier='';
    var dpp='';
    var jenisdetail='';
    var noinvoice='';


    var numRow = 1;
    var nos=1;
    while(document.getElementById('novoucher_'+numRow)) {
        if(document.getElementById('posting_'+numRow))
        {
            if(document.getElementById('posting_'+numRow).checked==true){
                nos++;
                if(notransaksi==''){
                    notransaksi=document.getElementById('novoucher_'+numRow).innerHTML;
                }
                else{
                    notransaksi+='###'+document.getElementById('novoucher_'+numRow).innerHTML;
                }
                if(noakun==''){
                    noakun=document.getElementById('noakun_'+numRow).innerHTML;
                }
                else{
                    noakun+='###'+document.getElementById('noakun_'+numRow).innerHTML;
                }
				
                if(nilai==''){
                    nilai=document.getElementById('nilai_'+numRow).value;
                }
                else{
                    nilai+='###'+document.getElementById('nilai_'+numRow).value;
                }
				
				if(dpp==''){
                    dpp=document.getElementById('dpp_'+numRow).value;
                }
                else{
                    dpp+='###'+document.getElementById('dpp_'+numRow).value;
                }
				
				if(jenis==''){
                    jenis=document.getElementById('jenis_'+numRow).value;
                }
                else{
                    jenis+='###'+document.getElementById('jenis_'+numRow).value;
                }
				
				if(jenisdetail==''){
                    jenisdetail=document.getElementById('jenisdetail_'+numRow).value;
                }
                else{
                    jenisdetail+='###'+document.getElementById('jenisdetail_'+numRow).value;
                }
				
                if(supplier==''){
                    supplier=document.getElementById('kodesupplier_'+numRow).value;
                }
                else{
                    supplier+='###'+document.getElementById('kodesupplier_'+numRow).value;
                }
                if(kompensasi==''){
                    kompensasi=document.getElementById('kompensasi_'+numRow).value;
                }
                else{
                    kompensasi+='###'+document.getElementById('kompensasi_'+numRow).value;
                }
                if(noinvoice==''){
                    noinvoice=document.getElementById('noinvoice_'+numRow).innerHTML;
                }
                else{
                    noinvoice+='###'+document.getElementById('noinvoice_'+numRow).innerHTML;
                }
            }
        }
    numRow++;
    }
    var param='notransaksi='+notransaksi+'&noakun='+noakun+'&nilai='+nilai+'&supplier='+supplier+'&kompensasi='+kompensasi;
    param+='&periode='+periode+'&kodeorg='+kodeorg+'&num='+nos+'&npwp='+npwp;
    param+='&jenis='+jenis+'&dpp='+dpp+'&jenisdetail='+jenisdetail+'&noinvoice='+noinvoice+'&supplier_kpp='+supplier_kpp;
  
    post_response_text('tax_slave_buktipotongpajak.php?proses=savedata',param,respon);
    

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    forms.style.display='none';
                    listtable.style.display='none';
                    //alert(con.responseText);
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function pdf(no_buktipotong,periode,kodeorg) {
	param = 'no_buktipotong=' + no_buktipotong;
	param += '&periode=' + periode;
	param += '&kodeorg=' + kodeorg;
	param += '&method=rekap';
	tujuan = 'tax_slave_bupotpdf.php?' + param;
	title = '';
	width = '1000';
	height = '700';
	ev = 'event';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
}


function pdfpph(no_buktipotong,periode,kodeorg) {
	param = 'no_buktipotong=' + no_buktipotong;
	param += '&periode=' + periode;
	param += '&kodeorg=' + kodeorg;
	param += '&method=pph';
	tujuan = 'tax_slave_bupotpdf.php?' + param;
	title = '';
	width = '1000';
	height = '700';
	ev = 'event';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
}




function getperiode(){
    kodeorg=document.getElementById('kodeorg').value; 
    param='proses=getperiode'+'&kodeorg='+kodeorg;
    tujuan='tax_slave_buktipotongpajak.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {                    
                    document.getElementById('periode').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}

function getnoakun(){
    kodeorg=document.getElementById('kodeorg').value; 
    periode=document.getElementById('periode').value; 
    param='proses=getnoakun'+'&kodeorg='+kodeorg;
	param += '&periode=' + periode;
    tujuan='tax_slave_buktipotongpajak.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    dib=con.responseText.split("####");
                    document.getElementById('noakun').innerHTML=dib[0];
                    document.getElementById('npwp').innerHTML=dib[1];
					getsupp();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}


function deleteht(kodesupplier,periode,noakun,kodeorg,no_buktipotong){
	param='proses=deleteht';
	param += '&kodesupplier=' + kodesupplier;
	param += '&periode=' + periode;
	param += '&noakun=' + noakun;
	param += '&kodeorg=' + kodeorg;
	param += '&no_buktipotong=' + no_buktipotong;
    tujuan='tax_slave_buktipotongpajak.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    loadData();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
	}  
}


function getsupp(){
    kodeorg=document.getElementById('kodeorg').value; 
    periode=document.getElementById('periode').value; 
    param='proses=getsupp'+'&kodeorg='+kodeorg;
	param += '&periode=' + periode;
	param += '&noakun=' + noakun;
    tujuan='tax_slave_buktipotongpajak.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('supplier').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}
function checkfp(nsfp)
{   
    totrow = document.getElementById('totrow').value;
    console.log(nsfp);
    for (i=1; i <= totrow; i++)
    {
        if (document.getElementById('kodesupplier_'+i).value==nsfp) {
            if (document.getElementById('posting_'+i).checked==false) {
                document.getElementById('posting_'+i).checked = true;
            }else{
                document.getElementById('posting_'+i).checked = false;
            }
        }
    }
}
function viewdetail(periode,kodeorg,noakun,kodesupplier){
    form();
    param = 'proses=viewdetail'+'&viewtipe=html'+'&periode='+periode+'&kodeorg='+kodeorg+'&noakun='+noakun+'&kodesupplier='+kodesupplier;
    tujuan = 'tax_slave_buktipotongpajak.php';
    post_response_text(tujuan, param, respog);
    function respog()
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
                    document.getElementById('containerd').innerHTML = con.responseText;
					loadfiles(periode,kodeorg,noakun,kodesupplier);
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
function form(){
    width = '';
    height = '';
    content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}
function dataexceldetail(ev,periode,kodeorg,noakun,kodesupplier,tujuan){
    met="viewdetail";
    param = 'proses=viewdetail'+'&viewtipe=excel'+'&periode='+periode+'&kodeorg='+kodeorg+'&noakun='+noakun+'&kodesupplier='+kodesupplier;
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev);
}
function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev);  
}
function checkAllDt(){
    totRow=document.getElementById('totrow').value;
    chkAll=document.getElementById('chckbxAll');
    for(awal=1;awal<=parseFloat(totRow);awal++){
        if(chkAll.checked==true){
            document.getElementById('posting_'+awal).checked=true;
        }else{
            document.getElementById('posting_'+awal).checked=false;
        }
    }   
}
function detailPDF(noinvoice, ev) {
    param = "proses=pdf&noinvoice=" + noinvoice;
    showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='keu_slave_tagihan_print_detail.php?" + param + "'></iframe>", '', '', ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function addfile(periode,kodeorg,noakun,kodesupplier) {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", document.getElementById("upload").value);
	formdata.append("kriteriaefil", getValue('kriteriaefil'));
	formdata.append("periode", periode);
	formdata.append("kodeorg", kodeorg);
	formdata.append("noakun", noakun);
	formdata.append("kodesupplier", kodesupplier);
	if (document.getElementById("upload").value == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "tax_slave_buktipotongpajak.php?proses=submitfile", true);
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
					document.getElementById("upload").value = "";
					loadfiles(periode,kodeorg,noakun,kodesupplier);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(periode,kodeorg,noakun,kodesupplier) {
	param = 'proses=loadfiles&periode='+periode+'&kodeorg='+kodeorg+'&noakun='+noakun+'&kodesupplier='+kodesupplier;
	tujuan = 'tax_slave_buktipotongpajak.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerupload').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(periode,kodeorg,noakun,kodesupplier,namafile) {
	param = 'proses=deletefile&namafile='+namafile+'&periode='+periode+'&kodeorg='+kodeorg+'&noakun='+noakun+'&kodesupplier='+kodesupplier;
	tujuan = 'tax_slave_buktipotongpajak.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(periode,kodeorg,noakun,kodesupplier);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
