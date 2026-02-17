<?php
// Certifique-se de que os caminhos para as classes estão corretos
require_once("../classes/Slideshow.php"); // 🚨 Alterado para Slideshow.php
require_once("../config/Config.php");

class SlideshowRouter { // 🚨 Nome da classe alterado

    private $slideshow; // 🚨 Propriedade alterada
    private $config;

    public function __construct() {
        // Inicializa as dependências: a classe de modelo e a classe de configuração/upload
        $this->slideshow = new Slideshow(); // 🚨 Instância da classe Slideshow
        $this->config = new Config();
    }

    /**
     * Ponto de entrada principal para lidar com as requisições HTTP.
     * Analisa o método (GET/POST) e a ação solicitada.
     * @return string O resultado da operação em formato JSON.
     */
    public function handleRequest() {
        // Define o cabeçalho para retornar JSON
        header('Content-Type: application/json');
        
        $method = $_SERVER['REQUEST_METHOD'];

        // POST: Usado para operações de Criação (create) e Atualização (update)
        if ($method === 'POST') {
            // Tenta decodificar JSON (para requisições com 'Content-Type: application/json')
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Se não houver JSON ou se for um formulário multipart/form-data, usa $_POST
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
        
        // DELETE: Pode ser usado para exclusão via DELETE (melhor prática RESTful)
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
     * Lida com a criação de um novo slide (POST).
     */
    private function create($data) {
        // 1. **Obrigatório:** Lida com o upload da foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto']); 
            if (!$uploadedFileName) {
                return $this->response(false, "Erro ao fazer upload da foto. Verifique as permissões.");
            }
            $this->slideshow->foto = $uploadedFileName;
        } else {
            return $this->response(false, "Nenhuma foto de slideshow válida foi enviada.");
        }

        // 2. Atribui o status
        $this->slideshow->status = $data['status'] ?? 0; // Padrão: 0 (Inativo)

        // 3. Executa a criação e retorna a resposta
        if ($this->slideshow->create()) {
            return $this->response(true, "Slide criado com sucesso");
        }
        return $this->response(false, "Erro ao criar slide");
    }

    /**
     * Lida com a atualização de um slide existente (POST).
     */
    private function update($data) {
        // 1. Lida com o upload da foto, se houver uma nova
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto']);
            if ($uploadedFileName) {
                $this->slideshow->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao fazer upload da nova foto.");
            }
        } else {
            // Se nenhum arquivo novo foi enviado, usa o nome da foto existente (passada no $data)
            $this->slideshow->foto = $data['foto'] ?? null;
        }

        // 2. Atribui o ID e o status
        $this->slideshow->id     = $data['id'] ?? null;
        $this->slideshow->status = $data['status'] ?? 0;

        // 3. Verifica se o ID é válido antes de atualizar
        if (!$this->slideshow->id) {
            return $this->response(false, "ID do slide não informado para atualização");
        }
        
        // 4. Verifica se o campo foto está preenchido (se não houver upload, o campo existente é obrigatório)
         if (!$this->slideshow->foto) {
             return $this->response(false, "O nome da foto é obrigatório para atualização.");
         }

        // 5. Executa a atualização e retorna a resposta
        if ($this->slideshow->update()) {
            return $this->response(true, "Slide atualizado com sucesso");
        }
        return $this->response(false, "Erro ao atualizar slide");
    }

    /**
     * Lida com a exclusão de um slide (DELETE/GET com action=delete).
     */
    private function delete($data) {
        $this->slideshow->id = $data['id'] ?? null;

        if (!$this->slideshow->id) {
             return $this->response(false, "ID do slide não informado para exclusão");
        }

        if ($this->slideshow->delete()) {
            return $this->response(true, "Slide excluído com sucesso");
        }
        return $this->response(false, "Erro ao excluir slide");
    }

    // ---------------------------------------------------------
    // MÉTODOS DE CONSULTA (READ)
    // ---------------------------------------------------------

    /**
     * Lista todos os slides (GET com action=listAll).
     */
    private function listAll() {
        $dados = $this->slideshow->listAll();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }
    
    /**
     * Lista apenas slides ativos (GET com action=listActive).
     */
    private function listActive() {
        $dados = $this->slideshow->listActive();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Lê um slide específico pelo ID (GET com action=read&id=X).
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }
        $dados = $this->slideshow->read($data['id']);
        
        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados,
            "message" => $dados ? "" : "Slide não encontrado."
        ]);
    }

    /**
     * Filtra slides por nome de arquivo (GET com action=filter&keyword=X).
     */
    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }
        $dados = $this->slideshow->filter($data['keyword']);
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
$router = new SlideshowRouter();
echo $router->handleRequest();