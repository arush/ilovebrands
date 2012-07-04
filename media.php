<?php

/*
 * imageurls.php allows you to dump the Media Gallery to a table.
 * cut'n paste out of Internet Explorer into Excel to build your spreadsheet.
 * Modify for performance or your particular needs.
 *
 */

?> 
 
<?php

require_once 'app/Mage.php';
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);
$baseurl = Mage::getBaseUrl('media');
?>

<html><head><title>Media Gallery Images</title></head><body>

<?php

$collection = Mage::getModel('catalog/product')
    ->getCollection()
    ->addAttributeToSort('sku','ASC')
    ->addAttributeToSelect('sku');

echo "<table><tbody>";

     echo '<tr style="color:#ffffff;background-color:#000000;"><td>ProdID</td><td>SKU</td><td>Full URL</td></tr>'."\n";



foreach ($collection as $product) {
    $sku = $product->getSku();
    $prodid = $product->getId();
    $galleryimages = Mage::getModel('catalog/product')->load($product->getId())->getMediaGalleryImages();
    

    echo "<tr><td>".$prodid."</td><td>".$sku."</td><td>";


    $numImages = count($galleryimages);
    $i = 0;
    //echo $numImages . '\n\n' . json_encode($galleryimages);
    
    foreach($galleryimages as $image) {

        echo $image->getUrl();
        if($numImages > 1 && $i != ($numImages-1)) {
            echo ';';
        }
        $i++;
    }

    echo "</td></tr>\n";

    flush();
}

echo "</tbody></table>";

?> 


</body></html>
