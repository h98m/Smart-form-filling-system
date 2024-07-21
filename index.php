<?php
session_start();

$phone = $_SESSION['phone'] ?? '';

// تعريف متغيرات للتحكم في عدد المحاولات والقفل
$max_attempts = 5; // عدد الخطأ المسموح به قبل القفل
$lockout_time = 5 * 60; // وقت القفل بالثواني (هنا 5 دقائق)
$lockout_message = '';

// التحقق من وقت القفل
if (isset($_SESSION['last_attempt_time']) && time() - $_SESSION['last_attempt_time'] < $lockout_time) {
    $remaining_time = $lockout_time - (time() - $_SESSION['last_attempt_time']);
    $lockout_message = "لقد تم حظر المحاولات لمدة " . gmdate("i دقيقة و s ثانية", $remaining_time) . ". يرجى المحاولة لاحقاً.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['phone'])) {
    // التحقق من وجود رسالة القفل
    if ($lockout_message) {
        // لا تقم بعملية أخرى، فقط عرض رسالة القفل
    } else {
        $phone = $_POST['phone'];

        // التحقق من أن رقم الهاتف يتكون فقط من أرقام ولا يبدأ بصفر
        if (!preg_match('/^[1-9][0-9]*$/', $phone)) {
            $error_message = 'رقم الهاتف غير صالح. يجب أن لا يبدأ الرقم بصفر أو يحتوي على أحرف.';
        } else {
            // إضافة رمز الدولة العراقي
            $phone = '964' . $phone; 

            $code = rand(1000, 9999); // إنشاء رمز عشوائي

            // حفظ الرمز ورقم الهاتف في جلسة المستخدم للتحقق لاحقًا
            $_SESSION['verification_code'] = $code;
            $_SESSION['phone'] = $phone;

            $instance_id = '668D4BA1543ED';
            $access_token = '668d4603bdbd7';
            $url = "https://app.arrivewhats.com/api/send?number=$phone&type=text&message=رمز التحقق الخاص بك هو: $code&instance_id=$instance_id&access_token=$access_token";

            try {
                $response = file_get_contents($url);
                if ($response === FALSE) {
                    throw new Exception('Failed to send message');
                }

                // تحويل المستخدم إلى صفحة التحقق من الرمز بعد إرسال الرمز
                header('Location: verify_code.php');
                exit();
            } catch (Exception $e) {
                $error_message = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدخال رقم الهاتف</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            text-align: right;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
            position: relative;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="tel"], input[type="number"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            text-align: right;
        }
        input[type="text"]:focus, input[type="tel"]:focus, input[type="number"]:focus {
            border-color: #28a745;
            outline: none;
        }
        input[type="submit"] {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .error, .lockout-message {
            color: red;
            margin-top: 5px;
            font-weight: bold;
        }
        .phone-prefix {
            position: absolute;
            left: 5px;
            top: 50%;
            transform: translateY(-50%);
            padding: 10px;
            font-size: 17px;
            color: #000000;
            pointer-events: none;
            user-select: none;
        }
        .phone-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        #phone {
            padding-left: 60px; /* Adjusted to fit the prefix properly */
            text-align: left;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var phoneInput = document.getElementById('phone');

            phoneInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, ''); // إزالة أي أحرف غير الأرقام

                if (this.value.length > 0 && this.value.charAt(0) === '0') {
                    this.value = this.value.substring(1); // إزالة الصفر الأول
                }
            });
        });
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>إدخال رقم الهاتف</h1>
        <form action="" method="post">
            <div class="form-group">
                <label for="phone">رقم الهاتف</label>
                <div class="phone-container">
                    <div class="phone-prefix">964+</div>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <?php if ($lockout_message): ?>
                    <div class="lockout-message"><?php echo $lockout_message; ?></div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="error"><?php echo $error_message; ?></div>
                <?php endif; ?>
            </div>
            <input type="submit" value="إرسال الرمز">
        </form>
    </div>
</body>
</html>
