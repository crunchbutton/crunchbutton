<?php
/* WikiParser
 * Version 1.0
 * Copyright 2005, Steve Blinch
 * http://code.blitzaffe.com
 *
 * This class parses and returns the HTML representation of a document containing 
 * basic MediaWiki-style wiki markup.
 *
 *
 * USAGE
 *
 * Refer to class_WikiRetriever.php (which uses this script to parse fetched
 * wiki documents) for an example.
 *
 *
 * LICENSE
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 *  
 *
 */

class Cana_Wiki {
	
	function Cana_Wiki() {
		$this->reference_wiki = '';
		$this->image_uri = '';
		$this->ignore_images = true;
	}
	
	function handle_sections($matches) {
		$level = strlen($matches[1]);
		$content = $matches[2];
		
		$this->stop = true;
		// avoid accidental run-on emphasis
		return $this->emphasize_off() . "\n\n<h{$level}>{$content}</h{$level}>\n\n";
	}
	
	function handle_newline($matches) {
		if ($this->suppress_linebreaks) return $this->emphasize_off();
		
		$this->stop = true;
		// avoid accidental run-on emphasis
		return $this->emphasize_off() . "<br /><br />";
	}
	
	function handle_list($matches,$close=false) {

		$listtypes = [
			'*'=>'ul',
			'#'=>'ol',
		];
		
		$output = "";
		
		$newlevel = ($close) ? 0 : strlen($matches[1]);
		
		while ($this->list_level!=$newlevel) {
			$listchar = substr($matches[1],-1);
			$listtype = $listtypes[$listchar];
			
			//$output .= "[".$this->list_level."->".$newlevel."]";
			
			if ($this->list_level>$newlevel) {
				$listtype = '/'.array_pop($this->list_level_types);
				$this->list_level--;
			} else {
				$this->list_level++;
				array_push($this->list_level_types,$listtype);
			}
			$output .= "\n<{$listtype}>\n";
		}
		
		if ($close) return $output;
		
		$output .= "<li>".$matches[2]."</li>\n";
		
		return $output;
	}
	
	function handle_definitionlist($matches,$close=false) {
		
		if ($close) {
			$this->deflist = false;
			return "</dl>\n";
		}
		
		
		$output = "";
		if (!$this->deflist) $output .= "<dl>\n";
		$this->deflist = true;

		switch($matches[1]) {
			case ';':
				$term = $matches[2];
				$p = strpos($term,' :');
				if ($p!==false) {
					list($term,$definition) = explode(':',$term);
					$output .= "<dt>{$term}</dt><dd>{$definition}</dd>";
				} else {
					$output .= "<dt>{$term}</dt>";
				}
				break;
			case ':':
				$definition = $matches[2];
				$output .= "<dd>{$definition}</dd>\n";
				break;
		}
		
		return $output;
	}
	
	function handle_preformat($matches,$close=false) {
		if ($close) {
			$this->preformat = false;
			return "</pre>\n";
		}
		
		$this->stop_all = true;

		$output = "";
		if (!$this->preformat) $output .= "<pre>";
		$this->preformat = true;
		
		$output .= $matches[1];
		
		return $output."\n";
	}
	
	function handle_horizontalrule($matches) {
		return "<hr />";
	}
	
	function wiki_link($topic) {
		return ucfirst(str_replace(' ','_',$topic));
	}
	
	function handle_image($href,$title,$options) {
		if ($this->ignore_images) return "";
		if (!$this->image_uri) return $title;
		
		$href = $this->image_uri . $href;
		
		$imagetag = sprintf(
			'<img src="%s" alt="%s" />',
			$href,
			$title
		);
		foreach ($options as $k=>$option) {
			switch($option) {
				case 'frame':
					$imagetag = sprintf(
						'<div style="float: right; background-color: #F5F5F5; border: 1px solid #D0D0D0; padding: 2px">'.
						'%s'.
						'<div>%s</div>'.
						'</div>',
						$imagetag,
						$title
					);
					break;
				case 'right':
					$imagetag = sprintf(
						'<div style="float: right">%s</div>',
						$imagetag
					);
					break;
			}
		}
		
		return $imagetag;
	}
	
	function handle_internallink($matches) {
		//var_dump($matches);
		$nolink = false;
		
		$href = $matches[4];
		$title = $matches[6] ? $matches[6] : $href.$matches[7];
		$namespace = $matches[3];

		if ($namespace=='Image') {
			$options = explode('|',$title);
			$title = array_pop($options);
			
			return $this->handle_image($href,$title,$options);
		}		
		
		$title = preg_replace('/\(.*?\)/','',$title);
		$title = preg_replace('/^.*?\:/','',$title);
		
		if ($this->reference_wiki) {
			$href = $this->reference_wiki.($namespace?$namespace.':':'').$this->wiki_link($href);
		} else {
			$nolink = true;
		}

		if ($nolink) return $title;
		
		return sprintf(
			'<a href="%s"%s>%s</a>',
			$href,
			($newwindow?' target="_blank"':''),
			$title
		);
	}
	
	function handle_externallink($matches) {
		$href = $matches[2];
		$title = $matches[3];
		if (!$title) {
			$this->linknumber++;
			$title = "[{$this->linknumber}]";
		}
		$newwindow = true;
		
		return sprintf(
			'<a href="%s"%s>%s</a>',
			$href,
			($newwindow?' target="_blank"':''),
			$title
		);		
	}
	
	function emphasize($amount) {
		$amounts = [
			2=>['<em>','</em>'],
			3=>['<strong>','</strong>'],
			4=>['<strong>','</strong>'],
			5=>['<em><strong>','</strong></em>'],
		];

		$output = "";

		// handle cases where emphasized phrases end in an apostrophe, eg: ''somethin'''
		// should read <em>somethin'</em> rather than <em>somethin<strong>
		if ( (!$this->emphasis[$amount]) && ($this->emphasis[$amount-1]) ) {
			$amount--;
			$output = "'";
		}

		$output .= $amounts[$amount][(int) $this->emphasis[$amount]];

		$this->emphasis[$amount] = !$this->emphasis[$amount];
		
		return $output;
	}
	
	function handle_emphasize($matches) {
		$amount = strlen($matches[1]);
		return $this->emphasize($amount);

	}
	
	function emphasize_off() {
		$output = "";
		foreach ($this->emphasis as $amount=>$state) {
			if ($state) $output .= $this->emphasize($amount);
		}
		
		return $output;
	}
	
	function handle_eliminate($matches) {
		return "";
	}
	
	function handle_variable($matches) {
		switch($matches[2]) {
			case 'CURRENTMONTH': return date('m');
			case 'CURRENTMONTHNAMEGEN':
			case 'CURRENTMONTHNAME': return date('F');
			case 'CURRENTDAY': return date('d');
			case 'CURRENTDAYNAME': return date('l');
			case 'CURRENTYEAR': return date('Y');
			case 'CURRENTTIME': return date('H:i');
			case 'NUMBEROFARTICLES': return 0;
			case 'PAGENAME': return $this->page_title;
			case 'NAMESPACE': return 'None';
			case 'SITENAME': return $_SERVER['HTTP_HOST'];
			default: return '';	
		}
	}
	
	function parse_line($line) {
		$line_regexes = [
			'preformat'=>'^\s(.*?)$',
			'definitionlist'=>'^([\;\:])\s*(.*?)$',
			'newline'=>'^$',
			'list'=>'^([\*\#]+)(.*?)$',
			'sections'=>'^(={1,6})(.*?)(={1,6})$',
			'horizontalrule'=>'^----$',
		];
		$char_regexes = [
//			'link'=>'(\[\[((.*?)\:)?(.*?)(\|(.*?))?\]\]([a-z]+)?)',
			'internallink'=>'('.
				'\[\['. // opening brackets
					'(([^\]]*?)\:)?'. // namespace (if any)
					'([^\]]*?)'. // target
					'(\|([^\]]*?))?'. // title (if any)
				'\]\]'. // closing brackets
				'([a-z]+)?'. // any suffixes
				')',
			'externallink'=>'('.
				'\['.
					'([^\]]*?)'.
					'(\s+[^\]]*?)?'.
				'\]'.
				')',
			'emphasize'=>'(\'{2,5})',
			'eliminate'=>'(__TOC__|__NOTOC__|__NOEDITSECTION__)',
			'variable'=>'('. '\{\{' . '([^\}]*?)' . '\}\}' . ')',
		];
				
		$this->stop = false;
		$this->stop_all = false;

		$called = [];
		
		$line = rtrim($line);
				
		foreach ($line_regexes as $func=>$regex) {
			if (preg_match("/$regex/i",$line,$matches)) {
				$called[$func] = true;
				$func = "handle_".$func;
				$line = $this->$func($matches);
				if ($this->stop || $this->stop_all) break;
			}
		}
		if (!$this->stop_all) {
			$this->stop = false;
			foreach ($char_regexes as $func=>$regex) {
				$line = preg_replace_callback("/$regex/i",[&$this,"handle_".$func],$line);
				if ($this->stop) break;
			}
		}
		
		$isline = strlen(trim($line))>0;
		
		// if this wasn't a list item, and we are in a list, close the list tag(s)
		if (($this->list_level>0) && !$called['list']) $line = $this->handle_list(false,true) . $line;
		if ($this->deflist && !$called['definitionlist']) $line = $this->handle_definitionlist(false,true) . $line;
		if ($this->preformat && !$called['preformat']) $line = $this->handle_preformat(false,true) . $line;
		
		// suppress linebreaks for the next line if we just displayed one; otherwise re-enable them
		if ($isline) $this->suppress_linebreaks = ($called['newline'] || $called['sections']);
		
		return $line;
	}
	
	function parse($text,$title = '') {
		
		$this->nowikis
			= $this->code
			= $this->list_level_types
			= [];
			
		$this->list_level
			= $this->linknumber
			= 0;
		
		$this->deflist
			= $this->suppress_linebreaks
			= $this->redirect
			= false;
		
		$this->page_title = $title;
		$output = '';
		
		$text = preg_replace_callback('/<nowiki>([\s\S]*?)<\/nowiki>/i',[&$this,'handle_save_nowiki'],$text);
		$text = preg_replace_callback('/<code>([\s\S]*?)<\/code>/i',[&$this,'handle_save_code'],$text);	

		$lines = explode("\n",$text);
		
		if (preg_match('/^\#REDIRECT\s+\[\[(.*?)\]\]$/',trim($lines[0]),$matches)) {
			$this->redirect = $matches[1];
		}
		
		foreach ($lines as $k=>$line) {
			$line = $this->parse_line($line);
			$output .= $line;
		}
		
		$out = explode(' ',$output);
		foreach ($out as $i => $outitem) {
			$out[$i] = preg_replace('/(http|ftp|https)\:\/\/[\w\.\/;:-=\+&!@#$%^&*\(\)_\,\"\'\[\]\{\}\?\|]+/i','<a href="$0" target="_blank">$0</a>',$outitem).' ';
		}
		$output = implode(' ',$out);

		$output = preg_replace_callback('/<nowiki><\/nowiki>/i',[&$this,'handle_restore_nowiki'],$output);
		$output = preg_replace_callback('/<code><\/code>/i',[&$this,'handle_restore_code'],$output);

		return $output;
	}

	function handle_save_nowiki($matches) {
		array_push($this->nowikis,$matches[1]);
		return '<nowiki></nowiki>';
	}
	
	function handle_restore_nowiki($matches) {
		return array_pop($this->nowikis);
	}

	function handle_save_code($matches) {
		array_push($this->code,$matches[1]);
		return '<code></code>';
	}

	function handle_restore_code($matches) {
		return '<pre class="prettyprint linenums">'.htmlspecialchars(array_shift($this->code)).'</pre>';
	}
	
	public static function output($content) {
		$wiki = new Cana_Wiki;
		ob_start();
		$content = $wiki->parse($content);
		ob_end_clean();
		return $content;
	}
	
	public static function strip($content) {
		$content = stripslashes($content);
		$content = str_replace('</div>',"</div>\n",$content);
		$content = htmlspecialchars_decode($content);
		$content = strip_tags($content,'<b><li><i><s><u><a><ul><ol><li><code><?php><%asp><?>');

		$replace = [
			'/\<ul\>.*?\<\/ul\>/i',
			'/\<ol\>.*?\<\/ol\>/i'
		];
		$content = preg_replace_callback($replace,'Cana_Wiki::stripReplace',$content);

		$find = [
			'/\<(b|li|i|s|u|a|li|code).*?(style|onclick|onmouseover|onblur|onactive).*?\>/i',
			'/√¢‚Ç¨≈ì/',			// left side double smart quote
			'/√¢‚Ç¨¬ù/',			// right side double smart quote
			'/√¢‚Ç¨Àú/',			// left side single smart quote
			'/√¢‚Ç¨‚Ñ¢/',			// right side single smart quote
			'/√¢‚Ç¨¬¶/',			// elipsis
			'/√¢‚Ç¨‚Äù/',			// em dash
			'/√¢‚Ç¨‚Äú/',			// en dash
			'/ﾔ|ﾕ/',
			'/ﾒ|ﾓ/',
			'/‘|’/',
			'/“|”/',
			'/\<i\>(.*?)\<\/i\>/i',
			'/\<b\>(.*?)\<\/b\>/i'
		];

		$replace = [
			'',
			'"',
			'"',
			"'",
			"'",
			"...",
			"-",
			"-",
			'¥',
			'"',
			"'",
			'"',
			"''\\1''",
			"'''\\1'''"
		];

		$content = preg_replace($find, $replace, $content);
		
		return $content;
	}
	
	public static function stripReplace($content) {
		$content = $content[0];
		if (strpos($content,'<ol>') !== false) {
			$type = 'ol';
		} else {
			$type = 'ul';
		}
		$content = strip_tags($content, '<li>');
		$content = preg_replace('/\<\/li\>/i',"\n",$content);
		switch ($type) {
			case 'ol':
				$content = preg_replace('/\<li\>/i','# ',$content);
				break;
			case 'ul':
				$content = preg_replace('/\<li\>/i','* ',$content);
				break;
		}
		return $content;
	}

}
