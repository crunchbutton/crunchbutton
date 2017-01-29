#! /usr/bin/env node

const
	request = require('request'),
	aws = require('aws-sdk');

let urls = [
	'https://crunchbutton.com',
	'https://cockpit.la',
	'https://crunchbutton-stage.herokuapp.com/',
	'https://cockpit-stage.herokuapp.com/',
	'https://crunchbutton-dev.herokuapp.com',
	'https://cockpit-dev.herokuapp.com'
];

let promises = [];
let versions = [];

let getVersion = url => {
	console.log(url);
	return new Promise((resolve, reject) => {
		request(url, (error, response, body) => {

			if (error) {
				reject(error);
				return;
			}

			let v = body.match(/<link rel="stylesheet" type="text\/css" href="\/assets(\/cockpit)?\/css\/bundle.css\?v=([a-z0-9]+)">/);
			if (!v) {
				console.log(body);
				reject('no matches for ', url);
				return;
			}
			resolve(v[2]);
		});
	});
}

for (let u of urls) {
	promises.push(getVersion(u));
}

Promise.all(promises).then(versions => {
	console.log('Keeping versions:', versions);
});