<?php

require_once 'db.php';

if(isset($_GET['pr_id'])){

}else{
    $sql = "SELECT * FROM purchase_order";

    $result = $db->query($sql);
    $po = [];

    while($row = $result->fetch_assoc()){
        $pos[] = $row;
    }



}

?>