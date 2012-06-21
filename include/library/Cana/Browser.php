<?php

/**
 * A simple browser detection library
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2010.06.08
 *
 */


class Cana_Browser extends Cana_Model {

	var $browser = 'Unknown';
	var $version = 'Unknown';
	var $platform = 'Unknown';
	var $userAgent = 'Unknown';

	function __construct() {

        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';

        if ($this->userAgent != 'Unknown') {

			// find operating system
	        if (preg_match('/win/i', $this->userAgent)) {
				$this->platform = 'Windows';
			} elseif (preg_match('/mac/i', $this->userAgent)) {
				$this->platform = 'Mac';
			} elseif (preg_match('/linux/i', $this->userAgent)) {
				$this->platform = 'Linux';
			} elseif (preg_match('/OS\/2/i', $this->userAgent)) {
				$this->platform = 'OS/2';
			} elseif (preg_match('/BeOS/i', $this->userAgent)) {
				$this->platform = 'BeOS';
			}
	
			// test for Opera        
			if (preg_match('/opera/i',$this->userAgent)) {
				$val = stristr($this->userAgent, 'opera');
				if (preg_match('/\//', $val)) {
					$val = explode('/',$val);
					$this->browser = $val[0];
					$val = explode(' ',$val[1]);
					$this->version = $val[0];
				} else {
					$val = explode(' ',stristr($val,'opera'));
					$this->browser = $val[0];
					$this->version = $val[1];
	            }

			// test for WebTV
			} elseif (preg_match('/webtv/i',$this->userAgent)) {
				$val = explode('/',stristr($this->userAgent,'webtv'));
				$this->browser = $val[0];
				$this->version = $val[1];
	        
			// test for MS Internet Explorer version 1
			} elseif (preg_match('/microsoft internet explorer/i', $this->userAgent)) {
				$this->browser = 'MSIE';
				$this->version = '1.0';
				$var = stristr($this->userAgent, '/');
				if (preg_match('/308|425|426|474|0b1/i', $var)) {
					$this->version = '1.5';
				}
	
			// test for NetPositive
			} elseif (preg_match('/NetPositive/i', $this->userAgent)) {
	            $val = explode('/',stristr($this->userAgent,'NetPositive'));
	            $this->platform = 'BeOS';
	            $this->browser = $val[0];
	            $this->version = $val[1];
	
	        // test for MS Internet Explorer
	        } elseif (preg_match('/msie/i',$this->userAgent) && !preg_match('/opera/i',$this->userAgent)) {
	            $val = explode(' ',stristr($this->userAgent,'msie'));
	            $this->browser = $val[0];
	            $this->version = $val[1];
	        
	        // test for MS Pocket Internet Explorer
	        } elseif (preg_match('/mspie/i',$this->userAgent) || preg_match('/pocket/i', $this->userAgent)) {
	            $val = explode(' ',stristr($this->userAgent,'mspie'));
	            $this->browser = 'MSPIE';
	            $this->platform = 'WindowsCE';
	            if (preg_match('/mspie/i', $this->userAgent))
	                $this->version = $val[1];
	            else {
	                $val = explode('/',$this->userAgent);
	                $this->version = $val[1];
	            }
	            
	        // test for Galeon
	        } elseif (preg_match('/galeon/i',$this->userAgent)) {
	            $val = explode(' ',stristr($this->userAgent,'galeon'));
	            $val = explode('/',$val[0]);
	            $this->browser = $val[0];
	            $this->version = $val[1];
	            
	        // test for Konqueror
	        } elseif (preg_match('/Konqueror/i',$this->userAgent)) {
	            $val = explode(' ',stristr($this->userAgent,'Konqueror'));
	            $val = explode('/',$val[0]);
	            $this->browser = $val[0];
	            $this->version = $val[1];
	            
	        // test for iCab
	        } elseif (preg_match('/icab/i',$this->userAgent)) {
	            $val = explode(' ',stristr($this->userAgent,'icab'));
	            $this->browser = $val[0];
	            $this->version = $val[1];
	
	        // test for OmniWeb
	        } elseif (preg_match('/omniweb/i',$this->userAgent)) {
	            $val = explode('/',stristr($this->userAgent,'omniweb'));
	            $this->browser = $val[0];
	            $this->version = $val[1];
	
	        // test for Phoenix
	        } elseif (preg_match('/Phoenix/i', $this->userAgent)) {
	            $this->browser = 'Phoenix';
	            $val = explode('/', stristr($this->userAgent,'Phoenix/'));
	            $this->version = $val[1];
	        
	        // test for Firebird
	        } elseif (preg_match('/firebird/i', $this->userAgent)) {
	            $this->browser = 'Firebird';
	            $val = stristr($this->userAgent, 'Firebird');
	            $val = explode('/',$val);
	            $this->version = $val[1];
	            
	        // test for Firefox
	        } elseif (preg_match('/Firefox/i', $this->userAgent)) {
	            $this->browser = 'Firefox';
	            $val = stristr($this->userAgent, 'Firefox');
	            $val = explode('/',$val);
	            $this->version = $val[1];
	            
			// test for Mozilla Alpha/Beta Versions
	        } elseif (preg_match('/mozilla/i',$this->userAgent) && 
	            preg_match('/rv:[0-9].[0-9][a-b]/i',$this->userAgent) && !preg_match('/netscape/i',$this->userAgent)) {
	            $this->browser = 'Mozilla';
	            $val = explode(' ',stristr($this->userAgent,'rv:'));
	            preg_match('/rv:[0-9].[0-9][a-b]/i',$this->userAgent,$val);
	            $this->version = str_replace('rv:','',$val[0]);
	            
	        // test for Mozilla Stable Versions
	        } elseif (preg_match('/mozilla/i',$this->userAgent) &&
	            preg_match('/rv:[0-9]\.[0-9]/i',$this->userAgent) && !preg_match('/netscape/i',$this->userAgent)) {
	            $this->browser = 'Mozilla';
	            $val = explode(' ',stristr($this->userAgent,'rv:'));
	            preg_match('/rv:[0-9]\.[0-9]\.[0-9]/i',$this->userAgent,$val);
	            $this->version = str_replace('rv:','',$val[0]);
	        
	        // test for Lynx & Amaya
	        } elseif (preg_match('/libwww/i', $this->userAgent)) {
	            if (preg_match('/amaya/i', $this->userAgent)) {
	                $val = explode('/',stristr($this->userAgent,'amaya'));
	                $this->browser = 'Amaya';
	                $val = explode(' ', $val[1]);
	                $this->version = $val[0];
	            } else {
	                $val = explode('/',$this->userAgent);
	                $this->browser = 'Lynx';
	                $this->version = $val[1];
	            }
	            
			// test for chrome        
			} elseif (preg_match('/chrome/i',$this->userAgent)) {
	            $val = explode(' ',stristr($this->userAgent,'chrome'));
	            $val = explode('/',$val[0]);
	            $this->browser = $val[0];
	            $this->version = $val[1];

	        
	        // test for Safari
	        } elseif (preg_match('/safari/i', $this->userAgent)) {
	            $val = explode(' ',stristr($this->userAgent,'safari'));
	            $val = explode('/',$val[0]);
	            $this->browser = $val[0];
	            $this->version = $val[1];
	
	        // remaining two tests are for Netscape
	        } elseif (preg_match('/netscape/i',$this->userAgent)) {
	            $val = explode(' ',stristr($this->userAgent,'netscape'));
	            $val = explode('/',$val[0]);
	            $this->browser = $val[0];
	            $this->version = $val[1];
	        } elseif (preg_match('/mozilla/i',$this->userAgent) && !preg_match('/rv:[0-9]\.[0-9]\.[0-9]/i',$this->userAgent)) {
	            $val = explode(' ',stristr($this->userAgent,'mozilla'));
	            $val = explode('/',$val[0]);
	            $this->browser = 'Netscape';
	            $this->version = $val[1];
	        }

	        // clean up extraneous garbage that may be in the name
	        $this->browser = preg_replace('/[^a-z,A-Z]/', '', $this->browser);
	        // clean up extraneous garbage that may be in the version        
	        $this->version = preg_replace('/[^0-9,.,a-z,A-Z]/', '', $this->version);
	

		}
	}
}