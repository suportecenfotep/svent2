<?php
// Inclui as classes necessárias
require_once("../classes/FotosProduto.php"); // Classe FotosProduto (que você acabou de criar)
require_once("../config/Config.php");

/**
 * Classe responsável por rotear as requisições HTTP
 * para os métodos da classe FotosProduto (CRUD e listByProduto).
 */
class FotosProdutoRouter {

    private $fotosProduto; // Instância da classe FotosProduto
    private $config;       // Instância da classe Config para upload/delete de arquivos

    /**
     * Construtor: Inicializa as dependências.
     */
    public function __construct() {
        // Assume que a classe FotosProduto está em ../classes/FotosProduto.php
        $this->fotosProduto = new FotosProduto(); 
        $this->config = new Config();
    }

    /**
     * Roteia a requisição HTTP (GET, POST, PUT ou DELETE) para o método de ação apropriado.
     */
    public function handleRequest() {
        // Define o cabeçalho de resposta como JSON
        header('Content-Type: application/json');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $data = [];
        $action = null; 

        // 1. Coleta os dados baseados no método HTTP
        if ($method === 'POST' || $method === 'PUT') {
            // Para CREATE (POST com upload) ou UPDATE (PUT/POST com dados)
            $data = $_POST;
        } elseif ($method === 'GET' || $method === 'DELETE') {
            // Para READ/LIST/DELETE (via query string)
            $data = $_GET; 
        }

        // 2. Extrai e valida a ação
        if (isset($data['action']) && is_string($data['action'])) {
            $action = $data['action'];
            unset($data['action']); 
        }

        // 3. Executa a ação se ela for válida
        if ($action && method_exists($this, $action)) {
            return $this->{$action}($data);
        }
        
        // 4. Resposta para requisição inválida ou sem ação suportada
        return $this->response(false, "Ação inválida ou método não suportado");
    }

    //---------------------------------------------------------
    //  MÉTODOS DE ROTEAMENTO (ACTIONS)
    //---------------------------------------------------------

    /**
     * Cria uma nova foto para um produto. Suporta upload de arquivo.
     */
    private function create($data) {
        if (!isset($data['produto_id'])) {
            return $this->response(false, "ID do produto é obrigatório para adicionar foto.");
        }
        
        // 1. Lógica de Upload de foto
        $uploadedFileName = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            // Usa 'galeria' como subdiretório para fotos adicionais
            $uploadedFileName = $this->config->upload($_FILES['foto'], 'galeria'); 
            if (!$uploadedFileName) {
                return $this->response(false, "Erro ao fazer upload da foto.");
            }
        } else {
            return $this->response(false, "Nenhum arquivo de foto válido enviado.");
        }

        // 2. Atribui as propriedades
        $this->fotosProduto->produto_id = $data['produto_id'];
        $this->fotosProduto->foto       = $uploadedFileName;
        $this->fotosProduto->descricao  = $data['descricao'] ?? null;

        // 3. Executa a criação
        if ($this->fotosProduto->create()) {
            // Retorna o nome do arquivo para confirmação no frontend
            return $this->response(true, "Foto adicionada com sucesso.", ['foto' => $uploadedFileName]); 
        }
        
        // Se falhar no banco, tenta reverter o upload do arquivo
        if ($uploadedFileName) {
             $this->config->deleteFile($uploadedFileName, 'galeria');
        }
        return $this->response(false, "Erro ao salvar o registro da foto no banco.");
    }

    /**
     * Lê uma única foto pelo ID.
     */
    private function read($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da foto não informado.");
        }
        $dados = $this->fotosProduto->read($data['id']);
        return json_encode([
            "success" => $dados ? true : false,
            "data" => $dados,
            "message" => $dados ? "Foto encontrada." : "Foto não encontrada."
        ]);
    }

    /**
     * Atualiza a descrição de uma foto.
     * NOTA: A atualização do arquivo da foto em si é menos comum aqui, 
     * geralmente é feito DELETE e CREATE de uma nova foto.
     */
    private function update($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da foto não informado para atualização.");
        }

        // 1. Lê a foto atual para garantir que o 'foto' e 'produto_id' não são perdidos
        $current = $this->fotosProduto->read($data['id']);
        if (!$current) {
            return $this->response(false, "Foto não encontrada para atualização.");
        }

        // 2. Atribui propriedades
        $this->fotosProduto->id         = $data['id'];
        $this->fotosProduto->produto_id = $data['produto_id'] ?? $current['produto_id']; // Mantém o produto_id
        $this->fotosProduto->foto       = $data['foto'] ?? $current['foto'];           // Mantém o nome do arquivo
        $this->fotosProduto->descricao  = $data['descricao'] ?? $current['descricao']; // Atualiza a descrição

        // 3. Executa a atualização
        if ($this->fotosProduto->update()) {
            return $this->response(true, "Descrição da foto atualizada com sucesso");
        }
        return $this->response(false, "Erro ao atualizar descrição da foto.");
    }

    /**
     * Deleta uma foto pelo ID, incluindo a exclusão do arquivo físico.
     */
    private function delete($data) {
        if (!isset($data['id'])) {
            return $this->response(false, "ID da foto não informado para exclusão.");
        }
        
        $this->fotosProduto->id = $data['id'];

        // 1. Obtém o nome do arquivo da foto antes de deletar o registro do banco
        $fotoRegistro = $this->fotosProduto->read($data['id']);

        if (!$fotoRegistro) {
            return $this->response(false, "Registro da foto não encontrado no banco.");
        }
        
        $fileName = $fotoRegistro['foto'];

        // 2. Deleta o registro do banco de dados
        if ($this->fotosProduto->delete()) {
            // 3. Tenta deletar o arquivo físico
            if ($fileName && !$this->config->deleteFile($fileName, 'galeria')) {
                 // Nota: Se a deleção do arquivo falhar, avisamos, mas a deleção do BD já ocorreu.
                 error_log("Aviso: Registro da foto {$data['id']} deletado, mas falha ao deletar arquivo {$fileName}.");
                 return $this->response(true, "Foto excluída do banco. ATENÇÃO: Falha ao remover o arquivo físico.");
            }
            return $this->response(true, "Foto excluída com sucesso");
        }
        
        return $this->response(false, "Erro ao excluir foto do banco de dados.");
    }

    /**
     * Lista todas as fotos de um produto específico.
     * Requer 'produto_id' no $data.
     */
    private function listByProduto($data) {
        if (!isset($data['produto_id'])) {
            return $this->response(false, "ID do produto é obrigatório para listar fotos.");
        }
        
        $produtoId = $data['produto_id'];
        $dados = $this->fotosProduto->listByProduto($produtoId);
        
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    /**
     * Lista todas as fotos de todos os produtos.
     */
    private function listAll($data = []) {
        $dados = $this->fotosProduto->listAll();
        return json_encode([
            "success" => true,
            "data" => $dados
        ]);
    }

    //---------------------------------------------------------
    //  MÉTODO UTILITÁRIO
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
$router = new FotosProdutoRouter();
echo $router->handleRequest();