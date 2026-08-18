function loadPOChat(nopo, ev) {
	title = "Chat:" + nopo;
	content = "<iframe frameborder=0 style='width:510px;height:290px;' src='log_slaveChatPO.php?nopo=" + nopo + "'></iframe>";
	width = '';
	height = '';
	showDialog2(title, content, width, height, ev);
}


function displaylistdata(){
	document.getElementById('txtsearch_rpo').value='';
	document.getElementById('tgl_cari_rpo').value='';
	document.getElementById('filterId').selectedIndex=0;
	document.getElementById('filterSupplier').selectedIndex='';
	loadData(0);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loadData(paged);
}

function loadData(num){
	txtSearchrpo=trim(document.getElementById('txtsearch_rpo').value);
	tglCarirpo=trim(document.getElementById('tgl_cari_rpo').value);
    filterId=document.getElementById('filterId');
    filterId=filterId.options[filterId.selectedIndex].value;
    filterSupplier=document.getElementById('filterSupplier');
    filterSupplier=filterSupplier.options[filterSupplier.selectedIndex].value;
	
	param='method=list_new_data_release_po'+'&txtSearchrpo='+txtSearchrpo+'&tglCarirpo='+tglCarirpo;
	param+='&page='+num+'&filterId='+filterId+'&filterSupplier='+filterSupplier;
	tujuan='log_slave_release_po.php';
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
					} else {
						//alert(con.responseText);
						document.getElementById('contain').innerHTML=con.responseText;
					}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	 }
	 post_response_text(tujuan, param, respog);
}

function release_po(id){
	rnopo=id;
	id_user=document.getElementById('user_login').value;
	param='nopo='+rnopo+'&id_user='+id_user+'&method=release_po';
	tujuan='log_slave_release_po.php';
        
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					cariRpo(0);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
	
	a =confirm("Are you sure want to release this PO:"+id);
	if(a){
		post_response_text(tujuan, param, respog);
	}else{
		return;
	}
}

function un_release_po(id,tanggal){
	rnopo=id;
	tglR=tanggal;
	id_user=document.getElementById('user_login').value;
	
	param='method=rejected&proses=PO&notransaksi='+rnopo+'&alasan=Unrelease PO';//pilId
	tujuan='log_slave_approval.php';
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					cariRpo(0);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	
	a = confirm("Are you sure want Unrelease this PO:."+id)
	if(a){
		post_response_text(tujuan, param, respog);
	}else{
		return;
	}
}

function tolakPo(){
	rnopo=document.getElementById('rnopo').value;
    ketrngan=document.getElementById('ket').value
	param='nopo='+rnopo+'&ket='+ketrngan+'&method=tolakPo';
	tujuan='log_slave_release_po.php';
	post_response_text(tujuan, param, respog);	
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					cariRpo(0);
                    closeDialog();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	} 	

}

function agree_po(){
	width='300';
	height='130';
	content="<div id=container></div>";
	ev='event';
	title="Persetujuan Atau Penolakan Form";
	showDialog1(title,content,width,height,ev);
}

function get_data_po(rnopo){
	agree_po();
    met=document.getElementById('method').value;
    met='getFormTolak';
    param='method='+met+'&nopo='+rnopo;
    tujuan='log_slave_release_po.php';
    
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	} 	
	post_response_text(tujuan, param, respog);	
}

function cancel_po(){
        closeDialog();
}

function saveKoreksi(id){
	texkKrsi=document.getElementById('krksiText_'+id).value;
    if(texkKrsi==""){
		alert("Enter a note");
		return;
	}else{
        nop=document.getElementById('td_'+id).innerHTML;
        met=document.getElementById('method').value;
        met='insertKoreksi';
        param='method='+met+'&texkKrsi='+texkKrsi+'&nopo='+nop;
        tujuan='log_slave_release_po.php';

        function respog(){
			if(con.readyState==4){
				if(con.status == 200){
					busy_off();
                    if(!isSaveResponse(con.responseText)){
						alert(con.responseText);
					}else{
						cariRpo(0);
                        document.getElementById('btnSave_'+id).disabled=true;
                        document.getElementById('krksiText_'+id).disabled=true;							
					}
				}else{
					busy_off();
                    error_catch(con.status);
				}
			}	
		} 	
        post_response_text(tujuan, param, respog);
	}
}

function undisable(id){
	document.getElementById('btnSave_'+id).disabled=false;
	document.getElementById('krksiText_'+id).disabled=false;	
}

function cancelpoform(jdl,nop,ev){
    title=jdl;
    width='300';
    height='';
    content="<div id=closeForm></div>";
    showDialog5(title,content,width,height,ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic5').style.top = pos[1] + 'px';
	document.getElementById('dynamic5').style.left = (pos[0] - 300) + 'px';
	document.getElementById('dynamic5').style.display = '';
		
	param='method=cancelpoform'+'&nopo='+nop;
	
	tujuan='log_slave_release_po.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('closeForm').innerHTML=con.responseText;				
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	} 	
}

function closeedPo(jdl,nop,ev){
    title=jdl;
    width='300';
    height='';
    content="<div id=closeForm></div>";
    showDialog5(title,content,width,height,ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic5').style.top = pos[1] + 'px';
	document.getElementById('dynamic5').style.left = (pos[0] - 300) + 'px';
	document.getElementById('dynamic5').style.display = '';
		
	param='method=closeForm'+'&nopo='+nop;
	
	tujuan='log_slave_release_po.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('closeForm').innerHTML=con.responseText;				
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	} 	
}

function bospo(jdl,nop,ev){
    // title=jdl;
    // width='';
    // height='';
    // content="<div id=closeForm></div>";
    // //showDialog5(title,content,width,height,ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic5').style.top = pos[1] + 'px';
	// document.getElementById('dynamic5').style.left = (pos[0] - 700) + 'px';
	// document.getElementById('dynamic5').style.display = '';
		
	param='method=bospo'+'&nopo='+nop;
	
	tujuan='log_slave_release_po.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//document.getElementById('closeForm').innerHTML=con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('800px','500px');
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	} 	
}

function unclose(npo){
    if(confirm("Anda yakin unclose PO "+npo)){
        param='method=unclose'+'&nopo='+npo;//pilId
        tujuan='log_slave_release_po.php';
        post_response_text(tujuan, param, respog);   
    }
    
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else{
					alert('PO berhasil diunclose');
                    getPage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	} 	
}
function cancelpo(npo){
    ket=document.getElementById('ketClose').value;
    
    if(ket==''){
        alert('Keterangan harus diisi');
		return;
    }
    
    if(confirm("Anda yakin cancel PO "+npo)){
        param='method=cancelpo'+'&nopo='+npo+'&ketClose='+ket;//pilId
        tujuan='log_slave_release_po.php';
        post_response_text(tujuan, param, respog);   
    }
    
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else{
					document.getElementById('closeForm').innerHTML=con.responseText;	
                    closeDialog5();
                    getPage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	} 	
}

function tutpDt(npo){
    ket=document.getElementById('ketClose').value;
    
    if(ket==''){
        alert('Keterangan harus diisi');
		return;
    }
    
    if(confirm("Anda yakin tutup PO "+npo)){
        param='method=closepo'+'&nopo='+npo+'&ketClose='+ket;//pilId
        tujuan='log_slave_release_po.php';
        post_response_text(tujuan, param, respog);   
    }
    
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else{
					document.getElementById('closeForm').innerHTML=con.responseText;	
                    closeDialog5();
                    getPage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	} 	
}

function closebos(nopo,totalrow){
    keterangan=document.getElementById('ketClose').value;
    
	if(keterangan==''){
        alert('Keterangan harus diisi');
		return;
    }
	
	strurl = '';
    for(var i=1;i<=totalrow;i++){
		strurl += '&kodebarang[]='+encodeURIComponent(trim(document.getElementById('kodebarang_'+i).innerHTML))
				+'&nopp[]='+encodeURIComponent(trim(document.getElementById('nopp_'+i).innerHTML))
				+'&sudahditerima[]='+encodeURIComponent(trim(document.getElementById('sudahditerima_'+i).innerHTML))
				+'&jumlahpesan[]='+encodeURIComponent(trim(document.getElementById('jumlahpesan_'+i).innerHTML))
				+'&diterima[]='+encodeURIComponent(trim(document.getElementById('diterima_'+i).value));
	}
    
    
    if(confirm("Anda yakin untuk mengembalikan PO "+nopo+" kepada team purchasing?")){
        param='method=closebos'+'&nopo='+nopo+'&keterangan='+keterangan;
		param+=strurl;
        tujuan='log_slave_release_po.php';
        post_response_text(tujuan, param, respog);   
    }
    
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else{
					document.getElementById('filterId').selectedIndex=3;
					document.getElementById('filterSupplier').selectedIndex='';
					// document.getElementById('closeForm').innerHTML=con.responseText;	
                    // closeDialog5();
					alertify.popup().destroy();
                    getPage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	} 	
}