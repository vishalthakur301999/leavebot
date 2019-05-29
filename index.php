<?php
$servername = "sql12.freemysqlhosting.net";
$username = "sql12291966";
$password = "aJehBgyhb2";
$db = "sql12291966";
$conn = mysqli_connect($servername,$username,$password);
mysqli_select_db($conn,$db);
function dateDiffInDays($date1, $date2)
{
    // Calulating the difference in timestamps
    $diff = (strtotime($date2) - strtotime($date1));
    return abs(round($diff / 86400));
}
$method = $_SERVER['REQUEST_METHOD'];
// Process only when method is POST
if($method == 'POST'){
    $requestBody = file_get_contents('php://input');
    $json = json_decode($requestBody);
    $flag = "";
    $flag = $json->queryResult->outputContexts[0]->parameters->flag;
    if(strcmp("login",$flag)==0){
        $uname = $json->queryResult->outputContexts[0]->parameters->person->name;
        $query = "select * from Leave_Balance where username = '$uname'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $speech1 = "Hey ".$uname."! What do you want to do today?";
            $response = new \stdClass();
            $response->fulfillmentText = $speech1;
            $response->source = "webhook";
            echo json_encode($response);
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
        $chkquery = "select * from Leave_Balance where username = '$uname'";
        $result = mysqli_query($conn, $chkquery);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $speech1 = "CL: $row[CL_Balance], PL: $row[PL_Balance], SL: $row[SL_Balance]";
            $response = new \stdClass();
            $response->fulfillmentText = $speech1;
            $response->source = "webhook";
            echo json_encode($response);
        }
        else{
            $speech1 = "Invalid user";
            $response = new \stdClass();
            $response->fulfillmentText = $speech1;
            $response->source = "webhook";
            echo json_encode($response);
        }
    }
    else if(strcmp("confirm",$flag)==0){
        $uname = $json->queryResult->outputContexts[0]->parameters->person->name;
        $from = $json->queryResult->outputContexts[0]->parameters->from;
        $from = substr($from,0,10);
        $to = $json->queryResult->outputContexts[0]->parameters->to;
        $to = substr($to,0,10);
        $dateDiff = dateDiffInDays($from, $to);
        $speech1 = "Confirm Leave of $dateDiff day/s?";
        $response = new \stdClass();
        $response->fulfillmentText = $speech1;
        $response->source = "webhook";
        echo json_encode($response);

    }
    else if(strcmp("apply",$flag)==0){
        $uname = $json->queryResult->outputContexts[0]->parameters->person->name;
        $from = $json->queryResult->outputContexts[0]->parameters->from;
        $from = substr($from,0,10);
        $to = $json->queryResult->outputContexts[0]->parameters->to;
        $to = substr($to,0,10);
        $dateDiff = dateDiffInDays($from, $to);
        $dateDiff = $dateDiff+1;
        $type = $json->queryResult->outputContexts[0]->parameters->type;
        $chkquery = "select * from Leave_Balance where username = '$uname'";
        $result = mysqli_query($conn, $chkquery);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if(strcmp("PL",$type)==0){
                $balance = "$row[PL_Balance]";
                $balance = (int)$balance;
                if($dateDiff<=$balance){
                    $query = "INSERT INTO Applied_Leaves(Lid,Eid,From_Date,To_Date,Leave_Type,Reason) VALUES ('',$row[Eid],'$from','$to','$type','')";
                    $n = $balance-$dateDiff;
                    $query2 = "UPDATE Leave_Balance SET PL_Balance=$n WHERE Eid = '$row[Eid]'";
                    $res = mysqli_query($conn, $query);
                    if (!$res) {
                        die('Invalid query: ' . mysqli_error($conn));
                    }
                    $res2 = mysqli_query($conn, $query2);
                    if (!$res) {
                        die('Invalid query: ' . mysqli_error($conn));
                    }
                    $speech1 = "Applied, Sent for your Manager approval";
                    $response = new \stdClass();
                    $response->fulfillmentText = $speech1;
                    $response->source = "webhook";
                    echo json_encode($response);
                }
                else{
                    $speech1 = "Leave Application Unsuccessful, Insufficient Leave Balance!";
                    $response = new \stdClass();
                    $response->fulfillmentText = $speech1;
                    $response->source = "webhook";
                    echo json_encode($response);
                }
            }
             else if(strcmp("CL",$type)==0){
                $balance = "$row[CL_Balance]";
                $balance = (int)$balance;
                if($dateDiff<=$balance){
                    $query = "INSERT INTO Applied_Leaves(Lid,Eid,From_Date,To_Date,Leave_Type,Reason) VALUES ('',$row[Eid],'$from','$to','$type','')";
                    $n = $balance-$dateDiff;
                    echo $dateDiff;
                    $query2 = "UPDATE Leave_Balance SET CL_Balance=$n WHERE Eid = '$row[Eid]'";
                    $res = mysqli_query($conn, $query);
                    if (!$res) {
                        die('Invalid query: ' . mysqli_error($conn));
                    }
                    $res2 = mysqli_query($conn, $query2);
                    if (!$res) {
                        die('Invalid query: ' . mysqli_error($conn));
                    }
                    $speech1 = "Applied, Sent for your Manager approval";
                    $response = new \stdClass();
                    $response->fulfillmentText = $speech1;
                    $response->source = "webhook";
                    echo json_encode($response);
                }
                else{
                    $speech1 = "Leave Application Unsuccessful, Insufficient Leave Balance!";
                    $response = new \stdClass();
                    $response->fulfillmentText = $speech1;
                    $response->source = "webhook";
                    echo json_encode($response);
                }
            }
            else if(strcmp("SL",$type)==0){
                $balance = "$row[SL_Balance]";
                $balance = (int)$balance;
                if($dateDiff<=$balance){
                    $query = "INSERT INTO Applied_Leaves(Lid,Eid,From_Date,To_Date,Leave_Type,Reason) VALUES ('',$row[Eid],'$from','$to','$type','')";
                    $n = $balance-$dateDiff;
                    echo $dateDiff;
                    $query2 = "UPDATE Leave_Balance SET SL_Balance=$n WHERE Eid = '$row[Eid]'";
                    $res = mysqli_query($conn, $query);
                    if (!$res) {
                        die('Invalid query: ' . mysqli_error($conn));
                    }
                    $res2 = mysqli_query($conn, $query2);
                    if (!$res) {
                        die('Invalid query: ' . mysqli_error($conn));
                    }
                    $speech1 = "Applied, Sent for your Manager approval";
                    $response = new \stdClass();
                    $response->fulfillmentText = $speech1;
                    $response->source = "webhook";
                    echo json_encode($response);
                }
                else{
                    $speech1 = "Leave Application Unsuccessful, Insufficient Leave Balance!";
                    $response = new \stdClass();
                    $response->fulfillmentText = $speech1;
                    $response->source = "webhook";
                    echo json_encode($response);
                }
            }
        }
    }
}
else
{
    echo "Method not allowed";
}
?>
