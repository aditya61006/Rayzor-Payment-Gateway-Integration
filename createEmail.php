<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendEmailbyMailer($data)
{
    $mail = new PHPMailer(true);

    try {


            $msg = " Dear Customer,<br><br>
                    Your payment has been successfully received.<br><br>
                    <strong>Transaction Details</strong><br><br>
                    <strong>Customer:</strong> ".$data['customer_name']."<br>
                    <strong>Payment Type:</strong> ".$data['payment_type']."<br>
                    <strong>Total Amount:</strong> ₹".$data['amount']."<br>
                    <strong>Payment ID:</strong> ".$data['payment_id']."<br>
                    <strong>Status:</strong> Success<br><br>
                    Thank you for your payment!<br><br>
                    Regards,<br>
                    <strong>Payment Team</strong>";

        $mail->SMTPDebug = SMTP::DEBUG_SERVER;

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'shagunlegent@gmail.com';
        $mail->Password = 'qfbarpgrebdkmprw';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender
        $mail->setFrom('shagunlegent@gmail.com', 'Mailer');

        // Receiver 
        $mail->addAddress('arjunkatoch128@gmail.com');

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Payment Successful – Transaction Confirmation';
        $mail->Body = $msg;
        $mail->AltBody = 'This is the body in plain text';

        // Send
        $mail->send();

        echo 'Message has been sent successfully';

    } catch (Exception $e) {

        echo "Message could not be sent. Mailer Error: "
            . $mail->ErrorInfo;
    }
}

