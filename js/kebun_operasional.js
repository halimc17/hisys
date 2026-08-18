function searchblok(title,ev){
    content="<fieldset><div id=formschblok style=\"min-width:350;\">";
	content+="Search <input type=text class=myinputtext id=schblok /><button class=mybutton onclick=cariblok()>Search</button></div></fieldset>";
	content+="<fieldset><legend><i>Result</i></legend><div id=dataschblok style=\"max-height:250px;overflow:auto;\"></div></fieldset>";
    title='Find Block';
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    //getListBarang();
}

function moveblok(blok){
	document.getElementById('ftPrestasi_kodeorg').firstChild.value=blok;
	closeDialog();
	changeOrg();
}

function cariblok(){
    schblok=document.getElementById('schblok').value;
	divisi=document.getElementById('divisi').value;
    param='proses=cariblok'+'&schblok='+schblok+'&divisi='+divisi;
    tujuan = 'kebun_slave_operasional_detail.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                   document.getElementById('dataschblok').innerHTML=con.responseText;
                   //updTahunTanam();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}










var showPerPage = 10;

function getValue(id) {
    var tmp = document.getElementById(id);
    
    if(tmp) {
        if(tmp.options) {
            return tmp.options[tmp.selectedIndex].value;
        } else if(tmp.nodeType=='checkbox') {
            if(tmp.checked==true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return tmp.value;
        }
    } else {
        return false;
    }
}

/* Search
 * Filtering Data
 */
function searchTrans(tipe,tipeVal) {
    var notrans = document.getElementById('sNoTrans');
	 var tipetransaksi = document.getElementById('sTipetransaksi');
	 var tanggal = getValue('sTanggal');
	 var tanggal2 = getValue('sTanggal2');
	 var jurnal = getValue('sJurnal');
	 var divisi = getValue('sDivisi');

	if(tanggal!='') {
        var tmpTanggal = tanggal.split('-');
        var tanggalR = tmpTanggal[2]+"-"+tmpTanggal[1]+"-"+tmpTanggal[0];
    } else {
        var tanggalR = '';
    }
    
     if(tanggal2!='') {
        var tmpTanggal2 = tanggal2.split('-');
        var tanggalR2 = tmpTanggal2[2]+"-"+tmpTanggal2[1]+"-"+tmpTanggal2[0];
    } else {
        var tanggalR2 = '';
    }
	var where = '[["notransaksi","'+notrans.value+'"],["divisi","'+divisi+'"],["tanggal","'+tanggalR+'"],["tanggal2","'+tanggalR2+'"],["jurnal","'+jurnal+'"],["tipetransaksi","'+tipetransaksi.value+'"]]';
    //var where = '[["notransaksi","'+notrans.value+'"],["divisi","'+divisi+'"],["'+tipe+'","'+tipeVal+'"],["tanggal","'+tanggalR+'"],["tanggal2","'+tanggalR2+'"],["jurnal","'+jurnal+'"],["tipetransaksi","'+tipetransaksi.value+'"]]';
	goToPages(1,showPerPage,where);//,
}

/* Paging
 * Paging Data
 */
function defaultList(tipe) {
	document.getElementById('sNoTrans').value='';
	document.getElementById('sTanggal').value='';
	document.getElementById('sTanggal2').value='';
	document.getElementById('sJurnal').value='';
	document.getElementById('sDivisi').value='';
    goToPages(1,showPerPage,'[["tipetransaksi","'+tipe+'"]]',tipe);
}

/*function goToPages(page,shows,where) {
    if(typeof where != 'undefined') {
        var newWhere = where.replace(/'/g,'"');
    }
    var workField = document.getElementById('workField');
    var param = "page="+page;
    param += "&shows="+shows;
    if(typeof where != 'undefined') {
        param+="&where="+newWhere;
    }
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional.php?proses=showHeadList', param, respon);
}*/


function goToPages(page,shows,where,tipe) {
	var notrans = document.getElementById('sNoTrans');
	 var tipetransaksi = document.getElementById('sTipetransaksi');
	 var tanggal = getValue('sTanggal');
	 var tanggal2 = getValue('sTanggal2');
	 var jurnal = getValue('sJurnal');
	 var divisi = getValue('sDivisi');
	
	if(tanggal!='') {
        var tmpTanggal = tanggal.split('-');
        var tanggalR = tmpTanggal[2]+"-"+tmpTanggal[1]+"-"+tmpTanggal[0];
    } else {
        var tanggalR = '';
    }
    
     if(tanggal2!='') {
        var tmpTanggal2 = tanggal2.split('-');
        var tanggalR2 = tmpTanggal2[2]+"-"+tmpTanggal2[1]+"-"+tmpTanggal2[0];
    } else {
        var tanggalR2 = '';
    }
	var where = '[["notransaksi","'+notrans.value+'"],["divisi","'+divisi+'"],["tanggal","'+tanggalR+'"],["tanggal2","'+tanggalR2+'"],["jurnal","'+jurnal+'"],["tipetransaksi","'+tipetransaksi.value+'"]]';
	//var where = '[["notransaksi","'+notrans.value+'"],["divisi","'+divisi+'"],["'+tipe+'","'+tipeVal+'"],["tanggal","'+tanggalR+'"],["tanggal2","'+tanggalR2+'"],["jurnal","'+jurnal+'"],["tipetransaksi","'+tipetransaksi.value+'"]]';
   
	if(typeof where != 'undefined') {
	    var newWhere = where.replace(/'/g,'"');
	}
	
	var workField = document.getElementById('workField');
	var param = "page="+page;
	param += "&shows="+shows+"&tipe="+tipe;
	if(typeof where != 'undefined') {
	    param+="&where="+newWhere;
	}
	
	function respon() {
	    if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					workField.innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
	    }
	}
	post_response_text('kebun_slave_operasional.php?proses=showHeadList', param, respon);
}


function choosePage(obj,shows,where) {
    var pageVal = obj.options[obj.selectedIndex].value;
    goToPages(pageVal,shows,where);
}

/* Halaman Manipulasi Data
 * Halaman add, edit, delete
 */
function showAdd(tipe) {
    var workField = document.getElementById('workField');
    var param = "tipe="+tipe;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional.php?proses=showAdd', param, respon);
}

function showEditFromAdd(tipe) {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('notransaksi');
    var param = "notransaksi="+trans.value;
    param+="&tipe="+tipe;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                    showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional.php?proses=showEdit', param, respon);
}

function showEdit(num,tipe) {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('notransaksi_'+num);
    var param = "numRow="+num+"&notransaksi="+trans.getAttribute('value');
    param+="&tipe="+tipe;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					workField.innerHTML = con.responseText;
                    showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional.php?proses=showEdit', param, respon);
}

/* Manipulasi Data
 * add, edit, delete
 */
function addDataTable(tipe) {
    var param = "notransaksi="+getValue('notransaksi')+"&kodeorg="+getValue('kodeorg');
    param += "&tanggal="+getValue('tanggal')+"&nikmandor="+getValue('nikmandor');
    param += "&nikmandor1="+getValue('nikmandor1')+"&nikasisten="+getValue('nikasisten');
    param += "&keranimuat="+getValue('keranimuat');
    param += "&nobkm="+getValue('nobkm');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('notransaksi').value = con.responseText;
                   // alert('Added Data Header');
                    showEditFromAdd(tipe);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional.php?proses=add&tipe='+tipe, param, respon);
}

function gantikegiatan(){
    var param = "kodekegiatan="+getValue('kodekegiatan');
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('nik').innerHTML = con.responseText;
					getstpremi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    var satuan = JSON.parse(getValue('satuan')),
		kegiatan = getValue('kodekegiatan'),
		satuanField = getById('hasilkerja_satuan');
	satuanField.value = satuan[kegiatan];
    post_response_text('kebun_slave_operasional_detail.php?proses=gantikegiatan', param, respon);
}



function getstpremi(){//indra
	kodekegiatan=document.getElementById('ftPrestasi_kodekegiatan').firstChild.value;
	notransaksi=document.getElementById('notransaksi').value;
	param='proses=getstpremi'+'&kodekegiatan='+kodekegiatan+'&notransaksi='+notransaksi;
    tujuan = 'kebun_slave_operasional_detail.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
				//	alert(con.responseText);
                 if(con.responseText==0){
					document.getElementById('ftAbsensi_insentif').firstChild.disabled=false;
				 }else{
					 document.getElementById('ftAbsensi_insentif').firstChild.disabled=true;
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

function editDataTable(tipe) {
    var param = "notransaksi="+getValue('notransaksi')+"&kodeorg="+getValue('kodeorg');
    param += "&tanggal="+getValue('tanggal')+"&nikmandor="+getValue('nikmandor');
    param += "&nikmandor1="+getValue('nikmandor1')+"&nikasisten="+getValue('nikasisten');
    param += "&keranimuat="+getValue('keranimuat');
    param += "&nobkm="+getValue('nobkm');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    defaultList(tipe);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional.php?proses=edit', param, respon);
}

/*
 * Detail
 */

function showDetail() {
	var detailField = document.getElementById('detailField');
    var notrans = document.getElementById('notransaksi').value;
    var afdeling = getValue('kodeorg');
    var param = "notransaksi="+notrans+"&afdeling="+afdeling;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    detailField.innerHTML = con.responseText;
					setValue('hasilkerja_satuan',getValue('firstSatuan'));
                    initUmrIns();
					getGudang();
					theFT.afterCrud='hiddenDiv';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional_detail.php?proses=showDetail', param, respon);
}

function showDetail2() {
	var detailField = document.getElementById('detailField');
    var notrans = document.getElementById('notransaksi').value;
    var afdeling = getValue('kodeorg');
    var param = "notransaksi="+notrans+"&afdeling="+afdeling;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					detailField.innerHTML = con.responseText;
                    setValue('hasilkerja_satuan',getValue('firstSatuan'));//tambahan jamhari untuk ngunci icon editnya muncul
                    initUmrIns();//tambahan jamhari untuk ngunci icon editnya muncul
                    getGudang();//tambahan jamhari untuk ngunci icon editnya muncul
                    theFT.afterCrud='hiddenDiv';//tambahan jamhari untuk ngunci icon editnya muncul
                    tabAction(document.getElementById('tabFRM1'),1,'FRM',2);

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional_detail.php?proses=showDetail', param, respon);
}

function deleteData(num) {
    var notrans = document.getElementById('notransaksi_'+num).getAttribute('value');
    var param = "notransaksi="+notrans;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    var tmp = document.getElementById('tr_'+num);
                    tmp.parentNode.removeChild(tmp);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }


    if(confirm('Hapus transaksi '+notrans+'. Anda yakin?')) {
        post_response_text('kebun_slave_operasional.php?proses=delete', param, respon);
    }
}

/* Update No Urut di halaman absensi
 */
function updNoUrut() {
    var tabBody = document.getElementById('mTabBody');
    var nourut = document.getElementById('nourut');
    var maxNum = 0;
    
    if(tabBody.childNodes.length>0) {
        for(i=0;i<tabBody.childNodes.length;i++) {
            var tmp = document.getElementById('nourut_'+i);
            if(tmp.innerHTML > maxNum) {
                maxNum = tmp.innerHTML;
            }
        }
    }
    nourut.value = parseInt(maxNum)+1;
}

/* Init Total HK, UMR dan Insentif
 */
function initUmrIns() {
    var numRow = 0;
    var totalPresHk = document.getElementById('totalPresHk');
    var totalAbsHk = document.getElementById('totalAbsHk');
    var totalPresUmr = document.getElementById('totalPresUmr');
    var totalAbsUmr = document.getElementById('totalAbsUmr');
    var totalPresIns = document.getElementById('totalPresIns');
    var totalAbsIns = document.getElementById('totalAbsIns');
    var idPres = 'ftPrestasi';var idAbs = 'ftAbsensi';
    
    // Init Total Value
    totalPresHk.setAttribute('realValue',0);
    totalPresHk.value = 0;
    totalAbsHk.setAttribute('realValue',0);
    totalAbsHk.value = 0;
    totalPresUmr.setAttribute('realValue',0);
    totalPresUmr.value = 0;
    totalAbsUmr.setAttribute('realValue',0);
    totalAbsUmr.value = 0;
    totalPresIns.setAttribute('realValue',0);
    totalPresIns.value = 0;
    totalAbsIns.setAttribute('realValue',0);
    totalAbsIns.value = 0;
    
    // Get Prestasi Value
    while(document.getElementById("tr_"+idPres+"_"+numRow)) {
        tmpHk = document.getElementById(idPres+"_jumlahhk_"+numRow).getAttribute('value');
        // tmpUmr = document.getElementById(idPres+"_umr_"+numRow).getAttribute('value');
        tmpUmr = 0;
        tmpIns = document.getElementById(idPres+"_upahpremi_"+numRow).getAttribute('value');
        
        totalPresHk.setAttribute('realValue',(parseFloat(totalPresHk.getAttribute('realValue')) + parseFloat(tmpHk)));
        totalPresHk.value = totalPresHk.getAttribute('realValue');
        totalPresUmr.setAttribute('realValue',(parseFloat(totalPresUmr.getAttribute('realValue')) + parseFloat(tmpUmr)));
        totalPresUmr.value = totalPresUmr.getAttribute('realValue');
        totalPresIns.setAttribute('realValue',(parseFloat(totalPresIns.getAttribute('realValue')) + parseFloat(tmpIns)));
        totalPresIns.value = totalPresIns.getAttribute('realValue');
        change_number(totalPresHk);
        change_number(totalPresUmr);
        change_number(totalPresIns);
        numRow++;
    }
    
	if(numRow > 0){
		hiddenDiv();
	}
	
    numRow = 0;
    // Get Absensi Value
    while(document.getElementById("tr_"+idAbs+"_"+numRow)) {
        tmpHk = document.getElementById(idAbs+"_jhk_"+numRow).getAttribute('value');
        // tmpUmr = document.getElementById(idAbs+"_umr_"+numRow).getAttribute('value');
        tmpUmr = 0;
        tmpIns = document.getElementById(idAbs+"_insentif_"+numRow).getAttribute('value');
        
        totalAbsHk.setAttribute('realValue',(parseFloat(totalAbsHk.getAttribute('realValue')) + parseFloat(tmpHk)));
        totalAbsHk.value = totalAbsHk.getAttribute('realValue');
        totalAbsUmr.setAttribute('realValue',(parseFloat(totalAbsUmr.getAttribute('realValue')) + parseFloat(tmpUmr)));
        totalAbsUmr.value = totalAbsUmr.getAttribute('realValue');
        totalAbsIns.setAttribute('realValue',(parseFloat(totalAbsIns.getAttribute('realValue')) + parseFloat(tmpIns)));
        totalAbsIns.value = totalAbsIns.getAttribute('realValue');
        change_number(totalAbsHk);
        change_number(totalAbsUmr);
        change_number(totalAbsIns);
        numRow++;
    }
}

/* Total HK, UMR atau Insentif
 */
function totalVal() {
    initUmrIns();
    
    var totalPresHk = document.getElementById('totalPresHk');
    var totalAbsHk = document.getElementById('totalAbsHk');
    var totalPresUmr = document.getElementById('totalPresUmr');
    var totalAbsUmr = document.getElementById('totalAbsUmr');
    var totalPresIns = document.getElementById('totalPresIns');
    var totalAbsIns = document.getElementById('totalAbsIns');
    var modeArr = new Array('Pres','Abs');
    var varArr = new Array('Hk','Umr','Ins');
    
    var PresHk = document.getElementById('ftPrestasi_jumlahhk').firstChild;
    // var PresUmr = document.getElementById('ftPrestasi_umr').firstChild;
    var PresUmr = 0;
    var PresIns = document.getElementById('ftPrestasi_upahpremi').firstChild;
    var AbsHk = document.getElementById('ftAbsensi_jhk').firstChild;
    var AbsUmr = document.getElementById('ftAbsensi_umr').firstChild;
    var AbsIns = document.getElementById('ftAbsensi_insentif').firstChild;
    
    var cancelPres = document.getElementById('clearFTBtn_ftPrestasi');
    var cancelAbs = document.getElementById('clearFTBtn_ftAbsensi');
    
    var numRowPres = document.getElementById('ftPrestasi_numRow').value;
    var numRowAbs = document.getElementById('ftAbsensi_numRow').value;
    
    // if NaN
    if(isNaN(parseFloat(PresHk.value))) {PresHk.value = 0;}
    if(isNaN(parseFloat(PresUmr.value))) {PresUmr.value = 0;}
    if(isNaN(parseFloat(PresIns.value))) {PresIns.value = 0;}
    if(isNaN(parseFloat(AbsHk.value))) {AbsHk.value = 0;}
    if(isNaN(parseFloat(AbsUmr.value))) {AbsUmr.value = 0;}
    if(isNaN(parseFloat(AbsIns.value))) {AbsIns.value = 0;}
    
    // Prestasi
    if(cancelPres.style.display=='') {
        var tmpRowHk = document.getElementById('ftPrestasi_jumlahhk_'+numRowPres).getAttribute('value');
        var tmpRowUmr = document.getElementById('ftPrestasi_umr_'+numRowPres).getAttribute('value');
        var tmpRowIns = document.getElementById('ftPrestasi_upahpremi_'+numRowPres).getAttribute('value');
        
        var tmpValHk = parseFloat(totalPresHk.getAttribute('realValue'))-parseFloat(tmpRowHk)+parseFloat(PresHk.value);
        var tmpValUmr = parseFloat(totalPresUmr.getAttribute('realValue'))-parseFloat(tmpRowUmr)+parseFloat(PresUmr.value);
        var tmpValIns = parseFloat(totalPresIns.getAttribute('realValue'))-parseFloat(tmpRowIns)+parseFloat(PresIns.value);
    } else {
		var tmpValHk = parseFloat(totalPresHk.getAttribute('realValue'));
		var tmpValUmr = parseFloat(totalPresUmr.getAttribute('realValue'));
		var tmpValIns = parseFloat(totalPresIns.getAttribute('realValue'));
		if(document.getElementById('form_ftPrestasi').style.display=='') {
			tmpValHk += parseFloat(PresHk.value);
			tmpValUmr += parseFloat(PresUmr.value);
			tmpValIns += parseFloat(PresIns.value);
		}
    }
    
    totalPresHk.setAttribute('realValue',tmpValHk);totalPresHk.value = tmpValHk;
    totalPresUmr.setAttribute('realValue',tmpValUmr);totalPresUmr.value = tmpValUmr;
    totalPresIns.setAttribute('realValue',tmpValIns);totalPresIns.value = tmpValIns;
    
    change_number(totalPresHk);
    change_number(totalPresUmr);
    change_number(totalPresIns);
    
    // Absensi
    if(cancelAbs.style.display=='') {
        var tmpRowHk = document.getElementById('ftAbsensi_jhk_'+numRowAbs).getAttribute('value');
        var tmpRowUmr = document.getElementById('ftAbsensi_umr_'+numRowAbs).getAttribute('value');
        var tmpRowIns = document.getElementById('ftAbsensi_insentif_'+numRowAbs).getAttribute('value');
        
        var tmpValHk = parseFloat(totalAbsHk.getAttribute('realValue'))-parseFloat(tmpRowHk)+parseFloat(AbsHk.value);
        var tmpValUmr = parseFloat(totalAbsUmr.getAttribute('realValue'))-parseFloat(tmpRowUmr)+parseFloat(AbsUmr.value);
        var tmpValIns = parseFloat(totalAbsIns.getAttribute('realValue'))-parseFloat(tmpRowIns)+parseFloat(AbsIns.value);
    } else {
        var tmpValHk = parseFloat(totalAbsHk.getAttribute('realValue'))+parseFloat(AbsHk.value);
        var tmpValUmr = parseFloat(totalAbsUmr.getAttribute('realValue'))+parseFloat(AbsUmr.value);
        var tmpValIns = parseFloat(totalAbsIns.getAttribute('realValue'))+parseFloat(AbsIns.value);
    }
    totalAbsHk.setAttribute('realValue',tmpValHk);totalAbsHk.value = tmpValHk;
    totalAbsUmr.setAttribute('realValue',tmpValUmr);totalAbsUmr.value = tmpValUmr;
    totalAbsIns.setAttribute('realValue',tmpValIns);totalAbsIns.value = tmpValIns;
    
    change_number(totalAbsHk);
    change_number(totalAbsUmr);
    change_number(totalAbsIns);
}

/* Cek HK, UMR atau Insentif
 */
function cekVal(obj,mode,cekVar) {
    var totalPres = document.getElementById('totalPres'+cekVar);
    var totalAbs = document.getElementById('totalAbs'+cekVar);
    var tmpVar = document.getElementById('tmpVal'+cekVar).value;
    if(mode=='Abs' && cekVar=='Hk') {
        if(obj.value>1) {
            alert('Nilai Maksimum HK adalah 1');
            obj.value = 1;
        }
    }
    totalVal();
    
    var selisih = parseFloat(totalAbs.getAttribute('realValue')) - parseFloat(totalPres.getAttribute('realValue'));
    if(parseFloat(totalPres.getAttribute('realValue')) < parseFloat(totalAbs.getAttribute('realValue'))) {
        alert('Nilai Prestasi harus lebih besar dari nilai Absensi');
        if(mode=='Abs' && cekVar=='Hk' && (selisih>1)) {
            obj.value = 1;
        } else {
            obj.value = tmpVar;
        }
    }
    totalVal();
}

/* Posting Data
 */
function postingData(numRow) {
    var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var param = "notransaksi="+notrans;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                   // alert('Posting Berhasil');
                   // javascript:location.reload(true);
                    x=document.getElementById('tr_'+numRow);
                    x.cells[9].innerHTML='';
                    x.cells[10].innerHTML='';
                    x.cells[11].innerHTML="<img class=\"zImgOffBtn\" title=\"Posting\" src=\"images/skyblue/posted.png\">";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    if(confirm('Are you sure confirm transakction:'+notrans+
        '?\nOnce confirmed, the data can not be edited.')) {
        post_response_text('kebun_slave_operasional_posting.php', param, respon);
    }
}

function printPDF(ev,tipe) {
    // Prep Param
    param = "proses=pdf&tipe="+tipe;
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='kebun_slave_operasional_print.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function detailPDF(numRow,ev,tipe) {
    // Prep Param
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    param = "proses=pdf&tipe="+tipe+"&notransaksi="+notransaksi;
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='kebun_slave_operasional_print_detail.php?"+param+"'></iframe>",'','',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function detailData(numRow,ev,tipe)
{
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    param = "proses=html&tipe="+tipe+"&notransaksi="+notransaksi;
        title="Data Detail";
        showDialog1(title,"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='kebun_slave_operasional_print_detail.php?"+param+"'></iframe>",'800','400',ev);	
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
}

function changeOrg() {
    var prestasi = document.getElementById('ftPrestasi_kodeorg').firstChild;
    // alert(prestasi.value);
	var material = document.getElementById('ftMaterial_kodeorg').firstChild;
    material.value = prestasi.value;
	getGudang();
}

function updateUMR(obj) {
    var nik = obj.options[obj.selectedIndex].value;
    var umr = document.getElementById('ftAbsensi_umr').firstChild;
    var tanggal=document.getElementById('tanggal').value;
    var jhk=document.getElementById('jhk').value;
    var tahun=tanggal.substr(6, 4);
    var param = "nik="+nik+'&tahun='+tahun+'&jhk='+jhk+'&tanggal='+tanggal;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    umr.value = con.responseText
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional_detail.php?proses=updateUMR', param, respon);
}

function updateUMR2() {
    var nik=document.getElementById('nik');
    nik = nik.options[nik.selectedIndex].value;
    var umr = document.getElementById('ftAbsensi_umr').firstChild;
    var tanggal=document.getElementById('tanggal').value;
    var jhk=document.getElementById('jhk').value;
    var tahun=tanggal.substr(6, 4);
    var param = "nik="+nik+'&tahun='+tahun+'&jhk='+jhk+'&tanggal='+tanggal;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    umr.value = con.responseText
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional_detail.php?proses=updateUMR', param, respon);
}


function filterKaryawan(idkomponen,cek){
   try{  
	showDetail2();
     kodeorg=document.getElementById('ftPrestasi_kodeorg_0').getAttribute('value');
	 filtermandor = document.getElementById('filtermandor');
     filternikpt = document.getElementById('filternikpt');
       if(cek.checked==true)
           param='kodeorg='+kodeorg+'&tipe=afdeling';
       else
           param='kodeorg='+kodeorg+'&tipe=unit';
       post_response_text('kebun_slave_operasional_detail.php?proses=gatKarywanAFD', param, respon);     
	   if(filtermandor.checked==true){
		   filtermandor.checked = false;
	   }
       if(filternikpt.checked==true){
           filternikpt.checked = false;
       }
		myFrm1 = document.getElementById('myFrm1');
		myFrm2 = document.getElementById('myFrm2');
		myFrm1.style.display = "";
		myFrm2.style.display = "none";
   }
   catch(err){
       alert('Simpan prestasi terlebih dahulu');
   }

     function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById(idkomponen).innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }   
}
function filterKaryawanPt(idkomponen,cek){
   try{  
    showDetail2();
     kodeorg=document.getElementById('ftPrestasi_kodeorg_0').getAttribute('value');
     filtermandor = document.getElementById('filtermandor');
     filternik = document.getElementById('filternik');
       if(cek.checked==true)
           param='kodeorg='+kodeorg+'&tipe=perpt';
       else
           param='kodeorg='+kodeorg+'&tipe=unit';
       post_response_text('kebun_slave_operasional_detail.php?proses=gatKarywanAFD', param, respon);     
       if(filtermandor.checked==true){
           filtermandor.checked = false;
       }
       if(filternik.checked==true){
           filternik.checked = false;
       }
       
        myFrm1 = document.getElementById('myFrm1');
        myFrm2 = document.getElementById('myFrm2');
        myFrm1.style.display = "";
        myFrm2.style.display = "none";
   }
   catch(err){
       alert('Simpan prestasi terlebih dahulu');
   }

     function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById(idkomponen).innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }   
}

function filterMandor(idkomponen,cek)
{
	myFrm1 = document.getElementById('myFrm1');
	myFrm2 = document.getElementById('myFrm2');
	
	try{  
     kodeorg=document.getElementById('ftPrestasi_kodeorg_0').getAttribute('value');
	 nikmandor = document.getElementById('nikmandor').options[document.getElementById('nikmandor').selectedIndex].value;
	 notransaksi=document.getElementById('notransaksi').value;
	 filternik=document.getElementById('filternik');
	 filternikpt=document.getElementById('filternikpt');
       if(cek.checked==true){
			param='nikmandor='+nikmandor+'&tipe=mandor&notransaksi='+notransaksi;
			myFrm1.style.display = "none";
			myFrm2.style.display = "";
       }else{
           param='kodeorg='+kodeorg+'&tipe=unit';
		   myFrm1.style.display = "";
		   myFrm2.style.display = "none";
	   }
	   
	   if(filternik.checked==true){
		   filternik.checked = false;
	   }
	   if(filternikpt.checked==true){
		  filternikpt.checked = false;
	   }
       post_response_text('kebun_slave_operasional_detail.php?proses=gatKarywanAFD', param, respon);     
   }
   catch(err){
       alert('Simpan prestasi terlebih dahulu');
   }

     function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					if(cek.checked==true){
						document.getElementById('myFrm2').innerHTML=con.responseText;
					}else{
						document.getElementById(idkomponen).innerHTML=con.responseText;
						showDetail2();
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }   
}

function getDivisi(kdKu,cek)
{
	var divisi = cek.options[cek.selectedIndex].value;
	kodeorg=document.getElementById('kodeorg').value;
	// alert(kodeorg);
	// divisi = document.getElementById('kdDivisi').value;
	param='divisi='+divisi+'&chKdOrg='+kodeorg;
	post_response_text('kebun_slave_operasional_detail.php?proses=getDivisi', param, respon);
	
     function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					// alert(con.responseText);
                    document.getElementById(kdKu).firstChild.innerHTML=con.responseText;
					changeOrg();
					// getGudang();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getGudang(){
	// var prestasi = document.getElementById('ftPrestasi_kodeorg').firstChild;
    // alert(prestasi.value);
	var material = document.getElementById('ftMaterial_kodeorg').firstChild;
	//var gudang = document.getElementById('ftMaterial_kodegudang').firstChild;
	// alert(material.value);
	
	// var chkBlok = cek.options[cek.selectedIndex].value;
	// kodeorg=document.getElementById('kodeorg').value;
	// // alert(kodeorg);
	// // divisi = document.getElementById('kdDivisi').value;
	param='material='+material.value;
	post_response_text('kebun_slave_operasional_detail.php?proses=updateGudang', param, respon);
	
     function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					// alert(con.responseText);
                    dtgd=document.getElementById('kodegudang');
                    for(x=0;x<dtgd.length;x++){
                        if(dtgd.options[x].value==trim(con.responseText)){
                                dtgd.options[x].selected=true;
                        }
                    } 
                    //gudang.value=con.responseText;
					// changeOrg();
					getstpremi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function changeGudang() {
	var row = document.getElementById('ftMaterial_kodebarang'),
		els = row.getElementsByTagName('input');
	for(i in els) {
		els[i].value = '';
	}
}

function hiddenDiv(){
	var addFTBtn = document.getElementById('addFTBtn_ftPrestasi');
	var clearFTBtn = document.getElementById('clearFTBtn_ftPrestasi');
	var delFTBtn = document.getElementById('delFTBtn');
	var editmodeFTBtn = document.getElementById('editmodeFTBtn');
	var divDivisi = document.getElementById('divDivisi');
	var kodeorg = document.getElementById('ftPrestasi_kodeorg').firstChild;
	var jumlahhk = document.getElementById('jumlahhk').firstChild;
	if(divDivisi != null){
		divDivisi.style.display = 'none';
	}
	if(addFTBtn != null){
		addFTBtn.style.visibility = 'hidden';
	}
	if(delFTBtn != null){
		delFTBtn.style.visibility = 'hidden';
	}
	// if(editmodeFTBtn != null){//tambahan jamhari untuk ngunci icon editnya muncul
	// 	editmodeFTBtn.style.visibility = 'hidden';//tambahan jamhari untuk ngunci icon editnya muncul
	// }  //tambahan jamhari untuk ngunci icon editnya muncul
	if(clearFTBtn != null){
		clearFTBtn.style.visibility = 'hidden';
	}
	if(kodeorg != null){
		kodeorg.disabled = true;
	}
}

function savePrestasi(){
	var totalAbsHk = parseFloat(document.getElementById('totalAbsHk').value);
	var jumlahhk = parseFloat(document.getElementById('jumlahhk').value);
	var notransaksi = document.getElementById('notransaksi').value;
	var hasilkerja = parseFloat(document.getElementById('hasilkerja').value);
	var totalPresHk = parseFloat(document.getElementById('totalPresHk').value);
	
	param='totalAbsHk='+totalAbsHk+'&jumlahhk='+jumlahhk+'&notransaksi='+notransaksi+'&hasilkerja='+hasilkerja;
	post_response_text('kebun_slave_operasional_detail.php?proses=savePrestasi', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					document.getElementById('jumlahhk').value = totalPresHk;
					alert(con.responseText);
                } else {
                    document.getElementById('totalPresHk').value=con.responseText;
					// changeOrg();
					alert('Update prestasi berhasil.');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getTotalKehadiran(key,nik){
	var kehadiranhk = parseFloat(document.getElementById('kehadiranhk_'+key).value);
	setkehadiranumr = document.getElementById('kehadiranumr_'+key);
	var totalAbsHk = parseFloat(document.getElementById('totalAbsHk').value);
	var jumlahhk = parseFloat(document.getElementById('jumlahhk').value);
	var notransaksi = document.getElementById('notransaksi').value;
	var hasilkerja = parseFloat(document.getElementById('hasilkerja').value);
	var totalPresHk = parseFloat(document.getElementById('totalPresHk').value);
	
	if(kehadiranhk == 0 || kehadiranhk == ""){
		setkehadiranumr.value = 0;
		return;
	}
	
	if(kehadiranhk > 1){
		alert('Nilai Maksimum HK adalah 1');
		document.getElementById('kehadiranhk_'+key).value = 0;
		setkehadiranumr.value = 0;
		return;
	}
	
	var umr = document.getElementById('kehadiranumr_'+key);
    var tanggal=document.getElementById('tanggal').value;
    var jhk=document.getElementById('jhk').value;
    var tahun=tanggal.substr(6, 4);
    var param = "nik="+nik+'&tahun='+tahun+'&jhk='+kehadiranhk+'&tanggal='+tanggal+'&totalAbsHk='+totalAbsHk+'&totalPresHk='+totalPresHk+'&notransaksi='+notransaksi;
    
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					document.getElementById('kehadiranhk_'+key).value = 0;
                } else {
                    //=== Success Response
					// res = con.responseText;
					// res = res.split('#####');
					
                    umr.value = con.responseText
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional_detail.php?proses=updateUMR2', param, respon);
}


function simpankehadiran(key,nik){
	var kehadiranhk = remove_comma_var(document.getElementById('kehadiranhk_'+key).value);
	var kehadiranumr = remove_comma_var(document.getElementById('kehadiranumr_'+key).value);
	var kehadiranpremi = remove_comma_var(document.getElementById('kehadiranpremi_'+key).value);
	var kehadiranhasilkerja = remove_comma_var(document.getElementById('kehadiranhasilkerja_'+key).value);
	var simpankehadiran = document.getElementById('simpankehadiran_'+key);
	setkehadiranhk = document.getElementById('kehadiranhk_'+key);
	var totalAbsHk = parseFloat(document.getElementById('totalAbsHk').value);
	var jumlahhk = parseFloat(document.getElementById('jumlahhk').value);
	var notransaksi = document.getElementById('notransaksi').value;
	var hasilkerja = parseFloat(document.getElementById('hasilkerja').value);
	var totalPresHk = parseFloat(document.getElementById('totalPresHk').value);	
	var tanggal=document.getElementById('tanggal').value;
    var jhk=document.getElementById('jhk').value;
    var tahun=tanggal.substr(6, 4);
	
	var param = 'notransaksi='+notransaksi+'&tanggal='+tanggal+'&kehadiranhk='+kehadiranhk+'&kehadiranumr='+kehadiranumr+'&kehadiranpremi='+kehadiranpremi+'&nik='+nik+'&kehadiranhasilkerja='+kehadiranhasilkerja;
   
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					document.getElementById('kehadiranhk_'+key).value = 0;
					document.getElementById('kehadiranumr_'+key).value = 0;
					document.getElementById('kehadiranpremi_'+key).value = 0;
					document.getElementById('kehadiranhasilkerja_'+key).value = 0;
                } else {
                    //=== Success Response
                    simpankehadiran.disabled = true;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_operasional_detail.php?proses=simpankehadiran', param, respon);
}

function opendisabled(key){
	var simpankehadiran = document.getElementById('simpankehadiran_'+key);
	simpankehadiran.disabled = false;
	document.getElementById('row_'+key).style.backgroundColor = '#D7EBFA';
}

function simpankehadiranAll(maxrow){
	loopsave(1, maxrow);
}

function loopsave(currRow, maxrow){
	kehadiranhk = remove_comma_var(document.getElementById('kehadiranhk_'+currRow).value);
	kehadiranumr = remove_comma_var(document.getElementById('kehadiranumr_'+currRow).value);
	kehadiranpremi = remove_comma_var(document.getElementById('kehadiranpremi_'+currRow).value);
	kehadiranhasilkerja = remove_comma_var(document.getElementById('kehadiranhasilkerja_'+currRow).value);
	notransaksi = document.getElementById('notransaksi').value;
	tanggal=document.getElementById('tanggal').value;
	kehadirannik = document.getElementById('kehadirannik_'+currRow).value;
	
	
	param = 'notransaksi='+notransaksi+'&tanggal='+tanggal+'&kehadiranhk='+kehadiranhk+'&kehadiranumr='+kehadiranumr+'&kehadiranpremi='+kehadiranpremi+'&kehadirannik='+kehadirannik+'&kehadiranhasilkerja='+kehadiranhasilkerja;
    param += "&proses=simpankehadiranAll";
	
	tujuan = 'kebun_slave_operasional_detail.php';
    post_response_text(tujuan, param, respog);
    document.getElementById('row_'+currRow).style.backgroundColor = 'cyan';
    //lockScreen('wait');
	
    function respog() {
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById('row_'+currRow).style.backgroundColor = 'red';
					document.getElementById('kehadiranumr_'+currRow).value = 0;
					document.getElementById('kehadiranpremi_'+currRow).value = 0;
					document.getElementById('kehadiranhk_'+currRow).value = 0;
					document.getElementById('kehadiranhasilkerja_'+currRow).value = 0;
                }
                else {
                    document.getElementById('simpankehadiran_'+currRow).disabled = true;
                    currRow += 1;
                    if (currRow > maxrow)
                    {
                        alert('Done');
						showDetail2();
                    }
                    else
                    {
                        loopsave(currRow, maxrow);
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

function getpremibkm(){
	hasilkerja=document.getElementById('ftAbsensi_hasilkerja').firstChild.value;
	kodeorg=document.getElementById('kodeorg').value;
	notransaksi=document.getElementById('notransaksi').value;
	tanggal=document.getElementById('tanggal').value;
	nik= document.getElementById('nik').options[document.getElementById('nik').selectedIndex].value;
	param='proses=getpremibkm'+'&hasilkerja='+hasilkerja+'&kodeorg='+kodeorg+'&notransaksi='+notransaksi+'&tanggal='+tanggal+'&nik='+nik;
    tujuan = 'kebun_slave_operasional_detail.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
				//	alert(con.responseText);
                  document.getElementById('ftAbsensi_insentif').firstChild.value=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 	
}



function getpremibkmkemandoran(nourut){
	hasilkerja=document.getElementById('kehadiranhasilkerja_'+nourut).value;
	kodeorg=document.getElementById('kodeorg').value;
	notransaksi=document.getElementById('notransaksi').value;
	tanggal=document.getElementById('tanggal').value;
	param='proses=getpremibkm'+'&hasilkerja='+hasilkerja+'&kodeorg='+kodeorg+'&notransaksi='+notransaksi+'&tanggal='+tanggal;
    tujuan = 'kebun_slave_operasional_detail.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
				//	alert(con.responseText);
                  document.getElementById('kehadiranpremi_'+nourut).value=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 	
}


