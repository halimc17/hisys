function batal(){
	document.getElementById('kebun').selectedIndex=0;
	document.getElementById('blok').selectedIndex=0;
	document.getElementById('tahuntanam').selectedIndex=0;
	getBlok();
	clearListData();
}

function clearListData(){
	document.getElementById('table1').innerHTML='';
	document.getElementById('graph1').innerHTML='';
	document.getElementById('table2').innerHTML='';
	document.getElementById('graph2').innerHTML='';
	document.getElementById('table3').innerHTML='';
	document.getElementById('graph3').innerHTML='';
	document.getElementById('table4').innerHTML='';
	document.getElementById('graph4').innerHTML='';
	document.getElementById('table5').innerHTML='';
	document.getElementById('graph5').innerHTML='';
	document.getElementById('table6').innerHTML='';
	document.getElementById('graph6').innerHTML='';
}

function getTt(){
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	param='proses=getTahunTanam'+'&kebun='+kebun+'&blok='+blok;
    tujuan='kebun_slave_2historypemupukan.php';
    post_response_text(tujuan, param, respog);	

    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
					vsplit = con.responseText.split('####');
                    document.getElementById('tahuntanam').innerHTML=vsplit[0];
					if(vsplit[1]=='BLOK' || vsplit[1]==''){
						document.getElementById('tahuntanam').disabled=true;
					}else{
						document.getElementById('tahuntanam').disabled=false;
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

function getBlok(){
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	param='proses=getblok'+'&kebun='+kebun;
    tujuan='kebun_slave_2historypemupukan.php';
    post_response_text(tujuan, param, respog);	

    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('blok').innerHTML=con.responseText;
					clearListData();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function preview(){
	getGraph1();
	// kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	// blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	// tahuntanam=document.getElementById('tahuntanam').options[document.getElementById('tahuntanam').selectedIndex].value;
	
	// if(kebun==''){
		// alert('Kebun harus dipilih.');
		// return false;
	// }
	
	// if(blok==''){
		// alert('Blok harus dipilih.');
		// return false;
	// }
	
	// param='proses=loaddata'+'&kebun='+kebun+'&blok='+blok+'&tahuntanam='+tahuntanam;
    // tujuan='kebun_slave_2historypemupukan.php';
    // post_response_text(tujuan, param, respog);
	
	// function respog(){
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // }
                // else {
					// split=con.responseText.split('####');
                    // document.getElementById('table1').innerHTML=split[0];
                    
					// document.getElementById('table2').innerHTML=split[1];
                    // document.getElementById('table3').innerHTML=split[2];
                    // document.getElementById('table4').innerHTML=split[3];
                    // document.getElementById('table5').innerHTML=split[4];
                    // document.getElementById('table6').innerHTML=split[5];
					// getGraph1();
                // }
            // }
            // else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
}

function getGraph1(){
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	tahuntanam=document.getElementById('tahuntanam').options[document.getElementById('tahuntanam').selectedIndex].value;
	
	param='proses=getGraph1'+'&kebun='+kebun+'&blok='+blok+'&tahuntanam='+tahuntanam;
    tujuan='kebun_slave_2historypemupukan.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('graph1').innerHTML=con.responseText;
					getGraph2();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getGraph2(){
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	tahuntanam=document.getElementById('tahuntanam').options[document.getElementById('tahuntanam').selectedIndex].value;
	
	param='proses=getGraph2'+'&kebun='+kebun+'&blok='+blok+'&tahuntanam='+tahuntanam;
    tujuan='kebun_slave_2historypemupukan.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('graph2').innerHTML=con.responseText;
					getGraph3();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getGraph3(){
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	tahuntanam=document.getElementById('tahuntanam').options[document.getElementById('tahuntanam').selectedIndex].value;
	
	param='proses=getGraph3'+'&kebun='+kebun+'&blok='+blok+'&tahuntanam='+tahuntanam;
    tujuan='kebun_slave_2historypemupukan.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('graph3').innerHTML=con.responseText;
					getGraph4();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getGraph4(){
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	tahuntanam=document.getElementById('tahuntanam').options[document.getElementById('tahuntanam').selectedIndex].value;
	
	param='proses=getGraph4'+'&kebun='+kebun+'&blok='+blok+'&tahuntanam='+tahuntanam;
    tujuan='kebun_slave_2historypemupukan.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('graph4').innerHTML=con.responseText;
					getGraph5();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getGraph5(){
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	tahuntanam=document.getElementById('tahuntanam').options[document.getElementById('tahuntanam').selectedIndex].value;
	
	param='proses=getGraph5'+'&kebun='+kebun+'&blok='+blok+'&tahuntanam='+tahuntanam;
    tujuan='kebun_slave_2historypemupukan.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('graph5').innerHTML=con.responseText;
					getGraph6();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getGraph6(){
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	tahuntanam=document.getElementById('tahuntanam').options[document.getElementById('tahuntanam').selectedIndex].value;
	
	param='proses=getGraph6'+'&kebun='+kebun+'&blok='+blok+'&tahuntanam='+tahuntanam;
    tujuan='kebun_slave_2historypemupukan.php';
    post_response_text(tujuan, param, respog);
	
	function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('graph6').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}