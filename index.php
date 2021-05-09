<!DOCTYPE html>
<html>
<head>
	<title>Matchstick</title>
	
</head>
<body>
	
	<table>
			<tr>
				<td width="50%">
					<fieldset>
						<legend>Query:</legend>
						<label>
							<form action="" method="post" autocomplete="off">
								<b>Question: </b>
								<input autofocus type="text" name="sayi" value="<?php if(isset($_POST["sayi"])){
									echo $_POST["sayi"];} ?>">
								<input type="submit" value="Submit">
								
							</form>
						</label>
					</fieldset>
				</td>
			</tr>
	</table>
	<script >
		function myFunction() {
		  var copyText = document.getElementById("myInput");

		  copyText.select(); 
		  copyText.setSelectionRange(0, 99999);

		  document.execCommand("copy");
		}
	</script>
</body>
</html>
<?php
if(isset($_POST["sayi"])){
	$input = $_POST["sayi"];
}else {
	$input = "4+0-2=2";
}
//$input="1 + 8 + 3 = 5";
DoStuff($input,2);
DoStuff($input,1);

function DoStuff($InputText = null, $MoveCount){
	$transformation=[
		"0" => ["8","9","6","0"],
		"1" => ["7","1","4"],
		"2" => ["3","2"],
		"3" => ["2","3","9","5","7","8"],
		"4" => ["4"],
		"5" => ["5","9","6","3"],
		"6" => ["6","8","5","0","9"],
		"7" => ["1","7"],
		"8" => ["0","6","9","8","2"],
		"9" => ["3","9","8","6","0","5"],
		"+" => ["+","-","="],
		"-" => ["+","-","="],
		"=" => ["+","-","="]
	];
	$diffarencesDic=[
		"0" => [2,1,1,0],
		"1" => [2,0,4],
		"2" => [1,0],
		"3" => [1,0,2,1,-4,4],
		"4" => [0],
		"5" => [0,2,2,1],
		"6" => [0,2,-2,1,1],
		"7" => [-2,0],
		"8" => [-2,-2,-2,0,-4],
		"9" => [-2,0,2,1,1,-2],
		"+" => [0,-2,1],
		"-" => [2,0,2],
		"=" => [1,-2,0]
	];
	//echo "INPUT: ".$InputText."<br>";
	//$inputArray = explode(' ', $InputText);
	$inputArray = str_split($InputText);
	if($MoveCount == 1) {
		$FirstPhase = checkRecursive($transformation, $inputArray, $diffarencesDic, "", [], 0, count($inputArray),true);
		echo count($FirstPhase)." result found"." (1 move). "."<br>";
		foreach ($FirstPhase as $value) {
			// echo  str_replace(" ", "",  $value)."<br>";
			echo '<b>Answers: </b>'.'<input type="text" disabled value="'.str_replace(" ", "",  $value).'" id="myInput">';
			echo '<button onclick="myFunction()">Copy</button>'."<br>";
		}
		
	}
	else if ($MoveCount == 2){
		$FirstPhase = checkRecursive($transformation, $inputArray, $diffarencesDic, "", [], 0, count($inputArray),false);
		$res = [];
		foreach ($FirstPhase as $value) {
			$valueArray = explode(' ', $value);
			$res = array_merge($res,
			 checkRecursive($transformation, $valueArray, $diffarencesDic, "", [], 0, count($valueArray),true)
			);
		}


		$res = array_merge($res,checkRecursive($transformation, $inputArray, $diffarencesDic, "", [], 0, count($inputArray),true,2));
		$res = array_unique($res);
		foreach ($res as $value) {
			echo  str_replace(" ", "",  $value)."<br>";
			
		}
		echo count($res)." result found"." (2 moves). "."<br>";
	}	
}

function checkRecursive($transformation, $inputArray, $diffarencesDic, $str, $array, $index=0, $count, $enableCheck, $moveLimit = 1){
	$resA = [];
	if($index == $count){
		if (validate($inputArray,
			$array,
			 $transformation, 
			 $diffarencesDic, $moveLimit))
		{
			if($enableCheck){
				if(check($str)){
				//	echo $str."<br>";
					$resA[] = $str;
				}
			}
			else{
				$res = join(" ",$array);
				$resA[] = $res;
			}
		}
	}
	else{
		foreach ($transformation[$inputArray[$index]] as $inStr) {
			$newArray = [];
			foreach ($array as $value) {
				$newArray[] = $value;
			}
			$newArray[] = $inStr;
			$resA = array_merge($resA,
			checkRecursive($transformation, $inputArray, $diffarencesDic, $str." ".$inStr, $newArray, $index+1, $count, $enableCheck, $moveLimit));
		}
	}
	return $resA;
}

function check($input)
{
	$input = str_replace(" ","", $input);
	$inputArray = explode('=', $input);

	if (count($inputArray)<2) {
		return false;
	}

	$values = [];
	foreach ($inputArray as $ia) {
		$k="\$s=".$ia.";";
		eval($k);
		array_push($values, $s);

	}
	$result = true;
	for ($i=1; $i < count($values); $i++) { 
		if($values[$i-1]!=$values[$i]){
			$result=false;
			break;
		}
	}
	return $result;
}
function validate($inputArrayValidate, $currentArray, $transformation, $diffarencesDic, $moveLimit){
	$diff=0;
	$diffCount=0;
	$diffStr = "";
	$isDoubleAction = false;
	for ($i=0; $i < 7 ; $i++) { 
		if ($inputArrayValidate[$i]!=$currentArray[$i]) {
			$inputval=$inputArrayValidate[$i];
			$inputValue = $transformation[$inputval];
			$key = array_search($currentArray[$i], $inputValue);
			$diff += $diffarencesDic[$inputval][$key];
			if($diffarencesDic[$inputval][$key] == 4 || $diffarencesDic[$inputval][$key] == -4) $isDoubleAction = true;
			$diffCount++;
		}
	}

	if($moveLimit == 1){

		$result = ($diffCount<=2) && ($diff == 0 || $diff == 1)  && !$isDoubleAction;
		return $result;
	}
	else if($moveLimit ==2){
		if ($diffCount> 4) return false;
		if ($diffCount==4) return (!$isDoubleAction && $diff == 0);
		if ($diffCount==3) return (($isDoubleAction && $diff == 0) || $diff == 1);
		if ($diffCount==2) return ($diff == 0 || (!$isDoubleAction && $diff == 2));
		if ($diffCount==1) return ($diff == 1);
		else return true;
	}
	return false;
}
