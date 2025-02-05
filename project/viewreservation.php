<?php
include('session_check.php');

// DB 연결
$db = '(DESCRIPTION =
    (ADDRESS_LIST =
        (ADDRESS = (PROTOCOL = TCP)(HOST = 203.249.87.57)(PORT = 1521))
    )
    (CONNECT_DATA = (SID = orcl))
)';

$conn = oci_connect("DB501_PROJ_G3", "teamdb03", $db, "AL32UTF8");

if (!$conn) {
    $e = oci_error();
    echo "<p>Database connection error: " . htmlentities($e['message'], ENT_QUOTES) . "</p>";
    exit;
}

// 세션에서 환자 ID 가져오기
$patientid = $_SESSION['username']; // 세션 변수 확인

// 예약 정보 조회 SQL (환자 이름 추가)
$sql = "SELECT 
        R.ReservationID,
        TO_CHAR(R.ReservationDate, 'YYYY-MM-DD HH24:MI') AS ReservationDate,
        D.NAME AS DoctorName,
        P.NAME AS PatientName,  -- 환자 이름 추가
        R.SYMPTOMS
    FROM 
        Reservation R
    JOIN 
        Doctor D ON R.DoctorID = D.DoctorID
    JOIN
        Patient P ON R.PatientID = P.ID  -- 환자 정보 추가
    WHERE 
        R.PatientID = :patientid";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":patientid", $patientid); // 세션 ID 바인딩
oci_execute($stid);

// 예약 삭제 처리
if (isset($_POST['delete'])) {
    $reservationIdToDelete = $_POST['reservationIdToDelete'];

    // 예약 삭제 SQL
    $deleteSql = "DELETE FROM Reservation WHERE ReservationID = :reservationId";
    $deleteStid = oci_parse($conn, $deleteSql);
    oci_bind_by_name($deleteStid, ":reservationId", $reservationIdToDelete);
    oci_execute($deleteStid);

    echo "<p>Reservation deleted successfully.</p>";
}

// 예약 날짜 변경 처리
if (isset($_POST['update'])) {
    $reservationIdToUpdate = $_POST['reservationIdToUpdate'];
    $newReservationDate = $_POST['newReservationDate'];

    // 날짜 형식 변환 (datetime-local 형식을 'YYYY-MM-DD HH24:MI' 형식으로 변환)
    $formattedDate = date("Y-m-d H:i", strtotime($newReservationDate));

    // 예약 날짜 변경 SQL
    $updateSql = "UPDATE Reservation SET ReservationDate = TO_DATE(:newDate, 'YYYY-MM-DD HH24:MI') WHERE ReservationID = :reservationId";
    $updateStid = oci_parse($conn, $updateSql);
    oci_bind_by_name($updateStid, ":newDate", $formattedDate);
    oci_bind_by_name($updateStid, ":reservationId", $reservationIdToUpdate);
    oci_execute($updateStid);

    echo "<p>Reservation date updated successfully.</p>";
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reservations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f7f7f7;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            margin-right: 5px;
        }
        .button-danger {
            background-color: red;
        }
        .button-update {
            background-color: green;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Reservation List</h2>

    <?php 
    $hasReservations = false; // 예약이 있는지 확인하는 플래그

    while ($row = oci_fetch_assoc($stid)): 
        $hasReservations = true; ?>
        <table>
            <thead>
            <tr>
                <th>Reservation ID</th>
                <th>Reservation Date</th>
                <th>Doctor Name</th>
                <th>Patient Name</th>  <!-- 환자 이름 추가 -->
                <th>Symptoms</th>
                <th>Actions</th>  <!-- 예약 삭제 및 날짜 변경 버튼 -->
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($row['RESERVATIONID'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['RESERVATIONDATE'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['DOCTORNAME'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['PATIENTNAME'], ENT_QUOTES) ?></td>  <!-- 환자 이름 출력 -->
                    <td><?= htmlspecialchars($row['SYMPTOMS'], ENT_QUOTES) ?></td>
                    <td>
                        <!-- 예약 삭제 -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="reservationIdToDelete" value="<?= htmlspecialchars($row['RESERVATIONID'], ENT_QUOTES) ?>">
                            <button type="submit" name="delete" class="button button-danger">Delete</button>
                        </form>
                        <!-- 예약 날짜 변경 -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="reservationIdToUpdate" value="<?= htmlspecialchars($row['RESERVATIONID'], ENT_QUOTES) ?>">
                            <input type="datetime-local" name="newReservationDate" required>
                            <button type="submit" name="update" class="button button-update">Update Date</button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php endwhile; ?>

    <?php if (!$hasReservations): ?>
        <p>No reservations found.</p>
    <?php endif; ?>

    <?php
    oci_free_statement($stid);
    oci_close($conn);
    ?>
    <a href="mainpage.php" class="button">뒤로가기</a>
</div>
</body>
</html>
