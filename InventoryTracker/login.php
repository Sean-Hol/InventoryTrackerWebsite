<?php
    session_start();
    // define variables and set to empty values
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
        header('Location: topics.php', true, 300);
        die();
    }
    $errorText = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user = $pass = "";
        $servername = "localhost";
        $SQLusername = "root";
        $SQLpassword = "";
        $dbname = "inventory_db";
        if (empty($_POST["password"])) {
            $errorText = "Password missing";
        } else {
            $password = validateValues($_POST["password"]);
        }
        if (empty($_POST["username"])) {
            $errorText = "Username missing";
        } else {
            $username = validateValues($_POST["username"]);
        }
        $mysqli = new mysqli($servername, $SQLusername, $SQLpassword, $dbname);
        if ($mysqli === false) {
            die("Connection failed: " . mysqli_connect_error());
        }
        if ($errorText == "") {
            $sql = "SELECT id, username, password FROM users WHERE username = ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("s", $param_username);
                $param_username = $username;
                if($stmt->execute()){
                    $stmt->store_result();
                    if($stmt->num_rows == 1){                    
                        $stmt->bind_result($id, $username, $hashed_password);
                        if($stmt->fetch()){
                            if(password_verify($password, $hashed_password)){
                                session_regenerate_id();
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;  
                                       
                                echo "<script> window.location.replace('index.php') </script>";
                                die();
                            } else{
                                $errorText = "Invalid username or password.";
                            }
                        } else{
                            $errorText = "Oops! Something went wrong. Please try again later.";
                        }
                        $stmt->close();
                    }
                    else {
                        $errorText = "Invalid username or password.";
                    }
                }
                $mysqli->close();
            }
        }
    }
    function validateValues($value) {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value);
        return $value;
        }
    ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Inventory Tracker</title>
        <style>
            * {
                font-family: 'Ubuntu', sans-serif;
            }
            .header {
                display: flex;
                align-items: center;
                justify-content: center;
                top: 0;
                cursor: pointer;
                background-color: #131331;
            }
            .mainbody{
                height:100%;
                width:100%;
                position:  relative;
                align-items: center;
                justify-content: center;
            }
            .inputbox{
            padding: 12px 20px;
            margin: 10px 20px;
            box-sizing: border-box;
            border-radius: 20px;
            font-size: calc(10vw/3);
            }
            .btn {
            padding: 12px 20px;
            margin: 8px 0;
            border: 2px solid rgb(0, 0, 0);
            border-radius: 8px;
            color: rgb(38, 38, 97);
            background-color:#131331;
            cursor: pointer;
            }
        </style>
    </head>
 
<body style="background: #181827;">
    <nav class="header">
        <h1 style="font-size: calc(10vw/2);color:lightgray;">Inventory Tracking</h1>
    </nav>
    <section class="mainBody">
        <h1>Login</h1>
        <form action="login" method="post" style="width:100%;justify-content: center;">
            <input class="inputbox" type="text" name="username" placeholder="Username"><br>
            <input class="inputbox" type="password" name="password" placeholder="Password"><br>
            <p style="color:red;font:30px;"><?php echo $errorText ?></p>
            <button class="btn" type="submit" style="font-size: calc(10vw/2);margin:10px;color:lightgray;">Login</button>
        </form>


</section>
        </body>
</html>