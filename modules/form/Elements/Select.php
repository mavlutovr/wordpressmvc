<?php
namespace Wdpro\Form\Elements;

class Select extends Base
{
	public function getParams()
	{
		$params = parent::getParams();
		
		if ($params['options'])
		{
			$options = array();
			
			foreach($params['options'] as $key=>$value)
			{
				if (is_array($value)) {
					$options[] = $value;
				}
				else {
					$options[] = array($key, $value);
				}
			}
			
			$params['options'] = $options;
		}
		
		return $params;
	}
}


