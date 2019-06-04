<?php
$servername = "sql12.freemysqlhosting.net";
$username = "sql12293814";
$password = "4pgZjwWysA";
$db = "sql12293814";
$conn = mysqli_connect($servername,$username,$password);
mysqli_select_db($conn,$db);

function dateDiffInDays($startDate,$endDate,$holidays = array("2019-01-26","2019-08-15","2019-10-02")){
    // do strtotime calculations just once
    $endDate = strtotime($endDate);
    $startDate = strtotime($startDate);


    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
    //We add one to inlude both dates in the interval.
    $days = ($endDate - $startDate) / 86400 + 1;

    $no_full_weeks = floor($days / 7);
    $no_remaining_days = fmod($days, 7);

    //It will return 1 if it's Monday,.. ,7 for Sunday
    $the_first_day_of_week = date("N", $startDate);
    $the_last_day_of_week = date("N", $endDate);

    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
    if ($the_first_day_of_week <= $the_last_day_of_week) {
        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
    }
    else {
        // (edit by Tokes to fix an edge case where the start day was a Sunday
        // and the end day was NOT a Saturday)

        // the day of the week for start is later than the day of the week for end
        if ($the_first_day_of_week == 7) {
            // if the start date is a Sunday, then we definitely subtract 1 day
            $no_remaining_days--;

            if ($the_last_day_of_week == 6) {
                // if the end date is a Saturday, then we subtract another day
                $no_remaining_days--;
            }
        }
        else {
            // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
            // so we skip an entire weekend and subtract 2 days
            $no_remaining_days -= 2;
        }
    }

    //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
    $workingDays = $no_full_weeks * 5;
    if ($no_remaining_days > 0 )
    {
        $workingDays += $no_remaining_days;
    }

    //We subtract the holidays
    foreach($holidays as $holiday){
        $time_stamp=strtotime($holiday);
        //If the holiday doesn't fall in weekend
        if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
            $workingDays--;
    }

    return $workingDays;
}

function getDatesFromRange($start, $end, $format = 'Y-m-d') {

    // Declare an empty array
    $array = array();

    // Variable that store the date interval
    // of period 1 day
    $interval = new DateInterval('P1D');

    $realEnd = new DateTime($end);
    $realEnd->add($interval);

    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

    // Use loop to store date into array
    foreach($period as $date) {
        $array[] = $date->format($format);
    }

    // Return the array elements
    return $array;
}

$method = $_SERVER['REQUEST_METHOD'];
if($method == 'POST') {
// Process only when method is POST
    $requestBody = file_get_contents('php://input');
    $json = json_decode($requestBody);
    $flag = "";
    $flag = $json->queryResult->outputContexts[0]->parameters->flag;
    if (strcmp("login", $flag) == 0) {
        $uname = $json->queryResult->outputContexts[0]->parameters->person->name;
        $query = "select * from leave_balance where username = '$uname'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $speech1 = "Hey " . $uname . "! What do you want to do today?";
            $response = new \stdClass();
            $response->fulfillmentText = $speech1;
            $response->source = "webhook";
            echo json_encode($response);
        } else {
            $speech1 = "Invalid user";
            $response = new \stdClass();
            $response->fulfillmentText = $speech1;
            $response->source = "webhook";
            echo json_encode($response);
        }
    } else if (strcmp("check", $flag) == 0) {
        $uname = $json->queryResult->outputContexts[1]->parameters->person->name;
        $chkquery = "select * from leave_balance where username = '$uname'";
        $result = mysqli_query($conn, $chkquery);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $speech1 = "CL: $row[CL_Balance], PL: $row[PL_Balance], SL: $row[SL_Balance]";
            $response = new \stdClass();
            $response->fulfillmentText = $speech1;
            $response->source = "webhook";
            echo json_encode($response);
        } else {
            $speech1 = "Invalid user";
            $response = new \stdClass();
            $response->fulfillmentText = $speech1;
            $response->source = "webhook";
            echo json_encode($response);
        }
    } else if (strcmp("confirm", $flag) == 0) {
        $uname = $json->queryResult->outputContexts[2]->parameters->person->name;
        $from = $json->queryResult->outputContexts[0]->parameters->from;
        $from = substr($from, 0, 10);
        $to = $json->queryResult->outputContexts[0]->parameters->to;
        $to = substr($to, 0, 10);
        $dateDiff = dateDiffInDays($from, $to);
            $i=1;
            $chkquery = "select * from leave_balance where username = '$uname'";
            $result = mysqli_query($conn, $chkquery);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $chkquery2 = "select * from applied_leaves where Eid = '$row[Eid]'";
                    $result2 = mysqli_query($conn, $chkquery2);
                    if (mysqli_num_rows($result2) > 0) {
                        while ($row2 = mysqli_fetch_assoc($result2)) {
                            $Date1 = getDatesFromRange("$row2[From_Date]", "$row2[To_Date]");
                            $Date2 = getDatesFromRange($from, $to);
                            $result3 = array_intersect($Date1, $Date2);
                            if (empty($result3)) {
                            } else {
                                $i=0;
                                $speech1 = "Duplicate Dates Found with previously applied Leave. Please try Again";
                                $response = new \stdClass();
                                $response->fulfillmentText = $speech1;
                                $response->source = "webhook";
                                echo json_encode($response);
                            }
                        }
                    }
                }
            }
            if($i==1){
                $speech1 = "Confirm Leave of $dateDiff day/s?";
                $response = new \stdClass();
                $response->fulfillmentText = $speech1;
                $response->source = "webhook";
                echo json_encode($response);
            }

    } else if (strcmp("apply", $flag) == 0) {
        $uname = $json->queryResult->outputContexts[0]->parameters->person->name;
        $from = $json->queryResult->outputContexts[0]->parameters->from;
        $remark = $json->queryResult->outputContexts[0]->parameters->any;
        $from = substr($from, 0, 10);
        $to = $json->queryResult->outputContexts[0]->parameters->to;
        $to = substr($to, 0, 10);
        $dateDiff = dateDiffInDays($from, $to);
        $type = $json->queryResult->outputContexts[0]->parameters->type;
        $chkquery = "select * from leave_balance where username = '$uname'";
        $result = mysqli_query($conn, $chkquery);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if (strcmp("PL", $type) == 0) {
                $balance = "$row[PL_Balance]";
                $balance = (int)$balance;
                if ($dateDiff <= $balance) {
                    $query = "INSERT INTO applied_leaves(Eid,From_Date,To_Date,Leave_Type,Reason) VALUES ($row[Eid],'$from','$to','$type','$remark')";
                    $n = $balance - $dateDiff;
                    $query2 = "UPDATE leave_balance SET CL_Balance=$n WHERE Eid = '$row[Eid]'";
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
                } else {
                    $speech1 = "Leave Application Unsuccessful, Insufficient Leave Balance!";
                    $response = new \stdClass();
                    $response->fulfillmentText = $speech1;
                    $response->source = "webhook";
                    echo json_encode($response);
                }
            } else if (strcmp("CL", $type) == 0) {
                $balance = "$row[CL_Balance]";
                $balance = (int)$balance;
                if ($dateDiff <= $balance) {
                    $query = "INSERT INTO applied_leaves(Eid,From_Date,To_Date,Leave_Type,Reason) VALUES ($row[Eid],'$from','$to','$type','$remark')";
                    $n = $balance - $dateDiff;
                    $query2 = "UPDATE leave_balance SET CL_Balance=$n  WHERE Eid = '$row[Eid]'";
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
                } else {
                    $speech1 = "Leave Application Unsuccessful, Insufficient Leave Balance!";
                    $response = new \stdClass();
                    $response->fulfillmentText = $speech1;
                    $response->source = "webhook";
                    echo json_encode($response);
                }
            } else if (strcmp("SL", $type) == 0) {
                $balance = "$row[SL_Balance]";
                $balance = (int)$balance;
                if ($dateDiff <= $balance) {
                    $query = "INSERT INTO applied_leaves(Eid,From_Date,To_Date,Leave_Type,Reason) VALUES ($row[Eid],'$from','$to','$type','$remark')";
                    $n = $balance - $dateDiff;
                    $query2 = "UPDATE leave_balance SET CL_Balance=$n WHERE Eid = '$row[Eid]'";
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
                } else {
                    $speech1 = "Leave Application Unsuccessful, Insufficient Leave Balance!";
                    $response = new \stdClass();
                    $response->fulfillmentText = $speech1;
                    $response->source = "webhook";
                    echo json_encode($response);
                }
            }
        }
    }
    else if(strcmp("graph", $flag) == 0){
        $chkquery1 = "select * from absentemployee where Time_period = 'today'";
        $result1 = mysqli_query($conn, $chkquery1);
        $row1=mysqli_fetch_assoc($result1);
        $chkquery2 = "select * from absentemployee  where Time_period = 'tommorow'";
        $result2 = mysqli_query($conn, $chkquery2);
        $row2=mysqli_fetch_assoc($result2);
        $chkquery3 = "select * from absentemployee  where Time_period = 'this_week'";
        $result3 = mysqli_query($conn, $chkquery3);
        $row3=mysqli_fetch_assoc($result3);
        $chkquery4 = "select * from absentemployee  where Time_period = 'this_month'";
        $result4 = mysqli_query($conn, $chkquery4);
        $row4=mysqli_fetch_assoc($result4);
        $speech1 = "$row1[absentees],$row2[absentees],$row3[absentees],$row4[absentees]";
        $response = new \stdClass();
        $response->fulfillmentText = $speech1;
        $response->source = "webhook";
        echo json_encode($response);
    }
    mysqli_close($conn);
}
else
{
    echo "Method not allowed";
}
?>
