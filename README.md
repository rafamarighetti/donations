## Iniciando Aplicação para desenvolvimento

1 - Navegue para a pasta backend (cmd: cd backend):

2 -  Altere o arquivo .env a e procure por DEV AMBIENT VARIABLE, descomente a url abaixo e comente a que está abaixo de DOCKER AMBIENT VARIABLE

3 - Execute no seu terminal CMD (cmd: docker compose up) para iniciar o banco de dados*

5 - Execute (cmd: php bin/console doctrine:migrations:migrate) para criar as tabelas relacionadas as entidades

6 - Inicie a aplicação executando no seu terminal CMD (cmd: symfony server:start --port=9000)



## Requisitos

* PHP 8.0.10 

* Composer 2.0.5

*Tenha a docker instalada em seu dispositivo e execute inicialize-o antes de executar o comando docker.

BONUS: Abra o PgAdmin em [http://localhost:8090](http://localhost:8090) no seu navegador ou importe a collection do projeto no seu aplicativo (recomendado: Postman)