<?php
// Certifique-se de que os caminhos para as classes estão corretos
require_once("../classes/Parceiro.php"); // 🚨 Alterado para Parceiro.php
require_once("../config/Config.php");

class ParceiroRouter { // 🚨 Nome da classe alterado

    private $parceiro; // 🚨 Propriedade alterada
    private $config;

    public function __construct() {
        // Inicializa as dependências: a classe de modelo e a classe de configuração/upload
        $this->parceiro = new Parceiro(); // 🚨 Instância da classe Parceiro
        $this->config = new Config();
    }

    /**
     * Ponto de entrada principal para lidar com as requisições HTTP.
     * Analisa o método (GET/POST) e a ação solicitada.
     * @return string O resultado da operação em formato JSON.
     */
    public function handleRequest() {
        
        $method = $_SERVER['REQUEST_METHOD'];

        // POST: Usado para operações de Criação (create) e Atualização (update)
        if ($method === 'POST') {
            // Tenta decodificar JSON
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Se não houver JSON, usa $_POST
            if (!$data) {
                $data = $_POST;
            }
            
            // Adiciona dados de arquivos se for multipart/form-data (para upload)
            if (!empty($_FILES)) {
                $data = array_merge($data, $_FILES);
            }

            if (isset($data['action']) && method_exists($this, $data['action'])) {
                return $this->{$data['action']}($data);
            }
        }
        
        // DELETE: Pode ser usado para exclusão via DELETE
        if ($method === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'delete') {
            return $this->delete($_GET);
        }

        // GET: Usado para operações de Leitura (read, listAll, filter, listActive)
        if ($method === 'GET' && isset($_GET['action'])) {
            $action = $_GET['action'];
            if (method_exists($this, $action)) {
                return $this->$action($_GET);
            }
        }
        
        // Resposta padrão para ação inválida ou método não suportado
        return $this->response(false, "Ação inválida ou método não suportado");
    }

    // ---------------------------------------------------------
    // MÉTODOS DE AÇÃO (CRUD)
    // ---------------------------------------------------------

    /**
     * Lida com a criação de um novo parceiro (POST).
     */
    private function create($data) {
        // 1. Lida com o upload da foto (FOTO É OBRIGATÓRIA para parceiros)
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto']); 
            if (!$uploadedFileName) {
                return $this->response(false, "Erro ao fazer upload da foto. O arquivo pode ser inválido.");
            }
            $this->parceiro->foto = $uploadedFileName;
        } else {
             return $this->response(false, "Foto do parceiro é obrigatória.");
        }

        // 2. Atribui os dados do POST/JSON às propriedades
        $this->parceiro->nome = $data['nome'] ?? null;
        $this->parceiro->link = $data['link'] ?? null; // Link é opcional
        
        // 3. Validação básica de campos obrigatórios
        if (empty($this->parceiro->nome)) {
            return $this->response(false, "Nome do parceiro é obrigatório.");
        }

        // 4. Executa a criação e retorna a resposta
        if ($this->parceiro->create()) {
            return $this->response(true, "Parceiro criado com sucesso");
        }
        return $this->response(false, "Erro ao criar parceiro");
    }

    /**
     * Lida com a atualização de um parceiro existente (POST).
     */
    private function update($data) {
        // 1. Lida com o upload da foto, se houver uma nova
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto']);
            if ($uploadedFileName) {
                $this->parceiro->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao fazer upload da nova foto.");
            }
        } else {
            // Se nenhum arquivo novo foi enviado, usa o nome da foto existente (passada no $data)
            $this->parceiro->foto = $data['foto'] ?? null;
        }

        // 2. Atribui os dados do POST/JSON às propriedades
        $this->parceiro->id   = $data['id'] ?? null;
        $this->parceiro->nome = $data['nome'] ?? null;
        $this->parceiro->link = $data['link'] ?? null;

        // 3. Validação básica de campos obrigatórios
        if (empty($this->parceiro->id)) {
            return $this->response(false, "ID do parceiro não informado para atualização");
        }
        if (empty($this->parceiro->nome)) {
            return $this->response(false, "Nome do parceiro é obrigatório.");
        }
        // A foto deve existir, seja a nova ou a anterior (passada no $data['foto'])
        if (empty($this->parceiro->foto)) {
            return $this->response(false, "A foto do parceiro é obrigatória.");
        }

        // 4. Executa a atualização e retorna a resposta
        if ($this->parceiro->update()) {
            return $this->response(true, "Parceiro atualizado com sucesso");
        }
        return $this->response(false, "Erro ao atualizar parceiro");
    }

    /**
     * Lida com a exclusão de um parceiro (DELETE/GET com action=delete).
     */
    private function delete($data) {
        $this->parceiro->id = $data['id'] ?? null;

        if (!$this->parceiro->id) {
             return $this->response(false, "ID do parceiro não informado para exclusão");
        }

        if ($this->parceiro->delete()) {
            return $this->response(true, "Parceiro excluído com sucesso");
        }
        return $this->response(false, "Erro ao excluir parceiro.");
    }

    // ---------------------------------------------------------
    // MÉTODOS DE CONSULTA (READ)
    // ---------------------------------------------------------

    /**
     * Lista todos os parceiros (GET com action=listAll).
     */
    private function listAll() {
        $dados = $this->parceiro->listAll();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }
    
    /**
     * Lista parceiros ativos (Atualmente, idêntico a listAll, pois não há campo 'status').
     */
    private function listActive() {
        $dados = $this->parceiro->listActive(); 
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Lê um parceiro específico pelo ID (GET com action=read&id=X).
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }
        $dados = $this->parceiro->read($data['id']);
        
        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados,
            "message" => $dados ? "" : "Parceiro não encontrado."
        ]);
    }

    /**
     * Filtra parceiros por nome ou link (GET com action=filter&keyword=X).
     */
    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }
        $dados = $this->parceiro->filter($data['keyword']);
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    // ---------------------------------------------------------
    // FUNÇÕES AUXILIARES
    // ---------------------------------------------------------

    /**
     * Formata uma resposta padrão em JSON.
     * @param bool $success Status da operação.
     * @param string $message Mensagem de retorno.
     * @return string Resposta JSON.
     */
    private function response($success, $message) {
        return json_encode([
            "success" => $success,
            "message" => $message
        ]);
    }
}

// Executa o router
$router = new ParceiroRouter();
echo $router->handleRequest();