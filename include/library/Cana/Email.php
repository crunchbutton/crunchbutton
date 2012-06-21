<?php

/**
 * Pear email sending wrapper
 * 
 * @author		Devin Smith <devin@cana.la>
 * @date		2006.02.01
 * 
 */


require_once Cana::config()->dirs->library.'PEAR/Mail.php';
require_once Cana::config()->dirs->library.'PEAR/Mail/mime.php';
require_once Cana::config()->dirs->library.'PEAR/Mail/sendmail.php';
require_once Cana::config()->dirs->library.'PEAR/Mail/RFC822.php';
require_once Cana::config()->dirs->library.'PEAR/Mail/mimePart.php';

class Cana_Email extends Cana_Model {

	const HEAD_CHARSET		= 'ISO-8859-1';
	const HTML_CHARSET		= 'ISO-8859-1';
	const TEXT_CHARSET		= 'ISO-8859-1';

	protected $mHeaders			= [];
	protected $mText			= '';		
	protected $mHtml			= '';
	private $_view;

	/*
	function getFilesArrayFromHTML(&$msg_html_body, $file_ext) {
		$files_array = array();
		$msg_html_body_copy = $msg_html_body;
		while($iFoundIndex = strpos($msg_html_body_copy,$file_ext)){
			// scan backwards to find the leading quote
			$iLeadingQuoteIndex = $iFoundIndex;
			while(substr($msg_html_body_copy, $iLeadingQuoteIndex, 1) != '"'){
				$iLeadingQuoteIndex--;
				if($iLeadingQuoteIndex == -1) break;
			}
			// reduce to just the ".gif" image name
			$image_name = substr($msg_html_body_copy, $iLeadingQuoteIndex+1, $iFoundIndex+strlen($file_ext)-$iLeadingQuoteIndex-1);

			if(!array_search ( $image_name, $files_array, true ))
				array_push($files_array, $image_name);
	
			// chop off this HTML,  to find next .gif
			$msg_html_body_copy = substr($msg_html_body_copy, $iFoundIndex+strlen($file_ext));
		}

		return $files_array;
	}
	*/
	
	public function __construct($params) {
		$this->params				= $params;
		$this->mHtml				= $params['messageHtml'];
		if (isset($params['messageTxt']) && trim($params['messageTxt'])) {
			$this->mTxt					= $params['messageTxt'];
		} else {
			$this->mTxt = strip_tags($this->mHtml);
		}

		$this->mHeaders['Subject']	= $params['subject'];
		if (is_array($params['to'])) {
			$this->mHeaders['To'] = '';
			foreach ($params['to'] as $key => $email) {
				$this->mHeaders['To'] .= ($key+1) < count($params['to']) ? ', ' : '';
			}
		} else {
			$this->mHeaders['To']			= $params['to'];
		}

		$this->mHeaders['From']				= $params['from'];
		if (isset($params['reply'])) {
			$this->mHeaders['Reply-To'] 	= $params['reply'];
		}
	}


	public function send() {
		
		// going to construct a Mime email
		$mime = new Mail_mime("\n");
		
		if (isset($this->mTxt) && $this->mTxt) {
			$mime->setTXTBody(wordwrap($this->mText));
		}
		$mime->setHTMLBody($this->mHtml);
		
		/*
		$fileTypes = array(
			'gif' => array(
				'extension'		=> 'gif',
				'mime'			=> 'image/gif'
			)
		);
		
		foreach ($fileTypes as $fileType) {
			$files = $this->getFilesArrayFromHTML($this->mHtml, '.'.$fileType['extension']);
			$fileNameCache = array();
			
			foreach($files as $fileName){
				if(!in_array($fileName, $fileNameCache)){
					$mime->addHTMLImage($this->imageDir.$fileName, $fileType['mime'],$fileName);
					$fileNameCache[] = $fileName;
				}
			}	

			$fileNameCache = array();
		}
		
		$max_attachment_size = 3000000;
		$attachment_size_sum = 0;
		*/
		// get the content 
		$content = $mime->get([
			'html_charset' => self::HTML_CHARSET,
			'text_charset' => self::TEXT_CHARSET,
			'head_charset' => self::HEAD_CHARSET
		]);

		// Strip the headers of CR and LF characters 
		$this->mHeaders=str_replace(["\r","\n"],'',$this->mHeaders);

		// get the headers (must happen after get the content)
		$hdrs = $mime->headers($this->mHeaders);
		
		// send the email
		try {

			$this->params['sendmail_path'] = '/usr/sbin/sendmail';	
			$mail = Mail::factory('sendmail',$this->params);

			if (PEAR::isError($mail)) {
				print('Failed to initialize PEAR::Mail: ' . $mail->toString());
				$response->setFault(MPSN_FAULT_GEN_ERROR);
			} else {
				$result = $mail->send($this->mHeaders['To'], $hdrs, $content);
				if (PEAR::isError($result)) {
					print_r($result);
					return false;
				} else {
					return true;
				}
			}

		} catch (Exception $e) {
			print_r($e);
			return false;
		}

	}
	
	public function view($view = null) {
		if (is_null($view)) {
			return $this->_view;
		} else {
			$this->_view = $view;
		}
		
	}
}

