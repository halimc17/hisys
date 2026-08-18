<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');

$_SESSION['pjd']['menu'] = 'sdm_pertanggungjawabanpjdstaffx';
include('sdm_pjdx.php');
echo close_body();
?>