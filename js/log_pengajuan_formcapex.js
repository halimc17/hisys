function add_new_data()
{
    document.getElementById('header').style.display = 'block';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('persetujuan').style.display = 'none';
    document.getElementById('listdata').style.display = 'none';
    cancelheader();  
}

function displayList()
{
    document.getElementById('notranscr').value='';
    document.getElementById('tglcr').value='';
    document.getElementById('listdata').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('persetujuan').style.display = 'none';
    loadData(0);
}

function get_notrans(){
    notrans = document.getElementById('notrans').value;
    tgltrans= document.getElementById('tgltrans').value;
    unit    = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;

    if (unit==''){
        alert('Unit harus dipilih. ');
        return;
    }

    method  = document.getElementById('method').value;
    param   ='notrans='+notrans+'&tgltrans='+tgltrans+'&unit='+unit+'&method='+method;
    tujuan  ='log_slave_pengajuan_formcapex.php';
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
                    //alert(con.responseText);
                    document.getElementById('notrans').value=trim(con.responseText);
                    detail();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function detail(){
    notrans = document.getElementById('notrans').value;
    tgltrans = document.getElementById('tgltrans').value;
    unit  = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
    param='notrans='+notrans+'&tgltrans='+tgltrans+'&unit='+unit+'&method=detail';
    tujuan='log_slave_pengajuan_formcapex.php';
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
                } else {
                    document.getElementById('detail').style.display = 'block';
                    document.getElementById('detail').innerHTML = con.responseText;
                    loaddatadetail(notrans);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function searchBrg(title,content,ev)
{
    width='auto';
    height='auto';
    showDialog1(title,content,width,height,ev);
    //alert('asdasd');
}

function findBrg(){
    txt=trim(document.getElementById('no_brg').value);
    if(txt==''){
        alert('Text is obligatory');
    }else if(txt.length<1){
        alert('Too short words');
    }else{
        param='txtfind='+txt+'&method=cariBarangDlmDtBs';
        // alert(param);
        // return;
        tujuan='log_slave_pengajuan_formcapex.php';
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
                } else {
                        //alert(con.responseText);
                        document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }      
}

function setBrg(kdbrg,nmbrg){
     document.getElementById('kdbrg').value=kdbrg;
     document.getElementById('nmbrg').value=nmbrg;
     closeDialog();
}

function savedetail(){
    notrans=document.getElementById('notrans').value;
    kdbrg=document.getElementById('kdbrg').value;
    jumlah=document.getElementById('jumlah').value;
    hrgsatuan=document.getElementById('hrgsatuan').value;
    tgleta=document.getElementById('tgleta').value;
    catatan=document.getElementById('catatan').value;
    method=document.getElementById('methoddt').value;

    param='kdbrg='+kdbrg+'&jumlah='+jumlah+'&hrgsatuan='+hrgsatuan+'&tgleta='+tgleta+'&catatan='+catatan+'&notrans='+notrans;
    param+='&method='+method;
    //alert(param);
    tujuan='log_slave_pengajuan_formcapex.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    cleardt();
                    loaddatadetail(notrans);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cleardt()
{
    document.getElementById('kdbrg').value='';
    document.getElementById('nmbrg').value='';
    document.getElementById('jumlah').value='';
    document.getElementById('hrgsatuan').value='';
    document.getElementById('tgleta').value='';
    document.getElementById('catatan').value='';
    document.getElementById('methoddt').value='insertdt'
}

function loaddatadetail(notrans){
    
    document.getElementById('dtl_ajuan').disabled=true;
    document.getElementById('notrans').disabled=true;
    document.getElementById('unit').disabled=true;
   
    param = 'method=loaddatadetail';
    param += '&notrans=' +notrans;
    tujuan = 'log_slave_pengajuan_formcapex.php';
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
                else {
                    document.getElementById('loaddatadetail').innerHTML = con.responseText;
					loadfiles(notrans);
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

function deldt(notrans,kdbrg)
{
    //alert('masukk');
    param='method=deldt'+'&notrans='+notrans+'&kdbrg='+kdbrg;
    //alert(param);
    tujuan='log_slave_pengajuan_formcapex.php';
    if(confirm(' Anda yakin ingin menghapus pengajuan ini?'))
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
                                       loaddatadetail(notrans);
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
              } 
    }
}

function editdt(notrans,kdbrg,nmbrg,jumlah,hrgsatuan,tgleta,catatan){
    document.getElementById('kdbrg').value=kdbrg;
    document.getElementById('nmbrg').value=nmbrg;
    document.getElementById('jumlah').value=jumlah;
    document.getElementById('hrgsatuan').value=hrgsatuan;
    document.getElementById('tgleta').value=tgleta;
    document.getElementById('catatan').value=catatan;
    document.getElementById('methoddt').value='updatedt';
    loaddatadetail(notrans);
}

function formpersetujuan(){
    notrans=document.getElementById('notrans').value;
    param='method=formpersetujuan'+'&notrans='+notrans;
    //alert(param);
    tujuan='log_slave_pengajuan_formcapex.php';
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
                    document.getElementById('persetujuan').style.display = 'block';
                    document.getElementById('detail').style.display = 'none';
                    document.getElementById('header').style.display = 'none';
                    document.getElementById('persetujuan').innerHTML = con.responseText;
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

function simpan(count) {
    notrans=document.getElementById('notrans').value;
    strUrl='';
	if(count=='0')
	{
		alert('Please contact administrator to setup Approval');
        return false;
	}
	else
	{
		for(i=1;i<=count;i++)
		{
			persetujuan = document.getElementById('persetujuan'+i).options[document.getElementById('persetujuan'+i).selectedIndex].value;
			if(persetujuan=='')
			{
				alert("Please compelete Approval");
				return;
			}
			strUrl += '&persetujuan['+i+']='+persetujuan;
		}
	}
	
    method=document.getElementById('methodht').value;

    param='notrans='+notrans;
    param+='&method='+method;
	param+=strUrl;

    tujuan='log_slave_pengajuan_formcapex.php';
    post_response_text(tujuan, param, respon);
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // cancel();
                    cancelheader();
                    displayList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    //post_response_text('keu_slave_tagihan.php?proses=add', param, respon);
}

function cancel(){
    // document.getElementById("diperiksa1").selectedIndex = "0";
    // document.getElementById("diperiksa2").selectedIndex = "0";
    // document.getElementById("budget").selectedIndex = "0";
    // document.getElementById("menyetujui1").selectedIndex = "0";
    // if (subtotal>50000000){
    // document.getElementById("menyetujui2").selectedIndex = "0";
    // }
}

function cancelheader(){
    document.getElementById("unit").selectedIndex = "0";
    document.getElementById("notrans").value = "";
    document.getElementById("unit").disabled = false;
    document.getElementById("dtl_ajuan").disabled = false;
}

function loadData(num){
    notranscr=document.getElementById('notranscr').value;
    tglcr=document.getElementById('tglcr').value;

    param='method=loadData';
    param+='&page='+num;

    if (notranscr != '') {
        param += '&notranscr=' + notranscr;
    }
    if (tglcr != '') {
        param += '&tglcr=' + tglcr;
    }
    tujuan='log_slave_pengajuan_formcapex.php';
    // alert(param);
    // return;
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    //alert(con.responseText);
                    //document.getElementById('container').innerHTML=con.responseText;
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }else{
                busy_off();
          error_catch(con.status);
            }
        }   
    }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);  
}

function delht(notrans)
{
    param='method=delht'+'&notrans='+notrans;
    tujuan='log_slave_pengajuan_formcapex.php';
    if(confirm(' Anda yakin ingin menghapus pengajuan ini?'))
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
                   displayList();  
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function editht(notrans,unit,tgltrans)
{
    document.getElementById('notrans').value=notrans;
    document.getElementById('unit').value=unit;
    document.getElementById('tgltrans').value=tgltrans;
    document.getElementById('listdata').style.display='none';
    document.getElementById('header').style.display='block';
    detail(notrans,unit,tgltrans);
}

function ajukan(notrans)
{
    param='method=ajukan'+'&notrans='+notrans;
    tujuan='log_slave_pengajuan_formcapex.php';
    if(confirm('Anda yakin ingin mengajukan ini ??'))
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
				}else {
					displayList();
                    //loaddata();
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		} 
    }
}

function form()
{
    width = '700';
    height = '';
    //nopp=document.getElementById('nopp_'+id).value;
    content = "<fieldset><div id=containerd align=center style=\"width:680px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}


function viewdetail(notrans)
{
    form();
    param = 'method=viewdetail' + '&notrans=' + notrans;
    tujuan = 'log_slave_pengajuan_formcapex.php';
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
					loadfiles(notrans);
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

function showupload(ev) {
	showformupload(ev);
	notrans = document.getElementById('notrans').value;
	param = 'method=showupload&notrans=' + notrans;
	tujuan = 'log_slave_pengajuan_formcapex.php';
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
					loadfiles(notrans);
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

function loadfiles(notrans) {
	param = 'method=loadfiles&notrans=' + notrans;
	tujuan = 'log_slave_pengajuan_formcapex.php';
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
	var notrans = document.getElementById("notrans").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("notrans", notrans);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "log_slave_pengajuan_formcapex.php?method=submitfile", true);
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
					loadfiles(notrans);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notrans, namafile) {
	param = 'method=deletefile&notrans=' + notrans + '&namafile=' + namafile;
	tujuan = 'log_slave_pengajuan_formcapex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(notrans);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}