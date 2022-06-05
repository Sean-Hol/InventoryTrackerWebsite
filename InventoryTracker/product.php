
<?php
    session_start();
    //Expires code after 30mins
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
        $_SESSION = array();  
        session_destroy();   
    }
    $_SESSION['LAST_ACTIVITY'] = time();
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        echo "<script> window.location.replace('login.php') </script>";
        die();
    }
    $error_text="";
    //User can submit modified quantities which are used to update the database
    if (($_SERVER["REQUEST_METHOD"] == "POST") && (isset($_POST["id"])) && (isset($_POST["quantity"]))) {
        if (empty($_POST["id"])) {
            $error_text="Invalid request";
        } else {
            $searchterm = validateValues($_POST["id"]);
            $idNum = $searchterm;
        }
        if (empty($_POST["quantity"])) {
            $error_text="Invalid quantity";
        } else {
            $newCount = validateValues($_POST["quantity"]);
        }
        if ($error_text==""){
            $connResult = "";
            $servername = "localhost";
            $SQLusername = "root";
            $SQLpassword = "";
            $dbname = "inventory_db";
            $mysqli = new mysqli($servername, $SQLusername, $SQLpassword, $dbname);
            if ($mysqli === false) {
                die("Connection failed: " . mysqli_connect_error());
            }
            $sql = "UPDATE item SET item.quantity = ? WHERE (item.id LIKE ?)";
            
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("ss",$quantity,$searchtermid);
                $quantity = $newCount;
                $searchtermid = $idNum;
                $stmt->execute();
            }
        }
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
                position: sticky;
                top: 0;
                cursor: pointer;
                background-color: #131331;
                z-index: 900;
            }
            .mainbody{
                height:100%;
                width:100%;
                position:  relative;
                align-items: center;
                justify-content: center;
                position:  relative;
            }
            .inputbox{
            padding: 12px 20px;
            margin: 8px 0;
            box-sizing: border-box;
            border-radius: 20px;
            font-size: calc(10vw/3);
            }
            input[type=number] {
                height: 60px;
            }

            input[type=number]:hover::-webkit-inner-spin-button {  
                width: 14px;
                height: 60px;
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
            table {
            margin-left: auto;
            margin-right: auto;
            background-color: #c9c3ff94;
            border-collapse: collapse;
            width: 80%;
            margin-top: 50px;
            min-width: 400px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            border-radius: 10px 10px 0px 0px;
            border:none;
            font-size: calc(10vw/3);
            }

            th, td{
                padding: 6px 7px;
                border:none;
                border-bottom: 2px solid #131331;
            }
            tr:first-of-type{
                border-radius: 10px;
            }

            tr:last-of-type {
                border-bottom: 4px solid #131331;
            }
        </style>
    </head>
 
<body style="background: #181827;">
    <nav class="header">
        <h1 style="font-size: calc(10vw/2);color:lightgray;">Inventory Tracking</h1>
    </nav>
    <section class="mainBody">
        <?php
        $lastSearched = "";
        if (($_SERVER["REQUEST_METHOD"] == "POST") && (isset($_POST["id"]))) {
            if (empty($_POST["id"])) {
                $searchterm = "";
            } else {
                $searchterm = validateValues($_POST["id"]);
                $idNum = $searchterm;
            }
            $connResult = "";
            $servername = "localhost";
            $SQLusername = "root";
            $SQLpassword = "";
            $dbname = "inventory_db";
            $mysqli = new mysqli($servername, $SQLusername, $SQLpassword, $dbname);
            if ($mysqli === false) {
                die("Connection failed: " . mysqli_connect_error());
            }
            $sql = "SELECT * FROM item WHERE (item.id LIKE ?)";
            
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("s",$searchtermid);
                $searchtermid = $idNum;
                if($stmt->execute()){
                    $stmt->store_result();          
                    if ($stmt->num_rows == 1) {
                        echo '<table cellpadding="2" cellspacing="2" border="1" style="text-align:center;">
                        <tr><th width="40%">Field</th><th width="60%">Value</th></tr> ';
                    // output data of each row
                        $stmt->bind_result($id,$itemName,$quantity,$location,$supplier);
                        if($stmt->fetch()) {
                            echo "<tr><td>ID num</td><td>".$id."</td></tr>";
                            echo "<tr><td>Item Name</td><td>".$itemName."</td></tr>";
                            echo "<tr><td>Quantity</td><td>".$quantity."</td></tr>";
                            echo "<tr><td>Location</td><td>".$location."</td></tr>";
                            echo "<tr><td>Supplier</td><td>".$supplier."</td></tr>"; 
                        }
                        echo "</table>";
                        echo '<form action="product"  method="post" style="width:100%;justify-content: center; margin-left:50px;">
                        <input class="inputbox" type="number" name="quantity" min="0" value="'.$quantity.'"><input type="hidden" name="id" value="'.$id.'" />
                        <button class="btn" type="submit" style="font-size: calc(10vw/2);margin:10px;color:lightgray;">Change Quantity</button>
                        </form>';
                    } else {
                    echo "<p style='color:lightgray;text-align:center;font-size:calc(10vw/4)'>Invalid Product ID</p>";
                    }
                }}
        } else {
            header('Location: login.php', true, 300);
            die();
        }
       
        function validateValues($value) {
            $value = trim($value);
            $value = stripslashes($value);
            $value = htmlspecialchars($value);
            return $value;
            }
        ?>
        <form action="index" style="width:100%;display:flex;">
            <button class="btn" type="submit" style="font-size: calc(10vw/2);margin:30px;color:lightgray;">Back</button>
        </form>
    </section>
    </body>
</html>