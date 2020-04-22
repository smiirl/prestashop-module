<?php

class smiirlcounterModuleFrontController extends ModuleFrontController
{
    public function __construct($response = array())
    {
        parent::__construct($response);
        $this->display_header = false;
        $this->display_header_javascript = false;
        $this->display_footer = false;
    }
    
    /*
    */
    public function display()
    {
		header('Content-Type: application/json');

		if(!Configuration::get('SMIIRL_LIVE_MODE'))
		{
			echo json_encode(array('live' => false), true);
		}
		elseif(Configuration::get('SMIIRL_TOKEN') !== preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['token']))
		{
			echo json_encode(array('token' => 'wrong'), true);
		}
		else
		{
			$date = new \DateTime('first day of this month');
			if(Configuration::get('SMIIRL_TIMERANGE') == 'week')
			{
				$date = new \DateTime('monday this week');
			}
			elseif(Configuration::get('SMIIRL_TIMERANGE') == 'day')
			{
				$date = new \DateTime('NOW');
				$date->setTime(0, 0, 0);
			}
			elseif(Configuration::get('SMIIRL_TIMERANGE') == 'year')
			{
				$date = new \DateTime('first day of january this year');
			}
			//$date->modify('+40 days');
			$sql = 'SELECT
				COUNT(*) as countOrders,
				SUM((SELECT SUM(od.product_quantity) FROM '._DB_PREFIX_.'order_detail od WHERE o.id_order = od.id_order)) as countProducts,
				SUM(o.total_paid_tax_excl / o.conversion_rate) as totalSales
				FROM '._DB_PREFIX_.'orders o
				WHERE o.valid = 1
				AND o.invoice_date >= "'.($date->format('Y/m/d')).'"';
			$results = Db::getInstance()->executeS($sql);
			if (isset($results[0])) {
				$json = array();
				foreach ($results[0] as $key => $value) {
					$json[$key] = intval($value);
				}
				$json['timerange'] = Configuration::get('SMIIRL_TIMERANGE');
				$json['from'] = intval($date->format('Ymd'));
				echo json_encode($json, true);
			}
		}
	}
	
	public function initContent()
	{
		parent::initContent();
	}
}
