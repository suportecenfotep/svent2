<?php

    require("./Config.php");

    $Config = new Config();

    echo $Config->dbConnect();

?>