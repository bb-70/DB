<?php
include('session_check.php'); 
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>의사 검색</title>
    <link rel="stylesheet" href="mainpage.css">
</head>
<body>
<div class="container">
    <?php
    echo "<h2>환영합니다, " . htmlspecialchars($_SESSION['username'], ENT_QUOTES) . "님</h2>";
    ?>

    <h2>의사 검색</h2>
    <a href='view_Medical_Record_Patient.php' class='link-button'>진료 내역 확인</a>
    <a href='viewreservation.php' class='link-button'>예약 기록 확인</a>

    <form method="POST" action="">
        <label for="doctorName">의사 이름 또는 진료과</label>
        <input type="text" id="doctorName" name="doctorName" placeholder="의사 이름 또는 진료과를 입력하세요" required>

        <button type="submit">검색</button>
    </form>

    <?php
    $username = "DB501_PROJ_G3";
    $password = "teamdb03";
    $db = '(DESCRIPTION = 	
        (ADDRESS_LIST=
            (ADDRESS = (PROTOCOL = TCP)(HOST = 203.249.87.57)(PORT = 1521))
        )
        (CONNECT_DATA = (SID = orcl))
    )';

    $connect = oci_connect($username, $password, $db, 'AL32UTF8');

    if (!$connect) {
        $e = oci_error();
        echo "<div class='message error'><p>데이터베이스 연결 오류: " . htmlentities($e['message'], ENT_QUOTES) . "</p></div>";
        error_log("Database Connection Error: " . $e['message']);
        exit;
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doctorName'])) {
            $doctorName = htmlspecialchars($_POST['doctorName'], ENT_QUOTES);

            $query = "SELECT NAME, DEPARTMENT, NURSEID, DOCTORID FROM DOCTOR WHERE LOWER(NAME) LIKE LOWER(:search) OR LOWER(DEPARTMENT) LIKE LOWER(:search)";
            $stmt = oci_parse($connect, $query);
            $searchTerm = "%" . $doctorName . "%";
            oci_bind_by_name($stmt, ':search', $searchTerm);
        } else {
            $query = "SELECT NAME, DEPARTMENT, NURSEID, DOCTORID FROM DOCTOR";
            $stmt = oci_parse($connect, $query);
        }

        if (oci_execute($stmt)) {
            echo "<table>";
            echo "<tr><th>이름</th><th>진료과</th><th>간호사 ID</th><th>예약</th></tr>";

            $found = false;
            while ($row = oci_fetch_assoc($stmt)) {
                $found = true;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['NAME'], ENT_QUOTES) . "</td>";
                echo "<td>" . htmlspecialchars($row['DEPARTMENT'], ENT_QUOTES) . "</td>";
                echo "<td>" . htmlspecialchars($row['NURSEID'], ENT_QUOTES) . "</td>";
                echo "<td>
                    <form method='POST' action='reservation.php'>
                        <input type='hidden' name='doctorID' value='" . htmlspecialchars($row['DOCTORID'], ENT_QUOTES) . "'>
                        <button type='submit'>예약</button>
                    </form>
                </td>";
                echo "</tr>";
            }

            if (!$found) {
                echo "<tr><td colspan='4'>검색 결과가 없습니다.</td></tr>";
            }

            echo "</table>";
        } else {
            $e = oci_error($stmt);
            echo "<div class='message error'><p>쿼리 실행 오류: " . htmlentities($e['message'], ENT_QUOTES) . "</p></div>";
            error_log("Query Execution Error: " . $e['message']);
        }

        oci_free_statement($stmt);
        oci_close($connect);
    }
    ?>
</div>
</body>
</html>
