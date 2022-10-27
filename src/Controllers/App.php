<?php

namespace Src\Controllers;

session_start();

use League\Plates\Engine;
use Src\Models\Usuarios;
use Src\Models\Grupos;
use Src\Models\Entregas;

class App
{
  /** @var Engine */
  private $view;

  public function __construct($router)
  {
    $this->view = Engine::create(
      dirname(__DIR__, 2) . "/theme/views",
      "php"
    );

    $this->view->addData(["router" => $router]);
  }

  public function login(): void
  {
    echo $this->view->render("login", [
      "title" => "Login"
    ]);
  }

  public function login_post(array $data): void
  {
    $itemData = filter_var_array($data, FILTER_DEFAULT);

    $usuario = htmlspecialchars($data["username"], ENT_QUOTES);
    $senha = htmlspecialchars($itemData['password'], ENT_QUOTES);

    $user = (new Usuarios())->find("username = :user", "user=$usuario")->fetch();

    if (password_verify($senha, $user->password)) {
      $_SESSION['user'] = $user->id;
      $_SESSION['user_type'] = $user->access;

      $callback["message"] = "Login realizado com sucesso";
      $callback["type"] = "success";
      echo json_encode($callback);
    } else {
      $callback["error"] = "Usuário e senha incorretos!!!";
      $callback["type"] = "error";
      echo json_encode($callback);
    }
  }

  public function cadastro(): void
  {
    echo $this->view->render("cadastro", [
      "title" => "Cadastro"
    ]);
  }

  public function cadastro_post(array $data): void
  {
    $user = new Usuarios();

    $username_enviado = htmlspecialchars($data["username"], ENT_QUOTES);
    $user_verify = (new Usuarios())->find("username = :user", "user=$username_enviado")->fetch();

    if (!empty($user_verify)) {
      $callback["error"] = "Já existe alguém utilizando esse username!!!";
      $callback["type"] = "error";
    } else {

      $user->name = htmlspecialchars($data["name"], ENT_QUOTES);
      $user->username = $username_enviado;
      $user->password = password_hash(htmlspecialchars($data["password"], ENT_QUOTES), PASSWORD_DEFAULT);
      $user->access = htmlspecialchars($data["access"], ENT_QUOTES);
      $user->save();

      $usuario = htmlspecialchars($data["access"], ENT_QUOTES) == 1 ? "Aluno" : "Professor";

      $callback["message"] = "$usuario cadastrado com sucesso";
      $callback["type"] = "success";
    }
    echo json_encode($callback);
  }

  public function inicio(): void
  {

    if (!empty($_SESSION['user']) && $_SESSION['user_type'] == 2) {
      $grupos = (new Grupos())->find("teacher_id_group = :user", "user=$_SESSION[user]")->fetch(true);
      $alunos = (new Usuarios())->find("access = :type", "type=1")->fetch(true);

      echo $this->view->render("inicio", [
        "title" => "Inicio",
        "grupos" => $grupos,
        "alunos" => $alunos,
      ]);
    } elseif (!empty($_SESSION['user']) && $_SESSION['user_type'] == 1) {
      $grupos = (new Grupos())->find("user_id_group = :user", "user=$_SESSION[user]")->fetch(true);
      $tasks = (new Entregas())->find("group_id = :group", "group=$grupos->id")->fetch(true);

      echo $this->view->render("inicio", [
        "title" => "Inicio",
        "grupos" => $grupos,
        "tasks" => $tasks,
      ]);
    } else {
      echo $this->view->render("login", [
        "title" => "Login"
      ]);
    }
  }

  public function criar_grupo(array $data): void
  {
    $name = htmlspecialchars($data["name"], ENT_QUOTES);
    $name_verify = (new Grupos())->find("name = :group_name", "group_name=$name")->fetch(true);

    $description = htmlspecialchars($data["description"], ENT_QUOTES);
    $integrantes = $data["integrantes"];

    if (!empty($name_verify)) {
      $callback["error"] =  "Já existe um grupo com esse nome";
      $callback["type"] = "error";
    } else {

      $newGroup = new Grupos();
      $newGroup->name = $name;
      $newGroup->description = $description;
      $newGroup->teacher_id_group = $_SESSION['user'];
      $newGroup->save();

      $groupId = (new Grupos())->find("name = :unico", "unico=$name")->fetch();

      if ($newGroup->fail()) {
        $callback["error"] = $newGroup->fail()->getMessage();
        $callback["type"] = "error";

        echo json_encode($callback);
      }

      foreach ($integrantes as $key => $integrante_id) {
        $user = (new Usuarios())->find("id = :id", "id=$integrante_id")->fetch();
        $user->group_id = $groupId->id;
        $user->save();

        if ($user->fail()) {
          $callback["error"] = $user->fail()->getMessage();
          $callback["type"] = "error";

          echo json_encode($callback);
        }
      }

      $entregas = (new Entregas())->find("teacher_id_entregas = :professor AND grupo = :grupo_id", "professor=$_SESSION[user]&grupo_id=0")->order("date ASC")->fetch(true);
      foreach ($entregas as $valor) {

        $entrega = new Entregas();
        $entrega->name = $valor->name;
        $entrega->date = $valor->date;
        $entrega->grupo = $groupId->id;
        $entrega->teacher_id_entregas = $_SESSION['user'];
        $entrega->save();


        if($entrega->fail()){
          $callback["error"] = $user->fail()->getMessage();
          $callback["type"] = "error";

          echo json_encode($callback);
        }
      }

      $callback["message"] = "Grupo criado com sucesso";
      $callback["type"] = "success";
      $callback["reload"] = true;
    }

    echo json_encode($callback);
  }

  public function nova_senha(array $data): void
  {
    $current_password = htmlspecialchars($data["current_password"], ENT_QUOTES);
    $new_password = htmlspecialchars($data["new_password"], ENT_QUOTES);
    $confirm_password = htmlspecialchars($data["confirm_password"], ENT_QUOTES);

    $user = (new Usuarios())->find("id = :id", "id=$_SESSION[user]")->fetch();

    if (password_verify($current_password, $user->password)) {

      if ($new_password == $confirm_password) {
        $user->password = password_hash($new_password, PASSWORD_DEFAULT);
        $user->save();

        $callback["message"] = "Senha atualizada com sucesso";
        $callback["type"] = "success";
        $callback["reload"] = true;
      } else {
        $callback["error"] = "As senhas informadas estão diferentes!!!";
        $callback["type"] = "error";
      }
    } else {

      $callback["error"] = "A senha atual está incorreta!!!";
      $callback["type"] = "error";
    }

    echo json_encode($callback);
  }

  public function criar_entregas(array $data): void
  {
    $name = htmlspecialchars($data["entrega"], ENT_QUOTES);
    $date = htmlspecialchars($data["prazo_entrega"], ENT_QUOTES);

    $task = new Entregas();
    $task->name = $name;
    $task->date = $date;
    $task->grupo = 0;
    $task->teacher_id_entregas = $_SESSION['user'];
    $task->save();

    $grupos = (new Grupos())->find("teacher_id_group = :user", "user=$_SESSION[user]")->fetch(true);

    foreach ($grupos as $grupo) {
      $task = new Entregas();
      $task->name = $name;
      $task->date = $date;
      $task->grupo = $grupo->id;
      $task->teacher_id_entregas = $_SESSION['user'];
      $task->save();
    }

    if ($task->fail()) {
      $callback["error"] = $task->fail()->getMessage();
      $callback["type"] = "error";

      echo json_encode($callback);
    }

    $callback["message"] = "Entrega criada com sucesso";
    $callback["type"] = "success";
    $callback["reload"] = true;

    echo json_encode($callback);
  }

  public function calendario(): void
  {
    if (!empty($_SESSION['user']) && $_SESSION['user_type'] == 2) {
      $entregas = (new Entregas())->find("teacher_id_entregas = :professor AND grupo = :grupo_id", "professor=$_SESSION[user]&grupo_id=0")->order("date ASC")->fetch(true);

      echo $this->view->render("calendario", [
        "title" => "Calendário",
        "entrega" => $entregas,
      ]);
    } else {
      echo $this->view->render("login", [
        "title" => "Login"
      ]);
    }
  }

  public function detalhe_grupo(array $data): void
  {
    if (!empty($_SESSION['user']) && $_SESSION['user_type'] == 2) {
      $grupo = (new Grupos())->findById($data["id"]);
      $entregas = (new Entregas())->find("grupo = :grupo_id", "grupo_id=$data[id]")->order("date ASC")->fetch(true);

      echo $this->view->render("detalhe", [
        "title" => "Detalhes",
        "grupo" => $grupo,
        "entrega" => $entregas,
      ]);
    } else {
      echo $this->view->render("login", [
        "title" => "Login"
      ]);
    }
  }

  public function sair(): void
  {
    session_destroy();

    echo $this->view->render("login", [
      "title" => "Login"
    ]);
  }
}
