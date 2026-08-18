function toggle(id) {
    var el = document.getElementById(id);
    var img = document.getElementById("arrow");
    var box = el.getAttribute("class");
    if(box == "hide"){
        el.setAttribute("class", "show");
        delay(img, "images/arrows_right.png", 400);
    }
    else{
        el.setAttribute("class", "hide");
        delay(img, "images/arrows_left.png", 400);
    }
}

function delay(elem, src, delayTime){
    window.setTimeout(function() {elem.setAttribute("src", src);}, delayTime);
}

// var menuTimeout = null;

// function showCoords(event) {
	// var x = event.clientX;
    // var y = event.clientY;
    // if(((screen.width) - x) <= 10){
		// clearTimeout(menuTimeout);
		// menuTimeout = null;
		// showMenu();
	// }else if(menuTimeout === null){
		// menuTimeout = setTimeout(hideMenu, 2000);
	// }
// }
// function showMenu() {
	// document.getElementById('menu').style.display = 'block';
// }

// function hideMenu() {
	// document.getElementById('menu').style.display = 'none';
// }

// //============

// function autohide(){
	// tdform = document.getElementById('tdform');
	// arrows = document.getElementById('arrows');
	// if(tdform.style.display == 'none'){
		// tdform.style.display = '';
		// arrows.innerHTML = '<';
	// }else{
		// tdform.style.display = 'none';
		// arrows.innerHTML = '>';
	// }
// }

function logout()
{
	param='';
	post_response_text('../logout.php', param, respog);
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					window.location='login.html'; 
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}	
		}
	}
}

function map()
{
	window.location='./bi_map.php'; 
}

function accordionClick(id){
	head = document.getElementById('head'+id);
	content = document.getElementById('content'+id);
	span = document.getElementById('span'+id);
	
	if(content.style.display == 'none'){
		content.style.display = '';
		span.innerHTML = "<img src='images/arrow_top1.png'>";
	}else{
		content.style.display = 'none';
		span.innerHTML = "<img src='images/arrow_down1.png'>";
		
	}
}

function graph()
{	
	window.location='./bi_graph.php'; 
}


function clearisi()
{
	document.getElementById('isidt').innerHTML = '';
}

function getisi(file)
{
		
    param = '';
   // param += '&idmenu=' + idmenu;
    tujuan = file; 

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
                else {
					
					
					//document.getElementById('head').style.display = 'none';
                   // document.getElementById('foot').style.display = 'block';
					document.getElementById('isidt').innerHTML = con.responseText;
                    //document.getElementById('menudt').innerHTML = con.responseText;
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



function homegraph()
{
	document.getElementById('head').style.display = 'block';
	document.getElementById('foot').style.display = 'none';
	document.getElementById('buttonback').style.display = 'none';
	//document.getElementById('foot').innerHTML = '';
}

function getmenu(idmenu)
{
	if(idmenu==undefined)
	{
		idmenu=document.getElementById('idmenu').value;
	}
	pt = document.getElementById('pt');
    pt = pt.options[pt.selectedIndex].value;
	
	thn = document.getElementById('thn');
    thn = thn.options[thn.selectedIndex].value;
	
    param = 'method=getmenu';
    param += '&idmenu=' + idmenu;
	if (pt != '') 
	{
        param += '&pt=' + pt;
    }
	if (thn != '') 
	{
        param += '&thn=' + thn;
    }

    tujuan = 'bi_slave_graph.php';
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
                else {
					
					document.getElementById('head').style.display = 'none';
                    document.getElementById('foot').style.display = 'block';
					document.getElementById('menudt').innerHTML = con.responseText;
					document.getElementById('idmenu').value=idmenu;
					document.getElementById('buttonback').style.display = 'block';
					
                    //document.getElementById('menudt').innerHTML = con.responseText;
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

function showoption()
{
	document.getElementById('menumap').style.display = 'block';
}

function hideoption()
{
	document.getElementById('menumap').style.display = 'none';
}

function showoptionx()
{
	document.getElementById('draggable').style.display = 'block';
}