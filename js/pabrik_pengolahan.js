
function tampilDetail(numRow,ev){   
	var nopengolahan = document.getElementById('nopengolahan_'+numRow).getAttribute('value'); 
    // content= "<div id=formhtml style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    // title='View HTML';
    // height='';
    // width='';
    // showDialog1(title,content,width,height,ev);	
    datahtml(nopengolahan);
}

function datahtml(nopengolahan){
	
    param = "proses=html&nopengolahan="+nopengolahan;
    tujuan = 'pabrik_slave_pengolahan.php';
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
                    // document.getElementById('formhtml').innerHTML=con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('20%','70%');
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}


function unposting(nopengolahan,tanggal){
	param = "proses=unposting&nopengolahan="+nopengolahan;
    tujuan = 'pabrik_slave_pengolahan.php';
	if(confirm("Anda yakin ingin melakukan unposting ?? data produksi akan otomatis terhapus untuk tanggal >= "+ tanggal +" ")){
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
                    //alert(con.responseText);
                    document.getElementById('formhtml').innerHTML=con.responseText;
					defaultList();
					closeDialog();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}


// function tampilDetail(numRow,ev)
// {
    // var nopengolahan = document.getElementById('nopengolahan_'+numRow).getAttribute('value');
    // // var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    // // var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    // // var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
   // // param = "proses=html&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        // // "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
		
		// //  param = "proses=html";
		    // param = "proses=html&nopengolahan="+nopengolahan;
        // title="Data Detail";
        // showDialog1(title,"<iframe frameborder=0 style='width:795px;height:400px'"+
        // " src='pabrik_slave_pengolahan.php?"+param+"'></iframe>",'800','400',ev);	
        // var dialog = document.getElementById('dynamic1');
        // dialog.style.top = '50px';
        // dialog.style.left = '15%';
// }



function postingData(numRow) {
    var nopengolahan = document.getElementById('nopengolahan_'+numRow).getAttribute('value');
	//nopengolahan=trim(document.getElementById('nopengolahan'+numRow).innerHTML);
    var param = "nopengolahan="+nopengolahan;
    
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
                    x.cells[4].innerHTML='';
                    x.cells[5].innerHTML='';
                    x.cells[6].innerHTML="<img class=\"zImgOffBtn\" title=\"Posting\" src=\"images/skyblue/posted.png\">";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    if(confirm('Are you sure confirm transakction:'+nopengolahan+
        '?\nOnce confirmed, the data can not be edited.')) {
        post_response_text('pabrik_slave_pengolahan.php?proses=posting', param, respon);
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
function searchTrans() {
    var tgl = document.getElementById('sTanggal');
    var where = '[["tanggal","'+tgl.value+'"]]';
    
    goToPages(1,showPerPage,where);
}

/* Paging
 * Paging Data
 */
function defaultList() {
    goToPages(1,showPerPage);
}

function goToPages(page,shows,where) {
    if(typeof where != 'undefined') {
        var newWhere = where.replace(/'/g,'"');
    }
    var workField = document.getElementById('workField');
    var param = "page="+page;
    param += "&shows="+shows+"&tipe=KB";
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
                    closeDialog();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan.php?proses=showHeadList', param, respon);
}

function choosePage(obj,shows,where) {
    var pageVal = obj.options[obj.selectedIndex].value;
    goToPages(pageVal,shows,where);
}

/* Halaman Manipulasi Data
 * Halaman add, edit, delete
 */
function showAdd() {
    var workField = document.getElementById('workField');
    var param = "";
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                    closeDialog();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan.php?proses=showAdd', param, respon);
}

function showEditFromAdd() {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('nopengolahan');
    var param = "nopengolahan="+trans.value;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
					// defaultList();
                    // showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan.php?proses=showEdit', param, respon);
}

function showEdit(num) {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('nopengolahan_'+num);
    var param = "numRow="+num+"&nopengolahan="+trans.innerHTML;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                    // showDetail();
					// defaultList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan.php?proses=showEdit', param, respon);
}

/* Manipulasi Data
 * add, edit, delete
 */
function addDataTable() {
    var noP = document.getElementById('nopengolahan');
    var param = "kodeorg="+getValue('kodeorg')+"&nopengolahan="+getValue('nopengolahan');
    param += "&tanggal="+getValue('tanggal')+"&shift="+getValue('shift')+"&jamshift="+getValue('jamshift');
    param += "&jammulai="+getValue('jammulai_jam')+":"+getValue('jammulai_menit')+":00";
    param += "&jamselesai="+getValue('jamselesai_jam')+":"+getValue('jamselesai_menit')+":00";
    param += "&asisten="+getValue('asisten');
	
	mandor =document.getElementById('mandor');  
	if(mandor){
		param+='&mandor='+mandor.value;
	} 
	
	// param += "&mandor="+getValue('mandor')+"&asisten="+getValue('asisten');
    param += "&jamdinasbruto="+getValue('jamdinasbruto')+"&jamstagnasi="+getValue('jamstagnasi');
    param += "&jumlahlori="+getValue('jumlahlori')+"&tbsdiolah="+getValue('tbsdiolah');
	param += "&lorirestan="+getValue('lorirestan');
	param += "&restansebelum="+getValue('restansebelum');
	param += "&restandidalam="+getValue('restandidalam');
	param += "&restansesudah="+getValue('restansesudah');
	param += "&lorirestan="+getValue('lorirestan');
    param += "&keterangan="+getValue('keterangan');
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    noP.value = con.responseText;
                    // showEditFromAdd();
					 defaultList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan.php?proses=add', param, respon);
}

function editDataTable() {
    var param = "kodeorg="+getValue('kodeorg')+"&nopengolahan="+getValue('nopengolahan');
    param += "&tanggal="+getValue('tanggal')+"&shift="+getValue('shift');
    param += "&jammulai="+getValue('jammulai_jam')+":"+getValue('jammulai_menit')+":00";
    param += "&jamselesai="+getValue('jamselesai_jam')+":"+getValue('jamselesai_menit')+":00";
    param += "&asisten="+getValue('asisten');
    param += "&jamdinasbruto="+getValue('jamdinasbruto')+"&jamstagnasi="+getValue('jamstagnasi');
    param += "&jumlahlori="+getValue('jumlahlori')+"&tbsdiolah="+getValue('tbsdiolah')+"&jamshift="+getValue('jamshift');
	param += "&lorirestan="+getValue('lorirestan');
    
	mandor =document.getElementById('mandor');  
	if(mandor){
		param+='&mandor='+mandor.value;
	}
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    defaultList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan.php?proses=edit', param, respon);
}

/*
 * Detail
 */

function showDetail() {
    var detailField = document.getElementById('detailField');
    var notrans = document.getElementById('nopengolahan').value;
    var param = "nopengolahan="+notrans+"&kodeorg="+getValue('kodeorg');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    detailField.innerHTML = con.responseText;
                    //updMesin();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan_detail.php?proses=showDetail', param, respon);
}

function deleteData(num) {
    var notrans = document.getElementById('nopengolahan_'+num).innerHTML;
    var param = "nopengolahan="+notrans;
    
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
    
    post_response_text('pabrik_slave_pengolahan.php?proses=delete', param, respon);
}

function printPDF(ev) {
    // Prep Param
    param = "proses=pdf";
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='pabrik_slave_pengolahan_print.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
function getNo(ev){
       downTm=getValue('jamstagnasi');
    //if(downTm!=0&&downTm!=''){
        var param = "nopengolahan="+getValue('nopengolahan');
        param+= "&tanggal="+getValue('tanggal')+"&shift="+getValue('shift')+"&kodeorg="+getValue('kodeorg');
        width=850;
        height=550;
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        //=== Success Response
                        isiform=con.responseText.split("####");
                        showDialog1(isiform[0],isiform[1],width,height,ev);
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        post_response_text('pabrik_slave_pengolahan_detail.php?proses=getForm', param, respon);
    //}
}
function addToDb(rowd){
    var isiDt='';
    var jmstg=0;
    for(i=1;i<=rowd;i++){
        objData=document.getElementById('dt_'+i);
        if(objData.checked==true){
            if(i==1){
                isiDt="&dtStation[]="+document.getElementById('stat_'+i).value+"&notrans[]="+document.getElementById('notrans_'+i).innerHTML;
                isiDt+="&dtMesin[]="+document.getElementById('msn_'+i).value+"&dtJammulai[]="+document.getElementById('jmml_'+i).value;    
                isiDt+="&dtJamselesai[]="+document.getElementById('jmsls_'+i).value+"&dtStag[]="+document.getElementById('stag_'+i).value;    
                isiDt+="&dtDownstatus[]="+document.getElementById('dwnstat_'+i).value+"&dtKet[]="+document.getElementById('ket_'+i).value;    
            }else{
                isiDt+="&dtStation[]="+document.getElementById('stat_'+i).value+"&notrans[]="+document.getElementById('notrans_'+i).innerHTML;
                isiDt+="&dtMesin[]="+document.getElementById('msn_'+i).value+"&dtJammulai[]="+document.getElementById('jmml_'+i).value;    
                isiDt+="&dtJamselesai[]="+document.getElementById('jmsls_'+i).value+"&dtStag[]="+document.getElementById('stag_'+i).value;    
                isiDt+="&dtDownstatus[]="+document.getElementById('dwnstat_'+i).value+"&dtKet[]="+document.getElementById('ket_'+i).value;    
            }
            var breakdwn=document.getElementById('stag_'+i).value;
            jmstg+=parseFloat(breakdwn);
        }
    }
    nopengolahan=document.getElementById('nopeng').value;
    param="nopengolahan="+nopengolahan+"&totJmStag="+jmstg;
    param+=isiDt;
    function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        //=== Success Response
                        closeDialog();
                        showDetail();
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        post_response_text('pabrik_slave_pengolahan_detail.php?proses=addDetail', param, respon);
}
function delData(nopeng,station,msn){
    
    param="nopengolahan="+nopeng+"&station="+station+"&mesin="+msn;
    function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        //=== Success Response
                        showDetail();
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        if(confirm(notif)){
            post_response_text('pabrik_slave_pengolahan_detail.php?proses=delDetail', param, respon);    
        }
}
function updMesin() {
    var mesin = document.getElementById('tahuntanam');
    var param = "station="+getValue('station');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    //eval("var res = "+con.responseText+";");
                    //mesin.options.length=0;
                    //for(i in res) {
                    //    mesin.options[mesin.options.length] = new Option(res[i],i);
                    //}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan_detail.php?proses=getPengolahan', param, respon);
}
 
function updMandorAst(mode) {
    var mandor = document.getElementById('mandor');
    var asisten = document.getElementById('asisten');
    var shift = document.getElementById('shift');
    if(shift.selectedIndex==-1) {
        var shiftVal = 'empty';
    } else {
        var shiftVal = getValue('shift');
    }
    var param = "tanggal="+getValue('tanggal')+"&shift="+shiftVal+"&mode="+mode;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    eval("var res = "+con.responseText+";");
                    if(res['shift']!='empty') {
                        shift.options.length=0;
                        for(i in res['shift']) {
                            shift.options[shift.options.length] = new Option(res['shift'][i],i);
                        }
                    }
                    mandor.options.length=0;
                    for(i in res['mandor']) {
                        mandor.options[mandor.options.length] = new Option(res['mandor'][i],i);
                    }
                    asisten.options.length=0;
                    for(i in res['asisten']) {
                        asisten.options[asisten.options.length] = new Option(res['asisten'][i],i);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan.php?proses=updMandorAst', param, respon);
}

function detailPDF(numRow,ev) {
    // Prep Param
    var nopengolahan = document.getElementById('nopengolahan_'+numRow).getAttribute('value');
    param = "proses=pdf&nopengolahan="+nopengolahan;
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='pabrik_slave_pengolahan_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function showMaterial(num,ev) {
    var station = document.getElementById('ftMesin_station_'+num).getAttribute('value');
    var mesin = document.getElementById('ftMesin_tahuntanam_'+num).getAttribute('value');
    
    var param = "nopengolahan="+getValue('nopengolahan')+
        "&kodeorg="+station+"&tahuntanam="+mesin+"&numRow="+num;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    showDialog1('Edit Material',con.responseText,'800','300',ev);
                    var dialog = document.getElementById('dynamic1');
                    dialog.style.top = '10%';
                    dialog.style.left = '15%';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan_material.php?proses=showMaterial', param, respon);
}

function getJam(){

     var param = "shift="+getValue('shift');    
     function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    isidt=con.responseText.split("####");
                    jmAwl=isidt[0].split(":");
                    jmSlsi=isidt[1].split(":");
                    if(getValue('shift')==''){
                        jmSlsi[1]=jmSlsi[0]=jmAwl[1]=jmAwl[0]="00";
                    }
                    jmmulai=document.getElementById('jammulai_jam');
                    for(x=0;x<jmmulai.length;x++){
                            if(jmmulai.options[x].value==jmAwl[0])
                            {
                                    jmmulai.options[x].selected=true;
                            }
                    }
                    jmmulaiMenit=document.getElementById('jammulai_menit');
                    for(x=0;x<jmmulaiMenit.length;x++){
                            if(jmmulaiMenit.options[x].value==jmAwl[1])
                            {
                                    jmmulaiMenit.options[x].selected=true;
                            }
                    }
                    jmmulai=document.getElementById('jamselesai_jam');
                    for(x=0;x<jmmulai.length;x++){
                            if(jmmulai.options[x].value==jmSlsi[0])
                            {
                                    jmmulai.options[x].selected=true;
                            }
                    }
                    jmmulaiMenit=document.getElementById('jamselesai_menit');
                    for(x=0;x<jmmulaiMenit.length;x++){
                            if(jmmulaiMenit.options[x].value==jmSlsi[1])
                            {
                                    jmmulaiMenit.options[x].selected=true;
                            }
                    }
				//getselisih();
				getdowntime();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_pengolahan_detail.php?proses=getJam', param, respon);
}



function getdowntime(){
	kodeorg=document.getElementById('kodeorg').value;
    tanggal=document.getElementById('tanggal').value;
    shift=document.getElementById('shift').value;
    param='proses=getdowntime'+'&shift='+shift+'&tanggal='+tanggal+'&kodeorg='+kodeorg;
    tujuan = 'pabrik_slave_pengolahan_detail.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                }
                else {
                    document.getElementById('jamstagnasi').value=con.responseText;
					getselisih();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}




function getselisih()
{
	a=document.getElementById('jamselesai_jam').value;
	b=document.getElementById('jammulai_jam').value;
	c=document.getElementById('jamselesai_menit').value;
	d=document.getElementById('jammulai_menit').value;
	datacek=0;
	if(a!=b)
	{	
		if(c!=d)
		{	
			if(a>b)//
			{
				if(c>d)//a>b c>d
				{
					x=a-b;
					y=c-d;
				}
				else //a>b c<d
				{
					x=a-b-1;
					y=(c-d)+60;
				}
			} else
			{
				if(c>d)
				{
					x=(a-b)+24;
					y=c-d;	
				}
				else
				{
					x=(a-b)+23;
					y=(c-d)+60;
				}
			}
		}
		else  //c==d
		{
			//y=0;
			if(a>b)//
			{
				x=a-b;
				y=0;
			}
			else //a<b
			{
				x=(a-b)+24;
				y=0;
			}
		}
	}
	else //a==b
	{
		if(c!=d)
		{	
			if(c>d)//a>b c>d
			{
				x=0;
				y=c-d;
			}
			else //a>b d>c
			{
				x=0;
				y=(c-d)+60;
			}		
		}
		else  //c==d
		{
			// alert('masuk');
			//alert('waktu mulai dan selsai masih sama harap periksa kembali !!');return;
			// document.getElementById('jamdinasbruto').value=0;
			datacek=1;
		}
	}
	
	
	
	//convert menit ke decimal /100
	//contoh 7.30 harusnya 7.50 -> 30/60=0.5
	//z=x+"."+y;
	if(datacek==0){
		m=parseFloat(y)/60;
		z=parseFloat(x)+parseFloat(m);
		//z=x+"."+m;
		document.getElementById('jamdinasbruto').value=z;
		getselisihdowntime();
	}else{
		document.getElementById('jamdinasbruto').value=0;
	}
}

function getselisihdowntime(){
	//getselisih();
	a=document.getElementById('jamdinasbruto').value;
	b=document.getElementById('jamstagnasi').value;
	
	c=parseFloat(a)-parseFloat(b);
	document.getElementById('jamdinasbruto').value=c;
	if(b=='' || b==0){
		getselisih();
	}
}

