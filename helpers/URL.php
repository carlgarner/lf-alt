<?php 

class URL
{
	private $uri_string;
	private $keyval			= array();
	private $segments		= array();

	public function __construct()
	{
		$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
		if(trim($path, '/') != '')
		{
			$this->uri_string = ($path == '/') ? '' : $path;
			
			foreach(explode('/', preg_replace("|/*(.+?)/*$|", "\\1", $this->uri_string)) as $val)
			{
				$val = trim($this->_filter_uri($val));

				if ($val != '')
				{
					$this->segments[] = $val;
				}
			}
		}
	}
	
	private function _filter_uri($str)
	{
		$bad	= array('$',		'(',		')',		'%28',		'%29');
		$good	= array('&#36;',	'&#40;',	'&#41;',	'&#40;',	'&#41;');

		return str_replace($bad, $good, $str);
	}

	private function _set_uri_string($str)
	{
		$this->uri_string = ($str == '/') ? '' : $str;
	}

	public function uri_to_assoc($n = 3, $default = array())
	{
		$total_segments = 'total_segments';
		$segment_array = 'segment_array';

		if(!is_numeric($n))
		{
			return $default;
		}

		if(isset($this->keyval[$n]))
		{
			return $this->keyval[$n];
		}

		if($this->_total_segments() < $n)
		{
			if(count($default) == 0)
			{
				return array();
			}

			$retval = array();
			foreach($default as $val)
			{
				$retval[$val] = FALSE;
			}
			
			return $retval;
		}

		$segments = array_slice($this->_segment_array(), ($n - 1));

		$i = 0;
		$lastval = '';
		$retval  = array();
		foreach($segments as $seg)
		{
			if ($i % 2)
			{
				$retval[$lastval] = $seg;
			}
			else
			{
				$retval[$seg] = FALSE;
				$lastval = $seg;
			}

			$i++;
		}

		if(count($default) > 0)
		{
			foreach($default as $val)
			{
				if(!array_key_exists($val, $retval))
				{
					$retval[$val] = FALSE;
				}
			}
		}

		$this->keyval[$n] = $retval;
		return $retval;
	}

	private function _segment_array()
	{
		return $this->segments;
	}

	private function _total_segments()
	{
		return count($this->segments);
	}

	public function uri_string()
	{
		return $this->uri_string;
	}
}