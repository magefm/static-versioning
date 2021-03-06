<?php

class MageFM_StaticVersioning_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{

    protected function &_prepareStaticAndSkinElements($format, array $staticItems, array $skinItems, $mergeCallback = null)
    {
        $designPackage = Mage::getDesign();
        $baseJsUrl = Mage::getBaseUrl('js');
        $items = array();
        if ($mergeCallback && !is_callable($mergeCallback)) {
            $mergeCallback = null;
        }

        // get static files from the js folder, no need in lookups
        foreach ($staticItems as $params => $rows) {
            foreach ($rows as $name) {
                $filePath = Mage::getBaseDir() . DS . 'js' . DS . $name;
                $items[$params][] = $mergeCallback ? Mage::getBaseDir() . DS . 'js' . DS . $name : $baseJsUrl . $name . (file_exists($filePath) ? '?v=' . md5_file($filePath) : '');
            }
        }

        // lookup each file basing on current theme configuration
        foreach ($skinItems as $params => $rows) {
            foreach ($rows as $name) {
                $filePath = $designPackage->getFilename($name, array('_type' => 'skin'));
                $items[$params][] = $mergeCallback ? $designPackage->getFilename($name, array('_type' => 'skin')) : $designPackage->getSkinUrl($name, array()) . (file_exists($filePath) ? '?v=' . md5_file($filePath) : '');
            }
        }

        $html = '';
        foreach ($items as $params => $rows) {
            // attempt to merge
            $mergedUrl = false;
            if ($mergeCallback) {
                $mergedUrl = call_user_func($mergeCallback, $rows);
            }
            // render elements
            $params = trim($params);
            $params = $params ? ' ' . $params : '';
            if ($mergedUrl) {
                $html .= sprintf($format, $mergedUrl, $params);
            } else {
                foreach ($rows as $src) {
                    $html .= sprintf($format, $src, $params);
                }
            }
        }
        return $html;
    }

}
