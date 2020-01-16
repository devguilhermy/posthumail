<?php
    require_once "../class/Cliente.php";

    if (isset($_GET['editar']) && isset($_GET['id'])) {
        $cliente = new Cliente();
        $resultado = $cliente->find($_GET['id']);

        $varnome = $resultado->nome;
        $varemail = $resultado->email;
        $varendereco = $resultado->endereco;
        $varid = $resultado->id;
    } else {
        $varnome = "";
        $varemail = "";
        $varendereco = "";
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
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
                <b>Cadastro de cliente</b>
            </div>
            <div class="panel-body">
                <?php
                    if (isset($_POST['cadastrar'])) {
                        $nome = $_POST['nome'];
                        $email = $_POST['email'];
                        $endereco = $_POST['endereco'];

                        $cliente = new Cliente();
                        $cliente->setNome($nome);
                        $cliente->setEmail($email);
                        $cliente->setEndereco($endereco);

                        if($cliente->insert()){
                            echo "Cadastrado com sucesso!";
                        }
                    } else if (isset($_POST['atualizar'])) {
                        $nome = $_POST['nome'];
                        $email = $_POST['email'];
                        $endereco = $_POST['endereco'];

                        $cliente = new Cliente();
                        $cliente->setNome($nome);
                        $cliente->setEmail($email);
                        $cliente->setEndereco($endereco);

                        if($cliente->update($_POST['id'])){
                            echo "Atualizado com sucesso!";
                        }

                    }
                ?>
                <div class="padding-50 col-sm-12 col-lg-12">
                    <form action="cadCliente.php" method="post">
                        <div class="row">
                            <div class="form-group col-sm-12 col-lg-12">
                                Nome:
                                <input type="text" class="form-control" name="nome" value="<?php echo $varnome ?>" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-12 col-lg-12">
                                E-mail:
                                <input type="text" class="form-control" name="email" value="<?php echo $varemail ?>" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-12 col-lg-12">
                                Endereço:
                                <input type="text" class="form-control" name="endereco" value="<?php echo $varendereco ?>" />
                            </div>
                        </div>
                        <?php
                            if (isset($_GET['editar'])) {
                                echo "<input type='hidden' name='id' value='$varid'>";
                                echo "<button class='btn btn-primary' type='submit' name='atualizar'><b>Atualizar</b></button>";
                            } else {
                                echo "<button class='btn btn-primary' type='submit' name='cadastrar'><b>Cadastrar</b></button>";
                            }
                        ?>
                    </form>

                </div>
            </div>
        </div>
    </div>
</body>

</html>