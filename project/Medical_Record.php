<?php
include('session_check.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Medical_Record'])) {
    // 입력 값 가져오기
    $reservationID = $_POST['reservation_id'] ?? '';
    $patientID = $_POST['patient_id'] ?? '';
    $doctorid = $_SESSION['username'];
    $symptoms = $_POST['symptoms'] ?? '';
    $reservationDate = $_POST['reservation_date'] ?? '';
    $prescription = $_POST['prescription'] ?? '';

    // 날짜 포맷 검증
    $dateValid = DateTime::createFromFormat('Y-m-d H:i', $reservationDate) !== false;
    if (!$dateValid) {
        die("Invalid date format for Reservation Date.");
    }

    // DB 연결
    $db = '(DESCRIPTION = 
      (ADDRESS_LIST=
        (ADDRESS = (PROTOCOL = TCP)(HOST = 203.249.87.57)(PORT = 1521))
      )
      (CONNECT_DATA = (SID = orcl))
    )';
    $connect = oci_connect("DB501_PROJ_G3", "teamdb03", $db, 'AL32UTF8');

    if (!$connect) {
        $e = oci_error();
        die("DB Connection Error: " . htmlentities($e['message'], ENT_QUOTES));
    }

    // 다음 RecordID를 가져오기 위한 쿼리
    $sql_get_id = "SELECT NVL(MAX(RecordID), 0) + 1 AS NextRecordID FROM MedicalRecord";//medicalrecord테이블에서 recordid의 max값을 가져와서 NULL이면 0반환 그리고 +1해서 NextRecordID로 부름 
    $stid_get_id = oci_parse($connect, $sql_get_id);//데이터베이스에 쿼리를 실행하기 위해 oci_parse사용 $connect는 연결리소스 $sql_get_id는 실행할 SQL쿼리
    oci_execute($stid_get_id); //준비된 SQL문을 실행

    $nextRecordID = null;
    if ($row = oci_fetch_assoc($stid_get_id)) { //oci_fetch_assoc는 결과 행을 연관 배열 형태로 반환
        $nextRecordID = $row['NEXTRECORDID'];//값을 변수애 저장
    } else {
        die("Error fetching next RecordID");//결과가 없으면 스크립트 종료하고 에러메세지 출력
    }
    oci_free_statement($stid_get_id);//사용이 끝난 쿼리리소스 해제

    // 진료기록 추가 쿼리
    $sql_insert = "INSERT INTO MedicalRecord (RecordID, ReservationID, PatientID, DoctorID, Symptoms, ReservationDate, Prescription) 
                   VALUES (:recordID, :reservationID, :patientID, :doctorID, :symptoms, TO_DATE(:reservationDate, 'YYYY-MM-DD HH24:MI'), :prescription)";
    $stid_insert = oci_parse($connect, $sql_insert);

    // 바인딩 변수
    oci_bind_by_name($stid_insert, ":recordID", $nextRecordID);
    oci_bind_by_name($stid_insert, ":reservationID", $reservationID);
    oci_bind_by_name($stid_insert, ":patientID", $patientID);
    oci_bind_by_name($stid_insert, ":doctorID", $doctorid); 
    oci_bind_by_name($stid_insert, ":symptoms", $symptoms);
    oci_bind_by_name($stid_insert, ":reservationDate", $reservationDate);
    oci_bind_by_name($stid_insert, ":prescription", $prescription);

    // SQL 실행 및 COMMIT
    if (oci_execute($stid_insert, OCI_NO_AUTO_COMMIT)) {
        oci_commit($connect);
        oci_free_statement($stid_insert);
        oci_close($connect);

        // 성공 시 doctor_mainpage로 리디렉션
        header("Location: view_Medical_Record.php");
        exit;
    } else {
        $e = oci_error($stid_insert);
        echo "<div class='message error'><p>Error adding record: " . htmlentities($e['message'], ENT_QUOTES) . "</p></div>";
        oci_free_statement($stid_insert);
        oci_close($connect);
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Record</title>
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
        input[type="text"], input[type="date"], textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
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
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Medical Record</h2>
    <form method="POST" action="">
        <label for="reservationID">Reservation ID</label>
        <input type="text" id="reservationID" name="reservation_id" value="<?= htmlspecialchars($_POST['reservation_id'] ?? '', ENT_QUOTES) ?>" readonly>

        <label for="patientID">Patient ID</label>
        <input type="text" id="patientID" name="patient_id" value="<?= htmlspecialchars($_POST['patient_id'] ?? '', ENT_QUOTES) ?>" readonly>

        <label for="symptoms">Symptoms</label>
        <textarea id="symptoms" name="symptoms" rows="4"><?= htmlspecialchars($_POST['symptoms'] ?? '', ENT_QUOTES) ?></textarea>

        <label for="reservationDate">Reservation Date</label>
        <input type="text" id="reservationDate" name="reservation_date" value="<?= htmlspecialchars($_POST['reservation_date'] ?? '', ENT_QUOTES) ?>" readonly>

        <label for="prescription">Prescription</label>
        <textarea id="prescription" name="prescription" rows="4" value="<?= htmlspecialchars($_POST['prescription'] ?? '', ENT_QUOTES) ?>"></textarea>

        <input type="hidden" name="Medical_Record" value="1">
        <button type="submit">Add Record</button>
    </form>
</div>

</body>
</html>
