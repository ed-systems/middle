<?php
function python3_exec($lines){
  // combine input lines into newline seperated string
  $lines_arg = join("\n", $lines);
  $exec_str = sprintf("python -c \"%s\"", $lines_arg);
  exec($exec_str, $out, $ret_code);
  // returns array (output, ret code). php system() returns last line only
  return array($out, $ret_code);
}

// see https://github.com/ed-systems/middle/issues/6
function grade_question($input){
  $copy = $input; 
  # new - get solution from url arguments rather than json
  $question_def = urldecode($copy['solution']);
  // parse for function name and number of args - remember, this is for extracing input elements rather than validating. colon is NOT optional (here it is, just to see if they entered it)
  $func_name_regex = '/def (?<func_name>\w+)\((?<args>[\w, ]+)\)(?<colon>:?)\s(?<def>\X+)/';
  preg_match($func_name_regex, $question_def, $matches);
  $func_name = $matches['func_name'];
  #print_r($matches);
  $num_args = sizeof(explode(",", $matches['args']));
  // GRADING:
  $grade = 0;
  // ...FUNCTION NAME STIPULATION
  if ($copy['function_name'] == $func_name){
    $copy['function_name_result'] = 'true';
    $copy['function_name_result_points'] = $copy['function_name_points'];
  }
  else {
    $copy['function_name_result'] = 'false';
    $copy['function_name_result_points'] = 0;
  }
  $grade += $copy['function_name_result_points'];
  // ...COLON STIPULATION
  if ($matches['colon'] == ':'){
    $copy['colon_result'] = 'true';
    $copy['colon_result_points'] = $copy['colon_points'];
  }
  else {
    $copy['colon_result'] = 'false';
    $copy['colon_result_points'] = 0;
  }
  $grade += $copy['colon_result_points'];
  // ...CONSTRAINT STIPULATION
  $constraint_regex = sprintf('/%s/', $copy['constraint']);
  if (preg_match($constraint_regex, $matches['def'])){
    $copy['constraint_result'] = 'true';
    $copy['constraint_result_points'] = $copy['constraint_points'];
  }
  else {
    $copy['constraint_result'] = 'false';
    $copy['constraint_result_points'] = 0;
  }
  $grade += $copy['constraint_result_points'];
  // ...TEST CASES
  $num_testcases = 6;
  // user may not have included semicolon, so reconstruct valid python exec str
  $def_str = sprintf("def %s(%s):\n%s", $func_name, $matches['args'], $matches['def']);
  // calculate points left, out of 100, after func_name/colon/constraint...
  $remaining_points = 100 - ($copy['colon_points'] + $copy['function_name_points'] + $copy['constraint_points']);
  // find the number of non_null test cases, calculate question worth
  $num_valid_questions = 0;
  for ($n = 1; $n <= $num_testcases; $n++){
    $in_idx = sprintf('input%d', $n);
    if (!empty($copy[$in_idx])){
      $num_valid_questions += 1;
    }
  }
  $question_points = $remaining_points/$num_valid_questions;
  // ... save to json
  $copy['output_points'] = $question_points;
  for ($n = 1; $n <= $num_testcases; $n++){
    $in_idx = sprintf('input%d', $n);
    $out_idx = sprintf('output%d', $n);
    $res_idx = sprintf('result%d', $n);
    $res_points_idx = sprintf('result%d_points', $n);
    $question_args = $copy[$in_idx];
    // don't run null testcases
    if ($copy[$in_idx] != null){
      // format string for call, e.g "print(add(%d, %d))"
      $func_call_str = sprintf('print(%s(%s))', $func_name, $question_args);
      // exec and get result, add to json copy
      $res = python3_exec(array($def_str, $func_call_str));
      $copy[$res_idx] = $res[0][0];
      // compare result to output and add points if correct
      if ($copy[$out_idx] == $copy[$res_idx]){
        $copy[$res_points_idx] = $question_points;
        $grade += $question_points;
      }
      else {
        $copy[$res_points_idx] = 0;
      }
    }
    else {
      $copy[$res_idx] = null;
      $copy[$res_points_idx] = null;
    }
  }
  // final grade
  $copy['autoGrade'] = $grade;
  return json_encode($copy);
}

#/* NORMAL OPERATION
$backend_input = file_get_contents('php://input');
$backend_data = json_decode($backend_input, true);
echo grade_question($backend_data);
#*/

// TESTS
/* grade question test
$test_solution = "def%20operation%28op%2C%20a%2C%20b%29%3A%0A%20%20%20%20if%20op%20%3D%3D%20%27%2B%27%3A%0A%20%20%20%20%20%20%20%20return%20a%20%2B%20b%0A%20%20%20%20elif%20op%20%3D%3D%20%27-%27%3A%0A%20%20%20%20%20%20%20%20return%20a%20-%20b%0A%20%20%20%20elif%20op%20%3D%3D%20%27%2A%27%3A%0A%20%20%20%20%20%20%20%20return%20a%20%2A%20b%0A%20%20%20%20elif%20op%20%3D%3D%20%27%2F%27%3A%0A%20%20%20%20%20%20%20%20return%20a%20%2F%20b%0A%20%20%20%20else%3A%0A%20%20%20%20%20%20%20%20return%20-1";
$test_json = <<<JSON
{
  "questionID": "32",
  "points": "100", "solution":"def%20operation%28op%2C%20a%2C%20b%29%3A%0A%20%20%20%20if%20op%20%3D%3D%20%27%2B%27%3A%0A%20%20%20%20%20%20%20%20return%20a%20%2B%20b%0A%20%20%20%20elif%20op%20%3D%3D%20%27-%27%3A%0A%20%20%20%20%20%20%20%20return%20a%20-%20b%0A%20%20%20%20elif%20op%20%3D%3D%20%27%2A%27%3A%0A%20%20%20%20%20%20%20%20return%20a%20%2A%20b%0A%20%20%20%20elif%20op%20%3D%3D%20%27%2F%27%3A%0A%20%20%20%20%20%20%20%20return%20a%20%2F%20b%0A%20%20%20%20else%3A%0A%20%20%20%20%20%20%20%20return%20-1",
  "function_name": "operation",
  "function_name_points": 10,
  "constraint": "elif",
  "constraint_points": 20,
  "colon_points": 10,
  "input1": "'+', 1, 2",
  "input2": "'-', 3, 4",
  "input3": "'*', 7, 8",
  "input4": "'/', -1, 1",
  "input5": "'^', 2, 2",
  "input6": null,
  "output1": "3",
  "output2": "-1",
  "output3": "56",
  "output4": "-1.0",
  "output5": "-1",
  "output6": null,
  "output1_points": 10,
  "output2_points": 10,
  "output3_points": 10,
  "output4_points": 10,
  "output5_points": 10,
  "output6_points": null
}
JSON;
$test = json_decode($test_json, true);
$res_json = grade_question($test);
#echo $res_json;
print_r(json_decode($res_json, true));
#*/
?>