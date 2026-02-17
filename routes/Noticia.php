<?php
// Certifique-se de que os caminhos para as classes estão corretos
require_once("../classes/Noticia.php"); 
require_once("../config/Config.php");

class NoticiaRouter {

    private $noticia;
    private $config;

    public function __construct() {
        $this->noticia = new Noticia();
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
                // Chama o método correspondente à ação (ex: $this->create($data))
                return $this->{$data['action']}($data);
            }
        }

        // GET: Usado para operações de Leitura (read, listAll, filter)
        if ($method === 'GET' && isset($_GET['action'])) {
            $action = $_GET['action'];
            if (method_exists($this, $action)) {
                // Chama o método correspondente à ação (ex: $this->read($_GET))
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
     * Lida com a criação de uma nova notícia (POST).
     */
    private function create($data) {
        // 1. Lida com o upload da foto, se houver
        if (isset($_FILES['foto'])) {
            $uploadedFileName = $this->config->upload($_FILES['foto']); // Assumindo que Config::upload é o método de upload
            if ($uploadedFileName) {
                $this->noticia->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao fazer upload da foto.");
            }
        } else {
            // Se nenhum arquivo foi enviado, define como null
            $this->noticia->foto = null;
        }

        // 2. Atribui os dados do POST/JSON às propriedades da Noticia
        // Usando o operador de coalescência null (??) para fornecer valores padrão
        $this->noticia->user_id     = $data['user_id'] ?? null;
        $this->noticia->titulo      = $data['titulo'] ?? null;
        $this->noticia->descricao   = $data['descricao'] ?? null;
        $this->noticia->status      = $data['status'] ?? 1; // Valor padrão 1 (ativo)

        // 3. Executa a criação e retorna a resposta
        if ($this->noticia->create()) {
            return $this->response(true, "Notícia criada com sucesso");
        }
        return $this->response(false, "Erro ao criar notícia");
    }

    /**
     * Lida com a atualização de uma notícia existente (POST).
     */
    private function update($data) {
        // 1. Lida com o upload da foto, se houver
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadedFileName = $this->config->upload($_FILES['foto']);
            if ($uploadedFileName) {
                $this->noticia->foto = $uploadedFileName;
            } else {
                return $this->response(false, "Erro ao fazer upload da nova foto.");
            }
        } else {
            // Se nenhum arquivo novo foi enviado, mantém a foto existente (passada no $data)
            $this->noticia->foto = $data['foto'] ?? null;
        }

        // 2. Atribui os dados do POST/JSON às propriedades da Noticia
        $this->noticia->id          = $data['id'] ?? null;
        $this->noticia->user_id     = $data['user_id'] ?? null;
        $this->noticia->titulo      = $data['titulo'] ?? null;
        $this->noticia->descricao   = $data['descricao'] ?? null;
        $this->noticia->status      = $data['status'] ?? 1;

        // 3. Verifica se o ID é válido antes de atualizar
        if (!$this->noticia->id) {
            return $this->response(false, "ID da notícia não informado para atualização");
        }

        // 4. Executa a atualização e retorna a resposta
        if ($this->noticia->update()) {
            return $this->response(true, "Notícia atualizada com sucesso");
        }
        return $this->response(false, "Erro ao atualizar notícia");
    }

    /**
     * Lida com a exclusão de uma notícia (POST/GET com action=delete).
     */
    private function delete($data) {
        $this->noticia->id = $data['id'] ?? null;

        if (!$this->noticia->id) {
             return $this->response(false, "ID da notícia não informado para exclusão");
        }

        if ($this->noticia->delete()) {
            return $this->response(true, "Notícia excluída com sucesso");
        }
        return $this->response(false, "Erro ao excluir notícia");
    }

    // ---------------------------------------------------------
    // MÉTODOS DE CONSULTA (READ)
    // ---------------------------------------------------------

    /**
     * Lista todas as notícias (GET com action=listAll).
     */
    private function listAll() {
        $dados = $this->noticia->listAll();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Lista notícias recentes por limite (GET com action=listRecentsByLimit&limit=X).
     */
    private function listRecentsByLimit($data) {
        $limit = $data['limit'] ?? 5;
        $dados = $this->noticia->listRecentsByLimit((int)$limit);
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Lê uma notícia específica pelo ID (GET com action=read&id=X).
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID não informado");
        }
        $dados = $this->noticia->read($data['id']);
        
        // Retorna sucesso=false se os dados forem nulos (notícia não encontrada)
        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados,
            "message" => $dados ? "" : "Notícia não encontrada."
        ]);
    }

    /**
     * Filtra notícias por palavra-chave (GET com action=filter&keyword=X).
     */
    private function filter($data) {
        if (!isset($data['keyword'])) {
            return $this->response(false, "Palavra-chave não informada");
        }
        $dados = $this->noticia->filter($data['keyword']);
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
        header('Content-Type: application/json');
        return json_encode([
            "success" => $success,
            "message" => $message
        ]);
    }
}

// Executa o router
$router = new NoticiaRouter();
echo $router->handleRequest();