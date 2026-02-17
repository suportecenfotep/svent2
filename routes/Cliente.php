<?php
require_once("../classes/Cliente.php"); 
require_once("../config/Config.php");

class ClienteRouter {

    private $cliente;
    private $config;

    public function __construct() {
        $this->cliente = new Cliente();
        $this->config = new Config();
    }

    public function handleRequest() {
        header('Content-Type: application/json');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $data = [];

        // POST
        if ($method === 'POST') {
            $json_data = json_decode(file_get_contents("php://input"), true);
            $data = $json_data ?: $_POST;

            if (isset($data['action']) && method_exists($this, $data['action'])) {
                return $this->{$data['action']}($data);
            }
        }

        // GET
        if ($method === 'GET' && isset($_GET['action'])) {
            $action = $_GET['action'];
            return $this->$action($_GET);
        }

        return $this->response(false, "Ação inválida ou método não suportado");
    }

    // ---------------------------------------------------------
    // CRUD
    // ---------------------------------------------------------

    private function create($data) {

        // Upload de foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto']);
            if ($uploadedFileName) {
                $this->cliente->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao fazer upload da foto.");
            }
        } else {
            $this->cliente->foto = $data['foto'] ?? null;
        }

        $this->cliente->nome     = $data['nome'] ?? null;
        $this->cliente->telefone = $data['telefone'] ?? null;
        $this->cliente->email    = $data['email'] ?? null;
        $this->cliente->senha    = $data['senha'] ?? null;
        $this->cliente->endereco = $data['endereco'] ?? null;

        // 🔹 Apenas País
        $this->cliente->pais_id = $data['pais_id'] ?? null;

        $this->cliente->status = $data['status'] ?? 1;
        $this->cliente->online = $data['online'] ?? 0;

        if (empty($this->cliente->nome) || empty($this->cliente->email) || empty($this->cliente->senha)) {
            return $this->response(false, "Campos obrigatórios (nome, email, senha) não podem estar vazios.");
        }

        if ($this->cliente->create()) {
            return $this->response(true, "Cliente criado com sucesso. Faça login para continuar.");
        }

        return $this->response(false, "Erro ao criar cliente. Email pode já existir.");
    }

    private function update($data) {

        if (!isset($data['id'])) {
            return $this->response(false, "ID do cliente não informado.");
        }

        $this->cliente->id       = $data['id'];
        $this->cliente->nome     = $data['nome'] ?? null;
        $this->cliente->telefone = $data['telefone'] ?? null;
        $this->cliente->email    = $data['email'] ?? null;
        $this->cliente->endereco = $data['endereco'] ?? null;
        $this->cliente->status   = $data['status'] ?? 1;
        $this->cliente->online   = $data['online'] ?? 0;

        // 🔹 Apenas País
        $this->cliente->pais_id = $data['pais_id'] ?? null;

        if ($this->cliente->update()) {
            return $this->response(true, "Cliente atualizado com sucesso");
        }

        return $this->response(false, "Erro ao atualizar cliente");
    }

    private function delete($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }

        $this->cliente->id = $data['id'];

        if ($this->cliente->delete()) {
            return $this->response(true, "Cliente excluído com sucesso");
        }

        return $this->response(false, "Erro ao excluir cliente");
    }

    private function listAll() {
        return json_encode([
            "success" => true,
            "data" => $this->cliente->listAll()
        ]);
    }

    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }

        $dados = $this->cliente->read($data['id']);

        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados
        ]);
    }

    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }

        return json_encode([
            "success" => true,
            "data" => $this->cliente->filter($data['keyword'])
        ]);
    }

    // ---------------------------------------------------------
    // AUTENTICAÇÃO
    // ---------------------------------------------------------

    private function login($data) {

        $this->cliente->email = $data['email'] ?? null;
        $this->cliente->senha = $data['senha'] ?? null;

        if (empty($this->cliente->email) || empty($this->cliente->senha)) {
            return $this->response(false, "Email e senha são obrigatórios.");
        }

        $dados = $this->cliente->login();

        if ($dados) {
            return json_encode([
                "success" => true,
                "message" => "Login efetuado com sucesso",
                "data" => $dados
            ]);
        }

        return $this->response(false, "Email ou senha inválidos ou conta inativa.");
    }

    private function logout($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }

        if ($this->cliente->logout($data['id'])) {
            return $this->response(true, "Logout efetuado com sucesso");
        }

        return $this->response(false, "Erro ao efetuar logout");
    }

    private function updatePassword($data) {
        if (!isset($data['id'], $data['nova_senha'])) {
            return $this->response(false, "ID e nova senha são obrigatórios.");
        }

        if ($this->cliente->updatePassword($data['id'], $data['nova_senha'])) {
            return $this->response(true, "Senha atualizada com sucesso.");
        }

        return $this->response(false, "Erro ao atualizar senha.");
    }

    // ---------------------------------------------------------
    // UTILITÁRIO
    // ---------------------------------------------------------

    private function response($success, $message, $data = null) {
        return json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data
        ]);
    }
}

$router = new ClienteRouter();
echo $router->handleRequest();