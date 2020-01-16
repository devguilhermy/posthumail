<?php

    require_once 'Crud.php';


    class Cliente extends Crud {
        protected $table = "cliente";
        private $nome;
        private $email;
        private $endereco;

        public function setNome($nome){
            $this->nome = $nome;
        }

        public function setEmail($email){
            $this->email = $email;
        }

        public function setEndereco($endereco){
            $this->endereco = $endereco;
        }

        public function getNome(){
            return $this->nome;
        }

        public function getEmail(){
            return $this->email;
        }

        public function getEndereco(){
            return $this->endereco;
        }

        //public function __construct($nome, $email, $endereco){
        //    $this->nome = $nome;
        //    $this->email = $email;
        //    $this->endereco = $endereco;
        //}
        
        public function insert(){
            $sql = "INSERT INTO $this->table (nome, email, endereco) values (:nome, :email, :endereco)";
            $stmt = DB::prepare($sql);
            $stmt->bindParam(":nome", $this->nome);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":endereco", $this->endereco);
            return $stmt->execute();
        }

        public function update($id){
            $sql = "UPDATE $this->table SET nome = :nome, email = :email, endereco = :endereco WHERE id = :id";
            $stmt = DB::prepare($sql);
            $stmt->bindParam(":nome", $this->nome);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":endereco", $this->endereco);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        }

    }
?>