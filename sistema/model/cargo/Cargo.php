<?php
    // Modelo dos cargos

    class Cargo 
    {
        private string $nome;

        public function __construct(string $nome)
        {
            $this->nome = $nome;
        }

        public function getNome(): string {
            return $this->nome;
        }

        public function setNome(string $nome): void {
            $this->nome = $nome;
        }
    }
?>