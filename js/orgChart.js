/*
 * @uthor:nangkoel@gmail.com
 * Indonesia 2009
 */

 activeOrg='';
 orgVal   ='';
 clos 	  =1;//this will STOP on the #9th child
 function saveOrg()
 {
 	_orgcode    = trim(document.getElementById('orgcode').value);
 	_orginitial = trim(document.getElementById('orginitial').value);
	_orgname    = trim(document.getElementById('orgname').value);
	_orgtype    = trim(document.getElementById('orgtype').value);
	_blokinduk    	= trim(document.getElementById('blokinduk').value);
	_namablokinduk  = trim(document.getElementById('namablokinduk').value);
	_orgidentnik= trim(document.getElementById('identnik').value);
	_orgadd     = trim(document.getElementById('orgadd').value);
	_orgcity    = trim(document.getElementById('orgcity').value);
	_orgcountry = document.getElementById('orgcountry').options[document.getElementById('orgcountry').selectedIndex].value;
	_alokasi 	= document.getElementById('alokasi').options[document.getElementById('alokasi').selectedIndex].value;
	_noakun 	= document.getElementById('noakun').options[document.getElementById('noakun').selectedIndex].value;
	_orgzip     = trim(document.getElementById('orgzip').value);
	_orgtelp    = trim(document.getElementById('orgtelp').value);
	_detail     = trim(document.getElementById('orgdetail').value);
	_tipepabrik = trim(document.getElementById('tipepabrik').value);
	_sustainable = trim(document.getElementById('sustainable').value);
	_sertifikat = trim(document.getElementById('sertifikat').value);
//response++++++++++++++++++++++++++++++++++++++++
		
		if(_orgidentnik=='4' || _orgidentnik=='5'){
			alert("Identify nik sudah terdaftar ganti dengan identify lainnya");
			return false;
		}

	   function respog(){
	   	//save active org on memory incase slow server response
			id         = activeOrg;
			newCaption = _orgcode;
	      if(con.readyState==4)
	      {
		        if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						if (id == 'HQ') {
						//just reload when org is HQ
						window.location.reload();
						}
						else if(id.lastIndexOf('_new')>-1)
						{
						  if (clos<9) {
						  	nex=clos+1;
						  	ne = "<li class=mmgr>";
						  	ne += "<img title=expand class=arrow src='images/foldc_.png' height=17px  onclick=show_sub('gr" + _orgcode + "',this);>";
						  	ne += "<a class=elink id='el" + _orgcode + "'  onclick=\"javascript:activeOrg=this.id;orgVal='" + orgVal + "';getCurrent('" + _orgcode + "');setpos('inputorg',event);\">" + _orgcode + "</a>";
						  	ne += "<ul id=gr" + _orgcode + " style='display:none;'>";
						  	ne += "<div id=main" + _orgcode + ">";
						  	ne += "</div>";
						  	ne += "<li class=mmgr>";
						  	ne += "<a id='" + _orgcode + "_new' class=elink title='Create Child'  onclick=\"javascript:orgVal='" + _orgcode + "';clos="+nex+";activeOrg='" + _orgcode + "_new';setpos('inputorg',event);\">New Org<a>";
						  	ne += "</li>";
						  	ne += "</ul>";
							ne += "</li>";
						  }
						  else
						  {
						  	ne = "<li class=mmgr>";
						  	ne += "<img title=expand class=arrow src='images/menu/arrow_8.gif'>";
						  	ne += "<a class=elink id='el" + _orgcode + "'  onclick=\"javascript:activeOrg=this.id;orgVal='" + orgVal + "';getCurrent('" + _orgcode + "');setpos('inputorg',event);\">" + _orgcode + "</a>";
                            ne += "</li>";					  	
						  }						
                          //alert('main'+orgVal);
						   document.getElementById('main'+orgVal).innerHTML+=ne;							
						}
						else {
							document.getElementById(id).innerHTML = newCaption;
							clearForm();
						}
					  hideById('inputorg');
					  clearForm();	
					}
				}
				else {busy_off();error_catch(con.status);}	
	      }	
	   }
//++++++++++++++++++++++++++++++++++++++++++++++++

	if(_orgcode.length==0 || _orgname.length==0)
	{
		alert('Org. Code and Org.Name is NULL');
	}		
	else
	{
		if(confirm('Save new Organization, Are you sure..?'))
		{
			param ='parent='	+orgVal;
			param+='&orgcode='	+_orgcode;
			param+='&orginitial='	+_orginitial;
			param+='&orgname='	+_orgname;
			param+='&orgtype='	+_orgtype;
			param+='&blokinduk='	+_blokinduk;
			param+='&namablokinduk='	+_namablokinduk;
			param+='&orgidentnik='	+_orgidentnik;
			param+='&orgadd='	+_orgadd;
			param+='&orgcity='	+_orgcity;
			param+='&orgcountry='+_orgcountry;												
			param+='&orgzip='	+_orgzip;	
			param+='&orgtelp='	+_orgtelp;
			param+='&orgdetail='+_detail;
			param+='&alokasi='+_alokasi;		
			param+='&noakun='+_noakun;		
param+='&tipepabrik='+_tipepabrik;
param+='&sustainable='+_sustainable;
param+='&sertifikat='+_sertifikat;			
		  post_response_text('slave_saveNewOrg.php', param, respog);
	      //alert(param);
	   }	
	}
 }
 
 function clearForm()
 {
  	document.getElementById('orgcode').value ='';
	document.getElementById('orgname').value ='';
	document.getElementById('orgtype').value ='';
	document.getElementById('blokinduk').value ='';
	document.getElementById('namablokinduk').value ='';
	document.getElementById('orgadd').value  ='';
	document.getElementById('orgcity').value ='';
    document.getElementById('orgzip').value  ='';
	document.getElementById('orgtelp').value ='';
	document.getElementById('alokasi').options[0].selected =true;
	document.getElementById('noakun').options[0].selected =true;
	document.getElementById('tridentnik').style.display ='none';
	document.getElementById('identnik').value =0;
 }


function getCurrent(code)
{
	param='code='+code;
	post_response_text('slave_getCurrentOrg.php', param, respon);
   function respon(){
      if(con.readyState==4)
      {
	        if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					if (con.responseText != '-1') {
						//alert(con.responseText);
						fillForm(con.responseText);
					}
					else 
						clearForm();	  
				}
			}
			else {busy_off();error_catch(con.status);}	
      }	
   }	
  function  fillForm(arrtex)
  {
  	arr=arrtex.split('|');
  	document.getElementById('orgcode').value =arr[0];
	document.getElementById("orginitial").value=arr[11];
	document.getElementById("tipepabrik").value=arr[12];
	document.getElementById("sustainable").value=arr[13];
	document.getElementById("sertifikat").value=arr[14];
	document.getElementById('orgname').value =arr[1];
	document.getElementById("blokinduk").value=arr[15];
	document.getElementById("namablokinduk").value=arr[16];
	//document.getElementById('orgtype').value =arr[2];
	obj=document.getElementById('orgtype');
	for(xY=0;xY<obj.length;xY++)
	{
		if(obj.options[xY].value==arr[2])
		{
			obj.options[xY].selected=true;
		}
	}
	if(obj.value=='PT')
	{
		document.getElementById("tridentnik").style.display='';
		document.getElementById("identnik").value=arr[10];		
	}
	else
	{
		document.getElementById("tridentnik").style.display='none';		
		document.getElementById("identnik").value=0;		
	}
	document.getElementById('orgadd').value  =arr[3];
	document.getElementById('orgcity').value =arr[5];
    document.getElementById('orgzip').value  =arr[6];
	document.getElementById('orgtelp').value =arr[4];
	curr=0;
	ctobj=document.getElementById('orgcountry');
	ct=ctobj.length;
	for (x = 0; x < ct; x++) {
		if (ctobj.options[x].value == arr[7]) //check if country code is match with option value, then select it
             ctobj.options[x].selected=true;
	}
	alobj=document.getElementById('alokasi');
	al=alobj.length;
	for (x = 0; x < al; x++) {
		if (alobj.options[x].value == arr[8]) 
             alobj.options[x].selected=true;
	}	
	alobj=document.getElementById('noakun');
	al=alobj.length;
	for (x = 0; x < al; x++) {
		if (alobj.options[x].value == arr[9]) 
             alobj.options[x].selected=true;
	}	
	
  } 	
}

function setpos(id,e)
{
	pos=getMouseP(e);
	document.getElementById(id).style.top=pos[1]+'px';
	document.getElementById(id).style.left=pos[0]+'px';
	document.getElementById(id).style.display='';	
}

function checkpt(tipe){
	if(tipe.value=="PT"){
		document.getElementById('tridentnik').style.display="";
		document.getElementById('identnik').value=0;		
	}
	else
	{
		document.getElementById('tridentnik').style.display="none";
		document.getElementById('identnik').value=0;
	}		
}
