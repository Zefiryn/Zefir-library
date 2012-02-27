<?php
/**
 * @package Zefir_Filter
 */
/**
 * Class with methods dealing with filtering strings with multibyte support
 * @author zefiryn
 * @since Jan 2011
 */
class Zefir_Filter
{
	/**
	 * Constructor
	 * @access public
	 * @return void
	 */
	public function __construct()
	{}
	
	/**
	 * Get the first character of the given string
	 * @access public
	 * @static 
	 * @param string $string
	 * @param boolean $upper
	 * @param string $encoding
	 * @return string
	 */
	public static function getFirstLetter($string, $upper = TRUE, $encoding = 'UTF-8')
	{
		$end = FALSE;	
		$letters = 'abcdefghijklmnopqrstuwxyzáčďéěíňóřšťúůýžàáâãäåæçèéêëíìíîïñòóôõöùúûüýÿăąćĉċčĕęěĝğġģĥĵķĸĺĻļłńņňŏŕŗřśŝşšţťũŭŰűųŵŷýźżž';
		
		$sign = mb_substr($string, 0, 1, $encoding);
		
		if ($sign == '„')
		{	
			$sign = mb_substr($string, 1, 1, $encoding);
			
		}
		
		if ($upper == TRUE)
			return mb_strtoupper($sign, $encoding);
		else
			return $sign;
	}

	/**
	 * Get the last character of the given string
	 * @access public
	 * @static 
	 * @param string $string
	 * @param boolean $upper
	 * @param string $encoding
	 * @return string
	 */
	public static function getLastChar($string, $upper = TRUE, $encoding = 'UTF-8')
	{
		if ($upper == TRUE)
			return mb_strtoupper(mb_substr($string, -1, 1, $encoding), $encoding);
		else
			return mb_substr($string, -1, 1, $encoding);
	}
	
	/**
	* CONVERTS DIACRITIC SYMBOLS TO NON-DIACRITIC ONE
	* this function was found: http://mynthon.net/howto/-/strip-accents-romove-national-characters-replace-diacritic-chars.txt
	* @param string $s a string in which charaters should be changed
	* @param int $case states whether output string should change letter case or not (-1-lowercase, 0 - no change, 1-uppercase)
	**/
	public static function strToUrl($s, $case=0)
	{
		$acc =	'É	Ê	Ë	š	Ì	Í	ƒ	œ	µ	Î	Ï	ž	Ð	Ÿ	Ñ	Ò	Ó	Ô	Š	£	Õ	Ö	Œ	¥	Ø	Ž	§	À	Ù	Á	Ú	Â	Û	Ã	Ü	Ä	Ý	';
		$str =	'E	E	E	s	I	I	f	o	m	I	I	z	D	Y	N	O	O	O	S	L	O	O	O	Y	O	Z	S	A	U	A	U	A	U	A	U	A	Y	';
		$acc.=	'Å	Æ	ß	Ç	à	È	á	â	û	Ĕ	ĭ	ņ	ş	Ÿ	ã	ü	ĕ	Į	Ň	Š	Ź	ä	ý	Ė	į	ň	š	ź	å	þ	ė	İ	ŉ	Ţ	Ż	æ	ÿ	';
		$str.=	'A	A	S	C	a	E	a	a	u	E	i	n	s	Y	a	u	e	I	N	S	Z	a	y	E	i	n	s	z	a	p	e	I	n	T	Z	a	y	';
		$acc.=	'Ę	ı	Ŋ	ţ	ż	ç	Ā	ę	Ĳ	ŋ	Ť	Ž	è	ā	Ě	ĳ	Ō	ť	ž	é	Ă	ě	Ĵ	ō	Ŧ	ſ	ê	ă	Ĝ	ĵ	Ŏ	ŧ	ë	Ą	ĝ	Ķ	ŏ	';
		$str.=	'E	l	n	t	z	c	A	e	I	n	T	Z	e	a	E	i	O	t	z	e	A	e	J	o	T	i	e	a	G	j	O	t	e	A	g	K	o	';
		$acc.=	'Ũ	ì	ą	Ğ	ķ	Ő	ũ	í	Ć	ğ	ĸ	ő	Ū	î	ć	Ġ	Ĺ	Œ	ū	ï	Ĉ	ġ	ĺ	œ	Ŭ	ð	ĉ	Ģ	Ļ	Ŕ	ŭ	ñ	Ċ	ģ	ļ	ŕ	Ů	';
		$str.=	'U	i	a	G	k	O	u	i	C	g	k	o	U	i	c	G	L	O	u	i	C	g	l	o	U	o	c	G	L	R	u	n	C	g	l	r	U	';
		$acc.=	'ò	ċ	Ĥ	Ľ	Ŗ	ů	ó	Č	ĥ	ľ	ŗ	Ű	ô	č	Ħ	Ŀ	Ř	ű	õ	Ď	ħ	ŀ	ř	Ų	ö	ď	Ĩ	Ł	Ś	ų	Đ	ĩ	ł	ś	Ŵ	ø	đ	';
		$str.=	'o	c	H	L	R	u	o	C	h	l	r	U	o	c	H	L	R	u	o	D	h	l	r	U	o	d	I	L	S	c	D	i	l	s	W	o	d	';
		$acc.=	'Ī	Ń	Ŝ	ŵ	ù	Ē	ī	ń	ŝ	Ŷ	Ə	ú	ē	Ĭ	Ņ	Ş	ŷ	 	:	;	.	,	‑';
		$str.=	'I	N	S	w	u	E	i	n	s	Y	e	u	e	I	N	S	y	-	-	-	-	-	-';
	
		$out = str_replace(explode("\t", $acc), explode("\t", $str), $s);

		if($case == -1)
		{
			return strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '', $out));
		}
		else if($case == 1)
		{
			return strtoupper(preg_replace('/[^a-zA-Z0-9_\-]/', '', $out));
		}
		else
		{
			return preg_replace('/[^a-zA-Z0-9_\-]/', '', $out);
		}
	}
	
	/**
	 * Get the extension from the filename
	 * @param string $name
	 * @return string $ext
	 */
	public static function getExtension($name)
	{
		if (strstr($name, '.'))
			$ext = substr($name, strrpos($name, '.')+1);
		else
			$ext = '';
			
		return $ext;
	}
	
	/**
	 * Strip bbcodes from the text
	 * @param string $string
	 */
	public static function stripBbcode($string)
	{
		$string = preg_replace('/\[.+\]/', null, $string);
		return $string;
	}
}

?>