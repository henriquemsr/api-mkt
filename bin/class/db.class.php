<?php
class db
    {
        public $isConnected;
        protected $datab;
		protected $configini;
		public $stmt;
		public $idret;
		public $execcount;

		public $table;
		public $field;

        public function __construct(){
            $this->isConnected = true;
            try {
				$this->datab = new PDO ("mysql:host=localhost;dbname=banco;charset=utf8","root","",array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
				$this->datab->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->datab->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				$this->datab->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				$this->datab->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }
            catch(PDOException $e) {
                $this->isConnected = false;
                throw new Exception($e->getMessage());
            }
        }
        public function Disconnect(){
            $this->datab = null;
            $this->isConnected = false;
        }

        public function getRow($query, $params=array()){
            try{
				$this->stmt = $this->datab->prepare($query);
				$this->stmt->execute($params);
				$this->execcount = $this->stmt->rowCount();
				return $this->stmt->fetch();
			}catch(PDOException $e){
				throw new Exception($e->getMessage());
            }
        }

        public function getRows($query, $params=array()){
            try{
				$this->stmt = $this->datab->prepare($query);
				$this->stmt->execute($params);
				$this->execcount = $this->stmt->rowCount();
				return $this->stmt->fetchAll();
			}catch(PDOException $e){
				throw new Exception($e->getMessage());
            }
        }

		public function rowCount(){
            try{
				return $this->execcount;
			}catch(PDOException $e){
				throw new Exception($e->getMessage());
            }
        }

		public function lastInsertId(){
            try{
				return $this->idret;
			}catch(PDOException $e){
				throw new Exception($e->getMessage());
            }
        }

        public function insertRow($query, $params){
            try{
      				$this->datab->beginTransaction();


      				$this->stmt = $this->datab->prepare($query);
      				$this->stmt->execute($params);
      				$this->idret = $this->datab->lastInsertId();
      				$this->execcount = $this->stmt->rowCount();
      				$this->datab->commit();
      			}catch(PDOException $e){
      				$this->datab->rollBack();
      				throw new Exception($e->getMessage());
            }
        }

        public function updateRow($query, $params){
            return $this->insertRow($query, $params);
        }

        public function deleteRow($query, $params){
            return $this->insertRow($query, $params);
        }

        public function execRow($query){
            try{
				$this->datab->beginTransaction();
				$this->datab->exec($query);
				$this->datab->commit();
			}catch(PDOException $e){
				$this->datab->rollBack();
				throw new Exception($e->getMessage());
            }
        }
    }
