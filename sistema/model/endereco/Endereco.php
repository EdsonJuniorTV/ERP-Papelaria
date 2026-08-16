<?php
    // Modelo da tabela Endereço
    class Endereco
    {
        private string $logradouro;
        private string $numero;
        private string $complemento;
        private string $bairro;
        private string $cidade;
        private string $estado;
        private string $cep;

        public function __construct(
            string $logradouro,
            string $numero,
            string $complemento,
            string $bairro,
            string $cidade,
            string $estado,
            string $cep
        ) {
            $this->logradouro = $logradouro;
            $this->numero = $numero;
            $this->complemento = $complemento;
            $this->bairro = $bairro;
            $this->cidade = $cidade;
            $this->estado = $estado;
            $this->cep = $cep;
        }

        public function getEnderecoCompleto(): string 
        {
            return "{$this->logradouro}, {$this->numero} - {$this->complemento} \n
            {$this->bairro} \n {$this->cidade}, {$this->estado}, {$this->cep}";
        }

        // Ver como seria feito para armazenar as informações
    }
?>