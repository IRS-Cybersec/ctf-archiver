<?php
	set_time_limit(0);

	echo PHP_EOL, 'Welcome to the Tomahaque archiver', PHP_EOL;
	echo 'Tomahaque uses a Captcha for logins. You will need to use your session cookie instead', PHP_EOL;
	$LOGIN = [];
	$LOGIN['uuid'] = readline('Tomahaque Event UUID from URL (like "abc123de-abcd-1234-12yz-abc123456xyz"): ');
	$LOGIN['cookie'] = readline('Tomahaque MAGROCKSESSID Cookie: ');

	$context = stream_context_create([
		'http' => [
			'method' => 'GET',
			'header' => "Cookie: MAGROCKSESSID={$LOGIN['cookie']}\r\n"
		]
	]);
	echo 'Retrieving Challenges List', PHP_EOL;
	$challs = json_decode(file_get_contents("https://www.tomahawque.com/api/event/{$LOGIN['uuid']}/challenges", false, $context), true);
	if (is_null($challs)) exit("Failed to retrieve challenges. Make sure that\r\n  -> your credentials are correct\r\n  -> if you received a 404 error above, ensure you got the right event UUID");

	$output = [];

	foreach ($challs as $category) {
		$output[$category['label']] = [];
		foreach ($category['challenges'] as $chall) {
			echo "Processing: {$chall['title']}", PHP_EOL;
			$chall_details = json_decode(file_get_contents("https://www.tomahawque.com/api/event/{$LOGIN['uuid']}/challenge/{$chall['id']}", false, $context), true);
			$output[$category['label']][] = [
				'title' => $chall['title'],
				'points' => $chall['points'],
				'difficulty' => $chall['difficulty'],
				'details' => $chall_details['details']
			];
			$folder_created = false;
			if (preg_match_all("/\]\((http.*?)\)/", $chall_details['details'], $files)) {
				if (!$folder_created) mkdir($chall['title']);
				$folder_created = true;
				foreach ($files[1] as $file) {
					$filename = basename($file);
					echo "  -> Downloading: {$filename}", PHP_EOL;
					file_put_contents("{$chall['title']}/{$filename}", fopen($file, 'r', false, $context));
				}
			}
			if (array_key_exists('files', $chall_details)) {
				if (!$folder_created) mkdir($chall['title']);
				$folder_created = true;
				foreach ($chall_details['files'] as $file) {
					echo "  -> Downloading: {$file['fileName']}", PHP_EOL;
					file_put_contents("{$chall['title']}/{$file['fileName']}", fopen($file['link'], 'r', false, $context));
				}
			}
		}
	}
	file_put_contents('challenges.json', json_encode($output));
	echo PHP_EOL, "The CTF has been archived successfully", PHP_EOL;
?>