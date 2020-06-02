# CTFd Scraper

[CTFd](https://ctfd.io/) is a popular platform used for Capture the Flag (CTF) competitons online. However, after the competitions end, most of the servers are taken down, and the challenges become unaccessible.

This scraper downloads all the challenges from a CTFd server for archival purposes.

## Usage

You need to install [PHP](https://www.php.net). The script was tested on PHP 7.4.6 on Windows, but should work on other platforms as well. Input the URL, username, and password, and let the magic happen.

```
> php scraper.php

Welcome to the CTFd archiver
CTFd URL (like "https://ctf.example.com"): THE URL OF YOUR CTF
CTFd Username: THE USERNAME USED TO LOG IN TO THE CTF
CTFd Password: THE PASSWORD USED TO LOG IN TO THE CTF
Getting CSRF Nonce
Logging in
Retrieving Challenges List
Processing: Challenge #1
Processing: Challenge #2
  -> Downloading: encrypted.txt for #2
  -> Downloading: script.py for #2

The CTF has been archived successfully
```

This script downloads everything into the current directory into folders, and a `challenge.json` file is created as well:

```json
[
	{
		"name": "Challenge #1",
		"description": "This challenge name is top tier",
		"tags": [
			"pwn"
		],
		"score": 250,
		"score_type": "standard",
	},
	{
		"name": "Challenge #2",
		"description": "Wow I am really creative and come up with good sample data",
		"tags": [
			"rev"
		],
		"score": 468,
		"score_type": "dynamic",
		"file": [
			"encrypted.txt",
			"script.py"
		]
	}
]
```