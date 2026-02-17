<?php

require_once '../config/Config.php';

class Model {

     private $db;

     public function __construct() {
         $config = new Config();
         $this->db = $config->dbConnect();
     }

     // ---------------------------------------------------------
     // MÉTODOS DE CRIAÇÃO DAS TABELAS (EXISTENTES)
     // ---------------------------------------------------------

     /**
      * Cria a tabela 'users' no banco de dados.
      */
     public function createTableUsers() {
         $query = "
            CREATE TABLE IF NOT EXISTS users (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              foto VARCHAR(255),
              identificacao VARCHAR(100) NOT NULL UNIQUE,
              nome VARCHAR(255) NOT NULL,
              telefone VARCHAR(20),
              email VARCHAR(150) UNIQUE,
              senha VARCHAR(255) NOT NULL,
              online BOOLEAN DEFAULT 0,
              status BOOLEAN DEFAULT 1,
              nivel ENUM('Administrador', 'Vendedor') NOT NULL DEFAULT 'Administrador',
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }

     /**
      * Cria a tabela 'noticias' no banco de dados.
      */
     public function createTableNoticias() {
         $query = "
            CREATE TABLE IF NOT EXISTS noticias (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              user_id INT(11) UNSIGNED NOT NULL,
              foto VARCHAR(255),
              titulo VARCHAR(255) NOT NULL,
              descricao TEXT,
              status BOOLEAN NOT NULL DEFAULT 1,
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              CONSTRAINT fk_noticias_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }

     /**
      * Cria a tabela 'parceiros' no banco de dados.
      * Colunas: id, foto, nome, link
      */
     public function createTableParceiros() {
         $query = "
            CREATE TABLE IF NOT EXISTS parceiros (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              foto VARCHAR(255) NOT NULL,
              nome VARCHAR(255) NOT NULL,
              link VARCHAR(255),
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }

     /**
      * Cria a tabela 'slideshow' (carrossel) no banco de dados.
      * Colunas: id, foto, status
      */
     public function createTableSlideshow() {
         $query = "
            CREATE TABLE IF NOT EXISTS slideshow (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              foto VARCHAR(255) NOT NULL,
              status BOOLEAN NOT NULL DEFAULT 1,
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }

     // ---------------------------------------------------------
     // MÉTODOS DE CRIAÇÃO DAS TABELAS DE PRODUTOS/SERVIÇOS
     // ---------------------------------------------------------

     /**
      * Cria a tabela 'categorias' no banco de dados.
      * Colunas: id, foto, nome, descricao, status
      */
     public function createTableCategorias() {
         $query = "
            CREATE TABLE IF NOT EXISTS categorias (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              foto VARCHAR(255),
              nome VARCHAR(100) NOT NULL UNIQUE,
              descricao TEXT,
              status TINYINT(1) NOT NULL DEFAULT 1,
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }

     /**
      * Cria a tabela 'subcategorias' no banco de dados.
      * Colunas: id, categoria_id (FK), nome, descricao, status
      */
     public function createTableSubcategorias() {
         $query = "
            CREATE TABLE IF NOT EXISTS subcategorias (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              categoria_id INT(11) UNSIGNED NOT NULL,
              nome VARCHAR(100) NOT NULL,
              descricao TEXT,
              status TINYINT(1) NOT NULL DEFAULT 1,
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              
              -- Restrição: Não pode haver duas subcategorias com o mesmo nome na mesma categoria
              UNIQUE KEY uk_subcategoria_categoria (categoria_id, nome),
              
              -- Chave Estrangeira: Referencia a tabela 'categorias'
              CONSTRAINT fk_subcategorias_categoria 
                   FOREIGN KEY (categoria_id) 
                   REFERENCES categorias(id) 
                   ON DELETE CASCADE 
                   ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }


     // ---------------------------------------------------------
     // MÉTODOS DE CRIAÇÃO DAS TABELAS GEOGRÁFICAS
     // ---------------------------------------------------------

     /**
      * Cria a tabela 'paises' no banco de dados.
      * Colunas: id, foto, nome, codigo, moeda, idioma (PT, EN, PT-BR), status
      */
     public function createTablePaises() {
         $query = "
            CREATE TABLE IF NOT EXISTS paises (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              foto VARCHAR(255),
              nome VARCHAR(100) NOT NULL UNIQUE,
              codigo VARCHAR(10) UNIQUE,
              moeda VARCHAR(50),
              idioma ENUM('PT', 'EN', 'PT-BR') NOT NULL,
              status BOOLEAN NOT NULL DEFAULT 1,
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }

     /**
      * Cria a tabela 'regioes' no banco de dados.
      * Colunas: id, pais_id (FK), nome, status
      */
     public function createTableRegioes() {
         $query = "
            CREATE TABLE IF NOT EXISTS regioes (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              pais_id INT(11) UNSIGNED NOT NULL,
              nome VARCHAR(100) NOT NULL,
              status BOOLEAN NOT NULL DEFAULT 1,
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY uk_regiao_pais (pais_id, nome),
              CONSTRAINT fk_regioes_pais FOREIGN KEY (pais_id) REFERENCES paises(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }

     /**
      * Cria a tabela 'distritos' no banco de dados.
      * Colunas: id, regiao_id (FK), nome, status
      */
     public function createTableDistritos() {
         $query = "
            CREATE TABLE IF NOT EXISTS distritos (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              regiao_id INT(11) UNSIGNED NOT NULL,
              nome VARCHAR(100) NOT NULL,
              status BOOLEAN NOT NULL DEFAULT 1,
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY uk_distrito_regiao (regiao_id, nome),
              CONSTRAINT fk_distritos_regiao FOREIGN KEY (regiao_id) REFERENCES regioes(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }

     /**
      * Cria a tabela 'municipios' no banco de dados.
      * Colunas: id, distrito_id (FK), nome, taxa_entrega
      */
     public function createTableMunicipios() {
         $query = "
            CREATE TABLE IF NOT EXISTS municipios (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              distrito_id INT(11) UNSIGNED NOT NULL,
              nome VARCHAR(100) NOT NULL,
              taxa_entrega DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
              status TINYINT(1) NOT NULL DEFAULT 1, /* Adicionado a coluna status */
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY uk_municipio_distrito (distrito_id, nome),
              CONSTRAINT fk_municipios_distrito FOREIGN KEY (distrito_id) REFERENCES distritos(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }

     /**
      * Cria a tabela 'freguesias' no banco de dados.
      * Colunas: id, municipio_id (FK), nome, status
      */
     public function createTableFreguesias() {
         $query = "
            CREATE TABLE IF NOT EXISTS freguesias (
              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              municipio_id INT(11) UNSIGNED NOT NULL,
              nome VARCHAR(100) NOT NULL,
              status BOOLEAN NOT NULL DEFAULT 1,
              createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY uk_freguesia_municipio (municipio_id, nome),
              CONSTRAINT fk_freguesias_municipio FOREIGN KEY (municipio_id) REFERENCES municipios(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ";
         return $this->db->query($query);
     }

      /**
       * Cria a tabela 'produtos' no banco de dados.
       * Colunas: id, foto, nome, preco, descricao, user_id (FK),
       * categoria_id (FK), subcategoria_id (FK), status
       */
      public function createTableProdutos() {
          $query = "
              CREATE TABLE IF NOT EXISTS produtos (
                  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  foto VARCHAR(255),
                  nome VARCHAR(255) NOT NULL,
                  preco DECIMAL(10,2) NOT NULL,
                  descricao TEXT,
                  user_id INT(11) UNSIGNED, /* ID do usuário/criador */
                  categoria_id INT(11) UNSIGNED NOT NULL,
                  subcategoria_id INT(11) UNSIGNED,
                  status TINYINT(1) NOT NULL DEFAULT 1,
                  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                  -- Chave Estrangeira: Usuário (permite NULL)
                  CONSTRAINT fk_produtos_user
                      FOREIGN KEY (user_id)
                      REFERENCES users(id)
                      ON DELETE SET NULL
                      ON UPDATE CASCADE,

                  -- Chave Estrangeira: Categoria (RESTRICT evita exclusão acidental)
                  CONSTRAINT fk_produtos_categoria
                      FOREIGN KEY (categoria_id)
                      REFERENCES categorias(id)
                      ON DELETE RESTRICT
                      ON UPDATE CASCADE,

                  -- Chave Estrangeira: Subcategoria (permite NULL)
                  CONSTRAINT fk_produtos_subcategoria
                      FOREIGN KEY (subcategoria_id)
                      REFERENCES subcategorias(id)
                      ON DELETE SET NULL
                      ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ";

          return $this->db->query($query);
      }


      /**
       * Cria a tabela 'fotosProduto' (galeria de fotos do produto) no banco de dados.
       * Colunas: id, produto_id (FK), foto, descricao
       */
      public function createTableFotosProduto() {
          $query = "
              CREATE TABLE IF NOT EXISTS fotosProduto (
                  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  produto_id INT(11) UNSIGNED NOT NULL,
                  foto VARCHAR(255) NOT NULL,
                  descricao VARCHAR(255),
                  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                  -- Chave Estrangeira: Referencia a tabela 'produtos'
                  -- ON DELETE CASCADE: Se o produto for deletado, suas fotos são deletadas automaticamente
                  CONSTRAINT fk_fotosproduto_produto
                      FOREIGN KEY (produto_id)
                      REFERENCES produtos(id)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ";

          return $this->db->query($query);
      }


     /**
       * Cria a tabela 'propriedadesProduto' no banco de dados.
       * Colunas: id, produto_id (FK), propriedade, valor, status
       */
      public function createTablePropriedadesProduto() {
          $query = "
              CREATE TABLE IF NOT EXISTS propriedadesProduto (
                  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  produto_id INT(11) UNSIGNED NOT NULL,
                  propriedade VARCHAR(100) NOT NULL, /* Ex: Tamanho, Cor, Material */
                  valor VARCHAR(100) NOT NULL,        /* Ex: M, Vermelho, Algodão */
                  status TINYINT(1) NOT NULL DEFAULT 1,
                  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                  -- Restrição: Não pode haver propriedade e valor repetidos para o mesmo produto
                  UNIQUE KEY uk_propriedade_produto (produto_id, propriedade, valor),

                  -- Chave Estrangeira: Referencia a tabela 'produtos'
                  -- ON DELETE CASCADE: Se o produto for deletado, suas propriedades também são deletadas
                  CONSTRAINT fk_propriedadesproduto_produto
                      FOREIGN KEY (produto_id)
                      REFERENCES produtos(id)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ";

          return $this->db->query($query);
      }

     // ---------------------------------------------------------
     // MÉTODOS DE CRIAÇÃO DAS TABELAS DE PROMOÇÕES
     // ---------------------------------------------------------

     /**
       * Cria a tabela 'promocoes' no banco de dados.
       * Colunas: id, produto_id (FK), desconto, data_inicio, data_fim, user_id (FK)
       */
      public function createTablePromocoes() {
          $query = "
              CREATE TABLE IF NOT EXISTS promocoes (
                  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  produto_id INT(11) UNSIGNED NOT NULL,
                  user_id INT(11) UNSIGNED,
                  desconto DECIMAL(5,2) NOT NULL, /* Percentagem de desconto */
                  data_inicio DATE NOT NULL,
                  data_fim DATE NOT NULL,
                  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                  -- Restricao: Desconto deve estar entre 0 e 100
                  CONSTRAINT chk_desconto CHECK (desconto >= 0 AND desconto <= 100),

                  -- Chave Estrangeira: Produto
                  CONSTRAINT fk_promocoes_produto
                      FOREIGN KEY (produto_id)
                      REFERENCES produtos(id)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE,

                  -- Chave Estrangeira: Usuario que criou a promocao
                  CONSTRAINT fk_promocoes_user
                      FOREIGN KEY (user_id)
                      REFERENCES users(id)
                      ON DELETE SET NULL
                      ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ";

          return $this->db->query($query);
      }


      /**
       * Cria a tabela 'clientes' no banco de dados.
       * Colunas: id, foto, nome, telefone, email, senha,
       * endereco,
       * pais_id (FK), regiao_id (FK), distrito_id (FK),
       * municipio_id (FK), freguesia_id (FK), online, status
       */
      public function createTableClientes() {
            $query = "
                CREATE TABLE IF NOT EXISTS clientes (
                    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    foto VARCHAR(255),
                    nome VARCHAR(255) NOT NULL,
                    telefone VARCHAR(20),
                    email VARCHAR(150) UNIQUE NOT NULL,
                    senha VARCHAR(255) NOT NULL,

                    -- Endereço textual do cliente (rua, bairro, referência, etc.)
                    endereco VARCHAR(255),

                    -- Apenas País como chave estrangeira
                    pais_id INT(11) UNSIGNED,

                    -- Controle de status
                    online BOOLEAN DEFAULT 0,
                    status BOOLEAN DEFAULT 1,

                    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                    CONSTRAINT fk_clientes_pais
                        FOREIGN KEY (pais_id)
                        REFERENCES paises(id)
                        ON DELETE SET NULL
                        ON UPDATE CASCADE

                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";

            return $this->db->query($query);
        }


     /**
       * Cria a tabela 'carrinho' no banco de dados.
       * Colunas: id, cliente_id (FK), produto_id (FK), qtd
       */
      public function createTableCarrinho() {
          $query = "
              CREATE TABLE IF NOT EXISTS carrinho (
                  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  cliente_id INT(11) UNSIGNED NOT NULL,
                  produto_id INT(11) UNSIGNED NOT NULL,
                  qtd INT(11) UNSIGNED NOT NULL DEFAULT 1,

                  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                  -- Restricao: um cliente nao pode repetir o mesmo produto no carrinho
                  UNIQUE KEY uk_carrinho_cliente_produto (cliente_id, produto_id),

                  -- Chave Estrangeira: Cliente
                  CONSTRAINT fk_carrinho_cliente
                      FOREIGN KEY (cliente_id)
                      REFERENCES clientes(id)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE,

                  -- Chave Estrangeira: Produto
                  CONSTRAINT fk_carrinho_produto
                      FOREIGN KEY (produto_id)
                      REFERENCES produtos(id)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ";

          return $this->db->query($query);
      }


     /**
       * Cria a tabela 'encomendas' no banco de dados.
       * Colunas: id, cliente_id (FK), lat, lng, subtotal, status
       */
      public function createTableEncomendas() {
          $query = "
              CREATE TABLE IF NOT EXISTS encomendas (
                  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  cliente_id INT(11) UNSIGNED NOT NULL,
                  lat DECIMAL(10,8),
                  lng DECIMAL(11,8),
                  subtotal DECIMAL(10,2) NOT NULL,
                  status ENUM('Novo','Confirmado','Entregue','Cancelado')
                      NOT NULL DEFAULT 'Novo',

                  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                  CONSTRAINT fk_encomendas_cliente
                      FOREIGN KEY (cliente_id)
                      REFERENCES clientes(id)
                      ON DELETE RESTRICT
                      ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ";

          return $this->db->query($query);
      }

      
     /**
       * Cria a tabela 'itensEncomendas' no banco de dados.
       * Colunas: id, encomenda_id (FK), produto_id (FK), preco, qtd, subtotal
       */
      public function createTableItensEncomendas() {
          $query = "
              CREATE TABLE IF NOT EXISTS itensEncomendas (
                  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  encomenda_id INT(11) UNSIGNED NOT NULL,
                  produto_id INT(11) UNSIGNED NOT NULL,
                  preco DECIMAL(10,2) NOT NULL,
                  qtd INT(11) UNSIGNED NOT NULL,
                  subtotal DECIMAL(10,2) NOT NULL,

                  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                  -- Restricao: um produto so pode aparecer uma vez por encomenda
                  UNIQUE KEY uk_item_encomenda_produto (encomenda_id, produto_id),

                  -- Chave Estrangeira: Encomenda
                  CONSTRAINT fk_itens_encomenda
                      FOREIGN KEY (encomenda_id)
                      REFERENCES encomendas(id)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE,

                  -- Chave Estrangeira: Produto
                  CONSTRAINT fk_itens_produto
                      FOREIGN KEY (produto_id)
                      REFERENCES produtos(id)
                      ON DELETE RESTRICT
                      ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ";

          return $this->db->query($query);
      }


     /**
       * Cria a tabela 'propriedadesItensEncomendas' no banco de dados.
       * Colunas: id, item_encomenda_id (FK), propriedade_id (FK)
       * Nota: propriedade_id referencia 'propriedadesProduto' para registrar
       * a variacao exata do produto comprado (ex: Camisa, Tamanho M, Cor Azul)
       */
      public function createTablePropriedadesItensEncomendas() {
          $query = "
              CREATE TABLE IF NOT EXISTS propriedadesItensEncomendas (
                  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  item_encomenda_id INT(11) UNSIGNED NOT NULL,
                  propriedade_id INT(11) UNSIGNED NOT NULL,

                  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                  -- Restricao: nao pode haver propriedades repetidas para o mesmo item
                  UNIQUE KEY uk_propriedade_item (item_encomenda_id, propriedade_id),

                  -- Chave Estrangeira: Item da Encomenda
                  CONSTRAINT fk_propriedades_item_encomenda
                      FOREIGN KEY (item_encomenda_id)
                      REFERENCES itensEncomendas(id)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE,

                  -- Chave Estrangeira: Propriedade do Produto
                  CONSTRAINT fk_propriedades_propriedade_produto
                      FOREIGN KEY (propriedade_id)
                      REFERENCES propriedadesProduto(id)
                      ON DELETE RESTRICT
                      ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ";

          return $this->db->query($query);
      }


     /**
      * Cria a tabela 'faq' (Perguntas Frequentes) no banco de dados.
      * Colunas: id, pergunta, resposta, categoria, ordem, status
      */
    public function createTableFaq() {
        $query = "
            CREATE TABLE IF NOT EXISTS faqs (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                categoria VARCHAR(100) DEFAULT NULL,
                pergunta VARCHAR(255) NOT NULL,
                resposta TEXT NOT NULL,
                `ordem` INT(11) UNSIGNED DEFAULT 0,
                status TINYINT(1) NOT NULL DEFAULT 1,
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_faq_categoria_pergunta (categoria, pergunta)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        return $this->db->query($query);
    }



     // ---------------------------------------------------------
     // MÉTODOS DE BACKUP
     // ---------------------------------------------------------
     /**
      * Realiza o backup de todas as tabelas do banco de dados.
      * @return string O nome do arquivo de backup criado.
      */
     public function backupDatabase() {
         $backupDir = "../backups/";
         if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
         }

         $filename = $backupDir . "backup_" . date("Y-m-d_H-i-s") . ".sql";

         $tables = [];
         $result = $this->db->query("SHOW TABLES");
         while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
         }

         $sqlScript = "-- Backup gerado em " . date("Y-m-d H:i:s") . "\n\n";

         foreach ($tables as $table) {
            // Estrutura da tabela
            $result = $this->db->query("SHOW CREATE TABLE $table");
            $row = $result->fetch_row();
            $sqlScript .= "\n\n" . $row[1] . ";\n\n";

            // Dados da tabela
            $result = $this->db->query("SELECT * FROM $table");
            $columnCount = $result->field_count;

            while ($row = $result->fetch_row()) {
              $sqlScript .= "INSERT INTO $table VALUES(";
              for ($i = 0; $i < $columnCount; $i++) {
                   $row[$i] = isset($row[$i]) ? $this->db->real_escape_string($row[$i]) : "NULL";
                   // Envolve o valor em aspas, exceto se for NULL
                   $sqlScript .= ($row[$i] === "NULL") ? 'NULL' : '"' . $row[$i] . '"';
                   if ($i < ($columnCount - 1)) {
                       $sqlScript .= ',';
                   }
              }
              $sqlScript .= ");\n";
            }
         }

         file_put_contents($filename, $sqlScript);
         return $filename;
     }
}