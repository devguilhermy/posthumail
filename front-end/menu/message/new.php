<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Posthumail - New message</title>
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
                <b>Register new message</b>
            </div>
            <div class="panel-body">
                <div class="well">
                    <p class="sub-title">We strongly reccomend you encrypt your messages before registering it!</p>
                    <p class="italic"><b>Use a website (<a href="https://encipher.it/">ex 1</a>,&nbsp;<a href="https://aesencryption.net/">ex 2</a>) or a software like <a href="https://gnupg.org/">GPG</a> (more secure) to encrypt text...</b></p>
                </div>
                <form id="form">
                    <div class="form-group">
                        <label for="fname">Posthumous message</label>
                        <textarea class="form-control" name="client_email" id="message" placeholder="Type your message..." rows="5" required="required"></textarea>
                    </div>
                    <div class="form-group" id="rcpts">
                        <label for="firstRcpt">Recipients</label>
                        <input type="email" class="form-control" name="recipients[]" id="firstRcpt" placeholder="Insert a recipient..." required="required">
                    </div>
                    <div id="message" class="hidden">
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="action" value="retrieve_user_info">
                        <button class="btn btn-success pull-left" id="addRcpt">Add recipient</button>
                        <button class="btn btn-primary pull-right" id="btn"><span class="glyphicon glyphicon-ok"></span>&nbsp;<b>Save</b></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    include "../../js.php";
    ?>
    <script>
        $("#addRcpt").click(function (event) {
            event.preventDefault();
            $("#rcpts").append("<br><input type='email' class='form-control' name='recipients[]' placeholder='Insert a recipient...' required='required'>");
        })
    </script>
</body>

</html>