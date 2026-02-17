<?php

    // Garante que o caminho para o Model está correto
    require '../model/Model.php';

    class Migration {

        public $model;

        public function __construct(){
            // Certifique-se de que a classe Model (com as novas funções) está no caminho correto
            $this->model = new Model();
        }

        /**
         * Executa a criação de todas as tabelas no banco de dados.
         */
        public function createTables(){
            
            // Tabelas de USUÁRIOS, CONTEÚDO e CARROSSEL
            $this->model->createTableUsers();
            $this->model->createTableNoticias();
            $this->model->createTableParceiros();
            $this->model->createTableSlideshow();
            $this->model->createTableFaq();
            
            // Tabelas de CATEGORIAS (Novas)
            // É importante criar a tabela principal (categorias) antes da FK (subcategorias)
            $this->model->createTableCategorias();
            $this->model->createTableSubcategorias();

            // Tabelas GEOGRÁFICAS
            $this->model->createTablePaises();
            $this->model->createTableRegioes();
            $this->model->createTableDistritos();
            $this->model->createTableMunicipios();
            $this->model->createTableFreguesias();

            // Tabelas de APLICAÇÃO
            $this->model->createTableProdutos();
            $this->model->createTableFotosProduto();
            $this->model->createTablePropriedadesProduto();
            $this->model->createTablePromocoes();
            $this->model->createTableClientes();
            $this->model->createTableCarrinho();
            $this->model->createTableEncomendas();
            $this->model->createTableItensEncomendas();
            $this->model->createTablePropriedadesItensEncomendas();


            return json_encode([
                "success" => true,
                "message" => "MIGRAÇÕES EFECTUADAS COM SUCESSO. As tabelas de Categorias e Subcategorias foram adicionadas."
            ]);
        }

    }

    // Executa a migração
    $migration = new Migration();
    echo $migration->createTables();
    
?>