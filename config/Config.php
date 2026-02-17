<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('DB_HOSTNAME', 'bi3avg0a9x2dczdrsvq3-mysql.services.clever-cloud.com');
define('DB_USER', 'unayc21wzthdfmlc');
define('DB_PASSWORD', 'Tb6yhOqrEzaLREO4SqJu');
define('DB_NAME', 'bi3avg0a9x2dczdrsvq3');
define('UPLOAD_DIR', '../uploads/');
// define('DB_PASSWORD', 'GPc|Kqe&2G@v');

class Config {

    private $conn;

    public function dbConnect() {
        if ($this->conn === null) {
            $this->conn = new mysqli(DB_HOSTNAME, DB_USER, DB_PASSWORD, DB_NAME);
            if ($this->conn->connect_error) {
                die("Falha na conexão com o banco de dados: " . $this->conn->connect_error);
            }
        }
        $this->conn->set_charset("utf8mb4");
        $this->makeHeaders();
        return $this->conn;
    }

    public function upload($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0777, true)) {
            return false;
        }
        $originalName = basename($file['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $uniqueName = md5(uniqid(rand(), true)) . '.' . $extension;
        $destination = UPLOAD_DIR . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $uniqueName;
        } else {
            return false;
        }
    }

    /**
     * Exclui um arquivo do sistema de arquivos no diretório UPLOAD_DIR.
     * @param string $fileName O nome do arquivo a ser excluído (e.g., o valor do campo 'foto').
     * @return bool Retorna true se o arquivo foi excluído ou se não existia, false em caso de erro.
     */
    public function deleteFile($fileName) {
        if (empty($fileName)) {
            return true; 
        }
        
        $filePath = UPLOAD_DIR . $fileName;

        if (file_exists($filePath) && is_file($filePath)) {
            if (unlink($filePath)) {
                return true;
            } else {
                error_log("Erro ao deletar arquivo físico: " . $filePath);
                return false;
            }
        }
        
        return true;
    }

    public function makeHeaders(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Access-Control-Allow-Headers: *");
        header("Content-Type: application/json");
    }

}

?>