<?php

/**
 * This is a very simple script to get all H5P multiple choice answers of learners. It could be extended 
 * by other H5P modules. You can use it as a base for the automatic correction of your online exam.  
 * Be aware that you secure this access that no third party gets access to this data.
 *
 * @copyright  2021 Sylvio Rüdian <ruediasy@informatik.hu-berlin.de>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
// typical Moodle header
include('lib/header.php');

// modify this course ID according to your course
$course_id = 6;

// give specific user ID or null to get data of all users
$user_id = null;

//////////////

$modinfo = get_fast_modinfo($course_id);
$sections = $modinfo->sections;

foreach($sections as $s){
	
	for ($i = 0; $i < count($s);$i++){
		$content_id = $s[$i];

		// get all H5P Question Sets of the Section and store correct answers of MC Questions
		$correct = $DB->get_record('hvp', array('id'=>$content_id));
		$questions = json_decode($correct->json_content)->questions;
		$all_correct_answers = [];
		foreach($questions as $q){
			$answers = $q->params->answers;
			$corr_arr = [];
			
			foreach($answers as $id=>$a){
				if ($a->correct) array_push($corr_arr,$id);
			}
			// uses the title of H5P element as identifier
			$all_correct_answers[trim(strip_tags($q->params->question))]=$corr_arr;
			
		}
		
		// get all answers of students
		if ($user_id == null) $results = $DB->get_records('hvp_xapi_results', array('content_id'=>$content_id));
		else $results = $DB->get_records('hvp_xapi_results', array('content_id'=>$content_id, 'user_id'=>$user_id));
		if (count($results)>0){
			echo '<h2>'.$content_id.'</h2>';
			echo '<table><tr><th>User-ID</th><th>Response</th><th>Answer</th><th>Correct?</th></tr>';
			foreach($results as $result){
				if ($result->response != ''){
					echo '<tr>'; 
						echo '<td>',$result->user_id,'</td>';
						echo '<td>',str_replace('[,]',',<br>',$result->response),'</td>';
						echo '<td>'; 
						$desc = trim($result->description);
						echo trim($desc).': ';
						
						$iscorrect = 0;
						$d = json_decode($result->additionals); 
						foreach($d as $k => $v) {
							if ($k == 'choices'){
								foreach($v as $w) {
									
									$response = explode('[,]',$result->response);
									foreach ($response as $resp){
										if ($w->id == $resp){ 
											echo '<br>Given answer: ';
											$desc2 = ($w->description);
											foreach($desc2 as $answer){
												
												if (in_array((int)$w->id, $all_correct_answers[$desc])){
													$iscorrect += 1;
												}else {
													$iscorrect -= 1;
													
												}
												echo $answer,' ';
											}
										}
									}
								}
							}
						}
						 echo '</td>';
						 echo '<td>'; 
						 // correct score
							if ($iscorrect<0) $iscorrect =0;
							$anz = count($all_correct_answers[$desc]);
							echo ($anz>0)? $iscorrect/$anz: '[must be programmed]';
						 echo '</td>';
					
					echo '</tr>';
				}
			}
			echo '</table>';
		}
	}
}
?>