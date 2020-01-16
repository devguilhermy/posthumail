<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Posthumail</title>
    <?php
    include "../css.php";
    ?>
</head>

<body>
    <?php
    include "navbar.php";
    ?>
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="padding-50 col-sm-12 col-lg-12">
                    <div class="jumbotron">
                        <h1>Welcome to Posthumail!</h1>
                        <p>You have X posthumous messages</p>
                        <p><a class="btn btn-primary btn-lg" href="https://www.php.net/manual/pt_BR/language.oop5.php" role="button">Ver mais</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<?php
include "../js.php";
?>

<script>
    $("#logout").click(function() {
        $.post(
            "../API/act.php", {
                action: "close_session"
            },
            function(data, status) {
                alert(data);
            }
        );
    });

    $(document).ready(function() {
        $.post(
            "../API/act.php", {
                action: "retrieve_session_info"
            },
            function(data, status) {
                var obj = JSON.parse(data);
                if (obj.data.client_id == null && obj.data.client_email == null) {
                    alert("n logado");
                    if (window.location.href != "https://localhost/posthumail/") {
                        window.location.replace("https://localhost/posthumail/");
                    }
                } else {
                    alert("logado");
                    window.location.replace("https://localhost/posthumail/menu/menu.php");
                }
            }
        );
    });
</script>

</html>