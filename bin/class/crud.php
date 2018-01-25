<?php

class crud{
    public function __construct(){

    }

    //CREATE
    public function create($dados){
        $odb = new db();
        $keydata = '';
        $value = '';
        $retorno = array();
        $arrData = array();

        reset($dados);
        while (list($key, $val) = each($dados)) {

            $keydata .= "$key".","; // name input
            $value .= ":$key".","; // value input

            $arrData += array( ''.$key.''=>''.$val.'');
        }

            try{

                $sql = "INSERT INTO ".__class__."
                                    ($keydata datecreate, dateupdate, status)
                                    VALUES
                                    ($value now(), now(), 1)";

                $ret   = $odb->insertRow($sql,  $arrData);
                $retid = $odb->lastInsertId();

                if($retid > 0){
                    $retorno = array("message"=>'Dados cadastrados com sucesso', "id"=>$retid, "error" =>false, "statuscode"=>201);
                }
                else
                    $retorno = array("message"=>"Erro ao cadastrar. Tente novamente.", "error" =>true, "statuscode"=>204);

                $odb->Disconnect();
                return $retorno;

            } catch (PDOException $e) {

               throw new Exception($e->getMessage());

            } catch (Exception $e) {
               throw new Exception($e->getMessage());
            }


    }

    //ListRows
    public function listrows($status = true){
       $retorno = array();
        try{

            $sql = "SELECT * FROM ".__class__." as a
                    WHERE 1 \r\n";
            if($status) $sql .= "AND a.status > 0 \r\n";
            $sql .= "ORDER by a.status DESC, a.id ASC";

            $odb = new db();
            $ret = $odb->getRows($sql);
            $retid = $odb->rowCount();

            $res = array();

            if($retid > 0){
                 $retorno =  array("message"=>$ret, "error" =>false, "statuscode"=>200);
            }
            else
                $retorno =  array("message"=>'Nenhum dado encontrado', "error" =>true, "statuscode"=>204);
                $odb->Disconnect();
            return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //ListRow
    public function listrow($id, $status = true){
       $retorno = array();
        try{

            $sql = "SELECT * FROM ".__class__." as a
                    WHERE
                    a.id = :id \r\n";
            if($status) $sql .= "AND a.status > 0 \r\n";
            $sql .= "ORDER by a.status DESC, a.id ASC";

            $odb = new db();
            $ret = $odb->getRow($sql, array("id"=>$id));
            $retid = $odb->rowCount();

            $res = array();

            if($retid > 0){
                 $retorno =  array("message"=>$ret, "error" =>false, "statuscode"=>200);
            }
            else
                 $retorno =  array("message"=>'Nenhum dado encontrado', "error" =>true, "statuscode"=>204);

            $odb->Disconnect();
            return $retorno;

        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //UPDATE
    public function update($dados){

        $odb = new db();
        $keydata = '';
        $val = '';
        $retorno = array();
        $arrData = array();

        //reset($dados);
        reset($dados);
          while (list($key, $val) = each($dados)) {


              if($key == 'id')
                  $where = "$key = :$key";
              else{
                  $keydata .= "$key=:$val, "; // name input
              }

              $arrData += array( ':'.$key.''=>''.$val.'');
          }

          $retorno = array();
        try{

            $sql = "UPDATE ".__class__." set $keydata dateupdate=now() WHERE $where";

                    //return $sql;
                    $odb = new db();
                    $ret =  $odb->updateRow($sql, $arrData);

                    $retorno = array("message"=>'Dados alterados com sucesso.', "error" =>false, "statuscode"=>200);

                $odb->Disconnect();
                return $retorno;


        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //DELETE
    public function delete($id){

        $odb = new db();
        $arrData = array();
        $retorno = array();

        //CHECK USER
        $isclient = $this->listrow($id);

        if($isclient['statuscode'] == 200){

            $where = "id = :id";
            $keydata = "status=:status, ";
            $arrData += array(':id'=>''.$id.'');
            $arrData += array( ':status'=>0);

            try{

                $sql = "UPDATE ".__class__." set $keydata dateupdate=now() WHERE $where";

                //return $sql;
                $odb = new db();
                $ret =  $odb->updateRow($sql, $arrData);

                $retorno = array("message"=>'Dados removidos com sucesso.', "error" =>false, "statuscode"=>200);

                $odb->Disconnect();



            } catch (PDOException $e) {
                throw new Exception($e->getMessage());
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

        }
        else
            $retorno = array("message"=>'Não encontrado', "error" =>true, "statuscode"=>204);


        return $retorno;

    }
}
