<?php
session_start();
$phone = $_SESSION['phone'] ?? '';

// تعريف متغيرات للتحكم في عدد المحاولات والقفل
$max_attempts = 5; // عدد الخطأ المسموح به قبل القفل
$lockout_time = 5 * 60; // وقت القفل بالثواني (هنا 5 دقائق)
 
// التحقق من وقت القفل
if (isset($_SESSION['last_attempt_time']) && time() - $_SESSION['last_attempt_time'] < $lockout_time) {
    $remaining_time = $lockout_time - (time() - $_SESSION['last_attempt_time']);
    echo "<p>لقد تم حظر المحاولات لمدة " . gmdate("i دقيقة و s ثانية", $remaining_time) . ". يرجى المحاولة لاحقاً.</p>";
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // عدد المحاولات الخاطئة
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 0;
    }

    $entered_code = $_POST['code'];
    $stored_code = $_SESSION['verification_code'];

    if ($entered_code == $stored_code) {
        // إعادة تعيين عدد المحاولات ووقت القفل عند النجاح
        $_SESSION['attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;

        // التحقق ناجح، استرجاع البيانات من قاعدة البيانات
        $servername = "localhost";
        $username = "root"; // أو اسم المستخدم الخاص بك
        $password = ""; // كلمة مرور MySQL (اتركها فارغة إذا لم تكن قد قمت بتعيينها)
        $dbname = "user_verification";

        // إنشاء الاتصال
        $conn = new mysqli($servername, $username, $password, $dbname);

        // التحقق من الاتصال
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT id_number, name, age, address FROM users WHERE phone='$phone'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // البيانات موجودة، حفظها في الجلسة
            $row = $result->fetch_assoc();
            $_SESSION['user_data'] = $row;
        } else {
            // المستخدم غير موجود، إعداد البيانات فارغة في الجلسة
            $_SESSION['user_data'] = array(
                'id_number' => '',
                'name' => '',
                'age' => '',
                'address' => ''
            );
        }

        $conn->close();
        header('Location: form.php');
        exit();
    } else {
        // زيادة عدد المحاولات عند الخطأ
        $_SESSION['attempts']++;

        if ($_SESSION['attempts'] >= $max_attempts) {
            $_SESSION['last_attempt_time'] = time(); // وقت القفل يبدأ من الآن
            header('Location: index.php'); // توجيه إلى index.php عند الحظر
            exit();
        } else {
            $error_message = 'رمز غير صحيح. يرجى المحاولة مرة أخرى.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدخال الرمز</title>
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
        .space {
            margin-top: 15px; /* زيادة المسافة إلى 30 بكسل */
        }
        .error-message {
            color: red;
            margin-top: 5px;
            font-weight: bold;
            color: #555;
            display: block;
        }
        
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var codeInput = document.getElementById('code');

            codeInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, ''); // إزالة أي أحرف غير الأرقام
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>إدخال الرمز</h1>
        <form action="" method="post">
            <div class="form-group">
                <label for="code">الرمز</label>
                <input type="text" id="code" name="code" required>
                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
            </div>
            <div class="space"></div>
            <input type="submit" value="تحقق">
        </form>
    </div>
</body>
</html>
