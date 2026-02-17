<?php
require_once("../classes/Faq.php");
require_once("../config/Config.php");

class FaqRouter {

    private $faq;
    private $config;

    public function __construct() {
        $this->faq = new Faq();
        $this->config = new Config();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) $data = $_POST;

            if (!empty($_FILES)) {
                $data = array_merge($data, $_FILES);
            }

            if (isset($data['action']) && method_exists($this, $data['action'])) {
                return $this->{$data['action']}($data);
            }
        }

        if ($method === 'GET' && isset($_GET['action'])) {
            $action = $_GET['action'];
            if (method_exists($this, $action)) {
                return $this->$action($_GET);
            }
        }

        return $this->response(false, "Ação inválida ou método não suportado");
    }

    // ----------------------------- CRUD -----------------------------

    private function create($data) {
        $this->faq->pergunta = $data['pergunta'] ?? null;
        $this->faq->resposta = $data['resposta'] ?? null;
        $this->faq->categoria = $data['categoria'] ?? null;
        $this->faq->status = $data['status'] ?? 1;

        if ($this->faq->create()) {
            return $this->response(true, "FAQ criada com sucesso");
        }
        return $this->response(false, "Erro ao criar FAQ");
    }

    private function update($data) {
        $this->faq->id = $data['id'] ?? null;
        $this->faq->pergunta = $data['pergunta'] ?? null;
        $this->faq->resposta = $data['resposta'] ?? null;
        $this->faq->categoria = $data['categoria'] ?? null;
        $this->faq->status = $data['status'] ?? 1;

        if (!$this->faq->id) {
            return $this->response(false, "ID da FAQ não informado");
        }

        if ($this->faq->update()) {
            return $this->response(true, "FAQ atualizada com sucesso");
        }
        return $this->response(false, "Erro ao atualizar FAQ");
    }

    private function delete($data) {
        $this->faq->id = $data['id'] ?? null;
        if (!$this->faq->id) {
            return $this->response(false, "ID da FAQ não informado para exclusão");
        }

        if ($this->faq->delete()) {
            return $this->response(true, "FAQ excluída com sucesso");
        }
        return $this->response(false, "Erro ao excluir FAQ");
    }

    // ----------------------------- LEITURA -----------------------------

    private function listAll() {
        $dados = $this->faq->listAll();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }

        $dados = $this->faq->read($data['id']);
        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados,
            "message" => $dados ? "" : "FAQ não encontrada."
        ]);
    }

    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }

        $dados = $this->faq->filter($data['keyword']);
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    // ----------------------------- UTIL -----------------------------

    private function response($success, $message) {
        return json_encode([
            "success" => $success,
            "message" => $message
        ]);
    }
}

$router = new FaqRouter();
echo $router->handleRequest();
