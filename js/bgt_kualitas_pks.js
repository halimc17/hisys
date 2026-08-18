function batal()
{
	document.getElementById('thnbudget').disabled=false;
	document.getElementById('thnbudget').value='';
	document.getElementById('kdpks').disabled=false;
	document.getElementById('kdpks').selectedIndex=0;
	
	//CPO
	document.getElementById('cpoffa').value='';
	document.getElementById('cpokadarair').value='';
	document.getElementById('cpokadarkotoran').value='';
	document.getElementById('cpofiberpress').value='';
	document.getElementById('cponutpress').value='';
	document.getElementById('cpoemptybunch').value='';
	document.getElementById('cpousb').value='';
	document.getElementById('cposoliddecanter').value='';
	document.getElementById('cpoheavyphase').value='';
	document.getElementById('cpofinaleffluent').value='';
	document.getElementById('cposterilizecondensat').value='';
	
	//PK
	document.getElementById('pkffa').value='';
	document.getElementById('pkkadarair').value='';
	document.getElementById('pkkadarkotoran').value='';
	document.getElementById('pkbroken').value='';
	document.getElementById('pkusb').value='';
	document.getElementById('pkfibercyclone').value='';
	document.getElementById('pkltds1').value='';
	document.getElementById('pkltds2').value='';
	document.getElementById('pkclaybath').value='';
	
	document.getElementById('cpototal').value=0;
	document.getElementById('pktotal').value=0;

	document.getElementById('saveDt').disabled=false;
	//document.getElementById('printContainer').style.display='none';	
	setValue('method','insert');
}

function totallossescpo(){
	//CPO
	cpofiberpress=document.getElementById('cpofiberpress').value;
	cponutpress=document.getElementById('cponutpress').value;
	cpoemptybunch=document.getElementById('cpoemptybunch').value;
	cpousb=document.getElementById('cpousb').value;
	cposoliddecanter=document.getElementById('cposoliddecanter').value;
	cpoheavyphase=document.getElementById('cpoheavyphase').value;
	cpofinaleffluent=document.getElementById('cpofinaleffluent').value;
	cposterilizecondensat=document.getElementById('cposterilizecondensat').value;
	if(cpofiberpress==''){cpofiberpress=0}
	if(cponutpress==''){cponutpress=0}
	if(cpoemptybunch==''){cpoemptybunch=0}
	if(cpousb==''){cpousb=0}
	if(cposoliddecanter==''){cposoliddecanter=0}
	if(cpoheavyphase==''){cpoheavyphase=0}
	if(cpofinaleffluent==''){cpofinaleffluent=0}
	if(cposterilizecondensat==''){cposterilizecondensat=0}
	
	total=parseFloat(cpofiberpress)+parseFloat(cponutpress)+parseFloat(cpoemptybunch)+parseFloat(cpousb)+parseFloat(cposoliddecanter)+parseFloat(cpoheavyphase)+parseFloat(cpofinaleffluent)+parseFloat(cposterilizecondensat);
	
	document.getElementById('cpototal').value=total;
}

function totallossespk(){
	pkusb=document.getElementById('pkusb').value;
	pkfibercyclone=document.getElementById('pkfibercyclone').value;
	pkltds1=document.getElementById('pkltds1').value;
	pkltds2=document.getElementById('pkltds2').value;
	pkclaybath=document.getElementById('pkclaybath').value;
	if(pkusb==''){pkusb=0}
	if(pkfibercyclone==''){pkfibercyclone=0}
	if(pkltds1==''){pkltds1=0}
	if(pkltds2==''){pkltds2=0}
	if(pkclaybath==''){pkclaybath=0}
	
	total=parseFloat(pkusb)+parseFloat(pkfibercyclone)+parseFloat(pkltds1)+parseFloat(pkltds2)+parseFloat(pkclaybath);
	
	document.getElementById('pktotal').value=total;
}

function simpan(){	
	thnbudget=document.getElementById('thnbudget').value;
	kdpks=document.getElementById('kdpks').options[document.getElementById('kdpks').selectedIndex].value;
	
	//CPO
	cpoffa=document.getElementById('cpoffa').value;
	cpokadarair=document.getElementById('cpokadarair').value;
	cpokadarkotoran=document.getElementById('cpokadarkotoran').value;
	cpofiberpress=document.getElementById('cpofiberpress').value;
	cponutpress=document.getElementById('cponutpress').value;
	cpoemptybunch=document.getElementById('cpoemptybunch').value;
	cpousb=document.getElementById('cpousb').value;
	cposoliddecanter=document.getElementById('cposoliddecanter').value;
	cpoheavyphase=document.getElementById('cpoheavyphase').value;
	cpofinaleffluent=document.getElementById('cpofinaleffluent').value;
	cposterilizecondensat=document.getElementById('cposterilizecondensat').value;
	
	//PK
	pkffa=document.getElementById('pkffa').value;
	pkkadarair=document.getElementById('pkkadarair').value;
	pkkadarkotoran=document.getElementById('pkkadarkotoran').value;
	pkbroken=document.getElementById('pkbroken').value;
	pkusb=document.getElementById('pkusb').value;
	pkfibercyclone=document.getElementById('pkfibercyclone').value;
	pkltds1=document.getElementById('pkltds1').value;
	pkltds2=document.getElementById('pkltds2').value;
	pkclaybath=document.getElementById('pkclaybath').value;
	
	method=document.getElementById('method').value;
		
	param='thnbudget='+thnbudget+'&kdpks='+kdpks+'&method='+method;
	param+='&cpoffa='+cpoffa+'&cpokadarair='+cpokadarair+'&cpokadarkotoran='+cpokadarkotoran;
	param+='&cpofiberpress='+cpofiberpress+'&cponutpress='+cponutpress+'&cpoemptybunch='+cpoemptybunch;
	param+='&cpousb='+cpousb+'&cposoliddecanter='+cposoliddecanter+'&cpoheavyphase='+cpoheavyphase;
	param+='&cpofinaleffluent='+cpofinaleffluent+'&cposterilizecondensat='+cposterilizecondensat;
	param+='&pkffa='+pkffa+'&pkkadarair='+pkkadarair+'&pkkadarkotoran='+pkkadarkotoran;
	param+='&pkbroken='+pkbroken+'&pkusb='+pkusb+'&pkfibercyclone='+pkfibercyclone;
	param+='&pkltds1='+pkltds1+'&pkltds2='+pkltds2+'&pkclaybath='+pkclaybath;
	
	tujuan='bgt_slave_kualitas_pks.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
		if (con.readyState == 4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					loadData();
					batal();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
        } 
	}   
}

function loadData()
{
	param='method=loadData';
	tujuan='bgt_slave_kualitas_pks.php';
	post_response_text(tujuan, param, respog);
	function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
					document.getElementById('container1').innerHTML=con.responseText;
				}
			}
		}else{
			busy_off();
			error_catch(con.status);
		}
	}
}

function del(tahunbudget,kdorg){
	param='method=delete'+'&thnbudget='+tahunbudget+'&kdpks='+kdorg;
    tujuan='bgt_slave_kualitas_pks.php';
    if(confirm("Delete, are you sure?")){
		post_response_text(tujuan, param, respog);	
	}
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
                    loadData();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function fillfield(tahunbudget,kdorg,cpoffa,cpokadarair,cpokadarkotoran,cpofiberpress,cponutpress,cpoemptybunch,cpousb,cposoliddecanter,cpoheavyphase,cpofinaleffluent,cposterilizecondensat,pkffa,pkkadarair,pkkadarkotoran,pkbroken,pkusb,pkfibercyclone,pkltds1,pkltds2,pkclaybath,cpoloses,pkloses){
	document.getElementById('method').value = 'update';
	document.getElementById('thnbudget').value = tahunbudget;
	l = document.getElementById('kdpks');
	for(a=0;a<l.length;a++){
		if(l.options[a].value==kdorg){
			l.options[a].selected=true;
		}
	}

	document.getElementById('thnbudget').disabled = true;
	document.getElementById('kdpks').disabled = true;
	
	document.getElementById('cpoffa').value = cpoffa;
	document.getElementById('cpokadarair').value = cpokadarair;
	document.getElementById('cpokadarkotoran').value = cpokadarkotoran;
	document.getElementById('cpofiberpress').value = cpofiberpress;
	document.getElementById('cponutpress').value = cponutpress;
	document.getElementById('cpoemptybunch').value = cpoemptybunch;
	document.getElementById('cpousb').value = cpousb;
	document.getElementById('cposoliddecanter').value = cposoliddecanter;
	document.getElementById('cpoheavyphase').value = cpoheavyphase;
	document.getElementById('cpofinaleffluent').value = cpofinaleffluent;
	document.getElementById('cposterilizecondensat').value = cposterilizecondensat;
	document.getElementById('cpototal').value = cpoloses;
	
	document.getElementById('pkffa').value = pkffa;
	document.getElementById('pkkadarair').value = pkkadarair;
	document.getElementById('pkkadarkotoran').value = pkkadarkotoran;
	document.getElementById('pkbroken').value = pkbroken;
	document.getElementById('pkusb').value = pkusb;
	document.getElementById('pkfibercyclone').value = pkfibercyclone;
	document.getElementById('pkltds1').value = pkltds1;
	document.getElementById('pkltds2').value = pkltds2;
	document.getElementById('pkclaybath').value = pkclaybath;
	document.getElementById('pktotal').value = pkloses;
	
}

function closepks(){
	thnbudget=document.getElementById('thnttp').options[document.getElementById('thnttp').selectedIndex].value;
	kdpks=document.getElementById('kdpksttp').options[document.getElementById('kdpksttp').selectedIndex].value;
	
	if(trim(thnbudget)==''){
		alert('Budget year required');
        return;
	}
	
	if(trim(kdpks)==''){
		alert('Mill code required');
		return;	
	}
	
	param='method=closepks'+'&thnbudget='+thnbudget+'&kdpks='+kdpks;
    tujuan='bgt_slave_kualitas_pks.php';
    if(confirm("Close, are you sure?")){
		post_response_text(tujuan, param, respog);	
	}
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
                    loadData();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function detail(id){
	for(a=1;a<13;a++){
		var row = document.getElementById(id+''+a);	
		if(row !== null){
			if (row.style.display == '') {
				row.style.display = 'none';
			}
			else {
				row.style.display = '';
			}
		}
	}
}

function form()
{
    width = '';
    height = '';
    content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}

function viewdetail(kdpks,thnbudget)
{
    form();
    param = 'method=viewdetail'+"&proses=html" + '&kdpks=' + kdpks + '&thnbudget=' + thnbudget;
    tujuan = 'bgt_slave_kualitas_pks.php';
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
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else
                {
                    document.getElementById('containerd').innerHTML = con.responseText;
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


function printFile(kdpks,thnbudget,tujuan,ev)
{
   tujuan=tujuan+"?kdpks="+kdpks+"&thnbudget="+thnbudget+"&method=viewdetail"+"&proses=excel";  
   width='700';
   height='400';
   title='FFB QUALITY';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);  
}