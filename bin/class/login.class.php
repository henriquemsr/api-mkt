<?php


class login{
    public function __construct(){}


    //Verificar se usuário já existe
    public function check_user($email){
        try{
            $retorno = false;
            $sql = "SELECT a.email FROM users as a WHERE a.email = :usersEmail AND a.status < 2";
            $odb = new db();
            $ret   = $odb->getRows($sql, array(":usersEmail" => $email));
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
                      INNER JOIN tb_users as u
                      ON u.usersId = t.inusers
                      WHERE u.usersEmail = :usersEmail ";

              $ret   = $odb->getRows($sql, array(":usersEmail" => $email));

              if(count($ret) > 0){

                $data = $this->get_user($ret[0]['token']);
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


    public function rememberpass($email){

         $odb = new db();
         $retorno = array("message"=>"Dados inválidos ou usuário não cadastrado! ", "error" =>true);

           try{

            $sql = "SELECT * FROM users as a WHERE a.email LIKE :email AND a.status =1 LIMIT 1";

            $ret   = $odb->getRow($sql, array(":login" => $email));
            $row = $odb->rowCount();

            if($row == 1){

                $maill = new mail();
                $send = $maill->mail_send_password($ret['name'], $ret['login'], $ret['email'], $ret['password_view']);

                if($send)
                    $retorno = array("message"=>"Seus dados de acesso foram enviados para o seu e-mail.", "error" =>true);
                else {
                    $retorno = array("message"=>"Dados inválidos ou usuário não cadastrado! ", "error" =>true);
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

    public function changepass($id, $password){

         $odb = new db();
         $retorno = array("message"=>"A Senha não foi alterada. Tente novamente.", "error" =>true);

           try{

            $sql = "SELECT * FROM users as a WHERE a.id LIKE :id AND a.status =1 LIMIT 1";

            $ret   = $odb->getRow($sql, array(":id" => $id));
            $row = $odb->rowCount();

            if($row == 1){

                $idusers =  $ret['id'];
                //$hash = $ret['hash'];

                $pass = functions::hashSSHA($password);
                $sql = "UPDATE users set password=:password, password_view=:password_view, hash=:usersHash
                    WHERE id = :id";


                    $odb = new db();
                    $ret =  $odb->updateRow($sql, array(":password"=>$pass["encrypted"], ":password_view"=>$password, ":usersHash" => $pass["salt"],  ":id"=>$id));


                    $retorno = array("message"=>"Senha alterada com sucesso: ", "error" =>false);


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
    public function disconnect_mobile($token){
        $retorno = array("message"=>"Não foi póssivel sair do sistema! ", "error" =>true,"statuscode"=>204);

        $logoff = $this->disconnect($token);

        if($logoff['error'] == false){
            $logoutauth = new log();
            $update = $logoutauth->update($token);
            $retorno = array("message"=>"Você foi desconectado.", "error" =>false, "statuscode"=>200);
        }


        return $retorno;
    }

    public function disconnect_web($token){
        $retorno = array("message"=>"Não foi póssivel sair do sistema! ", "error" =>true,"statuscode"=>204);

        $logoff = $this->disconnect($token);

        if($logoff['error'] == false){
            $logoutauth = new log();
            $update = $logoutauth->update($token);
            $retorno = array("message"=>"Você foi desconectado.", "error" =>false, "statuscode"=>200);
        }


        return $retorno;
    }

    //Conectar usuário
    public function connect_mobile($login, $password){
        $odb = new db();
        date_default_timezone_set('America/Sao_Paulo');
        $retorno = array("message"=>"Dados inválidos ou usuário não cadastrado! ", "error" =>true);

        try{

            $sql = "SELECT * FROM users as a WHERE a.email LIKE :email AND a.status >0 LIMIT 1";

            $ret   = $odb->getRow($sql, array(":email" => $login));
            $row = $odb->rowCount();

            if($row == 1){

                $dpassword = $ret['password'];
                $idusers = $ret['id'];
                $hash = $ret['hash'];
                $vpassword = functions::checkhashSSHA($hash, $password);

                if ($vpassword == $dpassword){
                    $tokenret = $this->gettoken($idusers);

                    if ($tokenret == ""){

                        $token = functions::generatetoken($login);
                        $tokenbase = functions::hashSSHA($token);

                        $sql = "INSERT INTO intoken (token,inusers,hash,state,dttoken) VALUES (:token,:inusers,:hash, 1,now())";
                        $ret = $odb->insertRow($sql, array(":token" => $tokenbase["encrypted"],":inusers" => $idusers,":hash" => $tokenbase["salt"]));
                        if($odb->execcount > 0){
                            $retorno = array("message"=>$this->get_user($tokenbase["encrypted"], 'users', true), "error" =>false);

                        }

                        $log = new log();
                        $update = $log->log_auth($this->get_user($tokenbase["encrypted"],'users', true));

                    }else{


                        $retorno =  array("message"=>$this->get_user($tokenret,'users', true), "error" =>false);


                        $log = new log();
                        $update = $log->log_auth($this->get_user($tokenret,'users', true));





                    }

                }else{
                    $retorno = array("message"=>"Os dados não conferem", "error" =>true);

                }
            }
            else
            {

                $retorno = array("message"=>"Seu cadastro ainda não foi liberado. Tente novamente em outro momento. Obrigado.", "error" =>true);
            }


            $odb->Disconnect();
            return $retorno;

        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function connect_web($email, $password){
        if (ini_get('date.timezone') == '') {
            date_default_timezone_set('UTC');
            date_default_timezone_set('America/Sao_Paulo');
        }

        $retorno = array("message"=>"", "error" =>true);
        try{
            $sql = "SELECT a.* FROM users as a WHERE a.email = :email AND a.status > 0";
            $odb = new db();
            $ret   = $odb->getRow($sql, array(":email" => $email));
            $retid = $odb->rowCount();

            if($retid == 1){

                $dpassword = $ret['password'];
                $idusers = $ret['id'];
                $hash = $ret['hash'];
                $vpassword = functions::checkhashSSHA($hash, $password);

                /*$sql = "SELECT * FROM intoken WHERE inusers = :id";
                $resLogin   = $odb->getRow($sql, array(":id" => $idusers));
                $numRow = $odb->rowCount();

                if($numRow > 0){
                  $emaill = new mail();
                  $res = $emaill->mail_logof($email, $resLogin['inusers']);
                  $retorno = array("message"=>"Este usuário já está logado. Uma mensagem foi enviada para o e-mail informado para que o Logout seja realizado e você possa acessar novamente o sistema!", "error" =>true);
                }
				else{*/
                if ($vpassword == $dpassword){
                    $tokenret = $this->gettoken($idusers);
                    $logauth = new log();

                    if ($tokenret == ""){
                        $token = functions::generatetoken($email);
                        $tokenbase = functions::hashSSHA($token);
                        $sql = "INSERT INTO intoken (token,inusers,hash,state,dttoken) VALUES (:token,:inusers,:hash, 1,now())";
                        $ret = $odb->insertRow($sql, array(":token" => $tokenbase["encrypted"],":inusers" => $idusers,":hash" => $tokenbase["salt"]));
                        if($odb->execcount > 0){
                            $retorno = array("message"=>$this->get_user($tokenbase["encrypted"], 'users', true), "error" =>false);
                            $newlog = $logauth->log_auth(array('id'=>$idusers, 'token'=>$tokenbase["encrypted"]));
                        }
                    }else{
                        $retorno = array("message"=>$this->get_user($tokenret,'users', true), "error" =>false);
                        $newlog = $logauth->log_auth(array('id'=>$idusers, 'token'=>$tokenret));
                    }
                }else{
                    $retorno = array("message"=>"Senha incorreta!", "error" =>true);
                }
            }
            else
            {
                $retorno = array("message"=>"Seu cadastro ainda não foi liberado. Tente novamente em outro momento. Obrigado.", "error" =>true);
            }
            //}

            $odb->Disconnect();
            return $retorno;

        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Conectar usuário
    public function connect_web_md5($login, $password){
        $odb = new db();
        $retorno = array("message"=>"Dados inválidos ou usuário não cadastrado! ", "error" =>true);

        try{
            //$loginP = $this->mask($this->limpaCPF_CNPJ($login), '###.###.###-##');
            $sql = "SELECT * FROM
                    participants as a WHERE
                    a.login LIKE :login AND
                    a.password LIKE :password AND
                    a.status >0 LIMIT 1";

            $ret   = $odb->getRow($sql, array(":login" => $login, ":password"=>md5($password)));
            $row = $odb->rowCount();

            ///CPF
            if($row == 1){

                $idusers =  $ret['id'];
                //$hash = $ret['hash'];

                $sql = "SELECT * FROM intoken WHERE inusers = :id";
                $resLogin   = $odb->getRow($sql, array(":id" => $idusers));
                $numRow = $odb->rowCount();

                      $tokenret = $this->gettoken($idusers);
                      if ($tokenret == ""){
                          $token = functions::generateStrongToken();
                          $tokenbase = functions::hashSSHA($token);
                          $sql = "INSERT INTO intoken (token,inusers,hash,state,dttoken) VALUES (:token,:inusers,:hash, 1,NOW())";
                          $ret = $odb->insertRow($sql, array(":token" => $tokenbase["encrypted"],":inusers" => $idusers,":hash" => $tokenbase["salt"]));
                          if($odb->execcount > 0){
                                $retorno = array("message"=>$this->get_user($tokenbase["encrypted"], 'participants', true), "usertype"=>"participants",  "error" =>false);
                          }
                      }else{
                            $retorno = array("message"=>$this->get_user($tokenret, 'participants', true), "usertype"=>"participants", "error" =>false);
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


    public function limpaCPF_CNPJ($valor){
        $valor = trim($valor);
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(",", "", $valor);
        $valor = str_replace("-", "", $valor);
        $valor = str_replace("/", "", $valor);
        return $valor;
    }

    public function mask($val, $mask){
        $maskared = '';
        $k = 0;
            for($i = 0; $i<=strlen($mask)-1; $i++)
        {
        if($mask[$i] == '#')
        {
        if(isset($val[$k]))
        $maskared .= $val[$k++];
        }
        else
        {
        if(isset($mask[$i]))
        $maskared .= $mask[$i];
        }
        }
        return $maskared;
    }

    //Obter usuário
    public function get_user($token, $table, $type=false){
        $retorno = array();
        try{
            $sql = "SELECT t.token,
                    u.id, u.name, u.email, u.userlogin, u.perfil
                    FROM intoken as t
                    INNER JOIN ".$table." as u
                    ON u.id = t.inusers
                    WHERE t.token = :token
                    AND t.state = 1
                    AND u.status = 1";
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
    public function passwordIsvalid($email, $password){
        $retorno = array("message"=>"Usuário ou senha incorretos!", "error" =>true);
        try{
            $sql = "SELECT a.usersId, a.usersEmail, a.usersPassword, a.usersHash FROM tb_users as a WHERE a.usersEmail = :usersEmail AND a.usersStatus < 2";
            $odb = new db();
            $ret   = $odb->getRow($sql, array(":usersEmail" => $email));
            $retid = $odb->rowCount();

            if($retid == 1){

                $dpassword = $ret['usersPassword'];
                $hash = $ret['usersHash'];
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
    public function disconnect($token){
        $retorno = array("message"=>"Não foi póssivel sair do sistema! ", "error" =>true);
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
}
