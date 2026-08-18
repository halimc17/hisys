/**
 * zMaster
 * Class for Master Data
 */
zMaster = {
	printPdf: function (event, config) {
        // Prep Param
		param = "table=";
        param += (config.table)? config.table: '';
        param += "&column=";
		param += (config.column)? config.column: '';
		
        // Prep Condition
        param += "&cond=";
		param += (config.cond)? config.cond: '';
		
        // Post to Slave
        page = (config.page)? config.page: "slave_master_pdf";
        
		showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px' src='"+page+".php?"+param+"'></iframe>",'800','400',event);
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
	},
	
	getValue: function(id) {
		var tmp = document.getElementById(id);
		if(!tmp) {
			alert("DOM Definition Error : "+id);
			return false;
		}
		if(tmp.getAttribute('type')=='checkbox') {
			if(tmp.checked==true) {
				return 1;
			} else {
				return 0;
			}
		} else if(tmp.options) {
			return tmp.options[tmp.selectedIndex].value;
			}
		else if(tmp.getAttribute('type')=='text') {
			return tmp.value;
			}	
		else if(tmp.getAttribute('type')=='textarea') {
			return tmp.value;
			}
		else if(tmp.getAttribute('type')=='button') {
			return tmp.value;
			}			
		else if(tmp.hasAttribute('value')) {
			if(tmp.getAttribute('value')!='') {
				return tmp.getAttribute('value');
			} else {
				return tmp.value;
			}
		} else {
			  if(tmp.innerHTML!='')
				{return tmp.innerHTML;}
			  else
				 {return tmp.value;	}	
		}
	},
	
	init: function() {
		return this;
	}
};

/*
 Function editRow
 Fungsi untuk passing parameter dari table ke field
 num = Nomor Urut Baris
 field = list dari field dengan format '##field1##field2'
 value = list dari nilai yang di pass dengan format '##value1##value2'
*/
function editRow(num,field,value,freeze) {
	var fieldJs = field.split("##");
	// Extract Updated Value
	value = '';
	for(i=1;i<fieldJs.length;i++) {
		var tmp = document.getElementById(fieldJs[i]+"_"+num);
		if(tmp)
			value += "##"+tmp.getAttribute('value');
	}

	var valueJs = value.split("##");
	var add = document.getElementById('add');
	var edit = document.getElementById('edit');
	var currRow= document.getElementById('currRow');

	if(freeze==undefined) {
		freeze = false;
	} else if(freeze) {
		var freezed = freeze.split('##');
	}

	// Pass Parameter
	for(i=1;i<fieldJs.length;i++) {
		var tmp = document.getElementById(fieldJs[i]),
			tmp1 = document.getElementById(fieldJs[i]+'_size');
		
		// Passing Name
		var tmpName = document.getElementById(fieldJs[i]+'_name');
		if(tmpName) {
			var tdEl = document.getElementById(fieldJs[i]+'_'+num);
			tmpName.value = tdEl.innerHTML;
		}
		
		if(tmp1) {
			// Case Multi Checkbox
			for(var k=0;k<tmp1.value;k++) {
				var tmp2 = document.getElementById(fieldJs[i]+k);
				if(valueJs[i][k]=='1') {
					tmp2.checked=true;
				} else {
					tmp2.checked=false;
				}
			}
			tmp.value = valueJs[i];
		} else if(tmp.options) {
			// Field Select
			for(j=0;j<tmp.options.length;j++) {
				if(tmp.options[j].value==valueJs[i])
					tmp.options[j].selected=true;
			}
		} else if(tmp.getAttribute('type')=='checkbox') {
			if(valueJs[i]=='1') {
				tmp.checked = true;
			} else {
				tmp.checked = false;
			}
		} else {
			tmp.value = valueJs[i];
		}
	}

	// Freeze Coresponden field
	if(freeze) {
		for(i in freezed) {
			document.getElementById(freezed[i]).setAttribute('disabled','disabled');
			var btn = document.getElementById(freezed[i]+'_search');
			if(btn) {
				btn.setAttribute('disabled',true);
			}
		}
	}

	// Update Current Edited Row
	currRow.value = num;

	// Display Edit & Hide Add
	add.style.display = 'none';
	edit.style.display = '';
}

/*
 Function clearData
 Fungsi untuk mengembalikan form ke mode tambah data
*/
function clearData(field,dis) {
        var disabledJs = dis.split('##');
        var fieldJs = field.split('##');
        var add = document.getElementById('add');
        var edit = document.getElementById('edit');
        var sBarang = document.getElementById('searchBarang');

        // Unfreezed All Field and Blank Value
        for(var i=1;i<fieldJs.length;i++) {
			tmpField = document.getElementById(fieldJs[i]);
			// Empty Field
			if(tmpField.options) {
				tmpField.selectedIndex='';
			} else {
				if(tmpField.className=='myinputtextnumber') {
					tmpField.value='0';
				} else {
					tmpField.value='';
				}
			}
			// Empty Name
			var tmpName = document.getElementById(fieldJs[i]+'_name');
			if(tmpName) {
				tmpName.value = '';
			}
			
			// Find if field stay disabled
			var isDis = false;
			for(var j=1;j<disabledJs.length;j++) {
				if(fieldJs[i]==disabledJs[j]) {
					isDis=true;
				}
			}
			// Enable
			if(isDis==false) {
				tmpField.removeAttribute('disabled');
			}
			
			// Enable Button
			var tmpBtn = document.getElementById(fieldJs[i]+'_search');
			if(tmpBtn) {
				tmpBtn.removeAttribute('disabled');
			}
        }
        if(sBarang) {
            sBarang.removeAttribute('disabled');
        }

        // Display Edit & Hide Add
        add.style.display = '';
        edit.style.display = 'none';
}

/*
 Function delRow
 Fungsi untuk menghapus row yang sesuai pada table
 num = Nomor Urut Baris
 field = list dari field primary key dengan format '##field1##field2'
 value = list dari nilai primary key dengan format '##value1##value2'
 page = halaman tujuan
 table = table database yang akan dihapus
*/
function delRow(num,field,value,page,table) {
	alertify.confirm("You'll delete row number "+num+"\n Are you sure?",
		function(){
			var fieldJs = field.split("##");
			var valueJs = value.split("##");
			var param = "tableName=" + table;

			// Get Parameter
			for(i=1;i<fieldJs.length;i++) {
				param += "&" + fieldJs[i] + "=" + valueJs[i];
			}

			// Post to Slave
			if(page==null) {
				page = "slave_master_delete";
			}
			
			post_response_text(page+'.php', param, respon);
		},
		function(){
			return;
		}
	);
	
	// Catch Result
	function respon(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Infomation",con.responseText);
				} else {
					alertify.alert("Infomation",'Data Deleted\n Note :' + con.responseText);
					var row=document.getElementById("tr_"+num)
					if(row) {
						row.style.display="none";
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

/*
 Function addData
 Fungsi untuk menambah data ke table
 field = list dari field dengan format '##field1##field2'
 table = nama table
 page = halaman tujuan (slave page)
*/
function addData(field,id,table,page,freeze,empty,exceptField,opt,dis) {
        var fieldJs = field.split("##");
        var exceptFieldJs = exceptField.split('##');

    for(i=1;i<fieldJs.length;i++) {
		var tmp = document.getElementById(fieldJs[i]);
		var except = false;
		for(j=1;j<exceptFieldJs.length;j++) {
			if(exceptFieldJs[j]==fieldJs[i]) {
				except = true;
			}
		}
		if(table=='setup_kegiatan') {
			if(fieldJs[i]=='kodekegiatan') {
				if(tmp.value.length==9) {
					
                }else{
					alert(fieldJs[i]+" must 9 character");
					return;
				}
			}
		} else if(table=='keu_5akun') {
            if(fieldJs[i]=='noakun') {
				if(tmp.value.length<7 && document.getElementById('detail').checked) {
					alert('Account number must 7 Char for detail Account');
					return;
				}
			}
		}
		if(zMaster.getValue(fieldJs[i])=='' && empty==false && except==false) {
			alert(fieldJs[i]+" is obligatory");
			return;
		}
	}

    alertify.confirm("You'll add new data\n Are you sure?",
		function(){
			if(typeof empty=='undefined') {
				empty = false;
			} else {
				empty = true;
			}
			
			var fieldJs = field.split("##");
			var idJs = id.split("##");
			var param = "tableName=" + table;
			var mTable = document.getElementById('masterTable');
			
			// Cek opt if searchBarang exist
			var optObj = JSON.parse(opt);
			for(var i in optObj) {
				if(optObj[i]=='searchBarang') {
					for(var j in optObj[i]) {
						var tmpVal = document.getElementById(optObj[i][j]).value;
						optObj[optObj[i][j]][tmpVal] = document.getElementById(optObj[i][j]+'_name').value;
					}
				}
			}
			opt = JSON.stringify(optObj);
			//console.log(opt);
			// Get Available numRow
			var numRow = 0;
			var tmpRow = document.getElementById('tr_'+numRow);
			while(tmpRow) {
					numRow++;
					tmpRow = document.getElementById('tr_'+numRow);
			}
			param += "&numRow="+numRow;
			param += "&opt="+opt;

			// Get Parameter
			var idField = "";
			var idVal = "";
			for(i=1;i<fieldJs.length;i++) {
				tmpField = document.getElementById(fieldJs[i]);
				if(tmpField.options) {
						param += "&" + fieldJs[i] + "=" + tmpField.options[tmpField.selectedIndex].value;
						for(j=1;j<idJs.length;j++){
								if(fieldJs[i] == idJs[j]) {
										idField += "##" + fieldJs[i];
										idVal += "##" + tmpField.options[tmpField.selectedIndex].value;
								}
						}
				} else if(tmpField.getAttribute('type')=='checkbox') {
						if(tmpField.checked) {
								var tmpVal = '1';
						} else {
								var tmpVal = '0';
						}
						param += "&" + fieldJs[i] + "=" + tmpVal;
						for(j=1;j<idJs.length;j++){
								if(fieldJs[i] == idJs[j]) {
										idField += "##" + fieldJs[i];
										idVal += "##" + tmpVal;
								}
						}
				} else {
						param += "&" + fieldJs[i] + "=" + document.getElementById(fieldJs[i]).value;
						for(j=1;j<idJs.length;j++){
								if(fieldJs[i] == idJs[j]) {
										idField += "##" + fieldJs[i];
										idVal += "##" + tmpField.value;
								}
						}
				}
			}
			param += "&idField="+idField+"&idVal="+idVal;
			if(typeof freeze!='undefined') {
					param += "&freeze="+freeze;
			}

			// Post to Slave
			if(page==null) {
				page = "slave_master_add";
			}
			
			post_response_text(page+'.php?empty='+empty, param, respon);
		},
		function(){
			return;
		}
	);
	// Catch Result
	function respon(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					mTable = document.getElementById('mTabBody');
					mTable.innerHTML += con.responseText;
					clearData(field,dis);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

/*
 Function editData
 Fungsi untuk mengubah data pada table
 field = list dari field dengan format '##field1##field2'
 table = nama table
 page = halaman tujuan (slave page)
*/
function editData(field,id,table,page,opt) {
    var fieldJs = field.split("##");

    for(i=1;i<fieldJs.length;i++) {
		var tmp = document.getElementById(fieldJs[i]);
		var except = false;
		if(table=='setup_kegiatan'){
			if(fieldJs[i]=='kodekegiatan'){
				if(tmp.value.length==9){

				}else{
					alert(fieldJs[i]+" must 9 character");
					return;
				}
			}
		} else if(table=='keu_5akun') {
			if(fieldJs[i]=='noakun'){
				if(tmp.value.length<7 && document.getElementById('detail').checked){
					alert('Account number must 7 Char for detail Account');
					return;
				}else{
				}
			}
		}   
		if(zMaster.getValue(fieldJs[i])=='' && tmp.getAttribute('type')!='checkbox' && table!='keu_5parameterjurnal'){
				alert(fieldJs[i]+" is obligatory");
				return;
		}
    }
	alertify.confirm("You'll edit the data\n Are you sure?",
		function(){
			var fieldJs = field.split("##"),
				idJs = id.split("##"),
				param = "tableName=" + table,
				mTable = document.getElementById('masterTable'),
				currRow = getValue('currRow'),
				IDs = "";
			
			// Cek opt if searchBarang exist
			var optObj = JSON.parse(opt);
			for(i in optObj) {
				if(optObj[i]=='searchBarang') {
					for(var j in optObj[i]) {
						var tmpVal = document.getElementById(optObj[i][j]).value;
						optObj[optObj[i][j]][tmpVal] = document.getElementById(optObj[i][j]+'_name').value;
					}
				}
			}
			opt = JSON.stringify(optObj);
			
			// Get Parameter
			for(i=1;i<fieldJs.length;i++) {
				tmpField = document.getElementById(fieldJs[i]);
				is_ID = false;
				for(j=1;j<idJs.length;j++) {
					if(idJs[j]==fieldJs[i]) {
						is_ID = true;
					}
				}
				if(tmpField.options) {
					param += "&" + fieldJs[i] + "=" + tmpField.options[tmpField.selectedIndex].value;
					//if(is_ID) {
					//	IDs += "##"+fieldJs[i]+","+tmpField.options[tmpField.selectedIndex].value;
					//}
				} else if(tmpField.getAttribute('type')=='checkbox') {
					if(tmpField.checked) {
						var tmpVal = '1';
					} else {
						var tmpVal = '0';
					}
					param += "&" + fieldJs[i] + "=" + tmpVal;
					//if(is_ID) {
					//	IDs += "##"+fieldJs[i]+","+tmpVal;
					//}
				} else {
					param += "&" + fieldJs[i] + "=" + document.getElementById(fieldJs[i]).value;
					//if(is_ID) {
					//	IDs += "##"+fieldJs[i]+","+document.getElementById(fieldJs[i]).value;
					//}
				}
				if(is_ID) {
					IDs += "##"+fieldJs[i]+","+ getValue(fieldJs[i]+'_'+currRow);
				}
			}
			param += "&IDs="+IDs;
			param += "&opt="+opt;

			// Post to Slave
			if(page==null) {
					page = "slave_master_edit";
			}
			// Catch Result
			function respon(){
				if (con.readyState == 4) {
					if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
						} else {
							eval(con.responseText);
							//alert(con.responseText);
							//location.reload(true);
						}
					} else {
						busy_off();
						error_catch(con.status);
					}
				}
			}
			post_response_text(page+'.php', param, respon);
		},
		function(){
			return;
		}
	);
}

/*
 Function masterPDF
 Fungsi untuk print table master
 table = nama table
 column = list field
 cond = Kondisi where untuk query
 page = halaman tujuan (slave page)
*/
function masterPDF(table,column,cond,page,event) {
        // Prep Param
        param = "table="+table;
        param += "&column="+column;

        // Prep Condition
        param += "&cond="+cond;

        // Post to Slave
        if(page==null) {
                page = 'null';
        }
        if(page=='null') {
                page = "slave_master_pdf";
        }

        // showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:395px' src='"+page+".php?"+param+"'></iframe>",'','',event);
        // var dialog = document.getElementById('dynamic1');
        // dialog.style.top = '50px';
        // dialog.style.left = '15%';
		
		alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+page+".php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}


function masterPDF2(table,column,cond,page,event) {
        // Prep Param
        param = "table="+table;
        param += "&column="+column;

        // Prep Condition
        param += "&cond="+cond;

        // Post to Slave
        if(page==null) {
                page = 'null';
        }
        if(page=='null') {
                page = "slave_master_pdf";
        }

        showDialog2('Print PDF',"<iframe frameborder=0 style='width:795px;height:395px' src='"+page+".php?"+param+"'></iframe>",'800','400',event);
        var dialog2 = document.getElementById('dynamic2');
        dialog2.style.top = '50px';
        dialog2.style.left = '15%';
}

function masterPDF3(table,column,cond,page,event) {
        // Prep Param
        param = "table="+table;
        param += "&column="+column;

        // Prep Condition
        param += "&cond="+cond;

        // Post to Slave
        if(page==null) {
                page = 'null';
        }
        if(page=='null') {
                page = "slave_master_pdf";
        }

        showDialog3('Print PDF',"<iframe frameborder=0 style='width:795px;height:395px' src='"+page+".php?"+param+"'></iframe>",'800','400',event);
        var dialog3 = document.getElementById('dynamic3');
        dialog3.style.top = '50px';
        dialog3.style.left = '15%';
}

function masterPDF5(table,column,cond,page,event) {
        // Prep Param
        param = "table="+table;
        param += "&column="+column;

        // Prep Condition
        param += "&cond="+cond;

        // Post to Slave
        if(page==null) {
                page = 'null';
        }
        if(page=='null') {
                page = "slave_master_pdf";
        }

        showDialog6('Print PDF',"<iframe frameborder=0 style='width:795px;height:395px' src='"+page+".php?"+param+"'></iframe>",'','',event);
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
}

/*
 Function updateMultiChk
 Fungsi untuk update field sesuai dengan centangan pada checkbox group
*/
function updateMultiChk(id) {
	var obj = document.getElementById(id),
		size = document.getElementById(id+'_size').value,
		tmpStr='',tmpList = [];
	
	for(var i=0;i<size;i++) {
		tmpList[i] = document.getElementById(id+i).checked;
	}
	
	for(i in tmpList) {
		if(tmpList[i]==true) {
			tmpStr += '1';
		} else {
			tmpStr += '0';
		}
	}
	
	obj.value = tmpStr;
}