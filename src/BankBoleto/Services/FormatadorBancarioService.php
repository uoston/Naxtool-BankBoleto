<?php

namespace BankBoleto\Services;

/**
 * Classe responsável por realizar formatações de campos bancários
 */
class FormatadorBancarioService {
    
    /**
     * Formatar CPF/CNPJ
     * 
     * @param string $cpfCnpj
     * @return string
     */
    public function formatCpfCnpj($cpfCnpj) {
        $cpfCnpj = preg_replace('/[^0-9]/', '', $cpfCnpj);
        if (strlen($cpfCnpj) == 11) {
            return substr($cpfCnpj, 0, 3) . '.' . substr($cpfCnpj, 3, 3) . '.' . 
                   substr($cpfCnpj, 6, 3) . '-' . substr($cpfCnpj, 9, 2);
        } else {
            return substr($cpfCnpj, 0, 2) . '.' . substr($cpfCnpj, 2, 3) . '.' . 
                   substr($cpfCnpj, 5, 3) . '/' . substr($cpfCnpj, 8, 4) . '-' . 
                   substr($cpfCnpj, 12, 2);
        }
    }
    
    /**
     * Formatar valor monetário
     * 
     * @param float $value
     * @return string
     */
    public function formatMoney( float $value) {             
        if (isset($value)) {            
            return number_format($value, 2, ',', '.');    
        }
        return '';
    }
        /**
     * Formatar Linha Digitável do Banco do Brasil
     * 
     * @param string $linha     
     * @return string
     */
    function formatarLinhaDigitavelBB($linha) {
        // Remove espaços e caracteres não numéricos, se houver
        $linha = preg_replace('/\D/', '', $linha);
    
        if (strlen($linha) !== 47) {
            throw new InvalidArgumentException('Linha digitável deve conter 47 dígitos.');
        }
    
        $campo1 = substr($linha, 0, 5) . '.' . substr($linha, 5, 5);
        $campo2 = substr($linha, 10, 5) . '.' . substr($linha, 15, 6);
        $campo3 = substr($linha, 21, 5) . '.' . substr($linha, 26, 6);
        $campo4 = substr($linha, 32, 1);
        $campo5 = substr($linha, 33, 14);
    
        return "{$campo1} {$campo2} {$campo3} {$campo4} {$campo5}";
    }
}