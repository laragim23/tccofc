<!DOCTYPE html>
<title>Iara Concept - Alterar Categoria</title>

<?php
//1. Conectar no BD (IP, usuario, senha, nome do bd)
require_once("conexao.php");

if (isset($_POST['voltar'])) {
  header("Location: listarCategoria.php");
 }

if (isset($_POST['salvar'])) {
  //2. Receber os dados para inserir no BD
  $id = $_POST['id'];
  $nome = $_POST['nome'];

  //3. Preparar a SQL
  $sql = "update categoria
    set nome= '$nome'
    where id = $id";

  //4. Executar a SQL
  mysqli_query($conexao, $sql);

  //5. Mostrar uma mensagem ao usuário
  $mensagem = "Alterado com sucesso.";
}

//Busca usuário selecionado pelo "usuarioListar.php"
$sql = "select * from categoria where id = " . $_GET['id'];
$resultado = mysqli_query($conexao, $sql);
$linha = mysqli_fetch_array($resultado)
  ?>


<?php require_once("cabecalho.php") ?>


<main id="main" class="main">

    <div class="pagetitle">
      <h1>Categorias</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Início</a></li>
          <li class="breadcrumb-item">Categorias</li>
          <li class="breadcrumb-item active">Alterar Categoria</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">

        <div class="card">
            <div class="card-body">
              <h5 class="card-title">Alterar Categoria</h5>

              <?php require_once("mensagem.php") ?>

              <form method="post" class="container">
                <?php
                $nome = isset($_POST['nome']) ? $_POST['nome'] : "";
                ?>
              </form>

              <!-- Multi Columns Form -->
              <form method="post" class="row g-3">
              <input type="hidden" class="form-control" value="<?= $linha['id'] ?>" name="id">
                <div class="col-md-12">
                    <label for="exampleFormControlInput1" class="form-label">Nome</label>
                    <input type="text" class="form-control" value="<?=$linha['nome'] ?>" name="nome">
                </div>

                <div class="text-center">
                  <button type="submit" name="salvar" class="btn btn-primary">Cadastrar</button>
                  <button type="submit" name="voltar" class="btn btn-secondary">Voltar</button>
                </div>
              </form><!-- End Multi Columns Form -->

            </div>
          </div>

        </div>

    </section>

</main>

<?php require_once("rodape.php") ?>