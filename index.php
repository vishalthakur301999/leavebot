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

    if(flag.strcmp("check")==1){
        $uname = $json->queryResult->outputContexts[0]->parameters->person->name;
        /*$chkquery = "select * from Leave_Balance where username = $uname";
        mysqli_query($conn,$query);*/
        $speech1 = "You have these many Leaves left";
        $response = new \stdClass();
        $response->fulfillmentText = $speech1;
        $response->source = "webhook";
        echo json_encode($response);
    }
    else if(flag.strcmp("apply")==1){
        $uname = $json->queryResult->outputContexts[0]->parameters->person->name;
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




