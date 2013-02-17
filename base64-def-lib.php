<?php
	/*
	 * Base64 Encoding and Decoding
	 *
	 * Copyleft @ 2012: Paulus Gandung Prakosa (syn-attack@devilzc0de.org)
	 */
	define ("__BASE64_CONST_I", 0x00fc0000);
	define ("__BASE64_CONST_II", 0x0003f000);
	define ("__BASE64_CONST_III", 0x00000fc0);
	define ("__BASE64_CONST_IV", 0x0000003f);
	
	class base64 {
		private $base64_charlist = array(
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
			'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f',
			'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
			'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '+', '/'
		);
		
		private function __find_index($chr) {
			for ($i = 0; $i < count($this->base64_charlist); $i++) {
				if ($chr === $this->base64_charlist[$i]) {
					return ($i);
				}
			}
		}
		
		public function __base64_encode($targ) {
			$temp = 0;
			$i = 0;
			$encoded = "";
			
			while (strlen($targ) > ($i + 3)) {
				$temp = (ord($targ[$i++]) & 0xff) << 16;
				$temp |= (ord($targ[$i++]) & 0xff) << 8;
				$temp |= (ord($targ[$i++]) & 0xff);
				
				$encoded .= $this->base64_charlist[($temp & __BASE64_CONST_I) >> 18];
				$encoded .= $this->base64_charlist[($temp & __BASE64_CONST_II) >> 12];
				$encoded .= $this->base64_charlist[($temp & __BASE64_CONST_III) >> 6];
				$encoded .= $this->base64_charlist[($temp & __BASE64_CONST_IV)];
			}
			
			if (strlen($targ) % $i == 2) {
				$temp = (ord($targ[$i++]) & 0xff) << 16;
				$temp |= (ord($targ[$i++]) & 0xff) << 8;
				
				$encoded .= $this->base64_charlist[($temp & __BASE64_CONST_I) >> 18];
				$encoded .= $this->base64_charlist[($temp & __BASE64_CONST_II) >> 12];
				$encoded .= ((($temp & __BASE64_CONST_III) >> 6) == 0x00) ? '=' : $this->base64_charlist[($temp & __BASE64_CONST_III) >> 6];
				$encoded .= (($temp & __BASE64_CONST_IV) == 0x00) ? '=' : $this->base64_charlist[($temp & __BASE64_CONST_IV)];
			}
			else if (strlen($targ) % $i == 1) {
				$temp = (ord($targ[$i++]) & 0xff) << 16;
				
				$encoded .= $this->base64_charlist[($temp & __BASE64_CONST_I) >> 18];
				$encoded .= $this->base64_charlist[($temp & __BASE64_CONST_II) >> 12];
				$encoded .= ((($temp & __BASE64_CONST_III) >> 6) == 0x00) ? '=' : $this->base64_charlist[($temp & __BASE64_CONST_III) >> 6];
				$encoded .= (($temp & __BASE64_CONST_IV) == 0x00) ? '=' : $this->base64_charlist[($temp & __BASE64_CONST_IV)];
			}
			
			return ($encoded);
		}
		
		public function __base64_decode($targ) {
			$temp = 0;
			$decoded = "";
			
			for ($i = 0; $i < strlen($targ); $i += 4) {
				$temp = ($this->__find_index($targ[$i]) & 0xff) << 18;
				$temp |= ($this->__find_index($targ[$i + 1]) & 0xff) << 12;
				$temp |= ($this->__find_index($targ[$i + 2]) & 0xff) << 6;
				$temp |= ($this->__find_index($targ[$i + 3]) & 0xff);
				
				$decoded .= chr(($temp & 0x00ff0000) >> 16);
				$decoded .= chr(($temp & 0x0000ff00) >> 8);
				$decoded .= chr($temp & 0x000000ff);
			}
			
			if (ord($targ[strlen($targ) - 2]) == 0x3d) {
				$decoded = substr($decoded, 0, strlen($decoded) - 2);
			}
			else if (ord($targ[strlen($targ) - 1]) == 0x3d) {
				$decoded = substr($decoded, 0, strlen($decoded) - 1);
			}
			
			return ($decoded);
		}
		
		public function __base64_file_encode($file_name, $use_include_path = false) {
			if ($use_include_path == false) {
				$fp = fopen($file_name . ".b64encoded", "w+");
				if (is_resource($fp) == true) {
					$content = $this->__base64_encode(file_get_contents($file_name, false));
					if (flock($fp, LOCK_EX) == true) {
						$nbytes_written = fwrite($fp, $content, strlen($content));
						if (flock($fp, LOCK_UN) == false) {
							fclose($fp);
							if (preg_match("/(Windows)/", php_uname('a'))) {
								shell_exec("del " . $file_name . ".b64encoded");
							}
							else if (preg_match("/(Linux)/", php_uname('a'))) {
								shell_exec("rm " . $file_name . ".b64encoded");
							}
							
							return (-1);
						}
					}
					
					fclose($fp);
				}
				else if (is_resource($fp) == false) {
					return (-1);
				}
			}
			else if ($use_include_path == true) {
				if (chdir(dirname($file_name)) == true) {
					$fp = fopen(basename($file_name) . ".b64encoded", "w+");
					if (is_resource($fp) == true) {
						$content = $this->__base64_encode(file_get_contents($file_name, false));
						if (flock($fp, LOCK_EX) == true) {
							$nbytes_written = fwrite($fp, $content, strlen($content));
							if (flock($fp, LOCK_UN) == false) {
								fclose($fp);
								if (preg_match("/(Windows)/", php_uname('a'))) {
									shell_exec("del " . basename($file_name) . ".b64encoded");
								}
								else if (preg_match("/(Linux)/", php_uname('a'))) {
									shell_exec("rm " . basename($file_name) . ".b64encoded");
								}
								
								return (-1);
							}
						}
						
						fclose($fp);
					}
					else if (is_resource($fp) == false) {
						return (-1);
					}
				}
				else if (chdir(dirname($file_name)) == false) {
					return (-1);
				}
			}
			
			return ($nbytes_written);
		}
		
		public function __base64_file_decode($file_name, $use_include_path = false) {
			if (preg_match("/(.b64encoded)/", $file_name)) {
				if ($use_include_path == false) {
					$fp = fopen($file_name . ".b64decoded", "w+");
					if (is_resource($fp) == true) {
						$content = $this->__base64_decode(file_get_contents($file_name, false));
						if (flock($fp, LOCK_EX) == true) {
							$nbytes_written = fwrite($fp, $content, strlen($content));
							if (flock($fp, LOCK_UN) == false) {
								fclose($fp);
								if (preg_match("/(Windows)/", php_uname('a'))) {
									shell_exec("del " . $file_name . ".b64decoded");
								}
								else if (preg_match("/(Linux)/", php_uname('a'))) {
									shell_exec("rm " . $file_name . ".b64decoded");
								}
								
								return (-1);
							}
						}
						
						fclose($fp);
					}
					else if (is_resource($fp) == false) {
						return (-1);
					}
				}
				else if ($use_include_path == true) {
					if (chdir(dirname($file_name)) == true) {
						$fp = fopen(basename($file_name) . ".b64decoded", "w+");
						if (is_resource($fp) == true) {
							$content = $this->__base64_decode(file_get_contents($file_name, false));
							if (flock($fp, LOCK_EX) == true) {
								$nbytes_written = fwrite($fp, $content, strlen($content));
								if (flock($fp, LOCK_UN) == false) {
									fclose($fp);
									if (preg_match("/(Windows)/", php_uname('a'))) {
										shell_exec("del " . basename($file_name) . ".b64decoded");
									}
									else if (preg_match("/(Linux)/", php_uname('a'))) {
										shell_exec("rm " . basename($file_name) . ".b64decoded");
									}
									
									return (-1);
								}
							}
							
							fclose($fp);
						}
						else if (is_resource($fp) == false) {
							return (-1);
						}
					}
					else if (chdir(dirname($file_name)) == false) {
						return (-1);
					}
				}
			}
			else if (preg_match("/(.b64encoded)/", $file_name)) {
				return (-1);
			}
			
			return ($nbytes_written);
		}
	}
?>