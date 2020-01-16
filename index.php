<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Posthumail</title>
    <?php include "css.php" ?>
</head>

<body>
    <div class="login-form">
        <form id="form">
            <h2 class="text-center" style="font-family: FonteSangrenta">Posthumail</h2>
            <div class="form-group">
                <input type="text" class="form-control" name="client_email" id="username" placeholder="Username">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="client_passwd" id="password" placeholder="Password">
            </div>
            <div id="message" class="hidden">
            </div>
            <div class="form-group">
                <input type="hidden" name="action" value="open_session">
                <button class="btn btn-primary btn-block" id="btn">Log in</button>
            </div>
            <div class="clearfix">
                <a href="signup.php">Create an Account</a>
                <a href="#" class="pull-right">Forgot Password?</a>
            </div>
        </form>
    </div>

    <?php include "js.php"; ?>
    <script>    
        $("#btn").click(function(event) {
            event.preventDefault();
            if ($("#username").val() == "" || $("#password").val() == "") {
                $("#message").show();
                $("#message").attr("class", "alert alert-danger");
                $("#message").text("Fill out all the fields!");
            } else {
                $.post(
                    "API/act.php", {
                        client_email: $("#username").val(),
                        client_passwd: $("#password").val(),
                        action: "open_session"
                    },
                    function(data, status) {
                        var obj = JSON.parse(data);
                        if (obj.status_code == 1500) {
                            window.location.replace("https://localhost/posthumail/menu/menu.php");
                        } else {
                            $("#message").show();
                            $("#message").attr("class", "alert alert-danger");
                            $("#message").text(obj.message);
                        }
                    },
                );
            }
        });
    </script>
</body>

</html>