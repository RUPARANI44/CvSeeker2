<?php

$token = $_POST["token"];

$token_hash = hash("sha256", $token);

$mysqli = require __DIR__ . "/config.php";

$sql = "SELECT * FROM user
        WHERE reset_token_hash_t = ?";

$stmt = $con->prepare($sql);

$stmt->bind_param("s", $token_hash);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

if ($user === null) {
    die("token not found");
}

if (strtotime($user["reset_token_expires_at_t"]) <= time()) {
    die("token has expired");
}

if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters");
}

if ( ! preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter");
}

if ( ! preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain at least one number");
}

if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match");
}

$password = password_hash($_POST["password"], PASSWORD_DEFAULT);

$sql = "UPDATE user
        SET password = ?,
            reset_token_hash_t = NULL,
            reset_token_expires_at_t = NULL
        WHERE id = ?";

$stmt = $con->prepare($sql);

$stmt->bind_param("ss", $password, $user["id"]);

$stmt->execute();

echo "Password updated. You can now login.</a>";
header("location:login.php");