<?php


//require '../api/config.php';
require '../vendor/autoload.php';
require './bin/autoload.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->contentType('application/json');
$app->contentType('text/html; charset=utf-8');
//$app->response->headers->set('Content-Type', 'application/json');

$corsOptions = array(
    "origin" => "*"
);

use \CorsSlim\CorsSlim;

$app->add(new CorsSlim($corsOptions));

//global variables;
$token = NULL;


//################################################################
//Internal Functions
//################################################################
/* Verify Required Params */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
	if ($error) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $ar = array("message" => 'É importante que todos os campos estejam preenchidos.', "error" => true);;//array("message" => 'Os seguintes parâmetros ' . substr($error_fields, 0, -2) . ' não podem estar vazios.', "error" => true);
        echoRespnse(401, $ar);
        $app->stop();
    }
}

/* Autenticação de Usuário */
function authenticate(\Slim\Route $route) {

    $app = \Slim\Slim::getInstance();
    $headers = $app->request->headers;//apache_request_headers();

    if (isset($headers['Authorization'])) {
        $api_key = $headers['Authorization'];
        global $token;
        $token = $api_key;
        //VALIDAR TOKEN
        $users = new users();
        if(!$users->isvalid($token)){
            $ar = array("message" => "Autenticação inválida! Por favor, deslogue e faça o login novamente no aplicativo.", "error" => true);
            echoRespnse(401, $ar);
            $app->stop();
        }
    } else {
        $ar = array("message" => "Autenticação inválida! Por favor, deslogue e faça o login novamente no aplicativo.", "error" => true);
        echoRespnse(401, $ar);
        $app->stop();
    }

}

/* Verificação de perfil */
function autorizar($permissao, $modo){
    global $token;

    $perfil = new perfil();

    if(!$perfil->autorizaToken($token, $permissao, $modo)){
        $ar = array("message" => "Seu perfil não é autorizado a realizar essa ação.", "error" => true);
        echoRespnse(403, $ar);
        $app = \Slim\Slim::getInstance();
        $app->stop();
    }
}

/* fim Verificação de perfil */

function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);

    echo json_encode($response);
}
//####################### ## ## ## ## ## ###############################


//################################################################
//LOGIN
//################################################################
$app->post('/email/:from', function ($from) use($app){
    verifyRequiredParams(array('email'));

    $sql = new login();
    $login = $app->request->post('email');
    $password = $app->request->post('password');

    switch($from){
        case 'mobile':
            $res = $sql->connect_mobile( $login, $password);

            break;

        case 'web':
            $res = $sql->connect_web($login, $password);
            break;

        case 'admin':
            $res = $sql->connect_admin($login, $password);
            break;
    }

    if($from != 'mobile'){
        if(!$res['error']){
            $res['user'] = $res['message'];
            unset($res['message']);

            $perfil = new perfil();
            $res['perfil'] = $perfil->listrow($res['user']['perfil']);
            $res['perfil'] = $res['perfil']['error']?array():$res['perfil']['message']['perfil'];

            $perfil_permissao = new perfil_permissao();
            $res['permissao'] = $perfil_permissao->get_by_perfil($res['user']['perfil']);
            $res['permissao'] = $res['permissao']['error']?array():$res['permissao']['message'];
        }
    }


    echoRespnse(200, $res);
});

$app->post('/logout/:from', 'authenticate', function ($from) use($app){

    global $token;
    $sql = new login();

    switch($from){
        case 'mobile':
            $res = $sql->disconnect_mobile($token);
            break;

        case 'web':
            $res = $sql->disconnect_web($token);
            break;

        case 'admin':
            $res = $sql->disconnect_web($token);
            break;

    }
    echoRespnse(200, $res);
});
//################################################################
// attendees
//################################################################

$app->post('/attendees', 'authenticate', function() use ($app){
    //autorizar('company', 'create');
    $data  = $app->request->post();
    reset($data);

    //CONNECT CLASS
    $class = new attendees();
    $ret = $class->create($data);

    echoRespnse($ret['statuscode'], $ret);
});

$app->put('/attendees/:id', 'authenticate', function($id) use ($app){
    autorizar('produto', 'update');
    $data  = $app->request->put();
    reset($data);

    //CONNECT CLASS
    $class = new attendees();
    $ret = $class->update($data, $id);

    echoRespnse($ret['statuscode'], $ret);
});

$app->delete('/attendees/:id', 'authenticate', function($id) use ($app){
    //autorizar('company', 'delete');
    $data  = $app->request->put();
    reset($data);

    //CONNECT CLASS
    $class = new attendees();
    $ret = $class->delete($id);

    echoRespnse($ret['statuscode'], $ret);
});

$app->get('/attendees', 'authenticate', function () use($app){
    //autorizar('company', 'view');
    $class = new attendees();

    $ret = $class->listrows(false);
    echoRespnse(200, $ret);
});

$app->get('/attendees/:id', 'authenticate', function ($id) use($app){
    //autorizar('company', 'view');
    $class = new attendees();

    $ret = $class->listrow($id, false);
    echoRespnse(200, $ret);
});
$app->get('/attendees/events/:id',  function ($id) use($app){
    //autorizar('company', 'view');
    $class = new attendees();

    $ret = $class->listattevent($id, false);
    echoRespnse(200, $ret);
});
//################################################################
//users_events
//################################################################
$app->post('/users_events', 'authenticate', function() use ($app){
    //autorizar('company', 'create');
    $data  = $app->request->post();
    reset($data);

    //CONNECT CLASS
    $class = new users_events();
    $ret = $class->create($data);

    echoRespnse($ret['statuscode'], $ret);
});

$app->put('/users_events/:id', 'authenticate', function($id) use ($app){
    autorizar('produto', 'update');
    $data  = $app->request->put();
    reset($data);

    //CONNECT CLASS
    $class = new users_events();
    $ret = $class->update($data, $id);

    echoRespnse($ret['statuscode'], $ret);
});

$app->delete('/users_events/:id', 'authenticate', function($id) use ($app){
    //autorizar('company', 'delete');
    $data  = $app->request->put();
    reset($data);

    //CONNECT CLASS
    $class = new users_events();
    $ret = $class->delete($id);

    echoRespnse($ret['statuscode'], $ret);
});

$app->get('/users_events', 'authenticate', function () use($app){
    //autorizar('company', 'view');
    $class = new users_events();

    $ret = $class->listrows(false);
    echoRespnse(200, $ret);
});

$app->get('/users_events/:users_id',  function ($users_id) use($app){
    //autorizar('company', 'view');
    $class = new users_events();

    $ret = $class->listUserEvent($users_id, false);
    echoRespnse(200, $ret);
});

//################################################################
// clients
//################################################################
$app->post('/clients', 'authenticate', function() use ($app){
    $data  = $app->request->post();
    reset($data);

    //CONNECT CLASS
    $class = new clients();
    $ret = $class->create($data);

    echoRespnse($ret['statuscode'], $ret);
});

$app->put('/clients/:id', 'authenticate', function($id) use ($app){
    //autorizar('clients', 'update');
    $data  = $app->request->put();
    reset($data);

    //CONNECT CLASS
    $class = new clients();
    $ret = $class->update($data, $id);

    echoRespnse($ret['statuscode'], $ret);
});

$app->delete('/clients/:id', 'authenticate', function($id) use ($app){
    $data  = $app->request->put();
    reset($data);

    //CONNECT CLASS
    $class = new clients();
    $ret = $class->delete($id);

    echoRespnse($ret['statuscode'], $ret);
});

$app->get('/clients', 'authenticate', function () use($app){
    $class = new clients();

    $ret = $class->listrows(false);
    echoRespnse(200, $ret);
});

$app->get('/clients/:id', 'authenticate', function ($id) use($app){
    $class = new clients();

    $ret = $class->listrow($id, false);
    echoRespnse(200, $ret);
});

//################################################################
//events
//################################################################
$app->post('/events', 'authenticate', function() use ($app){
    //autorizar('company', 'create');
    $data  = $app->request->post();
    reset($data);

    //CONNECT CLASS
    $class = new events();
    $ret = $class->create($data);

    echoRespnse($ret['statuscode'], $ret);
});

$app->put('/events/:id', 'authenticate', function($id) use ($app){
  //  autorizar('clients', 'update');
    $data  = $app->request->put();
    reset($data);

    //CONNECT CLASS
    $class = new events();
    $ret = $class->update($data, $id);

    echoRespnse($ret['statuscode'], $ret);
});

$app->delete('/events/:id', 'authenticate', function($id) use ($app){
    $data  = $app->request->put();
   reset($data);

    //CONNECT CLASS
    $class = new events();
    $ret = $class->delete($id);

    echoRespnse($ret['statuscode'], $ret);
});

$app->get('/events', 'authenticate', function () use($app){
    $class = new events();

    $ret = $class->listrows(false);
    echoRespnse(200, $ret);
});

$app->get('/events/:id', 'authenticate', function ($id) use($app){
    $class = new events();

    $ret = $class->listrow($id, false);
    echoRespnse(200, $ret);
});
//################################################################
//hotels
//################################################################
$app->post('/hotels', 'authenticate', function() use ($app){
    //autorizar('company', 'create');
    $data  = $app->request->post();
    reset($data);

    //CONNECT CLASS
    $class = new hotels();
    $ret = $class->create($data);

    echoRespnse($ret['statuscode'], $ret);
});

$app->put('/hotels/:id', 'authenticate', function($id) use ($app){
  //  autorizar('hotels', 'update');
    $data  = $app->request->put();
    reset($data);

    //CONNECT CLASS
    $class = new hotels();
    $ret = $class->update($data, $id);

    echoRespnse($ret['statuscode'], $ret);
});

$app->delete('/hotels/:id', 'authenticate', function($id) use ($app){
    $data  = $app->request->put();
    reset($data);

    //CONNECT CLASS
    $class = new hotels();
    $ret = $class->delete($id);

    echoRespnse($ret['statuscode'], $ret);
});

$app->get('/hotels', 'authenticate', function () use($app){
    $class = new hotels();

    $ret = $class->listrows(false);
    echoRespnse(200, $ret);
});

$app->get('/hotels/:id', 'authenticate', function ($id) use($app){
    $class = new hotels();

    $ret = $class->listrow($id, false);
    echoRespnse(200, $ret);
});

//################################################################
// USERS
//################################################################

$app->get('/adm/users', 'authenticate', function () use($app){
    autorizar('users', 'view');
    $class = new users();

    $ret = $class->listrows(false);
    echoRespnse(200, $ret);
});

$app->get('/users', 'authenticate', function () use($app){
    autorizar('users', 'view');
    $class = new users();

    $ret = $class->listrows();
    echoRespnse(200, $ret);
});

$app->get('/adm/users/:id', function ($id) use($app){
    //autorizar('users', 'view');
    $class = new users();

    $ret = $class->list_user_row($id, false);

    //if($ret['error'] == true && $ret['statuscode'] == 200)
    echoRespnse(200, $ret);
});

$app->get('/me/users/:id', function ($id) use($app){
    //autorizar('users', 'view');
    $class = new users();

    $data = $class->list_user_row($id, true);
    $ret['user'] = $data['message'];

    //GET ONDAS
    if($data['error'] == false && $data['statuscode'] == 200){
        $id = $ret['user']['id'];
        $class = new ondas_user();

        $data = $class->listrowbyuser($id, true);
        $ret['program'] = $data['message'];

        $arrProgram = array();
        //EVENT TYPE
        if($data['error'] == false && $data['statuscode'] == 200){

            /*$class = new program_ondas();

            foreach($ret['ondas'] as $v){

                $id = $v['id'];
                $data = $class->listrowbyonda($id);

                if($id)
                array_push($arrProgram, $data['message']);
                $ret['program'] = $arrProgram;
            }
            */

        }

    }
        echoRespnse(200, $ret);
});

$app->get('/users/users_events/:id', function ($id) use($app){
    //autorizar('users', 'view');
    $class = new events_users();

    $ret = $class->listrows_company($id);
    echoRespnse(200, $ret);
});

$app->get('/users/perfil/:id', function ($id) use($app){
    //autorizar('users', 'view');
    $class = new users();

    $ret = $class->listbyperfil($id);
    echoRespnse(200, $ret);
});

$app->post('/users', 'authenticate', function() use ($app){
    autorizar('users', 'create');
    $user  = $app->request->post();
    reset($user);

    //CONNECT CLASS
    $sql = new users();

    //CHECk if isUser
    $isUser = $sql->check_user($user['email']);

    if($isUser){
        echoRespnse(200, array("message"=>"Usuário já cadastrado.", "error"=>true));
    }
    else
    {
        $ret = $sql->add_user($user);
        echoRespnse($ret['statuscode'], $ret);
    }

});

$app->put('/users', 'authenticate', function () use($app){
    autorizar('users', 'update');
    $class         = new users();
    $ret           = $class->update($app->request->put());
    echoRespnse($ret['statuscode'], $ret);
});

$app->delete('/users/:id', 'authenticate', function ($id) use($app){
    autorizar('users', 'delete');
    $class         = new users();
    $ret           = $class->delete($id);
    echoRespnse($ret['statuscode'], $ret);

});

//############# ###################################################
// PERFIL
//############# ###################################################
$app->get('/perfil', 'authenticate', function () use($app){
    autorizar('perfil', 'view');
    $class = new perfil();

    $ret = $class->listrows();
    echoRespnse($ret['statuscode'], $ret);
});

$app->get('/adm/perfil', 'authenticate', function () use($app){
    autorizar('perfil', 'view');
    $class = new perfil();

    $ret = $class->listrows(false);
    echoRespnse($ret['statuscode'], $ret);
});

/*$app->get('/perfil/:id/permissoes', 'authenticate', function ($id) use($app){
    autorizar('perfil', 'view');
    $class = new perfil_permissao();

    $ret = $class->get_by_perfil($id);
    echoRespnse($ret['statuscode'], $ret);
});*/

$app->post('/perfil', 'authenticate', function() use ($app){
    autorizar('perfil', 'create');
    $perfil = json_decode($app->request->post('perfil'), true);
    $perm_new = json_decode($app->request->post('new'), true);

    //CONNECT CLASS
    $class = new perfil();

    $ret = $class->create($perfil);

    if(!$ret['error']){
        $class = new perfil_permissao();
        if(count($perm_new)){
            $ret['insert'] = $class->ins_many($ret['id'], $perm_new);
        }
    }

    echoRespnse($ret['statuscode'], $ret);
});

$app->put('/perfil', 'authenticate', function () use($app){
    autorizar('perfil', 'update');

    $perfil = json_decode($app->request->put('perfil'), true);
    $perm_new = json_decode($app->request->put('new'), true);
    $perm_edit = json_decode($app->request->put('edit'), true);
    $perm_del = json_decode($app->request->put('del'), true);

    //CONNECT CLASS
    $class         = new perfil();
    $ret           = $class->update($perfil);

    if(!$ret['error']){
        $class = new perfil_permissao();
        if(count($perm_new)){
            $ret['insert'] = $class->ins_many($perfil['id'], $perm_new);
        }
        if(count($perm_edit)){
            foreach ($perm_edit as $key => $perm) {
                $ret['edit-'.$perm['id']] = $class->update($perm);
            }
        }
        if(count($perm_del)){
            foreach ($perm_del as $key => $permid) {
                $ret['del-'.$permid] = $class->remove($permid);
            }
        }
    }

    echoRespnse($ret['statuscode'], $ret);
});

$app->delete('/perfil/:id', 'authenticate', function ($id) use($app){
    autorizar('perfil', 'delete');

    $class         = new perfil();
    $ret           = $class->delete($id);
    echoRespnse($ret['statuscode'], $ret);

});

//################################################################
// REMEMBER PASS
//################################################################
$app->post('/sendcodigo', function () use($app){
    //verifyRequiredParams(array('email', 'cod'));
    $class = new mail();
    $ret   = $class->sendcodigo($app->request->post());
    echoRespnse($ret['statuscode'], $ret);
});

$app->post('/changepassword', function () use($app){
    //verifyRequiredParams(array('email', 'cod'));
    $class = new users();
    $ret   = $class->changepassword($app->request->post());
    echoRespnse($ret['statuscode'], $ret);
});

//Desconectar
$app->get('/logoff', 'authenticate', function () use($app){
    $users = new users();
    global $token;
    $ret = $users->disconect($token);
    echoRespnse(200, $ret);
});


$app->run();
