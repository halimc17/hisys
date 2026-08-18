<?

include('../config/connection.php');
include('../lib/nangkoelib.php');
require_once ('../jpgraph/jpgraph.php');
require_once ('../jpgraph/jpgraph_line.php');


$pt=$_GET['pt'];



// Some (random) data
if($pt=='CKS')
{
	$ydata = array(1,13,5,2,12,7,2,4,11,8);	
}
else
{
	$ydata = array(11,3,8,12,5,1,9,13,5,7);	
}

 
// Size of the overall graph
$width=580;
$height=235;
 
// Create the graph and set a scale.
// These two calls are always required
$graph = new Graph($width,$height);
$graph->SetScale('intlin');
 
// Create the linear plot
$lineplot=new LinePlot($ydata);
 
// Add the plot to the graph
$graph->Add($lineplot);
 
// Display the graph
$graph->StrokeCSIM();



?>