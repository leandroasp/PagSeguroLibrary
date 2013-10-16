PagSeguro Library
=================
Biblioteca do PagSeguro em PHP adaptada para o Zend Framwork.

Primeiro passo
--------------
Primeiramente altere o arquivo ```ConfigParams.php``` com suas credenciais do PagSeguro.

PS: A biblioteca deve ficar no diretório das bibliotecas com o nome PagSeguro.

Como usar
---------
Registre o namespace em seu Bootstrap:

```php
Zend_Loader_Autoloader::getInstance()->registerNamespace('PagSeguro_');
```

Inicie a biblioteca, adicione os dados, registre e pronto:

```php
PagSeguro_Library::init();
$paymentRequest = new PagSeguro_PaymentRequest();
$paymentRequest->setCurrency("BRL");
$paymentRequest->addItem(1,'test',1,100,10);

$paymentRequest->setRedirectUrl('http://...');

$credentials = PagSeguro_Config::getAccountCredentials();
$paymentRequest->register($credentials);
```

Para mais detalhes [veja aqui o tutorial](https://pagseguro.uol.com.br/v2/guia-de-integracao/tutorial-da-biblioteca-pagseguro-em-php.html) do pr�prio PagSeguro.

Você verá que só precisará alterar os nomes das classes nos exemplos usados no tutorial do PagSeguro.

Como contribuir
---------------
Fork, code e submit um PULL REQUEST

Licen�ç
-------
It's free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

It's distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with this software. If not, see <http://www.gnu.org/licenses/>.
