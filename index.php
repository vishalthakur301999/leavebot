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
    $flag = $json->queryResult->outputContexts[0]->parameters->flag;
    if(flag.strcmp("Check")){
        $uname = $json->queryResult->outputContexts[0]->parameters->person->name;
        /*$chkquery = "select * from Leave_Balance where username = $uname";
        mysqli_query($conn,$query);*/
        $speech = "You have these many Leaves left";
        $response = new \stdClass();
        $response->fulfillmentText = $speech;
        $response->source = "webhook";
        echo json_encode($response);
    }
    elseif(flag.strcmp("Apply")){
        $uname = $json->queryResult->outputContexts[0]->parameters->person->name;
        /*$chkquery = "select * from Leave_Balance where username = $uname";
        $query = "INSERT INTO Bookings(ID, NoM, DateTime) VALUES ('',$number,'$date_time')";*/
        $speech = "You want to apply leave";
        mysqli_query($conn,$query);
        $response = new \stdClass();
        $response->fulfillmentText = $speech;
        $response->source = "webhook";
        echo json_encode($response);
    }
}
else
{
    echo "Method not allowed";
}

?>




