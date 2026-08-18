function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {
    ev.preventDefault();
    var data = ev.dataTransfer.getData("text");
	c=ev.target.getAttribute("ondragover");
	if(c!='' && c!=null){
     ev.target.appendChild(document.getElementById(data));
	}else if (ev.target.getAttribute("ondragstart")!=null) {
      currentList=ev.target.parentElement.childNodes;
      induk=ev.target.parentElement;
      induk.insertBefore(document.getElementById(data), ev.target);
    }
    else{  
	 alert('Drop not allowed on target');
	 return false;
	}
}

function getRowCount(){
 myTable=document.getElementById('myTable');
 numberOfRow=myTable.rows.length;
 return numberOfRow;
}

function getThisField(tablename, targetId) {

    idTable = targetId.charAt(targetId.length - 1);

    numberOfRow = getRowCount();
    //make sure not choose duplicate
    var status = true;
    for (s = 1; s <= numberOfRow; s++) {
        if (idTable != '1') {
            if (s == idTable) {
                continue;
            } else {
                isiOption = document.getElementById('tableList' + s).options[document.getElementById('tableList' + s).selectedIndex].value;
                if ((tablename == isiOption) && tablename != '') {
                    alert('Table ' + tablename + ' used in row ' + s);
                    document.getElementById('tableList' + idTable).options[0].selected = true;
                    document.getElementById('table' + idTable).innerHTML = '';
                    status = false;
                }
            }
        }
    }
    if (status == true) {
        //process
        param = 'action=getField&tablename=' + tablename + '&targetid=' + targetId;
        tujuan = 'tool_slaveQueryGenerator.php';
        post_response_text(tujuan, param, respog);
    }

    //if current table name=='' then clear row below
    if (tablename == '') {
        myTable = document.getElementById('myTable');
        for (d = (numberOfRow - 1); d >= (parseInt(idTable) - 1); d--) {
            if (d == 0) {
                continue;
            } else {
                myTable.deleteRow(d);
            }
        }
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById(targetId)) {
                        document.getElementById(targetId).innerHTML = con.responseText;
                    }
                    if (tablename != '') {
                        addJoinForm(targetId);
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);

            }
        }
    }
}
function addJoinForm(targetId){
    //on wich row this action
	idTable=targetId.charAt(targetId.length-1);
	numberOfRow=getRowCount();
	tablename=document.getElementById('tableList'+idTable);
	tablename=tablename.options[tablename.selectedIndex].value;
	if(idTable>'1' && tablename!=''){
		//field on previous table
		var arrField1='';
		for(a=1;a<parseInt(idTable);a++){
	    prevRow=a;
		prevTable=getValue('tableList'+prevRow);
		z=document.getElementById('table'+prevRow).childNodes;
			for(aa=0;aa<z.length;aa++)
			{
			   if(z[aa].tagName=='SPAN'){
				 arrField1+="<option value='"+trim(z[aa].innerHTML)+"'>"+trim(z[aa].innerHTML)+"</option>";
			   }
			}
		}
		//field on current table
		currTable=getValue('tableList'+idTable);
		x=document.getElementById('table'+idTable).childNodes;
		var arrField2='';
		for(bb=0;bb<x.length;bb++)
		{
		   if(x[bb].tagName=='SPAN'){
			 arrField2+="<option value='"+trim(x[bb].innerHTML)+"'>"+trim(x[bb].innerHTML)+"</option>";
		   }
		}
		// and to display
		todisplay=document.getElementById(targetId).innerHTML;
		todisplay+="<br/><br/>Join Fields: <div class=rounded5><select id=join"+prevRow+"a1>"+arrField1+"</select> = <select id='join"+prevRow+"b1' >"+arrField2+"</select> [ and ";
		todisplay+="<br/><select id=join"+prevRow+"a2><option value=''>Optional</option>"+arrField1+"</select> = <select id='join"+prevRow+"b2' ><option value=''>Optional</option>"+arrField2+"</select> and";
		todisplay+="<br/><select id=join"+prevRow+"a3><option value=''>Optional</option>"+arrField1+"</select> = <select id='join"+prevRow+"b3' ><option value=''>Optional</option>"+arrField2+"</select>]</div>";
		document.getElementById(targetId).innerHTML=todisplay;
	}	
	 // if current row is not the last, then update row below
		if(numberOfRow>idTable){
			zz=parseInt(idTable)+1;
			tablename=document.getElementById('tableList'+zz);
			tablename=tablename.options[tablename.selectedIndex].value;		 
			getThisField(tablename,'table'+zz);
		}	
}
 
function addNewRow(){
 //check weather column configuration has been visible
 if(document.getElementById('columnControl').style.display==''){
   alert("Can't add table while configuring column, please reset instead"); 
 }else{
	  numberOfRow=getRowCount();
	if(numberOfRow>3){
		alert('Maximum 4 table on a query')
	 }else{
         myTable=document.getElementById('myTable');
		 nextIndex=numberOfRow;
		 nextRow = myTable.insertRow(nextIndex);
		 cell1=nextRow.insertCell(0);
		 cell1.style.verticalAlign='top'
		 cell2=nextRow.insertCell(1);
		 content="<div class=rounded5>Join Table:<select id=tableList"+(nextIndex+1)+" onchange=getThisField(this.options[this.selectedIndex].value,'table"+(nextIndex+1)+"');>";
		 content+=document.getElementById('tableList'+nextIndex).innerHTML+"</select></div>";
		 cell1.innerHTML=content;
		 cell2.innerHTML="Fields:<a href=# onclick=showById('table"+(nextIndex+1)+"'); title='Maximize'>+</a>/<a href=# onclick=hideById('table"+(nextIndex+1)+"'); title='Minimize'>-</a>";
         cell2.innerHTML+="<div id=table"+(nextIndex+1)+" class=rounded5 ondrop='drop(event);generateParameter();' ondragover=allowDrop(event)></div>";
	 }
  }	 
	 
}

function allClear(){
//verify all row has correct options
    numberOfRow=getRowCount();
    status='true';
    for(x=1;x<=numberOfRow;x++){
        //table name checker
        a=document.getElementById('tableList'+x);
        a=a.options[a.selectedIndex].value;
        if (a=='') {
            alert('Table on row number '+x+' required');
            status='false';
            break;
        }else{
        //join table checker
            if (x<numberOfRow) {
                for(u=1;u<=3;u++){
                 r1=document.getElementById('join'+x+'a'+u);
                 r1=r1.options[r1.selectedIndex].value;
                 r2=document.getElementById('join'+x+'b'+u);
                 r2=r2.options[r2.selectedIndex].value;
                 if ((r1=='' && r2!='') || (r1!='' && r2==''))  {
                    alert('Join table condition incorrect on row number '+(x+1));
                    status='false';
                    break;
                 }
                }
            }
        }
    }
    return status;
}


function configureColumn() {
    closeFormDialoque()
    if (document.getElementById('columnControl').style.display == '') {
        if (confirm('Configured column will be discharges, are you sure..?')) {
            document.getElementById('columnControl').style.display = 'none';
            zu = document.getElementById('tableList1');
            zu = zu.options[zu.selectedIndex].value;
            getThisField(zu, 'table1');
            enableTable();
            clearColumnConfig()
        }
    } else {
        if (allClear() != 'false') {
            disableTable();
            loadCondition();
            document.getElementById('columnControl').style.display = '';
        }

    }
}

function loadCondition() {
    clearCondition();
    numRow = getRowCount();
    opt1 = document.createElement('SELECT');
    opt1.setAttribute("id", "condition1");
    for (c = 1; c <= numRow; c++) {
        zt = document.getElementById('table' + c).childNodes;
        for (aa = 0; aa < zt.length; aa++) {
            if (zt[aa].tagName == 'SPAN') {
                theOption1 = document.createElement("OPTION");
                theText1 = document.createTextNode(trim(zt[aa].innerHTML));
                theOption1.appendChild(theText1);
                theOption1.setAttribute('value', trim(zt[aa].innerHTML));
                opt1.appendChild(theOption1);
            }
        }
    }
    tab = document.createElement('TABLE');
    tab.setAttribute("id", "parameter");
    //first cell
    row1 = tab.insertRow(0);
    cell1 = row1.insertCell(0);
    cell1.innerHTML = "Condition 1";
    cell1 = row1.insertCell(1);
    cell1.appendChild(opt1);
    //second cell do note change
    opr = ['Choose', '=', '>', '>=', '<', '<', '!=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'IS NULL', 'IS NOT NULL'];
    opt2 = document.createElement('SELECT');
    opt2.setAttribute("id", "operator1");
    for (x = 0; x < opr.length; x++) {
        theOption2 = document.createElement("OPTION");
        theText2 = document.createTextNode(opr[x]);
        theOption2.appendChild(theText2);
        theOption2.setAttribute('value', opr[x]);
        opt2.appendChild(theOption2);
    }
    cell2 = row1.insertCell(2);
    cell2.appendChild(opt2);
    //3rd cell do note change
    opr = ['Choose', 'TEXT', 'NUMERIC', 'DATE'];
    opt3 = document.createElement('SELECT');
    opt3.setAttribute("id", "parameter1");
    for (x = 0; x < opr.length; x++) {
        theOption3 = document.createElement("OPTION");
        theText3 = document.createTextNode(opr[x]);
        theOption3.appendChild(theText3);
        theOption3.setAttribute('value', opr[x]);
        opt3.appendChild(theOption3);
    }
    cell3 = row1.insertCell(3);
    cell3.appendChild(opt3);
    document.getElementById('condition').appendChild(tab);

    var a = document.createElement('a');
    var linkText = document.createTextNode("Add More");
    a.appendChild(linkText);
    a.title = "Add more parameter";
    //a.href = "#";
    a.style.cursor = 'pointer';
    a.setAttribute("onclick", "addMoreParamenter()");
    document.getElementById('condition').appendChild(a);
}

function addMoreParamenter(){
    table=document.getElementById('parameter');
    parameterRow=table.rows.length;
    prevCon=document.getElementById('condition1');
    if (parameterRow>=prevCon.length) {
        alert('Reach maximum condition');
    }else{    
        row=table.insertRow(parameterRow);
        cell1=row.insertCell(0);
        cell1.innerHTML='Condition '+(parameterRow+1);
        cell2=row.insertCell(1);

        cln = prevCon.cloneNode(true);
        cln.setAttribute('id','condition'+(parameterRow+1));
        cell2.appendChild(cln);
    
        cell3=row.insertCell(2);
        prevCon=document.getElementById('operator1');
        cln = prevCon.cloneNode(true);
        cln.setAttribute('id','operator'+(parameterRow+1));
        cell3.appendChild(cln);
        
        cell4=row.insertCell(3);
        prevCon=document.getElementById('parameter1');
        cln = prevCon.cloneNode(true);
        cln.setAttribute('id','parameter'+(parameterRow+1));
        cell4.appendChild(cln);
    }
}

function clearColumnConfig(){
  document.getElementById('columnList').innerHTML='';
  document.getElementById('caption').innerHTML='';
  clearCondition();
}
function clearCondition(){
      document.getElementById('condition').innerHTML='';
}

function disableTable(){
	numberOfRow=getRowCount();
	 for(x=1;x<=numberOfRow;x++){
	  document.getElementById('tableList'+x).disabled=true;
	 }
}
function enableTable(){
	numberOfRow=getRowCount();
	 for(x=1;x<=numberOfRow;x++){
	  document.getElementById('tableList'+x).disabled=false;
	 }
}

function reset(){
if(confirm('Reloading, are you sure..?')){
  window.location.reload();
 }
}

function generateParameter(){
    document.getElementById('caption').innerHTML='';
    terpilih=document.getElementById('columnList').childNodes;
    x = document.createElement("TABLE");
    x.setAttribute("border","1px");
    x.setAttribute("cellspacing","0px");
    x.setAttribute("id","captionDisplay");
    x.style.borderColor ='#000000';
    row0=x.insertRow(0);
    row1=x.insertRow(1);
    row2=x.insertRow(2);
    row3=x.insertRow(3);
    row4=x.insertRow(4);

    for(zb=0;zb<(terpilih.length);zb++){
        cella=row0.insertCell(zb);
        cella.setAttribute("class",terpilih[zb].getAttribute("class"));
        cella.innerHTML=terpilih[zb].innerHTML;
        rey=cella.innerHTML.split(".");
        cellb=row1.insertCell(zb);
        cellb.innerHTML="<input type=text class=myinputtext id=caption"+zb+" style='width:95%' value='"+rey[1]+"'>";
        cellc=row2.insertCell(zb);
        cellc.setAttribute("align","center");
        cellc.style.color="#000000";
        cellc.innerHTML="opr<select id=group"+zb+" onchange=protectColumn(this,'"+zb+"')><option value=''></option><option value='sum'>sum()</option><option value=avg>avg()</option></select>";        
        celld=row3.insertCell(zb);
        celld.setAttribute("align","center");
        celld.style.color="#000000";
        celld.innerHTML="SubtotalBy<input type=checkbox  id=subtotal"+zb+" onclick=protectOrder(this,'"+zb+"')>";
        celle=row4.insertCell(zb);
        celle.setAttribute("align","center");
        celle.style.color="#000000";
        celle.innerHTML="Order<input type=checkbox  id=order"+zb+">";        
        
    }
    document.getElementById('caption').appendChild(x);
}

function getTableAndJoin(){
    zq=getRowCount();
    table=[];
    join=[];
    for(x=1;x<=zq;x++){
        table.push(document.getElementById('tableList'+x).options[document.getElementById('tableList'+x).selectedIndex].value);
            if (x<zq) {
                for(u=1;u<=3;u++){
                 r1=document.getElementById('join'+x+'a'+u);
                 r1=r1.options[r1.selectedIndex].value;
                 r2=document.getElementById('join'+x+'b'+u);
                 r2=r2.options[r2.selectedIndex].value;
                 if ((r1!='' && r2!='') || (r1!='' && r2!='')) {
                    join.push(r1+"="+r2);
                 }
                }
            }
    }
    ss=[];
    ss.push(table);
    ss.push(join);
    return ss;
}

function getColumnDisplay(){
    column=[];  //1
    caption=[]; //2
    group=[];   //3
    subtotal=[];//4
    order=[];//5
    status='true';
    try{
        tab=document.getElementById('captionDisplay');
        z=tab.rows[0].cells;
        if(z.length>0){
            for(a=0;a<z.length;a++){
               //1 
               column.push(z[a].innerHTML);
               //2
               caption.push(document.getElementById('caption'+a).value);
               if (document.getElementById('caption'+a).value=='') {
                  status='false';
               }
               //3
               if (document.getElementById('group'+a).options[document.getElementById('group'+a).selectedIndex].value!='') {
                group.push(document.getElementById('group'+a).options[document.getElementById('group'+a).selectedIndex].value);
               }else{
                group.push(0);
               }
               //4
               if (document.getElementById('subtotal'+a).checked==true) {
                subtotal.push(1);
               }else{
                subtotal.push(0);
               }       
               if (document.getElementById('order'+a).checked==true) {
                order.push(1);
               }else{
                order.push(0);
               }                            
            }
        }else{
            status='false';
        }
        ss=[];
        ss.push(column);
        ss.push(caption);
        ss.push(group);
        ss.push(subtotal);
        ss.push(order);
        if (status=='false') {
            alert('Caption display requierd');
            return false;
        }else{
            return ss;
        }
    }
    catch(e) {
        alert('No Column to display');
        return false;
    }
}

function getCondition(){
    table=document.getElementById('parameter');
    parameterRow=table.rows.length;
    parameter=[];
    checker=[];
    for(x=1;x<=parameterRow;x++){
        z=document.getElementById('parameter'+x);
        yVal=z.options[z.selectedIndex].value;
        t=document.getElementById('operator'+x);
        uVal=t.options[t.selectedIndex].value;
        k=document.getElementById('condition'+x);
        lVal=k.options[k.selectedIndex].value;
        if (yVal!='Choose' && uVal!='Choose') {
            parameter.push(lVal+'##'+uVal+'##'+yVal);
            checker.push(lVal);
        }
    }
    checker.sort();
    
    status='true';
    for(a=0;a<=checker.length;a++){
        if (a==0) {
        }
        else if (checker[a-1]==checker[a]) {
            status='false';
            alert("Duplicate parameter condition on "+checker[a]);
            break;
        }
    }
    if (status=='true') {       
        return parameter; 
    }else{
        return false;
    }
}

function previewQuery(ev){
    ww=getCondition();
    format=[];
    operator=[];
    field=[];
    for(x=0;x<ww.length;x++){
        ud=ww[x].split('##');
        field.push(ud[0]);
        operator.push(ud[1]);
        format.push(ud[2]);
    }
    g=getColumnDisplay();
    if (g && ww) {
        width='500';
        height='200';
        title='Customized Form';
        x = document.createElement("TABLE");
        x.setAttribute("id","flyTable");
        x.setAttribute("class","rounded5");
        for(r=0;r<field.length;r++){
            row=x.insertRow(r);
            cell=row.insertCell(0);
            ru=field[r].split(".");
            cell.innerHTML=ru[1].toUpperCase();
            cell.setAttribute("value",field[r]);
            cell1=row.insertCell(1);
            cell1.align='center';
            cell1.innerHTML=operator[r];
            cell2=row.insertCell(2);
            nullx=operator[r].indexOf("NULL");
            betw=operator[r].indexOf("BETWEE");
            if (format[r]=='TEXT' && nullx<0 && betw<0) {
                cell2.innerHTML="<input type=text class=myinputtext id=frmparam"+r+" onkeypress='return tanpa_kutip(event);' size='12pt'>";
            }else if (format[r]=='DATE' && nullx<0 && betw<0) {
                cell2.innerHTML="<input type=text class=myinputtext id=frmparam"+r+" onkeypress='return false;' onmousemove=setCalendar(this.id) size='12pt'>";
            }else if (format[r]=='NUMERIC' && nullx<0 && betw<0) {
                cell2.innerHTML="<input type=text class=myinputtextnumber id=frmparam"+r+" onkeypress='return angka_doang(event);' size='12pt'>";
            }else if (nullx>-1) {
                cell2.innerHTML="<input type=text class=myinputtext id=frmparam"+r+" disabled size='12pt' value='"+operator[r]+"'>";
            }else if (betw>-1) {
                if (format[r]=='TEXT'){
                    cell2.innerHTML="<input type=text class=myinputtext id=frmparam"+r+" onkeypress='return tanpa_kutip(event);' size='12pt'> and <input type=text class=myinputtext id=frmparama"+r+" onkeypress='return tanpa_kutip(event);' size='12pt'>";
                }else if (format[r]=='NUMERIC') {
                    cell2.innerHTML="<input type=text class=myinputtextnumber id=frmparam"+r+" onkeypress='return angka_doang(event);' size='12pt'> and <input type=text class=myinputtextnumber id=frmparama"+r+" onkeypress='return angka_doang(event);' size='12pt'>";
                } else if (format[r]=='DATE') {
                    cell2.innerHTML="<input type=text class=myinputtext id=frmparam"+r+" onkeypress='return false;' onmousemove=setCalendar(this.id) size='12pt'> and <input type=text class=myinputtext id=frmparama"+r+" onkeypress='return false;' onmousemove=setCalendar(this.id) size='12pt'>";
                }
            }
        }
        prevButt=document.createElement("BUTTON");
        prevButt.setAttribute("onclick","goTest(event,'preview')");
        prevButt.setAttribute("class","myButtonR");
        node = document.createTextNode("Go!");
        prevButt.appendChild(node);
        cent=document.createElement("center");
        cent.appendChild(prevButt);
        content='';
        width='150px';
        height='60px';
        //content=format.toString()+field.toString();
        formDialoque(title,content,width,height,ev);
        document.getElementById('dynamicX').appendChild(x);
        document.getElementById('dynamicX').appendChild(cent);
    }  
}

function isArray(myArray) {
    return myArray.constructor.toString().indexOf("Array") > -1;
}

function formDialoque(title,content,width,height,ev){
    closeFormDialoque();
    c = document.createElement('div');
    c.setAttribute('id', 'dynamic3');
    c.setAttribute('class', 'drag');
    c.style.position = 'absolute';
    c.style.display = 'none';
    c.style.width = width+'px';
    c.style.paddingTop = '3px';
    document.body.appendChild(c);
    cont="<b style='color:#FFFFFF;'>"+title+"</b><img src=images/closebig.gif align=right onclick=closeFormDialoque() title='Close detail' class=closebtn onmouseover=\"this.src='images/closebigon.gif';\" onmouseout=\"this.src='images/closebig.gif';\"><br><br>";
	cont+="<div id=dynamicX style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px'>";
	cont+=content;
	cont+="</div>";
	document.getElementById('dynamic3').innerHTML=cont;
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic3').style.top = pos[1] + 'px';
	document.getElementById('dynamic3').style.left = (pos[0]-100)+'px';
	document.getElementById('dynamic3').style.display='';
}

function closeFormDialoque(){
    try{
    document.getElementById('dynamic3').innserHTML='';
    document.getElementById('dynamic3').style.display='none';
    closeDialog1();
    }catch(e){
        
    }
}
function getJudul(){
    judul=document.getElementById('judul').value;
    if (judul=='') {
        alert('Report title required');
        return false;
    }else{
        return judul;
    }
}

function getParameterInput(){
    flyTable=document.getElementById('flyTable');
    numParam=flyTable.rows.length;
    paramX='';
    for(dd=0;dd<numParam;dd++){
        if(paramX!='') {
            paramX+=' and ';
       }       
        nullx=flyTable.rows[dd].cells[1].innerHTML.indexOf("NULL");
        betw=flyTable.rows[dd].cells[1].innerHTML.indexOf("BETWEE");
        likeX=flyTable.rows[dd].cells[1].innerHTML.indexOf("LIKE");
        inX=flyTable.rows[dd].cells[1].innerHTML.indexOf("IN");
        
        opera=flyTable.rows[dd].cells[1].innerHTML;
        opera=opera.replace("&gt;",">");
        opera=opera.replace("&lt;","<");
        rt=document.getElementById('frmparam'+dd).getAttribute("onmousemove");
        if (betw>-1) {
            par1=document.getElementById('frmparam'+dd).value;
            par2=document.getElementById('frmparama'+dd).value;
            if (rt!=null && rt.indexOf("setCalendar")>-1) {
                par1=par1.split("-");
                par1=par1[2]+"-"+par1[1]+"-"+par1[0];
                par2=par2.split("-");
                par2=par2[2]+"-"+par2[1]+"-"+par2[0];
            }
            paramX+=" ("+flyTable.rows[dd].cells[0].getAttribute("value")+" "+opera+" '"+par1+"' and '"+par2+"') ";
        }else if (nullx>-1) {
             paramX+=" "+flyTable.rows[dd].cells[0].getAttribute("value")+" "+opera;
        }else if (likeX>-1) {
             par1=document.getElementById('frmparam'+dd).value;
                if (rt!=null && rt.indexOf("setCalendar")>-1) {
                    par1=par1.split("-");
                    par1=par1[2]+"-"+par1[1]+"-"+par1[0];
                }             
             paramX+=" "+flyTable.rows[dd].cells[0].getAttribute("value")+" "+opera+" '::persen::"+par1+"::persen::'";
        }else if (inX>-1) {
             raw=document.getElementById('frmparam'+dd).value;
             raw=raw.split(",");
             par1='';
             for(cd=0;cd<raw.length;cd++){
                if (cd==0) {
                    par1+="'"+raw[cd]+"'";
                }else{
                    par1+=",'"+raw[cd]+"'";
                }
             }
             paramX+=" "+flyTable.rows[dd].cells[0].getAttribute("value")+" "+opera+" ("+par1+")";
        }else{
             par1=document.getElementById('frmparam'+dd).value;
                if (rt!=null && rt.indexOf("setCalendar")>-1) {
                    par1=par1.split("-");
                    par1=par1[2]+"-"+par1[1]+"-"+par1[0];
                }  
             paramX+=" "+flyTable.rows[dd].cells[0].getAttribute("value")+" "+opera+" '"+par1+"'";
        }
    }
    return paramX;
}
function goTest(ev, jenis) {
    //1.ambil table
    tableAndJoin = getTableAndJoin();
    //alert(tableAndJoin[0]+":"+tableAndJoin[1]);
    sd = tableAndJoin[1];
    joinX = sd.join(",");
    tableX = tableAndJoin[0].toString();
    //2.ambil judul laporan
    judul = getJudul();
    //3.ambil kolom display
    kolomTampil = getColumnDisplay();
    select = kolomTampil[0]
        namakolom = kolomTampil[1]
        group = kolomTampil[2]
        subtotal = kolomTampil[3]
        order = kolomTampil[4]
        //4. ambil kondisi
        //kondisi=getCondition();
        //5 ambil parameter input
        if (jenis == 'preview') {
            paramY = getParameterInput();
        } else {
            paramY = getCondition();
        }
        //if (paramY) {
	param = 'action=preview&table=' + tableX + '&join=' + joinX + '&judul=' + judul + '&kolomTampil=' + namakolom + '&parameter=' + paramY;
    param += '&kolomSelect=' + select + '&grouping=' + group + '&subtotal=' + subtotal + '&order=' + order + '&jenis=' + jenis;
    tujuan = 'tool_slaveQueryGenerator.php';
    post_response_text(tujuan, param, respog);
    //}
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (jenis == 'preview') {
                        title = judul;
                        height = '400px';
                        width = '900px';
                        content = '<div style="width:800px;height:350px;overflow:auto;text-shadow:none;">' + con.responseText + '</div>';
                        showDialog1(title, content, width, height, ev);
                    } else {
                        alert(con.responseText);
						window.location.reload();
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);

            }
        }
    }
}

function saveConfig(ev, jenis) {
    if (confirm('Saving configuration, Config not changeable, are you sure..?')) {
        goTest(ev, jenis)
    }
}

function protectColumn(obj,serial){
    if (obj.value!='') {
        document.getElementById('subtotal'+serial).checked=false;
        document.getElementById('order'+serial).checked=false;
        document.getElementById('subtotal'+serial).disabled=true;
        document.getElementById('order'+serial).disabled=true;
    }else{
        document.getElementById('subtotal'+serial).disabled=false;
        document.getElementById('order'+serial).disabled=false;       
    }
}

function protectOrder(obj,serial){
    if (obj.checked==true) {
        d=document.getElementById('captionDisplay');
        cols=d.rows[0].cells.length;
        valid='false';
        
        for(dd=0;dd<cols;dd++){
            if (dd==obj.id.replace('subtotal','') && document.getElementById('group'+dd).options[document.getElementById('group'+dd).selectedIndex].value!='') {
                alert('This column uses as sub total can not be grouped');
            }
            else if (document.getElementById('group'+dd).options[document.getElementById('group'+dd).selectedIndex].value!='') {
                valid='true';
            }
        }
        
        if (valid=='true') {
                document.getElementById('group'+serial).options[0].selected=true;
                document.getElementById('order'+serial).checked=true;
                document.getElementById('group'+serial).disabled=true;
                document.getElementById('order'+serial).disabled=true;
        }else{
           obj.checked=false;
           alert('Define column of "sum" before gouping');
        }
        for(dd=0;dd<cols;dd++){
            if (document.getElementById('subtotal'+dd)==obj) {
                continue;
            }else{
                document.getElementById('subtotal'+dd).checked=false;
            }
        }        
    }else{
        document.getElementById('group'+serial).disabled=false;
        document.getElementById('order'+serial).disabled=false;
        document.getElementById('order'+serial).checked=false;
    }
}

function change(obj, type, val, rnumber) {
    obj.style.backgroundColor = 'orange';
    if (val == '') {

        if (obj.checked == true) {
            sta = 1;
        } else {
            sta = 0;
        }
    } else {
        sta = val;
    }
    param = "column=" + type + '&status=' + sta + '&action=updateTable&rnumber=' + rnumber;
    tujuan = 'tool_slaveQueryGenerator.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    obj.style.backgroundColor = '#ffffff';
                }
            } else {
                busy_off();
                error_catch(con.status);

            }
        }
    }
}

function  userOf(ev,rnumber) {
    
    param='action=getUser&rnumber='+rnumber;
    tujuan='tool_slaveQueryGenerator.php';
    post_response_text(tujuan, param, respog);
    function respog()
	{
      if(con.readyState==4)
      {
        if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);                      
                    }
                    else {
                        
                         content=con.responseText;
                         title='';
                         height='350px';
                         width='500px';
                         formDialoque(title,content,width,height,ev);
                    }
		}
		else {
			busy_off();
			error_catch(con.status);
			
		}
      }	
     }    
}
function updateTollUser(obj,rnumber,user) {
    if (obj.checked==true) {
        val=1;
    }else{
        val=0;
    }
    param='action=updateUser&user='+user+'&rnumber='+rnumber+'&val='+val;
    tujuan='tool_slaveQueryGenerator.php';
    post_response_text(tujuan, param, respog);
    function respog()
	{
      if(con.readyState==4)
      {
        if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            if (obj.checked==true) {
                                obj.checked=false;
                            }else{
                                obj.checked=true;
                            }
                            alert(con.responseText);                      
                    }
                    else {
                    }
		}
		else {
			busy_off();
			error_catch(con.status);
			
		}
      }	
     }    
}

function browseR(ev,rnumber){
    param='action=browseReport&rnumber='+rnumber;
    tujuan='tool_slaveQueryGenerator.php';
    post_response_text(tujuan, param, respog);
    function respog()
	{
      if(con.readyState==4)
      {
        if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);                      
                    }
                    else {
                        content=con.responseText;
                         title='Customized Form';
                         height='180px';
                         width='200px';
                         formDialoque(title,content,width,height,ev);  
                    }
		}
		else {
			busy_off();
			error_catch(con.status);
			
		}
      }	
     }    
}

function displayReportUser(tipe,rnumber,ev){
    zet=getParameterInput();
    param="tipe="+tipe+"&rnumber="+rnumber+"&parameter="+zet+"&action=load";
    tujuan='report_slave_customized.php';
    title='';
    height='400px';
    width='';                   
    if (tipe!='html') {	
         content='<iframe height=300 src="'+tujuan+'?'+param+'"></iframe>';
         showDialog1(title,content,width,height,ev); 
    }else{
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
                    else {
                          content='<div style="max-width:1000px;height:350px;overflow:auto;text-shadow:none;">'+con.responseText+'</div>';
                          showDialog1(title,content,width,height,ev);                      
                    }
		}
		else {
			busy_off();
			error_catch(con.status);
			
		}
      }	
     }        
}