<?php
session_start(); // 세션 시작

// 로그인 상태 확인
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<script>
        alert('You must log in to access this page. Redirecting to the login page.');
        window.location.href = 'login.php';
    </script>";
    exit;
}

// 세션에서 사용자 정보 가져오기
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// 현재 페이지 이름 가져오기
$current_page = basename($_SERVER['PHP_SELF']);

// 역할에 따른 페이지 접근 제어
if ($role === 'doctor') {
    if (!in_array($current_page, ['Doctor_mainpage.php', 'checkreservation.php','view_Medical_Record.php','Medical_Record.php'])) {
        echo "<script>
            alert('Access Denied. Redirecting to the Doctor mainpage.');
            window.location.href = 'Doctor_mainpage.php';
        </script>";
        exit;
    }
} elseif ($role === 'patient') {
    if (!in_array($current_page, ['mainpage.php', 'view_Medical_Record_Patient.php','viewreservation.php','reservation.php'])) {
        echo "<script>
            alert('Access Denied. Redirecting to the Patient mainpage.');
            window.location.href = 'mainpage.php';
        </script>";
        exit;
    }
} else {
    // 잘못된 역할인 경우
    echo "<p>Invalid role detected. Please contact support.</p>";
    session_destroy();
    exit;
}
?>
