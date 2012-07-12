<?php

class Crunchbutton_Phaxio {
	public function __construct($params = []) {
		
	}

}

curl https://api.phaxio.com/v1/send \
        -F 'to=4141234567' \
        -F 'filename=@/path/to/a/supported/file' \
        -F 'api_key=API_KEY' \
        -F 'api_secret=API_SECRET'