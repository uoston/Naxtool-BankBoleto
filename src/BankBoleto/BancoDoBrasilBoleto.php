<?php

namespace BankBoleto;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BankBoleto\Services\FormatadorBancarioService;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Exception;

/**
 * Classe para geração de boletos do Banco do Brasil
 * Baseado nas normas do portal do desenvolvedor do Banco do Brasil
 */
class BancoDoBrasilBoleto {
    
    // Informações básicas do banco
    private $codigoBanco = '001';
    private $nomeBanco = 'BANCO DO BRASIL';
    private $logoBanco;
    
    // Dados do beneficiário
    private $beneficiario;
    private $beneficiarioCpfCnpj;
    private $beneficiarioEndereco1;
    private $beneficiarioEndereco2;
    private $agencia;
    private $conta;
    private $convenio;
    private $carteira;
    
    // Dados do pagador
    private $pagador;
    private $pagadorCpfCnpj;
    private $pagadorEndereco;
    private $pagadorCidade;
    private $pagadorEstado;
    private $pagadorCep;
    
    // Dados do documento
    private $dataVencimento;
    private $dataDocumento;
    private $dataProcessamento;
    private $numeroDocumento;
    private $nossoNumero;
    private $quantidade = 1;
    private $valor;
    private $descontoAbatimento = 0;
    private $outrasDeducoes = 0;
    private $outrosAcrescimos = 0;
    private $valorCobrado;
    private $especie = 'REAL';
    private $especieDoc = 'DM';
    private $aceite;
    private $demonstrativo = [];
    private $instrucoes = [];
    
    // Configurações
    private $qrcode = null;
    private $logotipo = null;
    private $resourcePath = '';
    private $codigoBarras = null;
    private $linhaDigitavel = null;
    
    private $formatadorHelper;
    
    public function __construct() {
        $this->logoBanco = 'resources/images/' . 'bb.jpg'; // Caminho para o logo do Banco do Brasil
        //$this->dataDocumento = date('d/m/Y');
        //$this->dataProcessamento = date('d/m/Y');
        $this->formatadorHelper = new FormatadorBancarioService ();
    }
    
    /**
     * Configurar informações do beneficiário
     */
    public function setBeneficiario($nome, $cpfCnpj, $endereco1, $endereco2) {
        $this->beneficiario = $nome;
        $this->beneficiarioCpfCnpj = $this->formatadorHelper->formatCpfCnpj($cpfCnpj);
        $this->beneficiarioEndereco1 = $endereco1;
        $this->beneficiarioEndereco2 = $endereco2;
    }
    
    /**
     * Configurar dados bancários
     */
    public function setDadosBancarios($agencia, $conta, $convenio, $carteira) {
        $this->agencia = $agencia;
        $this->conta = $conta;
        $this->convenio = $convenio;
        $this->carteira = $carteira;
    }
    
    /**
     * Configurar informações do pagador
     */
    public function setPagador($nome, $cpfCnpj, $endereco, $cidade, $estado, $cep) {
        $this->pagador = $nome;
        $this->pagadorCpfCnpj = $this->formatadorHelper->formatCpfCnpj($cpfCnpj);
        $this->pagadorEndereco = $endereco;
        $this->pagadorCidade = $cidade;
        $this->pagadorEstado = $estado;
        $this->pagadorCep = $cep;
    }
    
    /**
     * Configurar dados do documento
     */
    public function setDocumento($dataVencimento,$dataDocumento, $numeroDocumento, $nossoNumero, $valor, $aceite= 'A',$quantidade = 1) {
        $date = date_create($dataVencimento);
        $this->dataVencimento = $date->format('d/m/Y');
        $datadocumento = date_create($dataDocumento);
        $this->dataDocumento = $datadocumento->format('d/m/Y');    
        $this->dataProcessamento = $datadocumento->format('d/m/Y');    
        $this->numeroDocumento = $numeroDocumento;
        $this->nossoNumero = $nossoNumero;
        $this->valor = $valor;
        $this->quantidade = $quantidade ;
        $this->aceite = $aceite;
        $this->valorCobrado = '';
    }
    
    /**
     * Configurar código de barras já gerado
     */
    public function setCodigoBarras($codigoBarras) {
        $this->codigoBarras = $codigoBarras;
    }
    
    /**
     * Configurar linha digitável já gerada
     */
    public function setLinhaDigitavel($linhaDigitavel) {
        $this->linhaDigitavel = $this->formatadorHelper->formatarLinhaDigitavelBB($linhaDigitavel);
    }
    
    /**
     * Adicionar demonstrativo
     */
    public function addDemonstrativo($texto) {
        $this->demonstrativo[] = $texto;
    }
    
    /**
     * Adicionar instrução
     */
    public function addInstrucao($texto) {
        $this->instrucoes[] = $texto;
    }
    
    /**
     * Configurar QR Code
     */
    public function setQRCode($qrcode) {
        $backend  = new \BaconQrCode\Renderer\Image\SvgImageBackEnd;
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(new \BaconQrCode\Renderer\RendererStyle\RendererStyle((int) 250, 0), $backend);
        $writer   = new \BaconQrCode\Writer($renderer);

        $qrCode   = $writer->writeString($qrcode);        

         $imagemBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCode);
         
        $this->qrcode = $imagemBase64;
    }
    
    /**
     * Configurar logotipo do beneficiário
     */
    public function setLogotipo($logoBanco) {
        $this->logotipo = __DIR__. '/../../resources/images/' . $logoBanco;
    }
    
    /**
     * Configurar caminho para recursos (CSS, imagens)
     */
    public function setResourcePath($path) {
        $this->resourcePath = $path;
    }
    
    /**
     * Gerar imagem do código de barras usando BaconQrCode
     */
    public function gerarImagemCodigoBarras() {
        if (!$this->codigoBarras) {
            throw new Exception('Código de barras não definido. Use o método setCodigoBarras().');
        }
    
        $generator = new BarcodeGeneratorSVG();
        // Tipo I25 — "Interleaved 2 of 5", usado para boletos
        $barcodeSvg = $generator->getBarcode($this->codigoBarras, $generator::TYPE_INTERLEAVED_2_5);
    
        return $barcodeSvg;
    }    
    
    /**
     * Gerar HTML do boleto
     */
    public function gerarHTML() {
        $codigoBarrasImage = null;
        
        // Tentar gerar a imagem do código de barras se estiver definido
        if ($this->codigoBarras) {
            try {
                $codigoBarrasImage = $this->gerarImagemCodigoBarras();
                // Converter SVG para base64 para embutir no HTML
                $codigoBarrasBase64 = 'data:image/svg+xml;base64,' . base64_encode($codigoBarrasImage);
                $codigoBarrasImage = $codigoBarrasBase64;
            } catch (Exception $e) {
                // Se houver erro, não exibir imagem
                $codigoBarrasImage = null;
            }
        }
        
        // Garantindo que todas as variáveis necessárias estejam inicializadas
        $data = [
            'resource_path' => $this->resourcePath ?: '',
            'cedente' => $this->beneficiario ?: 'Nome do Beneficiário',
            'cedente_cpf_cnpj' => $this->beneficiarioCpfCnpj ?: '',
            'cedente_endereco1' => $this->beneficiarioEndereco1 ?: '',
            'cedente_endereco2' => $this->beneficiarioEndereco2 ?: '',
            'logotipo' => $this->logotipo ?: '',
            'logo_banco' => $this->logoBanco ?: '',
            'codigo_banco_com_dv' => $this->codigoBanco . '-9',
            'linha_digitavel' => $this->linhaDigitavel ?: '',
            'agencia_codigo_cedente' => ($this->agencia ?: '') . ' / ' . ($this->conta ?: ''),
            'data_vencimento' => $this->dataVencimento ?: '',
            'sacado' => $this->pagador ?: '',
            'pagador_cpf_cnpj' => $this->pagadorCpfCnpj ?: '',
            'pagador_endereco' => $this->pagadorEndereco ?: '',
            'pagador_cidade' => $this->pagadorCidade ?: '',
            'sacado_endereco1' => $this->pagadorEndereco ?: '',
            'sacado_endereco2' => ($this->pagadorCep ?: '') . ' - ' . ($this->pagadorCidade ?: '') . ' - ' . ($this->pagadorEstado ?: ''),
            'pagador_estado' => $this->pagadorEstado ?: '',
            'pagador_cep' => $this->pagadorCep ?: '',
            'numero_documento' => $this->numeroDocumento ?: '',
            'nosso_numero' => $this->nossoNumero ?: '',
            'especie' => $this->especie ?: 'REAL',
            'quantidade' => $this->quantidade,
            'valor_unitario' => '',
            'desconto_abatimento' => $this->descontoAbatimento ?$this->formatadorHelper->formatMoney($this->descontoAbatimento): '',
            'valor_documento' => $this->formatadorHelper->formatMoney($this->valor ?: 0),
            'outras_deducoes' => $this->outrasDeducoes ?$this->formatadorHelper->formatMoney($this->outrasDeducoes): '',
            'outros_acrescimos' => $this->outrosAcrescimos ?$this->formatadorHelper->formatMoney($this->outrosAcrescimos): '',
            'valor_cobrado' => $this->valorCobrado ?$this->formatadorHelper->formatMoney($this->valorCobrado ):'',
            'demonstrativo' => array_pad($this->demonstrativo ?: [], 5, ''),
            'qrcode' => $this->qrcode ?: null,
            'pagamento_minimo' => null,
            'codigo_barras' => $this->codigoBarras ?: null,
            'codigo_barras_image' => $codigoBarrasImage,
            'data_documento' => $this->dataDocumento ?: '',
            'data_processamento' => $this->dataProcessamento ?: '',
            'especie_doc' => $this->especieDoc ?: '',
            'aceite' => $this->aceite ,
            'carteira' => $this->carteira ?: '',
            'instrucoes' => $this->instrucoes ?: [],
            'mora_multa'=>'',
            'imprime_instrucoes_impressao' => $this->instrucoes ? '1' : '0',
            'esconde_uso_banco' => false,
            'uso_banco'=>'',
            'mostra_cip'=>false,
            'sacador_avalista'=> '',
            'instrucoes' => array( // Até 8
                $this->instrucoes[0]??'',
                $this->instrucoes[1]??'',
                $this->instrucoes[2]??'',
                $this->instrucoes[3]??'',
                $this->instrucoes[4]??'',
                $this->instrucoes[5]??'',
                $this->instrucoes[6]??'',
                $this->instrucoes[7]??'',
            ),
            'local_pagamento' => 'PAGÁVEL EM QUALQUER BANCO ATÉ O VENCIMENTO'
        ];
        
        ob_start();
        extract($data);  // Isso extrai as variáveis do array para o escopo local
        //include __DIR__ . '/template.phtml';
        include __DIR__ . '/../../resources/views/bancodobrasil.phtml';
        
        return ob_get_clean();
    }
}