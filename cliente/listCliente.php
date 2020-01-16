<?php
    require_once "../class/Cliente.php";
    $cliente = new Cliente();

    
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Listagem de Clientes</title>
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <script type="text/javascript" src="../js/jquery-2.2.3.js"></script>
    <script type="text/javascript" src="../js/bootstrap.js"></script>
</head>

<body>
    <?php
    include "../navbar.php";
    ?>
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">
                <b>Listagem de cliente</b>
            </div>
            <div class="panel-body">
                <div class="padding-50 col-sm-12 col-lg-12">
                    <?php 
                        if (isset($_GET['excluir']) && isset($_GET['id'])) {    
                            if ($cliente->delete($_GET['id'])) {
                                echo "Excluído com sucesso!";
                            }
                        }
                    ?>  
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Endereço</th>
                                <th>Editar</th>
                                <th>Excluir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($cliente->findAll() as $key => $value) {
                                    echo "<tr>";
                                    echo "<td>$value->id</td>";
                                    echo "<td>$value->nome</td>";
                                    echo "<td>$value->email</td>";
                                    echo "<td>$value->endereco</td>";
                                    echo "<td><a href='cadCliente.php?editar&id=$value->id'><span class='glyphicon glyphicon-pencil'</a></td>";
                                    echo "<td><a href='listCliente.php?excluir&id=$value->id' onclick='return confirm(\"Tem certeza?\")'><span class='glyphicon glyphicon-remove'</a></td>";
                                    echo "</tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>