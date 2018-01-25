<?php

class systemdata{
    public function __construct(){}



    //Verificar se usuário já existe
    public function get_systemdata(){

            $retorno = array();
        try{
            $sql = "SELECT * FROM systemdata";
            $odb = new db();
            $ret   = $odb->getRows($sql);
            $rows = $odb->rowCount();
            
            if($rows > 0){
                $retorno = $ret;
            }
            else
                $retorno ='No System Data!';

            $odb->Disconnect();
            return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}
