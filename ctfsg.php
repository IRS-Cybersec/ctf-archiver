<?php
	set_time_limit(0);

	echo PHP_EOL, 'Welcome to the CTF.SG archiver', PHP_EOL;
	$LOGIN = [];
	$LOGIN['url'] = readline('CTF.SG Prefix (like "cyberthon21t"): ');
	$LOGIN['username'] = readline('CTF.SG Username: ');
	$LOGIN['password'] = readline('CTF.SG Password: ');
	$api_url = 'https://api.';
	$api_url .= $LOGIN['url'];
	if (!empty($LOGIN['url'])) $api_url .= '.';
	$api_url .= 'ctf.sg/graphql';

	print($api_url);

	$context = stream_context_create([
		'http' => [
			'method' => 'POST',
			'header' => "Content-type: application/json\r\n",
			'content' => json_encode([
				'operationName' => 'logIn',
				'variables' => [
					'email' => $LOGIN['username'],
					'password' => $LOGIN['password']
				],
				'query' => 'mutation logIn($email: String!, $password: String!) {authenticateUser(email: $email, password: $password)}'
			])
		]
	]);
	echo 'Logging in', PHP_EOL;
	$resp = json_decode(file_get_contents($api_url, false, $context), true);
	if (isset($resp['errors'])) exit("Failed to sign in: are your credentials correct?");
	$token = "Bearer {$resp['data']['authenticateUser']}";
	$username = json_decode(base64_decode(explode('.', $token)[1]), true)['username'];

	echo 'Getting Competitions', PHP_EOL;
	$context = stream_context_create([
		'http' => [
			'method' => 'POST',
			'header' => "Authorization: $token\r\nContent-Type: application/json\r\n",
			'content' => '{"operationName":null,"variables":{},"query":"{competitions {id name}}"}'
		]
	]);
	$resp = json_decode(file_get_contents($api_url, false, $context), true);
	if (!isset($resp['data']['competitions'])) exit("No competitions found");
	$output = [];
	for ($i = 0; $i < count($resp['data']['competitions']); $i++) {
		echo PHP_EOL, "Competition: {$resp['data']['competitions'][$i]['name']}", PHP_EOL;

		echo 'Retrieving Challenges List', PHP_EOL;
		$context = stream_context_create([
			'http' => [
				'method' => 'POST',
				'header' => "Authorization: $token\r\nContent-Type: application/json\r\n",
				'content' => '{"operationName":"competitionGameData","variables":{"id":"' . $resp['data']['competitions'][$i]['id'] . '"},"query":"query competitionGameData($id: ID!) {competition(id: $id) {challenges {id name}}}"}'
			]
		]);
		$challs = json_decode(file_get_contents($api_url, false, $context), true)['data']['competition']['challenges'];

		$chall_output = [];

		foreach ($challs as $metadata) {
			echo "Processing: {$metadata['name']}", PHP_EOL;
			$chall_info = json_decode(file_get_contents($api_url, false, $context), true);
			$context = stream_context_create([
				'http' => [
					'method' => 'POST',
					'header' => "Authorization: $token\r\nContent-Type: application/json\r\n",
					'content' => '{"operationName":"challenge","variables":{"id":"' . $metadata['id'] . '"},"query":"query challenge($id: ID!) {challenge(id: $id) {name description category score type numberOfSolvers flagPreview {expectedFormat} files {url name}}}"}'
				]
			]);
			$chall_info = json_decode(file_get_contents($api_url, false, $context), true)['data']['challenge'];
			if (!isset($chall_output[$chall_info['category']])) $chall_output[$chall_info['category']] = [];
			$chall = [
				'name' => $chall_info['name'],
				'description' => $chall_info['description'],
				'category' => $chall_info['category'],
				'score' => $chall_info['score'],
				'solves' => $chall_info['numberOfSolvers'],
				'flag_format' => $chall_info['flagPreview']['expectedFormat']
			];
			if (!empty($chall_info['files'])) {
				$folder_name = preg_replace('/[<>:"\/\\\|\?\*]/', '_', $chall_info['name']);
				mkdir($folder_name);
				foreach ($chall_info['files'] as $file) {
					echo "  -> Downloading: {$file['name']}", PHP_EOL;
					file_put_contents("{$folder_name}/{$file['name']}", fopen($file['url'], 'r', false));
					$chall['file'][] = $file['name'];
				}
			}
			$chall_output[$chall_info['category']][] = $chall;
		}

		$output[$resp['data']['competitions'][$i]['name']] = $chall_output;
	}

	file_put_contents('challenges.json', json_encode($output));
	echo PHP_EOL, "The CTF has been archived successfully", PHP_EOL;
?>
