function getPage(){
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(pg){
	sccari=document.getElementById('sccari').value;
	param = 'method=loaddata&page='+pg+'&sccari='+sccari;
	tujuan = 'help_slave_show.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contain').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewpdf(modul,modultext,judul,bahasa,ev){
	width = '';
	height = '';
	content = "<fieldset><legend>"+modultext+" - "+judul+"</legend><div id=containerpdf></div></fieldset>";
	title = "";
	showDialogx(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamicx').style.top = pos[1] + 'px';
	document.getElementById('dynamicx').style.left = (pos[0]) + 'px';
	document.getElementById('dynamicx').style.display = '';
	
	var param = "modul="+modul+'&judul='+judul+'&bahasa='+bahasa+'&method=viewpdf';
	tujuan = 'help_slave_show.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('containerpdf').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}