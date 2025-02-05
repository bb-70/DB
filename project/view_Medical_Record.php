<?php
include('session_check.php'); // 교수 확인

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

$doctorid = $_SESSION['username']; // 세션 변수 확인

// 진료 기록 정보 조회 코드
$sql = "SELECT 
        M.RECORDID,
        P.NAME AS PATIENTNAME, 
        TO_CHAR(M.ReservationDate, 'YYYY-MM-DD HH24:MI') AS ReservationDate,
        M.SYMPTOMS,
        M.PRESCRIPTION
    FROM 
        MEDICALRECORD M
    JOIN 
        PATIENT P ON P.ID = M.PATIENTID
    WHERE 
        M.DOCTORID = :doctorid";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":doctorid", $doctorid); // 세션에서 가져온 의자 ID 사용
oci_execute($stid);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records</title>
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
    </style>
</head>
<body>
<div class="container">
    <h2>Medical Records</h2>

    <?php 
    // 여러 진료 기록 정보가 있을 경우 출력
    $hasRecords = false; // 진료 기록 정보가 있는지 확인하는 플래그

    while ($row = oci_fetch_assoc($stid)): 
        $hasRecords = true; ?>
        <table>
            <thead>
            <tr>
                <th>MedicalRecordID</th>
                <th>Patient Name</th>
                <th>Reservation Date</th>
                <th>Symptoms</th>
                <th>Prescription</th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($row['RECORDID'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['PATIENTNAME'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['RESERVATIONDATE'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['SYMPTOMS'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['PRESCRIPTION'], ENT_QUOTES) ?></td>
                </tr>
            </tbody>
        </table>
    <?php endwhile; ?>

    <?php if (!$hasRecords): ?>
        <p>No medical records found.</p>
    <?php endif; ?>

    <?php
    oci_free_statement($stid);
    oci_close($conn);
    ?>
    <a href="Doctor_mainpage.php" class="button">뒤로가기</a>
</div>
</body>
</html>
