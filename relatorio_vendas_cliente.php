<!DOCTYPE html>
<title>Relatório de Vendas</title>

<?php
session_start();
if (empty($_SESSION)) {
    print "<script>location.href='index.php';</script>";
}
?>

<?php
// Inclua a conexão com o banco de dados
require_once("conexao.php");

// Excluir registros relacionados na tabela compravendaproduto
if (isset($_GET['id'])) {
    // Excluir registros relacionados na tabela compravendaproduto
    $sqlExcluirProdutos = "DELETE FROM compravendaproduto WHERE compravenda_id = " . $_GET['id'];
    mysqli_query($conexao, $sqlExcluirProdutos);

    // Agora você pode excluir a venda na tabela compravenda
    $sqlExcluirVenda = "DELETE FROM compravenda WHERE id = " . $_GET['id'];
    mysqli_query($conexao, $sqlExcluirVenda);
    $mensagem = "Exclusão realizada com sucesso.";
}

// Inicializar a variável de pesquisa
$pesquisaCliente = "";
$pesquisaVendedor = "";

$filtro = "";
// Verificar se o formulário de pesquisa foi enviado
if (isset($_POST['cliente']) && $_POST['cliente'] != '') {
    $pesquisaCliente = $_POST['cliente'];
    $filtro .= " and c.nome LIKE '%$pesquisaCliente%' ";
}
if (isset($_POST['vendedor']) && $_POST['vendedor'] != '') {
    $pesquisaVendedor = $_POST['vendedor'];
    $filtro .= " and v.nome LIKE '%$pesquisaVendedor%' ";
}
if (isset($_POST["salvar"])) {
    $situacao = $_POST['situacao'];
    $idCompraVenda = $_POST['id'];
    $sqlAlterar = "update compravenda set situacao = '$situacao' where id = $idCompraVenda";
    mysqli_query($conexao, $sqlAlterar);
    $mensagem = "Alteração realizada com sucesso.";
}

// Inicializar as variáveis de pesquisa para data
$pesquisaDataInicio = "";
$pesquisaDataFim = "";

// Verificar se as datas foram enviadas no formulário de pesquisa
if (isset($_POST['dataInicio']) && $_POST['dataInicio'] != '') {
    $pesquisaDataInicio = $_POST['dataInicio'];
    $filtro .= " and cv.datacriacao >= '$pesquisaDataInicio 00:00:00' "; // Inclui todas as vendas do dia
}

if (isset($_POST['dataFim']) && $_POST['dataFim'] != '') {
    $pesquisaDataFim = $_POST['dataFim'];
    $filtro .= " and cv.datacriacao <= '$pesquisaDataFim 23:59:59' "; // Inclui todas as vendas do dia
}

if (isset($_POST['situacaoFiltro']) && $_POST['situacaoFiltro'] != '') {
    $situacaoFiltro = $_POST['situacaoFiltro'];
    $filtro .= " and cv.situacao = '$situacaoFiltro'";
}

// Consulta SQL para obter as vendas por cliente com base na pesquisa
$sql = "SELECT cv.id, c.nome as cliente, v.nome as vendedor, cv.datacriacao, cv.operacao, cv.situacao
        FROM compravenda cv
        LEFT JOIN cliente c ON cv.cliente_id = c.id
        LEFT JOIN vendedor v ON cv.vendedor_id = v.id
        WHERE cv.operacao = 'venda'
              $filtro
        ORDER BY cv.datacriacao desc"; //v.total 

//3. Executar a SQL
$resultado = mysqli_query($conexao, $sql);

// Recuperar o valor total da variável de sessão
$valorTotal = isset($_SESSION['valorTotal']) ? $_SESSION['valorTotal'] : 0;

// Limpar a variável de sessão
unset($_SESSION['valorTotal']);

?>

<?php require_once("cabecalho.php") ?>
<main id="main" class="main">
    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Relatório de Vendas
                    <a href="relatorio_condicionais_cliente.php" class="btn btn-primary"><i
                            class="bi bi-basket"></i></a>
                </h5>
                <?php require_once("mensagem.php") ?>

                <!-- Formulário de pesquisa -->
                <div class="row">
                    <div class="mb-3">
                        <form method="post">
                            <div class="input-group">
                                <input type="text" class="form-control" id="cliente" name="cliente"
                                    value="<?php echo $pesquisaCliente; ?>" placeholder="Pesquisar cliente">

                                <input type="text" class="form-control" id="vendedor" name="vendedor"
                                    value="<?php echo $pesquisaVendedor; ?>" placeholder="Pesquisar vendedor">

                                <button name="pesquisar" stype="button" class="btn btn-primary"><i
                                        class="bi bi-search"></i></button>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <label for="datacriacao" class="form-label">Data inicial</label>
                                    <input type="date" class="form-control" id="dataInicio" name="dataInicio"
                                        placeholder="Data Inicial" value="<?php echo $pesquisaDataInicio; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="datacriacao" class="form-label">Data final</label>
                                    <input type="date" class="form-control" id="dataFim" name="dataFim"
                                        placeholder="Data Final" value="<?php echo $pesquisaDataFim; ?>">
                                </div>

                                <div class="col-md-4">
                                    <label for="situacaoFiltro" class="form-label">Situação</label>
                                    <select class="form-select" id="situacaoFiltro" name="situacaoFiltro">
                                        <option value="" selected>Todas</option>
                                        <option value="Pago">Pago</option>
                                        <option value="Pendente">Pendente</option>
                                        <option value="Atrasado">Atrasado</option>
                                        <option value="Devolvido">Devolvido</option>
                                    </select>
                                </div>
                            </div>
                        </form>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Data da Venda</th>
                                    <th>Total (R$)</th>
                                    <th>Situação</th>
                                    <th scope="col">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($linha = mysqli_fetch_array($resultado)) { ?>
                                    <tr>
                                        <th>
                                            <?= $linha['id'] ?>
                                        </th>
                                        <td>
                                            <?= $linha['cliente'] ?>
                                        </td>
                                        <td>
                                            <?= $linha['vendedor'] ?>
                                        </td>
                                        <td>
                                            <?= date("d/m/Y", strtotime($linha['datacriacao'])) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $total = 0;
                                            $sqlProduto = "select quantidade, valorunitario from compravendaproduto where compravenda_id = " . $linha['id'];
                                            $resultadoVendaProduto = mysqli_query($conexao, $sqlProduto);
                                            if (mysqli_num_rows($resultadoVendaProduto) > 0) {

                                                while ($linhaVendaProduto = mysqli_fetch_array($resultadoVendaProduto)) {
                                                    $total += ($linhaVendaProduto['quantidade'] * $linhaVendaProduto['valorunitario']);
                                                }
                                            }
                                            $sqlDesconto = "select desconto from compravenda where id = " . $linha['id'];
                                            $resultadoDesconto = mysqli_query($conexao, $sqlDesconto);
                                            $linhaDesconto = mysqli_fetch_array($resultadoDesconto);
                                            echo $total - $linhaDesconto['desconto']; ?>
                                        </td>
                                        <td>
                                            <?= $linha['situacao'] ?>
                                        </td>
                                        <td>
                                            <a class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalSituacao_<?= $linha['id'] ?>">
                                                <i class="bi bi-pencil-square"></i></a>

                                            <a href="relatorio_vendas_cliente.php? id=<?= $linha['id'] ?>"
                                                class="btn btn-danger" onclick="return confirm('Deseja excluir mesmo?')">
                                                <i class="bi bi-trash3-fill"></i>
                                            </a>
                                        </td>
                                        <div class="modal fade" id="modalSituacao_<?= $linha['id'] ?>" tabindex="-1"
                                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h2 class="modal-title fs-5" id="exampleModalLabel">
                                                            Situação da Venda
                                                        </h2>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <form action="" method="post">
                                                    <input type="hidden" value="<?= $linha['id'] ?>" name="id">
                                                        <div class="modal-body">

                                                        <h5><b>Cliente: </b><?= $linha['cliente']; ?></h5>

                                                            <select type="hidden" class="form-select" name="situacao"
                                                                id="selectbox" data-selected="">
                                                                <option class="fonte-status" value="" disabled="disabled"
                                                                    placeholder="Situação">Situação</option>
                                                                <option value="Pago" <?= ($linha['situacao'] == 'Pago') ? 'selected="selected"' : ''; ?>>Pago</option>
                                                                <option value="Pendente" <?= ($linha['situacao'] == 'Pendente') ? 'selected="selected"' : ''; ?>>Pendente</option>
                                                                <option value="Atrasado" <?= ($linha['situacao'] == 'Atrasado') ? 'selected="selected"' : ''; ?>>Atrasado</option>
                                                                <option value="Devolvido"
                                                                    <?= ($linha['situacao'] == 'Devolvido') ? 'selected="selected"' : ''; ?>>Devolvido</option>
                                                            </select>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Fechar</button>
                                                                <button class="btn btn-primary" type="submit"
                                                                    name="salvar">Salvar</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
    </section>

</main>

<?php
// Fechar a conexão
mysqli_close($conexao);
?>

<?php require_once("rodape.php") ?>