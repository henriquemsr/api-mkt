<?php
class functions{
    
    public static function hashSSHA($password) { 
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
 
    public static function checkhashSSHA($salt, $password) { 
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
         return $hash;
    }	
    
    public static function generatepass() {
	  $CaracteresAceitos = 'abcdxywzABCDZYWZ0123456789'; 
	  $max = strlen($CaracteresAceitos)-1;
	  $password = null;
	  for($i=0; $i < 8; $i++) { 
	   $password .= $CaracteresAceitos{mt_rand(0, $max)}; 
	  }
	  return $password;
	}
    
    public static function generatetoken($mail){
		$now = date('YmdHis');
		$token = $mail . $now;
		//32 caracter
		$ret = hash('md5', $token);
		return $ret;
	}


	public static function generateStrongToken($length = 9, $add_dashes = false, $available_sets = 'lud')
	{
		$sets = array();
		if(strpos($available_sets, 'l') !== false)
			$sets[] = 'abcdefghjkmnpqrstuvwxyz';
		if(strpos($available_sets, 'u') !== false)
			$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		if(strpos($available_sets, 'd') !== false)
			$sets[] = '23456789';
		if(strpos($available_sets, 's') !== false)
			$sets[] = '!@#$%&*?';
		$all = '';
		$password = '';
		foreach($sets as $set)
		{
			$password .= $set[array_rand(str_split($set))];
			$all .= $set;
		}
		$all = str_split($all);
		for($i = 0; $i < $length - count($sets); $i++)
			$password .= $all[array_rand($all)];
		$password = str_shuffle($password);
		if(!$add_dashes)
			return $password;
		$dash_len = floor(sqrt($length));
		$dash_str = '';
		while(strlen($password) > $dash_len)
		{
			$dash_str .= substr($password, 0, $dash_len) . '-';
			$password = substr($password, $dash_len);
		}
		$dash_str .= $password;
		return $dash_str;
	}

	public static function createSlug($string, $slug = false){

		$string = strtolower($string);
		// Código ASCII das vogais
		$ascii['a'] = range(224, 230);
		$ascii['e'] = range(232, 235);
		$ascii['i'] = range(236, 239);
		$ascii['o'] = array_merge(range(242, 246), array(240, 248));
		$ascii['u'] = range(249, 252);
		// Código ASCII dos outros caracteres
		$ascii['b'] = array(223);
		$ascii['c'] = array(231);
		$ascii['d'] = array(208);
		$ascii['n'] = array(241);
		$ascii['y'] = array(253, 255);
		foreach ($ascii as $key=>$item) {
			$acentos = '';
			foreach ($item AS $codigo) $acentos .= chr($codigo);
			$troca[$key] = '/['.$acentos.']/i';
		}
		$string = preg_replace(array_values($troca), array_keys($troca), $string);
		// Slug?
		if ($slug) {
			// Troca tudo que não for letra ou número por um caractere ($slug)
			$string = preg_replace('/[^a-z0-9]/i', $slug, $string);
			// Tira os caracteres ($slug) repetidos
			$string = preg_replace('/' . $slug . '{2,}/i', $slug, $string);
			$string = trim($string, $slug);
		}
		return $string;


	}
    
}
