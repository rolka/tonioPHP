<?php

$myTemplate = file_get_contents('template.html');
$output = '';

// validate post
if(isset($_POST['number']) && isset($_POST['email']) && isValidNumber($_POST['number']) && (isValidEmail($_POST['email']) || $_POST['email'] == 'Enter your email(optional)'))
{
	$number = $_POST['number'];
	$email = validateEmail($_POST['email']);

	$conn = new mysqli('localhost', 'muhibind_2012', 'rzabul01', 'muhibind_01');
	
	$response = array();
	// get results 
	$myArray = $conn->query("SELECT number FROM emails");
		
	// fetch associative array 
	while($row = $myArray->fetch_assoc())
	{
		$response[] = $row[number];
	}

	if(in_array($number, $response))
	{
		$output = str_replace('[+result+]', 'This number has been already used', $myTemplate);
	}
	else
	{
		$result = $conn->query("INSERT INTO emails (email, number) values ('$email', '$number')");

		if($result)
		{
			$output = str_replace('[+result+]', 'Thanks for subscribing', $myTemplate);
		}
		else
		{
			$output = str_replace('[+result+]', 'Something went wrong with the connection, please go back and try again', $myTemplate);
		}
	}
}
else // display errors
{
	$output = str_replace('[+result+]', 'Something went wrong, please go back and try again', $myTemplate);
}

echo $output;

function isValidNumber($numberIn)
{
	/*$numberIn = trim($numberIn);

	if($numberIn == '')
	{
		return false;
	}
	else
	{
		if(preg_match('/(0|\+44)\d{9,11}\#?\d{3,4}?/', $numberIn))
		{
			return true;
		}
		else
		{
			return false;
		}
	}*/
	return true;
}

function validateEmail($emailIn)
{
	if($emailIn == 'Enter your email(optional)')
	{
		return  'email not provided';
	}
	else
	{
		return $emailIn;
	}
}

function isValidEmail($emailIn)
{
	$isValid = true;
	$atIndex = strrpos($emailIn, "@");
	
	if (is_bool($atIndex) && !$atIndex)
	{
		$isValid = false;
	}
	else
	{
		$domain = substr($emailIn, $atIndex+1);
		$local = substr($emailIn, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		
		if ($localLen < 1 || $localLen > 64)
		{
			// local part length exceeded
			$isValid = false;
		}
		else if ($domainLen < 1 || $domainLen > 255)
		{
			// domain part length exceeded
			$isValid = false;
		}
		else if ($local[0] == '.' || $local[$localLen-1] == '.')
		{
			// local part starts or ends with '.'
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $local))
		{
			// local part has two consecutive dots
			$isValid = false;
		}
		else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		{
			// character not valid in domain part
			$isValid = false;
		}
		else if(preg_match('/\\.\\./', $domain))
		{
			// domain part has two consecutive dots
			$isValid = false;
		}
		else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
		{
			// character not valid in local part unless 
			// local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local)))
			{
				$isValid = false;
			}
		}
		
		if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
		{
			// domain not found in DNS
			$isValid = false;
		}
	}

	return $isValid;
}


?>