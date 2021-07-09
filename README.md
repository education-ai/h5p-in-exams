# Using H5P in exams: Patch your Exam Server to avoid Cheating
If you want to use H5P in exams, there is the vulnerability for students to cheat for correct answers, which makes H5P not useful for exams.
In this experiment, we adjusted H5P for two setups, for Moodle and Wordpress. If you want to use H5P in your exam, please install a new Moodle (or Wordpress) instance, apply the patch in the code and then you can use H5P without the ability to cheat for correct answers within the code. The changes are minor, but even in 2021 there is still no solution. Thus we created a workaround that could be used by instructors to create exams with H5P.

Before giving insights into our solution, I would like to show you how students can cheat if you use H5P in online exams. All the information of the H5P content can be found in the sources, that can be assessed with every modern browser. There you can find the correctness of answers without any effort:

```
var H5PIntegration = ... [\"answers\":[{\"correct\":true,\"...
```

# H5P in Moodle 
Add the function *filtercontentsforexam()* and put it at the beginning of *addassetstopage()* in /mod/hvp/classes/view_assets.php

``` 
    /**
     * Removes correct answers and disables auto check to use H5P in exams
     *
     * @copyright  2021 Sylvio Rüdian <ruediasy@informatik.hu-berlin.de>
     * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function filtercontentsforexam(){
        $data = $this->settings['contents'];
        foreach($data as $key => $v){$mykey = $key;}

        $old = array('"correct":true','"showResultPage":true,"showSolutionButton":true,"showRetryButton":true','showScorePoints":true','"enableSolutionsButton":true','"enableCheckButton":true','"noResultMessage":"Finished"');
        $new = array('"correct":false','"showResultPage":false,"showSolutionButton":false,"showRetryButton":false','showScorePoints":false','"enableSolutionsButton":false','"enableCheckButton":false','"noResultMessage":"Your results were stored."');

        $this->settings['contents'][$mykey]['jsonContent'] = str_replace($old,$new, $data[$mykey]['jsonContent']);
	}
	
	public function addassetstopage() {
        global $PAGE;
        
        \mod_hvp\view_assets::filtercontentsforexam();
        ...
```
	
That's it! If you now want open a Multiple Choice Question, you wont get any feedback, but the answers are stored using the xapi (can be accessed in the table hvp_xapi_results). As the XAPI does not know what the correct answer is, it will always give 0 points. Given answers need to be extrated after the exam and then they can be graded.

# Teacher Tool to get Answers of Students
We created a small script that allows you to get all answers of your students of the exam. As the XAPI always stores that the answer was wrong (this could not be known by the XAPI as we set all answers to be wrong), but the given answer is stored, we could simply restore whether the students' answers were correct. We implemented this as a simple php script, that could be located in moodle/teacher/. Be aware that you secure this folder (e.g. with a secured folder using .htpasswd). Opening the script in the browser gives you an overview of all given answers. Currently the correctness can be automatically be derived with Multiple Choice Questions. 

You need to set the course ID. You can se a user ID to get results of a single user only.

We hope that the features help you to use H5P in exams without being able to cheat.

