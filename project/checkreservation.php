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

// 세션에서 의사 ID 가져오기
$doctorid = $_SESSION['username']; // 세션에서 의사 ID를 가져온다고 가정

// 환자 예약 정보 조회 쿼리
$sql = "SELECT 
        R.ReservationID, 
        R.PatientID, 
        P.NAME AS PatientName, 
        TO_CHAR(R.ReservationDate, 'YYYY-MM-DD HH24:MI') AS ReservationDate, 
        R.SYMPTOMS
    FROM 
        RESERVATION R
    JOIN 
        PATIENT P ON R.PATIENTID = P.ID
    WHERE 
        R.DOCTORID = :doctorid
    ORDER BY R.ReservationDate ASC";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":doctorid", $doctorid);
oci_execute($stid);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Reservations</title>
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
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Patient Reservations</h2>

    <?php 
    $hasReservations = false; // 예약 정보가 있는지 확인하는 플래그
    ?>

    <table>
        <thead>
        <tr>
            <th>Reservation ID</th>
            <th>Patient ID</th>
            <th>Patient Name</th>
            <th>Reservation Date</th>
            <th>Symptoms</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = oci_fetch_assoc($stid)): 
            $hasReservations = true; ?>
            <tr>
                <td><?= htmlspecialchars($row['RESERVATIONID'], ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($row['PATIENTID'], ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($row['PATIENTNAME'], ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($row['RESERVATIONDATE'], ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($row['SYMPTOMS'], ENT_QUOTES) ?></td>
                <td>
                    <form method="POST" action="Medical_Record.php">
                        <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($row['RESERVATIONID'], ENT_QUOTES) ?>">
                        <input type="hidden" name="patient_id" value="<?= htmlspecialchars($row['PATIENTID'], ENT_QUOTES) ?>">
                        <input type="hidden" name="symptoms" value="<?= htmlspecialchars($row['SYMPTOMS'], ENT_QUOTES) ?>">
                        <input type="hidden" name="reservation_date" value="<?= htmlspecialchars($row['RESERVATIONDATE'], ENT_QUOTES) ?>">
                        <button type="submit">Send to Medical Record</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php if (!$hasReservations): ?>
        <p>No reservations found for you.</p>
    <?php endif; ?>

    <?php
    oci_free_statement($stid);
    oci_close($conn);
    ?>
</div>
</body>
</html>
