
# 🚗 FlexCar - Sistema de Aluguel de Carros

O **FlexCar** é um sistema web de aluguel de carros com login, cadastro, reserva de veículos, painel administrativo e gerenciamento de usuários. Foi desenvolvido em PHP, MySQL, HTML, CSS e JavaScript.

---

## 🧰 Requisitos

- Sistema Operacional: Windows
- Navegador (Chrome, Edge, etc.)
- [XAMPP](https://www.apachefriends.org/index.html)
- Git (opcional)

---

## ⚙️ Como Rodar Localmente com XAMPP

### 🔹 1. Instalar o XAMPP

1. Acesse: https://www.apachefriends.org/
2. Baixe e instale o XAMPP
3. Após a instalação, abra o **XAMPP Control Panel**
4. Clique em **Start** nos serviços **Apache** e **MySQL**

---

### 🔹 2. Copiar os arquivos do projeto

1. Vá até a pasta do XAMPP:
   ```
   C:\xampp\htdocs\
   ```
2. Crie uma nova pasta chamada:
   ```
   SiteAluguelDeCarros
   ```
3. Copie todos os arquivos do projeto FlexCar para dentro dessa pasta, incluindo:
   - index.php, login.php, cadastro.php etc.
   - style.css
   - pasta `img/`
   - banco de dados SQL: `flexcar_banco_completo.sql`

---

### 🔹 3. Configurar o banco de dados

1. Acesse o navegador e vá até:
   ```
   http://localhost/phpmyadmin
   ```
2. Clique em **Nova** (ou "New") no menu à esquerda
3. Crie um banco de dados com nome:
   ```
   aluguel_carros
   ```
4. Clique no banco criado, vá até a aba **Importar**
5. Selecione o arquivo:
   ```
   flexcar_banco_completo.sql
   ```
6. Clique em **Executar**

---

### 🔹 4. Acessar o sistema

Abra o navegador e digite:
```
http://localhost/SiteAluguelDeCarros
```

Você verá a página inicial com os carros disponíveis para reserva.

---

## 👤 Usuários de Teste

Você pode usar os seguintes logins para testar o sistema:

### ✅ Administrador
- E-mail: `admin@flexcar.com`
- Senha: `admin123` *(se foi cadastrada com hash real)*

### ✅ Cliente
- E-mail: `joao@cliente.com`
- Senha: `cliente123`

> 💡 Para alterar ou redefinir senhas manualmente, use o PHP:
```php
echo password_hash("nova_senha", PASSWORD_DEFAULT);
```

---

## 📂 Estrutura do Projeto

- `index.php` — página inicial e catálogo
- `login.php`, `cadastro.php` — autenticação
- `reserva.php`, `salvar_reserva.php` — reservas
- `minhas_reservas.php` — painel do cliente
- `cadastrar_carro.php`, `gerenciar_usuarios.php` — painel do admin
- `conexao.php` — conexão com MySQL
- `img/` — imagens dos veículos

---

## 📋 Observações

- Certifique-se de que o MySQL está ativo no XAMPP
- O sistema foi projetado para fins **educacionais**
- Você pode adaptar a estrutura para servidores reais se desejar

---

## 📄 Licença

Distribuição livre para fins acadêmicos.
