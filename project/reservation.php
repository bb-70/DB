<?php
include('session_check.php'); 

// DB 접속 정보
$username = "DB501_PROJ_G3";
$password = "teamdb03";
$db = '(DESCRIPTION =   
        (ADDRESS_LIST =
            (ADDRESS = (PROTOCOL = TCP)(HOST = 203.249.87.57)(PORT = 1521))
        )
        (CONNECT_DATA = (SID = orcl))
    )';

// Establish connection to Oracle DB
$connect = oci_connect($username, $password, $db, 'AL32UTF8');

if (!$connect) {
    $e = oci_error();
    echo "Error connecting to database: " . $e['message'];
    exit;
}

// 예약 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_reservation'])) {
    $patientid = $_SESSION['username'];
    $doctorid = $_POST['doctorID']; // POST로 받은 doctorID 사용
    $symptoms = $_POST['symptoms'];
    $reservationdate = $_POST['reservationdate'];

    // 의사 이름 가져오기
    $doctor_query = "SELECT name FROM doctor WHERE doctorid = :doctorid";
    $doctor_statement = oci_parse($connect, $doctor_query);
    oci_bind_by_name($doctor_statement, ':doctorid', $doctorid);
    oci_execute($doctor_statement);
    $doctor_row = oci_fetch_assoc($doctor_statement);
    if ($doctor_row) {
        $doctor_name = $doctor_row['NAME'];
    }
    oci_free_statement($doctor_statement);

    // 예약 정보 삽입
    $insert_query = "INSERT INTO reservation (reservationid, patientid, doctorid, symptoms, reservationdate) VALUES (reservation_seq.nextval, :patientid, :doctorid, :symptoms, TO_TIMESTAMP(:reservationdate, 'YYYY-MM-DD HH24:MI'))";
    $insert_statement = oci_parse($connect, $insert_query);

    oci_bind_by_name($insert_statement, ':patientid', $patientid);
    oci_bind_by_name($insert_statement, ':doctorid', $doctorid);
    oci_bind_by_name($insert_statement, ':symptoms', $symptoms);
    oci_bind_by_name($insert_statement, ':reservationdate', $reservationdate);

    $result = oci_execute($insert_statement, OCI_COMMIT_ON_SUCCESS);
    if ($result) {
        oci_free_statement($insert_statement);
        oci_close($connect);
        // 예약이 성공하면 viewreservation.php로 리다이렉트
        header("Location: viewreservation.php");
        exit; // header 이후에 코드를 실행하지 않도록 종료합니다.
    } else {
        $e = oci_error($insert_statement);
        echo "<p class='message error'>Error creating reservation: " . $e['message'] . "</p>";
    }

    oci_free_statement($insert_statement);
}

oci_close($connect);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>진료 예약 페이지</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f7f7f7;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button, input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover, input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>진료 예약</h1>
        <form method="post" action="">
            <input type="hidden" name="doctorID" value="<?php echo isset($_POST['doctorID']) ? htmlspecialchars($_POST['doctorID'], ENT_QUOTES) : ''; ?>">

            <?php if (isset($doctor_name)): ?>
                <p>의사: <?php echo htmlspecialchars($doctor_name); ?></p>
            <?php endif; ?>

            <label for="symptoms">증상:</label>
            <textarea name="symptoms" id="symptoms" rows="4" required></textarea>

            <label for="reservationdate">예약 날짜 및 시간 (YYYY-MM-DD HH:MM):</label>
            <input type="text" name="reservationdate" id="reservationdate" placeholder="2024-12-31 14:30" required>

            <input type="hidden" name="confirm_reservation" value="1">
            <input type="submit" value="예약하기">
        </form>
        <a href="mainpage.php" class="button">뒤로가기</a>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#reservationdate", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true
            });
        });
    </script>
</body>
</html>
