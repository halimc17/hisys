exacData();
function deleteComment(id,parentid){
	var task = parentid+"&detailid="+id;
	var switcher = 'delete';
	exacData(task,switcher);
}
function changedata(e,parentid,switcher){
	var val =  e.value+"&parentid="+parentid;
	var r = confirm("Apakah ingin Mengganti selection ?");
	if (r == true) {
		exacData(val,switcher);
	}
}
function changeOpt(val,switchcase,idreplace){
	var id = document.getElementById(idreplace);
	getSlave(switchcase+'&'+val,'',replaceOpt);
	function replaceOpt(data){
		id.innerHTML = data;
		if(idreplace == "assignmentto"){
			clearpilihkaryawan();
		}
	}
}
function getlistdatakarywan(ev){
	var dept_radio = document.getElementById('dept_radio');
	var jab_radio = document.getElementById('jab_radio');
	var all_radio = document.getElementById('all_radio');

	var departementto = document.getElementById('departementto');
	var jabatanto = document.getElementById('jabatanto');
	var allto = document.getElementById('allto');
	var assignmentto = document.getElementById('assignmentto');
	opt = assignmentto.getElementsByTagName('option');
	if(opt.length > 0){
		title = "Pilih Karyawan";
		content = '<div style="max-height:150px;overflow-y:scroll;width:100%;"><table class="sortable" border="0" cellspacing="1" width="100%">';
		no = 1;
		for(i=0; i<opt.length; i++){
			content += "<tr class=\"rowcontent\" onclick='pilihkaryawan(this)' style='cursor:pointer;'>";
			content += "<td value="+opt[i].value+" >"+opt[i].innerHTML+"</td>";
			content += "</tr>";
			no++;
		}
		content += "</table></div>";
		width = 500;
		height = "";
		showDialog5(title,content,width,height,ev);
	}else{
		if(dept_radio.checked == true){
			if(departementto.value == ""){
				alert("Pilih Departement terlebih dahulu");
			}else{
				alert("Data karyawan kosong");
			}
		}else if (jab_radio.checked == true) {
			if(jabatanto.value == ""){
				alert("Pilih Jabatan terlebih dahulu");
			}else{
				alert("Data karyawan kosong");
			}
		}else{
			
		}
	}
}
function clearpilihkaryawan(){
	if(document.getElementById('datalist_detail')){
		document.getElementById('datalist_detail').innerHTML = "";
	}
}
function pilihkaryawan(e){
	var datalist_both = document.getElementById('datalist_both');
	var td = e.getElementsByTagName('td');
	ele = td[0];
	val = ele.getAttribute('value');
	text = ele.innerHTML;
	li = document.createElement('li');
	li.id = val;
	HTML = '<input type="hidden" name="assignmentto[]" value="'+val+'">'+text+"";
	li.innerHTML = HTML; 
	if(document.getElementById('datalist_detail')){
		document.getElementById('datalist_detail').appendChild(li);
	}else{
		datalist_detail = document.createElement('ul');
		datalist_detail.id ='datalist_detail';
		datalist_detail.appendChild(li);
		datalist_both.appendChild(datalist_detail);
	}
	e.remove();
	var assignmentto = document.getElementById('assignmentto');
	opt = assignmentto.getElementsByTagName('option');
	if(opt.length > 0){
		for(i=0; i<opt.length; i++){
			if(opt[i].value == val && opt[i].innerHTML ==  text){
				opt[i].remove();
			}
		}
	}
}
function getSlave(switchcase,param,funct) {
	
	var prosees = ""
	if(typeof switchcase !== 'undefined'){
		prosees = "?"+switchcase;
	}
	if(typeof param !== 'undefined'){
		param = param;
	}else{
		param = "";
	}
	post_response_text('sdm_slave_taskassignment.php'+prosees, param, respon);
	function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                    //=== Success Response
					if(funct){
						eval(funct(con.responseText));
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function exacData(task,switcher){
	if(typeof task == 'undefined'){
		param = '';
    }else{
		param = "id="+task;
	}
	if(typeof switcher == 'undefined' || switcher == 'taskdetail'){
		prosess = 'viewtask';
		//switcher = 'viewtask';
    }else if(switcher == 'buatbaru'){
		prosess = 'viewtaskdetail';
    }else{
		prosess = switcher;
	}
	
	tujuan='sdm_slave_taskassignment.php?switch='+prosess;
    post_response_text(tujuan, param, respog);		
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
						return false;
                }
                else {
					prosess = prosess.split('&')[0];
					if(prosess == 'viewtaskdetail'){
						var formelement = document.getElementById('formelement');
						formelement.innerHTML = con.responseText;
					}else if (prosess == 'delete'){
						taskid = task.split('&')[0];
						exacData(taskid,'viewtaskdetail');
					}else if (prosess == 'viewtask'){
						var formelement = document.getElementById('formelement');
						formelement.innerHTML = "";
						var tableBox = document.getElementById('taskmanagemenhead');
						tableBox.innerHTML = con.responseText;
						if(switcher == 'taskdetail'){ 
							exacData(task,'viewtaskdetail');
						}
					}else if (prosess == 'updatestatus'){
						parentid = task.split('=');
						if(parentid.length > 1){
							exacData(parentid[1],'taskdetail');
						}
					}
					if(switcher == 'buatbaru'){
						var formelement = document.getElementById('formelement');
						formelement.innerHTML = con.responseText;
						var tableBox = document.getElementById('taskmanagemenhead');
						tableBox.innerHTML = "";
					}
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }

}
function clearForm(e){
	e.reset();
}
function saveform(e,callback){
	busy_on();
	var fileform = e;
	var tujuan = e.action;
	if (!fileform.action){return false;}
	var xhr = new XMLHttpRequest();
	xhr.onload = callback;
	if (fileform.method.toLowerCase() === "post"){
		xhr.open("post", fileform.action);
		xhr.send(new FormData(fileform));
	}	
}
function askSuccess(){
	if(this.readyState==4)
	{
		if (this.status == 200) {
			busy_off();
			if (!isSaveResponse(this.responseText)) {
					alert(this.responseText);
			}else {
				var tableBox = document.getElementById('taskmanagemenhead');
				tableBox.innerHTML = this.responseText;
				exacData();
			}
		}
		else {
			busy_off();
			error_catch(this.status);
		}
	}
}
function commentSuccess(){
	if(this.readyState==4)
	{
		if (this.status == 200) {
			busy_off();
			if (!isSaveResponse(this.responseText)) {
					alert(this.responseText);
			}else {
				var tableBox = document.getElementById('taskmanagemenhead');
				var refresh = document.getElementById('refresh');
				tableBox.innerHTML = this.responseText;
				refresh.click();
			}
		}
		else {
			busy_off();
			error_catch(this.status);
		}
	}
}
function quoteAct(idquote){
	var idquote = document.getElementById(idquote);
	var legendname = idquote.parentNode.getElementsByTagName('legend')[0];
	var comment = document.getElementById('isicomment');
	isi = "&lt;quote&gt;"+legendname.textContent+" : "+idquote.textContent+"&lt;&#47;quote&gt;";
	console.log(isi);
	comment.insertAdjacentHTML('beforeend', isi);
}
function pilih(param)
{
	if(param==0){
		document.getElementById("caption_sending").style.display = 'block';
		document.getElementById("departementto").style.display = 'block';
		document.getElementById("tanda").style.display = 'block';
		document.getElementById("caption_sending").innerHTML = "Departemen";
		if(document.getElementById("departementto").style.display == 'none')
		{
			document.getElementById("departementto").style.display = 'block';
		}
		if(document.getElementById("jabatanto").style.display == 'block')
		{
			document.getElementById("jabatanto").style.display = 'none';
			document.getElementById("jabatanto").value = "";
			document.getElementById("assignmentto").value = "";
		}
	}else if(param==1){
		document.getElementById("caption_sending").style.display = 'block';
		document.getElementById("jabatanto").style.display = 'block';
		document.getElementById("tanda").style.display = 'block';
		document.getElementById("caption_sending").innerHTML = "Jabatan";
		if(document.getElementById("departementto").style.display == 'block')
		{
			document.getElementById("departementto").style.display = 'none';
			document.getElementById("departementto").value = "";
			document.getElementById("assignmentto").value = "";
		}
		if(document.getElementById("jabatanto").style.display == 'none');
		{
			document.getElementById("jabatanto").style.display = 'block';
		}
	}else if(param==2){
		document.getElementById("departementto").style.display = 'none';
		document.getElementById("jabatanto").style.display = 'none';
		document.getElementById("caption_sending").style.display = 'none';
		document.getElementById("tanda").style.display = 'none';

		changeOpt('All','switch=getkaryawan','assignmentto');
	
	
	}
	clearpilihkaryawan();
}