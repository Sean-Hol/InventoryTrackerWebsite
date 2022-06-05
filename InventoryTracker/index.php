
<?php
    session_start();

    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
        $_SESSION = array();  
        session_destroy();   
    }
    $_SESSION['LAST_ACTIVITY'] = time();
    //Redirect if not logged in
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        echo "<script> window.location.replace('login.php') </script>";
        die();
    }
    ?>

<script>
    function submit_form(idNum){
        var form = document.getElementById(idNum);
        form.submit();
    }
</script>

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
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST["searchterm"])) {
                    $searchterm = "";
                } else {
                    $searchterm = validateValues($_POST["searchterm"]);
                    $lastSearched = $searchterm;
                }
            }
        ?>
        <form action="index" method="post" style="width:100%;display:flex;justify-content: center;">
            <input class="inputbox" type="text" name="searchterm" value="<?php echo $lastSearched ?>">
            <button class="btn" type="submit" style="font-size: calc(10vw/2);margin:30px;color:lightgray;">Search</button>
        </form>

        <?php 
        //Only run if request is a POST
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $connResult = "";
            $servername = "localhost";
            $SQLusername = "root";
            $SQLpassword = "";
            $dbname = "inventory_db";
            $mysqli = new mysqli($servername, $SQLusername, $SQLpassword, $dbname);
            if ($mysqli === false) {
                die("Connection failed: " . mysqli_connect_error());
            }
            // Sends search query to database using prepared statements
            $sql = "SELECT * FROM item WHERE (item.itemName LIKE ?) OR (item.id LIKE ?) OR (item.location LIKE ?)";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("sss", $searchterms,$searchtermid,$searchtermlocation);
                $searchterms = '%'.$searchterm.'%';
                $searchtermid = $searchterm;
                $searchtermlocation = '%'.$searchterm.'%';
                if($stmt->execute()){
                    $stmt->store_result();          
                    if ($stmt->num_rows > 0) {
                        echo '<table cellpadding="2" cellspacing="2" border="1" style="text-align:center;">
                        <tr><th width="40%">Item Name</th><th width="20%">Quantity</th><th width="20%">Location</th><th width="20%">Item ID</th></tr> ';
                    // output data of each row
                        $stmt->bind_result($id,$itemName,$quantity,$location,$supplier);
                        while($stmt->fetch()) {
                            echo "<form id=\"".$id."\" method='POST' action='product' ><tr onclick=\"submit_form(".$id.");\"><td>".$itemName."</td><td>".$quantity."</td><td>".$location."</td><td>".$id."</td><input type=\"hidden\" name=\"id\" value=".$id." /></tr></form>";
                        }
                        echo "</table>";
                        $stmt->close();
                    } else {
                    echo "<p style='color:lightgray;text-align:center;font-size:calc(10vw/4)'>0 results<br>You can search by name, location or id of item</p>";
                    }
                    
                }
                $mysqli->close();
            }
        }
        //Prevents XSS by using input santisation
        function validateValues($value) {
            $value = trim($value);
            $value = stripslashes($value);
            $value = htmlspecialchars($value);
            return $value;
            }
        ?>
        <form action="logout" style="width:100%;display:flex;">
            <button class="btn" type="submit" style="font-size: calc(10vw/2);margin:30px;color:lightgray;">Logout</button>
        </form>

    </section>
    </body>
</html>