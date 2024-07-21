<?php
session_start();
$user_data = $_SESSION['user_data'] ?? array(
    'id_number' => '',
    'name' => '',
    'age' => '',
    'address' => ''
);
 
$phone_number = $_SESSION['phone'] ?? '';
// إزالة رمز البلد (+964) إن وجد
if (strpos($phone_number, '964') === 0) {
    $phone_number = substr($phone_number, 3); // إزالة +964
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام التقديم الذكي</title>
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
        .error {
            color: red;
            margin-top: 5px;
        }
        .phone-prefix {
            position: absolute;
            left: 10px;
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
        .phone-container input {
            padding-left: 70px; /* Adjusted to fit the prefix properly */
        }
        .phone-prefix {
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        .phone-container input {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>التقديم الذكي</h1>
        <form id="applicationForm" action="submit_form.php" method="post">
            <div class="form-group">
                <label for="id-number">رقم الهوية</label>
                <input type="text" id="id-number" name="id-number" value="<?php echo htmlspecialchars($user_data['id_number']); ?>" readonly required>
            </div>
            <div class="form-group">
                <label for="name">الاسم</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" readonly required>
            </div>
            <div class="form-group">
                <label for="age">العمر</label>
                <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($user_data['age']); ?>" readonly required>
                <span id="age-error" class="error"></span>
            </div>
            <div class="form-group">
                <label for="phone">رقم الهاتف</label>
                <div class="phone-container">
                    <span class="phone-prefix">964+ </span>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone_number); ?>" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="address">العنوان</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user_data['address']); ?>" readonly required>
            </div>
            <input type="submit" value="إرسال">
        </form>
    </div>
    <script>
        document.getElementById('applicationForm').addEventListener('submit', function(event) {
            var ageInput = document.getElementById('age');
            var age = ageInput.value;
            var ageError = document.getElementById('age-error');
            var phoneInput = document.getElementById('phone');
            var phone = phoneInput.value.replace(/^0+/, '');
            var phoneError = document.getElementById('phone-error');
            
            var validPhone = /^(77|78|75)\d{7}$/;

            if (age > 60) {
                ageError.textContent = "العمر يجب أن يكون 60 عامًا أو أقل.";
                event.preventDefault();
            } else {
                ageError.textContent = "";
            }

            if (!validPhone.test(phone)) {
                phoneError.textContent = "رقم الهاتف يجب أن يبدأ بـ 77 أو 78 أو 75 ويتألف من 10 أرقام.";
                event.preventDefault();
            } else {
                phoneError.textContent = "";
            }
        });

        // منع النسخ من الحقول
        document.querySelectorAll('input[readonly]').forEach(function(input) {
            input.addEventListener('copy', function(event) {
                event.preventDefault();
            });
        });
    </script>
</body>
</html>
