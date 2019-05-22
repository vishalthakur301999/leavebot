<?php
$servername = "sql12.freemysqlhosting.net";
$username = "sql12291966";
$password = "aJehBgyhb2";
$db = "sql12291966";
$conn = mysqli_connect($servername,$username,$password);
mysqli_select_db($conn,$db);

$method = $_SERVER['REQUEST_METHOD'];

// Process only when method is POST
if($method == 'POST'){
    $requestBody = file_get_contents('php://input');

    $json = json_decode($requestBody);

    $flag = "";
    $flag = $json->queryResult->outputContexts[0]->parameters->flag;
    if(strcmp("login",$flag)){
        $uname = $json->queryResult->outputContexts[1]->parameters->person->name;
        $chkquery = "select * from Leave_Balance where username = $uname";
        mysqli_query($conn,$query);
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            echo "Hey ".$uname."! What do you want to do today?";
        }
        else{
            $speech1 = "Invalid user";
            $response = new \stdClass();
            $response->fulfillmentText = $speech1;
            $response->source = "webhook";
            echo json_encode($response);
        }

    }
    else if(strcmp("check",$flag)==0){
        $uname = $json->queryResult->outputContexts[1]->parameters->person->name;
        $chkquery = "select * from Leave_Balance where username = $uname";
        mysqli_query($conn,$query);
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $speech1 = "You have $row[CL_Balance] Casual Leaves and $row[PL_Balance] Paid Leaves Left";
            $response = new \stdClass();
            $response->fulfillmentText = $speech1;
            $response->source = "webhook";
            echo json_encode($response);
        }


    }
    else if(strcmp("apply",$flag)==0){
        $uname = $json->queryResult->outputContexts[1]->parameters->person->name;
        /*$chkquery = "select * from Leave_Balance where username = $uname";
        $query = "INSERT INTO Bookings(ID, NoM, DateTime) VALUES ('',$number,'$date_time')";
        mysqli_query($conn,$query);*/
        $speech2 = "You want to apply leave";
        $response = new \stdClass();
        $response->fulfillmentText = $speech2;
        $response->source = "webhook";
        echo json_encode($response);
    }
}
else
{
    echo "Method not allowed";
}

?>




