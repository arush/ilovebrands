<?php

class Ebizmarts_SagePayReporting_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getDetailTransactionColumns()
	{
		return array(
								'transactiontype' => 'Transaction Type',
								'vpstxid' => 'VPS Tx ID',
								'vendortxcode' => 'Vendor Tx Code',
								'result' => 'Result',
								't3maction' =>'3rd Man Action',
								'authprocessor' => 'Auth Processor',
								);
	}

	public function formatBasket($basket)
	{

		if(strlen($basket)>0){

			$items = explode(':', $basket);

			array_shift($items);

			$cols = 6;

			$hasSku = (strpos($items[0], '|') !== false);
			if($hasSku){
				$e = explode('|', $items[0]);
				$items[0] = $e[1];
				array_unshift($items, $e[0]);
				$cols = 7;
			}

			$html = '<table class="trndetail-basket"><thead>
					<tr>';

			if($hasSku){
				$html .= '<td>Item Sku</td>';
			}

			$html .='	<td>Item Name</td>
						<td>Quantity</td>
						<td>Item value</td>
						<td>Item tax</td>
						<td>Item total</td>
						<td>Line total</td>
					</tr></thead><tbody>';

			$counter = 1;
			foreach($items as $_index => $_item){
				if($counter===1){
					$html .= '<tr>';
				}

				if($hasSku && $_item == 'Delivery'){
					$html .= '<td>---</td>';
				}

				$html .= '<td>'.$_item.'</td>';

				if(++$_index%$cols === 0){
					$html .= '</tr>';
					$counter = 1;
				}else{
					$counter++;
				}
			}

			$html .= '</tbody></table>';

		return $html;

		}

		return $basket;
	}

}