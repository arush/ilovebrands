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

			//Remove line number from basket
			array_shift($items);

			//Split into rows
			$rows = array_chunk($items, 6);

			$html = '<table class="trndetail-basket"><thead>
					<tr>';

			$html .='   <td>Item Sku</td>
			            <td>Item Name</td>
						<td>Options</td>
						<td>Quantity</td>
						<td>Item value</td>
						<td>Item tax</td>
						<td>Item total</td>
						<td>Line total</td>
					</tr></thead><tbody>';

			foreach($rows as $_index => $row){

				$html .= '<tr>';

				$hasSku = (strpos($row[0], '|') !== false);
				if(!$hasSku){
					$row[0] = '--|'.$row[0];
				}

				$itemns = explode('|', $row[0]);

				//Product options
				$options = '';
				preg_match('/_(.*?)_/', $itemns[1], $matches);
				if(count($matches) > 0){
					$options = explode('.', str_replace('-', ' -> ', $matches[1]));
					$options = implode(', ', $options);

					$name = explode('_', $itemns[1]);
					$itemns[1] = $name[0];
				}

				$html .= '<td>'.$itemns[0].'</td>';
				$html .= '<td>'.$itemns[1].'</td>';
				$html .= '<td>'.$options.'</td>';
				$html .= '<td>'.$row[1].'</td>';
				$html .= '<td>'.$row[2].'</td>';
				$html .= '<td>'.$row[3].'</td>';
				$html .= '<td>'.$row[4].'</td>';
				$html .= '<td>'.$row[5].'</td>';

				$html .= '</tr>';

			}

			$html .= '</tbody></table>';

			return $html;

		}

		return $basket;
	}

}

