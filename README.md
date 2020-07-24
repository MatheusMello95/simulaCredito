# Requisitos do servidor:

* *PHP >= 7.2.5*
* *BCMath PHP Extension*
* *Ctype PHP Extension*
* *Fileinfo PHP extension*
* *JSON PHP Extension*
* *Mbstring PHP Extension*
* *OpenSSL PHP Extension*
* *PDO PHP Extension*
* *Tokenizer PHP Extension*
* *XML PHP Extension*

## Passo a passo:
1. Rodar o comando: composer install
2. Rodar o comando: php artisan serve

## Rotas:

### GET: localhost:8000/api/instituicoes
    retorna as instituições cadastradas.

### GET localhost:8000/api/convenios
    retonar os convenios cadastrados.

### POST localhost:8000/api/emprestimos
    Simulação de credito

    * Paramentros:
        * valor_emprestimo: tipo float, obrigatorio
        * instituicoes: tipo array 
        * convenios: tipo array
        * parcela: tipo numerico
