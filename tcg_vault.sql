-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/09/2026 às 20:55
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `tcg_vault`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nota` int(11) NOT NULL CHECK (`nota` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `data_avaliacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`) VALUES
(1, 'Pokémon TCG'),
(2, 'Yu-Gi-Oh! TCG'),
(3, 'Jogos de Mesa');

-- --------------------------------------------------------

--
-- Estrutura para tabela `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `data_adicionado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `favoritos`
--

INSERT INTO `favoritos` (`id`, `usuario_id`, `produto_id`, `data_adicionado`) VALUES
(2, 3, 7, '2026-09-28 17:53:31'),
(3, 3, 9, '2026-09-28 17:53:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `imagem`, `categoria_id`) VALUES
(1, 'Booster Box Pokémon TCG Coleção Fenda Paradoxal Japonesa', 'Booster box importada do Japão com a coleção Fenda Paradoxal (Paradox Rift).', 760.00, 'fenda.jpg', 1),
(2, 'Lata Box de Pikachu e Zekrom da coleção Sol e Lua – União de Aliados', 'Lata colecionável contendo a dupla lendária GX Aliados de Pikachu e Zekrom.', 160.00, 'Pikachu.jpg', 1),
(3, 'Box Coleção Arena de Batalha: Kyurem Preto vs Kyurem Branco', 'Kit completo de batalhas contendo decks estratégicos de Kyurem Preto e Kyurem Branco.', 380.00, 'Kyurem.webp', 1),
(6, 'Catan: O Jogo de Tabuleiro', 'Jogo de mesa clássico de estratégia, negociação e construção de civilizações.', 299.90, 'catan.webp', 3),
(7, 'Deck Yu-gi-oh: Obelisco o atormetador', 'Deck temático de um dos deuses egípcios mais famosos de Yu-gi-oh, o poderoso OBELISCO O ATORMENTADOR que foi usado pelo famoso Seto Kaiba', 89.90, '6ab419361fcc9.jpg', 2),
(8, 'lata 2025 mega pack', 'A 2025 Mega-Pack Tin é o item de colecionador definitivo para elevares o teu Deck e a tua coleção ao próximo nível!\r\n\r\nEmbalada numa lata metálica de design exclusivo, esta edição reúne as cartas mais populares, poderosas e cobiçadas do último ano. Cada lata contém 3 Mega-Packs recheados com 39 cartas no total, garantindo uma verdadeira chuva de raridades', 239.99, '6ab420826f7e0.jpg', 2),
(9, 'deck estrutural destino de olhos azuis', 'O Deck Estrutural: Destino Branco de Olhos Azuis (Blue-Eyes White Destiny) é o reforço lendário que o dragão mais icónico do Yu-Gi-Oh! precisava para dominar os duelos atuais!\r\n\r\nFocado em estratégias de Invocação-Sincro, este deck traz novas formas devastadoras para invocar o lendário Dragão Branco de Olhos Azuis e as suas variantes mais poderosas. O grande destaque vai para o novo Dragão Sincro de Olhos Azuis, capaz de negar os efeitos das cartas do oponente e proteger os teus monstros no campo.', 69.99, '6ab420d20d401.jpg', 2),
(10, 'UNO: NO MERCY', 'Prepare-se para o teste definitivo de amizades. O UNO: No Mercy (Sem Piedade) eleva o clássico jogo de cartas a um nível completamente insano de competição! Com regras mais severas, penalidades acumulativas de compra de cartas (como o temido +10!) e a regra do massacre — onde quem acumular 25 cartas na mão é eliminado instantaneamente —, esta versão foi feita para quem busca partidas rápidas, estratégicas e cheias de reviravoltas. É a escolha perfeita para reunir os amigos e ver quem realmente domina a mesa sem demonstrar nenhuma misericórdia!', 29.90, '6ab42c058c612.jpg', 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('admin','cliente') DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`) VALUES
(1, 'clthanos', 'bostadevacalo2@gmail.com', '$2y$10$HBj5RX57cqnNSjzFMQtwDetoyklKNsEZbsVDnGbIIVYHOkII.4gjO', 'cliente'),
(2, 'Administrador TCG', 'admin@tcgvault.com', '123', 'admin'),
(3, 'gustavo', 'teurabo@gmail.com', '2009', 'cliente'),
(4, 'BeanPower', 'hydra@gmail.com', '22', 'cliente');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `avaliacoes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avaliacoes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
