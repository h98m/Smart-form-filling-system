<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_SESSION['phone'];
    $id_number = $_POST['id-number'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $address = $_POST['address'];

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

    // التحقق مما إذا كان السجل موجودًا
    $sql = "SELECT id FROM users WHERE phone='$phone'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // تحديث السجل الموجود
        $sql = "UPDATE users SET id_number='$id_number', name='$name', age=$age, address='$address' WHERE phone='$phone'";
    } else {
        // إدراج سجل جديد
        $sql = "INSERT INTO users (phone, id_number, name, age, address) VALUES ('$phone', '$id_number', '$name', $age, '$address')";
    }

    if ($conn->query($sql) === TRUE) {
        echo "تم حفظ البيانات بنجاح";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
