$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
function setroleuser(no, idrole, sumber){
	username  = trim(document.getElementById('username_'+no).innerHTML);
	
	if(document.getElementById('adddt_'+no).checked==true){
        action = 'add';
	}else{
        action = 'remove';
	}
	
    param = 'method=setroleuser';
	param+= '&username=' + username;
	param+= '&idrole=' + idrole;
	param+= '&action=' + action;
	param+= '&sumber=' + sumber;
    tujuan = 'main_slave_rolesmenu.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function gettpk(){
	menu = document.getElementById('id_menu').value;
	sctpk = document.getElementById('sctpk').value;
	sclok = document.getElementById('sclok').value;
	scjbt = document.getElementById('scjbt').value;
	scact = document.getElementById('scact').value;
	scus = document.getElementById('scus').value;
	scnm = document.getElementById('scnm').value;
	
    param='parent=&idrole='+menu+'&sctpk='+sctpk+'&scjbt='+scjbt+'&sclok='+sclok+'&scact='+scact+'&scus='+scus+'&scnm='+scnm;
	post_response_text('main_slave_rolesmenu.php?method=gettpk', param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					document.getElementById('containerdt').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function setformuser(idrole,namarole){
	showById('ctrmenu');
	param = 'idrole=' + idrole;
	param += '&method=setformuser';
	
	tujuan = 'main_slave_rolesmenu.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// width  = '';
					// height = '';
					// ev     = 'event';
					// content= "<fieldset><div id=containerd style=\"max-height:500px;overflow:auto;\"></div></fieldset>";
					// title = "Tambah user untuk Role : "+namarole;
					// showDialog1(title, content, width, height, ev);
					// document.getElementById('containerd').innerHTML = con.responseText;
					// leftFixedTable();
					
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					//leftFixedTable();
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function copyfrom(idrole){
	showById('ctrmenu');
	param = 'idrole=' + idrole;
	param += '&method=copyfrom';
	
	tujuan = 'main_slave_rolesmenu.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// width  = '';
					// height = '';
					// ev     = 'event';
					// content= "<fieldset><div id=containerd ></div></fieldset>";
					// title = "Copy detail";
					// showDialog1(title, content, width, height, ev);
					// document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('40px','70%');
					//leftFixedTable();
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showcopy(sumber){
	if(sumber=='user'){
		document.getElementById('tableuser').style.display = "";
		document.getElementById('tablerole').style.display = "none";
		document.getElementById('fromrole').value = "";
		document.getElementById('detailmenucopy').innerHTML = "";
	}else{
		document.getElementById('tablerole').style.display = "";
		document.getElementById('tableuser').style.display = "none";
		document.getElementById('fromuser').value = "";
		document.getElementById('detailmenucopy').innerHTML = "";
	}
}

function detailmenucopy(sumber,jenis){
	param  = 'sumber=' + sumber;
	param += '&jenis=' + jenis;
	param += '&method=detailmenucopy';
	
	tujuan = 'main_slave_rolesmenu.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('detailmenucopy').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savecopy(sumber,id){
	idsumber = document.getElementById(id).value;
	idrole = document.getElementById('idrolecopy').value;
	param  = 'sumber=' + sumber;
	param += '&idsumber=' + idsumber;
	param += '&idrole=' + idrole;
	param += '&method=savecopy';
	
	tujuan = 'main_slave_rolesmenu.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert("Done");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function changePrivillage(idmenu, idrole, obj) {
    if (obj.checked){
        action = 'add';
	}else{
        action = 'remove';
	}
    document.getElementById('orderlab' + idmenu).style.backgroundColor = '#E36707';
    param = 'idrole=' + idrole + '&idmenu=' + idmenu + '&action=' + action;
	param += '&method=addroledt';
	param += '&sumber=addroledt';
	
    post_response_text('main_slave_rolesmenu.php', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    if (obj.checked){
                        obj.checked = false;
					}else{
                        obj.checked = true;
					}
                } else {
					data = con.responseText.split("####");
					if(data.length>0){
						for(i=0;i<data.length;i++){
							if(document.getElementById('cx' + data[i])!=undefined){								
								c = document.getElementById('cx' + data[i]);
								if(c.checked==false && data[i]!=idmenu){
									document.getElementById('cx' + data[i]).checked=true;
								}
								if(c.checked==true && data[i]!=idmenu && action == 'remove'){
									document.getElementById('cx' + data[i]).checked=false;
								}
							}
						}
					}
					document.getElementById('orderlab' + idmenu).style.backgroundColor = '#FFFFFF';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function show_sub(id, obj){
    if (document.getElementById(id).style.display == 'none') {
        document.getElementById(id).style.display = '';
        obj.src = 'images/foldo.png';
        obj.setAttribute('title', 'Collaps');
    } else {
        document.getElementById(id).style.display = 'none';
        obj.src = 'images/foldc.png';
        obj.setAttribute('title', 'Expand');
    }
}

function showById(objtohide){
    document.getElementById(objtohide).style.display = 'none';
}
function collapsAllOrder() {
    for (x = 0; x <= max_id; x++) {
        try {
            document.getElementById('orderchild' + x).style.display = 'none';
        } catch (e) {}
    }
}
function expandAllOrder() {
    for (x = 0; x <= max_id; x++) {
        try {
            document.getElementById('orderchild' + x).style.display = '';
        } catch (e) {}
    }
}

function setMapUserMenu(ev, rowobj, idrole) {
    closeDialog();
	pos = getMouseP(ev);
    param = 'idrole=' + idrole;
    param += '&method=showmenu';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('contentmenu').innerHTML = con.responseText;
                    document.getElementById('ctrmenu').style.display = '';
                    document.getElementById('ctrmenu').style.top = pos[1] + 'px';
                    document.getElementById('ctrmenu').style.left = pos[0] + 'px';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('main_slave_rolesmenu.php', param, respog);
}

function batal() {
    document.getElementById('method').value = 'insert';
    document.getElementById('status').selectedIndex = '0';
    document.getElementById('nama').value = '';
    document.getElementById('id').value = '';
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);
}

function loaddata(page) {
	nama  = document.getElementById('namacari').value;
	
    param = 'method=loaddata';
	param+='&page=' + page;
	param+='&nama=' + nama;
	
    tujuan = 'main_slave_rolesmenu.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					data = con.responseText.split("####");
                    document.getElementById('container').innerHTML = data[0];
                    document.getElementById('footer').innerHTML = data[1];
                    batal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillfield(id,name,satus) {
    document.getElementById('nama').value = name;
    document.getElementById('status').value = satus;
    document.getElementById('method').value = 'update';
    document.getElementById('id').value = id;
	showById('ctrmenu');
}

function simpan(){
	nama  = trim(document.getElementById('nama').value);
	sts   = trim(document.getElementById('status').value);
	idrole= trim(document.getElementById('id').value);
	method= trim(document.getElementById('method').value);

    param = 'nama=' + nama+'&method=' + method;
	param+='&idrole=' + idrole;
	param+='&sts=' + sts;
	param+='&sumber=addroleht';
    tujuan = 'main_slave_rolesmenu.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					batal();
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefield(id) {
    param = 'id=' + id + '&method=delete';
    tujuan = 'main_slave_rolesmenu.php';
	alertify.confirm("Warning","Anda yakin?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
