<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Posthumail - List messages</title>
    <?php
    include "../../css.php";
    ?>
</head>

<body>
    <?php
    include "../navbar.php";
    ?>
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-heading title">
                <b>List messages</b>
            </div>
            <div class="panel-body">
                <table class="table table-responsive">
                    <thead>
                        <th>#</th>
                        <th>Message</th>
                        <th>Recipients</th>
                    </thead>
                    <tr>
                        <td>1</td>
                        <td>Bilau</td>
                        <td>gui@gmail.com</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <?php
    include "../../js.php";
    ?>
    <script>
        $("#addRcpt").click(function(event) {
            event.preventDefault();
            $("#rcpts").append("<br><input type='email' class='form-control' name='recipients[]' placeholder='Insert a recipient...' required='required'>");
        })
    </script>
</body>

</html>