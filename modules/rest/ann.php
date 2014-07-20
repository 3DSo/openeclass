<?php

function PostReadAnnouncements($aid){
    $uname = $_SESSION['uname'];
    $uid = -1;
    $database = Database::get();
    $database -> queryFunc("SELECT id FROM user WHERE username='$uname'", function($row) use(&$uid) { $uid = $row->id;});
    $here = false;
    $database -> queryFunc("SELECT user_id, ann_id FROM announcement_users", function($row) use(&$here, $uid, $aid)
				{
				   if($row->user_id == $uid && $row->ann_id == $aid)
					$here = true;
				});
    if(!$here){
        $query = "INSERT INTO announcement_users (user_id, ann_id) VALUES ($uid, $aid)";
        $database -> querySingle($query);
    }
    else
	echo json_encode(array("error" => "ALREADY_READ"));
}

function GetAnn($cid) {
	$flag = 0;	
	$i = 0;	
	$error=array( 'error'=>0);
	for($i=0;$i<strlen($cid);$i++)
		{if($cid[$i] <= '0' || $cid[$i] >='9')
			{$flag = 1;}}
	if($flag == 0)
	{
		$annou=array();
		$database = Database::get();
		$database->queryFunc("SELECT id,title,content,date FROM announcement WHERE course_id=$cid", function($row)use (&$annou) {$annou[] = $row;} );
		if($annou!=null)
		{ 
			echo json_encode($annou);
		}
		else 
		{
			$error=array( 'error'=>1);
			echo json_encode($error);
		}
	}
	else
	{
		$error=array( 'error'=>1);
		echo json_encode($error);
	}		
}

function GetAllAnn(){
	$un = $_SESSION['uname'];
	$uid=0;
	$database = Database::get();
	$database->queryFunc("SELECT id FROM user WHERE username='$un'",function($row)use (&$uid){ $uid = $row->id;});
	$u_annou=array();
	$temp_vis=array();
	$courset=array();
       	$database->queryFunc("SELECT IF(user_id=$uid AND announcement.id IN (SELECT ann_id from announcement_users ),'1','0')as visible from announcement LEFT JOIN announcement_users ON id=ann_id",function($row)use (&$temp_vis){ $temp_vis[] = $row; });
	$error = array( 'error'=>0);
	$database->queryFunc("SELECT id,title,content,date from announcement where course_id IN ( select course_id from course_user where user_id = $uid)", function($row)use (&$u_annou) {$u_annou[] = $row;});
	$database->queryFunc("SELECT course.title FROM course,announcement WHERE announcement.course_id = course.id",function($row)use (&$courset){ $courset[] = $row; });
		for($i=0;$i<count($u_annou);$i++)
		{
			$temp_vis[$i]=(array)$temp_vis[$i];
			$u_annou[$i]=(array)$u_annou[$i];
			$courset[$i]=(array)$courset[$i];
			$tvis=$temp_vis[$i]['visible'];
			$tid=$u_annou[$i]['id'];
			$tdate=$u_annou[$i]['date'];
			$tit=$u_annou[$i]['title'];
			$tcont=$u_annou[$i]['content'];
			$tcourtit=$courset[$i]['title'];
			$u_annou[$i]=array('id'=>$tid,'date'=>$tdate,'title'=>$tit,'content'=>$tcont,'courseTitle'=>$tcourtit,'visible'=>$tvis);
		}
	if ($u_annou!=null)
	{	
		echo json_encode($u_annou);		
		
	}
	else
	{
		$error = array( 'error'=>1);
		echo json_encode($error);
	}
}

?>
