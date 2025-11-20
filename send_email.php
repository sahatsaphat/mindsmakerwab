<?php
// ตั้งค่า header ให้ตอบกลับเป็น JSON
header('Content-Type: application/json');

$response = []; //สำหรับเก็บข้อความตอบกลับ

// ตรวจสอบว่าเป็นการร้องขอแบบ POST หรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับข้อมูล JSON ที่ถูกส่งมาจาก JavaScript
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if (stripos($contentType, 'application/json') === false) {
        http_response_code(400);
        $response['message'] = 'Content-Type must be application/json';
        echo json_encode($response);
        exit;
    }

    $rawPostData = trim(file_get_contents("php://input"));
    $formData = json_decode($rawPostData, true);

    // ตรวจสอบว่าการ decode JSON สำเร็จหรือไม่
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        $response['message'] = 'Invalid JSON input. Error: ' . json_last_error_msg();
        echo json_encode($response);
        exit;
    }

    // 1. รับและทำความสะอาดข้อมูลจากฟอร์ม
    $name = isset($formData["name"]) ? filter_var(trim($formData["name"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $email_address = isset($formData["email"]) ? filter_var(trim($formData["email"]), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($formData["phone"]) ? filter_var(trim($formData["phone"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $company = isset($formData["company"]) ? filter_var(trim($formData["company"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $message_text = isset($formData["message"]) ? filter_var(trim($formData["message"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

    $interests = [];
    // ตรวจสอบว่า 'interest' key มีอยู่และเป็น array หรือไม่ ก่อนที่จะวน loop
    if (isset($formData["interest"]) && is_array($formData["interest"])) {
        foreach ($formData["interest"] as $interest_item) {
            $interests[] = filter_var(trim($interest_item), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
    }
    $interests_string = !empty($interests) ? implode(", ", $interests) : 'N/A';

    $budget_currency_from = isset($formData["budget_currency_from"]) ? filter_var(trim($formData["budget_currency_from"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'N/A';
    $budget_from = isset($formData["budget_from"]) ? filter_var(trim($formData["budget_from"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'N/A';
    $budget_currency_to = isset($formData["budget_currency_to"]) ? filter_var(trim($formData["budget_currency_to"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'N/A';
    $budget_to = isset($formData["budget_to"]) ? filter_var(trim($formData["budget_to"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'N/A';

    // 2. ตรวจสอบข้อมูลที่จำเป็น
    if (empty($name) || empty($email_address) || empty($message_text) || empty($phone)) {
        http_response_code(400); // Bad Request
        $response['message'] = 'Name, Email, Phone, and Message are required fields.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    // 3. เตรียมเนื้อหาอีเมล
    $recipient_email = "sahassapat2605@gmail.com"; // อีเมลผู้รับ
    $subject = "New Contact Form Submission from " . $name;

    $email_body = "You have received a new message from your website contact form:\n\n";
    $email_body .= "Name: " . $name . "\n";
    $email_body .= "Email: " . $email_address . "\n";
    $email_body .= "Phone: " . $phone . "\n";
    $email_body .= "Company: " . (!empty($company) ? $company : 'N/A') . "\n\n";

    $email_body .= "Interested in: " . $interests_string . "\n\n";

    $email_body .= "Budget Estimate:\n";
    $email_body .= "From: " . (!empty($budget_from) ? $budget_from : 'N/A') . " " . $budget_currency_from . "\n";
    $email_body .= "To: " . (!empty($budget_to) ? $budget_to : 'N/A') . " " . $budget_currency_to . "\n\n";

    $email_body .= "Message:\n" . $message_text . "\n";

    // Headers ของอีเมล
    // ***สำคัญ: เปลี่ยน 'noreply@yourdomain.com' เป็นอีเมลที่มาจากโดเมนของคุณจริงๆ***
    $from_email = "Mind Maker Contact <noreply@yourdomain.com>"; // แก้ไข yourdomain.com เป็นโดเมนของคุณ
    $headers = "From: " . $from_email . "\r\n";
    $headers .= "Reply-To: " . $email_address . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // 4. ส่งอีเมล
    if (mail($recipient_email, $subject, $email_body, $headers)) {
        http_response_code(200); // OK
        $response['message'] = 'Thank you for your message! It has been sent.';
    } else {
        http_response_code(500); // Internal Server Error
        $response['message'] = 'Sorry, there was an error sending your message. Please try again later.';
        // หากต้องการ debug อาจเพิ่ม error_get_last() ที่นี่ (สำหรับ developer เท่านั้น)
        // $php_errormsg = error_get_last();
        // $response['debug_info'] = $php_errormsg['message'] ?? 'No error message available from PHP mail function.';
    }
} else {
    http_response_code(405); // Method Not Allowed
    $response['message'] = 'Method not allowed. Please use POST.';
}

// ส่งข้อความตอบกลับเป็น JSON
echo json_encode($response);
exit;
?>