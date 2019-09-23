<?php 

namespace NFePHP\NFSe\GINFE;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\GINFE
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <marlon.academi at gmail dot com>
 * @link       https://github.com/Focus599Dev/sped-nfsginfe for the canonical source repository
 */

use NFePHP\Common\Strings;
use NFePHP\NFSe\GINFE\Exception\DocumentsException;
use  NFePHP\NFSe\GINFE\Factories\Parser;

class Convert {

	public $txt;
    
    public $dados;
    
    public $numNFs = 1;
    
    public $notas;
    
    public $layouts = [];
    
    public $xmls = [];

    /**
     * Constructor method
     * @param string $txt
    */
    public function __construct($txt = ''){

        if (!empty($txt)) {

            $this->txt = trim($txt);

        }

    }

    /**
     * Convert in XML
     * @param string $txt
     * @return array
     * @throws NFePHP\NFSe\GINFE\Exception\DocumentsException
     */
    public function toXml($txt = ''){

        if (!empty($txt)) {
            $this->txt = trim($txt);
        }
        
        $txt = Strings::removeSomeAlienCharsfromTxt($this->txt);

        if (!$this->isNFSe($txt)) {
            throw DocumentsException::wrongDocument(12, '');
        }

        $this->notas = $this->sliceNotas($this->dados);

        $this->checkQtdNFSe();

        $this->validNotas();

        $i = 0;

        foreach ($this->notas as $nota) {

            $version = $this->layouts[$i];

            $parser = new Parser($version);
            
            $this->xmls[] = $parser->toXml($nota);

            $i++;
        }

        return $this->xmls;
    }

    /**
     * Check if it is an NFSe in TXT format
     * @param string $txt
     * @return boolean
    */
    protected function isNFSe($txt){
        
        if (empty($txt)) {
            throw DocumentsException::wrongDocument(15, '');
        }

        $this->dados = explode("\n", $txt);
        
        $fields = explode('|', $this->dados[0]); 
        
        if ($fields[0] == 'NOTAFISCAL') {
            
            $this->numNFs = (int) $fields[1];
            
            return true;
        }

        return false;
    }

    /**
     * Separate nfse into elements of an array
     * @param  array $array
     * @return array
     */
    protected function sliceNotas($array){

        $aNotas = [];

        $annu = explode('|', $array[0]);

        $numnotas = $annu[1];

        unset($array[0]);

        if ($numnotas == 1) {
            $aNotas[] = $array;
            return $aNotas;
        }

        $iCount = 0;

        $xCount = 0;

        $resp = [];

        foreach ($array as $linha) {

            if (substr($linha, 0, 2) == 'A|') {

                $resp[$xCount]['init'] = $iCount;

                if ($xCount > 0) {
                    $resp[$xCount -1]['fim'] = $iCount;
                }

                $xCount += 1;

            }

            $iCount += 1;
        }

        $resp[$xCount-1]['fim'] = $iCount;

        foreach ($resp as $marc) {

            $length = $marc['fim']-$marc['init'];

            $aNotas[] = array_slice($array, $marc['init'], $length, false);

        }

        return $aNotas;

    }

     /**
     * Verify number of NFSe declared
     * If different throws an exception
     * @throws \NFePHP\NFe\Exception\DocumentsException
     */
    protected function checkQtdNFSe() {
        $num = count($this->notas);

        if ($num != $this->numNFs) {

            throw DocumentsException::wrongDocument(13, '');

        }
    }

    /**
     * Valid all NFSe in txt and get layout version for each nfe
    */
    protected function validNotas() {
        
        foreach ($this->notas as $nota) {

            $this->loadLayouts($nota);

        }
    }

    /**
     * Read and set all layouts in NFSe
     * @param array $nota
    */
    protected function loadLayouts($nota) {
        
        if (empty($nota)) {
            throw DocumentsException::wrongDocument(17, '');
        }

        foreach ($nota as $campo) {
            
            $fields = explode('|', $campo);
            
            if ($fields[0] == 'A') {

                $this->layouts[] = $fields[2];

                break;

            }

        }

    }

}                                                                                                                            

?>