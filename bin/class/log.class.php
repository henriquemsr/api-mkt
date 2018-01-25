<?php

class log{
    public function __construct(){}

    //CREATE
    public static function log_auth($user){
        $odb = new db();
        $keydata = '';
        $value = '';
        $retorno = array();
        $arrData = array();

            try{
                
                $sql = "INSERT INTO ".__class__."_auth 
                                    (user, token, login, datecreate, status)
                                    VALUES 
                                    (:user, :token, now(), now(), 1)";

                $ret   = $odb->insertRow($sql,  
                                array(":user"=>$user['id'], ":token"=>$user['token']));

                $retid = $odb->lastInsertId();

                if($retid > 0){
                    $retorno = array("message"=>'', "error" =>false, "statuscode"=>201);
                }
                else
                    $retorno = array("message"=>"", "error" =>true, "statuscode"=>204);

                $odb->Disconnect();
                return $retorno;

            } catch (PDOException $e) {

               throw new Exception($e->getMessage());

            } catch (Exception $e) {
               throw new Exception($e->getMessage());
            }
        

    }

    //UPDATE
    public function update($token){

        $odb = new db();
        $keydata = '';
        $value = '';
        $retorno = array();
        $arrData = array();
        try{

            $sql = "UPDATE log_auth set logout=now(),  dateupdate=now() WHERE token = :token";

                    //return $sql;
                    $odb = new db();
                    $ret =  $odb->updateRow($sql, array(":token"=>$token));

                    $retorno = array("message"=>'Dados alterados com sucesso.', "error" =>false, "statuscode"=>200);

                $odb->Disconnect();
                return $retorno;


        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
