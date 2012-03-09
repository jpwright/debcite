<?php
//
//  FPDI - Version 1.3.3
//
//    Copyright 2004-2010 Setasign - Jan Slabon
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//

/**
 * This class is used as a bridge between TCPDF and FPDI
 * and will create the possibility to use both FPDF and TCPDF
 * via one FPDI version.
 * 
 * We'll simply remap TCPDF to FPDF again.
 * 
 * It'll be loaded and extended by FPDF_TPL.
 */
class FPDF extends TCPDF {
    
    function __get($name) {
        switch ($name) {
            case 'PDFVersion':
                return $this->PDFVersion;
            case 'k':
                return $this->k;
            default:
                // Error handling
                $this->Error('Cannot access protected property '.get_class($this).':$'.$name.' / Undefined property: '.get_class($this).'::$'.$name);
        }
    }

    function __set($name, $value) {
        switch ($name) {
            case 'PDFVersion':
                $this->PDFVersion = $value;
                break;
            default:
                // Error handling
                $this->Error('Cannot access protected property '.get_class($this).':$'.$name.' / Undefined property: '.get_class($this).'::$'.$name);
        }
    }

    function _putstream($s)
	{
		$this->_out($this->_getstream($s));
	}
	
	function _putresourcedict() {
		$out = '2 0 obj';
		$out .= ' << /ProcSet [/PDF /Text /ImageB /ImageC /ImageI]';
		$out .= ' /Font <<';
		foreach ($this->fontkeys as $fontkey) {
			$font = $this->getFontBuffer($fontkey);
			$out .= ' /F'.$font['i'].' '.$font['n'].' 0 R';
		}
		$out .= ' >>';
		$out .= ' /XObject <<';
		foreach ($this->imagekeys as $file) {
			$info = $this->getImageBuffer($file);
			$out .= ' /I'.$info['i'].' '.$info['n'].' 0 R';
		}
		if (count($this->tpls)) {
            foreach($this->tpls as $tplidx => $tpl) {
                $out .= sprintf('%s%d %d 0 R', $this->tplprefix, $tplidx, $tpl['n']);
            }
        }
		$out .= ' >>';
		// visibility
		$out .= ' /Properties <</OC1 '.$this->n_ocg_print.' 0 R /OC2 '.$this->n_ocg_view.' 0 R>>';
		// transparency
		$out .= ' /ExtGState <<';
		foreach ($this->extgstates as $k => $extgstate) {
			if (isset($extgstate['name'])) {
				$out .= ' /'.$extgstate['name'];
			} else {
				$out .= ' /GS'.$k;
			}
			$out .= ' '.$extgstate['n'].' 0 R';
		}
		$out .= ' >>';
		// gradient patterns
		if (isset($this->gradients) AND (count($this->gradients) > 0)) {
			$out .= ' /Pattern <<';
			foreach ($this->gradients as $id => $grad) {
				$out .= ' /p'.$id.' '.$grad['pattern'].' 0 R';
			}
			$out .= ' >>';
		}
		// gradient shadings
		if (isset($this->gradients) AND (count($this->gradients) > 0)) {
			$out .= ' /Shading <<';
			foreach ($this->gradients as $id => $grad) {
				$out .= ' /Sh'.$id.' '.$grad['id'].' 0 R';
			}
			$out .= ' >>';
		}
		// spot colors
		if (isset($this->spot_colors) AND (count($this->spot_colors) > 0)) {
			$out .= ' /ColorSpace <<';
			foreach ($this->spot_colors as $color) {
				$out .= ' /CS'.$color['i'].' '.$color['n'].' 0 R';
			}
			$out .= ' >>';
		}
		$out .= ' >> endobj';
		$this->_out($out);
	}

    /**
     * Encryption of imported data by FPDI
     *
     * @param array $value
     */
    function pdf_write_value(&$value) {
        switch ($value[0]) {
    		case PDF_TYPE_STRING :
				if ($this->encrypted) {
				    $value[1] = $this->_unescape($value[1]);
                    $value[1] = $this->_RC4($this->_objectkey($this->_current_obj_id), $value[1]);
                 	$value[1] = $this->_escape($value[1]);
                } 
    			break;
    			
			case PDF_TYPE_STREAM :
			    if ($this->encrypted) {
			        $value[2][1] = $this->_RC4($this->_objectkey($this->_current_obj_id), $value[2][1]);
                }
                break;
                
            case PDF_TYPE_HEX :
            	if ($this->encrypted) {
                	$value[1] = $this->hex2str($value[1]);
                	$value[1] = $this->_RC4($this->_objectkey($this->_current_obj_id), $value[1]);
                    
                	// remake hexstring of encrypted string
    				$value[1] = $this->str2hex($value[1]);
                }
                break;
    	}
    }
    
    /**
     * Unescapes a PDF string
     *
     * @param string $s
     * @return string
     */
    function _unescape($s) {
        $out = '';
        for ($count = 0, $n = strlen($s); $count < $n; $count++) {
            if ($s[$count] != '\\' || $count == $n-1) {
                $out .= $s[$count];
            } else {
                switch ($s[++$count]) {
                    case ')':
                    case '(':
                    case '\\':
                        $out .= $s[$count];
                        break;
                    case 'f':
                        $out .= chr(0x0C);
                        break;
                    case 'b':
                        $out .= chr(0x08);
                        break;
                    case 't':
                        $out .= chr(0x09);
                        break;
                    case 'r':
                        $out .= chr(0x0D);
                        break;
                    case 'n':
                        $out .= chr(0x0A);
                        break;
                    case "\r":
                        if ($count != $n-1 && $s[$count+1] == "\n")
                            $count++;
                        break;
                    case "\n":
                        break;
                    default:
                        // Octal-Values
                        if (ord($s[$count]) >= ord('0') &&
                            ord($s[$count]) <= ord('9')) {
                            $oct = ''. $s[$count];
                                
                            if (ord($s[$count+1]) >= ord('0') &&
                                ord($s[$count+1]) <= ord('9')) {
                                $oct .= $s[++$count];
                                
                                if (ord($s[$count+1]) >= ord('0') &&
                                    ord($s[$count+1]) <= ord('9')) {
                                    $oct .= $s[++$count];    
                                }                            
                            }
                            
                            $out .= chr(octdec($oct));
                        } else {
                            $out .= $s[$count];
                        }
                }
            }
        }
        return $out;
    }
    
    /**
     * Hexadecimal to string
     *
     * @param string $hex
     * @return string
     */
    function hex2str($hex) {
    	return pack('H*', str_replace(array("\r", "\n", ' '), '', $hex));
    }
    
    /**
     * String to hexadecimal
     *
     * @param string $str
     * @return string
     */
    function str2hex($str) {
        return current(unpack('H*', $str));
    }
}