<?php

namespace Ordrin;

class Validation {
	private $errors = array();
	private $mapFunctions=array(
			'password' => 'validateText',
			'firstName' => 'validateText',
			'lastName' => 'validateText',
			'text' => 'validateText',
			'email' => 'validateEmail',
			'restaurantId' => 'validateInteger',
			'itemId' => 'validateInteger',
			'quantity' => 'validateInteger',
			'option' => 'validateInteger',
			'money' => 'validateMoney',
			'trayItems' => 'validateTrayItems',
			'zipCode' => 'validateZipCode',
			'telephone' => 'validatePhone',
			'city' => 'validateCity',
			'state' => 'validateState',
			'url' => 'validateURL',
			'cvc' => 'validateCVC',
			'expirationDate' => 'validateExpirationDate',
			'cardNumber' => 'validateCardNumber'
	);
	public function __construct(&$errors = null){
		if($errors){
			$this->errors = $errors;
		}
	}
	public function getErrors(){
		return $this->errors;
	}
	public function validate($field,$value,$required = false){
		if(!$required && empty($value)){

			return true;
		}
		else if($this->mapFunctions[$field] && method_exists(__CLASS__,$this->mapFunctions[$field])){
				$function = $this->mapFunctions[$field];
				return $this->$function($value);
			}
	}
	public function validateInteger ($value){
		if(!preg_match('/^\d+$/', $value) || $value == ''){
			$this->errors[] = 'Validation - (invalid, must be integer) (' . $value . ')';
			return false;
		}
		return true;	
	}
	public function validateMoney ($value){
		if(!preg_match('/^\$?\d*(\.\d{2})?$/', $value) || $value == ''){
			$this->errors[] = 'Validation - Money (invalid) (' . $value . ')';
			return false;
		}
		return true;	
	}
	public function validateTrayItems ($value){
		if(!preg_match('/^\d+(,\d+)*(\+\d+(,\d+)*)*/', $value) || $value == ''){
			$this->errors[] = 'Tray - Validation - Items (invalid, items must be a non-empty array of TrayItems or string tray representation) (' . $value . ')';
			return false;
		}
		return true;	
	}
	public function validateText ($value){
		if(!preg_match('/^[\w]+$/i', $value)){
			$this->errors[] = 'Validation - Text (invalid) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateEmail ($value){
		if(!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value)){
			$this->errors[] = 'Validation - Email (invalid) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateZipCode ($value){
		if(!preg_match('/(^\d{5}$)|(^\d{5}\-\d{4}$)/', $value)){
			$this->errors[] = 'Validation - Zip Code (invalid) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validatePhone ($value){
		if(!preg_match('/^\(?\d{3}\)?[\- .]?\d{3}[\- .]?\d{4}$/', $value)){
			$this->errors[] = 'Validation - Phone Number (invalid, must be in format ###-###-####) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateCity ($value){
		if(!preg_match('/^[A-z.\- ]+$/', $value)){
			$this->errors[] = 'Validation - City (invalid, only letters/spaces allowed) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateState ($value){
		if(!preg_match('/^[a-z]{2}$/i', $value)){
			$this->errors[] = 'Validation - State (invalid, must be two-letter state abbreviation) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateURL ($value){
		if(!preg_match('/^(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?$/', $value)){
			$this->errors[] = 'Validation - URL (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateCVC ($value){
		if(!preg_match('/^\d{3,4}$/', $value)){
			$this->errors[] = 'Validation - CVC (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateExpirationDate ($value){
		if(!preg_match('/^\d{2}\/(\d{2}|\d{4})$/', $value)){
			$this->errors[] = 'Validation - Expiration Date (invalid, must be in format mm/yy or mm/yyyy) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateCardNumber($number) {
		//Perform a Luhn Test
		$odd = true;
		$sum = 0;
		foreach ( array_reverse(str_split($number)) as $num) {
			$sum += array_sum( str_split(($odd = !$odd) ? $num*2 : $num) );
			}
		if (($sum % 10 == 0) && ($sum != 0)){
			return true;
		}
		$this->errors[] = 'Validation - Card Number (' . $number . ')';
		return false;
	}
}