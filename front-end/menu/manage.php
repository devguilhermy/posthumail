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
            <div class="panel-heading title">
                <b>Configuration panel</b>
            </div>
            <div class="panel-body">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                    <span class="glyphicon glyphicon-envelope"></span>&nbsp;<b>E-mail options</b>
                    </div>
                    <div class="panel-body">
                        <div class="well">
                            <p><span class="glyphicon glyphicon-refresh"></span>&nbsp;<b>Confirmation e-mail interval:</b>&nbsp;&nbsp;The frequency in days you want to receive e-mails asking confirmation that you are alive</p>
                            <p><span class="glyphicon glyphicon-calendar"></span>&nbsp;<b>Timit limit:</b>&nbsp;&nbsp;How many weeks without confirmation until sending the posthumous e-mails</p>
                        </div>
                        <br>
                        <div id="message" class="hidden">
                        </div>
                        <br>
                        <div class="manage-form">
                            <form id="form">
                                <div class="input-group col-12">
                                    <span class="input-group-addon" id="basic-addon1"><b>Interval:</b></span>
                                    <input type="text" class="form-control" name="interval" onkeyup="this.value = this.value.toLowerCase()" placeholder="Confirmation e-mail interval..." maxlength="" aria-describedby="basic-addon1" required="required">
                                </div>
                                <br>
                                <div class="input-group col-12">
                                    <span class="input-group-addon" id="basic-addon2"><b>Time limit:</b></span>
                                    <input type="text" class="form-control"  name="deadline" placeholder="Time limit with no response..." maxlength="" aria-describedby="basic-addon2" required="required">
                                </div>
                                <br>
                                <button type="submit" class="btn btn-primary pull-right">
                                    <span class="glyphicon glyphicon-floppy-disk"></span>&nbsp;<b>Save</b>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="panel panel-danger">
                    <div class="panel-heading">
                    <span class="glyphicon glyphicon-cog"></span>&nbsp;<b>Account options</b>
                    </div>
                    <div class="panel-body">
                        <br>
                        <div id="message" class="hidden">
                        </div>
                        <br>
                        <div class="manage-account">
                            <form id="form">
                                <div class="input-group col-12">
                                    <span class="input-group-addon" id="basic-addon3"><span class="glyphicon glyphicon-user"></span></span>
                                    <input type="text" class="form-control" name="name" placeholder="Your name..." maxlength="" aria-describedby="basic-addon3" required="required">
                                </div>
                                <br>
                                <div class="input-group col-12">
                                    <span class="input-group-addon" id="basic-addon4"><b>@</b></span>
                                    <input type="email" class="form-control" name="password" placeholder="Your e-mail..." maxlength="" aria-describedby="basic-addon4" required="required">
                                </div>
                                <br>
                                <div class="input-group col-12">
                                    <span class="input-group-addon" id="basic-addon5"><span class="glyphicon glyphicon-lock"></span></span>
                                    <input type="password" class="form-control" name="password" placeholder="Type an password..." maxlength="" aria-describedby="basic-addon5" required="required">
                                </div>
                                <br>
                                <div class="input-group col-12">
                                    <span class="input-group-addon" id="basic-addon6"><span class="glyphicon glyphicon-lock"></span></span>
                                    <input type="password" class="form-control" name="password" placeholder="Confirm your password..." maxlength="" aria-describedby="basic-addon6" required="required">
                                </div>
                                <br>
                                <button type="submit" class="btn btn-danger pull-right">
                                    <span class="glyphicon glyphicon-floppy-disk"></span>&nbsp;<b>Save</b>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<?php
include "../js.php";
?>

</html>