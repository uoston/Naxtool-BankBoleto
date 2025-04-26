# BankBoleto

## Descrição
Este projeto é responsável pela geração de boletos bancários, com suporte a múltiplos bancos, incluindo Banco do Brasil, Caixa, Itaú, entre outros.

## Estrutura do Projeto
- **src/BankBoleto/**: Contém as classes principais para geração de boletos.
- **resources/**: Contém os recursos estáticos, como imagens e templates.
- **vendor/**: Dependências gerenciadas pelo Composer.

## Requisitos
- PHP 7.4 ou superior
- Composer

## Instalação
1. Clone o repositório:
   ```bash
   git clone <url-do-repositorio>
   ```
2. Instale as dependências:
   ```bash
   composer install
   ```

## Uso
Para integrar este projeto em um sistema maior, adicione o seguinte ao seu `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "caminho/para/o/projeto/boleto"
        }
    ],
    "require": {
        "bankboleto/boleto-teste": "*"
    }
}
```

Em seguida, execute:
```bash
composer update
```

## Integração

Para integrar este projeto em um sistema maior, adicione o seguinte ao seu `composer.json` do projeto principal:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/seu-usuario/boleto"
        }
    ],
    "require": {
        "bankboleto/boleto": "^1.0"
    }
}
```

Em seguida, execute:
```bash
composer update
```

Certifique-se de que o autoload do Composer esteja configurado no projeto principal para carregar as classes do pacote.

## Autores
- Uoston

## Licença
Este projeto está licenciado sob a licença MIT.