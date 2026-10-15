# StockControl | Gestão Inteligente de Estoque

<div align="center">

![Status](https://img.shields.io/badge/STATUS-CONCLU%C3%8DDO-10B981?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

> **Um sistema de gerenciamento de estoque Full Stack focado na organização simplificada, rastreabilidade de produtos e controle rigoroso de entradas e saídas.**

</div>

---

## Sobre o Projeto

O **StockControl** foi desenvolvido como solução para a Situação de Aprendizagem (Full Stack) de Controle de Estoque. O objetivo é evitar perdas, rupturas e auxiliar na tomada de decisão de empresas, garantindo o controle eficiente de produtos e materiais. 

O design foi construído com extrema fidelidade ao protótipo no Figma, utilizando um tema *Dark Mode* moderno com detalhes em roxo, garantindo uma experiência de usuário (UX) fluida e visualmente agradável.

---

## Funcionalidades Principais

### Controle de Acesso e Autenticação
- **Login Seguro:** Validação de credenciais com criptografia de senhas (`password_hash` e `password_verify` do PHP).
- **Níveis de Acesso (RBAC):** 
  * **Administrador:** Acesso total (CRUD completo de produtos, exclusões, movimentações e comentários).
  * **Operador:** Acesso restrito (Visualização, registro de movimentações e adição de comentários).

### Gestão de Produtos (Dashboard)
- **Métricas em Tempo Real:** Cálculo dinâmico de total de unidades, produtos ativos e itens com estoque baixo.
- **Filtros e Busca:** Busca textual por nome/descrição e *toggle* para exibir apenas produtos ativos.
- **Cadastro Completo:** Inserção de produtos com nome, descrição, preço, quantidade, status e **Upload de Imagens**.
- **Ações Rápidas:** Modais integrados para edição e exclusão segura.

### Controle de Movimentações (Entradas e Saídas)
- **Modal Interativo:** Alternância fluida entre "Entrada" e "Saída" sem recarregar a página.
- **Preview de Estoque:** Cálculo instantâneo via JavaScript mostrando como o estoque ficará após a ação.
- **Histórico Completo:** Página dedicada rastreando o "caminho de cada item", detalhando data, hora, tipo (+/-), quantidade, usuário responsável e observações, com filtro por produto.

### Sistema de Comentários (Observações)
- **Histórico Colaborativo:** Cada produto possui uma linha do tempo de observações para comunicação entre a equipe.
- **Renderização Dinâmica:** Modais assíncronos (via `Fetch API`) que carregam os comentários sem *refresh*.
- **Controle de Autoria:** Administradores ou o próprio autor do comentário podem deletar registros.

---

## Estrutura do Banco de Dados (MySQL)

O sistema opera sobre um banco de dados relacional (DER) estruturado da seguinte forma:

1. **`usuarios`**: Gerencia acessos (ID, nome, email, senha criptografada, nível de acesso).
2. **`produtos`**: Catálogo de itens (ID, nome, descrição, quantidade, preço, status, imagem).
3. **`movimentacoes`**: Histórico de estoque ligado aos produtos e usuários (Chaves Estrangeiras).
4. **`comentarios`**: Observações atreladas ao produto e ao autor (Chaves Estrangeiras).

---

## Tecnologias Utilizadas

- **Front-End:** HTML5 Semântico, CSS3 Moderno (CSS Grid, Flexbox, UI/UX baseada no Figma), JavaScript (Vanilla, Fetch API, manipulação de DOM).
- **Back-End:** PHP 8+ (Lógica de negócios, controle de sessão, upload de arquivos).
- **Banco de Dados:** MySQL com acesso seguro via **PDO (PHP Data Objects)**, prevenindo *SQL Injection*.
- **Servidor:** Apache (via XAMPP / WAMP / Laragon).

---

## Estrutura de Arquivos

```bash
StockControl/
├── index.php                 # Tela de Login
├── cadastro.php              # Cadastro de novos usuários
├── esqueci_senha.php         # Recuperação de acesso
├── painel.php                # Dashboard principal e listagem de produtos
├── novo-produto.php          # Formulário de criação de produtos
├── editar-produto.php        # Formulário de edição de produtos
├── movimentacoes.php         # Tela de histórico completo de estoque
│
├── style.css                 # Folha de estilos global (Fidelidade ao Figma)
├── script.sql                # Dump completo da estrutura do Banco de Dados
│
├── conn/                     
│   └── conexao.php           # Configuração de conexão PDO com o MySQL
│
├── uploads/                  # Diretório gerado automaticamente para imagens dos produtos
│
└── rotas_backend/            # Arquivos invisíveis de processamento (Actions)
    ├── adicionar-comentario.php
    ├── buscar-comentarios.php
    ├── excluir-comentario.php
    ├── excluir-produto.php
    ├── registrar-movimentacao.php
    └── logout.php
```
