<?php

    require '../model/Model.php';

    class Backup {

        public $model;

        public function __construct(){
            $this->model = new Model();
        }

        public function handleBackupDB(){
            $this->model->backupDatabase();
            return json_encode("TABELAS DESCARREGADAS COM SUCESSO");
        }

    }

    $Backup = new Backup();
    echo $Backup->handleBackupDB();
    

?>