<?php

function python3_exec($lines){
  // combine input lines into newline seperated string
  $lines_arg = join("\n", $lines);
  $exec_str = sprintf("python3 -c '%s'", $lines_arg);
  $out = system($exec_str, $ret_code);
  // returns array (output, ret code). php system() returns last line only
  return array($out, $ret_code);
}

// see https://github.com/ed-systems/middle/issues/4
// just does the comparison, json processing will be outside - essentially, just returns modified json
function grade_question($input){
  $copy = $input;
  // get python func def
  $question_def = $copy['solution'];
  // parse for function name and number of args
  $func_name_regex = '/def (?<func_name>\w+)\((?<args>[\w,]+)\):/';
  preg_match($func_name_regex, $question_def, $matches);
  $func_name = $matches['func_name'];
  $num_args = sizeof(explode(",", $matches['args']));
  // populate results and grade for test cases. for now, always 2 test cases
  $num_testcases = 2;
  $testcase_points = $copy['points']/$num_testcases;
  $grade = 0;
  for ($n = 1; $n <= $num_testcases; $n++){
    $in_idx = sprintf('input%d', $n);
    $out_idx = sprintf('output%d', $n);
    $res_idx = sprintf('result%d', $n);
    $question_args = $copy[$in_idx];
    // format string for call, e.g "print(add(%d, %d))"
    $func_call_str = sprintf('print(%s(%s))', $func_name, $question_args);
    // exec and get result, add to json copy
    $res = python3_exec(array($question_def, $func_call_str));
    $copy[$res_idx] = $res[0];
    // compare result to output and add points if correct
    if ($copy[$out_idx] == $copy[$res_idx]){
      $grade += $testcase_points;
    }
  }
  // add final grade
  $copy['autoGrade'] = $grade;
  return json_encode($copy);
}

$backend_input = file_get_contents('php://input');
$backend_data = json_decode($backend_input, true);
echo grade_question($backend_data);

/* python3 exec test
echo print_r(python3_exec(array('print("hello")', 'print("world")')));
*/ 
/* grade question test
$test_json = '{
                "questionID": 1,
                "points": 20,
                "solution": "def add(a,b): return a + b",
                "input1": "2, 5",
                "output1": "7",
                "input2": "3, 7",
                "output2": "10"
}';
$test = json_decode($test_json, true);
echo grade_question($test);
*/

?>