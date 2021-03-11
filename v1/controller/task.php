<?php

require_once "db.php";
require_once "../model/task.php";
require_once "../model/response.php";
try{
    $writeDB = DB::connectWriteDB();
    $readDB  = DB::connectReadDB();
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
            
            $query = $readDB->prepare('SELECT task_id, task_title, task_description, DATE_FORMAT(task_deadline, "%d/%m/%Y %H:%i:%s") as task_deadline, task_complete FROM tbl_tasks WHERE task_id = :taskid');
            $query->bindParam(':taskid', $task_id, PDO::PARAM_INT);
            $query->execute();

            //echo $test  = 'SELECT task_id, task_title, task_description, DATE_FORMAT(task_deadline, "%d/%m/%Y %H:%i:%s"), task_complete FROM tbl_tasks WHERE task_id = :'.$task_id.'';

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
                $task = new Task($row['task_id'], $row['task_title'], $row['task_description'], $row['task_deadline'], $row['task_complete']);

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
            $response->addMessage('Failed to get Task - Query Error ');
            $response->send();
            exit;
        }


    }
    elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){

        try{

            $query = $writeDB->prepare('DELETE FROM tbl_tasks WHERE task_id = :taskid');
            $query->bindParam('taskid', $task_id, PDO::PARAM_INT);
            $query->execute();

            $row_count = $query->rowCount();

            if($row_count === 0)
            {    
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage('Task not found');
                $response->send();
                exit;
            }

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData('Task Deleted');
            $response->send();
            exit;

    
        }
        catch(PDOException $ex){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage('Failed to delete DB error');
            $response->send();
            exit;
        }

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