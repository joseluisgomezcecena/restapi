<?php

require_once "db.php";
require_once "../model/task.php";
require_once "../model/response.php";

try{

}
catch(PDOException $ex){
    error_log("Connection error - " . $ex, 0); //php error log
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage('Database connection error');
    $response->send();
    exit;
}

if(array_key_exists("taskid", $_GET)){
    $task_id = $_GET['taskid'];

    if($task_id == '' || !is_numeric($task_id)){
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage('Invalid task id');
        $response->send();
        exit;
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET'){

        try{
            
            $query = $readDB->prepare('SELECT task_id, task_title, task_description, DATE_FORMAT(task_deadline, "%d/%m/%Y %H:%i:%s"), task_completed FROM tbl_tasks WHERE task_id = :taskid');
            $query->bindParam(':taskid', $task_id, PDO::PARAM_INT);
            $query->execute();

            $row_count = $query->rowCount();

            if($row_count === 0){
                
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage('Task not found');
                $response->send();
                exit;    
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $task = new Task($row['task_id'], $row['task_title'], $row['task_description'], $row['task_deadline'], $row['task_completed']);

                $tasksArray[] = $task->returnTaskAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $row_count;
            $returnData['tasks'] = $tasksArray;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;



        }
        catch(TaskException $ex){

            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit;

        }
        catch(PDOException $ex){
            error_log("Query Error - " . $ex, 0); //php error log
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage('Failed to get Task');
            $response->send();
            exit;
        }


    }
    elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){

    }
    elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){

    }
    else{
        
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage('Invalid server request, not allowed');
        $response->send();
        exit;

    }
}