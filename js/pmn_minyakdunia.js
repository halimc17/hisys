
function getDataOnline(){
	var tujuan = "http://www.bursamalaysia.com/market/derivatives/prices/prices_f.html?_=" + new Date().getTime();
	var param = "";
	con.open("GET", tujuan, true);
	con.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	con.setRequestHeader("Connection", "close");
	con.onreadystatechange = functiontoexecute;
	con.send(null);
	function functiontoexecute(){
		if(con.readyState==4)
        {
          if (con.status == 200) {
             console.log(con.responseText);
          }
          else {
              error_catch(con.status);
          }
        } 
	}
}
function readHTML(html){
	var doc = JSON.parse(html);
	var parser = new DOMParser();
	var content = parser.parseFromString(doc.html, "text/html");
	var table = content.getElementById('bm_derivatives_prices_table');
	var th = table.getElementsByTagName('th');
	var tbody = table.getElementsByTagName('tbody')[0];
	var tr = tbody.getElementsByTagName('tr');
	
	var header = [];
	for(i=0; i<th.length; i++){
		judul = th[i].textContent.toLowerCase();
		var result = judul.replace(/\s/g, '');
		var result = result.replace(/\*/g, '');
		var result = result.replace(/\./g, '');
		header.push(result);
	}
	var dataTr = [];
	for(i=0; i<tr.length; i++){
		var datarow = tr[i].getElementsByTagName('td');
		var dataTd = {};
		for(ii=0; ii<datarow.length; ii++){
			if(ii == 11){
				dataTd[header[ii]].push(datarow[ii].getElementsByTagName('a')[0].textContent);
			}else{
				dataTd[header[ii]].push(datarow[ii].textContent);
			}
		}
		dataTr.push(dataTd);
	}	
	console.log(dataTr);
	//loaddata('updatedata',dataTr);
}

function loaddata(switcher,data){
	if(typeof data !== 'undefined'){
		var param = "data="+data;
	}else{
		var param = "";
	}
	if(typeof switcher !== 'undefined'){
		var switcher = "?swicther="+switcher;
	}else{
		var switcher = "";
	}
	post_response_text('pmn_slave_minyakdunia.php'+switcher, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    table=document.getElementById('content');
					table.innerHTML = con.responseText;
				}
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
window.addEventListener('load',loaddata);