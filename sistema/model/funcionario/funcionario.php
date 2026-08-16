<?php // Ver um modo melhor de colocar o endereço o cargo nas tabelas
    // Importações importantes
    require_once 'endereco/Endereco.php';
    require_once 'cargo/Cargo.php';

    // Modelo do funcionario

    enum Status: string {
        case Ativo = "Ativo";
        case Inativo = "Inativo";
        case Demitido = "Demitido";
    }

    class Funcionario 
    {
        private string $cpf;
        private string $nome;
        private DateTime $dataNascimento;
        private DateTime $dataAdmissao;
        private string $telefone;
        private string $email;
        private Status $status;

        // Atributos referentes a chaves estrangeiras
        private Cargo $idCargo;
        private Endereco $idEndereco;

        public function __construct(
            string $cpf,
            string $nome,
            DateTime $dataNascimento,
            DateTime $dataAdmissao,
            string $telefone,
            string $email,
            Status $status,
            Cargo $idCargo,
            Endereco $idEndereco
        ) {
            $this->cpf = $cpf;
            $this->nome = $nome;
            $this->dataNascimento = $dataNascimento;
            $this->dataAdmissao = $dataAdmissao;
            $this->telefone = $telefone;
            $this->email = $email;
            $this->status = $status;
            $this->idCargo = $idCargo;
            $this->idEndereco = $idEndereco;
        }

        public function getCpf(): string {
            return $this->cpf;
        }

        public function setCpf(string $cpf): void {
            $this->cpf = $cpf;
        }

        public function getNome(): string {
            return $this->nome;
        }

        public function setNome(string $nome): void {
            $this->nome = $nome;
        }

        public function getDataNascimento(): DateTime {
            return $this->dataNascimento;
        }

        public function setDataNascimento(DateTime $dataNascimento): void {
            $this->dataNascimento = $dataNascimento;
        }

        public function getDataAdmissao(): DateTime {
            return $this->dataAdmissao;
        }

        public function setDataAdmissao(DateTime $dataAdmissao): void {
            $this->dataAdmissao = $dataAdmissao;
        }

        public function getTelefone(): string {
            return $this->telefone;
        }

        public function setTelefone(string $telefone): void {
            $this->telefone = $telefone;
        }

        public function getEmail(): string {
            return $this->email;
        }

        public function setEmail(string $email): void {
            $this->email = $email;
        }

        public function getStatus(): Status {
            return $this->status;
        }

        public function setStatus(Status $status): void {
            $this->status = $status;
        }

        public function getIdCargo(): Cargo {
            return $this->idCargo;
        }

        public function setIdCargo(Cargo $idCargo): void {
            $this->idCargo = $idCargo;
        }

        public function getIdEndereco(): Endereco {
            return $this->idEndereco;
        }

        public function setIdEndereco(Endereco $idEndereco): void {
            $this->idEndereco = $idEndereco;
        }
    }
?>