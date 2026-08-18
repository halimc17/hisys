function getMesin(kdDiv,kdMsn){
	divId=document.getElementById('divId').options[document.getElementById('divId').selectedIndex].value;
	param='divId='+divId+'&proses=getMesin';
	if(kdMsn!='0'){
		param+='&msnId='+kdMsn;
	}
	tujuan='pabrik_slave_5submesin.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
					if(kdMsn!='0'){
						document.getElementById('msnId').disabled=true;	
					}
                    document.getElementById('msnId').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}
function getData(kdMsn,kdSbMsn){
	if((kdMsn==0)&&(kdMsn==0)){
		getdt=document.getElementById('msnId');
		getdt=getdt.options[getdt.selectedIndex].value;	
	}else{
		getdt=kdMsn;
		document.getElementById('sbMesinCode2').value=kdSbMsn;
	}
	document.getElementById('sbMesinCode').value=getdt;
}
function cancel(){
    document.getElementById('method').value='insert';
	kodeorg=document.getElementById('divId');
	kodeorg.disabled=false;
	kodeorg=kodeorg.options[0].selected=true;
	kodetangki=document.getElementById('msnId');
	kodetangki.disabled=false;
	kodetangki.innerHTML="<option value=''></option>";
	kodetangki2=document.getElementById('sbMesinCode');
	kodetangki2.value="";
	kodetangki3=document.getElementById('sbMesinCode2');
	kodetangki3.value="";
	kodetangki3.disabled=false;
	document.getElementById('sbMesinNama').value='';
	dtck=document.getElementById('statusDt');
	dtck.checked=false;
}

function loadData(){
	kodeorg=document.getElementById('divId').options[document.getElementById('divId').selectedIndex].value;
	param='divId='+kodeorg+'&proses=loadData';
	tujuan='pabrik_slave_5submesin.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
                    document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function simpan(){
	kodeorg=document.getElementById('divId').options[document.getElementById('divId').selectedIndex].value;
	kodetangki=document.getElementById('msnId').options[document.getElementById('msnId').selectedIndex].value;
	sbMesinCd=document.getElementById('sbMesinCode').value;
	sbMesinCd2=document.getElementById('sbMesinCode2').value;
	sbMesinNm=document.getElementById('sbMesinNama').value;
	method=document.getElementById('method').value;
	ckdt=document.getElementById('statusDt');
	param='divId='+kodeorg+'&msnId='+kodetangki+'&sbMesinCode='+sbMesinCd;
	param+='&sbMesinNama='+sbMesinNm+'&proses='+method+'&sbMesinCd2='+sbMesinCd2;
	if(ckdt.checked==true){
		param+='&stat=1';
	}else if(ckdt.checked==false){
		param+='&stat=0';
	}
	tujuan='pabrik_slave_5submesin.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
                    document.getElementById('container').innerHTML=con.responseText;
					cancel();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function fillfield(stId,msnId,sbmsnId,nmSubmsin,stat){
	x=document.getElementById('divId');
	for(a=0;a<x.length;a++){
		if(x.options[a].value==stId){
			x.options[a].selected=true;
		}
	}
	x.disabled=true;
	getMesin(stId,msnId);
	document.getElementById('sbMesinCode').value=msnId;
	document.getElementById('sbMesinCode2').value=sbmsnId;
	document.getElementById('sbMesinNama').value=nmSubmsin;
	document.getElementById('sbMesinCode2').disabled=true;
	document.getElementById('method').value='update';
	dtck=document.getElementById('statusDt');
	if(stat=='1'){
		dtck.checked=true;
	}else if(stat=='0'){
		dtck.checked=false;
	}
	
}

function deletefield(kodeorg,kodetangki){
	param='msnId='+kodeorg+'&sbMesinCode='+kodetangki+'&proses=delete';
	tujuan='pabrik_slave_5submesin.php';
	if(confirm("Anda yakin hapus item ini?"))
    {
		post_response_text(tujuan, param, respog);
	}
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
                    document.getElementById('container').innerHTML=con.responseText;
					cancel();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function printPDF(ev){
	//kodeorg=document.getElementById('divId').options[document.getElementById('divId').selectedIndex].value;
	param='proses=pdf';
	
	showDialog1('Print PDF',"<iframe frameborder=0 style='width:595px;height:400px'"+
        " src='pabrik_slave_5submesin.php?"+param+"'></iframe>",'600','400',ev);
}