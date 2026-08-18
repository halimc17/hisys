<?
try{
    $owlPDO = new PDO('mysql:host='.$db->hostname.';dbname='.$db->database, $db->username, $db->password, array(PDO::ATTR_PERSISTENT => false));
    $owlPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e) {
    throw new RuntimeException('Gagal, could not connect');
    //throw new RuntimeException('Error!: '.$e->getMessage());
    die();
}
?>