<?php
  error_reporting(E_ALL);
  ini_set("display_errors", 1);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 입력된 데이터 가져오기
    $id = $_POST['ID'];
    $password = $_POST['PASSWORD'];
    $name = $_POST['NAME'];
    $dob = $_POST['dob'];
    $gender = $_POST['GENDER'];
    $ssn_first = $_POST['ssn_first'];
    $ssn_second = $_POST['ssn_second'];
    $phone = $_POST['PHONE'];
    $address = $_POST['ADDRESS'];

    // 나이 계산
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;

    // Oracle DB 연결
    $db = '(DESCRIPTION = 
      (ADDRESS_LIST=
        (ADDRESS = (PROTOCOL = TCP)(HOST = 203.249.87.57)(PORT = 1521))
      )
      (CONNECT_DATA = (SID = orcl))
    )';
    $connect = oci_connect("DB501_PROJ_G3", "teamdb03", $db, 'UTF8');

    if (!$connect) {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }

    $stid_check = null;
    $stid_insert = null;

    try {
        // ID 중복 확인
        $sql_check = "SELECT * FROM Patient WHERE ID = :id";
        $stid_check = oci_parse($connect, $sql_check);
        if (!$stid_check) {
            throw new Exception("Failed to prepare the statement for checking user.");
        }

        oci_bind_by_name($stid_check, ":id", $id);
        oci_execute($stid_check);

        if (oci_fetch_array($stid_check, OCI_ASSOC)) {
            $message = "Username already exists. Please choose a different username.";
        } else {
            // 새로운 사용자 추가
            $ssn = $ssn_first . $ssn_second;
            $sql_insert = "INSERT INTO Patient (ID, PASSWORD, NAME, AGE, GENDER, SSN, PHONE, ADDRESS) 
                           VALUES (:id, :password, :name, :age, :gender, :ssn, :phone, :address)";
            $stid_insert = oci_parse($connect, $sql_insert);
            if (!$stid_insert) {
                throw new Exception("Failed to prepare the statement for insertion.");
            }

            oci_bind_by_name($stid_insert, ":id", $id);
            oci_bind_by_name($stid_insert, ":password", $password);
            oci_bind_by_name($stid_insert, ":name", $name);
            oci_bind_by_name($stid_insert, ":age", $age);
            oci_bind_by_name($stid_insert, ":gender", $gender);
            oci_bind_by_name($stid_insert, ":ssn", $ssn);
            oci_bind_by_name($stid_insert, ":phone", $phone);
            oci_bind_by_name($stid_insert, ":address", $address);

            $result = oci_execute($stid_insert);
            if ($result) {
                echo "<script>
                    alert('Sign Up Successful! You can now log in.');
                    window.location.href = 'login.php';
                </script>";
            } else {
                $e = oci_error($stid_insert);
                throw new Exception("Sign Up Failed: " . htmlentities($e['message'], ENT_QUOTES));
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    } finally {
        // 리소스 해제
        if ($stid_check && is_resource($stid_check)) {
            oci_free_statement($stid_check);
        }
        if ($stid_insert && is_resource($stid_insert)) {
            oci_free_statement($stid_insert);
        }
        oci_close($connect);
    }
  }
?>

<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f7f7f7;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            color: red;
        }
        .message.success {
            color: green;
        }
        input[type="text"], input[type="password"], input[type="date"], input[type="tel"], textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="radio"] {
            margin-right: 5px;
        }

        .radio-container {
            margin-bottom: 16px;
        }
        .ssn-container {
            display: flex;
            justify-content: space-between;
        }
        .ssn-container input {
            width: 48%;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .error {
            color: red;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Patient Sign Up</h2>
    <form method="POST" action="" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="username">Patient ID</label>
            <input type="text" id="username" name="ID" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="PASSWORD" maxlength="12" required>
        </div>

        <div class="form-group">
            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm_password" required>
            <span class="error" id="password_error"></span>
        </div>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="NAME" required>
        </div>

        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" required>
        </div>

        <div class="form-group">
            <label>Gender</label>
            <div class="radio-container">
                <input type="radio" id="male" name="GENDER" value="M" required> Male
                <input type="radio" id="female" name="GENDER" value="F" required> Female
            </div>
        </div>

        <div class="form-group">
            <label for="ssn">SSN</label>
            <div class="ssn-container">
                <input type="text" id="ssn_first" name="ssn_first" maxlength="6" placeholder="First 6 digits" required>
                <input type="password" id="ssn_second" name="ssn_second" maxlength="7" placeholder="Last 7 digits" required>
                <button type="button" onclick="toggleSSNVisibility()">Show/Hide SSN</button>
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="PHONE" required>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="ADDRESS" rows="4" required></textarea>
        </div>

        <button type="submit">Sign Up</button>
    </form>

    <div class="message">
        <?php if (isset($message)) echo "<p>$message</p>"; ?>
    </div>
</div>

<script>
  function toggleSSNVisibility() {
    var ssnSecondField = document.getElementById('ssn_second');
    var type = ssnSecondField.type === "password" ? "text" : "password";
    ssnSecondField.type = type;
  }

  function validateForm() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;
    var passwordError = document.getElementById("password_error");

    if (password !== confirmPassword) {
      passwordError.textContent = "Passwords do not match. Please check again.";
      return false;
    } else {
      passwordError.textContent = "";
      return true;
    }
  }
</script>

</body>
</html>
