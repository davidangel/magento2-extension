<?php
/**
 * StoreFront Bazaarvoice Extension for Magento
 *
 * PHP Version 5
 *
 * LICENSE: This source file is subject to commercial source code license
 * of StoreFront Consulting, Inc.
 *
 * @category  SFC
 * @package   Bazaarvoice_Ext
 * @author    Dennis Rogers <dennis@storefrontconsulting.com>
 * @copyright 2016 StoreFront Consulting, Inc
 * @license   http://www.storefrontconsulting.com/media/downloads/ExtensionLicense.pdf StoreFront Consulting Commercial License
 * @link      http://www.StoreFrontConsulting.com/bazaarvoice-extension/
 */

namespace Bazaarvoice\Connector\Block;

use \Bazaarvoice\Connector\Helper\Seosdk;

class Questions extends Product
{

    public function getSEOContent()
    {
        $seoContent = '';
        if ($this->getConfig('general/enable_cloud_seo') && $this->isEnabled()) {
            /** Check if admin has configured a legacy display code */
            if (strlen($this->getConfig('bv_config/display_code'))) {
                $deploymentZoneId =
                    $this->getConfig('bv_config/display_code') .
                    '-' . $this->getConfig('general/locale');
            }
            else {
                $deploymentZoneId =
                    str_replace(' ', '_', $this->getConfig('general/deployment_zone')) .
                    '-' . $this->getConfig('general/locale');
            }

            $product = $this->getProduct();

            $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
            $productUrl = $urlInterface->getCurrentUrl();
            $parts = parse_url($productUrl);
            if (isset($parts['query'])) {
                parse_str($parts['query'], $query);
                unset($query['bvrrp']);
	            unset($query['bvstate']);
                $baseUrl = $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . '?' . http_build_query($query);
            } else {
                $baseUrl = $productUrl;
            }

            $this->_logger->debug($baseUrl);

            $params = array(
                'seo_sdk_enabled' => TRUE,
                'bv_root_folder' => $deploymentZoneId, /** replace with your display code (BV provided) */
                'subject_id' => $this->getHelper()->getProductId($product), /** replace with product id */
                'cloud_key' => $this->getConfig('general/cloud_seo_key'), /** BV provided value */
                'base_url' => $baseUrl,
                'page_url' => $productUrl,
                'staging' => ($this->getConfig('general/environment') == 'staging' ? TRUE : FALSE)
            );


            $requestInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\RequestInterface');
            if ($requestInterface->getParam('bvreveal') == 'debug')
                $params['bvreveal'] = 'debug';

            $bv = new Seosdk($params);
            $seoContent = $bv->questions->getContent();
            $seoContent .= '<!-- BV Questions Parameters: ' . print_r($params, 1) . '-->';
        }

        return $seoContent;
    }

}