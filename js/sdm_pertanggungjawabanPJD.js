/**
 * @author repindra.ginting
 */
 
 document.addEventListener("load",function(){
	getNotransPJD(); 
 });
 
function savePPJD()
{
	notransaksi	= document.getElementById('notransaksi');
	notransaksi	=notransaksi.options[notransaksi.selectedIndex].value;
	tanggal		= document.getElementById('tanggal').value;
	jenisby		= document.getElementById('jenisby');
	jenisby		=jenisby.options[jenisby.selectedIndex].value;
	keterangan	= document.getElementById('keterangan').value;
	jumlah		= remove_comma(document.getElementById('jumlah'));
	method		= document.getElementById('method').value;
 
		if (notransaksi == '' || tanggal == '' || jenisby=='') {
			alert('Transaction number,date and cost type are obligatory');
		}
		else {
			param ='notransaksi='+notransaksi+'&tanggal='+tanggal;
			param +='&jenisby='+jenisby+'&keterangan='+keterangan; 
			param +='&jumlah='+jumlah+'&method='+method;
			if (confirm('Saving, are you sure..?')) {
				tujuan = 'sdm_slave_savePertanggungjawabanPJDinas.php';
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
					document.getElementById('innercontainer').innerHTML=con.responseText;
					alert('Saved');
					if(document.getElementById('notransaksi1')){
					document.getElementById('notransaksi1').disabled=false;
					}
					clearForm2();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
		
}
function previewPJD(notransaksi,ev)
{
	 nosk=notransaksi;	
	param='notransaksi='+nosk;
	tujuan = 'sdm_slave_printPJD_pdf.php?'+param;	
	//tujuan = 'sdm_slave_printPtjwbPJD_pdf.php?'+param;	
 //display window
   title=nosk;
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);
   
}
function simpanUraianPjDinas()
{
	if(document.getElementById('notransaksi1')){
	notransaksi	= document.getElementById('notransaksi1');
	}
	notransaksi	=notransaksi.options[notransaksi.selectedIndex].value;	
	uraian=document.getElementById('uraian').value;
	if(notransaksi=='' || uraian=='')
	{
		alert('Transaction Number and Descaription are obligatory');
	}
	else
	{
	   param='notransaksi='+notransaksi+'&uraian='+uraian;
			if (confirm('Saving, are you sure..?')) {
				tujuan = 'sdm_slave_savePertanggungjawabanPJDinasUraian.php';
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
					alert('Saved Successfull');
					document.getElementById('uraian').value='';
					loadum();
					
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}



function loadum(){//load uang muka
	notransaksi	= document.getElementById('notransaksi');
	notransaksi	=notransaksi.options[notransaksi.selectedIndex].value;	
	if(notransaksi==''){
		alert('Transaction Number and Descaription are obligatory');
	} else {
		param ='notransaksi='+notransaksi+'&method=loadum';
		tujuan = 'sdm_slave_savePertanggungjawabanPJDinas.php';
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
					document.getElementById('innercontainer').innerHTML=con.responseText;
					cariPJD(0);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function removeerrorflagPjd(e){
	if(typeof e === "undefined"){
		eleArr = document.getElementsByClassName("errorflag");
	}else{
		eParentNode = e.parentNode;
		eleArr = eParentNode.getElementsByClassName("errorflag");
	}
	if(eleArr.length > 0){
		eleArr[0].remove();
		if(typeof e === "undefined"){
			errorflagRemove();
		}
	}
}
function bysimpan(){
	removeerrorflagPjd();
    notransaksi=document.getElementById('notransaksi').value;
	bytgl1=document.getElementById('bytgl1').value;
	//bytgl2=document.getElementById('bytgl2').value;	
	//bykel=document.getElementById('bykel').value;	
	
	
	formkelompok = document.getElementById('formkelompok');
	allinput = formkelompok.getElementsByTagName('input');
	allselection = formkelompok.getElementsByTagName('select');	
	
	bydet=document.getElementById('bydet').value;	
	//byrp=document.getElementById('byrp').value;	
	byket=document.getElementById('byket').value;	
	
	if(notransaksi==''){
        alert('No Transaksi kosong!');
		document.getElementById('notransaksi').focus();
		return false;
    }
    if(bytgl1=='' || allinput.length == 0 || bydet==''){
        alert('Lengkapi pengisian');return false;
    }
	paramArray = "";
	for(i=0; i<allinput.length; i++){
		var name = allinput[i].getAttribute('name');
		// if(allinput[i].value!= '0' && allinput[i].value != ''){
			paramArray += "&"+name+"="+allinput[i].value;
		// }
	}
	if(allselection.length > 0){
		for(i=0; i<allselection.length; i++){
			name_select = allselection[i].getAttribute('name');
			selectedNode = allselection[i].options[allselection[i].selectedIndex];
			paramArray += "&"+name_select+"="+selectedNode.value;
		}
	}
	
	param='method=inserttgjwb'+'&notransaksi='+notransaksi+'&bytgl1='+bytgl1+'&bytgl2='+bytgl2;
	param+='&bydet='+bydet+'&byket='+byket+paramArray;
    tujuan='sdm_slave_bypjdinas.php';
    post_response_text(tujuan, param, respog);		
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
					try{
						var dataArr = JSON.parse(con.responseText);
						if(dataArr.status == 'false'){
							var dataError = dataArr.message;
							var span = document.createElement("span");
							for (var key in dataError) {
								if (dataError.hasOwnProperty(key)) {
								    if(document.getElementById(key)){
									   mssg = dataError[key];
									   var spanCln = span.cloneNode(true);
									   spanCln.className = "errorflag";
									   spanCln.innerHTML = "<font color='red'>"+mssg+"</font>";
									   document.getElementById(key).parentNode.appendChild(spanCln);
									   
								    }
								}
							}
						}else{
							byclear();							
							document.getElementById('innercontainer').innerHTML=con.responseText;
							//alert('Saved');
							if(document.getElementById('notransaksi1')){
							document.getElementById('notransaksi1').disabled=false;
							}
							//clearForm2();
						}
					}catch(e){
						console.log(e);
						console.log(con.responseText);
						byclear();							
						document.getElementById('innercontainer').innerHTML=con.responseText;
						//alert('Saved');
						if(document.getElementById('notransaksi1')){
						document.getElementById('notransaksi1').disabled=false;
						}
						//clearForm2();
					}
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }
}

function byclear(){
	document.getElementById('bytgl1').value='';
	document.getElementById('bytgl2').value='';	
	//document.getElementById('bykel').value='';	
	document.getElementById('bydet').value='';	
	//document.getElementById('byrp').value='';	
	document.getElementById('byket').value='';	
	loadum();
}	




function previewPJDUraian(notransaksi,ev)
{
	 nosk=notransaksi;	
	param='notransaksi='+nosk;
	tujuan = 'sdm_slave_printPtjwbPJDUraian_pdf.php?'+param;	
 //display window
   title=nosk;
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);
   
}

function previewPJDOri(ev)
{
    try {
		nosk = document.getElementById('notransaksi').options[document.getElementById('notransaksi').selectedIndex].value;
	}
	catch(err)
	{
	 nosk='';	
	}
	param='notransaksi='+nosk;
	tujuan = 'sdm_slave_printPJD_pdf.php?'+param;	
	//tujuan = 'sdm_slave_printPtjwbPJD_pdf.php?'+param;	
 //display window
   title=nosk;
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);
   
}

function clearForm()
{
	notransaksi	= document.getElementById('notransaksi');
				notransaksi.options[0].selected=true;
	if(document.getElementById('notransaksi1')){
	document.getElementById('notransaksi1').disabled=false;
	}
	// document.getElementById('notransaksi').disabled=false;
	/*
	document.getElementById('tanggal').value='';
	jenisby		= document.getElementById('jenisby');
				jenisby.options[0].selected=true;
	document.getElementById('keterangan').value='';
	document.getElementById('jumlah').value=0;
	*/
}

function clearForm2()
{
	notransaksi	= document.getElementById('notransaksi');
				notransaksi.options[0].selected=true;
	
	document.getElementById('notransaksi').disabled=false;
	/*
	document.getElementById('tanggal').value='';
	jenisby		= document.getElementById('jenisby');
				jenisby.options[0].selected=true;
	// document.getElementById('notransaksi1').disabled=false;
	
	document.getElementById('keterangan').value='';
	document.getElementById('jumlah').value=0;
	*/
}

function deleteDetail(notransaksi,tanggal)
{	
			param ='notransaksi='+notransaksi+'&method=delete&tanggal='+tanggal;
			if (confirm('Deleting  are you sure..?')) {
				tujuan = 'sdm_slave_savePertanggungjawabanPJDinas.php';
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
					document.getElementById('innercontainer').innerHTML=con.responseText;
					cariPJD(0);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}

function selesai()
{
	document.getElementById('innercontainer').innerHTML='';
	if(document.getElementById('uraian')){
		document.getElementById('uraian').value='';
	}
  loadList();
  tabAction(document.getElementById('tabFRM1'),1,'FRM',1);
  clearForm(); 
  clearForm2(); 
}



function loadList()
{      num=0;
	 	param='&page='+num;
		tujuan = 'sdm_getPJDinasPertgjwbList.php';
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
					
function getNotransPJD()
{
	tex=trim(document.getElementById('txtbabp').value);
		param='method=getnotransaksi';
		tujuan = 'sdm_getPJDinasPertgjwbList.php';
		
		post_response_text(tujuan, param, respog);			
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						document.getElementById('notransaksi').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
}
function cariPJD(num)
{
	tex=trim(document.getElementById('txtbabp').value);
		param='&page='+num;
		if(tex!='')
			param+='&tex='+tex;
		tujuan = 'sdm_getPJDinasPertgjwbList.php';
		
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

function cariPJDUraian(num)
{
	tex=trim(document.getElementById('txtbabp').value);
		param='&page='+num;
		if(tex!='')
			param+='&tex='+tex;
		tujuan = 'sdm_getPJDinasPertgjwbUraianList.php';
		
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
function editPPJD(notransaksi)
{
	param ='notransaksi='+notransaksi;
	tujuan = 'sdm_slave_savePertanggungjawabanPJDinas.php';
	post_response_text(tujuan, param, respog);
	tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
		jk=document.getElementById('notransaksi');
		for(x=0;x<jk.length;x++)
		{
			if(jk.options[x].value==notransaksi)
			{
				jk.options[x].selected=true;
			}
		}
		if(document.getElementById('notransaksi1')){
			document.getElementById('notransaksi1').value=notransaksi;
			document.getElementById('notransaksi1').disabled=true;
		}
		document.getElementById('notransaksi').disabled=true;
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('innercontainer').innerHTML=con.responseText;
					loadUraian(notransaksi);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function loadUraian(notransaksi)
{
	param='notransaksi='+notransaksi;
	tujuan = 'sdm_slave_getPertanggungjawabanPJDinasUraian.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					if(document.getElementById('uraian')){
						document.getElementById('uraian').value=con.responseText;
					}
				//alert(con.responseText);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}
// author Atwal 
function removeObj(id){
	Element.prototype.remove = function() {
		this.parentElement.removeChild(this);
	}
	NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
		for(var i = this.length - 1; i >= 0; i--) {
			if(this[i] && this[i].parentElement) {
				this[i].parentElement.removeChild(this[i]);
			}
		}
	}
	document.getElementById(id).remove();
}
function remove_option(id,val,text_){
	var optionkelompok = document.getElementById('bykel');
	var opt = document.createElement('option');
	opt.setAttribute("value", val);
	opt.text = text_;
	optionkelompok.appendChild(opt);
	removeObj(id);
}
function create_new_field(id_name,title){
	var optionkelompok = document.getElementById('bykel');
	var option = optionkelompok.getElementsByTagName('option');
	var titletext =  title;
	var selectedNode = optionkelompok.options[optionkelompok.selectedIndex];
	if(!selectedNode){
		alert('('+titletext.namakelompok+') sudah habis!');
		return false;
	}
	var Judul = selectedNode.text;
	var value = selectedNode.value;
	
	var bothForAppend = document.getElementById(id_name);
	var last_num = parseInt(bothForAppend.getAttribute('rute-count'));
	next_	= (last_num + 1);
	bothForAppend.setAttribute('rute-count', next_);
	var fieldset = document.createElement('tr');
	fieldset.setAttribute("id", id_name+'_'+next_);
	fieldset.setAttribute("rute-num", next_);
	new_option = ""
	if(value == 3){
		new_option += '<select name="option_penginapan">';
		new_option += '<option value="1">Mess</option>';
		new_option += '<option value="2">Hotel</option>';
		new_option += '</select>';
	}
	_html = '<td width=10><a href="#" style="margin-right:5px;" title="'+titletext.delete+'" onclick="remove_option(\''+ id_name +'_'+ next_ +'\',\''+value+'\',\''+Judul+'\');"><img src="images/delete1.png" style="width:10px;"></a></td>';
	_html += '<td>'+Judul+' <input name="kodekelompok[]" type="hidden" value="'+value+'"></td>';
	_html += '<td>:</td>';
	_html += '<td><input id="rupiah_'+value+'" onkeyup="removeerrorflagPjd(this)" name="rupiah[]" class="myinputtextnumber" onkeypress="return angka_doang(event)"  style="width:70px" type="text" placeholder="0"><span>'+titletext.rupiah+'</span>&nbsp;'+new_option+'</td>';
	
	fieldset.innerHTML = _html;
	bothForAppend.appendChild(fieldset);
	selectedNode.remove();

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

function uploaddata(notrans)
{
    form();
    param = 'method=uploaddata' + '&notransaksi=' + notrans;
    tujuan = 'sdm_slave_bypjdinas.php';
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

function addfile(notransaksi){
    var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", document.getElementById("upload").value);
	formdata.append("kriteriaefil", getValue('kriteriaefil'));
	formdata.append("notransaksi", notransaksi);
	if (document.getElementById("upload").value == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "sdm_slave_bypjdinas.php?method=simpanupload", true);
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
	param = 'method=loadfiles&notransaksi='+notransaksi;
	tujuan = 'sdm_slave_bypjdinas.php';
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

function deletefile(notransaksi,namafile) {
	param = 'method=deletefile&namafile='+namafile+'&notransaksi='+notransaksi;
	tujuan = 'sdm_slave_bypjdinas.php';
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

function delfile (notrans,id) {
    param='method=delfile'+'&notransaksi='+notrans+'&id='+id;
    tujuan='sdm_slave_bypjdinas.php';
    post_response_text(tujuan, param, respog);
    function respog(){
          if(con.readyState==4){
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else {
                        uploaddata(notrans);
                    }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
          } 
    }  
}

function displayfile(doc,ev)
{
    param = 'method=displayfile' + '&doc=' + doc;
    title="Data Detail";
     showDialog4(title,"<iframe frameborder=0 style='width:795px;height:395px'"+
    " src='sdm_slave_bypjdinas.php?"+param+"'></iframe>",'800','400',ev); 
    var dialog = document.getElementById('dynamic4');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function posting(notransaksi)
{
    param='method=posting'+'&notransaksi='+notransaksi;
    tujuan='sdm_slave_bypjdinas.php';
    if(confirm(' Anda yakin ingin memposting transaksi ini?'))
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
					var result = con.responseText;
					if(result.trim() != ""){
						alert(con.responseText);
					}else{
						cariPJD(0);
						getNotransPJD();
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

// End