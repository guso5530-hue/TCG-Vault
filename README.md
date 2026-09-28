# TCG-Vault
projeto de site E-Commerce
# 🃏 TCG Vault - E-Commerce de Card Games

O **TCG Vault** é uma plataforma e-commerce focada na venda de cartas, boosters e caixas de Trading Card Games (TCG), como Yu-Gi-Oh!, Pokémon e Magic: The Gathering. O projeto conta com catálogo dinâmico, sistema de autenticação, painel administrativo, carrinho de compras, avaliações de produtos e promoções automatizadas.

---

## 🚀 Funcionalidades

### 🛒 Cliente / Usuário
- **Catálogo de Produtos:** Filtro por categorias, busca por nome/descrição e ordenação por preço e nome.
- **Carrinho de Compras:** Adição de produtos com alteração de quantidade, cálculo de subtotal e total geral.
- **Sistema de Descontos Automáticos:**
  - **35% OFF** de boas-vindas para **novos clientes** (primeira compra).
  - **+15% OFF Extra** acumulativo para produtos da categoria **Yu-Gi-Oh!** (totalizando **50% OFF**).
  - *Nota:* Administradores não recebem desconto automático ao navegar.
- **Favoritos:** Opção de favoritar/desfavoritar produtos.
- **Avaliações com Estrelas:** Clientes logados podem deixar notas (1 a 5 estrelas) e comentários nos produtos, exibindo a média geral.

### 🛡️ Painel Administrativo
- **Gestão de Produtos:** Cadastro, edição e exclusão de itens no catálogo.
- **Gestão de Categorias:** Organização de categorias dos TCGs.

---

## 🛠️ Tecnologias Utilizadas

- **Linguagem:** PHP 8.x
- **Banco de Dados:** MySQL / MariaDB
- **Front-end:** HTML5, CSS3, JavaScript
- **Framework CSS:** Bootstrap 5.3 + Bootstrap Icons
- **Servidor Local:** XAMPP / WAMP / Laragon

---

## 📁 Estrutura de Arquivos

```text
TCG-Vault/
├── conexao.php      # Conexão com o banco de dados MySQL
├── index.php        # Página inicial (Catálogo, buscas e promoções)
├── produto.php      # Detalhes do produto, favoritos e avaliações
├── carrinho.php     # Gerenciamento do carrinho de compras
├── login.php        # Autenticação e cadastro de usuários
├── logout.php       # Encerramento de sessão
├── admin.php        # Painel administrativo
└── img/             # Imagens dos produtos
