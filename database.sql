CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);


/*

USUARIO DE EXEMPLO
senha: senha123

*/

INSERT INTO usuarios (nome, email, senha, created_at, updated_at)
VALUES ('Exemplo Usuario', 'exemplo@usuario.com', '$2y$10$Fy2eWTUImKtcqu3K.TZmL.NZMOFo2vAYi5NrS9UlnRTQdtSeuyhfC', current_timestamp, current_timestamp);
