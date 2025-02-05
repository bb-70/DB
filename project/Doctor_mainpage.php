<?php
include('session_check.php'); 
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Main</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .container h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .button-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .button {
            flex: 1;
            min-width: 150px;
            max-width: 200px;
            height: 150px;
            padding: 14px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
    
</head>
<body>

<div class="container">
    <?php
    echo "<h2>환영합니다, " . htmlspecialchars($_SESSION['username'], ENT_QUOTES) . "님</h2>";
    ?>
    <div class="button-container">
        <a href="checkreservation.php" class="button">예약 내역 확인</a>
        <a href="view_Medical_Record.php" class="button">진료 기록 내역</a>
        <a href="Medical_Record.php" class="button">진료 기록 작성</a>
    </div>
</div>

</body>
</html>
