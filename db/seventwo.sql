-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17-Fev-2026 às 07:55
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `seventwo`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `carrinho`
--

CREATE TABLE `carrinho` (
  `id` int(11) UNSIGNED NOT NULL,
  `cliente_id` int(11) UNSIGNED NOT NULL,
  `produto_id` int(11) UNSIGNED NOT NULL,
  `qtd` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) UNSIGNED NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `categorias`
--

INSERT INTO `categorias` (`id`, `foto`, `nome`, `descricao`, `status`, `createdAt`, `updatedAt`) VALUES
(1, '19f5e95edba4f5d36bc870baf9a6d073.png', 'VESTUÁRIO', 'VESTUÁRIO', 1, '2025-12-15 05:15:27', '2025-12-15 05:15:27');

-- --------------------------------------------------------

--
-- Estrutura da tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) UNSIGNED NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `pais_id` int(11) UNSIGNED DEFAULT NULL,
  `online` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `clientes`
--

INSERT INTO `clientes` (`id`, `foto`, `nome`, `telefone`, `email`, `senha`, `endereco`, `pais_id`, `online`, `status`, `createdAt`, `updatedAt`) VALUES
(1, NULL, 'José Domingos António', '934823332', 'ajosedomingos231@gmail.com', '$2y$10$mSKDW1yfdhbU/1kboL/2zuyfb0H0ruouywo0PS3CcBdbKUyU6t4ZK', 'Huambo, Cidade Alta', 1, 1, 1, '2026-02-17 06:43:01', '2026-02-17 06:43:12');

-- --------------------------------------------------------

--
-- Estrutura da tabela `distritos`
--

CREATE TABLE `distritos` (
  `id` int(11) UNSIGNED NOT NULL,
  `regiao_id` int(11) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `encomendas`
--

CREATE TABLE `encomendas` (
  `id` int(11) UNSIGNED NOT NULL,
  `cliente_id` int(11) UNSIGNED NOT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `status` enum('Novo','Confirmado','Entregue','Cancelado') NOT NULL DEFAULT 'Novo',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `encomendas`
--

INSERT INTO `encomendas` (`id`, `cliente_id`, `lat`, `lng`, `subtotal`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 1, -12.75025780, 15.77034020, 12000.00, 'Novo', '2025-12-15 08:19:42', '2025-12-15 08:19:42'),
(2, 1, -8.83710000, 13.23330000, 48000.00, 'Novo', '2026-02-17 06:45:23', '2026-02-17 06:45:23');

-- --------------------------------------------------------

--
-- Estrutura da tabela `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) UNSIGNED NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `pergunta` varchar(255) NOT NULL,
  `resposta` text NOT NULL,
  `ordem` int(11) UNSIGNED DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `faqs`
--

INSERT INTO `faqs` (`id`, `categoria`, `pergunta`, `resposta`, `ordem`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 'ENCOMENDAS', 'OLA', 'MUNDO', NULL, 1, '2025-12-15 05:14:29', '2025-12-15 05:14:29');

-- --------------------------------------------------------

--
-- Estrutura da tabela `fotosproduto`
--

CREATE TABLE `fotosproduto` (
  `id` int(11) UNSIGNED NOT NULL,
  `produto_id` int(11) UNSIGNED NOT NULL,
  `foto` varchar(255) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `freguesias`
--

CREATE TABLE `freguesias` (
  `id` int(11) UNSIGNED NOT NULL,
  `municipio_id` int(11) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `itensencomendas`
--

CREATE TABLE `itensencomendas` (
  `id` int(11) UNSIGNED NOT NULL,
  `encomenda_id` int(11) UNSIGNED NOT NULL,
  `produto_id` int(11) UNSIGNED NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `qtd` int(11) UNSIGNED NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `itensencomendas`
--

INSERT INTO `itensencomendas` (`id`, `encomenda_id`, `produto_id`, `preco`, `qtd`, `subtotal`, `createdAt`, `updatedAt`) VALUES
(1, 1, 1, 12000.00, 1, 12000.00, '2025-12-15 07:52:01', '2025-12-15 07:52:01'),
(3, 2, 1, 12000.00, 4, 48000.00, '2026-02-17 06:45:23', '2026-02-17 06:45:23');

-- --------------------------------------------------------

--
-- Estrutura da tabela `municipios`
--

CREATE TABLE `municipios` (
  `id` int(11) UNSIGNED NOT NULL,
  `distrito_id` int(11) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `taxa_entrega` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `noticias`
--

INSERT INTO `noticias` (`id`, `user_id`, `foto`, `titulo`, `descricao`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 1, '0e7bf95c63ee07fe39b5fd84992a6bed.png', 'TITLE', 'DESCRIPTIOS', 1, '2025-12-15 05:16:01', '2025-12-15 05:16:01');

-- --------------------------------------------------------

--
-- Estrutura da tabela `paises`
--

CREATE TABLE `paises` (
  `id` int(11) UNSIGNED NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `codigo` varchar(10) DEFAULT NULL,
  `moeda` varchar(50) DEFAULT NULL,
  `idioma` enum('PT','EN','PT-BR') NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `paises`
--

INSERT INTO `paises` (`id`, `foto`, `nome`, `codigo`, `moeda`, `idioma`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 'c5c6d73fbdafb4a7654e1a71e5c2614c.png', 'ANGOLA', 'AO', 'Kz', 'PT', 1, '2025-12-15 05:14:55', '2025-12-15 05:14:55');

-- --------------------------------------------------------

--
-- Estrutura da tabela `parceiros`
--

CREATE TABLE `parceiros` (
  `id` int(11) UNSIGNED NOT NULL,
  `foto` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `parceiros`
--

INSERT INTO `parceiros` (`id`, `foto`, `nome`, `link`, `createdAt`, `updatedAt`) VALUES
(1, '954e9667f81427578858a2132c0b4bff.png', 'PARCEIRO', 'https://localhost.org', '2025-12-15 05:16:21', '2025-12-15 05:16:21');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) UNSIGNED NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `descricao` text DEFAULT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `categoria_id` int(11) UNSIGNED NOT NULL,
  `subcategoria_id` int(11) UNSIGNED DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `produtos`
--

INSERT INTO `produtos` (`id`, `foto`, `nome`, `preco`, `descricao`, `user_id`, `categoria_id`, `subcategoria_id`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 'fd95b71441732ee1a1d6b52bc5af1379.png', 'title', 12000.00, 'description here...', 1, 1, 1, 1, '2025-12-15 05:26:09', '2025-12-15 05:26:09');

-- --------------------------------------------------------

--
-- Estrutura da tabela `promocoes`
--

CREATE TABLE `promocoes` (
  `id` int(11) UNSIGNED NOT NULL,
  `produto_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `desconto` decimal(5,2) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `propriedadesitensencomendas`
--

CREATE TABLE `propriedadesitensencomendas` (
  `id` int(11) UNSIGNED NOT NULL,
  `item_encomenda_id` int(11) UNSIGNED NOT NULL,
  `propriedade_id` int(11) UNSIGNED NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `propriedadesproduto`
--

CREATE TABLE `propriedadesproduto` (
  `id` int(11) UNSIGNED NOT NULL,
  `produto_id` int(11) UNSIGNED NOT NULL,
  `propriedade` varchar(100) NOT NULL,
  `valor` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `regioes`
--

CREATE TABLE `regioes` (
  `id` int(11) UNSIGNED NOT NULL,
  `pais_id` int(11) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `regioes`
--

INSERT INTO `regioes` (`id`, `pais_id`, `nome`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 1, 'Huambo', 1, '2025-12-15 05:15:05', '2025-12-15 05:15:05');

-- --------------------------------------------------------

--
-- Estrutura da tabela `slideshow`
--

CREATE TABLE `slideshow` (
  `id` int(11) UNSIGNED NOT NULL,
  `foto` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `slideshow`
--

INSERT INTO `slideshow` (`id`, `foto`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 'af367b768262839202853ea9767b466e.png', 1, '2025-12-15 05:16:38', '2025-12-15 05:16:38');

-- --------------------------------------------------------

--
-- Estrutura da tabela `subcategorias`
--

CREATE TABLE `subcategorias` (
  `id` int(11) UNSIGNED NOT NULL,
  `categoria_id` int(11) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `subcategorias`
--

INSERT INTO `subcategorias` (`id`, `categoria_id`, `nome`, `descricao`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 1, 'ROUPAS', 'ROUPAS', 1, '2025-12-15 05:15:39', '2025-12-15 05:15:39');

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `identificacao` varchar(100) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `online` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `nivel` enum('Administrador','Vendedor') NOT NULL DEFAULT 'Administrador',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `foto`, `identificacao`, `nome`, `telefone`, `email`, `senha`, `online`, `status`, `nivel`, `createdAt`, `updatedAt`) VALUES
(1, '97301cb9ef50313f15e1888298f7344d.png', '010194927HO043', 'JOSÉ DOMINGOS ANTÓNIO', '934823332', 'ajosedomingos231@gmail.com', '$2y$10$Yt.TN5z0UEJpKZTAcnufyuZSqAmTUAHGmKblc.K8.AG5MFREQjj.q', 1, 1, 'Administrador', '2025-12-15 05:06:56', '2025-12-15 05:08:18');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `carrinho`
--
ALTER TABLE `carrinho`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_carrinho_cliente_produto` (`cliente_id`,`produto_id`),
  ADD KEY `fk_carrinho_produto` (`produto_id`);

--
-- Índices para tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices para tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_clientes_pais` (`pais_id`);

--
-- Índices para tabela `distritos`
--
ALTER TABLE `distritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_distrito_regiao` (`regiao_id`,`nome`);

--
-- Índices para tabela `encomendas`
--
ALTER TABLE `encomendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_encomendas_cliente` (`cliente_id`);

--
-- Índices para tabela `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_faq_categoria_pergunta` (`categoria`,`pergunta`);

--
-- Índices para tabela `fotosproduto`
--
ALTER TABLE `fotosproduto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fotosproduto_produto` (`produto_id`);

--
-- Índices para tabela `freguesias`
--
ALTER TABLE `freguesias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_freguesia_municipio` (`municipio_id`,`nome`);

--
-- Índices para tabela `itensencomendas`
--
ALTER TABLE `itensencomendas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_item_encomenda_produto` (`encomenda_id`,`produto_id`),
  ADD KEY `fk_itens_produto` (`produto_id`);

--
-- Índices para tabela `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_municipio_distrito` (`distrito_id`,`nome`);

--
-- Índices para tabela `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_noticias_user` (`user_id`);

--
-- Índices para tabela `paises`
--
ALTER TABLE `paises`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Índices para tabela `parceiros`
--
ALTER TABLE `parceiros`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produtos_user` (`user_id`),
  ADD KEY `fk_produtos_categoria` (`categoria_id`),
  ADD KEY `fk_produtos_subcategoria` (`subcategoria_id`);

--
-- Índices para tabela `promocoes`
--
ALTER TABLE `promocoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_promocoes_produto` (`produto_id`),
  ADD KEY `fk_promocoes_user` (`user_id`);

--
-- Índices para tabela `propriedadesitensencomendas`
--
ALTER TABLE `propriedadesitensencomendas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_propriedade_item` (`item_encomenda_id`,`propriedade_id`),
  ADD KEY `fk_propriedades_propriedade_produto` (`propriedade_id`);

--
-- Índices para tabela `propriedadesproduto`
--
ALTER TABLE `propriedadesproduto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_propriedade_produto` (`produto_id`,`propriedade`,`valor`);

--
-- Índices para tabela `regioes`
--
ALTER TABLE `regioes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_regiao_pais` (`pais_id`,`nome`);

--
-- Índices para tabela `slideshow`
--
ALTER TABLE `slideshow`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_subcategoria_categoria` (`categoria_id`,`nome`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identificacao` (`identificacao`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `carrinho`
--
ALTER TABLE `carrinho`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `distritos`
--
ALTER TABLE `distritos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `encomendas`
--
ALTER TABLE `encomendas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `fotosproduto`
--
ALTER TABLE `fotosproduto`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `freguesias`
--
ALTER TABLE `freguesias`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `itensencomendas`
--
ALTER TABLE `itensencomendas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `paises`
--
ALTER TABLE `paises`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `parceiros`
--
ALTER TABLE `parceiros`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `promocoes`
--
ALTER TABLE `promocoes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `propriedadesitensencomendas`
--
ALTER TABLE `propriedadesitensencomendas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `propriedadesproduto`
--
ALTER TABLE `propriedadesproduto`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `regioes`
--
ALTER TABLE `regioes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `slideshow`
--
ALTER TABLE `slideshow`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `subcategorias`
--
ALTER TABLE `subcategorias`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `carrinho`
--
ALTER TABLE `carrinho`
  ADD CONSTRAINT `fk_carrinho_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_carrinho_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_clientes_pais` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `distritos`
--
ALTER TABLE `distritos`
  ADD CONSTRAINT `fk_distritos_regiao` FOREIGN KEY (`regiao_id`) REFERENCES `regioes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `encomendas`
--
ALTER TABLE `encomendas`
  ADD CONSTRAINT `fk_encomendas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `fotosproduto`
--
ALTER TABLE `fotosproduto`
  ADD CONSTRAINT `fk_fotosproduto_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `freguesias`
--
ALTER TABLE `freguesias`
  ADD CONSTRAINT `fk_freguesias_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `itensencomendas`
--
ALTER TABLE `itensencomendas`
  ADD CONSTRAINT `fk_itens_encomenda` FOREIGN KEY (`encomenda_id`) REFERENCES `encomendas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_itens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `municipios`
--
ALTER TABLE `municipios`
  ADD CONSTRAINT `fk_municipios_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `distritos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `fk_noticias_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `fk_produtos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produtos_subcategoria` FOREIGN KEY (`subcategoria_id`) REFERENCES `subcategorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produtos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `promocoes`
--
ALTER TABLE `promocoes`
  ADD CONSTRAINT `fk_promocoes_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_promocoes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `propriedadesitensencomendas`
--
ALTER TABLE `propriedadesitensencomendas`
  ADD CONSTRAINT `fk_propriedades_item_encomenda` FOREIGN KEY (`item_encomenda_id`) REFERENCES `itensencomendas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_propriedades_propriedade_produto` FOREIGN KEY (`propriedade_id`) REFERENCES `propriedadesproduto` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `propriedadesproduto`
--
ALTER TABLE `propriedadesproduto`
  ADD CONSTRAINT `fk_propriedadesproduto_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `regioes`
--
ALTER TABLE `regioes`
  ADD CONSTRAINT `fk_regioes_pais` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD CONSTRAINT `fk_subcategorias_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
