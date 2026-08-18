var menuDisplayed = false;
var menuBox = null;

function rightclick(id,elename,ev,tipe='0')
{
	if (ev.which == 3) 
	{
		pos = new Array();
		pos = getMouseP(ev);
		
		menuBox = window.document.querySelector(".menu");
		menuBox.style.left = pos[0] + "px";
		menuBox.style.top = pos[1] + "px";
		menuBox.style.display = "block";
		menuDisplayed = true;
		
		if(tipe=='0'){
			document.getElementById('btnrestore').style.display = 'none';
			document.getElementById('btnrename').style.display = '';
			document.getElementById('btndelete').style.display = '';
			document.getElementById('btnreloadframe').style.display = '';
			
			document.getElementById('btnrename').onclick = function(){ refile(id,elename,ev); };
			document.getElementById('btndelete').onclick = function(){ deletefile(id); };
			document.getElementById('btnreloadframe').onclick = function(){ reloadframe(); };
		}else if(tipe=='rb'){
			document.getElementById('btnrestore').style.display = '';
			document.getElementById('btnrename').style.display = 'none';
			document.getElementById('btndelete').style.display = '';
			document.getElementById('btnreloadframe').style.display = '';
			
			document.getElementById('btnrestore').onclick = function(){ restore(id); };
			document.getElementById('btndelete').onclick = function(){ deletefilerb(id); };
			document.getElementById('btnreloadframe').onclick = function(){ reloadframe(); };
		}else{
			document.getElementById('btnrestore').style.display = 'none';
			document.getElementById('btnrename').style.display = 'none';
			document.getElementById('btndelete').style.display = 'none';
			document.getElementById('btnreloadframe').style.display = '';
			
			document.getElementById('btnrename').onclick = function(){ refile(id,elename,ev); };
			document.getElementById('btndelete').onclick = function(){ deletefile(id); };
			document.getElementById('btnreloadframe').onclick = function(){ reloadframe(); };
		}
	}
}

document.addEventListener("contextmenu", function (e) 
{
	e.preventDefault();
}, false);

window.addEventListener("click", function() 
{
	if(menuDisplayed == true)
	{
		menuBox.style.display = "none"; 
	}
	
	if(txtDisplayed == true)
	{
		txtBox.style.display = "none"; 
		lblBox.style.display = ""; 
	}
}, true);

var txtDisplayed = false;
var lblBox = null;
var txtBox = null;

function restore(id)
{
	param='method=restorefile'+'&id='+id;
	tujuan='slave_rencanagis.php';
	if(confirm('Are you sure restore this item?'))
	{
		post_response_text(tujuan, param, respog);
	}
	
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
					alert("Success");
					openrb();
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

function deletefilerb(id)
{
	param='method=deletefilerb'+'&id='+id;
	tujuan='slave_rencanagis.php';
	if(confirm('Are you sure delete permanently this item?'))
	{
		post_response_text(tujuan, param, respog);
	}
	
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
					alert("Success");
					openrb();
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

function refile(id,elename,ev)
{
	txtDisplayed = true;
	
	lblBox = document.getElementById(elename+''+id);
	lblBox.style.display = "none";
	
	splitname = lblBox.innerHTML.split('.');
	
	
	txtBox = document.getElementById(elename+'x'+id);
	txtBox.style.display='';
	txtBox.value=splitname[0];
	txtBox.focus();
	txtBox.onkeypress=function(e){
		key = getKey(e);
		if(key==13)
		{
			renamefile(id);
		}
	};
}

function renamefile(id)
{
	lblname = lblBox.innerHTML;
	txtname	= txtBox.value;
	if(txtname=='')
	{
		alert("You must type a file name");
		return;
	}
	if(txtname==lblname)
	{
		 
	}
	else
	{
		param='method=renamefile'+'&id='+id+'&oldname='+lblname+'&newname='+txtname;
		tujuan='slave_rencanagis.php';
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
						loaddata(con.responseText);
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
	txtBox.style.display = "none"; 
	lblBox.style.display = "";
}

function reloadframe()
{
	window.location.reload();
}

function deletefile(id)
{
	param='method=deletefile'+'&id='+id;
	tujuan='slave_rencanagis.php';
	if(confirm('Are you sure delete this item?'))
	{
		post_response_text(tujuan, param, respog);
	}
	
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
					alert("Success");
					loaddata(con.responseText);
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

function downloadfile(id)
{
	var iframe = document.getElementById('invsdownload');
	var ahref = document.getElementById('invsdownload2');
	
	param='method=downloadfile'+'&id='+id;
	tujuan='slave_rencanagis.php';
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
					ahref.setAttribute('href',con.responseText);
					ahref.setAttribute('download',"");
					document.getElementById('invsdownload2').click();
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

function upload(title,method,ev)
{
	path = document.getElementById('lbldatapath').innerHTML;
	
	closeDialog();
	content= "<div style='width:100%;'>";
	content+="<fieldset><div id=divpopup style='overflow:auto;min-height:50px;min-width:300px'></div></fieldset>";
    width='auto';
	height='auto';
	showDialog1(title,content,width,height,ev);
	
	var left = (screen.width/2)-(150);
	var top = (screen.height/2)-(150);
	document.getElementById('dynamic1').style.top = top+'px';
	document.getElementById('dynamic1').style.left = left+'px';
	
	param='method='+method;
	tujuan='slave_rencanagis.php';
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
					document.getElementById('divpopup').innerHTML=con.responseText;
					document.getElementById('uploadpath').innerHTML=path;
					
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

function uploadfile()
{
	var id = document.getElementById("id").value;
	var uploadpath = document.getElementById("uploadpath").innerHTML;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("id", id);
	formdata.append("uploadpath", uploadpath);
	
	if(getValue('upload')=="")
	{
		alert("warning : Upload file has been empty.");
		return false;
	}
	
	var con = createXMLHttpRequest();
	con.open("POST", "slave_rencanagis.php?method=uploadfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
    
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
					cancelpopup();
                    alert('Uploaded Success.');
					loaddata(id);
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

function newfolder(title,method,ev)
{
	closeDialog();
	content= "<div style='width:100%;'>";
	content+="<fieldset><div id=divpopup style='overflow:auto;min-height:50px;min-width:300px'></div></fieldset>";
    width='auto';
	height='auto';
	showDialog1(title,content,width,height,ev);
	
	var left = (screen.width/2)-(150);
	var top = (screen.height/2)-(150);
	document.getElementById('dynamic1').style.top = top+'px';
	document.getElementById('dynamic1').style.left = left+'px';
	
	param='method='+method;
	tujuan='slave_rencanagis.php';
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
					document.getElementById('divpopup').innerHTML=con.responseText;
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

function cancelpopup()
{
	closeDialog();
}

function createfolder()
{
	id = document.getElementById('id').value;
	lbldatapath = document.getElementById('lbldatapath').innerHTML;
	foldername = trim(document.getElementById('foldername').value);
	level = trim(document.getElementById('level').value);
	
	if(foldername=='')
	{
		alert("Warning : Folder Name is Empty!");
		return;
	}
	
	param='method=createfolder'+'&lbldatapath='+lbldatapath+'&id='+id+'&level='+level+'&foldername='+foldername;
	tujuan='slave_rencanagis.php';
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
					cancelpopup();
                    alert('Uploaded Success.');
					loaddata(id);
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

function loaddata(id)
{
	if(id=='' || id=='0')
	{
		document.getElementById('id').value='';
		document.getElementById('induk').value='';
		document.getElementById('level').value='';
		document.getElementById('previd').value='';
		loadall();
	}
	else
	{
		imgfolder = document.getElementById('imgfolder_'+id);
		imgfolder.setAttribute('title','Expand');
		xxx=imgfolder.src;
		if((xxx.indexOf('images/archive.png') > 1) || (xxx.indexOf('images/archive1.png')) > 1){
			img = 'x';
		}else{
			img = '0';
		}
		openfolder(id,img);
	}
}

function search()
{
	ptx=document.getElementById('pt').value;
	unitx=document.getElementById('unit').value;
	supplierx=document.getElementById('sup').value;
	periodex1=document.getElementById('periodex1').value;
	periodex2=document.getElementById('periodex2').value;
	namnox=document.getElementById('namno').value;
	novox=document.getElementById('novo').value;

	if(periodex1=='' || periodex2=='')
	{
		alert('Warning : Periode cannot be null');
	}
	else
	{
		param='method=searchdatax'+'&pt='+ptx+'&unit='+unitx+'&supplierx='+supplierx+'&periodex1='+periodex1;
		param+='&periodex2='+periodex2+'&namno='+namnox+'&novo='+novox;
		tujuan='slave_rencanagis.php';
		post_response_text(tujuan, param, respog);
		
		
	}

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
						
						splitval = (con.responseText).split("####");
						document.getElementById('ulfolder_'+id).innerHTML=splitval[0];
						document.getElementById('tbodyright').innerHTML=splitval[1];
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

function levelup()
{
	id=document.getElementById('id');
	tipe=document.getElementById('tipefl').value;
	if(id.value!='')
	{
		if(tipe=='rb'){
			home();
		}else if(tipe=='dc'){
			if(id.value=='1'){
				home();
			}else{
				opendc(id.value);
			}
		}else{
			if(id.value=='1'){
				openfolder(id.value,'0');
			}else{
				openfolder(id.value,'','x');
			}
		}
	}
}

function home()
{
	id = document.getElementById('id').value;
	
	if(id!='')
	{
		document.getElementById('id').value='';
		document.getElementById('induk').value='';
		document.getElementById('level').value='';
		document.getElementById('previd').value='';
		
		document.getElementById('divfile').style.display='block';
		document.getElementById('divrb').style.display='none';
		document.getElementById('divdc').style.display='none';
		
		document.getElementById('lbldatapath').innerHTML = ''; 
		
		loadall();
	}
}

function loadall()
{
	document.getElementById('btnaction3').style.display='';
	document.getElementById('btnaction4').style.display='';
	
	id=document.getElementById('id').value;
	induk=document.getElementById('induk').value;
	level=document.getElementById('level').value;
	previd=document.getElementById('previd').value;
	
	param='method=loadall'+'&id='+id+'&induk='+induk+'&level='+level+'&previd='+previd;
	tujuan='slave_rencanagis.php';
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
					splitval = (con.responseText).split("####");
					document.getElementById('ulfolder_'+id).innerHTML=splitval[0];
					document.getElementById('tbodyright').innerHTML=splitval[1];
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

function opensearch(title,method,ev)
{
	path = document.getElementById('lbldatapath').innerHTML;
	
	closeDialog();
	content= "<div style='width:100%;'>";
	content+="<fieldset><div id=divpopupx style='overflow:auto;min-height:50px;min-width:300px'>"+title+"</div></fieldset>";
    width='auto';
	height='auto';
	showDialog4(title,content,width,height,ev);
	
	var left = (screen.width/2)-(150);
	var top = (screen.height/2)-(150);
	document.getElementById('dynamic4').style.top = top+'px';
	document.getElementById('dynamic4').style.left = left+'px';
	
	param='method='+method;
	tujuan='slave_rencanagis.php';
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
					document.getElementById('divpopupx').innerHTML=con.responseText;
					
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

function openfolder(id,tipe='0',ubah='0')
{
	if(ubah=='0'){
		if(tipe=='0'){
			document.getElementById('btnaction3').style.display='';
			document.getElementById('btnaction4').style.display='';
		}else{
			document.getElementById('btnaction3').style.display='none';
			document.getElementById('btnaction4').style.display='none';
		}
	}
	
	document.getElementById('divfile').style.display='block';
	document.getElementById('divrb').style.display='none';
	document.getElementById('divdc').style.display='none';
	
	document.getElementById('tipefl').value='';
	
	imgfolder = document.getElementById('imgfolder_'+id);
	title = imgfolder.getAttribute("title");
	lblfolder = document.getElementById('lblfolder_'+id);
	liplus = document.getElementById('liplus_'+id);
	
	param='method=openfolder'+'&id='+id;
	tujuan='slave_rencanagis.php';
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
					splitval = (con.responseText).split("####");
					imgclass = document.getElementsByClassName('imgfolder_'+splitval[0]);
					ulclass = document.getElementsByClassName('ullevel_'+splitval[0]);
					document.getElementById('id').value=id;
					document.getElementById('tbodyright').innerHTML=splitval[2];
					document.getElementById('previd').value=splitval[3];
					if(splitval[5] > 0)
					{
						liplus.style.display = "block";
					}
					else
					{
						liplus.style.display = "none";
					}
					
					if (title == 'Expand') 
					{
						for (i = 0; i < imgclass.length; i++) 
						{
							xxx=imgclass[i].src;
							if((xxx.indexOf('images/archive.png') > 1) || (xxx.indexOf('images/archive1.png')) > 1){
								imgclass[i].src='images/archive.png';
							}else{
								imgclass[i].src='images/foldc_.png';
							}
							
							// imgclass[i].src='images/archive.png';
							// if(xxx=='images/foldc.png' || xxx=='images/foldc_.png'){
								// imgclass[i].src='images/foldc_.png';								
							// }
							imgclass[i].setAttribute('title','Expand');
							ulclass[i].style.display='none';
						}
						imgfolder.src='images/foldo.png';
						if(id=='1'){
							imgfolder.src='images/archive1.png';							
						}
						imgfolder.setAttribute('title','Collaps');
						lblfolder.setAttribute('title','Collaps');
						document.getElementById('ullevel_'+id).innerHTML = splitval[1];
						document.getElementById('ullevel_'+id).style.display = '';
						document.getElementById('lbldatapath').innerHTML = splitval[4];
					}
					else
					{
						for (i = 0; i < imgclass.length; i++) 
						{
							ulclass[i].style.display='none';
						}
						imgfolder.src='images/foldc_.png';
						if(id=='1'){
							imgfolder.src='images/archive.png';
						}
						imgfolder.setAttribute('title','Expand');
						lblfolder.setAttribute('title','Expand');
						document.getElementById('id').value=splitval[3];
						if(splitval[0]=='0')
						{
							document.getElementById('id').value='';
							document.getElementById('previd').value='';
						}
						loadright(splitval[3]);
						splitpath = splitval[4].split('/');
						countpath = parseFloat(splitpath.length)-2;
						
						temppath = '';
						valpath = '';
						for (i = 0; i < countpath; i++) 
						{
							valpath = temppath+''+splitpath[i]+'/';
							temppath = valpath;
						}
						document.getElementById('lbldatapath').innerHTML = valpath;
					}
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

function gantiunit()
{

	pt = document.getElementById('pt').value;
	param='method=gantiunit'+'&pt='+pt;
	tujuan='slave_rencanagis.php';
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
					document.getElementById('unit').innerHTML=con.responseText;
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

function loadright(id)
{
	param='method=loadright'+'&id='+id;
	tujuan='slave_rencanagis.php';
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
					document.getElementById('tbodyright').innerHTML=con.responseText;
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

function openrb()
{
	document.getElementById('divfile').style.display='none';
	document.getElementById('divrb').style.display='block';
	document.getElementById('divdc').style.display='none';
	
	document.getElementById('btnaction3').style.display='none';
	document.getElementById('btnaction4').style.display='none';
	
	document.getElementById('tipefl').value='rb';
	
	loadrightrb();
}

function loadrightrb()
{
	document.getElementById('id').value = "rb";
	param='method=loadrightrb';
	tujuan='slave_rencanagis.php';
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
					document.getElementById('tbodyrightrb').innerHTML=con.responseText;
					document.getElementById('lbldatapath').innerHTML = "Recycle Bin/";
					imgclass = document.getElementsByClassName('imgfolder_0');
					ulclass = document.getElementsByClassName('ullevel_0');
					for (i = 0; i < imgclass.length; i++) 
					{
						xxx=imgclass[i].src;
						if((xxx.indexOf('images/archive.png') > 1) || (xxx.indexOf('images/archive1.png')) > 1){
							imgclass[i].src='images/archive.png';
						}else{
							imgclass[i].src='images/foldc_.png';
						}
						ulclass[i].style.display='none';
					}
					// imgfolder.src='images/foldc_.png';
					imgfolder.setAttribute('title','Expand');
					lblfolder.setAttribute('title','Expand');
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

function opendc(id)
{
	document.getElementById('btnaction3').style.display='none';
	document.getElementById('btnaction4').style.display='none';
	
	document.getElementById('divfile').style.display='none';
	document.getElementById('divrb').style.display='none';
	document.getElementById('divdc').style.display='';
	
	imgfolder = document.getElementById('imgfolder_'+id);
	title = imgfolder.getAttribute("title");
	lblfolder = document.getElementById('lblfolder_'+id);
	liplus = document.getElementById('liplus_'+id);
	
	param='method=opendc'+'&id='+id;
	tujuan='slave_rencanagis.php';
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
					splitval = (con.responseText).split("####");
					imgclass = document.getElementsByClassName('imgfolder_'+splitval[0]);
					ulclass = document.getElementsByClassName('ullevel_'+splitval[0]);
					document.getElementById('id').value=id;
					document.getElementById('tbodyright').innerHTML=splitval[2];
					document.getElementById('previd').value=splitval[3];
					if(splitval[5] > 0)
					{
						liplus.style.display = "block";
					}
					else
					{
						liplus.style.display = "none";
					}
					
					if (title == 'Expand') 
					{
						for (i = 0; i < imgclass.length; i++) 
						{
							imgclass[i].src='images/foldc_.png';
							imgclass[i].setAttribute('title','Expand');
							ulclass[i].style.display='none';
						}
						imgfolder.src='images/foldo.png';
						imgfolder.setAttribute('title','Collaps');
						lblfolder.setAttribute('title','Collaps');
						document.getElementById('ullevel_'+id).innerHTML = splitval[1];
						document.getElementById('ullevel_'+id).style.display = '';
						document.getElementById('lbldatapath').innerHTML = splitval[4];
						document.getElementById('tipefl').value='dc';
					}
					else
					{
						for (i = 0; i < imgclass.length; i++) 
						{
							ulclass[i].style.display='none';
						}
						imgfolder.src='images/foldc_.png';
						imgfolder.setAttribute('title','Expand');
						lblfolder.setAttribute('title','Expand');
						document.getElementById('id').value=splitval[3];
						if(splitval[0]=='0')
						{
							document.getElementById('id').value='';
							document.getElementById('previd').value='';
						}
						loadright(splitval[3]);
						splitpath = splitval[4].split('/');
						countpath = parseFloat(splitpath.length)-2;
						
						temppath = '';
						valpath = '';
						for (i = 0; i < countpath; i++) 
						{
							valpath = temppath+''+splitpath[i]+'/';
							temppath = valpath;
						}
						document.getElementById('lbldatapath').innerHTML = valpath;
						document.getElementById('tipefl').value='';
					}
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

function loadrightdc()
{
	document.getElementById('id').value = "dc";
	param='method=loadrightdc';
	tujuan='slave_rencanagis.php';
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
					document.getElementById('tbodyrightdc').innerHTML=con.responseText;
					document.getElementById('lbldatapath').innerHTML = "Documents/";
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