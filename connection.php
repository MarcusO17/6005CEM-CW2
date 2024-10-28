<?php
    $caPath = realpath(dirname(__FILE__) . getenv("certpath"));
    $database  = mysqli_init();
    $database->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
    $database->ssl_set(NULL, NULL, $caPath, NULL, NULL);

    $database->real_connect(getenv("host")
                        ,getenv("username")
                        ,getenv("password")
                        ,getenv("dbname")
                        ,getenv("port"));

  

    if ($database->connect_error){
        die("Connection failed:  ".$database->connect_error);
    }

?>