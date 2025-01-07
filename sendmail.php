<?php
require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Заголовки для обработки CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://abdurakhimov.info');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обработка preflight-запроса (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Основной код
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $name = $input['name'] ?? null;
    $email = $input['email'] ?? null;
    $message = $input['message'] ?? null;

    // Проверка данных
    if (!$name || !$email || !$message) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Настройка SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Настройка отправки
        $mail->setFrom($_ENV['EMAIL_USER'], 'Contact Form');
        $mail->addAddress($_ENV['EMAIL_USER']); // Email получателя

        $mail->Subject = "New Message from $name";
        $mail->Body = "Name: $name\nEmail: $email\nMessage: $message";

        // Отправка письма
        $mail->send();
        http_response_code(200);
        echo json_encode(['message' => 'Message sent successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to send message', 'details' => $mail->ErrorInfo]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
