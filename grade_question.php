<?php
function python3_exec($lines){
  // combine input lines into newline seperated string
  $lines_arg = join("\n", $lines);
  $exec_str = sprintf("python -c '%s'", $lines_arg);
  exec($exec_str, $out, $ret_code);
  // returns array (output, ret code). php system() returns last line only
  return array($out, $ret_code);
}

// see https://github.com/ed-systems/middle/issues/6
function grade_question($input){
  $copy = $input;
  $question_def = $copy['solution'];
  // parse for function name and number of args - remember, this is for extracing input elements rather than validating. colon is NOT optional (here it is, just to see if they entered it)
  $func_name_regex = '/def (?<func_name>\w+)\((?<args>[\w,]+)\)(?<colon>:?) (?<def>.*)/';
  preg_match($func_name_regex, $question_def, $matches);
  $func_name = $matches['func_name'];
  $num_args = sizeof(explode(",", $matches['args']));
  // GRADING:
  $grade = 0;
  // ...FUNCTION NAME STIPULATION
  if ($copy['function_name'] == $func_name){
    $copy['function_name_result'] = true;
    $copy['function_name_result_points'] = $copy['function_name_points'];
  }
  else {
    $copy['function_name_result'] = false;
    $copy['function_name_result_points'] = 0;
  }
  $grade += $copy['function_name_result_points'];
  // ...COLON STIPULATION
  if ($matches['colon'] == ':'){
    $copy['colon_result'] = true;
    $copy['colon_result_points'] = $copy['colon_points'];
  }
  else {
    $copy['colon_result'] = false;
    $copy['colon_result_points'] = 0;
  }
  $grade += $copy['colon_result_points'];
  // ...CONSTRAINT STIPULATION
  $constraint_regex = sprintf('/%s/', $copy['constraint']);
  if (preg_match($constraint_regex, $matches['def'])){
    $copy['constraint_result'] = true;
    $copy['constraint_result_points'] = $copy['constraint_points'];
  }
  else {
    $copy['constraint_result'] = false;
    $copy['constraint_result_points'] = 0;
  }
  $grade += $copy['constraint_result_points'];
  // ...TEST CASES
  $num_testcases = 6;
  // user may not have included semicolon, so reconstruct valid python exec str
  $def_str = sprintf('def %s(%s): %s', $func_name, $matches['args'], $matches['def']);
  for ($n = 1; $n <= $num_testcases; $n++){
    $in_idx = sprintf('input%d', $n);
    $out_idx = sprintf('output%d', $n);
    $out_points_idx = sprintf('output%d_points', $n);
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
        $copy[$res_points_idx] = $copy[$out_points_idx];
        $grade += $copy[$out_points_idx];
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

/* NORMAL OPERATION
$backend_input = file_get_contents('php://input');
$backend_data = json_decode($backend_input, true);
echo grade_question($backend_data);
#*/

// TESTS
// python3 exec
//echo print_r(python3_exec(array('print("hello")', 'print("world")')));

#/* grade question test
$test_json = <<<JSON
{
  "questionID": 1,
  "points": 20,
  "solution": "def add(a,b): return a + b",
  "function_name": "add",
  "function_name_points": 5,
  "constraint": "return",
  "constraint_points": 5,
  "colon_points": 5,
  "input1": "2, 5",
  "output1": "7",
  "output1_points": 10,
  "input2": "3, 7",
  "output2": "10",
  "output2_points": 10,
  "input3": "2, 5",
  "output3": "7",
  "output3_points": 10,
  "input4": "3, 7",
  "output4": "10",
  "output4_points": 10,
  "input5": null,
  "output5": null,
  "output5_points": null,
  "input6": null,
  "output6": null,
  "output6_points": null
}
JSON;
$test = json_decode($test_json, true);
$res_json = grade_question($test);
#echo $res_json;
print_r(json_decode($res_json, true));
#*/
?>