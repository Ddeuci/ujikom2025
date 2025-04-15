<?php
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input tidak boleh kosong
    if (empty($username) || empty($password)) {
        echo "Username dan Password harus diisi!";
        exit;
    }

    // Cek apakah username sudah digunakan
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Username sudah terdaftar. Silakan gunakan username lain.";
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Hash password sebelum disimpan
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        echo "Pendaftaran berhasil! Silakan login.";
        header("Location: masuk.php");
        exit();
    } else {
        echo "Terjadi kesalahan. Silakan coba lagi.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #ff758c, #ff7eb3);
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .judul {
            color: #ff4d6d;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.3s;
        }

        input:focus {
            border-color: #ff758c;
            outline: none;
            box-shadow: 0px 0px 8px rgba(255, 117, 140, 0.5);
        }

        input[type="submit"] {
            background: #ff4d6d;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background: #ff2459;
        }

        p {
            margin-top: 15px;
            font-size: 14px;
        }

        a {
            text-decoration: none;
            color: #ff4d6d;
            font-weight: bold;
            transition: 0.3s;
        }

        a:hover {
            color: #ff2459;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="judul">Daftar Akun Baru</h2>
    <form action="" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Daftar">
    </form>
    <p>Sudah punya akun? <a href="masuk.php">Login di sini</a></p>
</div>

</body>
</html>
