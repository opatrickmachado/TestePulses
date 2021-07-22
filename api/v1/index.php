<?php

    require 'Slim/Slim.php';
    require 'Db.php';

    \Slim\Slim::registerAutoloader();
    $app = new \Slim\Slim();

    $db = new Db;

    $app->post('/ola', function() use ($app){
        echo "Ola ".genToken();
    });
    
    // cadastrar novo usuário no banco de dados
    $app->post('/cadastra', 'authenticate', function() use($app, $db){
        
        $response = array();
        
        $email = $app->request->post("email");
        $pass = $app->request->post("pass");
        
        $ue = $db->query(sprintf("SELECT token FROM usuario WHERE email = '%s'", $email));
        
        if($ue->num_rows > 0){
            $response['erro'] = true;
            $response['mensagem'] = "Desculpe, mas o email cadastrado ja existe!";
            response(200, $response);
            $app->stop();
        }
        
        $ret = $db->query(sprintf("INSERT INTO usuario (email, pass, token) VALUES ('%s', '%s', '%s')", $email, $pass, genToken()));
        
        if($ret){
            $response['erro'] = false;
            $response['mensagem'] = "Oba, cadastro efetuado com sucesso!";
        } else {
            $response['erro'] = true;
            $response['mensagem'] = "Infelizmente houve erro ao efetuar o cadastro";
        }
        
        response(200, $response);
        
    });

    $app->post("/login", function() use($app, $db){
        $response = array();
        
        $email = $app->request->post("email");
        $pass = $app->request->post("pass");
       
        $ue = $db->query(sprintf("SELECT token FROM usuario WHERE email = '%s' AND pass = '%s'", $email, $pass));
        
        if($ue->num_rows > 0){
            
            $dados = $ue->fetch_assoc();
            
            $response['erro'] = false;
            $response['token'] = $dados["token"];
            
        } else {
            $response['erro'] = true;
            $response['token'] = "Usuário/Senha incorreto(s)";
        }
        
        response(200, $response);
        
    });

    $app->post('/cadastrartarefa', 'authenticate', function() use($app, $db){
        $response = array();
        
        $tarefa = $app->request->post("tarefa");
        
        $ret = $db->query(sprintf("INSERT INTO todolist (tarefa) VALUES ('%s')", $tarefa));
        
        if($ret){
            $response['erro'] = false;
            $response['mensagem'] = "Oba, cadastro efetuado com sucesso!";
        } else {
            $response['erro'] = true;
            $response['mensagem'] = "Infelizmente houve erro ao efetuar o cadastro";
        }
        
        response(200, $response);
        
    });

    // funções auxiliares

    function response($status_code, $response){
        $app = \Slim\Slim::getInstance();
        $app->status($status_code);
        $app->contentType("application/json");
        echo json_encode($response);
    }

    function genToken(){
        return md5(uniqid(rand(), true));   
    }
    
    function authenticate(\Slim\Route $rote){
        $headers = apache_request_headers();
        $response = array();
        
        $app = \Slim\Slim::getInstance();
        
        if(isset($headers["Authorization"])){
            
            $db = new Db;
            
            $vt = $db->query(sprintf("SELECT email FROM usuario WHERE token = '%s'", $headers["Authorization"]));
            
            if($vt->num_rows < 1){
                $response['erro'] = true;
                $response['token'] = "Sorry, Autenticação esta incorreta!";
                response(200, $response);
                $app->stop();
            }
            
            
        } else {
            $response["erro"] = true;
            $response["mensagem"] = "Ops, você não está autenticado!";
            response(200, $response);
            $app->stop();
        }
    }

    $app->run();