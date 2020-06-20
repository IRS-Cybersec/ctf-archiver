# CTF Scraper

Online CTFs are common way to learn to learn cybersecurity skills. Unfortunately, after the events end, most of the servers are taken down, and the challenges become unaccessible.

This scraper downloads all the challenges and files from a CTF server for archival purposes.

## Supported Platforms
* CTFd
* Tomahawque

## Usage

You need to install [PHP](https://www.php.net). The script was tested on PHP 7.4.6 on Windows, but should work on other platforms as well. Input the metadata and your credentials, and let the magic happen.

```
> php (platform).php

Welcome to the (platform) archiver
CTFd URL (like "https://ctf.example.com"): https://ctf.example.com
CTFd Username: user
CTFd Password: passw0rd
Getting CSRF Nonce
Logging in
Retrieving Challenges List
Processing: Challenge #1
Processing: Challenge #2
  -> Downloading: encrypted.txt for #2
  -> Downloading: script.py for #2

The CTF has been archived successfully
```

This script downloads everything into the current directory into folders, and a `challenge.json` file with the challenges is created as well:

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