<?php
require_once("../classes/Produto.php");
require_once("../config/Config.php");

/**
 * Router responsável por tratar requisições relacionadas a Produtos.
 */
class ProdutoRouter {

    private $produto;
    private $config;

    public function __construct() {
        $this->produto = new Produto();
        $this->config = new Config();
    }

    public function handleRequest() {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];
        $data = [];

        if ($method === 'POST' || $method === 'PUT') {
            $data = $_POST;
        } elseif ($method === 'GET' || $method === 'DELETE') {
            $data = $_GET;
        }

        $action = isset($data['action']) ? $data['action'] : null;
        unset($data['action']);

        if ($action && method_exists($this, $action)) {
            return $this->{$action}($data);
        }

        return $this->response(false, "Ação inválida ou não suportada");
    }

    // ===================================================
    // MÉTODOS CRUD
    // ===================================================

    private function create($data) {
        $uploadedFileName = null;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto'], 'produtos');
            if (!$uploadedFileName) {
                return $this->response(false, "Erro ao fazer upload da foto.");
            }
        }

        $this->produto->foto = $uploadedFileName;
        $this->produto->nome = $data['nome'] ?? null;
        $this->produto->preco = floatval($data['preco'] ?? 0);
        $this->produto->descricao = $data['descricao'] ?? null;
        $this->produto->user_id = $data['user_id'] ?? null;
        $this->produto->categoria_id = $data['categoria_id'] ?? null;
        $this->produto->subcategoria_id = $data['subcategoria_id'] ?? null;
        $this->produto->status = $data['status'] ?? 1;

        if (empty($this->produto->nome) || empty($this->produto->preco) || empty($this->produto->categoria_id)) {
            return $this->response(false, "Campos obrigatórios: nome, preço e categoria.");
        }

        if ($this->produto->create()) {
            return $this->response(true, "Produto criado com sucesso");
        }

        return $this->response(false, "Erro ao criar produto.");
    }

    private function update($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID do produto não informado.");
        }

        $this->produto->id = $data['id'];

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto'], 'produtos');
            if ($uploadedFileName) {
                $this->produto->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao atualizar a foto.");
            }
        } else {
            $this->produto->foto = $data['foto'] ?? null;
        }

        $this->produto->nome = $data['nome'] ?? null;
        $this->produto->preco = floatval($data['preco'] ?? 0);
        $this->produto->descricao = $data['descricao'] ?? null;
        $this->produto->user_id = $data['user_id'] ?? null;
        $this->produto->categoria_id = $data['categoria_id'] ?? null;
        $this->produto->subcategoria_id = $data['subcategoria_id'] ?? null;
        $this->produto->status = $data['status'] ?? 1;

        if (empty($this->produto->nome) || empty($this->produto->preco)) {
            return $this->response(false, "Campos obrigatórios: nome e preço.");
        }

        if ($this->produto->update()) {
            return $this->response(true, "Produto atualizado com sucesso");
        }

        return $this->response(false, "Erro ao atualizar produto.");
    }

    private function delete($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID do produto não informado.");
        }

        $this->produto->id = $data['id'];
        if ($this->produto->delete()) {
            return $this->response(true, "Produto excluído com sucesso");
        }

        return $this->response(false, "Erro ao excluir produto.");
    }

    // ===================================================
    // LISTAGENS E FILTROS
    // ===================================================

    private function listAll() {
        $dados = $this->produto->listAll();
        return $this->response(true, "Lista completa de produtos", $dados);
    }

    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }

        $dados = $this->produto->read($data['id']);
        return $this->response($dados ? true : false, $dados ? "Produto encontrado" : "Produto não encontrado", $dados);
    }

    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }

        $dados = $this->produto->filter($data['keyword']);
        return $this->response(true, "Filtro aplicado", $dados);
    }

    private function filterByLimit($data) {
        $search = isset($data['search']) ? trim($data['search']) : '';
        $limit = isset($data['limit']) ? intval($data['limit']) : 10;
        $offset = isset($data['offset']) ? intval($data['offset']) : 0;
        $categoria_id = isset($data['categoria_id']) ? intval($data['categoria_id']) : null;

        $dados = $this->produto->filterByLimit($search, $limit, $offset, $categoria_id);

        return $this->response(true, "Produtos filtrados carregados com sucesso.", $dados);
    }


    private function listBySubcategoria($data) {
        if (!isset($data['subcategoria_id'])) {
            return $this->response(false, "Subcategoria não informada");
        }

        $dados = $this->produto->listBySubcategoria($data['subcategoria_id']);
        return $this->response(true, "Produtos filtrados pela subcategoria", $dados);
    }

    /**
     * Lista produtos com limit e offset (para paginação / "ver mais").
     * Espera query string: ?action=listByLimit&limit=10&offset=20
     */
    private function listByLimit($data) {
        $limit = isset($data['limit']) ? intval($data['limit']) : 10;
        $offset = isset($data['offset']) ? intval($data['offset']) : 0;

        $dados = $this->produto->listByLimit($limit, $offset);

        return $this->response(
            true,
            "Produtos carregados (limit={$limit} offset={$offset}).",
            $dados
        );
    }

    private function search($data) {
        if (!isset($data['keyword']) || trim($data['keyword']) === '') {
            return $this->response(false, "Palavra-chave não informada.");
        }

        $keyword = trim($data['keyword']);
        $limit = isset($data['limit']) ? intval($data['limit']) : 50;

        $dados = $this->produto->search($keyword, $limit);

        if (empty($dados)) {
            return $this->response(false, "Nenhum produto encontrado para '{$keyword}'.");
        }

        return $this->response(true, "Resultados da pesquisa carregados com sucesso.", $dados);
    }


    // ===================================================
    // MÉTODO UTILITÁRIO
    // ===================================================
    private function response($success, $message, $data = null) {
        return json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data
        ]);
    }
}

$router = new ProdutoRouter();
echo $router->handleRequest();
