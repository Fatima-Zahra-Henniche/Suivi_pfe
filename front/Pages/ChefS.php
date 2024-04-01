<?php require 'connect.php'; ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div>
        <div>
            <div>
                <div>
                    <div>
                        <h1>Upload Excel File</h1>
                    </div>
                    <div>
                        <form action="connect.php" method="post" enctype="multipart/form-data">
                            <input type="file" name="exel" required value="">
                            <button type="submit" name="import">Import</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST['import'])) {
        $file = $_FILES['exel']['tmp_name'];
        $handle = fopen($file, "r");
        $c = 0;
        while (($filesop = fgetcsv($handle, 1000, ",")) !== false) {
            $name = $filesop[0];
            $email = $filesop[1];
            $phone = $filesop[2];
            $address = $filesop[3];
            $sql = "INSERT INTO `etudiants`(`name`, `email`, `phone`, `address`) VALUES ('$name','$email','$phone','$address')";
            $result = mysqli_query($conn, $sql);
            $c = $c + 1;
        }
        if ($result) {
            echo "You database has imported successfully. You have inserted " . $c . " records";
        } else {
            echo "Sorry! There is some problem.";
        }
    }
    ?>
</body>

</html>