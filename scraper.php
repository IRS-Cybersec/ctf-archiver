<?php
	set_time_limit(0);

	echo PHP_EOL, 'Welcome to the CTFd archiver', PHP_EOL;
	$LOGIN = [];
	$LOGIN['url'] = readline('CTFd URL (like "https://ctf.example.com"): ');
	$LOGIN['username'] = readline('CTFd Username: ');
	$LOGIN['password'] = readline('CTFd Password: ');

	echo 'Getting CSRF Nonce', PHP_EOL;
	preg_match('/\'csrfNonce\': "(.+?)"/', file_get_contents("{$LOGIN['url']}/login"), $nonces);
	foreach ($http_response_header as $header) {
		if (preg_match('/^Set-Cookie: session=(.+?);/', $header, $cookies)) {
			$cookie = $cookies[1];
			break;
		}
	}
	if (!$cookie) exit("\r\nFailed to connect: check the URL and your internet connection");

	$context = stream_context_create([
		'http' => [
			'method' => 'POST',
			'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
			"Cookie: session={$cookie}\r\n",
			'content' => http_build_query([
				'name' => $LOGIN['username'],
				'password' => $LOGIN['password'],
				'nonce' => $nonces[1]
			])
		]
	]);
	echo 'Logging in', PHP_EOL;
	file_get_contents("{$LOGIN['url']}/login", false, $context);
	$cookie = false;
	foreach ($http_response_header as $header) {
		if (preg_match('/^Set-Cookie: session=(.+?);/', $header, $cookies)) {
			$cookie = $cookies[1];
			break;
		}
	}
	if (!$cookie) exit("Failed to sign in: are your credentials correct?");

	$context = stream_context_create([
		'http' => [
			'method' => 'GET',
			'header' => "Cookie: session={$cookie}\r\n"
		]
	]);

	echo 'Retrieving Challenges List', PHP_EOL;
	$challs = json_decode(file_get_contents("{$LOGIN['url']}/api/v1/challenges", false, $context), true);
	if (!$challs) exit("Failed to retrieve challenges");

	$chall_output = [];
	if (!$challs['success']) exit("Failed to retrieve challenges");

	foreach ($challs['data'] as $metadata) {
		echo "Processing: {$metadata['name']}", PHP_EOL;
		$chall_info = json_decode(file_get_contents("https://ctf.hsctf.com/api/v1/challenges/{$metadata['id']}", false, $context), true);
		if (!$chall_info || !$chall_info['success']) {
			echo 'Failed to process, skipping'; 
			continue;
		}
		$chall = [
			'name' => $chall_info['data']['name'],
			'description' => $chall_info['data']['description'],
			'tags' => $chall_info['data']['tags'],
			'score' => $chall_info['data']['value'],
			'score_type' => $chall_info['data']['type']
		];
		if (!empty($chall_info['data']['files'])) {
			$folder_name = preg_replace('/[<>:"\/\\\|\?\*]/', '_', $chall_info['data']['name']);
			mkdir($folder_name);
			foreach ($chall_info['data']['files'] as $file) {
				preg_match("/\/files\/.+?\/(.+?)\?token/", $file, $filename);
				echo "  -> Downloading: {$filename[1]}\r\n";
				file_put_contents("{$folder_name}/{$filename[1]}", fopen("{$LOGIN['url']}{$file}", 'r', false, $context));
				$chall['file'][] = $filename[1];
			}
		}
		$chall_output[] = $chall;
	}

	file_put_contents('challenges.json', json_encode($chall_output));
	echo PHP_EOL, "The CTF has been archived successfully", PHP_EOL;
?>