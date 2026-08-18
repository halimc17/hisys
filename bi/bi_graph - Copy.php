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






echo"<div id=head  style='display:block'>";
OPEN_BOX('','BI GRAPH');

$str="select * from ".$dbname.".menugraph where tipe=0 order by caption asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

while($bar=$res->fetch())
{
	$menuht[$bar['id']]=$bar['id'];
	$capht[$bar['id']]=$bar['caption'];
	$icon[$bar['id']]=$bar['icon'];
}

echo "<div style='height:600px' style='display:block'>";
$col=array("1"=>"orange","2"=>"teal","3"=>"green","4"=>"pink","5"=>"wisteria");
foreach($menuht as $head)
{
	$no+=1;

	if($icon[$head]!='')
	{
		$logo="<img src=".$icon[$head]." class=iconmenu>";
	}
	else
	{
		$logo="<img src=images/owl.png class=iconmenu>";
	}
	
	$div='thumbnail tile tile-wide tile-'.$col[$no];
	echo"
	<div class='col-sm-1 col-md-3'>
		<div class='".$div."' onclick=getmenu(".$head.")>
			".$logo."
			<br><br>
				<h2 align=center><b>".$capht[$head]."</b></h2>
		</div>
	</div>
	";
}
echo"</div>";
CLOSE_BOX();
echo"</div>";


echo"<div id=foot  style='display:none'>";
OPEN_BOX('','BI GRAPH  <img style=cursor:pointer class=ressicon onclick=getoption() src=images/menuBtn.png class=iconmenu>');
echo "<div style='height:600px' id=menudt>";


echo"</div>";
CLOSE_BOX();
echo"</div>";



CLOSE_BODY_NEWBI();
?>