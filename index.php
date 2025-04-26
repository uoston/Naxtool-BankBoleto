<?php

// Carrega o autoload do Composer
require_once __DIR__ . '/vendor/autoload.php';

use BankBoleto\BancoDoBrasilBoleto;

// Exemplo de uso
$boleto = new BancoDoBrasilBoleto();
$boleto->setResourcePath(__DIR__); // Caminho para os recursos (CSS, imagens, etc.)
$boleto->setLogotipo('images/bb.jpg'); // Caminho para o logotipo do beneficiário
$boleto->setBeneficiario('Jr10 AlphaVille', '59344009000162', 'Largo Da Baixa de Quintas , Baixa de Quintas', 'Salvador','BA', '40.300-456');
$boleto->setPagador('Uoston Santos de Santana', '82716978549', 'Rua São João, 14, Bairro Dois de Julho', 'Camaçari', 'BA', '42.809-241');
$boleto->setDadosBancarios('64', '3457', '123456', '17');//conta,a agencia, convenio, carteira
$boleto->setDocumento('2025-04-25','2025-04-12', '', '37400440000000045', 0.10,'A',1);//data de vendimento, data do documento, nosso numero, valor, aceite,quantidade
$boleto->setLinhaDigitavel('00190000090374004400700000055178110620000000010');
$boleto->setQrcode('00020101021226900014br.gov.bcb.pix2568qrcodepix.bb.com.br/pix/v2/cobv/32ca611e-4790-416d-923a-3c7af67f3ef652040000530398654040.105802BR5925JR10 ESCOLINHA DE FUTEBOL6008SALVADOR62070503***6304725B');
$boleto->setCodigoBarras('00191106200000000100000003740044000000005517');

// Adicionar demonstrativo e instruções
//$boleto->addDemonstrativo('Pagamento referente a mensalidade de maio/2025');
$boleto->addInstrucao('Após o vencimento, cobrar multa de 2% e juros de 1% ao mês');
//$boleto->addInstrucao('Não receber após 30 dias do vencimento');

// Configurar path para recursos (onde está o CSS, se houver)
$boleto->setResourcePath('');

$html = $boleto->gerarHTML();

echo $html;