// JavaScript Document


function pdfpanjang(nokontrak) {
    param = "method=pdfpanjang&nokontrak="+nokontrak;
	alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_kontrakjual_slave_v2.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
}


function viewlistfile(nokontrak) {
    param       = 'method=viewlistfile&nokontrak='+trim(nokontrak);
	tujuan      = 'pmn_kontrakjual_slave_v2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					
					if (document.getElementById('listfiles') !== null) {
						// document.getElementById('listfiles').innerHTML = con.responseText;
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('500px','400px'); 
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
	var nokontrak = document.getElementById("noKtrk").value;
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("nokontrak", nokontrak);
	formdata.append("kriteriaefil", kriteriaefil);
	if (getValue('upload') == "") {
		alertify.alert("Informasi","warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled=true;
	// busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "pmn_kontrakjual_slave_v2.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					//=== Success Response
					document.getElementsByClassName("mybutton").disabled=false;
					alertify.alert("Informasi",'Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(nokontrak);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(nokontrak, namafile) {
	param = 'method=deletefile&nokontrak=' + nokontrak + '&namafile=' + namafile;
	tujuan = 'pmn_kontrakjual_slave_v2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					loadfiles(nokontrak);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loadfiles(nokontrak) {
	
    nokontrak = document.getElementById('noKtrk').value;
    param       = 'method=loadfiles&nokontrak='+trim(nokontrak);
	tujuan      = 'pmn_kontrakjual_slave_v2.php';
	// alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					// loaddatadetail();
					// document.getElementById('listfiles').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



maxf=0
sekarang=1;
function simpandtall(maxRow){  	
	maxf=maxRow;
	loopsave(1,maxRow);
}



function loopsave(currRow,maxRow) {
    param = "";
	nokontrak = document.getElementById('noKtrk').value;
	kodebarang = document.getElementById('kdBrg').value;
    pasal=document.getElementById('pasal'+currRow).value;
    keterangan=document.getElementById('keterangan'+currRow).value;
	method='simpandt';
    param='kodebarang='+kodebarang+'&pasal='+pasal+'&keterangan='+keterangan+'&nokontrak='+nokontrak;
	param += '&method=' + method;
	
	// alert(param);return;
	tujuan = 'pmn_kontrakjual_slave_v2.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row'+currRow).style.backgroundColor='';
	document.getElementById('row'+currRow).style.backgroundColor='cyan';
   
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					 document.getElementById('row'+currRow).style.backgroundColor='red';
					unlockScreen();
                } else {
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
						alert('Done');
						// datadetail();
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




function simpandt(no){
    nokontrak = document.getElementById('noKtrk').value;
	kodebarang = document.getElementById('kdBrg').value;
    pasal=document.getElementById('pasal'+no).value;
    keterangan=document.getElementById('keterangan'+no).value;
	method='simpandt';
    param='kodebarang='+kodebarang+'&pasal='+pasal+'&keterangan='+keterangan+'&nokontrak='+nokontrak;
	param += '&method=' + method;
    tujuan='pmn_kontrakjual_slave_v2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					// datadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function updatedt(no){
    nokontrak = document.getElementById('noKtrk').value;
	kodebarang = document.getElementById('kdBrg').value
    pasal=document.getElementById('pasal'+no).value;
    keterangan=document.getElementById('keterangan'+no).value;
	method='updatedt';
    param='kodebarang='+kodebarang+'&pasal='+pasal+'&keterangan='+keterangan+'&nokontrak='+nokontrak;
	param += '&method=' + method;
    tujuan='pmn_kontrakjual_slave_v2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					// datadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}


function deletedt(nokontrak,kodebarang,pasal){
	method='deletedt';
    param='kodebarang='+kodebarang+'&pasal='+pasal+'&nokontrak='+nokontrak;
	param += '&method=' + method;
    tujuan='pmn_kontrakjual_slave_v2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					// datadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}




function datadetail() {
	nokontrak = document.getElementById('noKtrk').value;
	kodebarang = document.getElementById('kdBrg').value;
	param = 'method=datadetail' + '&nokontrak=' + nokontrak+ '&kodebarang=' + kodebarang;
	tujuan = 'pmn_kontrakjual_slave_v2.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('datadetail').innerHTML = con.responseText;
					loadfiles(nokontrak);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function newdata(){
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	document.getElementById('detail').style.display='none';
	cancelht();
}

function displaylist() {
	// clearFrom();
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('header').style.display = 'none';
	document.getElementById('kodecustomersch').value='';
	document.getElementById('nokontraksch').value='';
    document.getElementById('tanggalmulaisch').value='';
    document.getElementById('tanggalselesaisch').value='';
    document.getElementById('kodeptsch').value='';
	loaddata(0);
}


function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}


function loaddata(num) {
	nokontraksch=document.getElementById('nokontraksch').value;
	tanggalmulaisch=document.getElementById('tanggalmulaisch').value;
	tanggalselesaisch=document.getElementById('tanggalselesaisch').value;
	kodeptsch=document.getElementById('kodeptsch').value;
	kodecustomersch=document.getElementById('kodecustomersch').value;

	if(tanggalmulaisch>tanggalselesaisch){
		alertify.alert('Informasi','Tanggal dari tidak boleh lebih besar dari tanggal sampai.');return;
	}
	if(tanggalselesaisch<tanggalmulaisch){
		alertify.alert('Informasi','Tanggal sampai tidak boleh lebih kecil dari tanggal dari.');return;
	}

	param = 'method=loaddata&page=' + num;
	param += '&nokontraksch=' + nokontraksch+'&kodeptsch=' + kodeptsch+'&kodecustomersch=' + kodecustomersch;
	param += '&tanggalmulaisch=' + tanggalmulaisch+'&tanggalselesaisch=' + tanggalselesaisch;
	tujuan = 'pmn_kontrakjual_slave_v2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi',con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];

                    // loadfiles(nokontrak);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function cancelht(){
	document.getElementById('method').value='insert';
	
	document.getElementById('noreferensi').value='';
	document.getElementById('noKtrk').value='';
	document.getElementById('tlgKntrk').value='';
	document.getElementById('kdPt').value='';
	document.getElementById('kdPt').disabled=false;
	document.getElementById('millcode').value='';
	// document.getElementById('millcode').value='';
	document.getElementById('daerahctr').value='';
	document.getElementById('noext').value='';
	document.getElementById('custId').value='';
	document.getElementById('berikat').checked=false;
	document.getElementById('millcode').disabled=false;
	
	// document.getElementById('kdBrg').value='';
	document.getElementById('stn').value='';
	// document.getElementById('nilaikontrak').value='0';
	document.getElementById('HrgStn').value='0';
	document.getElementById('jmlh').value='0';
	document.getElementById('kurs').value='IDR';
	document.getElementById('ppnId').value='';
	document.getElementById('tBlg').innerHTML='';
	
	// document.getElementById('tanggalmuat1').value='';
	// document.getElementById('tanggalmuat2').value='';
	
	document.getElementById('tglKrm0').value='';
	document.getElementById('tglSd0').value='';
	document.getElementById('jmlh0').value='';
	document.getElementById('tglKrm1').value='';
	document.getElementById('tglSd1').value='';
	document.getElementById('jmlh1').value='';
	document.getElementById('tglKrm2').value='';
	document.getElementById('tglSd2').value='';
	document.getElementById('jmlh2').value='';
	document.getElementById('tglKrm3').value='';
	document.getElementById('tglSd3').value='';
	document.getElementById('jmlh3').value='';
	
	document.getElementById('tmbngn').value='';
	document.getElementById('ffa').value='';
	document.getElementById('mdani').value='';	
	document.getElementById('dirt').value='';
	document.getElementById('grading').value='';
	document.getElementById('dobi').value='';
	document.getElementById('moist').value='';	
	// document.getElementById('impu').value='';
	document.getElementById('tlransi').value='';
	document.getElementById('syrtByr').value='';
	document.getElementById('ketplns').value='';	
	document.getElementById('tglByr').value='';
	document.getElementById('termbyr').value='';
	document.getElementById('byrKe').value='';
	document.getElementById('tndtng').value='';	
	document.getElementById('tppenjualan').value='';
	document.getElementById('cttnLain').value='';
	// document.getElementById('tipejualbeli').value='';
	
	// getDataCust();
}




function dataKeExcel(ev,tujuan,nokontrak)
{
        judul='Report Ms.Excel';	
        param='nokontrak='+nokontrak+'&proses=excel';
        printFile(param,tujuan,judul,ev)	
}


function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   showDialog1(title,content,width,height,ev); 	
}
function formDetail(nokontrak,ev){
   title="Add "+nokontrak;
   width='780';
   height='320';
   content="<div id=continerform style=width:600;height:320;overflow:auto;> </div>";
   showDialog1(title,content,width,height,ev); 	
}
function addDetail(nokontrak,totKnrtk,komoditi,ev){
	formDetail(nokontrak,ev)
	param='method=getFormDet'+'&nokontrak='+nokontrak;
	param+='&totKontrak='+totKnrtk+'&komoditi='+komoditi;
        //alert(param);
	tujuan='pmn_kontrakjual_slave_v2.php';
	function respog(){
            if(con.readyState==4)
            {
                    if (con.status == 200) 
                      {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                        }
                        else {
                                //alert(con.responseText);
                                document.getElementById('continerform').innerHTML=con.responseText;
                                document.getElementById('nokntr_ref2').value="";
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }	
    } 	
		 post_response_text(tujuan, param, respog);
}
function loadNewData(){
        param='method=LoadNew';
        tujuan='pmn_kontrakjual_slave_v2.php';
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
                                //alert(con.responseText);
                                document.getElementById('containerlist').innerHTML=con.responseText;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }	
         } 	
		 post_response_text(tujuan, param, respog);
}
function cariBast(num){				
				txtSearch=document.getElementById('txtnokntrk').value;
				ptSch=document.getElementById('ptSch').value;
				param='txtSearch='+txtSearch+'&ptSch='+ptSch+'&method=LoadNew'
                param+='&page='+num;
                tujuan = 'pmn_kontrakjual_slave_v2.php';
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
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	
}
function saveKP(){
	
        noKntrk=document.getElementById('noKtrk').value;
        noKtrk_M=document.getElementById('noKtrk_M').value;
        custid=document.getElementById('custId').value;
        tglkntr=document.getElementById('tlgKntrk').value;
        tglberlaku=document.getElementById('tglberlaku').value;
		/*detail barang*/
        kdbrg=document.getElementById('kdBrg').value;
        satuan=document.getElementById('stn').value;
        HrgStn=remove_comma_var(document.getElementById('HrgStn').value);
        tBlg=document.getElementById('tBlg').innerHTML;
        qty=remove_comma_var(document.getElementById('jmlh').value);
        ppn=document.getElementById('ppnId');
        ppn=ppn.options[ppn.selectedIndex].value;
		noext=document.getElementById('noext').value;
		posisictr=document.getElementById('posisictr').options[document.getElementById('posisictr').selectedIndex].value;
		daerahctr=document.getElementById('daerahctr').options[document.getElementById('daerahctr').selectedIndex].value;
		millcode=document.getElementById('millcode').value;
	
		
        param='noKntrk='+noKntrk+'&noKtrk_M='+noKtrk_M+'&custId='+custid+'&tlgKntrk='+tglkntr+'&tglberlaku='+tglberlaku+'&kdBrg='+kdbrg+'&noext='+noext+'&millcode='+millcode;
        param+='&satuan='+satuan+'&tBlg='+tBlg+'&qty='+qty+'&HrgStn='+HrgStn+'&ppnId='+ppn+'&posisictr='+posisictr+'&daerahctr='+daerahctr;
		
		/*tanggal dan jumlah penyerahan */
        tglKrm0=document.getElementById('tglKrm0').value;
        tglKrm1=document.getElementById('tglKrm1').value;
        tglKrm2=document.getElementById('tglKrm2').value;
        tglKrm3=document.getElementById('tglKrm3').value;
        tglSd0=document.getElementById('tglSd0').value;
        tglSd1=document.getElementById('tglSd1').value;
        tglSd2=document.getElementById('tglSd2').value;
        tglSd3=document.getElementById('tglSd3').value;
			persenppn=document.getElementById('persenppn').value;
        persenppn=remove_comma_var(document.getElementById('persenppn').value);
        jmlh0=remove_comma_var(document.getElementById('jmlh0').value);
        jmlh1=remove_comma_var(document.getElementById('jmlh1').value);
        jmlh2=remove_comma_var(document.getElementById('jmlh2').value);
        jmlh3=remove_comma_var(document.getElementById('jmlh3').value);
        param+='&tglKrm0='+tglKrm0+'&tglKrm1='+tglKrm1+'&tglKrm2='+tglKrm2;
        param+='&tglKrm3='+tglKrm3+'&tglSd0='+tglSd0+'&tglSd1='+tglSd1;
        param+='&tglSd2='+tglSd2+'&tglSd3='+tglSd3+'&jmlh0='+jmlh0;
        param+='&jmlh1='+jmlh1+'&jmlh2='+jmlh2+'&jmlh3='+jmlh3;
		
		/*toleransi,kualitas dan franco*/
        tlransi=document.getElementById('tlransi').value;
        franco=document.getElementById('tmbngn');
        // franco=franco.options[franco.selectedIndex].value;
        nmperson=document.getElementById('nmPerson');
        nmperson=nmperson.options[nmperson.selectedIndex].value;
        kualitasffa=document.getElementById('ffa').value;
        kualitasdob=document.getElementById('dobi').value;
        kualitasmdani=document.getElementById('mdani').value;
        moist=document.getElementById('moist').value;
        dirt=document.getElementById('dirt').value;        
        grading=document.getElementById('grading').value;   
        param+='&tlransi='+tlransi+'&franco='+franco+'&kualitasffa='+kualitasffa+'&persenppn='+persenppn;
        param+='&kualitasdob='+kualitasdob+'&kualitasmdani='+kualitasmdani+'&nmPerson='+nmperson;
        param+='&moist='+moist+'&dirt='+dirt+'&grading='+grading;
        
		
		/*syart,term pembayaran*/
        syrtByr=document.getElementById('syrtByr');
        syrtByr=syrtByr.options[syrtByr.selectedIndex].value;
        termbyr=document.getElementById('termbyr');
        termbyr=termbyr.options[termbyr.selectedIndex].value;
		ketdp=document.getElementById('ketdp').value;
		ketplns=document.getElementById('ketplns').value;
        byrKe=document.getElementById('byrKe').value;
        // byrKe=document.getElementById('byrKe');
        // byrKe=byrKe.options[byrKe.selectedIndex].value;
		tndtng=document.getElementById('tndtng').value;
        tndtngJbtn=document.getElementById('tndtngJbtn').value;
        tndtngPembli=document.getElementById('tndtngPembli').value;
        jtbnPembli=document.getElementById('jtbnPembli').value;
        cttnLain=document.getElementById('cttnLain').value;
        kdPt=document.getElementById('kdPt').value;
        kurs=document.getElementById('kurs').value;
        tglbayar=document.getElementById('tglByr').value;
        met=document.getElementById('method').value;
		kntrk=document.getElementById('noreferensi').value;
        // kntrk=kntrk.options[kntrk.selectedIndex].value;
		
		forcemajuere=document.getElementById('forcemajuere').value;
		perselisihan=document.getElementById('perselisihan').value;

        tppenjualan=document.getElementById('tppenjualan').options[document.getElementById('tppenjualan').selectedIndex].value;
		
		berikat=document.getElementById('berikat');
		if(berikat.checked==true)
		   berikat=1;
		else
		   berikat=0;   
		
        
        param+='&method='+met+'&syrtByr='+syrtByr+'&ketdp='+ketdp+'&ketplns='+ketplns+'&byrKe='+byrKe+'&tndtng='+tndtng;
        param+='&tndtngJbtn='+tndtngJbtn+'&tndtngPembli='+tndtngPembli+'&kurs='+kurs;
        param+='&jtbnPembli='+jtbnPembli+'&cttnLain='+cttnLain+'&kdPt='+kdPt+'&tglByr='+tglbayar+'&kntrkRef='+kntrk;
		param+='&berikat='+berikat+'&forcemajuere='+forcemajuere+'&perselisihan='+perselisihan+'&termbyr='+termbyr+'&tppenjualan='+tppenjualan;
		
		
		// if(byrKe == ""){
		// 	alert("Field bayar ke, harus diisi.");
		// 	return false;
		// }
		
		// if(posisictr == ""){
			// alert("Posisi kontrak harus dipilih.");
			// return false;
		// }
		
        tujuan='pmn_kontrakjual_slave_v2.php';
        
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
                                                        // console.log(con.responseText);
                                                        document.getElementById('noKtrk').value=con.responseText;
                                                        // loadNewData();
                                                        // clearFrom();

														// document.getElementById('detail').style.display = 'block';
														// datadetail();
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 
         if(confirm("Are you sure?"))	
         {
                post_response_text(tujuan, param, respog);


         }

}

function clearFrom(){
        location.reload();
}
function getSatuan(kdbrg,cust,sat)
{
        if((kdbrg==0)||(cust==0)||(sat==0))
        {
                kdBrg=document.getElementById('kdBrg').value;
                param='kdBrg='+kdBrg+'&method=getSatuan';
        }
        else
        {
                kdBrg=kdbrg;
                satuan=sat;
                param='kdBrg='+kdBrg+'&method=getSatuan'+'&satuan='+satuan;
        }

        //alert(param);
        tujuan='pmn_kontrakjual_slave_v2.php';

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
                                                        //alert(con.responseText);
                                                        document.getElementById('stn').innerHTML=con.responseText;
                                                            if(cust!=0){
                                                                    getDataCust(cust);
                                                            }

                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	
        post_response_text(tujuan, param, respog);
}
function copyFromLast()
{
        param='method=getLastData';
        tujuan='pmn_kontrakjual_slave_v2.php';
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
                                                        //alert(con.responseText);
                                                        document.getElementById('noKtrk').disabled=false;
                                                        ar=con.responseText.split("###");
                                                        document.getElementById('noKtrk').value=ar[0];
                                                        document.getElementById('custId').value=ar[1];
                                                        document.getElementById('tlgKntrk').value=ar[2];
                                                        document.getElementById('kdBrg').value=ar[3];
                                                        document.getElementById('HrgStn').value=ar[4];
                                                        document.getElementById('tBlg').value=ar[5];
                                                        document.getElementById('jmlh').value=ar[6];
                                                        document.getElementById('tglKrm').value=ar[7];
                                                        document.getElementById('tglSd').value=ar[8];
                                                        document.getElementById('tlransi').value=ar[9];
                                                        document.getElementById('noDo').value=ar[10];
                                                        document.getElementById('kualitas').value=ar[11];
                                                        document.getElementById('syrtByr').value=ar[12];
                                                        document.getElementById('tndtng').value=ar[13];
                                                        document.getElementById('tmbngn').value=ar[14];
                                                        document.getElementById('cttn1').value=ar[15];
                                                        document.getElementById('cttn2').value=ar[16];
                                                        document.getElementById('cttn3').value=ar[17];
                                                        document.getElementById('cttn4').value=ar[18];
                                                        document.getElementById('cttn5').value=ar[19];
                                                        document.getElementById('othCttn').value=ar[20];
                                                        getSatuan(ar[3],ar[1],ar[21]);
                                                        document.getElementById('kdPt').value=ar[22];

                                                        //document.getElementById('stn').value;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	
        post_response_text(tujuan, param, respog);
}
function getDataCust(dt)
{
        if(dt==0)
        {
                custId=document.getElementById('custId').value;
        }
        else
        {
                custId=dt;
        }
        param='method=getCust'+'&custId='+custId;
        tujuan='pmn_kontrakjual_slave_v2.php';
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
                                                        //alert(con.responseText);
                                                        ar=con.responseText.split("###");
                                                        document.getElementById('nmPerson').innerHTML=ar[0];
                                                        document.getElementById('kdBrg').innerHTML=ar[1];
														 document.getElementById('berikat').disabled=false;
														 document.getElementById('persenppn').disabled=false;
														if(ar[2]=='1'){
															
															document.getElementById('berikat').checked=true;
															document.getElementById('persenppn').value=0;
															// document.getElementById('persenppn').disabled=false;
														}else{
														   document.getElementById('berikat').checked=false;
														   document.getElementById('persenppn').value=10;
															// document.getElementById('persenppn').disabled=false;
														}
														document.getElementById('tlransi').value=ar[3];
														
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	
        post_response_text(tujuan, param, respog);
}
function fillField(nokntrk,noKtrk_M)
{
        noKntrk=nokntrk;
        param='method=getEditData'+'&noKntrk='+noKntrk+'&noKtrk_M='+noKtrk_M;
        tujuan='pmn_kontrakjual_slave_v2.php';
        tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
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
													
													document.getElementById('listdata').style.display='none';
													document.getElementById('header').style.display='block';
													
                                                        //alert(con.responseText);
                                                    document.getElementById('method').value='update';
                                                   // alert(con.responseText);
                                                    ar=con.responseText.split("###");
                                                    // console.log(ar);
                                                    document.getElementById('noKtrk').value=ar[0];
                                                    document.getElementById('custId').value=ar[1];
                                                    document.getElementById('tlgKntrk').value=ar[2];
                                                    /*detail barang*/
                                                    document.getElementById('kdBrg').innerHTML=ar[3];
                                                    document.getElementById('stn').innerHTML=ar[4];
                                                    document.getElementById('HrgStn').value=ar[5];
                                                    document.getElementById('kurs').value=ar[6];
                                                    document.getElementById('tBlg').innerHTML=ar[7];
                                                    document.getElementById('jmlh').value=ar[8];

                                                    /*tanggal dan jumlah penyerahan */
                                                    document.getElementById('tglKrm0').value=ar[9];
                                                    document.getElementById('tglSd0').value=ar[10];
                                                    document.getElementById('tglKrm1').value=ar[11];
                                                    document.getElementById('tglSd1').value=ar[12];
                                                    document.getElementById('tglKrm2').value=ar[13];
                                                    document.getElementById('tglSd2').value=ar[14];
                                                    document.getElementById('tglKrm3').value=ar[15];
                                                    document.getElementById('tglSd3').value=ar[16];
                                                    document.getElementById('jmlh0').value=ar[17];
                                                    document.getElementById('jmlh1').value=ar[18];
                                                    document.getElementById('jmlh2').value=ar[19];
                                                    document.getElementById('jmlh3').value=ar[20];

                                                    /*toleransi,kualitas dan franco*/
                                                    document.getElementById('tmbngn').value=ar[21];
                                                    document.getElementById('ffa').value=ar[22];
                                                    document.getElementById('dobi').value=ar[23];
                                                    document.getElementById('mdani').value=ar[24];
                                                    document.getElementById('tlransi').value=ar[25];

                                                    /*syart,term pembayaran*/
                                                    document.getElementById('syrtByr').value=ar[26];
                                                    document.getElementById('byrKe').innerHTML=ar[27];
                                                    document.getElementById('tndtng').value=ar[28];
                                                    document.getElementById('tndtngJbtn').value=ar[29];
                                                    document.getElementById('tndtngPembli').value=ar[30];
                                                    document.getElementById('jtbnPembli').value=ar[31];
                                                    document.getElementById('cttnLain').value=ar[32];
                                                    document.getElementById('nmPerson').innerHTML=ar[33];
                                                    jk=document.getElementById('kdPt');
                                                    for(x=0;x<jk.length;x++){
                                                                    if(jk.options[x].value==ar[34])
                                                                    {
                                                                                    jk.options[x].selected=true;
                                                                    }
                                                    }
                                                    jk.disabled=true;
                                                    jk2=document.getElementById('ppnId');
                                                    for(x=0;x<jk2.length;x++){
                                                                    if(jk2.options[x].value==ar[35])
                                                                    {
                                                                                    jk2.options[x].selected=true;
                                                                    }
                                                    }
                                                    document.getElementById('tglByr').value=ar[36];
                                                    //alert(ar[3]);
                                                    document.getElementById('moist').value=ar[37];
                                                    document.getElementById('dirt').value=ar[38];
                                                    document.getElementById('grading').value=ar[39];
													document.getElementById('kntrkRef').innerHTML=ar[40];
													document.getElementById('ketdp').innerHTML=ar[41];
													document.getElementById('ketplns').innerHTML=ar[42];
													// alert(ar[42]);
													
													
													if(ar[43]=='1'){
												    document.getElementById('berikat').checked=true;
													}else{
													   document.getElementById('berikat').checked=false;
													}
													
													document.getElementById('forcemajuere').innerHTML=ar[44];
													document.getElementById('perselisihan').innerHTML=ar[45];
													document.getElementById('noext').innerHTML=ar[46];
													document.getElementById('posisictr').innerHTML=ar[47];
													document.getElementById('daerahctr').innerHTML=ar[48];
													document.getElementById('noKtrk_M').value=ar[49];
													document.getElementById('termbyr').value=ar[50];
													document.getElementById('millcode').value=ar[51];
                                                    document.getElementById('tppenjualan').value=ar[52];
                                                    document.getElementById('persenppn').value=ar[53];
                                                    document.getElementById('tglberlaku').value=ar[54];
													
                                                    loaddata(0);
													// document.getElementById('detail').style.display = 'block';
                                                    
													// datadetail();
													
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
  

                                        }
                      }	
         } 	
        post_response_text(tujuan, param, respog);
}

function delData(nokontrk)
{
        noKntrk=nokontrk;
        param='method=dataDel'+'&noKntrk='+noKntrk;
        // alert(param);
        tujuan='pmn_kontrakjual_slave_v2.php';
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
                                                        //alert(con.responseText);
                                                        //document.getElementById('stn').innerHTML=con.responseText;
                                                        clearFrom();
                                                        //tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
                                                        // cariNoKntrk();
														document.getElementById('method').value='insert';

                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 
         if(confirm("Are you sure?"))	
         {
                post_response_text(tujuan, param, respog);
                loaddata(0);
         }

}
function cariNoKntrk()
{
        txtSearch=document.getElementById('txtnokntrk').value;
        ptSch=document.getElementById('ptSch').value;
        //param='txtSearch='+txtSearch+'&method=cariNokntrk';
        param='txtSearch='+txtSearch+'&ptSch='+ptSch+'&method=LoadNew';
        
    //
        tujuan='pmn_kontrakjual_slave_v2.php';
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
                                                        //alert(con.responseText);
                                                        //document.getElementById('stn').innerHTML=con.responseText;
                                                        //clearFrom();
                                                        //tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
                                                        //tabAction(document.getElementById('tabFRM1'),0,'FRM',1);	
                                                        document.getElementById('containerlist').innerHTML=con.responseText;

                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 
}
function getRek(){
		pt=document.getElementById('kdPt');
		pt=pt.options[pt.selectedIndex].value;
		param='kdpt='+pt+'&method=getRek';
		tujuan='pmn_kontrakjual_slave_v2.php';
        post_response_text(tujuan, param, respog);
        function respog(){
			if(con.readyState==4){
				if (con.status == 200){
								busy_off();
								if (!isSaveResponse(con.responseText)) {
										alert(con.responseText);
								}
								else {
										dert=con.responseText.split("####");
										document.getElementById('byrKe').innerHTML=dert[0];
										document.getElementById('kntrkRef').innerHTML=dert[1];

								}
						}
						else {
								busy_off();
								error_catch(con.status);
				}
			}	
         } 
}

function getBerat(){
	var isi;
	isi=document.getElementById('jmlh').value;
	document.getElementById('jmlh0').value=isi;
}

function hitungHarga() {
    var hargasatuan = remove_comma_var(getValue('HrgStn')),
        kuantitas = remove_comma_var(getValue('jmlh')),
        container = getById('tmpHarga');
    if (hargasatuan=='') hargasatuan = 0;
    if (kuantitas=='') kuantitas = 0;
    container.value = parseFloat(hargasatuan) * parseFloat(kuantitas);
    rupiahkan(getById('tmpHarga'),'tBlg',true);
}
function saveDet(){
    nokontr=document.getElementById('nokontrak').value;
    jmlhnokontr=document.getElementById('jmlHnokontrak').value;
    nokntrkRef=document.getElementById('nokntr_ref');
    nokntrkRef=nokntrkRef.options[nokntrkRef.selectedIndex].value;
    kuota=document.getElementById('jmlhRef').value;
    nokRef=document.getElementById('nokntr_ref2').value;
    param='method=saveDet'+'&nokontrak='+nokontr+'&jmlHnokontrak='+jmlhnokontr;
    param+='&nokntr_ref='+nokntrkRef+'&jmlhRef='+kuota+'&nokntr_ref2='+nokRef;
    tujuan='pmn_kontrakjual_slave_v2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                }
                else {
                    loadDetail(nokontr);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     } 
    
}
function loadDetail(nokontrak){
    param='method=loadDet'+'&nokontrak='+nokontrak;
    tujuan='pmn_kontrakjual_slave_v2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                }
                else {
                   document.getElementById('isidetail').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     } 
}
function delData2(nokontrak,nokntr_ref){
    param='method=delDet';
    param+='&nokntr_ref='+nokntr_ref+'&nokontrak='+nokontrak;
    tujuan='pmn_kontrakjual_slave_v2.php';
    if(confirm("Anda Yakin Menghapus No.Kontrak induk "+nokntr_ref+"?")){
        post_response_text(tujuan, param, respog);
    }
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                }
                else {
                    loadDetail(nokontrak);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     } 
}
function fillField2(nokontrak,nokntr_ref){
    param='method=editDet';
    param+='&nokntr_ref='+nokntr_ref+'&nokontrak='+nokontrak;
    tujuan='pmn_kontrakjual_slave_v2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                }
                else {
                    isied=con.responseText.split("####");
                    document.getElementById('nokntr_ref').innerHTML=isied[1];
                    document.getElementById('jmlhRef').value=isied[2];
                    document.getElementById('nokntr_ref2').value=isied[3];
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     } 
}

function posting(nokontrak,numrow)
{
    param='method=posting'+'&nokontrak='+nokontrak;
    tujuan='pmn_kontrakjual_slave_v2.php';
    if(confirm('Anda yakin ingin memposting transaksi ini ??'))
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
                                    }
                                    else 
                                    {
                                        //document.getElementById('contain').innerHTML=con.responseText;    
                                        x = document.getElementById('tr_' + numrow);
                                        //x.cells[9].innerHTML = '';
                                        // x.cells[8].innerHTML = '';
                                        // x.cells[9].innerHTML = '';
                                        // x.cells[10].innerHTML = '';
                                        loaddata();
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
              } 
    }
}

function carinorefrensi(title,ev)
{
	content= "<div>";
    content+="<fieldset>Search : <input type=text id=textnoref class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=gocarinorefrensi()>Go</button><p>";
    content+="<div id=containercari style=\"max-height:250px;min-height:auto;overflow:auto;\"></div></fieldset></div>";
    
    width='';
    height='';
    showDialog1(title,content,width,height,ev); 
}

function gocarinorefrensi()
{
	textnoref=document.getElementById('textnoref').value;
	
	if(textnoref.length <= 2)
	{
		alert("No. Referensi too short text. Min 3 Char.");
		return;
	}
	
	param='method=gocarinorefrensi'+'&textnoref='+textnoref;
    tujuan='pmn_kontrakjual_slave_v2.php';
	post_response_text(tujuan, param, respog);  
	
    function respog()
    {
		if(con.readyState==4)
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
					document.getElementById('containercari').innerHTML=con.responseText;
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

function fillnorefrensi(noreferensi,kodeorg,buyer,berikat,komoditi,kuantitas,harga,ppn,paymentdate,bayarke,kualitas1,kualitas2,kualitas3,kualitas4)
{
	closeDialog();
	document.getElementById('noreferensi').value=noreferensi;
	kdPtl=document.getElementById('kdPt');
    for(a=0;a<kdPtl.length;a++){
        if(kdPtl.options[a].value==kodeorg)
            {
                kdPtl.options[a].selected=true;
            }
    }
    custIdl=document.getElementById('custId');
    for(a=0;a<custIdl.length;a++){
        if(custIdl.options[a].value==buyer)
            {
                custIdl.options[a].selected=true;
            }
    }	
	document.getElementById('kurs').value='IDR';
	getSatuan2(komoditi,buyer,'KG');
	if(berikat=='0')
	{
		document.getElementById('berikat').checked=false;
	}
	else
	{
		document.getElementById('berikat').checked=true;
	}
	
	document.getElementById('jmlh').value=kuantitas;
	document.getElementById('HrgStn').value=harga;
	document.getElementById('ppnId').value=ppn;
	
	
	document.getElementById('ffa').value='';
	document.getElementById('mdani').value='';
	document.getElementById('dirt').value='';
	document.getElementById('dobi').value='';
	document.getElementById('moist').value='';
	document.getElementById('grading').value='';
	if(komoditi=='40000001')
	{
		document.getElementById('ffa').value=kualitas1;
		document.getElementById('mdani').value=kualitas2;
		document.getElementById('dirt').value=kualitas3;
	}
	else
	{
		document.getElementById('ffa').value=kualitas4;
		document.getElementById('dobi').value=kualitas1;
		document.getElementById('moist').value=kualitas2;
		document.getElementById('grading').value=kualitas3;
	}
	
	document.getElementById('tglByr').value=paymentdate;
	//document.getElementById('byrKe').value=bayarke;
	byrKel=document.getElementById('byrKe');
    for(a=0;a<byrKel.length;a++){
        if(byrKel.options[a].value==bayarke)
            {
                byrKel.options[a].selected=true;
            }
    }
	document.getElementById('kdPt').disabled=true;
	document.getElementById('custId').disabled=true;
	document.getElementById('berikat').disabled=true;
	document.getElementById('kdBrg').disabled=true;
	document.getElementById('HrgStn').disabled=true;
	document.getElementById('kurs').disabled=true;
	document.getElementById('jmlh').disabled=true;
	document.getElementById('ppnId').disabled=true;
	document.getElementById('ffa').disabled=true;
	document.getElementById('mdani').disabled=true;
	document.getElementById('dirt').disabled=true;
	//document.getElementById('dobi').disabled=true;
	document.getElementById('moist').disabled=true;
	document.getElementById('grading').disabled=true;
	document.getElementById('tlransi').disabled=true;
	document.getElementById('tglByr').disabled=true;
	document.getElementById('byrKe').disabled=true;
}

function getSatuan2(kdbrg,cust,sat)
{
	param='kdBrg='+kdbrg+'&method=getSatuan'+'&satuan='+sat;
	tujuan='pmn_kontrakjual_slave_v2.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
    {
		if(con.readyState==4)
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
					document.getElementById('stn').innerHTML=con.responseText;
                    getDataCust2(cust,kdbrg);
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

function getDataCust2(dt,komoditi)
{
	param='method=getCust'+'&custId='+dt;
    tujuan='pmn_kontrakjual_slave_v2.php';
    function respog()
    {
		if(con.readyState==4)
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
					ar=con.responseText.split("###");
                    document.getElementById('nmPerson').innerHTML=ar[0];
                    document.getElementById('kdBrg').innerHTML=ar[1];
					document.getElementById('tlransi').value=ar[2];
                    document.getElementById('kdBrg').value=komoditi;
					hitungHarga();
				}
			}
			else 
			{
				busy_off();
                error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
	
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
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



function showupload(ev,no) {
	showformupload(ev);
	nopp = document.getElementById('detail_kode'+no).innerHTML;
	param = 'method=showupload&rnopp=' + nopp;
	tujuan = 'pmn_kontrakjual_slave_v2.php';
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
					loadfiles(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfiles(nopp) {
	param = 'method=loadfiles&rnopp=' + nopp;
	tujuan = 'pmn_kontrakjual_slave_v2.php';
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
	var nopp = document.getElementById("noppupload").innerHTML;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("rnopp", nopp);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "pmn_kontrakjual_slave_v2.php?method=submitfile", true);
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
					loadfiles(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(nopp, namafile) {
	param = 'method=deletefile&rnopp=' + nopp + '&namafile=' + namafile;
	tujuan = 'pmn_kontrakjual_slave_v2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function downloadfile(path, filename) {
	param = 'path=' + path + '&filename=' + filename;
	tujuan = 'download.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
*/

function form_ajukan(notransaksi){
	let content = "<fieldset style=width:95%><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	let title   = "Ajukan Kontrak : " + notransaksi;

	alertify.popup(title, content).set({'resizable':true,'maximizable':true}).resizeTo('20%','10%');

	let param = "method=form_ajukan";
	param += "&notransaksi=" + notransaksi;
	let tujuan = "pmn_kontrakjual_slave_v2.php";
	post_response_text(tujuan, param, function(){
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
	});
}

function ajukan(){
	let notransaksi = document.getElementById("notransaksi_ajukan");
	let jlh         = document.getElementById("jlh");

	if(jlh.value == 0){
		alertify.alert("Warning: Approval kosong");
		return;
	}

	let param = "method=ajukan";
	param += "&notransaksi=" + notransaksi.value;
	param += "&jlh=" + jlh.value;

	for (i = 1; i <= jlh.value; i++) {
        console.log("&" + "kepada"+ i + "=" + document.getElementById("kepada" + i).value)
		param += "&" + "kepada"+ i + "=" + document.getElementById("kepada" + i).value;
	}


	let tujuan = "pmn_kontrakjual_slave_v2.php";
	post_response_text(tujuan, param, () => {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.alert('Info', 'Success');
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	});
}






