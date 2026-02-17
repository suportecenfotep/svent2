<?php
// Inclui as classes necessárias
require_once("../classes/User.php");
require_once("../config/Config.php");

/**
 * Classe responsável por rotear as requisições HTTP
 * para os métodos da classe User (CRUD e Autenticação).
 */
class UserRouter {

    private $user;
    private $config;

    /**
     * Construtor: Inicializa as dependências.
     */
    public function __construct() {
        $this->user = new User();
        $this->config = new Config();
    }

    /**
     * Roteia a requisição HTTP (GET ou POST) para o método de ação apropriado.
     */
    public function handleRequest() {
        // Define o cabeçalho de resposta como JSON
        header('Content-Type: application/json');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $data = [];

        // 1. Processa dados para requisições POST
        if ($method === 'POST') {
            // Tenta obter dados JSON (para requisições com 'Content-Type: application/json')
            $json_data = json_decode(file_get_contents("php://input"), true);
            if ($json_data) {
                $data = $json_data;
            } else {
                // Caso contrário, usa $_POST (para form-data)
                $data = $_POST;
            }
            
            // Verifica a ação e executa o método
            if (isset($data['action']) && method_exists($this, $data['action'])) {
                return $this->{$data['action']}($data);
            }
        }

        // 2. Processa dados para requisições GET
        if ($method === 'GET' && isset($_GET['action'])) {
            $action = $_GET['action'];
            $data = $_GET; // Os dados para GET vêm de $_GET
            
            if (method_exists($this, $action)) {
                return $this->$action($data);
            }
        }

        // 3. Resposta para requisição inválida
        return $this->response(false, "Ação inválida ou método não suportado");
    }

    //---------------------------------------------------------
    //  MÉTODOS DE ROTEAMENTO (ACTIONS)
    //---------------------------------------------------------

    /**
     * Cria um novo usuário.
     */
    private function create($data) {
        // Lógica de Upload de foto: Prioriza $_FILES, se não houver, usa nulo.
        if (isset($_FILES['foto'])) {
            $uploadedFileName = $this->config->upload($_FILES['foto']);
            if ($uploadedFileName) {
                $this->user->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao fazer upload da foto.");
            }
        } else {
            $this->user->foto = null;
        }

        // Atribui as demais propriedades do usuário, usando null ou padrão se não existirem
        $this->user->nome          = $data['nome'] ?? null;
        $this->user->identificacao = $data['identificacao'] ?? null;
        $this->user->telefone      = $data['telefone'] ?? null;
        $this->user->email         = $data['email'] ?? null;
        $this->user->senha         = $data['senha'] ?? null;
        // Assume 'Administrador' é o padrão correto conforme a criação da tabela
        $this->user->nivel         = $data['nivel'] ?? "Administrador"; 
        $this->user->status        = $data['status'] ?? 1;
        $this->user->online        = $data['online'] ?? 0;

        // Validação básica para campos obrigatórios
        if (empty($this->user->nome) || empty($this->user->identificacao) || empty($this->user->email) || empty($this->user->senha)) {
            return $this->response(false, "Campos obrigatórios (nome, identificacao, email, senha) não podem estar vazios.");
        }

        if ($this->user->create()) {
            return $this->response(true, "Usuário criado com sucesso");
        }
        return $this->response(false, "Erro ao criar usuário. Identificação ou Email podem já existir.");
    }

    /**
     * Atualiza um usuário existente.
     */
    private function update($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID do usuário não informado para atualização.");
        }

        // Lógica de Upload de foto: Trata foto existente ou nova.
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto']);
            if ($uploadedFileName) {
                $this->user->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao fazer upload da nova foto.");
            }
        } else {
             // Mantém a foto existente se não houver novo upload
            $this->user->foto = $data['foto'] ?? null; 
        }

        // Atribui as propriedades
        $this->user->id            = $data['id'];
        $this->user->nome          = $data['nome'] ?? null;
        $this->user->identificacao = $data['identificacao'] ?? null;
        $this->user->telefone      = $data['telefone'] ?? null;
        $this->user->email         = $data['email'] ?? null;
        $this->user->senha         = $data['senha'] ?? null; // A senha é opcional na atualização
        $this->user->nivel         = $data['nivel'] ?? "Administrador";
        $this->user->status        = $data['status'] ?? 1;
        $this->user->online        = $data['online'] ?? 0;

        if ($this->user->update()) {
            return $this->response(true, "Usuário atualizado com sucesso");
        }
        return $this->response(false, "Erro ao atualizar usuário");
    }

    /**
     * Deleta um usuário.
     */
    private function delete($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID do usuário não informado para exclusão.");
        }
        
        $this->user->id = $data['id'];

        if ($this->user->delete()) {
            return $this->response(true, "Usuário excluído com sucesso");
        }
        return $this->response(false, "Erro ao excluir usuário");
    }

    /**
     * Lista todos os usuários.
     */
    private function listAll($data = []) {
        $dados = $this->user->listAll();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Lê um único usuário pelo ID.
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }
        $dados = $this->user->read($data['id']);
        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados
        ]);
    }

    /**
     * Filtra usuários por palavra-chave.
     */
    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }
        $dados = $this->user->filter($data['keyword']);
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Tenta fazer login do usuário.
     */
    private function login($data) {
        $this->user->email = $data['email'] ?? null;
        $this->user->senha = $data['senha'] ?? null;

        if (empty($this->user->email) || empty($this->user->senha)) {
            return $this->response(false, "Email e senha são obrigatórios para login.");
        }

        $dados = $this->user->login();

        if ($dados) {
            return json_encode([
                "success" => true,
                "message" => "Login efetuado com sucesso",
                "data" => $dados
            ]);
        }
        return $this->response(false, "Email ou senha inválidos ou usuário inativo");
    }

    /**
     * Efetua o logout do usuário.
     */
    private function logout($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID do usuário não informado");
        }

        if ($this->user->logout($data['id'])) {
            return $this->response(true, "Logout efetuado com sucesso");
        }
        return $this->response(false, "Erro ao efetuar logout");
    }

    //---------------------------------------------------------
    //  MÉTODO UTILITÁRIO
    //---------------------------------------------------------

    /**
     * Retorna uma resposta padronizada em formato JSON.
     */
    private function response($success, $message, $data = null) {
        return json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data
        ]);
    }
}

// ---------------------------------------------------------

// Ponto de entrada do script: Executa o router
$router = new UserRouter();
echo $router->handleRequest();