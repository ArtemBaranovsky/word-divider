<?php

use Classes\Captcha;

session_start();
require_once('vendor/autoload.php');

if(!empty($_SESSION['spamAttempts'])) {
    $notification = Classes\Notification::initial($_SESSION['spamAttempts']);
}

if($_SERVER['HTTP_X_REQUESTED_WITH']) {

    if ($notification->send($_POST['send_to_phone'], 'Get your confirmation code', $_SESSION['digit'])) {
        $_SESSION['success'] = "The message with confirmation code has been successfully sent.";
        $responce = json_encode($_POST['messages'][0]['client-ref']);
        die($responce);
    }

}

echo'<html><head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/main.css">
    <title>Forum</title>
</head>';

echo '<body>
    <div class="container card">';

if(!empty($_SESSION['error'])){
    foreach ($_SESSION['error'] as $error) {
        echo '<div class="alert alert-danger alert-dismissible  show" role="alert">
                  <span>'.$error.'</span>
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>';
    }
    unset($_SESSION['error']);
}

if(!empty($_SESSION['success'])){
    echo '<div class="alert alert-info alert-dismissible  show" role="alert">
                  <span>'.$_SESSION['success'].'</span>
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>';
    unset($_SESSION['success']);
}

if(!empty($_SESSION['spamAttempts'])){
        ob_start();
    if(!empty($_SESSION['notification'])) {
        var_dump($_SESSION['notification']);
    }
        $captcha = new Captcha();
        ob_end_clean();
}

echo '<form id="send-message-form" action="forum.php" method="post" onsubmit="return checkForm(this);">
        <div class="form-group">
            <label for="email">Message</label>
            <textarea name="message" id="" rows="10" class="form-control" placeholder="Enter your message" required></textarea>
        </div>';

if(!empty($notification)) {
    $notification->html();
}

echo '<input type="submit" class="btn btn-primary" value="Submit" />
    </form>
</div>
<script src="assets/js/main.js"></script>
</body></html>';

