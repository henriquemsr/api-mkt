<?php

class users{
    public function __construct(){}

    //Verificar se usuário já existe
    public function check_user($email){
        try{
            $retorno = false;
            $sql = "SELECT a.email FROM users as a WHERE a.email = :email";
            $odb = new db();
            $ret   = $odb->getRows($sql, array(":email" => $email));
            $retid = $odb->rowCount();
            if($retid > 0){
                $retorno = true;
            }else{
                $retorno = false;
            }
            $odb->Disconnect();
            return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function check_user_tipo($email){
        try{

            $odb = new db();

            if($this->check_user($email)){

              $sql = "SELECT t.token
                      FROM intoken as t
                      INNER JOIN users as u
                      ON u.id = t.inusers
                      WHERE u.email = :email ";

              $ret   = $odb->getRows($sql, array(":email" => $email));

              if(count($ret) > 0){

                $data = $this->get_user($ret[0]['remember_token']);
                  $retorno = array("message"=>$data, "error" =>false);
              }

                //
            }
            else {
              # code...
              $retorno = array("message"=>false, "error"=>true);
            }


            $odb->Disconnect();
            return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Verificar se usuário já existe
    public function list_user(){
        try{
            $retorno = false;
            $sql = "SELECT * FROM users";
            $odb = new db();
            $ret   = $odb->getRows($sql);

            $retorno = array("message"=>$ret, "error" =>false);

            $odb->Disconnect();
            return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    //Verificar se usuário já existe
    public function listbyperfil($id){
        try{
            $retorno = false;
            $odb = new db();
            $sql = "SELECT * FROM users WHERE perfil = :id";

            $ret   = $odb->getRows($sql, array(":id" => $id));

            $retorno = array("message"=>$ret, "error" =>false, "statuscode"=>200);

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

            $sql = "SELECT id, name, email,  perfil,  datecreate, dateupdate, status
                    FROM ".__class__." as a
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

    //Verificar se usuário já existe
    public function list_user_row($id, $status = true){
        $retorno = array("message"=>'Erro ao pegar informações do usuário!', "error" =>true, "statuscode"=>409);
        try{
            $retorno = false;
            $sql = "SELECT id, name,  email,perfil, datecreate, dateupdate, status
                    FROM users WHERE id=:id";

            if($status) $sql .= " AND status > 0 \r\n";
            $sql .= " ORDER by status DESC, id ASC";

            $odb = new db();
            $ret   = $odb->getRow($sql, array(":id" => $id));

            if(count($ret) > 0 & $ret != false){
              $retorno = array("message"=>$ret, "error" =>false, "statuscode"=>200);
            }
            else{
              $retorno = array("message"=>'Nenhum usuário encontrado ou Usuário inativo!', "error" =>true, "statuscode"=>204);
            }

            $odb->Disconnect();
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $retorno;
    }

    ////REMEMBER_PASS
    public function remember_pass($email, $cod){

        try{

                if($this->check_user($email)){
                    $odb = new mail();
                    $sendMail = $odb->remember_pass($email, $cod);

                    //$odb->Disconnect();
                    $retorno = array("message"=>'Uma mensagem foi enviada para o e-mail informado. Por favor acesse-o para criar uma nova senha.', "error" =>false);


                }
                else
                {
                    $retorno = array("message"=>'E-mail incorreto ou inexistente!', "error" =>true);
                }



                return $retorno;

            } catch (PDOException $e) {

               throw new Exception($e->getMessage());

            } catch (Exception $e) {
               throw new Exception($e->getMessage());
            }

    }

    //remove
    public function remove($id){
        $retorno = array("message"=>"Não foi póssivel remover o usuário!", "error" =>true);

            try{


                $sql = "DELETE FROM users WHERE id = :id";
                $odb = new db();
                $ret   = $odb->deleteRow($sql,
                       array(":id" => $id));

                //if($retid > 0){

                    $retorno = array("message"=>"Cadastro removido com sucesso!", "error" =>false);
                //}

                $odb->Disconnect();
                return $retorno;

            } catch (PDOException $e) {

               throw new Exception($e->getMessage());

            } catch (Exception $e) {
               throw new Exception($e->getMessage());
            }
    }

    //remove
    public function removeemaildownload($id){
        $retorno = array("message"=>"Não foi póssivel remover o e-mail!", "error" =>true);

            try{


                $sql = "DELETE FROM emails_downloads WHERE id = :id";
                $odb = new db();
                $ret   = $odb->deleteRow($sql,
                       array(":id" => $id));

                //if($retid > 0){

                    $retorno = array("message"=>"E-mail removido com sucesso!", "error" =>false);
                //}

                $odb->Disconnect();
                return $retorno;

            } catch (PDOException $e) {

               throw new Exception($e->getMessage());

            } catch (Exception $e) {
               throw new Exception($e->getMessage());
            }
    }

    //Criar usuário
    public function create_user($name, $email, $perfil, $password = false){
        $retorno = array("message"=>"Não foi póssivel realizar o seu cadastro!", "error" =>true);

        $password = isset($user['password'])?$password:$this->generatePassword(5, false, true);

        if (!$this->check_user($email)){
            try{
                $pass = functions::hashSSHA($password);
                $sql = "INSERT INTO users (name, email,  password, passwordview, perfil, hash, datecreate, dateupdate, status)
                                  VALUES (:name, :email, :password, :passwordview, :perfil, :hash, now(), now(), 1)";
                $odb = new db();
                $ret   = $odb->insertRow($sql,
                       array(":name" => $name,":email" => $email,  ":passwordview"=>$password, ":password" => $pass["encrypted"], ":perfil" => $perfil, ":hash"=>$pass["salt"]));
                       $retid = $odb->lastInsertId();

                if($retid > 0){
                      $retorno = array("message"=>'Usuário cadastrado com sucesso!', "id"=>$retid, "error" =>false);
                }
                else {
                  $retorno = array("message"=>'Falha ao cadastrar o usuário!', "error" =>true);
                }


                $odb->Disconnect();
                return $retorno;

            } catch (PDOException $e) {

               throw new Exception($e->getMessage());

            } catch (Exception $e) {
               throw new Exception($e->getMessage());
            }
        }else{
            return array("message"=>"Email já existente!", "error" =>true);
        }
    }

    public function generatePassword($tamanho = 8, $maiusculas = true, $numeros = true, $simbolos = false){

      // Caracteres de cada tipo
        $lmin = 'abcdefghjkmnpqrstuvwxyz';
        $lmai = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
        $num = '23456789';
        $simb = '!@#$%*-';
        // Variáveis internas
        $retorno = '';
        $caracteres = '';
        // Agrupamos todos os caracteres que poderão ser utilizados
        $caracteres .= $lmin;
        if ($maiusculas) $caracteres .= $lmai;
        if ($numeros) $caracteres .= $num;
        if ($simbolos) $caracteres .= $simb;
        // Calculamos o total de caracteres possíveis
        $len = strlen($caracteres);
        for ($n = 1; $n <= $tamanho; $n++) {
        // Criamos um número aleatório de 1 até $len para pegar um dos caracteres
        $rand = mt_rand(1, $len);
        // Concatenamos um dos caracteres na variável $retorno
        $retorno .= $caracteres[$rand-1];
        }
        return $retorno;

    }

    //UPDATE
    public function update($dados){

        $odb = new db();
        $keydata = '';
        $value = '';
        $retorno = array();
        $arrData = array();

        $pass = '';
        $email = '';
        reset($dados);
        while (list($key, $val) = each($dados)) {

            if($key == 'password'){

                if(!empty($val) || $val != ''){
                    $pass = functions::hashSSHA($val);
                    $keydata .= "$key=:$key, ";
                    $keydata .= "hash=:hash, ";
                    $arrData += array( ':'.$key.''=>''.$pass["encrypted"].'');
                    $arrData += array( ':hash'=>''.$pass["salt"].'');
                }


            }
            else if($key == 'email'){
                $email = $val;
                $keydata .= "$key=:$key, ";
                $arrData += array( ':'.$key.''=>''.$val.'');
            }
            else if($key == 'id'){
                $id = $val;
                $where = "$key = :$key";
                $arrData += array( ':'.$key.''=>''.$val.'');
            }else{
                $keydata .= "$key=:$key, "; // name input
                $arrData += array( ':'.$key.''=>''.$val.'');
            }


        }


        $retorno = array();
        try{

            $sql = "UPDATE ".__class__." set $keydata dateupdate=now() WHERE $where";

            //return $sql;
            $odb = new db();
            $ret =  $odb->updateRow($sql, $arrData);


            $odb->Disconnect();
            return $this->list_user_row($id);
            //return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return array("message"=>"Erro ao atualizar dados.", "error" =>true, "statuscode"=>409);
    }

    //New Pass
    public function changepassword($dados){

        reset($dados);
        $email = $dados['login'];
        $senha = $dados['password'];

        $retorno = array();
        try{
            $pass = functions::hashSSHA($senha);
            $sql = "UPDATE users set password=:usersPassword, hash=:usersHash, dateupdate=now()
                    WHERE email = :email";


                    $odb = new db();
                    $ret =  $odb->updateRow($sql, array(":usersPassword"=>$pass["encrypted"], ":usersHash" => $pass["salt"],  ":email"=>$email));


                    //if(count($ret) > 0){
                        $retorno = array("message"=>'Senha alterada com sucesso.', "error" =>false, "statuscode"=>200);
                      /*}
                      else{
                        $retorno = array("message"=>'Nenhum usuário encontrado!', "error" =>true, "statuscode"=>204);
                      }*/

                $odb->Disconnect();
                return $retorno;


        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //New Pass App
    public function new_pass_app($email, $senha, $oldPass){

        $isPass = $this->passwordIsvalid($email, $oldPass);

        if($isPass){

          $retorno = array();
          try{
              $pass = functions::hashSSHA($senha);
              $sql = "UPDATE users set usersPassword=:usersPassword, usersHash=:usersHash, usersDataUpdate=now()
                      WHERE usersEmail = :email";


                      $odb = new db();
                      $ret =  $odb->updateRow($sql, array(":usersPassword"=>$pass["encrypted"], ":usersHash" => $pass["salt"],  ":email"=>$email));


                      $retorno = array("message"=>"Senha alterada com sucesso: ", "error" =>false);


                  $odb->Disconnect();



          } catch (PDOException $e) {
              throw new Exception($e->getMessage());
          } catch (Exception $e) {
              throw new Exception($e->getMessage());
          }

        }
        else {
          $retorno = array("message"=>"Senha inválida! ", "error" =>true);
        }

          return $retorno;
    }

    //Obter usuário
    public function get_user($token, $type=false){
        $retorno = array();
        try{
            $sql = "SELECT t.token,
                    u.*
                    FROM intoken as t
                    INNER JOIN users as u
                    ON u.id = t.inusers
                    WHERE t.token = :token
                    AND t.state = 1";
                    //AND u.state = 1


            $odb = new db();
            $ret   = $odb->getRow($sql, array(":token" => $token));

            $retid = $odb->rowCount();
            if($retid > 0){
                $retorno = $ret;
            }
            else {
              $retorno = 'Houve uma falha, talvez seja necessário atualizar a página!';
            }
            $odb->Disconnect();
            return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Conectar usuário
    public function connect($email, $password){
        $retorno = array("message"=>"Usuário ou senha incorretos!", "error" =>true);
        try{
            $sql = "SELECT a.* FROM users as a WHERE a.email = :email";
            $odb = new db();
            $ret   = $odb->getRow($sql, array(":email" => $email));
            $retid = $odb->rowCount();

            if($retid == 1){

                $dpassword = $ret['password'];
                $idusers = $ret['id'];
        				$hash = $ret['hash'];
        				$vpassword = functions::checkhashSSHA($hash, $password);

                $sql = "SELECT * FROM intoken WHERE inusers = :id";
                $resLogin   = $odb->getRow($sql, array(":id" => $idusers));
                $numRow = $odb->rowCount();

                /*if($numRow > 0){
                  $emaill = new mail();
                  $res = $emaill->mail_logof($email, $resLogin['inusers']);
                  $retorno = array("message"=>"Este usuário já está logado. Uma mensagem foi enviada para o e-mail informado para que o Logout seja realizado e você possa acessar novamente o sistema!", "error" =>true);
                }
				        else{*/
                  if ($vpassword == $dpassword){
                      $tokenret = $this->gettoken($idusers);
                      if ($tokenret == ""){
                          $token = functions::generatetoken($email);
                          $tokenbase = functions::hashSSHA($token);
                          $sql = "INSERT INTO intoken (token,inusers,hash,state,dttoken) VALUES (:token,:inusers,:hash, 1,now())";
                          $ret = $odb->insertRow($sql, array(":token" => $tokenbase["encrypted"],":inusers" => $idusers,":hash" => $tokenbase["salt"]));
                          if($odb->execcount > 0){
                                $retorno = array("message"=>$this->get_user($tokenbase["encrypted"], true), "error" =>false);
                          }
                      }else{
                            $retorno = array("message"=>$this->get_user($tokenret, true), "error" =>false);
                      }
                  }else{
                      $retorno = array("message"=>"Senha incorreta!", "error" =>true);
                  }
                //}
            }

            $odb->Disconnect();
            return $retorno;

        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    //Conectar usuário
    public function passwordIsvalid($email, $password){
        $retorno = array("message"=>"Usuário ou senha incorretos!", "error" =>true);
        try{
            $sql = "SELECT a.id, a.email, a.password, a.hash FROM users as a WHERE a.email = :usersEmail AND a.status < 2";
            $odb = new db();
            $ret   = $odb->getRow($sql, array(":usersEmail" => $email));
            $retid = $odb->rowCount();

            if($retid == 1){

                $dpassword = $ret['usersPassword'];
                $hash = $ret['hash'];
        				$vpassword = functions::checkhashSSHA($hash, $password);


				        if ($vpassword == $dpassword){
                    return true;
                }else{
                    return false;
                }
            }
            $odb->Disconnect();
            return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    //Desconectar usuário
    public function disconect($token){
        $retorno = array("message"=>"Não foi póssivel sair do sistema!", "error" =>true);
        try{
            $odb = new db();
            $query = "DELETE FROM intoken WHERE token = :token AND state = 1";
            $ret   = $odb->deleteRow($query, array(":token"  => $token));
            $retid = $odb->rowCount();
            if($retid > 0){
                $retorno = array("message"=>"Você foi desconectado!", "error" =>false);
            }
            $odb->Disconnect();
            return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Verificar se token está conectado.
    public function isvalid($token){
        $retorno = false;
        try{
            $sql = "SELECT t.token FROM intoken as t WHERE t.token = :token AND t.state = 1";
            $odb = new db();
            $ret   = $odb->getRows($sql, array(":token" => $token));
            $retid = $odb->rowCount();

            if($retid > 0){
                $retorno = true;
            }
            $odb->Disconnect();
            return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function gettoken($id){
         $retorno = "";
        try{
            $sql = "SELECT t.token FROM intoken as t WHERE t.inusers = :inusers AND t.state = 1";
            $odb = new db();
            $ret   = $odb->getRow($sql, array(":inusers" => $id));
            $retid = $odb->rowCount();
            if($retid > 0){
                $retorno = $ret["token"];
            }
            $odb->Disconnect();
            return $retorno;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function check_emails($usrs){
        return array_map(function($usr){
            if (!filter_var($usr['email'], FILTER_VALIDATE_EMAIL)) {
                $usr['error'] = "E-mail inválido!";
            }else{
                // VALIDAR SERVIDOR AQUI
            }
            return $usr;
        }, $usrs);
    }

    //Criar usuário
    public function add_user($user){
        reset($user);

        $retorno = array("statuscode"=>409, "message"=>"Não foi póssivel realizar o cadastro!", "error" =>true);

        $password = isset($user['password'])?$user['password']:$this->generatePassword(5, false, true);

        if (!$this->check_user($user['email'])){
            try{
                $pass = functions::hashSSHA($password);
                $user['passwordview'] = $password;
                $user['password'] = $pass['encrypted'];
                $user['hash'] = $pass['salt'];
                $user['email'] = $user['email'];


                $keydata = '';
                $value = '';
                $arrData = array();
                $odb = new db();
                while (list($key, $val) = each($user)) {



                    if($key !='users'){

                        if($key =='type')$type = true;
                        $keydata .= "$key".","; // name input
                        $value .= ":$key".","; // value input

                        $arrData += array( ':'.$key.''=>''.$val.'');

                    }
                    else $company = $val;


                }

                $sql = "INSERT INTO ".__class__."
                        ($keydata datecreate, dateupdate, status)
                        VALUES
                        ($value now(), now(), 1)";


                $ret   = $odb->insertRow($sql,  $arrData);
                $retid = $odb->lastInsertId();

                if($retid > 0){

                    if($type){
                        $sql = "INSERT INTO users_events
                            (event, users, datecreate, dateupdate, status)
                            VALUES
                            (:event, :users,  now(), now(), 1)";
                            $ret   = $odb->insertRow($sql,  array(":event"=>$company, ":users"=>$retid));

                    }
                    $retorno = array("statuscode"=>201, "message"=>'Usuário cadastrado com sucesso!', "id"=>$retid, "error" =>false);
                }
                else {
                    $retorno = array("statuscode"=>409, "message"=>'Falha ao cadastrar o usuário!', "error" =>true);
                }


                $odb->Disconnect();
            } catch (PDOException $e) {

               throw new Exception($e->getMessage());

            } catch (Exception $e) {
               throw new Exception($e->getMessage());
            }
            return $retorno;

        }else{
            return array("statuscode"=>205, "message"=>"Email já existente!", "error" =>true);
        }
    }

    //DELETE
    public function delete($id){

        $odb = new db();
        $arrData = array();
        $retorno = array();

        //CHECK USER
        $isuser = $this->list_user_row($id);

        if($isuser['statuscode'] == 200){

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
            $retorno = array("message"=>'Usuário não encontrado', "error" =>true, "statuscode"=>204);


        return $retorno;

    }
}
