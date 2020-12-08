const sleep = ms => new Promise(res => setTimeout(res, ms));

(async () => {
	let challs = {};
	const challElems = document.getElementsByClassName('challenge-card');
	for (const challElem of challElems) {
		let chall = {};
		chall.name = challElem.getElementsByClassName('challenge-name')[0].innerText;
		console.log(`Processing ${chall.name}`);
		challElem.click();
		await sleep(700);
		chall.points = parseInt(document.getElementsByClassName('pill-points')[0].innerText);
		chall.solves = parseInt(document.getElementsByClassName('solves')[0].innerText.split(' ')[0]);
		chall.desc = document.getElementsByClassName('challenge-information')[0].innerText;
		const challCat = document.getElementsByClassName('pill-category')[0].innerText;
		if (challCat in challs) challs[challCat].push(chall);
		else challs[challCat] = [chall];
		document.getElementsByClassName('modal-close')[0].click();
	}
	console.log(challs)
})();