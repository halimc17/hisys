/**
 * @author repindra.ginting
 */


function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0]) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(notransaksi){
	ev = 'event';
	showformupload(ev);
	param='method=showupload&notransaksi='+notransaksi;
	tujuan='pabrik_slave_permintaan_perbaikan.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
                    document.getElementById('contUpload').innerHTML=con.responseText;
					loadfiles(notransaksi);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function submitfile() {
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('notransaksiupload').innerHTML;
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}

	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	busy_on();
	con.open("POST", "pabrik_slave_permintaan_perbaikan.php?method=submitfile", true);
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
					document.getElementById('btnsubmit').disabled=false;
					document.getElementById("upload").value = "";
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'pabrik_slave_permintaan_perbaikan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
}
function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		form();
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'pabrik_slave_permintaan_perbaikan.php';
		post_response_text(tujuan, param, respog);
	} else {
		alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.');
		return;
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notransaksi, namafile) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'pabrik_slave_permintaan_perbaikan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}





function fillField(nodok,tglOrder,jmOrder,mnOrder,namaPemohon,statusPemohon,pabrik,station,mesin,shift,tipePerbaikan,
                    uraianKerusakan,tglMulai,jmMulai,mnMulai,tglSelesai,jmSelesai,mnSelesai,jumlahJamPerbaikan,
                    statusKetuntasan,hasilKerja,namaMesin,komMain,komPros,dwnstat,kar1,kar2,kar3){
	var re = /<br *\/?>/gi;
    document.getElementById('listData').style.display='none';
    document.getElementById('headher').style.display='block';
    document.getElementById('nodok').value=nodok;
    document.getElementById('tglOrder').value=tglOrder;
    document.getElementById('jmOrder').value=jmOrder;
    document.getElementById('mnOrder').value=mnOrder;
    document.getElementById('namaPemohon').value=namaPemohon;
    document.getElementById('statusPemohon').value=statusPemohon;
    document.getElementById('pabrik').value=pabrik;
    document.getElementById('station').value=station;
    //document.getElementById('mesin').value=mesin;
    document.getElementById('shift').value=shift;
    document.getElementById('persetujuan1').value=kar1;
    document.getElementById('persetujuan2').value=kar2;
    document.getElementById('persetujuan3').value=kar3;
    document.getElementById('tipePerbaikan').value=tipePerbaikan;
    document.getElementById('uraianKerusakan').value=uraianKerusakan.replace(re, '\n');

    document.getElementById('mesin').innerHTML="<option value='"+mesin+"'>"+ namaMesin +"</option>";
    document.getElementById('station').disabled=true;
    document.getElementById('mesin').disabled=true;
    document.getElementById('tglOrder').disabled=true;
    document.getElementById('jmOrder').disabled=true;
    document.getElementById('mnOrder').disabled=true;
    jk=document.getElementById('dwnStat');
    for(x=0;x<jk.length;x++){
            if(jk.options[x].value==dwnstat)
            {
                    jk.options[x].selected=true;
            }
    }
    document.getElementById('method').value='update';
}



 function saveHeader()
{
    nodok=document.getElementById('nodok').value;
    tglOrder=document.getElementById('tglOrder').value;
    jmOrder=document.getElementById('jmOrder').value;
    mnOrder=document.getElementById('mnOrder').value;
    namaPemohon=document.getElementById('namaPemohon').value;
    statusPemohon=document.getElementById('statusPemohon').value;
    pabrik=document.getElementById('pabrik').value;
    station=document.getElementById('station').value;
    mesin=document.getElementById('mesin').value;
    shift=document.getElementById('shift').value;
    tipePerbaikan=document.getElementById('tipePerbaikan').value;
    uraianKerusakan=document.getElementById('uraianKerusakan').value;

    dwnStat=document.getElementById('dwnStat').value;

    persetujuan1=document.getElementById('persetujuan1').options[document.getElementById('persetujuan1').selectedIndex].value;
    persetujuan2=document.getElementById('persetujuan2').options[document.getElementById('persetujuan2').selectedIndex].value;
    persetujuan3=document.getElementById('persetujuan3').options[document.getElementById('persetujuan3').selectedIndex].value;
 
    method=document.getElementById('method').value;
 
    if(nodok=='' || tglOrder=='' || pabrik=='' || station=='' || mesin=='')
    {
        alert('please compleate the form');return;
    }
 

    param='nodok='+nodok+'&tglOrder='+tglOrder+'&jmOrder='+jmOrder+'&mnOrder='+mnOrder+'&namaPemohon='+namaPemohon;
    param+='&statusPemohon='+statusPemohon+'&pabrik='+pabrik+'&station='+station+'&mesin='+mesin+'&shift='+shift;
    param+='&tipePerbaikan='+tipePerbaikan+'&uraianKerusakan='+uraianKerusakan+'&dwnStat='+dwnStat;
    param+='&persetujuan2='+persetujuan2+'&persetujuan3='+persetujuan3;
    param+='&method='+method+'&persetujuan1='+persetujuan1;
	// alert(param);
   
   

    tujuan='pabrik_slave_permintaan_perbaikan.php';
   
    post_response_text(tujuan, param, respon);  
 
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
                } else 
                {
                    alert('Saved');
                   // loadList();
                   clearForm();
				   loadData();
                    
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

function clearForm() {
	document.getElementById('nodok').value='';
	document.getElementById('pabrik').value='';
	document.getElementById('tglOrder').value='';
	document.getElementById('jmOrder').value='00';
	document.getElementById('mnOrder').value='00';
	document.getElementById('mnOrder').value='';
	document.getElementById('namaPemohon').value='';
	document.getElementById('statusPemohon').value='';
	document.getElementById('dwnStat').value='';
	document.getElementById('uraianKerusakan').value='';
	document.getElementById('station').value='';
	document.getElementById('mesin').value='';
	document.getElementById('shift').value='';
	document.getElementById('tipePerbaikan').value='';
	    document.getElementById('persetujuan1').value='';
    document.getElementById('persetujuan2').value='';
    document.getElementById('persetujuan3').value='';
}


function ajukan(notran){
    param='method=ajukan'+'&nodok='+notran;
    tujuan = 'pabrik_slave_permintaan_perbaikan.php';
    if(confirm("Confirm data to approval :"+notran)){
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
                    loadData(0);

                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }   
}

 

function getNodok()
{
    station=document.getElementById('station').value; 
    tglOrder=document.getElementById('tglOrder').value; 
    param='method=getNodok'+'&station='+station+'&tglOrder='+tglOrder;
    tujuan='pabrik_slave_permintaan_perbaikan.php';
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
                    //alert(con.responseText);
                    document.getElementById('nodok').value=trim(con.responseText);
                    //.value=trim(con.responseText);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}


function getMesin(station,mesin)
{
    station=document.getElementById('station').value; 
    param='method=getMesin'+'&station='+station+'&mesin='+mesin;
    tujuan='pabrik_slave_permintaan_perbaikan.php';
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
                    //alert(con.responseText);
                    document.getElementById('mesin').innerHTML=con.responseText;
                    getNodok();
                    //.value=trim(con.responseText);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }   
     }      
}

function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loadData(paged);
}

function loadData(num) {
	tujuan = 'pabrik_slave_permintaan_perbaikan.php';
	schNodok=trim(document.getElementById('schNodok').value);
	schTgl=trim(document.getElementById('schTgl').value);
	schdwnStat=trim(document.getElementById('schdwnStat').value);
	schstation=trim(document.getElementById('schstation').value);
    param='schNodok='+schNodok;
    param+='&schTgl='+schTgl;
	param+='&schdwnStat='+schdwnStat;
	// param+='&schstatusKetuntasan='+schstatusKetuntasan;
	param+='&schstation='+schstation;
	param+='&page='+num;
    param+='&method=loadData';//loadSch
    //alert(param);
    post_response_text(tujuan, param, respog);
        function respog(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    // if (!isSaveResponse(con.responseText)) {
                        // alert(con.responseText);
                    // }
                    // else {
                        //displayList();
						document.getElementById('listData').style.display='block';
						document.getElementById('headher').style.display='none';
                        document.getElementById('contain').innerHTML=con.responseText;
                        //loadData();
                    // }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }   
}


function listdatalalu(title,ev){
	mesin=document.getElementById('mesin').value;
	tglOrder=document.getElementById('tglOrder').value;
	if(tglOrder==''){
		alert('Date empty');return;
	}
	if(mesin==''){
		alert('Mechine empty');return;
	}
    content= "<div id=formlistdatalalu style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    title='Record Maintenance '+mesin;
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    getlistdatalalu(mesin,tglOrder);
}

function getlistdatalalu(mesin,tglOrder){
	
    param='method=getlistdatalalu';
	param+='&mesin='+mesin+'&tglOrder='+tglOrder;
    tujuan = 'pabrik_slave_permintaan_perbaikan.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formlistdatalalu').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 		
}


function add_new_data(){
    document.getElementById('headher').style.display='block';
    document.getElementById('listData').style.display='none';
    cancelHead();
	document.getElementById('method').value='insert';
}


function displayList() {
	document.getElementById('listData').style.display='block';
	document.getElementById('headher').style.display='none';
	document.getElementById('schTgl').value='';
	document.getElementById('schNodok').value='';

	document.getElementById('schdwnStat').value='';
	document.getElementById('schstation').value='';
	// loadData();
}




function deleteHead(nodok)
{
    param='method=deleteHead'+'&nodok='+nodok;
    tujuan='pabrik_slave_permintaan_perbaikan.php';
    if(confirm(' Anda yakin ingin menghapus '+nodok+' ?? '))
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
                    loadData(0);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }   
}



function cancelHead()
{
    document.getElementById('nodok').value='';
    document.getElementById('tglOrder').value='';
    document.getElementById('jmOrder').value='00';
    document.getElementById('mnOrder').value='00';
    document.getElementById('namaPemohon').value='';
    document.getElementById('statusPemohon').value='P';
    document.getElementById('station').value='';
    document.getElementById('mesin').value='';
    document.getElementById('shift').value='1';
    document.getElementById('tipePerbaikan').value='prev';
    document.getElementById('uraianKerusakan').value='';

    document.getElementById('station').disabled=false;
    document.getElementById('mesin').disabled=false;
    document.getElementById('tglOrder').disabled=false;
    document.getElementById('jmOrder').disabled=false;
    document.getElementById('mnOrder').disabled=false;
    
}


