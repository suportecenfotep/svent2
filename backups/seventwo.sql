-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 10-Nov-2025 às 05:12
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
-- Banco de dados: `sevenvtnz`
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

--
-- Extraindo dados da tabela `carrinho`
--

INSERT INTO `carrinho` (`id`, `cliente_id`, `produto_id`, `qtd`, `createdAt`, `updatedAt`) VALUES
(14, 1, 13, 2, '2025-11-01 12:47:48', '2025-11-01 12:48:01');

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
(1, '6662760fe24264308f5016de7a360eb5.jpg', 'TELEFONES', 'TELEFONES', 1, '2025-10-11 15:30:21', '2025-10-11 15:30:21'),
(2, 'f6b04d7fc2172befde5820f4306ce001.jpg', 'TSHIRTS', 'TSHIRTS', 1, '2025-10-14 05:23:10', '2025-10-14 05:23:10'),
(3, '259ce4697269ee8be61235e12b138be1.jpg', 'CALÇADOS', 'CALÇADOS', 1, '2025-10-14 05:57:33', '2025-10-15 00:04:57'),
(4, '217de1acff07a986477eccc2494bcbf0.jpg', 'CASA & DECORAÇÃO', 'CASA & DECORAÇÃO', 1, '2025-10-14 23:58:46', '2025-10-15 00:04:30'),
(5, '54c19d0557a5eb2e58f001b01bb21689.jpg', 'ACESSÓRIOS', 'ACESSÓRIOS', 1, '2025-10-14 23:59:25', '2025-10-14 23:59:25');

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
  `pais_id` int(11) UNSIGNED DEFAULT NULL,
  `regiao_id` int(11) UNSIGNED DEFAULT NULL,
  `distrito_id` int(11) UNSIGNED DEFAULT NULL,
  `municipio_id` int(11) UNSIGNED DEFAULT NULL,
  `freguesia_id` int(11) UNSIGNED DEFAULT NULL,
  `online` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `clientes`
--

INSERT INTO `clientes` (`id`, `foto`, `nome`, `telefone`, `email`, `senha`, `pais_id`, `regiao_id`, `distrito_id`, `municipio_id`, `freguesia_id`, `online`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 'f70a3680b6d3a8f5069789ebe482417c.jpg', 'JOSÉ DOMINGOS ANTÓ', '934823332', 'ajosedomingos231@gmail.com', '$2y$10$88nCfmOgHf70j7At7VWYQOUtqmTcR1tfUaPqhhUeZwHCtw/YYzIvy', 1, 1, 1, 1, 1, 1, 1, '2025-10-14 03:14:46', '2025-11-01 19:42:28');

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

--
-- Extraindo dados da tabela `distritos`
--

INSERT INTO `distritos` (`id`, `regiao_id`, `nome`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 1, 'HUAMBO', 1, '2025-10-11 14:45:38', '2025-10-11 14:45:38');

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
  `taxa_entrega` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Novo','Confirmado','Entregue','Cancelado') NOT NULL DEFAULT 'Novo',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `encomendas`
--

INSERT INTO `encomendas` (`id`, `cliente_id`, `lat`, `lng`, `subtotal`, `taxa_entrega`, `status`, `createdAt`, `updatedAt`) VALUES
(33, 1, -8.88012800, 13.27759360, 12000.00, 2100.00, 'Confirmado', '2025-10-29 05:10:22', '2025-10-29 05:12:12'),
(34, 1, -8.88012800, 13.27759360, 52833.00, 2100.00, 'Novo', '2025-10-29 05:14:24', '2025-10-29 05:14:24'),
(35, 1, -12.76065960, 15.78033690, 12000.00, 2100.00, 'Novo', '2025-10-31 13:26:13', '2025-10-31 13:26:13'),
(36, 1, -8.88012800, 13.27759360, 41500.00, 2100.00, 'Novo', '2025-11-01 12:19:32', '2025-11-01 12:19:32'),
(37, 1, -8.88012800, 13.27759360, 12000.00, 2100.00, 'Novo', '2025-11-01 12:23:57', '2025-11-01 12:23:57');

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
(1, 'Pagamentos', 'Como Fazer os Pagamentos ?', 'Resposta aqui...', NULL, 1, '2025-10-18 07:21:37', '2025-10-18 08:52:15'),
(2, 'Encomendas', 'Como receber as encomendas ?', 'Encomendas', NULL, 1, '2025-10-18 08:53:07', '2025-10-18 08:53:07');

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

--
-- Extraindo dados da tabela `freguesias`
--

INSERT INTO `freguesias` (`id`, `municipio_id`, `nome`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 1, 'HUAMBO', 1, '2025-10-11 15:18:41', '2025-10-11 15:18:41');

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
(32, 33, 13, 12000.00, 1, 12000.00, '2025-10-29 05:10:23', '2025-10-29 05:10:23'),
(33, 34, 13, 12000.00, 3, 36000.00, '2025-10-29 05:14:25', '2025-10-29 05:14:25'),
(34, 34, 14, 4500.00, 3, 13500.00, '2025-10-29 05:14:25', '2025-10-29 05:14:25'),
(35, 34, 15, 1111.00, 3, 3333.00, '2025-10-29 05:14:25', '2025-10-29 05:14:25'),
(36, 35, 13, 12000.00, 1, 12000.00, '2025-10-31 13:26:13', '2025-10-31 13:26:13'),
(37, 36, 11, 10375.00, 4, 41500.00, '2025-11-01 12:19:32', '2025-11-01 12:19:32'),
(38, 37, 13, 12000.00, 1, 12000.00, '2025-11-01 12:23:57', '2025-11-01 12:23:57');

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

--
-- Extraindo dados da tabela `municipios`
--

INSERT INTO `municipios` (`id`, `distrito_id`, `nome`, `taxa_entrega`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 1, 'HUAMBO', 2100.00, 1, '2025-10-11 14:55:42', '2025-10-11 14:55:42');

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
(1, 1, '01521242f3f2264fa942899a1250563f.jpg', 'title here', 'description here...', 1, '2025-10-11 14:25:14', '2025-10-11 14:25:14'),
(2, 1, '808abdae23c2f22be6d666ea068bd203.jpg', 'test', 'test\r\n', 1, '2025-10-18 09:22:13', '2025-10-18 09:22:13');

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
(1, '0010340195766244151d0ce2e61679fe.jpg', 'ANGOLA', 'AOA', 'Kz', 'PT', 1, '2025-10-11 14:37:09', '2025-10-11 14:37:09');

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
(1, '56642dc914488ce14ceaebba364686c2.jpg', 'Name of test', 'http://link-of-test.com', '2025-10-11 14:26:34', '2025-10-11 14:26:34');

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
(7, 'ed1cf157fb21079cab83aad12dcd4dd2.png', 'Tshirt Never Give Up', 9000.00, 'Tshirt Never Give Up', 1, 2, 2, 1, '2025-10-14 05:25:45', '2025-10-14 06:07:25'),
(8, '7859639e726b488b11ad86a1a7283e11.jpg', 'Tshirts OMG', 7500.00, '', 1, 2, 2, 1, '2025-10-14 05:27:13', '2025-10-14 05:27:13'),
(9, '9f3b67b0f53982129ac18754eab1bde9.png', 'Tshirts AS', 12000.00, '', 1, 2, 2, 1, '2025-10-14 05:32:12', '2025-10-14 05:32:12'),
(10, 'b300b5e0fcea12bc37c56b09b17cf290.jpg', 'ADIDAS', 45000.00, '', 1, 3, 3, 1, '2025-10-14 05:59:21', '2025-10-14 05:59:21'),
(11, 'd034b656ce54ff91e8e92d2f9eba1cd3.jpg', 'Sapatilhas...', 12500.00, '', 1, 3, 3, 1, '2025-10-14 06:03:03', '2025-10-14 06:03:03'),
(12, '8696da09401cc435e91610c6ab4ba4cb.jpg', 'PRODUTO 1', 45000.00, '', 1, 3, 3, 1, '2025-10-18 03:16:00', '2025-10-18 03:16:00'),
(13, 'fed1c50c92742b20d8cb97b42c7504f9.webp', 'Produto 2', 12000.00, '', 1, 2, 2, 1, '2025-10-18 03:16:37', '2025-10-18 03:16:37'),
(14, 'e7c356d8bea3ba68658b169d1207b30a.jpg', 'Produto 3', 4500.00, '', 1, 2, 2, 1, '2025-10-18 03:17:30', '2025-10-18 03:17:30'),
(15, '769bc491bb99af5bcaae0c0f00e5456a.jpg', '4500', 1111.00, '', 1, 3, 3, 1, '2025-10-18 03:18:13', '2025-10-18 03:18:13');

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

--
-- Extraindo dados da tabela `promocoes`
--

INSERT INTO `promocoes` (`id`, `produto_id`, `user_id`, `desconto`, `data_inicio`, `data_fim`, `createdAt`, `updatedAt`) VALUES
(3, 10, 1, 14.00, '2025-10-01', '2025-10-31', '2025-10-14 19:51:07', '2025-10-14 19:51:07'),
(4, 11, 1, 17.00, '2025-09-30', '2025-11-08', '2025-10-14 19:51:21', '2025-10-14 19:51:21');

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

--
-- Extraindo dados da tabela `propriedadesproduto`
--

INSERT INTO `propriedadesproduto` (`id`, `produto_id`, `propriedade`, `valor`, `status`, `createdAt`, `updatedAt`) VALUES
(4, 10, 'Cores', 'Azul e Vermelha', 1, '2025-10-17 06:07:53', '2025-10-17 06:07:53');

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
(1, 1, 'HUAMBO', 1, '2025-10-11 14:41:23', '2025-10-11 14:41:23');

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
(1, 1, 'IPHONES', 'Iphones here', 1, '2025-10-11 15:45:12', '2025-10-11 15:45:12'),
(2, 2, 'MASCULINA', 'MASCULINA', 1, '2025-10-14 05:24:49', '2025-10-14 05:24:49'),
(3, 3, 'ADIDAS', 'ADIDAS', 1, '2025-10-14 05:58:43', '2025-10-14 05:58:43');

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
(1, '32c1676379f54cb8e3feb489c49d71c5.jpg', '010194927HO043', 'JOSÉ DOMINGOS ANTÓNIO', '934823332', 'ajosedomingos231@gmail.com', '$2y$10$88nCfmOgHf70j7At7VWYQOUtqmTcR1tfUaPqhhUeZwHCtw/YYzIvy', 1, 1, 'Administrador', '2025-10-11 05:46:04', '2025-11-10 04:04:16');

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
  ADD KEY `fk_clientes_pais` (`pais_id`),
  ADD KEY `fk_clientes_regiao` (`regiao_id`),
  ADD KEY `fk_clientes_distrito` (`distrito_id`),
  ADD KEY `fk_clientes_municipio` (`municipio_id`),
  ADD KEY `fk_clientes_freguesia` (`freguesia_id`);

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `distritos`
--
ALTER TABLE `distritos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `encomendas`
--
ALTER TABLE `encomendas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `fotosproduto`
--
ALTER TABLE `fotosproduto`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `freguesias`
--
ALTER TABLE `freguesias`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `itensencomendas`
--
ALTER TABLE `itensencomendas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de tabela `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `fk_clientes_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `distritos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clientes_freguesia` FOREIGN KEY (`freguesia_id`) REFERENCES `freguesias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clientes_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clientes_pais` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clientes_regiao` FOREIGN KEY (`regiao_id`) REFERENCES `regioes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
