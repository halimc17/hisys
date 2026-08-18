<?
include('../config/connection.php');
include('../lib/nangkoelib.php');


OPEN_BODY_NEWBI();
?>
<link rel=stylesheet type=text/css href=../bi/style/graph.css>

<script>

function detailrow(nourut,total)
{
	for(i=1;i<=total;i++)
	{
		var row = document.getElementById('dt'+nourut+i);
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




function menu()
{
	document.getElementById('isidata').innerHTML = '';
	var row = document.getElementById('nav');
	if(row !== null){
		if (row.style.display == '') {
			row.style.display = 'none';
		}
		else {
			row.style.display = '';
		}
	}
}


function clearmenu()
{
	document.getElementById('nav').style.display = 'none';
}


function getdata(file)
{
    param = 'method=getdata';
    tujuan =file;
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
                    document.getElementById('isidata').style.display = 'block';
                    document.getElementById('isidata').innerHTML = con.responseText;
					
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
</script>
	
<?
OPEN_BOX_BI('','BI GRAPH');

$str="select * from ".$dbname.".menugraph where tipe=0 order by  caption asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$menuht[$bar['id']]=$bar['id'];
	$capht[$bar['id']]=$bar['caption'];
}


$str="select * from ".$dbname.".menugraph where tipe=1 order by  caption asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$dt[$bar['id']]=$bar['id'];
	$listdt[$bar['induk']][$bar['id']]=$bar['id'];
	$menudt[$bar['induk']][$bar['id']]=$bar['id'];
	$capdt[$bar['induk']][$bar['id']]=$bar['caption'];
	$file[$bar['induk']][$bar['id']]=$bar['file'];
	$totdt[$bar['induk']]+=1;
}

$stylehidden = "style='display:none'";	
echo "<div style='height:600px'>";


echo"<table  cellspacing=1 cellpadding=1 border=0>";

echo"<tr>";
	echo"<td style='border:1px solid orange;width:300px;height:580px;vertical-align:top;padding:5px;'>";
		//echo"<div id=nav>";
		echo"<table cellspacing=0 cellpadding=1 border=0 bgcolor=white>";
		foreach($menuht as $head)
		{
			$no+=1;
			echo"<thead>
					<tr class=rowcontent>
					<td style='border:0px solid orange;width:300px;height:100%;vertical-align:top;padding:10px;'><b>
						".$no.". ".$capht[$head]."
					</td>
					<td align=center style='border:1px;width:50px;vertical-align:top;padding:5px;'>
						<img src=images/icon_plus.png class=zImgBtn title='detail'  onclick=\"detailrow('".$no."','".$totdt[$head]."')\">  
					</td>
					
			</tr></thead>";	
			$nourut=0;
			foreach($dt as $detail)
			{
				if($listdt[$head][$detail]!='')
				{
					$nourut++;
					echo"<tr class=rowcontent ".$stylehidden." id=dt".$no."".$nourut.">";
					
						echo"<td colspan=2 onclick=getdata('".$file[$head][$detail]."');clearmenu(); style='border:0px solid orange;width:200px;height:100%;vertical-align:top;padding:10px;'>
							".$no.".".$nourut.". ".$capdt[$head][$detail]."
						</td>";
					echo"</tr>";
				}
			}	
		}
		echo"</table>";
		//echo"</div>";
	echo"</td>";
	
	
	echo"<td style='border:1px solid orange;width:1000px;height:100%;vertical-align:top;padding:5px;'>";
		echo"<div id=isidata></div>";
	echo"</td>";
echo"</tr>";
echo"</table>";






echo"</div>";
CLOSE_BOX();
CLOSE_BODY_NEWBI();
?>