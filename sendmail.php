<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');

session_start();
$max_requests = 5;
$time_window = 60; // 60 секунд
$ip = $_SERVER['REMOTE_ADDR'];

if (!isset($_SESSION['requests'][$ip])) {
    $_SESSION['requests'][$ip] = [];
}

$_SESSION['requests'][$ip] = array_filter($_SESSION['requests'][$ip], function($timestamp) use ($time_window) {
    return $timestamp > time() - $time_window;
});

if (count($_SESSION['requests'][$ip]) >= $max_requests) {
    echo json_encode(["error" => "Слишком много запросов. Попробуйте позже."]);
    exit;
}

$_SESSION['requests'][$ip][] = time();

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name'], $data['email'], $data['message'])) {
    echo json_encode(["error" => "Некорректные данные."]);
    exit;
}

$name = htmlspecialchars(strip_tags(trim($data['name'])));
$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars(strip_tags(trim($data['message'])));

if (!$email) {
    echo json_encode(["error" => "Некорректный email."]);
    exit;
}

$recaptcha_secret = $_ENV['RECAPTCHA_SECRET'] ?? '';
$recaptcha_response = $data['recaptcha'] ?? '';

if ($recaptcha_secret && $recaptcha_response) {
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
    $responseKeys = json_decode($response, true);
    if (!$responseKeys["success"]) {
        echo json_encode(["error" => "Ошибка проверки reCAPTCHA."]);
        exit;
    }
}

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['EMAIL_USER'];
    $mail->Password = $_ENV['EMAIL_PASS'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom($_ENV['EMAIL_USER'], 'Contact Form');
    $mail->addAddress('your-email@example.com');
    $mail->isHTML(true);
    $mail->Subject = 'Новое сообщение с сайта';
    $mail->Body = "<b>Имя:</b> $name<br><b>Email:</b> $email<br><b>Сообщение:</b><br>$message";
    $mail->send();
    echo json_encode(["success" => "Сообщение отправлено."]);
} catch (Exception $e) {
    error_log("Ошибка отправки email: " . $mail->ErrorInfo);
    echo json_encode(["error" => "Не удалось отправить сообщение."]);
}
