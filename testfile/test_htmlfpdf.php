<?php
require('../lib/fpdf.php');
require('../lib/htmlparser.inc');
require('../lib/htmltofpdf.php');
$htmlTable  = '';
$htmlTable  .= '<img src="https://image.freepik.com/free-icon/apple-logo_318-40184.jpg" width="20">';
$htmlTable  .= '<br><b>APPLE KEGIGIT, PT</b><br><hr><br>Start of the HTML table.<br>';
$htmlTable .='<table>
<thead>
<tr>
<th width="10" rowspan="2" align="center" background="215,235,255" color="0,0,0">no</th>
<th width="60" align="center">ex</th>
<th width="50" rowspan="2" align="center">Gender</th>
<th width="50" align="center">Location</th>
</tr>
<tr>
<th width="50">Name</th>
<th width="10">Thn Tnm</th>
<th width="25" align="center">HO</th>
<th width="25" align="center">SITE</th>
</tr>
</thead>
<tbody>';
for($i=1; $i<=5; $i++){
$htmlTable .='
			<tr>
			<td width="10">'.$i.'</td>
			<td width="50">Azeem</td>
			<td width="10">2000</td>
			<td width="50">Male</td>
			<td width="50">Pakistan</td>
			</tr>';
}
$htmlTable .='
</tbody>
</table>';
$htmlTable  .= '<br>End of the table.';

$pdf=new PDF_HTML_Table();
$pdf->AddPage('P');
$pdf->SetFont('Arial','',10);



$pdf->WriteHTML($htmlTable);
$txt = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';

$pdf->Output();

?>