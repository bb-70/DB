<?php
session_start(); // 세션 시작
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>

<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        input[type="radio"] {
            margin-right: 5px;
        }

        .form-group input[type="radio"] {
            margin-right: 10px;
        }

        .submit-btn {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        .message {
            text-align: center;
            margin-top: 20px;
        }

        .message p {
            color: #d9534f;
        }

        .success {
            color: #5cb85c;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <form method="POST" action="">
            <div class="form-group">
                <label for="role">Login as:</label><br>
                <input type="radio" id="doctor" name="role" value="doctor">
                <label for="doctor">Doctor</label>
                <input type="radio" id="patient" name="role" value="patient" checked>
                <label for="patient">Patient</label>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" class="submit-btn" value="Login">
            </div>
            <div class="message">
                <p>Don't have an account? <a href="signup.php" style="color: #4CAF50; text-decoration: underline;">Sign Up</a></p>
            </div>

        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $role = $_POST['role'];
            $username = $_POST['username'];
            $password = $_POST['password'];

            $db = '(DESCRIPTION = 	
                (ADDRESS_LIST=
                    (ADDRESS = (PROTOCOL = TCP)(HOST = 203.249.87.57)(PORT = 1521))
                )
                (CONNECT_DATA = (SID = orcl))
            )';

            $connect = oci_connect("DB501_PROJ_G3", "teamdb03", $db, 'AL32UTF8');

            if (!$connect) {
                $e = oci_error();
                echo "<div class='message'><p>Database Connection Error: " . htmlentities($e['message'], ENT_QUOTES) . "</p></div>";
                exit;
            }

            $table = ($role == "doctor") ? "DOCTOR" : "PATIENT";
            $id_column = ($role == "doctor") ? "DOCTORID" : "ID";

            $sql = "SELECT * FROM $table WHERE $id_column = :id AND PASSWORD = :password";
            $stid = oci_parse($connect, $sql);
            oci_bind_by_name($stid, ":id", $username);
            oci_bind_by_name($stid, ":password", $password);
            oci_execute($stid);

            if ($row = oci_fetch_array($stid, OCI_ASSOC)) {
                // 세션 정보 저장
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                $_SESSION['loggedin'] = true;

                // 로그인 성공 시 역할에 따라 다른 페이지로 이동
                if ($role === 'doctor') {
                    header("Location: Doctor_mainpage.php");
                } else {
                    header("Location: mainpage.php");
                }
                exit;
            } else {
                echo "<div class='message'><p>Invalid credentials. Please try again.</p></div>";
            }

            oci_free_statement($stid);
            oci_close($connect);
        }
        ?>
    </div>
</body>
</html>
