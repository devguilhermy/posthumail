<?php
    if (isset($_POST['signup'])) {
        register_new_user(""); 
    }
?>

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
        <form action="login/response.php" method="post">
            <h2 class="text-center" style="font-family: FonteSangrenta">Posthumail</h2>
            <div class="form-group">
                <input type="text" class="form-control" name="name" placeholder="Name" required="required">
            </div>
            <div class="form-group">
                <input type="email" class="form-control" name="password" placeholder="E-mail" required="required">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password" required="required">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-danger btn-block" name=signup>Sign Up</button>
            </div>
            <div class="clearfix text-center">
                <a href="index.php">Sign In</a>
            </div>
        </form>
    </div>
    
    <?php include "js.php" ?>
</body>
</html>